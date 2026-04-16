@extends('layouts.template1')

@section('content')
<div class="card">
	<div class="card-body">
		<div class="row">
			<div class="col-md-12">
				<div class="button-list">
					<a href="{{ url('keterangan_status_mahasiswa/add') }}" class="btn btn-bordered-primary waves-effect width-md waves-light"><i class="mdi mdi-plus"></i> Tambah Data</a>
					<div class="btn-group">
						<button type="button" class="btn btn-info dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false"><i class="mdi mdi-printer mr-1"></i> Cetak <i class="mdi mdi-chevron-down"></i></button>
						<div class="dropdown-menu">
							<a onclick="pdf()" href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-printer"></i> PDF</a>
							<a onclick="excel()" href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-printer"></i> Excel</a>
						</div>
					</div>
					<button class="btn btn-bordered-danger waves-effect width-md waves-light" id="btnDelete" data-placement="top" title="Silahkan pilih data terlebih dahulu." data-toggle="modal" data-target="#hapus"><i class="mdi mdi-delete"></i> Hapus</button>  	
                    <a href="#mdle" data-toggle="modal" class="btn btn-bordered-success waves-effect waves-light"><i class="mdi mdi-file"></i> Upload Data dari Excel</a>
				</div>
			</div>
		</div>
		<div class="form-row mt-2">
			<div class="form-group col-md-4">
				<label class="col-form-label"><h5 class="mb-0">Tahun Semester</h5></label>
				<select class="TahunID form-control" onchange="filter()">
					<option value="">-- Lihat Semua --</option>
					@foreach($tahuns as $row)
						<option value="{{ $row->ID }}">{{ $row->Nama }}</option>
					@endforeach
				</select>
			</div>

            <div class="form-group col-md-4">
                <label class="col-form-label"><h5 class="mb-0">Program Kuliah</h5></label>
                <select class="ProgramID form-control" onchange="filter()">
                    <option value="">-- Semua --</option>
                    @foreach($programs as $row)
                        <option value="{{ $row->ID }}">{{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-4">
                <label class="col-form-label"><h5 class="mb-0">Program Studi</h5></label>
                <select class="ProdiID form-control" onchange="filter()">
                    <option value="">-- Semua --</option>
                    @foreach($prodis as $row)
                        <option value="{{ $row->ID }}">{{ $row->ProdiID }} || {{ get_field($row->JenjangID,'jenjang') }} || {{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="col-form-label"><h5 class="mb-0">Status</h5></label>
                <select class="StatusMhswID form-control" onchange="filter()">
                    <option value="">-- Lihat Semua --</option>
                    @foreach($statuses as $row)
                        <option value="{{ $row->ID }}">{{ $row->KodeDikti }} || {{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>			
             <div class="form-group col-md-4">
                <label class="col-form-label"><h5 class="mb-0">Tahun Masuk</h5></label>
                <select class="TahunMasuk form-control" onchange="filter()">
                    <option value="">-- Lihat Semua --</option>
                    @foreach($tahunMasuk as $row)
                        <option value="{{ $row->TahunMasuk }}">{{ $row->TahunMasuk }}</option>
                    @endforeach
                </select>
            </div>		
			<div class="form-group col-md-4">
				<label class="col-form-label mt-2"><h5 class="m-0">Kata Kunci</h5></label>
				<input type="text" class="form-control keyword" onkeyup="filter()" placeholder="Kata Kunci ..">
			</div>
		</div>
	</div>
</div>
<div class="card">
	<div class="card-body">
		<div id="konten"></div>
	</div>
</div>

<form id="f_import" onsubmit="saveDataImport(this);return false;" action="{{ url('keterangan_status_mahasiswa/import') }}" enctype="multipart/form-data" method="POST" class="form-horizontal">
    @csrf
	<div class="modal fade" id="mdle" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
            <h4 class="modal-title">Download & Upload Data</h4>       
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		    </div>
			<div class="modal-body">
				<table>
					<tr><td><h4><b><i class="fa fa-cog"></i> Cara Upload Data</b></h4></td></tr>
					<tr><td>1. Silahkan download file Excel-nya terlebih dahulu (Tombol `Download Format`).</td></tr>
					<tr><td>2. Buka file tersebut menggunakan Ms.Excel atau aplikasi Excel Viewer lainnya.</td></tr>
					<tr><td>3. Masukan NPM, Kode Tahun, Tanggal (yyyy-mm-dd), dan Status Mahasiswa di kolom yang sudah disediakan.</td></tr>
					<tr><td>4. Kode Tahun Dapat Dilihat di menu Tahun Akademik.</td></tr>
					<tr><td>5. Status Mahasiswa yg diinputkan harus <b>sama</b> dengan yang ada di <b>aplikasi</b>.</td></tr>
					<tr><td>6. Simpan, lalu upload kembali ke aplikasi.</td></tr>
				</table>
				<br>
				<div class="form-horizontal">
					<div class="control-group">
						<input type="file" name="file" accept=".xls, .xlsx" class="form-control" required />
						<button id="download" class="btn btn-bordered-success mt-2" type="button"><i class="fa fa-download"></i> Download Format</button> 
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" id="upload" class="btn btn-primary">Upload</button>
				<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
			</div>
		</div>
	  </div>
	</div>
</form>
@endsection

@push('scripts')
<script type="text/javascript">	
function filter(url) {
	if(url == null) url = "{{ url('keterangan_status_mahasiswa/search') }}";
	
	$.ajax({
		type: "POST",
		url: url,
		data: {
            _token: "{{ csrf_token() }}",
			ProdiID : $(".ProdiID").val(),
			ProgramID : $(".ProgramID").val(),
			TahunID : $(".TahunID").val(),
			keyword : $(".keyword").val(),
			StatusMhswID : $(".StatusMhswID").val(),
			TahunMasuk : $(".TahunMasuk").val(),
		},
		success: function(data) {
			$("#konten").html(data);
		}
	});
	return false;
}

function pdf(){
    var params = $.param({
        ProdiID : $(".ProdiID").val(),
        ProgramID : $(".ProgramID").val(),
        TahunID : $(".TahunID").val(),
        keyword : $(".keyword").val(),
        StatusMhswID : $(".StatusMhswID").val(),
        TahunMasuk : $(".TahunMasuk").val(),
    });
	window.open("{{ url('keterangan_status_mahasiswa/pdf') }}?" + params, '_Blank');
}
	
function excel(){
    var params = $.param({
        ProdiID : $(".ProdiID").val(),
        ProgramID : $(".ProgramID").val(),
        TahunID : $(".TahunID").val(),
        keyword : $(".keyword").val(),
        StatusMhswID : $(".StatusMhswID").val(),
        TahunMasuk : $(".TahunMasuk").val(),
    });
	window.open("{{ url('keterangan_status_mahasiswa/excel') }}?" + params, '_Blank');
}

filter();

$("#download").click(function(){
    window.open("{{ url('keterangan_status_mahasiswa/downloadFormat') }}", "_BLANK");
});

function saveDataImport(formz) {
    var formData = new FormData(formz);
    $.ajax({
        type: 'POST',
        url: $(formz).attr('action'),
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function(){
            $('#upload').html('<i class="fa fa-spin fa-spinner"></i> Silahkan Tunggu ...').attr('disabled', true);
        },
        success: function(data){
            swal({
                title: "Pemberitahuan",
                text: data,
                type: "info"
            }).then((res)=>{
                $("#mdle").modal("hide");
                filter();
            });
            $('#upload').html('Upload').removeAttr('disabled');
        },
        error: function(){
            swal('Pemberitahuan', 'Maaf file gagal di proses !.', 'error');
            $('#upload').html('Upload').removeAttr('disabled');
        }
    });
    return false;
}
</script>
@endpush
