@extends('layouts.template1')

@section('content')
<div class="card">
	<div class="card-body">
		<div class="row">
			<div class="col-md-12 mb-2">
				<div class="button-list">
					<div class="btn-group">
						<button type="button" class="btn btn-info dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false"><i class="mdi mdi-printer mr-1"></i> Cetak <i class="mdi mdi-chevron-down"></i></button>
						<div class="dropdown-menu">
							<a onclick="pdf()" href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-printer"></i> PDF</a>
							<a onclick="excel()" href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-printer"></i> Excel</a>
						</div>
					</div>
					<button class="btn btn-bordered-danger waves-effect  width-md waves-light" id="btnDelete" data-placement="top" title="Silahkan pilih data terlebih dahulu." data-toggle="modal" disabled><i class="mdi mdi-delete"></i> {{ __('app.delete') }}</button>
				</div>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-4">
				<label class="col-form-label "><h5 class="m-0">Dari Tanggal</h5></label>
				<input class="form-control" type='text' id='Tgl1' value="{{ date('Y-m-d') }}" onkeyup="filter()" onchange="filter()" onfocus="filter()">
			</div>
			<div class="form-group col-md-4">
				<label class="col-form-label "><h5 class="m-0">Sampai Tanggal</h5></label>
				<input class="form-control" type='text' id='Tgl2' value="{{ date('Y-m-d') }}" onkeyup="filter()" onchange="filter()" onfocus="filter()">
			</div>

			<div class="form-group col-md-4">
				<label class="col-form-label "><h5 class="m-0">Status Mendaftar</h5></label>
				<select class="StatusMendaftar form-control" onchange="filter();">
					<option value="">-- {{ __('app.view_all') }} --</option>
					<option value="1" >Ya</option>
					<option value="2" >Tidak</option>
				</select>
			</div>

			<div class="form-group col-md-12">
				<label class="col-form-label"><h5 class="mb-0">{{ __('app.keyword_legend') }}</h5></label>
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
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

$(document).ready(function() {
	$('#Tgl1').datetimepicker({
		format:'Y-m-d',
		onShow:function( ct ){
			this.setOptions({
				maxDate:$('#Tgl2').val()?$('#Tgl2').val():false
			})
		},
		timepicker:false,
		mask:'9999-19-39'
	});

	$('#Tgl2').datetimepicker({
		format:'Y-m-d',
		onShow:function( ct ){
			this.setOptions({
				minDate:$('#Tgl1').val()?$('#Tgl1').val():false
			})
		},
		timepicker:false,
		mask:'9999-19-39'
	});
});

function filter(url) {
	if(url == null) url = "{{ url('data_leads_pmb/search') }}";

	$.ajax({
		type: "POST",
		url: url,
		data: {
			StatusMendaftar : $(".StatusMendaftar").val(),
			keyword : $(".keyword").val(),
			tgl1 : $("#Tgl1").val(),
			tgl2 : $("#Tgl2").val(),
		},
		success: function(data) {
			$("#konten").html(data);
		}
	});
	return false;
}
filter();

$('.keyword').keyup(fncDelay(function (e) {
	filter();
}, 500));

function pdf(){
	var StatusMendaftar = $(".StatusMendaftar").val();
	var keyword = $(".keyword").val();
	var tgl1 = $("#Tgl1").val();
	var tgl2 = $("#Tgl2").val();

	var link = "?1";
	link += "&StatusMendaftar="+StatusMendaftar;
	link += "&keyword="+keyword;
	link += "&Tgl1="+tgl1;
	link += "&Tgl2="+tgl2;

	window.open("{{ url('data_leads_pmb/pdf') }}"+link,"_Blank");
}

function excel(){
	var StatusMendaftar = $(".StatusMendaftar").val();
	var keyword = $(".keyword").val();
	var tgl1 = $("#Tgl1").val();
	var tgl2 = $("#Tgl2").val();

	var link = "?1";
	link += "&StatusMendaftar="+StatusMendaftar;
	link += "&keyword="+keyword;
	link += "&Tgl1="+tgl1;
	link += "&Tgl2="+tgl2;

	window.open("{{ url('data_leads_pmb/excel') }}"+link,"_Blank");
}

function checkall(chkAll,checkid) {
	if (checkid != null) {
		if (checkid.length == null) checkid.checked = chkAll.checked;
		else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;

		$("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
		$("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
	}
}
</script>
@endpush
