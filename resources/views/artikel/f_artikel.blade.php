@extends('layouts.template1')

@section('content')

@php
if(empty($row)) {
	$row = new stdClass();
	$row->id = '';
	$row->judul = '';
	$row->isi = '';
	$row->status = '';
	$row->event_date = '';
	$row->gambar = '';
	$row->publish = '';
	$row->metatitle = '';
	$row->metakeywords = '';
	$row->metadescription = '';
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
	<form id="f_agenda" onsubmit="savedata(this); return false;" action="{{ url('artikel_pmb/save/' . ($save ?? 1)) }}" enctype="multipart/form-data">
		<input class="form-control" type="hidden" name="id" id="id" value="{{ $row->id ?? '' }}">
			<h3>Artikel PMB</h3>
			<div class="form-row mt-3">

				<div class="form-group col-md-12">
					<label class="col-form-label" for="Kode">Judul *</label>
					<div class="controls">
						<input type="text" id="judul" required name="judul" class="form-control" value="{{ $row->judul ?? '' }}" />
					</div>
				</div>

				<div class="form-group col-md-12">
					<label class="col-form-label" for="Nama">Isi *</label>
					<div class="controls">
						<textarea name="isi" id="isi" class="form-control tinymce">{{ $row->isi ?? '' }}</textarea>
					</div>
				</div>

				<div class="form-group col-md-12">
					<label class="col-form-label" for="Nama">Event Date *</label>
					<div class="controls">
						<input type="text" id="event_date" required name="event_date" class="form-control datepicker" value="{{ $row->event_date ? date('d/m/Y', strtotime($row->event_date)) : '' }}" autocomplete="off" />
					</div>
				</div>

				<div class="form-group col-md-12">
					<label class="col-form-label" for="Nama">Status *</label>
					<div class="controls">
						<select class="form-control" name="status">
							<option value="0" {{ ($row->status ?? 0) == '0' ? 'selected' : '' }} >Tidak Ada Keterangan</option>
							<option value="1" {{ ($row->status ?? 0) == '1' ? 'selected' : '' }}>Artikel</option>
							<option value="2" {{ ($row->status ?? 0) == '2' ? 'selected' : '' }}>Berita</option>
							<option value="3" {{ ($row->status ?? 0) == '3' ? 'selected' : '' }}>Pengumuman</option>
						</select>
					</div>
				</div>

				<div class="form-group col-md-12">
					<label class="col-form-label" for="Nama">Gambar *</label>
					<div class="controls">
						<input type="file" id="gambar" name="gambar" class="form-control" accept="image/*" />
						<input type="hidden" id="foto" name="foto" value="{{ $row->gambar ?? '' }}" />
						<p class="text-info">(Rekomendasi : 750 x 420 px)</p>
						<p>File Sebelumnya : {{ ($row->gambar != "") ? "<a href='".asset('pmb/artikel/'.$row->gambar)."' target='_blank'>".$row->gambar."</a>" : "(Tidak Ada)" }}</p>
					</div>
				</div>

				<div class="form-group col-md-12">
					<label class="col-form-label" for="Nama">Meta Title *</label>
					<div class="controls">
						<input type="text" id="metatitle" required name="metatitle" class="form-control" value="{{ $row->metatitle ?? '' }}" />
					</div>
				</div>

				<div class="form-group col-md-12">
					<label class="col-form-label" for="Nama">Meta Keywords *</label>
					<div class="controls">
						<input type="text" id="metakeywords" required name="metakeywords" class="form-control" value="{{ $row->metakeywords ?? '' }}" />
					</div>
				</div>

				<div class="form-group col-md-12">
					<label class="col-form-label" for="Nama">Meta Description *</label>
					<div class="controls">
						<textarea name="metadescription" id="metadescription" class="form-control" rows="8">{{ $row->metadescription ?? '' }}</textarea>
					</div>
				</div>

				<div class="form-group col-md-12">
					<label class="col-form-label" for="publish">Publish *</label>
					<div class="controls">
						<input type="checkbox" id="publish" name="publish" value="1" {{ ($row->publish ?? 0) == 1 ? 'checked' : '' }} />
						<label for="publish">Publish</label>
					</div>
				</div>

			</div>
			<button onClick="btnEdit({{ $save ?? 1 }},1)" type="button" class="btn btn-bordered-success waves-effect  width-md waves-light btnEdit">{{ $btn }} Data</button>
			<button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">{{ __('app.save') }} Data</button>
			<button type="button" onClick="back()" class="btn btn-bordered-danger waves-effect  width-md waves-light">{{ __('app.back') }}</button>

		</form>
	</div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

function initTinyMCE() {
    if (typeof tinymce === 'undefined') { setTimeout(initTinyMCE, 100); return; }
    tinymce.EditorManager.editors = [];
    tinymce.init({
        selector: 'textarea.tinymce',
        height: 300,
        plugins: 'image link code table',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | image link code',
        file_picker_types: 'image',
        file_picker_callback: function(cb, value, meta) {
            var input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.onchange = function() {
                var file = this.files[0];
                var reader = new FileReader();
                reader.onload = function() {
                    var id = 'blobid' + (new Date()).getTime();
                    var blobCache = tinymce.activeEditor.editorUpload.blobCache;
                    var base64 = reader.result.split(',')[1];
                    var blobInfo = blobCache.create(id, file, base64);
                    blobCache.add(blobInfo);
                    cb(blobInfo.blobUri(), { title: file.name });
                };
                reader.readAsDataURL(file);
            };
            input.click();
        }
    });
}
$(document).ready(function() { initTinyMCE(); });

function savedata(formz){
	if (typeof tinymce !== 'undefined') { tinymce.triggerSave(); }
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
					window.location="{{ url('artikel_pmb') }}";
				}
				if({{ $save ?? 1 }} == '2') {
					window.location.href = "{{ url('artikel_pmb/view') }}/{{ $row->id ?? '' }}";
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
