@extends('layouts.template1')

@section('content')

@php
if(empty($row)) {
	$row = new stdClass();
	$row->kode = '';
	$row->sumber = 'dari_database';
	$row->table = '';
	$row->field = '';
	$row->relasi = '';
	$row->isi_hardcode = '';
	$row->digit = '';
	$judul = __('app.title_add');
	$btn = __('app.add');
} else {
	$row = (object) $row;
	$judul = __('app.title_view');
	$btn = __('app.edit');
}
$arr_no_required_hardcode = array('URUTREGIS','URUTPEND');
@endphp

<div class="card">
	<div class="card-body">
		<form id="f_master_format_nim_pmb" onsubmit="savedata(this); return false;" action="{{ url('master_format_nim_pmb/save/' . ($save ?? 1)) }}" enctype="multipart/form-data">
		<input type="hidden" name="id" id="id" value="{{ $row->kode ?? '' }}">
			<h3>Master Format</h3>
				<div class="form-row mt-3">
					<div class="form-group col-md-12">
						<label class="col-form-label" for="kode">Kode *</label>
						<div class="controls">
							<input type="text" required id="kode" name="kode" class="form-control" value="{{ $row->kode ?? '' }}" />
							<small>* Tidak Boleh Menggunakan Kode yang sudah digunakan</small>
						</div>
					</div>

					<div class="form-group col-md-12">
						<label class="col-form-label" for="sumber">Sumber *</label>
						<div class="controls">
							<select class="form-control" name="sumber" id="sumber" onchange="changesumber()">
								<option value="dari_database" {{ ($row->sumber ?? '') == 'dari_database' ? 'selected' : '' }} >Dari Database</option>
								<option value="hardcode" {{ ($row->sumber ?? '') == 'hardcode' ? 'selected' : '' }} >Hardcode</option>
							</select>
						</div>
					</div>

					<div class="dari_database col-md-12">
						<div class="form-group">
							<label class="col-form-label" for="table">Table *</label>
							<div class="controls">
								<input type="text" required id="table" name="table" class="form-control" value="{{ $row->table ?? '' }}" />
							</div>
						</div>

						<div class="form-group">
							<label class="col-form-label" for="field">Field *</label>
							<div class="controls">
								<input type="text" required id="field" name="field" class="form-control" value="{{ $row->field ?? '' }}" />
							</div>
						</div>

						<div class="form-group">
							<label class="col-form-label" for="relasi">Relasi *</label>
							<div class="controls">
								<input type="text" required id="relasi" name="relasi" class="form-control" value="{{ $row->relasi ?? '' }}" />
							</div>
						</div>
					</div>

					<div class="hardcode col-md-12">
						<div class="form-group">
							<label class="col-form-label" for="hardcode">Isian Hardcode @if(!in_array($row->kode ?? '',$arr_no_required_hardcode))*@endif</label>
							<div class="controls">
								<input type="text" @if(!in_array($row->kode ?? '',$arr_no_required_hardcode))required @endif id="isi_hardcode" name="isi_hardcode" class="form-control" value="{{ $row->isi_hardcode ?? '' }}" />
							</div>
						</div>
					</div>

					<div class="form-group col-md-12">
						<label class="col-form-label" for="digit">Digit *</label>
						<div class="controls">
							<input type="text" required id="digit" name="digit" class="form-control" value="{{ $row->digit ?? '' }}" />
							<small style="color:red">* jika digit diinput 0 maka format yang tergenerate sesuai dengan data tanpa penambahan angka 0 didepan.</small>
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

function changesumber() {
	var sumber = $('#sumber').val();

	if(sumber == 'dari_database'){
		$('.dari_database').show();
		$('.hardcode').hide();

		$("#table").prop('required',true);
		$("#field").prop('required',true);
		$("#relasi").prop('required',true);
		$("#isi_hardcode").prop('required',false);
	}else{
		$('.dari_database').hide();
		$('.hardcode').show();

		$("#table").prop('required',false);
		$("#field").prop('required',false);
		$("#relasi").prop('required',false);
		@if(!in_array($row->kode ?? '',$arr_no_required_hardcode))
		$("#isi_hardcode").prop('required',true);
		@endif
	}
}
changesumber();

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
					window.location="{{ url('master_format_nim_pmb') }}";
				}
				if({{ $save ?? 1 }} == '2') {
					window.location.href = "{{ url('master_format_nim_pmb/view') }}/{{ $row->kode ?? '' }}";
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
