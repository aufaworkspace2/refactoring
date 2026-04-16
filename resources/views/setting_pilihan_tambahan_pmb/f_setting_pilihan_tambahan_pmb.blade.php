@extends('layouts.template1')

@section('content')

<div class="card">
	<div class="card-body">
		<form onsubmit="savedata(this); return false;" id="f_setting" action="{{ url('setting_pilihan_tambahan_pmb/set_publish_all/' . ($save ?? 1)) }}" enctype="multipart/form-data">
		<div class="form-row mt-2">
			<div class="form-group col-md-12">
				<label class="col-form-label" for="Mode">Setting Muncul Pilihan Prodi di PMB</label>
				<div class="controls">
					<div class="table-responsive" id="dataMode">
						<table class="table table-bordered table-hovered">
							<thead class="bg-primary text-white">
								<tr>
									<th>Pilihan</th>
									<th style="width: 30%">Status</th>
								</tr>
							</thead>
							<tbody>
								@foreach($pilihan_aktif ?? [] as $key_pa => $pa)
								<tr>
									<td>{{ $pa }}</td>
									<td>
										<div class="form-check">
											<input type="radio" id="muncul_pmb1_{{ $key_pa }}" name="muncul_pmb[{{ $key_pa }}]" class="form-check-input" {{ (isset($metadata_muncul_pmb[$key_pa]) && $metadata_muncul_pmb[$key_pa] == 1) ? "checked" : "" }} value="1" required/>
											<label class="form-check-label" for="muncul_pmb1_{{ $key_pa }}">Aktif</label>
										</div>
										<div class="form-check">
											<input type="radio" id="muncul_pmb0_{{ $key_pa }}" name="muncul_pmb[{{ $key_pa }}]" class="form-check-input" {{ (isset($metadata_muncul_pmb[$key_pa]) && $metadata_muncul_pmb[$key_pa] == 0) ? "checked" : "" }} value="0" />
											<label class="form-check-label" for="muncul_pmb0_{{ $key_pa }}">Tidak Aktif</label>
										</div>
									</td>
								</tr>
								@endforeach
							</tbody>
							</table>
						</div>
				</div>
			</div>

			<div class="form-group col-md-12">
				<label class="col-form-label" for="Mode">Setting Nominal Tambahan di Formulir PMB </label>
				<div class="controls">
					<div class="table-responsive" id="dataMode">
						<table class="table table-bordered table-hovered">
							<thead class="bg-primary text-white">
								<tr>
									<th>Pilihan</th>
									<th style="width:30%">Nominal</th>
								</tr>
							</thead>
							<tbody>
								@foreach($pilihan_aktif ?? [] as $key_pa => $pa)
								<tr>
									<td>{{ $pa }}</td>
									<td>
										<input type="text" name="tambahan_nominal[{{ $key_pa }}]" class="form-control currency" value="{{ $metadata_tambahan_nominal[$key_pa] ?? 0 }}">
									</td>
								</tr>
								@endforeach
							</tbody>
							</table>
						</div>
				</div>
			</div>

		</div>
		<br>
		<button type="submit" class="btn btn-primary btn-phone-block btnSave">{{ __('app.save') }} Data</button>

		</form>
	</div>
</div>

@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

$(document).ready(function() {
	// Initialize currency mask if available
	if ($.fn.mask) {
		$('.currency').mask('#.##0', {reverse: true, byPassKeys:[17, 65]});
		$('.currency').trigger('input');
	}
});

function savedata(formz){
	// Unmask currency if mask is available
	if ($.fn.mask && $('.currency').inputmask) {
		$('.currency').unmask();
	}
	
	var formData = new FormData(formz);
	$.ajax({
		type:'POST',
		url: $(formz).attr('action'),
		data:formData,
		cache:false,
		contentType: false,
		processData: false,
		beforeSend: function(r){
		silahkantunggu();
		},
		success:function(data){
			window.location = "{{ url('setting_pilihan_tambahan_pmb') }}";
			berhasil();
			alertsuccess();
		},
		error: function(data){
			// Re-mask currency if mask is available
			if ($.fn.mask) {
				$('.currency').mask('#.##0', {reverse: true, byPassKeys:[17, 65]});
			}

			$(".btnSave").html("{{ __('app.save') }} Data");
			$(".btnSave").removeAttr("disabled");
			$(".alert-error").animate({ backgroundColor: "#ec9b9b" }, 1000);
			$(".alert-error").animate({ backgroundColor: "#df3d3d" }, 1000);
			$(".alert-error").animate({ backgroundColor: "#ec9b9b" }, 1000);
			$(".alert-error").animate({ backgroundColor: "#df3d3d" }, 1000);

			$(".alert-error").show();
			$(".alert-error-content").html("{{ __('app.alert-error') }}");
			window.setTimeout(function() { $(".alert-error").slideUp( "slow" ); }, 6000);
		}
	});
}
</script>
@endpush
