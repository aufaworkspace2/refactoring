@extends('layouts.template1')

@section('content')
<div class="card">
	<div class="card-body">
		<div class="row">
			<div class="col-md-12">
				<div class="button-list">
					@if ($Create == 'YA')
						<a href="{{ url('gelombang_pmb/add_detail') }}?gelombang_id={{ request('gelombang_id') }}" class="btn btn-bordered-primary waves-effect  width-md waves-light"><i class="mdi mdi-plus"></i> {{ __('app.add') }} Data</a>
					@endif
					<button class="btn btn-bordered-danger waves-effect  width-md waves-light" id="btnDelete" data-placement="top" title="Silahkan pilih data terlebih dahulu." data-toggle="modal"><i class="mdi mdi-delete"></i> {{ __('app.delete') }}</button>
					<a href="javascript:void(0);" class="btn btn-warning waves-effect waves-light" onclick="tampilkan()"><icon class="mdi mdi-calendar-arrow-right"></icon>Edit Tanggal Batch</a>
					<a href="{{ url('gelombang_pmb/generate_gelombang') }}?gelombang_id={{ request('gelombang_id') }}" class="btn btn-bordered-secondary waves-effect  width-md waves-light"><i class="fa fa-cog"></i> Generate Periode</a>
					<a href="{{ url('gelombang_pmb') }}" class="btn btn-bordered-success waves-effect  width-md waves-light"><icon class="mdi mdi-arrow-left"></icon> Kembali</a>
				</div>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-12">
				<label class="col-form-label mt-2"><h4 class="m-0">{{ __('app.keyword_legend') }}</h4></label>
				<input type="text" class="form-control keyword" id="keyword" onkeyup="filter()" placeholder="{{ __('app.keyword') }} ..">
			</div>
		</div>
	</div>
</div>
<div class="card"><div class="card-body"><div id="konten"></div></div></div>

<div id="div_modal">
	<div class="modal large" id="tanggal_modal" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header"><h4 class="modal-title">Setting Tanggal</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
				<div class="modal-body">
					<div class="card"><div class="card-body">
						<div class="form-row">
							<div class="form-group col-md-12">
								<label class="col-form-label"><h5 class="mb-0">Tanggal Buka Pendaftaran *</h5></label>
								<div class="row">
									<div class="col-md-5"><input type='date' ID='tgl1' class='form-control tgl1'></div>
									<div class="col-md-2 mt-2" style="vertical-align : middle;text-align:center;">s/d</div>
									<div class="col-md-5"><input type='date' ID='tgl2' class='form-control tgl2'></div>
								</div>
							</div>
						</div>
					</div></div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
						<button type="button" class="btn btn-primary" onClick='savet()'>Edit Data</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

var gelombang_id = '{{ request('gelombang_id') }}';

function tampilkan() { $('#tanggal_modal').modal('show'); }
function filter(url) {
	if(url == null) url = "{{ url('gelombang_pmb/search_detail') }}";
	$.ajax({ type: "POST", url: url, data: { gelombang_id : gelombang_id, keyword : $('#keyword').val() }, success: function(data) { $("#konten").html(data); } });
	return false;
}
function checkall(chkAll,checkid) {
	if (checkid != null) {
		if (checkid.length == null) checkid.checked = chkAll.checked;
		else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;
		$("input:checkbox[name='checkID[]']").parents('tr').removeClass('checked_tabel');
		$("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('checked_tabel');
	}
}
filter();
function savet(){
	$('#tanggal_modal').modal('hide');
	$.ajax({
		type:'POST', url: "{{ url('gelombang_pmb/edit_tanggal_batch') }}", dataType: "JSON",
		data:{ tgl1 : $('.tgl1').val(), tgl2 : $('.tgl2').val(), gelombang_id : {{ request('gelombang_id') }} },
		beforeSend: function(r){ silahkantunggu(); },
		success:function(respond){
			if(respond.status == 1){ berhasil(); alertsuccess(); } else { alertfail(); berhasil(); }
			filter();
		},
		error: function(data){ alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.'); }
	});
}
</script>
@endpush
