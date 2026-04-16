@extends('layouts.template1')

@section('content')

@php
if(empty($row)) {
	$row = new stdClass();
	$row->id = '';
	$row->kode = '';
	$row->nama = '';
	$row->jalur_pendaftaran = '';
	$row->tipe = '';
	$row->master_diskon_id_list = '';
	$row->file = '';
	$row->keterangan = '';
	$judul = __('app.title_add');
	$btn = __('app.add');
} else {
	$row = (object) $row;
	$judul = __('app.title_view');
	$btn = __('app.edit');
}
@endphp

<div class="card">
	<div class="card-body">
		<form id="f_syarat_pmb" onsubmit="savedata(this); return false;" action="{{ url('syarat_pmb/save/' . ($save ?? 1)) }}" enctype="multipart/form-data">
		<input type="hidden" name="id" id="id" value="{{ $row->id ?? '' }}">
			<h3>Persyaratan</h3>
				<div class="form-row mt-3">

					<div class="form-group col-md-12">
						<label class="col-form-label" for="kode">Kode *</label>
						<div class="controls">
							<input type="text" required id="kode" name="kode" class="form-control" value="{{ $row->kode ?? '' }}" />
							<small>* Tidak Boleh Menggunakan Kode yang sudah digunakan</small>
						</div>
					</div>

					<div class="form-group col-md-12">
						<label class="col-form-label" for="nama">Nama *</label>
						<div class="controls">
							<input type="text" required id="nama" name="nama" class="form-control" value="{{ $row->nama ?? '' }}" />
							<small>* Tidak Boleh Menggunakan Nama yang sudah digunakan</small>
						</div>
					</div>

					<div class="form-group col-md-12">
						<label class="col-form-label" for="jalur_pendaftaran">Jalur Pendaftaran *</label>
						<div class="controls">
							<select id="jalur_pendaftaran" name="jalur_pendaftaran[]" class="span5" multiple required="">
								<option value=''>Pilih</option>
									@php
										if(isset($row->jalur_pendaftaran) && $row->jalur_pendaftaran) {
											$jalur = explode(",", $row->jalur_pendaftaran);
										} else {
											$jalur = array();
										}
										$jalur_list = \DB::table('pmb_edu_jalur_pendaftaran')->get();
									@endphp
									@foreach($jalur_list as $raw)
										@php
											$s = (in_array($raw->id, $jalur))? 'selected' : '';
										@endphp
										<option value="{{ $raw->id }}" {{ $s }} >{{ $raw->nama }}</option>
									@endforeach
							</select>
						</div>
					</div>

					<div class="form-group col-md-12">
						<label class="col-form-label" for="file">File Referensi </label>
						<div class="controls">
							<input type="file" id="file" name="file" class="form-control mb-1" accept="image/png, image/jpeg, image/jpg, .pdf" />
							<input type="hidden" id="old_file" name="old_file" value="{{ $row->file ?? '' }}" />
							<small>File : {{ ($row->file != "") ? "<a href='".asset('pmb/file_referensi_syarat/'.$row->file)."' target='_blank'>".$row->file."</a>" : "(Tidak Ada)" }}</small>
						</div>
					</div>

					<div class="form-group col-md-12">
						<label class="col-form-label" for="keterangan">Keterangan File </label>
						<div class="controls">
							<input type="text" id="keterangan" name="keterangan" class="form-control" value="{{ $row->keterangan ?? '' }}" />
						</div>
					</div>

					<div class="form-group col-md-12">
						<label class="col-form-label" for="tipe">Tipe *</label>
						<div class="controls">
							<select id="tipe" name="tipe" class="form-control" required="" onchange="changetipe()" >
								<option value=''>Pilih</option>
								<option value='umum' {{ ($row->tipe ?? '') == 'umum' ? 'selected' : '' }}>Umum</option>
							</select>
						</div>
					</div>

					<div class="form-group col-md-12" id="div_diskon">
						<label class="col-form-label" for="master_diskon_id_list">Beasiswa / Diskon *</label>
						<div class="controls">
							<select id="master_diskon_id_list" name="master_diskon_id_list[]" class="span5" multiple>
									@php
										if(isset($row->master_diskon_id_list) && $row->master_diskon_id_list) {
											$master_diskon_id_list = explode(",", $row->master_diskon_id_list);
										} else {
											$master_diskon_id_list = array();
										}
										$master_diskon_list = \DB::table('master_diskon')
											->select('master_diskon.*', \DB::raw('if(master_diskon.ProdiID = 0, CONCAT("Semua Programstudi"), CONCAT(jenjang.Nama," || ",programstudi.Nama)) as prodi'))
											->leftJoin('programstudi', 'programstudi.ID', '=', 'master_diskon.ProdiID')
											->leftJoin('jenjang', 'jenjang.ID', '=', 'programstudi.JenjangID')
											->get();
									@endphp
									@foreach($master_diskon_list as $raw)
										@php
											if ($raw->Tipe == 'nominal') {
												$hrg = number_format($raw->Jumlah, 0, ',', '.');
											} else {
												$hrg = $raw->Jumlah . ' %';
											}
											$s = (in_array($raw->ID, $master_diskon_id_list))? 'selected' : '';
										@endphp
										<option value="{{ $raw->ID }}" {{ $s }} >{{ $raw->prodi }} -- {{ $raw->Nama }} {{ $hrg }}</option>
									@endforeach
							</select>
						</div>
					</div>

				</div>
				<button onClick="btnEdit({{ $save ?? 1 }},1)" type="button" class="btn btn-bordered-success waves-effect  width-md waves-light btnEdit">{{ $btn }} Data</button>
				<button type="submit" class="btn btn-bordered-primary waves-effect  width-md waves-light btnSave">{{ __('app.save') }} Data</button>
				<button type="button" onClick="back()" class="btn btn-bordered-danger waves-effect  width-md waves-light">{{ __('app.back') }}</button>

		</form>
	</div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

