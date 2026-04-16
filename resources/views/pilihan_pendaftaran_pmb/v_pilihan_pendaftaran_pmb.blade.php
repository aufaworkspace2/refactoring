@extends('layouts.template1')

@section('content')
<div class="card">
	<div class="card-body">
		<div class="row">
			<div class="col-md-12">
				<div class="button-list">
					@if($Create == 'YA')
						<a href="{{ url('pilihan_pendaftaran_pmb/add') }}" class="btn btn-bordered-primary waves-effect  width-md waves-light"><i class="mdi mdi-plus"></i> {{ __('add') }} Data</a>
					@endif
						<button class="btn btn-bordered-danger waves-effect  width-md waves-light" id="btnDelete" data-placement="top" title="Silahkan pilih data terlebih dahulu." data-toggle="modal" disabled><i class="mdi mdi-delete"></i> {{ __('delete') }}</button>
				</div>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-4">
				<label class="col-form-label mt-2"><h4 class="m-0">Tahun Akademik</h4></label>
				<select id="tahun_id" class="tahun_id form-control" onchange="filter()">
					<option value="">-- {{ __('view_all') }} --</option>
					@php
						$tahun_list = \DB::table('tahun')->orderBy('TahunID', 'desc')->get();
					@endphp
					@foreach($tahun_list as $row)
						@php
							$aktif = (isset($row->ProsesBuka) && $row->ProsesBuka == 1) ? '(Aktif)' : '';
							$s = (isset($row->ProsesBuka) && $row->ProsesBuka == 1) ? 'selected' : '';
						@endphp
						<option value="{{ $row->ID }}" {{ $s }} >{{ $row->Nama }} {{ $aktif }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-8">
				<label class="col-form-label mt-2"><h4 class="m-0">{{ __('keyword_legend') }}</h4></label>
				<input type="text" class="form-control keyword" placeholder="{{ __('keyword') }} ..">
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

<!-------------------------------------------------------------------- Javascript Area -------------------------------------------------------------------->

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

	function filter(url) {
		if(url == null)
		url = "{{ url('pilihan_pendaftaran_pmb/search') }}";

		$.ajax({
		type: "POST",
		url: url,
		data: {
			keyword : $(".keyword").val(),
			tahun_id : $(".tahun_id").val(),
		},
		success: function(data) {
			$("#konten").html(data);
		}
	});
	return false;
}

$('.keyword').keyup(fncDelay(function (e) {
	filter();
}, 500));




	function checkall(chkAll,checkid) {
		if (checkid != null)
		{
			if (checkid.length == null) checkid.checked = chkAll.checked;
			else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;

			$("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
			$("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
		}
	}
	filter();


</script>
@endpush

<!------------------------------------------------------------------ End Javascript Area ------------------------------------------------------------------>
