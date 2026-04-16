@extends('layouts.template1')

@section('content')
<div class="card">
	<div class="card-body">
		<div class="row">
			<div class="col-md-12">
				<div class="button-list">
					@if($Create == 'YA')
						<a href="{{ url('jalur_pendaftaran_pmb/add') }}" class="btn btn-bordered-primary waves-effect  width-md waves-light"><i class="mdi mdi-plus"></i> {{ __('app.add') }} Data</a>
					@endif
					<div class="btn-group">
						<button type="button" class="btn btn-info dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false"><i class="mdi mdi-printer mr-1"></i> Cetak <i class="mdi mdi-chevron-down"></i></button>
						<div class="dropdown-menu">
							<a onclick="pdf()" href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-printer"></i> {{ __('app.pdf') }}</a>
							<a onclick="excel()" href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-printer"></i> {{ __('app.excel') }}</a>
						</div>
					</div>
					<button class="btn btn-bordered-danger waves-effect  width-md waves-light" id="btnDelete" data-placement="top" title="Silahkan pilih data terlebih dahulu." data-toggle="modal" disabled><i class="mdi mdi-delete"></i> {{ __('app.delete') }}</button>
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
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

function filter(url) {
	if(url == null) url = "{{ url('jalur_pendaftaran_pmb/search') }}";
	$.ajax({ type: "POST", url: url, data: { keyword : $(".keyword").val() }, success: function(data) { $("#konten").html(data); } });
	return false;
}
function pdf(){ window.open("{{ url('jalur_pendaftaran_pmb/pdf') }}/?keyword="+$(".keyword").val(),"_Blank"); }
function excel(){ window.open("{{ url('jalur_pendaftaran_pmb/excel') }}/?keyword="+$(".keyword").val(),"_Blank"); }
function checkall(chkAll,checkid) {
	if (checkid != null) {
		if (checkid.length == null) checkid.checked = chkAll.checked;
		else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;
		$("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
		$("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
	}
}
filter();
</script>
@endpush
