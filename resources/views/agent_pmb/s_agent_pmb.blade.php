<div class="row mb-2">
	<div class="col-md-12">
		{!! $total_row !!}
	</div>
</div>
<form id="f_delete_agent_pmb" action="{{ url('agent_pmb/delete') }}">
	<div class="table-responsive">
		<table class="table table-bordered mb-0 table-hover tablesorter">
			<thead class="bg-primary text-white">
				<tr>
					@if($Delete == 'YA')
						<th>
							<div class="checkbox checkbox-info">
								<input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.forms.namedItem('f_delete_agent_pmb')); show_btnDelete();">
								<label for="checkAll"></label>
							</div>
						</th>
					@endif
					<th class="text-center">No.</th>
					<th>Nama</th>
					<th>Institusi</th>
					<th>No Telepon</th>
					<th>Email</th>
					<th>Kode Referal</th>
					<th>Link Daftar</th>
				</tr>
			</thead>
			<tbody>
				@php $no = $offset; $i = 0; @endphp
				@foreach($query as $row)
					@php $row = (object) $row; @endphp
					<tr class="agent_pmb_{{ $row->id ?? '' }}">
						@if($Delete == 'YA')
							<td class="align-middle">
								<div class="checkbox checkbox-info">
									<input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnDelete()" value="{{ $row->id ?? '' }}">
									<label for="checkID{{ $i }}"></label>
								</div>
							</td>
						@endif
						<td class="text-center">{{ ++$no }}.</td>

						<td>
							@if($Update == 'YA')
								<a href="{{ url('agent_pmb/view/' . ($row->id ?? '')) }}">{{ $row->nama ?? '' }}</a>
							@else
								{{ $row->nama ?? '' }}
							@endif
						</td>
						<td class="text-center">{{ $row->institusi ?? '' }}</td>
						<td class="text-center">{{ $row->no_telepon ?? '' }}</td>
						<td class="text-center">{{ $row->email ?? '' }}</td>
						<td class="text-center">{{ $row->kode_referal ?? '' }}</td>
						<td class="text-center">{{ config('app.pmb_url') ?? getenv('PMB_URL') }}/registrasi?rf={{ $row->kode_referal ?? '' }}</td>

					</tr>
					@php $i++; @endphp
				@endforeach

			</tbody>
		</table>
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
						<button type="submit" class="btn btn-danger waves-effect">{{ __('app.delete') }}</button>
						<button type="button" class="btn btn-primary waves-effect waves-light" data-dismiss="modal">{{ __('app.close') }}</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			{!! $link !!}
		</div>
	</div>
</form>

<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

tablesorter();
$("#f_delete_agent_pmb").submit(function() {
	$.ajax({
		type: "POST",
		url: $("#f_delete_agent_pmb").attr('action'),
		data: $("#f_delete_agent_pmb").serialize(),
		dataType: 'json',
		success: function(response){
			// Remove rows based on response
			if(response.status === 'success' && response.removed_ids){
				response.removed_ids.forEach(function(id) {
					var className = '.' + response.class_prefix + id;
					$(className).remove();
				});
			}

			// Hide modal and cleanup
			$("#hapus").modal("hide");
			setTimeout(function() {
				$("body").removeClass("modal-open");
				$(".modal-backdrop").remove();
			}, 300);

			$(".alert-success").animate({ backgroundColor: "#dff0d8" }, 1000);
			$(".alert-success").animate({ backgroundColor: "#b6ef9e" }, 1000);
			$(".alert-success").animate({ backgroundColor: "#dff0d8" }, 1000);
			$(".alert-success").animate({ backgroundColor: "#b6ef9e" }, 1000);

			$(".alert-success").show();
			$(".alert-success-content").html("{{ __('app.alert-success-delete') }}");
			window.setTimeout(function() { $(".alert-success").slideUp(); }, 10000);

			// Refresh data
			filter();
		},
		error: function(data) {
			// Hide modal and cleanup
			$("#hapus").modal("hide");
			setTimeout(function() {
				$("body").removeClass("modal-open");
				$(".modal-backdrop").remove();
			}, 300);

			$(".alert-error").animate({ backgroundColor: "#ec9b9b" }, 1000);
			$(".alert-error").animate({ backgroundColor: "#df3d3d" }, 1000);
			$(".alert-error").animate({ backgroundColor: "#ec9b9b" }, 1000);
			$(".alert-error").animate({ backgroundColor: "#df3d3d" }, 1000);

			$(".alert-error").show();
			$(".alert-error-content").html("{{ __('app.alert-error-delete') }}");
			window.setTimeout(function() { $(".alert-error").slideUp("slow"); }, 6000);
		}
	});
	return false;
});

// Global function for show_btnDelete
window.show_btnDelete = function() {
	i = 0; hasil = false;
	while (document.getElementsByName('checkID[]').length > i) {
		var checkname = document.getElementById('checkID' + i);
		if (checkname && checkname.checked == true) { hasil = true; }
		i++;
	}
	if (hasil == true) {
		$('#btnDelete').removeAttr('disabled');
		$('#btnDelete').removeAttr('href');
		$('#btnDelete').removeAttr('title');
		$('#btnDelete').attr('href', '#hapus');
	} else {
		$('#btnDelete').attr('disabled','disabled');
		$('#btnDelete').attr('href','#');
		$('#btnDelete').attr('title', '{{ __('app.Pilih dahulu data yang akan di hapus') }}');
	}
}
show_btnDelete();

$("input:checkbox[name='checkID[]']").click(function() {
	if (this.checked == true) { $(this).parents('tr').addClass('table-danger'); }
	else { $(this).parents('tr').removeClass('table-danger'); }
});

$('#btnDelete').click(function() {
	$.ajax({
		url: "{{ url('welcome/test') }}/?table=agent_pmb&field=nama",
		type: "POST",
		data: {
			checkID: $("input:checkbox[name='checkID[]']:checked").map(function(){
				return this.value;
			}).get(),
			_token: "{{ csrf_token() }}"
		},
		success: function(data) { $('.data_name').html(data); }
	});
});
</script>
