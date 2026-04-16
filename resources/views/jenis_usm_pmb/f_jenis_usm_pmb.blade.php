@extends('layouts.template1')

@section('content')
@php
if(empty($row)) { $row = new stdClass(); $row->id = ''; $row->kode = ''; $row->nama = ''; $row->jenis = ''; $btn = __('app.add'); } else { $row = (object) $row; $btn = __('app.edit'); }
@endphp

<div class="card">
	<div class="card-body">
	<form id="f_jenis_usm" onsubmit="savedata(this); return false;" action="{{ url('jenis_usm_pmb/save/' . ($save ?? 1)) }}" enctype="multipart/form-data">
		<input type="hidden" name="id" id="id" value="{{ $row->id ?? '' }}">
			<h3>Jenis USM PMB</h3>
			<div class="form-row mt-3">
				<div class="form-group col-md-12">
					<label class="col-form-label">Kode *</label>
					<input type="text" id="kode" required name="kode" class="form-control" value="{{ $row->kode ?? '' }}" />
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label">Nama *</label>
					<input type="text" id="nama" required name="nama" class="form-control" value="{{ $row->nama ?? '' }}" />
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label">Jenis *</label>
					<input type="text" id="jenis" required name="jenis" class="form-control" value="{{ $row->jenis ?? '' }}" />
				</div>
			</div>
			<button onClick="btnEdit({{ $save ?? 1 }},1)" type="button" class="btn btn-bordered-success waves-effect width-md waves-light btnEdit">{{ $btn }} Data</button>
			<button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">{{ __('app.save') }} Data</button>
			<button type="button" onClick="back()" class="btn btn-bordered-danger waves-effect width-md waves-light">{{ __('app.back') }}</button>
		</form>
	</div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
function savedata(formz){ var formData = new FormData(formz); $.ajax({ type:'POST', url: $(formz).attr('action'), data:formData, cache:false, contentType: false, processData: false, beforeSend: function(r){ silahkantunggu(); }, success:function(data){ if(data == 'gagal'){ alertfail(); berhasil(); }else{ if({{ $save ?? 1 }} == '1'){ window.location="{{ url('jenis_usm_pmb') }}"; } if({{ $save ?? 1 }} == '2'){ window.location.href = "{{ url('jenis_usm_pmb/view') }}/{{ $row->id ?? '' }}"; } berhasil(); alertsuccess(); } }, error: function(data){ $(".btnSave").html("{{ __('app.save') }} Data"); $(".btnSave").removeAttr("disabled"); alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.'); } }); }
function btnEdit(type,checkid){ $("input:text, input:file, .num, input:radio, select, textarea, button:submit").attr('disabled',true); $(".btnSave").css('display','none'); if (checkid == 1){ $("input:text, input:file, .num, input:radio, select, textarea, button:submit").removeAttr('disabled'); $(".btnEdit").fadeOut(0); $(".btnSave").fadeIn(0); } }
btnEdit({{ $save ?? 1 }});
</script>
@endpush
