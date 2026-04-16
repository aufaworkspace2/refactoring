@extends('layouts.template1')

@section('content')
<div class="card">
	<div class="card-body">
		<div class="row">
			<div class="col-md-12">
				<div class="button-list">
					@if($Create == 'YA')
						<a href="{{ url('banner_pmb/add') }}" class="btn btn-bordered-primary waves-effect  width-md waves-light"><i class="mdi mdi-plus"></i> {{ __('app.add') }} Data</a>
					@endif
					<button class="btn btn-bordered-danger waves-effect  width-md waves-light" id="btnDelete" data-placement="top" title="Silahkan pilih data terlebih dahulu." data-toggle="modal"><i class="mdi mdi-delete"></i> {{ __('app.delete') }}</button>
				</div>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-12">
				<label class="col-form-label mt-2"><h4 class="m-0">{{ __('app.keyword_legend') }}</h4></label>
				<input type="text" class="form-control keyword" onkeyup="filter()" placeholder="{{ __('app.keyword') }} ..">
			</div>
		</div>
	</div>
</div>
<div class="card">
	<div class="card-body">
	<div id="konten"></div>
	</div>
</div>

<!-- Modal Delete -->
<div class="modal fade" id="hapus" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">{{ __('app.confirm_header') }}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				{{ __('app.confirm_message') }}
				<p class="data_name"></p>
			</div>
			<div class="modal-footer">
				<form id="f_delete_banner" action="{{ route('banner_pmb.delete') }}" method="POST">
					@csrf
					<button type="submit" class="btn btn-primary"><i class="mdi mdi-delete"></i> {{ __('app.delete') }}</button>
					<button type="button" class="btn btn-danger" data-dismiss="modal"><i class="mdi mdi-close"></i> {{ __('app.close') }}</button>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
function filter(url) { if(url == null) url = "{{ url('banner_pmb/search') }}"; $.ajax({ type: "POST", url: url, data: { keyword : $(".keyword").val() }, success: function(data) { $("#konten").html(data); } }); return false; }
function checkall(chkAll,checkid) { if (checkid != null) { if (checkid.length == null) checkid.checked = chkAll.checked; else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked; $("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger'); $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger'); } }
filter();

// Handle delete form submit
$(document).on('submit', '#f_delete_banner', function(e){
	e.preventDefault();
	e.stopImmediatePropagation();
	
	$.ajax({
		type: "POST",
		url: $(this).attr('action'),
		data: $(this).serialize(),
		success: function(data){
			$('#hapus').modal('hide');
			if(data.status == 1) {
				alertsuccess(data.message);
				setTimeout(function() { filter(); }, 500);
			} else {
				swal('Pemberitahuan', data.message, 'error');
			}
		},
		error: function(){
			swal('Pemberitahuan', 'Maaf, data gagal diproses!.', 'error');
		}
	});
	return false;
});
</script>
@endpush
