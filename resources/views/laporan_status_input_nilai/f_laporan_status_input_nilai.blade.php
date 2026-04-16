@extends('layouts.template1')
@section('content')
<div class="card">
	<div class="card-body">
		<form id="formSave">
			<input type="hidden" name="_token" value="{{ csrf_token() }}">
			<input type="hidden" name="ID" id="ID" value="{{ $row->ID ?? '' }}">

			<div class="form-group row">
				<label class="col-sm-3 col-form-label">Nama Jabatan</label>
				<div class="col-sm-9">
					<input type="text" name="Nama" id="Nama" class="form-control" value="{{ $row->Nama ?? '' }}" required>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-3 col-form-label">Singkatan</label>
				<div class="col-sm-9">
					<input type="text" name="singkatan" id="singkatan" class="form-control" value="{{ $row->singkatan ?? '' }}" required>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-3 col-form-label">Kode Dikti</label>
				<div class="col-sm-9">
					<input type="text" name="KodeDikti" id="KodeDikti" class="form-control" value="{{ $row->KodeDikti ?? '' }}" required>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-3 col-form-label">Urut</label>
				<div class="col-sm-9">
					<input type="number" name="Urut" id="Urut" class="form-control" value="{{ $row->Urut ?? '' }}">
				</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-3 col-form-label">Tunjangan Fungsional Dos/Kar</label>
				<div class="col-sm-9">
					<input type="text" name="TunjanganFungsionalDosKar" id="TunjanganFungsionalDosKar" class="form-control" value="{{ $row->TunjanganFungsionalDosKar ?? '' }}">
				</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-3 col-form-label">Tunjangan Fungsional Dos Saja</label>
				<div class="col-sm-9">
					<input type="text" name="TunjanganFungsionalDosSaja" id="TunjanganFungsionalDosSaja" class="form-control" value="{{ $row->TunjanganFungsionalDosSaja ?? '' }}">
				</div>
			</div>

			<div class="form-group row">
				<div class="col-sm-9 offset-sm-3">
					<button type="button" onclick="saveData({{ $save }})" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
					<a href="{{ url('laporanstatusinputnilai') }}" class="btn btn-secondary">Batal</a>
				</div>
			</div>
		</form>
	</div>
</div>

@push('scripts')
<script type="text/javascript">
function saveData(saveType) {
	var formData = new FormData($('#formSave')[0]);

	$.ajax({
		type: "POST",
		url: "{{ url('laporanstatusinputnilai/save') }}/" + saveType,
		data: formData,
		contentType: false,
		processData: false,
		success: function(response) {
			if (response === 'gagal') {
				swal('Error', 'Data dengan Kode Dikti atau Singkatan yang sama sudah ada', 'error');
			} else {
				swal('Sukses', 'Data berhasil disimpan', 'success');
				window.location.href = "{{ url('laporanstatusinputnilai') }}";
			}
		},
		error: function() {
			swal('Error', 'Terjadi kesalahan saat menyimpan data', 'error');
		}
	});
}
</script>
@endpush
@endsection
