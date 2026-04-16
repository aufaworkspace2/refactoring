@extends('layouts.template1')
@section('content')
<div class="card">
	<div class="card-body">
		<div class="form-row">
			<div class="col-md-12 mb-2">
				<button type="button" class="btn btn-success" onclick="excel()"><i class="fa fa-file-excel mr-1"></i>Excel</button>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">Tahun Masuk</h5></label>
				<select class="TahunMasuk form-control" id="TahunMasuk">
					<option value="">-- Lihat Semua --</option>
					@foreach(DB::table('mahasiswa')->select('TahunMasuk')->distinct()->orderBy('TahunMasuk', 'DESC')->get() as $row)
						@if($row->TahunMasuk)
							<option value="{{ $row->TahunMasuk }}">{{ $row->TahunMasuk }}</option>
						@endif
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">Tahun Akademik</h5></label>
				<select class="TahunID form-control" id="TahunID">
					<option value="">-- Lihat Semua --</option>
					@foreach(get_all('tahun') as $row)
						<option value="{{ $row->ID }}">{{ $row->Nama }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">Program</h5></label>
				<select class="ProgramID form-control" id="ProgramID">
					<option value="">-- Lihat Semua --</option>
					@foreach(get_all('program') as $row)
						<option value="{{ $row->ID }}">{{ $row->Nama }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">Prodi</h5></label>
				<select class="ProdiID form-control" id="ProdiID">
					<option value="">-- Lihat Semua --</option>
					@foreach(get_all('programstudi') as $row)
						<option value="{{ $row->ID }}">{{ get_field($row->JenjangID, 'jenjang') }} - {{ $row->Nama }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">Kelas</h5></label>
				<select class="KelasID form-control" id="KelasID">
					<option value="">-- Lihat Semua --</option>
					@foreach(get_all('kelas') as $row)
						<option value="{{ $row->ID }}">{{ $row->Nama }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">Semester Masuk</h5></label>
				<select class="SemesterMasuk form-control" id="SemesterMasuk">
					<option value="">-- Lihat Semua --</option>
					<option value="1">Ganjil</option>
					<option value="2">Genap</option>
				</select>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">Semester</h5></label>
				<input type="number" class="Semester form-control" id="Semester" placeholder="Semester">
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">Pencarian</h5></label>
				<input type="text" class="keyword form-control" id="keyword" placeholder="NIM / Nama Mahasiswa .." />
			</div>
			<div class="form-group col-md-12 align-self-end">
				<button type="button" onclick="filter()" class="btn btn-bordered-primary waves-effect waves-light btn-block"><i class="fa fa-search mr-1"></i> Cari</button>
			</div>
		</div>
	</div>
</div>
<div class="card">
	<div class="card-body">
		<div id="konten">
			<center>
				<h3> --- SILAHKAN PILIH FILTER DAN KLIK TOMBOL "CARI" UNTUK MENAMPILKAN DATA ---</h3>
			</center>
		</div>
	</div>
</div>

@push('scripts')
<script type="text/javascript">
function filter() {
	$.ajax({
		type: "POST",
		url: "{{ url('rekapnilai/search') }}",
		data: {
			_token: "{{ csrf_token() }}",
			TahunMasuk : $("#TahunMasuk").val(),
			TahunID : $("#TahunID").val(),
			ProgramID : $("#ProgramID").val(),
			ProdiID : $("#ProdiID").val(),
			KelasID : $("#KelasID").val(),
			SemesterMasuk : $("#SemesterMasuk").val(),
			Semester : $("#Semester").val(),
			keyword : $("#keyword").val(),
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
	var params = $.param({
		TahunMasuk : $("#TahunMasuk").val(),
		TahunID : $("#TahunID").val(),
		ProgramID : $("#ProgramID").val(),
		ProdiID : $("#ProdiID").val(),
		KelasID : $("#KelasID").val(),
		SemesterMasuk : $("#SemesterMasuk").val(),
		Semester : $("#Semester").val(),
		keyword : $("#keyword").val(),
	});
	window.open("{{ url('rekapnilai/excel') }}?" + params, "_Blank");
}
</script>
@endpush
@endsection
