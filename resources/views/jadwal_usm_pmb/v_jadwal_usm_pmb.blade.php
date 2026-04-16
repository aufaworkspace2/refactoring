@extends('layouts.template1')

@section('content')
<div class="card">
	<div class="card-body">
		<div class="row">
			<div class="col-md-12">
				<div class="button-list">
					@if($Create == 'YA')
						<a href="{{ url('jadwal_usm_pmb/add') }}" class="btn btn-bordered-primary waves-effect  width-md waves-light"><i class="mdi mdi-plus"></i> {{ __('app.add') }} Data</a>
					@endif
					<button class="btn btn-bordered-danger waves-effect  width-md waves-light" id="btnDelete" data-placement="top" title="Silahkan pilih data terlebih dahulu." data-toggle="modal"><i class="mdi mdi-delete"></i> {{ __('app.delete') }}</button>
					<div class="btn-group">
						<button type="button" class="btn btn-info dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false"><i class="mdi mdi-printer mr-1"></i> Cetak <i class="mdi mdi-chevron-down"></i></button>
						<div class="dropdown-menu">
							<a onclick="pdf()" href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-printer"></i> PDF</a>
							<a onclick="excel()" href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-printer"></i> Excel</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-4">
				<label class="col-form-label"><h5 class="m-0">Gelombang</h5></label>
				<select class="gelombang form-control" onchange="filter()">
					<option value="">-- {{ __('app.view_all') }} --</option>
				</select>
			</div>
			<div class="form-group col-md-4">
				<label class="col-form-label"><h5 class="m-0">Jenis USM</h5></label>
				<select class="jenis form-control" onchange="filter()">
					<option value="">-- {{ __('app.view_all') }} --</option>
				</select>
			</div>
			<div class="form-group col-md-4">
				<label class="col-form-label"><h5 class="m-0">{{ __('app.keyword_legend') }}</h5></label>
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

<div id="hapus" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="hapus" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="hapus">{{ __('app.confirm_header') }}</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			</div>
			<div class="modal-body">
				<p>{{ __('app.confirm_message') }}</p>
				<p class="data_name"></p>
			</div>
			<div class="modal-footer">
				<button type="button" onclick="hapusdata()" class="btn btn-danger waves-effect" >{{ __('app.delete') }}</button>
				<button type="button" class="btn btn-primary waves-effect waves-light" data-dismiss="modal">{{ __('app.close') }}</button>
			</div>
		</div>
	</div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
function filter(url) { if(url == null) url = "{{ url('jadwal_usm_pmb/search') }}"; $.ajax({ type: "POST", url: url, data: { gelombang : $(".gelombang").val(), jenis : $(".jenis").val(), keyword : $(".keyword").val() }, success: function(data) { $("#konten").html(data); } }); return false; }
function checkall(chkAll,checkid) { if (checkid != null) { if (checkid.length == null) checkid.checked = chkAll.checked; else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked; $("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger'); $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger'); } }
function pdf(){ window.open("{{ url('jadwal_usm_pmb/pdf') }}","_Blank"); }
function excel(){ window.open("{{ url('jadwal_usm_pmb/excel') }}","_Blank"); }
function hapusdata(){ $.ajax({ type: "POST", url: "{{ url('jadwal_usm_pmb/delete') }}", data: $("#f_delete_jadwal").serialize(), success: function(data){ var res = JSON.parse(data); $("#hapus").modal("hide"); setTimeout(function(){ $("body").removeClass("modal-open"); $(".modal-backdrop").remove(); }, 300); filter(); if(res.status==1){ $(".alert-success").show(); $(".alert-success-content").html(res.message); }else{ $(".alert-error").show(); $(".alert-error-content").html(res.message); } } }); }
filter();
</script>
@endpush
