@extends('layouts.template1')

@section('content')
<div class="card">
	<div class="card-body">
		<div class="row">
			<div class="col-md-12">
				<div class="button-list">
					@if($Create == 'YA')
						<a href="{{ url('mahasiswa/add') }}" class="btn btn-bordered-primary waves-effect  width-md waves-light"><i class="mdi mdi-plus"></i> {{ __('mahasiswa.add') }} Data</a>
					@endif
					<div class="btn-group">
						<button type="button" class="btn btn-info dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false"><i class="mdi mdi-printer mr-1"></i> Cetak <i class="mdi mdi-chevron-down"></i></button>
						<div class="dropdown-menu">
							<a onclick="pdf()" href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-printer"></i> {{ __('mahasiswa.pdf') }}</a>
							<a onclick="excel()" href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-printer"></i> {{ __('mahasiswa.excel') }}</a>
							<a onclick="ktm_all()" href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-printer"></i> Cetak KTM Kolektif</a>
						</div>
					</div>
					<button class="btn btn-bordered-danger waves-effect  width-md waves-light" id="btnDelete" data-placement="top" title="Silahkan pilih data terlebih dahulu." data-toggle="modal"><i class="mdi mdi-delete"></i> {{ __('mahasiswa.delete') }}</button>  	
					<div class="btn-group">
						<button type="button" class="btn btn-success dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false"><i class="mdi mdi-upload"></i> Upload Data by Excel <i class="mdi mdi-chevron-down"></i></button>
						<div class="dropdown-menu">
							<a href="{{ url('mahasiswa/add_upload/tambah') }}" class="dropdown-item"><i class="mdi mdi-upload"></i> Tambah Data Mahasiswa Baru</a>
							<a href="{{ url('mahasiswa/add_upload/edit') }}" class="dropdown-item"><i class="mdi mdi-upload"></i> Edit Data Biografi Mahasiswa</a>
						</div>
					</div>
					<a href="{{ url('mahasiswa/add_student') }}" class="btn btn-bordered-warning waves-light waves-effect"><i class="mdi mdi-download"></i> Ubah Data Kolektif</a>
					@if($button_moodle == 1)
						<a onclick="generate_data()" class="btn btn-bordered-secondary waves-light waves-effect"><i class="mdi mdi-application-export"></i> Generate Ke Moodle</a>
					@endif
				</div>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">{{ __('mahasiswa.ProgramID') }}</h5></label>
				<select class="ProgramID form-control" onchange="filter()">
					<option value=""> -- {{ __('mahasiswa.view_all') }} -- </option>
					@foreach(get_all('program') as $row)
						<option value="{{ $row->ID }}">{{ $row->ProgramID }} || {{ $row->Nama }}</option>
					@endforeach
				</select>
            </div>
			
            <div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">{{ __('mahasiswa.ProdiID') }}</h5></label>
				<select class="ProdiID form-control" onchange="filter(); changekelas();">
				<option value="">-- {{ __('mahasiswa.view_all') }} --</option>
				@foreach(get_all('programstudi') as $row)
					<option value="{{ $row->ID }}">{{ $row->ProdiID }} || {{ get_field($row->JenjangID,'jenjang') }} || {{ $row->Nama }}</option>
				@endforeach
				</select>
			</div>	
            <div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">{{ __('mahasiswa.StatusMhswID') }}</h5></label>
				<select class="StatusMhswID form-control" onchange="filter()">
					<option value=""> -- {{ __('mahasiswa.view_all') }} -- </option>
					@foreach(get_all('statusmahasiswa') as $row)
						<option value="{{ $row->ID }}">{{ $row->Nama }}</option>
					@endforeach
				</select>
            </div>
            
            <div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">{{ __('mahasiswa.TahunMasuk') }}</h5></label>
				<select class="TahunMasuk form-control" onchange="filter()">
					<option value=""> -- {{ __('mahasiswa.view_all') }} -- </option>
					@foreach($TahunMasuk as $row)
						<option value="{{ $row->TahunMasuk }}">{{ $row->TahunMasuk }}</option>
					@endforeach
				</select>
			</div>		
            <div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">{{ __('mahasiswa.SemesterMasuk') }}</h5></label>
				<select class="SemesterMasuk form-control" onchange="filter()">
					<option value=""> -- {{ __('mahasiswa.view_all') }} -- </option>
					<option value="1">Ganjil</option>
					<option value="2">Genap</option>
				</select>
			</div>		
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">Status Pindahan</h5></label>
				<select class="statusPindahan form-control" onchange="filter()">
					<option value=""> -- Lihat Semua -- </option>							
					@foreach(DB::table('jenis_pendaftaran')->where('Aktif','Ya')->get() as $jp)
					    <option value="{{ $jp->Kode }}"> {{ $jp->Nama }} </option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">Urutkan dengan</h5></label>
				<div class="row">
					<div class="col-md-6">
						<select name="orderby" class="form-control orderby" id="orderby"  onchange="filter()">
							<option value="mahasiswa.Nama" >Nama</option>
							<option value="mahasiswa.NPM" >NPM</option>
						</select>
					</div>
					<div class="col-md-6">
						<select name="descasc" class="form-control descasc" id="descasc form-control" onchange="filter()">
							<option value="ASC" >A-Z</option>
							<option value="DESC">Z-A</option>
						</select>
					</div>
				</div>
            </div>
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">{{ __('mahasiswa.keyword_legend') }}</h5></label>
				<input type="text" class="form-control keyword" onkeyup="filter()" placeholder="{{ __('mahasiswa.keyword') }} .." />
			</div>
		</div>
	</div>
