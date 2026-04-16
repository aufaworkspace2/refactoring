<div class="row mb-2">
	<div class="col-md-12">
		{!! $total_row !!}
	</div>
</div>
<form id="f_delete_data_leads" action="{{ url('data_leads_pmb/delete') }}" >
	<div class="table-responsive">
		<table class="table table-bordered mb-0 table-hover tablesorter">
			<thead class="bg-primary text-white">
				<tr>
					@if($Delete == 'YA')
						<th width="2%" class="sorterfalse">
							<div class="checkbox checkbox-info">
								<input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.forms.namedItem('f_delete_data_leads')); show_btnDelete();">
								<label for="checkAll"></label>
							</div>
						</th>
					@endif
					<th class="text-center align-middle" width="2%">No.</th>
					<th >Nama</th>
					<th >Email</th>
					<th >No. HP</th>
					<th >Tanggal</th>
					<th >Status Daftar</th>
				</tr>
			</thead>
			<tbody>
			@php $no=$offset; $i=0; @endphp
			@foreach($query as $row)
				@php $row = (object) $row; @endphp
				<tr class="data_{{ $row->id ?? '' }}">
					@if($Delete == 'YA')
						<td class="">
							@if(empty($row->ChannelPembayaranID_list ?? ''))
							<div class="checkbox checkbox-info">
								<input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnDelete()" value="{{ $row->id ?? '' }}" >
								<label for="checkID{{ $i }}"></label>
							</div>
							@php $i++; @endphp
							@else
							-
							@endif
						</td>
					@endif
					<td class="text-center ">{{ ++$no }}.</td>
					<td>{{ $row->nama ?? '' }}</td>
					<td>{{ $row->email ?? '' }}</td>
					<td>{{ $row->telepon ?? '' }}</td>
					<td>{{ $row->created_at ? date('d/m/Y', strtotime($row->created_at)) : '' }}</td>
					<td>
					@if($row->MhswID ?? null)
						<label class='badge badge-success'>Sudah</label>
					@else
						<label class='badge badge-secondary'>Belum</label>
					@endif
					</td>
				</tr>
			@endforeach
			</tbody>
		</table>
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
					<button type="submit" class="btn btn-danger waves-effect" >{{ __('app.delete') }}</button>
					<button type="button" class="btn btn-primary waves-effect waves-light" data-dismiss="modal">{{ __('app.close') }}</button>
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
$("#f_delete_data_leads").submit(function(){
	$.ajax({
		type: "POST",
		url: $("#f_delete_data_leads").attr('action'),
		data: $("#f_delete_data_leads").serialize(),
		dataType: 'json',
		success:function(response){
			// Remove rows based on response
			if(response.status === 'success' && response.removed_ids){
				response.removed_ids.forEach(function(id) {
					var className = '.' + response.class_prefix + id;
					$(className).remove();
				});
			}

			$("#hapus").modal("hide");
			setTimeout(function() {
				$("body").removeClass("modal-open");
				$(".modal-backdrop").remove();
			}, 300);
			filter();

            $( ".alert-success" ).animate({ backgroundColor: "#dff0d8" }, 1000 );
			$( ".alert-success" ).animate({ backgroundColor: "#b6ef9e" }, 1000 );
			$( ".alert-success" ).animate({ backgroundColor: "#dff0d8" }, 1000 );
			$( ".alert-success" ).animate({ backgroundColor: "#b6ef9e" }, 1000 );

			$(".alert-success").show();
			$(".alert-success-content").html("{{ __('app.alert-success-delete') }}");
			window.setTimeout(function() { $(".alert-success").slideUp(); }, 10000);
		},
		error: function(data){
			$( ".alert-error" ).animate({ backgroundColor: "#ec9b9b" }, 1000 );
			$( ".alert-error" ).animate({ backgroundColor: "#df3d3d" }, 1000 );
			$( ".alert-error" ).animate({ backgroundColor: "#ec9b9b" }, 1000 );
			$( ".alert-error" ).animate({ backgroundColor: "#df3d3d" }, 1000 );

			$(".alert-error").show();
			$(".alert-error-content").html("{{ __('app.alert-error-delete') }}");
			window.setTimeout(function() { $(".alert-error").slideUp( "slow" ); }, 6000);
		}
	});
	return false;
});

window.show_btnDelete = function(){
	i=0; hasil = false;
	while(document.getElementsByName('checkID[]').length > i) {
		var el = document.getElementById('checkID'+i);
		if(el && el.checked) { hasil = true; }
		i++;
	}
	if(hasil == true) {
		$('#btnDelete').removeAttr('disabled');
		$('#btnDelete').removeAttr('href');
		$('#btnDelete').removeAttr('title');
		$('#btnDelete').attr('href', '#hapus');
	} else {
		$('#btnDelete').attr('disabled','disabled');
		$('#btnDelete').attr('href','#');
		$('#btnDelete').attr('title', 'Pilih dahulu data yang akan di hapus');
	}
}
show_btnDelete();

$("input:checkbox[name='checkID[]']").click(function(){
	if(this.checked == true){ $(this).parents('tr').addClass('table-danger'); }
	else { $(this).parents('tr').removeClass('table-danger'); }
});

function checkall(chkAll,checkid) {
	if (checkid != null) {
		if (checkid.length == null) checkid.checked = chkAll.checked;
		else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;
		$("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
		$("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
	}
}
</script>
