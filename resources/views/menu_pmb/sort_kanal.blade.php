@extends('layouts.template1')

@section('content')
<div class="card">
	<div class="card-body">
		<h3>Urutan Menu</h3>
		<form id="f_sort" onsubmit="save_sort(this); return false;">
			<div class="form-row mt-3">
				<div class="col-md-12">
					<ul id="sortable" class="list-group">
						@foreach($query ?? [] as $row)
							@php $row = (object) $row; @endphp
							<li class="list-group-item ui-state-default" style="cursor: move;">
								<input type="hidden" name="data_urut[]" value="{{ $row->id ?? '' }}">
								<i class="fa fa-bars mr-2"></i>
								{{ $row->namamenu ?? '' }}
							</li>
						@endforeach
					</ul>
				</div>
			</div>
			<button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light mt-3">{{ __('app.save') }} Urutan</button>
			<a href="{{ url('menu_pmb') }}" class="btn btn-bordered-danger waves-effect width-md waves-light mt-3">{{ __('app.back') }}</a>
		</form>
	</div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
$(function() { $("#sortable").sortable(); $("#sortable").disableSelection(); });
function save_sort(formz){ var formData = new FormData(formz); $.ajax({ type:'POST', url: "{{ url('menu_pmb/save_sort') }}", data:formData, cache:false, contentType: false, processData: false, beforeSend: function(r){ silahkantunggu(); }, success:function(data){ berhasil(); alertsuccess(); window.location="{{ url('menu_pmb') }}"; }, error: function(data){ alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.'); } }); }
</script>
@endpush