</div>
<div class="card">
	<div class="card-body">
		<div id="konten"></div>
	</div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">	
function changekelas(){	
	// Implementation adapted for compatibility if needed.
    // If c_kelas controller is intact in CI3 but needed here:
    // $.ajax({
	// 	url:"{{ url('kelas/changekelas') }}",
	// ...
}

function filter(url) {
	if(url == null)
	    url = "{{ url('mahasiswa/search') }}";
	
	$.ajax({
		type: "GET",
		url: url,
		data: {
			ProgramID : $(".ProgramID").val(),
			ProdiID : $(".ProdiID").val(),
			TahunMasuk : $(".TahunMasuk").val(),
			SemesterMasuk : $(".SemesterMasuk").val(),
			StatusMhswID : $(".StatusMhswID").val(),
			JenjangID : $(".JenjangID").val(),
			KelasID : $(".KelasID").val(),
			keyword : $(".keyword").val(),
			statusPindahan: $(".statusPindahan").val(),
			orderby: $(".orderby").val(),
			descasc: $(".descasc").val(),
		},
		success: function(data) {
			$("#konten").html(data);
		}
	});
	return false;
}

$(document).ready(function() {
    filter();
});

function pdf(){
	var programID 		= $(".ProgramID").val();
	var prodiID 		= $(".ProdiID").val();
	var kelasID 		= $(".KelasID").val();
	var tahunMasuk 		= $(".TahunMasuk").val();
	var SemesterMasuk 	= $(".SemesterMasuk").val();
	var statusMhswID 	= $(".StatusMhswID").val();
	var statusPindahan 	= $(".statusPindahan").val();
	var keyword = $(".keyword").val();
	var orderby = $(".orderby").val();
	var descasc = $(".descasc").val();
	
	if (prodiID == '') {
		toastr.info("Maaf, anda harus memilih program studi untuk cetak ke PDF, Karena PDF Tidak dapat memuat data yang banyak.");
	} else {
		var link			= 'programID=' + programID
							+ '&prodiID=' + prodiID
							+ '&kelasID=' + kelasID
							+ '&statusMhswID=' + statusMhswID
							+ '&SemesterMasuk=' + SemesterMasuk
							+ '&tahunMasuk=' + tahunMasuk
							+ '&keyword=' + keyword
							+ '&descasc=' + descasc
							+ '&orderby=' + orderby
							+ '&statusPindahan=' + statusPindahan;
		
		window.open('{{ url("mahasiswa/pdf") }}/?' + link,"_Blank");
	}
}

function ktm_all(){
	var programID 		= $(".ProgramID").val();
	var prodiID 		= $(".ProdiID").val();
	var kelasID 		= $(".KelasID").val();
	var tahunMasuk 		= $(".TahunMasuk").val();
	var SemesterMasuk 	= $(".SemesterMasuk").val();
	var statusMhswID 	= $(".StatusMhswID").val();
	var statusPindahan 	= $(".statusPindahan").val();
	var keyword = $(".keyword").val();
	var orderby = $(".orderby").val();
	var descasc = $(".descasc").val();
	
	if (programID == '' || prodiID == '' || tahunMasuk == '') {
		toastr.info("Maaf, anda harus memilih program kuliah, program studi dan tahun masuk untuk cetak ke PDF, Karena PDF Tidak dapat memuat data yang banyak.");
	} else {
		var link			= 'programID=' + programID
							+ '&SemesterMasuk=' + SemesterMasuk
							+ '&statusPindahan=' + statusPindahan
							+ '&StatusMhswID=' + statusMhswID
							+ '&prodiID=' + prodiID
							+ '&tahunMasuk=' + tahunMasuk;
		
		window.open('{{ url("mahasiswa/pdf_ktm") }}/?' + link,"_Blank");
	}
}
	
