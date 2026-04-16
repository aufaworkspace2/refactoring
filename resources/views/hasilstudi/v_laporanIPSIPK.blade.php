@extends('layouts.template1')
@section('content')
<div class="card">
	<div class="card-body">
		<div class="row">
			<div class="col-md-12">
				<div class="button-list">
					<div class="btn-group">
						<button type="button" class="btn btn-info dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false"><i class="mdi mdi-printer mr-1"></i> Cetak <i class="mdi mdi-chevron-down"></i></button>
						<div class="dropdown-menu">
							<a onclick="cetakData('pdf')" href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-printer"></i> PDF</a>
							<a onclick="cetakData('excel')" href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-printer"></i> Excel</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-4">
				<label class="col-form-label"><h5 class="m-0">Program Kuliah</h5></label>
				<select name='ProgramID' required class="form-control" id="ProgramID">
					<option value="">-- Lihat Semua --</option>
					@foreach(get_all('program') as $row)
						<option value="{{ $row->ID }}">{{ $row->Nama }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-4">
				<label class="col-form-label"><h5 class="m-0">Tahun Semester</h5></label>
				<select name='TahunID' required class="form-control" id="TahunID">
					@foreach(get_all('tahun', 'TahunID', 'DESC') as $r)
						<option {{ ($r->ProsesBuka == '1') ? 'selected' : '' }} value='{{ $r->ID }}'>{{ $r->Nama }} {{ ($r->ProsesBuka == '1') ? '(Aktif)' : '' }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-4">
				<label class="col-form-label"><h5 class="m-0">Program Studi</h5></label>
				<select name='ProdiID' required class="form-control" id="ProdiID">
					 @foreach(get_all('programstudi') as $row)
                        <option value="{{ $row->ID }}">{{ $row->ProdiID }} || {{ get_field($row->JenjangID, 'jenjang') }} || {{ $row->Nama }}</option>
                    @endforeach
				</select>
			</div>
			<div class="form-group col-md-4">
				<label class="col-form-label"><h5 class="m-0">Tahun Masuk</h5></label>
				<select id="TahunMasuk" class="TahunMasuk form-control" style="width: 100%">
					@foreach($tahunMasuk as $row)
						<option value="{{ $row->TahunMasuk ?? '' }}">{{ $row->TahunMasuk ?? '' }}</option>
					@endforeach
				</select>
            </div>
			<!-- Search & Filter  -->
			<div class="form-group col-md-2">
				<label class="col-form-label"><h5 class="m-0">Dengan</h5></label>
				<select name='Jenis' id="Jenis" required class="Jenis form-control">
					<option value="IPK">IPK</option>
					<option value="IPS">IPS</option>
				</select>
			</div>
			<div class="form-group col-md-2">
				<label class="col-form-label"><h5 class="m-0">Dari</h5></label>
				<input type="number" id="Dari" class="form-control Dari" />
			</div>
			<div class="form-group col-md-2">
				<label class="col-form-label"><h5 class="m-0">Sampai</h5></label>
				<input type="number" id="Sampai" class="form-control Sampai" />
			</div>
			<div class="form-group col-md-2 align-self-end">
				<a onclick="setFilter()" class="btn btn-bordered-success waves-effect waves-light">
					<i class="fa fa-search"></i> Filter
				</a>
			</div>
		</div>
	</div>
</div>
<div class="card">
	<div class="card-body">
		<div id="loadHasil">
			<center>
				<h4>Silahkan Pilih Filter dan Klik Tombol "Filter" Untuk Menampilkan Data</h4>
			</center>
		</div>
	</div>
</div>

@push('scripts')
<script type="text/javascript">
function setFilter() {
	$.ajax({
		type: "POST",
		url: "{{ url('hasilstudi/filterIpk') }}",
		data: {
            _token: "{{ csrf_token() }}",
			jenis: $("#Jenis").val(),
			dari: $("#Dari").val(),
			tahunID: $('#TahunID').val(),
			prodiID: $('#ProdiID').val(),
			programID: $('#ProgramID').val(),
			sampai: $("#Sampai").val(),
			tahunMasuk: $("#TahunMasuk").val()
		},
		beforeSend: function() {
			$("#loadHasil").html('<center><i class="fa fa-spinner fa-spin"></i> Sedang memuat data ...</center>');
		},
		success: function(data) {
			$("#loadHasil").html(data);
		}
	});
	return false;
}

function cetakData(tipe)
{
	var jenis 		= $("#Jenis").val();
	var dari 		= $("#Dari").val();
	var tahunID 	= $('#TahunID').val();
	var prodiID 	= $('#ProdiID').val();
	var programID 	= $('#ProgramID').val();
	var sampai 		= $("#Sampai").val();
	var tahunMasuk 	= $("#TahunMasuk").val();
	
	var param		= 'jenis=' + jenis +
					'&dari=' + dari + 
					'&tahunID=' + tahunID + 
					'&prodiID=' + prodiID + 
					'&programID=' + programID + 
					'&sampai=' + sampai + 
					'&type=' + tipe + 
					'&tahunMasuk=' + tahunMasuk;
					
	window.open("{{ url('hasilstudi/cetak') }}?" + param, "_Blank");
}
</script>
@endpush
@endsection
