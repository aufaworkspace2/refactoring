<div class="row mb-2">
	<div class="col-md-12">
		{!! $total_row !!}
	</div>
</div>
<form id="f_delete_jalur_pendaftaran_pmb" action="{{ url('jalur_pendaftaran_pmb/delete') }}" >
	<div class="table-responsive">
		<table class="table table-bordered mb-0 table-hover tablesorter">
			<thead class="bg-primary text-white">
				<tr>
				@if($Delete == 'YA')
					<th>
						<div class="checkbox checkbox-info">
							<input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.forms.namedItem('f_delete_jalur_pendaftaran_pmb')); show_btnDelete();">
							<label for="checkAll"></label>
						</div>
					</th>
				@endif
					<th class="text-center">No.</th>
					<th>Kode</th>
					<th>Nama</th>
					<th>Aktif</th>
				</tr>
			</thead>
			<tbody>
			@php $no=$offset; $i=0; $default = array(1,2,3,4,5,6,7,8,9,10,11); @endphp
			@foreach($query as $row)
				@php $row = (object) $row; @endphp
				<tr class="jalur_pendaftaran_pmb_{{ $row->id ?? '' }}">
				@if($Delete == 'YA')
					@if(!in_array($row->id,$default) && ($row->jumlah_pilihan_pendaftaran ?? 0) == 0)
				    	<td class="align-middle">
							<div class="checkbox checkbox-info">
								<input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnDelete()" value="{{ $row->id ?? '' }}" >
								<label for="checkID{{ $i }}"></label>
							</div>
						</td>
					@else
						<td></td>
					@endif
				  @endif
					<td class="text-center">{{ ++$no }}.</td>
					<td>{{ $row->kode ?? '' }}</td>
					<td>
					@if($Update == 'YA' && !in_array($row->id,$default))
					<a href="{{ url('jalur_pendaftaran_pmb/view/' . ($row->id ?? '')) }}" >{{ $row->nama ?? '' }}</a>
					@else
						{{ $row->nama ?? '' }}
					@endif
					</td>
					<td>
					@if($Update == 'YA')
						<script>
						$('#bukatutup{{ $no }}').bootstrapSwitch();
						$('input:checkbox[name="bukatutup{{ $no }}"]:checked').parents('tr').css("background","#dcfece");
						</script>
						<input id="bukatutup{{ $no }}"  name="bukatutup{{ $no }}" onchange="bukatutup(this)" value="{{ $row->id ?? '' }}" type="checkbox" data-on-color="success" data-off-color="danger" data-on-text="Yes" data-off-text="No" {{ (isset($row->aktif) && $row->aktif == 1) ? "checked" : "" }} />
					@else
						@if(isset($row->aktif) && $row->aktif == '1')
							<span class='badge badge-success'>Aktif</span>
						@else
							<span class='badge badge-default'>Tidak Aktif</span>
						@endif
					@endif
					</td>
				</tr>
                @php $i++; @endphp
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

<!-------------------------------------------------------------------- Javascript Area -------------------------------------------------------------------->

<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

// Define function bukatutup FIRST before bootstrapSwitch initialize
function bukatutup(chek){
	var aha = $(chek).bootstrapSwitch('state');
	var val = $(chek).val();

	if(aha == true){ var buka = 1; var tutup = 0; }
	if(aha == false){ var tutup = 1; var buka = 0; }

	toastr.options = {
	  "closeButton": false, "debug": false, "newestOnTop": false, "progressBar": false,
	  "positionClass": "toast-top-left", "preventDuplicates": false, "onclick": null,
	  "showDuration": "300", "hideDuration": "1000", "timeOut": "5000", "extendedTimeOut": "1000",
	  "showEasing": "swing", "hideEasing": "linear", "showMethod": "fadeIn", "hideMethod": "fadeOut"
	}

	$.ajax({
		url:"{{ url('jalur_pendaftaran_pmb/aktif') }}",
		data:{ buka : buka, tutup:tutup, val : val},
		type:"POST",
		beforeSend: function() { $('.loading').fadeIn('fast'); },
		success: function(data) {
			$('.loading').fadeOut('fast');
			toastr["success"]("", "Update Data Berhasil");
			filter("{{ url('jalur_pendaftaran_pmb/search') }}/{{ $offset ?? 0 }}");
		},
		error: function(){
			$('.loading').fadeOut('fast');
			toastr["danger"]("", "Update Data Gagal");
		}
	});
}

tablesorter();

$("#f_delete_jalur_pendaftaran_pmb").submit(function(){
	$.ajax({
		type: "POST",
		url: $("#f_delete_jalur_pendaftaran_pmb").attr('action'),
		data: $("#f_delete_jalur_pendaftaran_pmb").serialize(),
		dataType: 'json',
		success:function(response){
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

            $( ".alert-success" ).animate({ backgroundColor: "#dff0d8" }, 1000 );
			$( ".alert-success" ).animate({ backgroundColor: "#b6ef9e" }, 1000 );
			$( ".alert-success" ).animate({ backgroundColor: "#dff0d8" }, 1000 );
			$( ".alert-success" ).animate({ backgroundColor: "#b6ef9e" }, 1000 );

			$(".alert-success").show();
			$(".alert-success-content").html("{{ __('app.alert-success-delete') }}");
			window.setTimeout(function() { $(".alert-success").slideUp(); }, 10000);

			// Refresh data
			filter();
		},
		error: function(data){
			// Hide modal and cleanup
			$("#hapus").modal("hide");
			setTimeout(function() {
				$("body").removeClass("modal-open");
				$(".modal-backdrop").remove();
			}, 300);

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

// Global function for show_btnDelete
window.show_btnDelete = function(){
	i=0; hasil = false;
	while(document.getElementsByName('checkID[]').length > i)
	{
		var checkname = document.getElementById('checkID'+i);
		if(checkname && checkname.checked == true) { hasil = true; }
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
		$('#btnDelete').attr('title', '{{ __('app.Pilih dahulu data yang akan di hapus') }}');
	}
}
show_btnDelete();

$("input:checkbox[name='checkID[]']").click(function(){
	if(this.checked == true){ $(this).parents('tr').addClass('table-danger'); }
	else { $(this).parents('tr').removeClass('table-danger'); }
});

$('#btnDelete').click(function(){
	$.ajax({
		url : "{{ url('welcome/test') }}/?table=jalur_pendaftaran_pmb&field=nama",
		type: "POST",
		data: {
			checkID: $("input:checkbox[name='checkID[]']:checked").map(function(){
				return this.value;
			}).get(),
			_token: "{{ csrf_token() }}"
		},
		success: function(data){ $('.data_name').html(data); }
	});
});
</script>