function excel()
{
	var programID 		= $(".ProgramID").val();
	var prodiID 		= $(".ProdiID").val();
	var kelasID 		= $(".KelasID").val();
	var tahunMasuk 		= $(".TahunMasuk").val();
	var SemesterMasuk 	= $(".SemesterMasuk").val();
	var statusMhswID 	= $(".StatusMhswID").val();
	var statusPindahan 	= $(".statusPindahan").val();
	var keyword = $(".keyword").val();
	var orderby = $(".orderby").val();
	var descasc = $(".descasc").val();

	var link			= 'programID=' + programID
						+ '&prodiID=' + prodiID
						+ '&kelasID=' + kelasID
						+ '&statusMhswID=' + statusMhswID
						+ '&tahunMasuk=' + tahunMasuk
						+ '&SemesterMasuk=' + SemesterMasuk
						+ '&keyword=' + keyword
						+ '&descasc=' + descasc
						+ '&orderby=' + orderby
						+ '&statusPindahan=' + statusPindahan;
	
	window.open('{{ url("mahasiswa/excel") }}/?' + link,"_Blank");
}

function checkall(chkAll,checkid) {
	if (checkid != null) 
	{
		if (checkid.length == null) checkid.checked = chkAll.checked;
        else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;
		
		$("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
		$("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
    }
}

var TahunMasuk = @json($TahunMasuk);
var select_tahunmasuk = '';
select_tahunmasuk += '<select data-gen_post="true" class="form-control" id="TahunMasuk" required name="TahunMasuk">';
$.each(TahunMasuk, function(i, v) {
    select_tahunmasuk += '<option value="' + v.TahunMasuk + '">' + v.TahunMasuk + '</option>';
});
select_tahunmasuk += '</select>';

var Prodi = @json($Prodi);
var select_prodi = '';
select_prodi += '<select data-gen_post="true" class="form-control" id="Prodi" required name="Prodi">';
$.each(Prodi, function(i, v) {
    select_prodi += '<option value="' + v.ID + '">' + v.jenjang+ ' ' + v.Nama + '</option>';
});
select_prodi += '</select>';

function generate_data() {
    swal({
        title: 'Pilihan Pengambilan data?',
        type: 'warning',
        html:
                '<div class="form-group col-md-12">'+
                    '<label class="col-form-label" for="ProgramID">Tahun Masuk</h5></label>'+
                    '<div class="controls">'+
                        select_tahunmasuk +
                    '</div>'+
                '</div>'+
                '<div class="form-group col-md-12">'+
                        '<label class="col-form-label" for="ProgramID">Programstudi</h5></label>'+
                        '<div class="controls">'+
                            select_prodi +
                        '</div>'+
                    '</div>'+
            '</div>',
        preConfirm: function(result) {
            return new Promise(function(resolve, reject) {
                if (result) {
                    resolve($('[data-gen_post="true"]').serialize());
                } else {
                    reject('You need to select something!');
                }
            });
        },
        showCancelButton: true
    }).then(function(result) {
        if(result) {
            process_generate(result);
        }
    });
}

function process_generate(post_form){
	$.ajax({
		type: "POST",
		url: "{{ url('mahasiswa/generate') }}",
		data: post_form + '&_token={{ csrf_token() }}', 
		dataType: 'json',
		beforeSend: function(r) {
			$(".loading").show();
		},
		success:function(data){
			var type = data.status; 
			var alert = data.message; 
			
			$(".loading").hide();
			swal({
				title: "Informasi !",
				text: alert,
				type: type
				}).then(function(){
			});
		},
		error: function(data){
			$(".loading").hide();
			toastr.error("Terjadi kesalahan dalam proses generate data. Jika berlanjut hubungi Administrator.");
		}
	});
	return false;
}
</script>
@endpush
