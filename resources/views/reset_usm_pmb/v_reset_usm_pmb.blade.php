@extends('layouts.template1')

@section('content')
<div class="card">
	<div class="card-body">
		<div class="row">
			<div class="col-md-12">
				<div class="button-list">
					<button class="btn btn-bordered-danger waves-effect  width-md waves-light" id="btnDelete" data-placement="top" title="Silahkan pilih data terlebih dahulu." data-toggle="modal"><i class="mdi mdi-delete"></i> Reset Nilai USM</button>
				</div>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="m-0">Gelombang</h5></label>
				<select class="gelombang form-control" onchange="filter()">
					<option value="">-- {{ __('app.view_all') }} --</option>
				</select>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="m-0">Status Test</h5></label>
				<select class="statustest form-control" onchange="filter()">
					<option value="">-- {{ __('app.view_all') }} --</option>
					<option value="selesai">Selesai</option>
					<option value="belum">Belum Selesai</option>
				</select>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="m-0">Status Lulus</h5></label>
				<select class="statuslulus_pmb form-control" onchange="filter()">
					<option value="">-- {{ __('app.view_all') }} --</option>
					<option value="1">Lulus</option>
					<option value="2">Tidak Lulus</option>
					<option value="3">Belum Lulus</option>
				</select>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="m-0">{{ __('app.keyword_legend') }}</h5></label>
				<input type="text" class="form-control keyword" placeholder="{{ __('app.keyword') }} ..">
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
				<button type="button" onclick="resetnilai()" class="btn btn-danger waves-effect" >Reset</button>
				<button type="button" class="btn btn-primary waves-effect waves-light" data-dismiss="modal">{{ __('app.close') }}</button>
			</div>
		</div>
	</div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

function filter(url) {
	if(url == null) url = "{{ url('reset_usm_pmb/search') }}";
	$.ajax({
		type: "POST",
		url: url,
		data: {
			gelombang : $(".gelombang").val(),
			statustest : $(".statustest").val(),
			statuslulus_pmb : $(".statuslulus_pmb").val(),
			keyword : $(".keyword").val(),
		},
		success: function(data) {
			$("#konten").html(data);
		}
	});
	return false;
}

function checkall(chkAll,checkid) {
	if (checkid != null) {
		if (checkid.length == null) checkid.checked = chkAll.checked;
		else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;
		$("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
		$("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
	}
}

function resetnilai() {
	$.ajax({
		type: "POST",
		url: "{{ url('reset_usm_pmb/save') }}",
		data: $("#f_delete_reset_usm").serialize(),
		success: function(data) {
			$("#hapus").modal("hide");
			filter();
			$(".alert-success").show();
			$(".alert-success-content").html("Data berhasil direset");
			window.setTimeout(function() { $(".alert-success").slideUp(); }, 10000);
		}
	});
}

filter();
</script>
@endpush
