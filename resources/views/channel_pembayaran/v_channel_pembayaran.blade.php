@extends('layouts.template1')
@section('content')
<div class="card">
	<div class="card-body">
		<div class="row">
			<div class="col-md-12">
				<div class="button-list">
					@if($Create == 'YA')
						<a href="{{ url('channel_pembayaran/add') }}" class="btn btn-bordered-primary waves-effect width-md waves-light"><i class="mdi mdi-plus"></i> {{ __('add') }} Data</a>
					@endif
					<button class="btn btn-bordered-danger waves-effect width-md waves-light" id="btnDelete" data-placement="top" title="Silahkan pilih data terlebih dahulu." data-toggle="modal" disabled><i class="mdi mdi-delete"></i> {{ __('app.delete') }}</button>
				</div>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-6">
				<label class="col-form-label"><h5 class="mb-0">Metode Pembayaran</h5></label>
				<select id="MetodePembayaranID" class="MetodePembayaranID form-control" onchange="filter()">
					<option value="">-- {{ __('app.view_all') }} --</option>
					@foreach($MetodePembayaranList as $row)
						<option value="{{$row->ID}}">{{$row->Nama}}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-6">
				<label class="col-form-label"><h5 class="mb-0">{{ __('app.keyword_legend') }}</h5></label>
				<input type="text" class="form-control keyword" onkeyup="filter()" placeholder="{{ __('keyword') }} .." />
			</div>
		</div>
	</div>
</div>
<div class="card">
	<div class="card-body">
	<div id="konten"></div>
	</div>
</div>

<!-------------------------------------------------------------------- Javascript Area -------------------------------------------------------------------->

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });


	function filter(url) {
		if(url == null)
		url = "{{ url('channel_pembayaran/search') }}";

		$.ajax({
			type: "POST",
			url: url,
			data: {
				MetodePembayaranID : $(".MetodePembayaranID").val(),
				keyword : $(".keyword").val(),
			},
			beforeSend: function(data) {
				$("#konten").html("<center><h3><i class='fa fa-spin fa-spinner'></i> Loading.. </h3></center>");
			},
			success: function(data) {
				$("#konten").html(data);
			}
		});
		return false;
	}
	filter();


</script>
@endpush

	<!------------------------------------------------------------------ End Javascript Area ------------------------------------------------------------------>
@endsection
