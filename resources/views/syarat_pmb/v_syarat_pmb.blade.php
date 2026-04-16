@extends('layouts.template1')

@section('content')
<div class="card">
	<div class="card-body">
		<div class="row">
			<div class="col-md-12">
				<div class="button-list">
					@if($Create == 'YA')
						<a href="{{ url('syarat_pmb/add') }}" class="btn btn-bordered-primary waves-effect  width-md waves-light"><i class="mdi mdi-plus"></i> {{ __('app.add') }} Data</a>
					@endif
					<button class="btn btn-bordered-danger waves-effect  width-md waves-light" id="btnDelete" data-placement="top" title="Silahkan pilih data terlebih dahulu." data-toggle="modal"><i class="mdi mdi-delete"></i> {{ __('app.delete') }}</button>
				</div>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-4">
				<label class="col-form-label mt-2"><h4 class="m-0">Tipe</h4></label>
				<select id="tipe" class="tipe form-control" onchange="filter()">
					<option value="">-- {{ __('app.view_all') }} --</option>
					<option value="umum" selected>Umum</option>
				</select>
			</div>
			<div class="form-group col-md-8">
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
	if(url == null) url = "{{ url('syarat_pmb/search') }}";
	$.ajax({
		type: "POST",
		url: url,
		data: {
			keyword : $(".keyword").val(),
			tipe : $(".tipe").val(),
		},
		success: function(data) {
			$("#konten").html(data);
		}
	});
	return false;
}

function pdf(){
	window.open("{{ url('syarat_pmb/pdf') }}/?keyword="+$(".keyword").val()+"&tipe="+$(".tipe").val(),"_Blank");
}

function excel(){
	window.open("{{ url('syarat_pmb/excel') }}/?keyword="+$(".keyword").val()+"&tipe="+$(".tipe").val(),"_Blank");
}

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
