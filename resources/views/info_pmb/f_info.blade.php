@extends('layouts.template1')

@section('content')
@php
if(empty($row)) { 
	$row = (object)['id' => '', 'email' => '', 'telepon' => '', 'fax' => '', 'youtube' => '', 'twitter' => '', 'facebook' => '', 'instagram' => '', 'password' => '', 'whatsapp' => '', 'logo' => '']; 
} else { 
	$row = (object) $row; 
}
@endphp

<div class="card">
	<div class="card-body">
	<form id="f_info" onsubmit="savedata(this); return false;" action="{{ url('info_pmb/save') }}" enctype="multipart/form-data">
			<h3>Info PMB</h3>
			<div class="form-row mt-3">
				<div class="form-group col-md-6">
					<label class="col-form-label">Email *</label>
					<input type="email" id="email" required name="email" class="form-control" value="{{ $row->email ?? '' }}" />
				</div>
				<div class="form-group col-md-6">
					<label class="col-form-label">Telepon *</label>
					<input type="text" id="telepon" required name="telepon" class="form-control" value="{{ $row->telepon ?? '' }}" />
				</div>
				<div class="form-group col-md-6">
					<label class="col-form-label">Fax</label>
					<input type="text" id="fax" name="fax" class="form-control" value="{{ $row->fax ?? '' }}" />
				</div>
				<div class="form-group col-md-6">
					<label class="col-form-label">WhatsApp *</label>
					<input type="text" id="whatsapp" required name="whatsapp" class="form-control" value="{{ $row->whatsapp ?? '' }}" />
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label">Logo *</label>
					<input type="file" id="logo" name="logo" class="form-control" accept="image/*" />
					<input type="hidden" id="foto" name="foto" value="{{ $row->logo ?? '' }}" />
					<p>File Sebelumnya : {{ ($row->logo != "") ? "<a href='".asset('pmb/logo/'.$row->logo)."' target='_blank'>".$row->logo."</a>" : "(Tidak Ada)" }}</p>
				</div>
				<div class="form-group col-md-4">
					<label class="col-form-label">YouTube</label>
					<input type="text" id="youtube" name="youtube" class="form-control" value="{{ $row->youtube ?? '' }}" />
				</div>
				<div class="form-group col-md-4">
					<label class="col-form-label">Twitter</label>
					<input type="text" id="twitter" name="twitter" class="form-control" value="{{ $row->twitter ?? '' }}" />
				</div>
				<div class="form-group col-md-4">
					<label class="col-form-label">Facebook</label>
					<input type="text" id="facebook" name="facebook" class="form-control" value="{{ $row->facebook ?? '' }}" />
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label">Instagram</label>
					<input type="text" id="instagram" name="instagram" class="form-control" value="{{ $row->instagram ?? '' }}" />
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label">Password</label>
					<input type="password" id="password" name="password" class="form-control" value="{{ $row->password ?? '' }}" />
				</div>
			</div>
			<button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">{{ __('app.save') }} Data</button>
		</form>
	</div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
function savedata(formz){ var formData = new FormData(formz); $.ajax({ type:'POST', url: $(formz).attr('action'), data:formData, cache:false, contentType: false, processData: false, beforeSend: function(r){ silahkantunggu(); }, success:function(data){ berhasil(); alertsuccess(); }, error: function(data){ $(".btnSave").html("{{ __('app.save') }} Data"); $(".btnSave").removeAttr("disabled"); alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.'); } }); }
</script>
@endpush
