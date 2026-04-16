@extends('layouts.template1')

@section('content')
<div class="card">
	<div class="card-body">
		<div class="row">
			<div class="col-md-12">
				<div class="button-list">
					@if($Create == 'YA')
						<a href="{{ url('setting_prodi_tambahan_jurusan/add') }}" class="btn btn-bordered-primary waves-effect  width-md waves-light"><i class="mdi mdi-plus"></i> {{ __('app.add') }} Data</a>
					@endif
					<button class="btn btn-bordered-danger waves-effect  width-md waves-light" id="btnDelete" data-placement="top" title="Silahkan pilih data terlebih dahulu." data-toggle="modal"><i class="mdi mdi-delete"></i> {{ __('app.delete') }}</button>
				</div>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-4">
				<label class="col-form-label"><h5 class="mb-0">Jalur Pendaftaran</h5></label>
				<select class="JalurID form-control" onchange="filter();">
					<option value="">-- {{ __('app.view_all') }} --</option>
					@php $jalur_list = \DB::table('pmb_edu_jalur_pendaftaran')->get(); @endphp
					@foreach($jalur_list as $row)
						<option value="{{ $row->id }}">{{ $row->nama }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-4">
				<label class="col-form-label"><h5 class="mb-0">Program Studi</h5></label>
				<select class="ProdiID form-control" onchange="filter();">
					<option value="">-- {{ __('app.view_all') }} --</option>
					@php $prodi_list = \DB::table('programstudi')->get(); @endphp
					@foreach($prodi_list as $row)
						@php $jenjang = function_exists('get_field') ? get_field($row->JenjangID,'jenjang') : ''; @endphp
						<option value="{{ $row->ID }}">{{ $row->ProdiID ?? '' }} || {{ $jenjang }} || {{ $row->Nama }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-4">
				<label class="col-form-label"><h5 class="mb-0">{{ __('app.keyword_legend') }}</h5></label>
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
	if(url == null) url = "{{ url('setting_prodi_tambahan_jurusan/search') }}";
	$.ajax({
		type: "POST",
		url: url,
		data: {
			ProdiID : $(".ProdiID").val(),
			JalurID : $(".JalurID").val(),
			keyword : $(".keyword").val(),
		},
		success: function(data) {
			$("#konten").html(data);
		}
	});
	return false;
}

filter();

function checkall(chkAll, checkid) {
	if (chkAll.checked) {
		$('input[name="checkID[]"]').attr('checked', true);
		$('input[name="checkID[]"]').prop('checked', true);
	} else {
		$('input[name="checkID[]"]').attr('checked', false);
		$('input[name="checkID[]"]').prop('checked', false);
	}
	$("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
	$("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
}
</script>
@endpush
