@extends('layouts.template1')
@section('content')
<div class="card">
	<div class="card-body">
		<div class="form-row">
			<div class="col-md-12">
				<button type="button" class="btn btn-success" onclick="excel()"><i class="fa fa-file-excel mr-1"></i>Excel</button>
			</div>
			<div class="form-group col-md-4">
				<label class="col-form-label"><h5 class="mb-0">Tahun Akademik</h5></label>
				<select class="TahunID form-control" id="TahunID">
					<option value="">-- Pilih --</option>
					@foreach(get_all('tahun') as $row)
						<option value="{{ $row->ID }}">{{ $row->Nama }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-4">
				<label class="col-form-label"><h5 class="mb-0">Program</h5></label>
				<select class="ProgramID form-control" id="ProgramID">
					<option value="">-- Lihat Semua --</option>
					@foreach(get_all('program') as $row)
						<option value="{{ $row->ID }}">{{ $row->Nama }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-4">
				<label class="col-form-label"><h5 class="mb-0">Prodi</h5></label>
				<select class="ProdiID form-control" id="ProdiID">
					<option value="">-- Lihat Semua --</option>
					@foreach(get_all('programstudi') as $row)
						<option value="{{ $row->ID }}">{{ get_field($row->JenjangID, 'jenjang') }} - {{ $row->Nama }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-2">
				<label class="col-form-label"><h5 class="mb-0">Status</h5></label>
				<select class="form-control Status" id="Status">
					<option value="">-- Lihat Semua --</option>
					<option value="1">Sudah</option>
					<option value="2">Belum</option>
				</select>
			</div>
			<div class="form-group col-md-8">
				<label class="col-form-label"><h5 class="mb-0">Cari Berdasarkan Dosen</h5></label>
				<select class="DosenID form-control select2" id="DosenID">
					<option value=""> -- Pilih -- </option>
					@php
						$listDosen = DB::table('dosen')
							->select('dosen.ID', 'dosen.NIDN', 'dosen.Nama', 'dosen.Title', 'dosen.Gelar')
							->join('jadwal', function($join) {
								$join->on('jadwal.DosenID', '=', 'dosen.ID')
								     ->orWhereRaw('FIND_IN_SET(dosen.ID, jadwal.DosenAnggota)');
							})
							->groupBy('dosen.ID')
							->orderBy('dosen.Nama', 'ASC')
							->whereNotNull('dosen.Nama')
							->where('dosen.Nama', '!=', '')
							->where('dosen.Nama', '!=', '-')
							->get();
					@endphp
					@foreach($listDosen as $row)
						<option value="{{ $row->ID }}">{{ $row->Title }} {{ $row->Nama }}, {{ $row->Gelar }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-2 align-self-end">
				<button type="button" onclick="filter()" class="btn btn-bordered-primary waves-effect waves-light btn-block"><i class="fa fa-search mr-1"></i> Cari</button>
			</div>
		</div>
	</div>
</div>
<div class="card">
	<div class="card-body">
		<div id="konten">
			<center>
				<h3> --- PILIH DOSEN LALU KLIK TOMBOL CARI ---</h3>
			</center>
		</div>
	</div>
</div>

@push('scripts')
<script type="text/javascript">
function filter(url) {
	if(url == null)
	url = "{{ url('laporan_status_input_nilai/search') }}";

	$.ajax({
		type: "POST",
		url: url,
		data: {
			_token: "{{ csrf_token() }}",
			DosenID : $("#DosenID").val(),
			TahunID : $("#TahunID").val(),
			ProgramID : $("#ProgramID").val(),
			ProdiID : $("#ProdiID").val(),
			Status : $("#Status").val(),
		},
		beforeSend: function(data) {
			$("#konten").html("<center><h3><i class='fa fa-spin fa-spinner'></i> Sedang Memuat Data.. </h3></center>");
		},
		success: function(data) {
			$("#konten").html(data);
		}
	});
	return false;
}

function excel(){
	var DosenID = $("#DosenID").val();
	var TahunID = $("#TahunID").val();
	var ProgramID = $("#ProgramID").val();
	var ProdiID = $("#ProdiID").val();
	var Status = $("#Status").val();

	var link = `?DosenID=${DosenID}&TahunID=${TahunID}&ProgramID=${ProgramID}&ProdiID=${ProdiID}&Status=${Status}`;
	window.open("{{ url('laporan_status_input_nilai/excel') }}" + link, "_Blank");
}
</script>
@endpush
@endsection
