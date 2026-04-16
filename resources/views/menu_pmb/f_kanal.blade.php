@extends('layouts.template1')

@section('content')
@php
if(empty($row)) { $row = new stdClass(); $row->id = ''; $row->namamenu = ''; $row->link = ''; $row->icon = ''; $row->url = ''; $row->status = 0; $row->megamenu = 0; $btn = __('app.add'); } else { $row = (object) $row; $btn = __('app.edit'); }
@endphp

<div class="card">
	<div class="card-body">
	<form id="f_kanal" onsubmit="savedata(this); return false;" action="{{ url('menu_pmb/save/' . ($save ?? 1)) }}" enctype="multipart/form-data">
		<input type="hidden" name="id" id="id" value="{{ $row->id ?? '' }}">
			<h3>Menu PMB</h3>
			<div class="form-row mt-3">
				<div class="form-group col-md-12">
					<label class="col-form-label">Nama Menu *</label>
					<input type="text" id="namamenu" required name="namamenu" class="form-control" value="{{ $row->namamenu ?? '' }}" />
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label">Link *</label>
					<input type="text" id="link" required name="link" class="form-control" value="{{ $row->link ?? '' }}" />
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label">Icon</label>
					<input type="text" id="icon" name="icon" class="form-control" value="{{ $row->icon ?? '' }}" placeholder="Contoh: fa fa-home" />
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label">URL</label>
					<input type="text" id="url" name="url" class="form-control" value="{{ $row->url ?? '' }}" />
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label">Status</label>
					<input type="checkbox" id="status" name="status" value="1" {{ ($row->status ?? 0) == 1 ? 'checked' : '' }} />
					<label for="status">Aktif</label>
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label">Mega Menu</label>
					<input type="checkbox" id="megamenu" name="megamenu" value="1" {{ ($row->megamenu ?? 0) == 1 ? 'checked' : '' }} />
					<label for="megamenu">Aktif</label>
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
function savedata(formz){ var formData = new FormData(formz); $.ajax({ type:'POST', url: $(formz).attr('action'), data:formData, cache:false, contentType: false, processData: false, beforeSend: function(r){ silahkantunggu(); }, success:function(data){ if(data == 'gagal'){ alertfail(); berhasil(); }else{ if({{ $save ?? 1 }} == '1'){ window.location="{{ url('menu_pmb') }}"; } if({{ $save ?? 1 }} == '2'){ window.location.href = "{{ url('menu_pmb/view') }}/{{ $row->id ?? '' }}"; } berhasil(); alertsuccess(); } }, error: function(data){ $(".btnSave").html("{{ __('app.save') }} Data"); $(".btnSave").removeAttr("disabled"); alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.'); } }); }
function btnEdit(type,checkid){ $("input:text, input:file, .num, input:radio, select, textarea, button:submit").attr('disabled',true); $(".btnSave").css('display','none'); if (checkid == 1){ $("input:text, input:file, .num, input:radio, select, textarea, button:submit").removeAttr('disabled'); $(".btnEdit").fadeOut(0); $(".btnSave").fadeIn(0); } }
btnEdit({{ $save ?? 1 }});
</script>
@endpush
