@extends('layouts.template1')

@section('content')
@php
if(empty($row)) { $row = new stdClass(); $row->id = ''; $row->judul = ''; $row->deskripsi = ''; $row->link = ''; $row->image = ''; $row->status = 0; $btn = __('app.add'); } else { $row = (object) $row; $btn = __('app.edit'); }
@endphp

<div class="card">
	<div class="card-body">
	<form id="f_banner" onsubmit="savedata(this); return false;" action="{{ url('banner_pmb/save/' . ($save ?? 1)) }}" enctype="multipart/form-data">
		<input type="hidden" name="id" id="id" value="{{ $row->id ?? '' }}">
			<h3>Banner PMB</h3>
			<div class="form-row mt-3">
				<div class="form-group col-md-12">
					<label class="col-form-label">Judul *</label>
					<input type="text" id="judul" required name="judul" class="form-control" value="{{ $row->judul ?? '' }}" />
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label">Deskripsi *</label>
					<textarea name="deskripsi" id="deskripsi" class="form-control" rows="5">{{ $row->deskripsi ?? '' }}</textarea>
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label">Link *</label>
					<input type="text" id="link" required name="link" class="form-control" value="{{ $row->link ?? '' }}" />
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label">Gambar *</label>
					<input type="file" id="gambar" name="gambar" class="form-control" accept="image/*" />
					<input type="hidden" id="foto" name="foto" value="{{ $row->image ?? '' }}" />
					<p>File Sebelumnya : {{ ($row->image != "") ? "<a href='".asset('pmb/banner/'.$row->image)."' target='_blank'>".$row->image."</a>" : "(Tidak Ada)" }}</p>
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label">Status</label>
					<input type="checkbox" id="status" name="status" value="1" {{ ($row->status ?? 0) == 1 ? 'checked' : '' }} />
					<label for="status">Aktif</label>
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
function savedata(formz){ var formData = new FormData(formz); $.ajax({ type:'POST', url: $(formz).attr('action'), data:formData, cache:false, contentType: false, processData: false, beforeSend: function(r){ silahkantunggu(); }, success:function(data){ if(data == 'gagal'){ alertfail(); berhasil(); }else{ if({{ $save ?? 1 }} == '1'){ window.location="{{ url('banner_pmb') }}"; } if({{ $save ?? 1 }} == '2'){ window.location.href = "{{ url('banner_pmb/view') }}/{{ $row->id ?? '' }}"; } berhasil(); alertsuccess(); } }, error: function(data){ $(".btnSave").html("{{ __('app.save') }} Data"); $(".btnSave").removeAttr("disabled"); alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.'); } }); }
function btnEdit(type,checkid){ $("input:text, input:file, .num, input:radio, select, textarea, button:submit").attr('disabled',true); $(".btnSave").css('display','none'); if (checkid == 1){ $("input:text, input:file, .num, input:radio, select, textarea, button:submit").removeAttr('disabled'); $(".btnEdit").fadeOut(0); $(".btnSave").fadeIn(0); } }
btnEdit({{ $save ?? 1 }});
</script>
@endpush
