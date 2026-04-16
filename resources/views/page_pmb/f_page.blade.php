@extends('layouts.template1')

@section('content')
@php
if(empty($row)) { $row = new stdClass(); $row->id = ''; $row->namamenu = ''; $row->isi = ''; $row->link = ''; $row->files = ''; $row->status = 0; $btn = __('app.add'); } else { $row = (object) $row; $btn = __('app.edit'); }
@endphp

<div class="card">
	<div class="card-body">
	<form id="f_page" onsubmit="savedata(this); return false;" action="{{ url('page_pmb/save/' . ($save ?? 1)) }}" enctype="multipart/form-data">
		<input type="hidden" name="id" id="id" value="{{ $row->id ?? '' }}">
			<h3>Page PMB</h3>
			<div class="form-row mt-3">
				<div class="form-group col-md-12">
					<label class="col-form-label">Nama Menu *</label>
					<input type="text" id="namamenu" required name="namamenu" class="form-control" value="{{ $row->namamenu ?? '' }}" />
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label">Isi *</label>
					<textarea name="isi" id="isi" class="form-control tinymce" rows="10">{{ $row->isi ?? '' }}</textarea>
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label">Link</label>
					<input type="text" id="link" name="link" class="form-control" value="{{ $row->link ?? '' }}" />
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label">Files</label>
					<input type="file" id="files" name="files" class="form-control" />
					<input type="hidden" id="file_old" name="file_old" value="{{ $row->files ?? '' }}" />
					<p>File Sebelumnya : {{ ($row->files != "") ? "<a href='".asset('pmb/page/'.$row->files)."' target='_blank'>".$row->files."</a>" : "(Tidak Ada)" }}</p>
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
function initTinyMCE() { if (typeof tinymce === 'undefined') { setTimeout(initTinyMCE, 100); return; } tinymce.EditorManager.editors = []; tinymce.init({ selector: 'textarea.tinymce', height: 300, plugins: 'image link code table', toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | image link code', file_picker_types: 'image', file_picker_callback: function(cb, value, meta) { var input = document.createElement('input'); input.setAttribute('type', 'file'); input.setAttribute('accept', 'image/*'); input.onchange = function() { var file = this.files[0]; var reader = new FileReader(); reader.onload = function() { var id = 'blobid' + (new Date()).getTime(); var blobCache = tinymce.activeEditor.editorUpload.blobCache; var base64 = reader.result.split(',')[1]; var blobInfo = blobCache.create(id, file, base64); blobCache.add(blobInfo); cb(blobInfo.blobUri(), { title: file.name }); }; reader.readAsDataURL(file); }; input.click(); } }); } }
$(document).ready(function() { initTinyMCE(); });
function savedata(formz){ if (typeof tinymce !== 'undefined') { tinymce.triggerSave(); } var formData = new FormData(formz); $.ajax({ type:'POST', url: $(formz).attr('action'), data:formData, cache:false, contentType: false, processData: false, beforeSend: function(r){ silahkantunggu(); }, success:function(data){ if(data == 'gagal'){ alertfail(); berhasil(); }else{ if({{ $save ?? 1 }} == '1'){ window.location="{{ url('page_pmb') }}"; } if({{ $save ?? 1 }} == '2'){ window.location.href = "{{ url('page_pmb/view') }}/{{ $row->id ?? '' }}"; } berhasil(); alertsuccess(); } }, error: function(data){ $(".btnSave").html("{{ __('app.save') }} Data"); $(".btnSave").removeAttr("disabled"); alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.'); } }); }
function btnEdit(type,checkid){ $("input:text, input:file, .num, input:radio, select, textarea, button:submit").attr('disabled',true); $(".btnSave").css('display','none'); if (checkid == 1){ $("input:text, input:file, .num, input:radio, select, textarea, button:submit").removeAttr('disabled'); $(".btnEdit").fadeOut(0); $(".btnSave").fadeIn(0); } }
btnEdit({{ $save ?? 1 }});
</script>
@endpush