$(document).ready(function() {
	autocomplete('jalur_pendaftaran','','Pilih Jalur Pendaftaran');
});

function changetipe()
{
	var tipe = $('#tipe').val();

	if(tipe == 'beasiswa'){
		$('#div_diskon').show();
		autocomplete('master_diskon_id_list','','Pilih Beasiswa');
	}else{
		$('#div_diskon').hide();
	}
}
changetipe();

function savedata(formz){
	var formData = new FormData(formz);
	$.ajax({
		type:'POST',
		url: $(formz).attr('action'),
		data:formData,
		cache:false,
		contentType: false,
		processData: false,
		beforeSend: function(r){ silahkantunggu(); },
		success:function(data){
			if(data == 'gagal'){
				alertfail();
				berhasil();
			}else{
				if({{ $save ?? 1 }} == '1') {
					window.location="{{ url('syarat_pmb') }}";
				}
				if({{ $save ?? 1 }} == '2') {
					window.location.href = "{{ url('syarat_pmb/view') }}/{{ $row->id ?? '' }}";
				}
				berhasil();
				alertsuccess();
			}
		},
		error: function(data){
			$(".btnSave").html("{{ __('app.save') }} Data");
			$(".btnSave").removeAttr("disabled");
			alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.');
		}
	});
}

function btnEdit(type,checkid) {
	$("input:text, input:file, .num, input:radio, select, textarea, button:submit").attr('disabled',true);
	$(".btnSave").css('display','none');

	if (checkid == 1) {
		$("input:text, input:file, .num, input:radio, select, textarea, button:submit").removeAttr('disabled');
		$(".btnEdit").fadeOut(0);
		$(".btnSave").fadeIn(0);
	}
}
btnEdit({{ $save ?? 1 }});
</script>
@endpush
