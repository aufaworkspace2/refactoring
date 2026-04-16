<div class="row mb-2">
	<div class="col-md-12">
		{!! $total_row !!}
	</div>
</div>
<form id="f_delete_pilihan_pendaftaran_pmb" action="{{ url('pilihan_pendaftaran_pmb/delete') }}" >
	<div class="table-responsive">
		<table class="table table-bordered mb-0 table-hover tablesorter">
			<thead class="bg-primary text-white">
				<tr>
					@if($Delete == 'YA')
					<th class="sorterfalse">
						<div class="checkbox checkbox-info">
							<input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.forms.namedItem('f_delete_pilihan_pendaftaran_pmb')); show_btnDelete();">
							<label for="checkAll"></label>
						</div>
					</th>
					@endif
					<th class="text-center">No.</th>
					<th>Nama</th>
					<th>Tahun</th>
					<th>Program</th>
					<th>Jalur</th>
					<th>Jenis Pendaftaran</th>
					<th>Beasiswa/Diskon</th>
					<th>Aktif</th>
				</tr>
			</thead>
			<tbody>
			@php $no=$offset; $i=0; @endphp
			@foreach($query as $row)
				@php $row = (object) $row; @endphp
				<tr class="pilihan_pendaftaran_pmb_{{ $row->id ?? '' }}">
				@if($Delete == 'YA')
					@if(($row->jumlah_gelombang_detail ?? 0) == 0)
				    	<td>
							<div class="checkbox checkbox-info">
								<input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnDelete()" value="{{ $row->id ?? '' }}" >
								<label for="checkID{{ $i }}"></label>
							</div>
						</td>
					@else
						<td></td>
					@endif
				  @endif
					<td class="center">{{ ++$no }}.</td>
					<td>
					@if($Update == 'YA')
					<a href="{{ url('pilihan_pendaftaran_pmb/view/' . ($row->id ?? '')) }}" >{{ $row->nama ?? '' }}</a>
					@else
						{{ $row->nama ?? '' }}
					@endif
					</td>
					<td>
						@php
							$tahun_nama = '';
							if(isset($row->tahun_id) && function_exists('get_field')) {
								$tahun_nama = get_field($row->tahun_id, 'tahun');
							}
						@endphp
						{{ $tahun_nama }}
					</td>
					<td>
						@php
							if(isset($row->program_id) && $row->program_id) {
								echo "<ol>";
								$program = explode(",", $row->program_id);
								foreach($program as $p) {
									$prog_nama = '';
									if(function_exists('get_field')) {
										$prog_nama = get_field($p, 'program');
									}
									echo "<li>" . $prog_nama . "</li>";
								}
								echo "</ol>";
							}
						@endphp
					</td>
					<td>
						@php
							if(isset($row->jalur) && $row->jalur) {
								echo "<ol>";
								$jalur = explode(",", $row->jalur);
								foreach($jalur as $p) {
									$jalur_nama = '';
									if(function_exists('get_field')) {
										$jalur_nama = get_field($p, 'pmb_edu_jalur_pendaftaran');
									}
									echo "<li>" . $jalur_nama . "</li>";
								}
								echo "</ol>";
							}
						@endphp
					</td>
					<td>
						@php
							if(isset($row->jenis_pendaftaran) && $row->jenis_pendaftaran) {
								echo "<ol>";
								$jenis_pendaftaran = explode(",", $row->jenis_pendaftaran);
								foreach($jenis_pendaftaran as $p) {
									$jp_nama = '';
									if(function_exists('get_field')) {
										$jp_nama = get_field($p, 'jenis_pendaftaran');
									}
									echo "<li>" . $jp_nama . "</li>";
								}
								echo "</ol>";
							}
						@endphp
					</td>
					<td>
						@php
							if(isset($row->master_diskon_id_list) && $row->master_diskon_id_list) {
								echo "<ol>";
								$master_diskon_id_list = explode(",", $row->master_diskon_id_list);
								foreach($master_diskon_id_list as $m) {
									$m_nama = '';
									if(function_exists('get_field')) {
										$m_nama = get_field($m, 'master_diskon');
									}
									echo "<li>" . $m_nama . "</li>";
								}
								echo "</ol>";
							} else {
								echo "Tidak Ada Beasiswa/Diskon";
							}
						@endphp
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
					<h4 class="modal-title" id="hapus">{{ __('confirm_header') }}</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<div class="modal-body">
					<p>{{ __('confirm_message') }}</p>
					<p class="data_name"></p>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-danger waves-effect" >{{ __('delete') }}</button>
					<button type="button" class="btn btn-primary waves-effect waves-light" data-dismiss="modal">{{ __('close') }}</button>
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
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

tablesorter();
$("#f_delete_pilihan_pendaftaran_pmb").submit(function(){
		$.ajax({
		type: "POST",
		url: $("#f_delete_pilihan_pendaftaran_pmb").attr('action'),
		data: $("#f_delete_pilihan_pendaftaran_pmb").serialize(),
		success:function(data){
			$("#hapus").modal("hide");
			setTimeout(function(){
				$("body").removeClass("modal-open");
				$(".modal-backdrop").remove();
			}, 300);
			filter();

            $( ".alert-success" ).animate({
					backgroundColor: "#dff0d8"
			}, 1000 );
			$( ".alert-success" ).animate({
					backgroundColor: "#b6ef9e"
			}, 1000 );
			$( ".alert-success" ).animate({
					backgroundColor: "#dff0d8"
			}, 1000 );
			$( ".alert-success" ).animate({
					backgroundColor: "#b6ef9e"
			}, 1000 );

			$(".alert-success").show();
			$(".alert-success-content").html("{{ __('alert-success-delete') }}");
			window.setTimeout(function() { $(".alert-success").slideUp(); }, 10000);
		},
		error: function(data){
			$( ".alert-error" ).animate({
					backgroundColor: "#ec9b9b"
			}, 1000 );
			$( ".alert-error" ).animate({
					backgroundColor: "#df3d3d"
			}, 1000 );
			$( ".alert-error" ).animate({
					backgroundColor: "#ec9b9b"
			}, 1000 );
			$( ".alert-error" ).animate({
					backgroundColor: "#df3d3d"
			}, 1000 );

			$(".alert-error").show();
			$(".alert-error-content").html("{{ __('alert-error-delete') }}");
			window.setTimeout(function() { $(".alert-error").slideUp( "slow" ); }, 6000);
		}
		});
		return false;
	});

window.show_btnDelete = function(){
	i=0; hasil = false;
	while(document.getElementsByName('checkID[]').length > i)
	{
		var el = document.getElementById('checkID'+i);

		if(el && el.checked)
		{
			hasil = true;
		}
		i++;
	}
	if(hasil == true) {
		$('#btnDelete').removeAttr('disabled');
		$('#btnDelete').removeAttr('href');
		$('#btnDelete').removeAttr('title');
		$('#btnDelete').attr('href', '#hapus');
	}
	else
	{
		$('#btnDelete').attr('disabled','disabled');
		$('#btnDelete').attr('href','#');
		$('#btnDelete').attr('title', 'Pilih dahulu data yang akan di hapus');
	}
}
show_btnDelete();

$("input:checkbox[name='checkID[]']").click(function(){
	if(this.checked == true){
		$(this).parents('tr').addClass('table-danger');
	}
	else
	{
		$(this).parents('tr').removeClass('table-danger');
	}
});
$('#btnDelete').click(function(){
	$.ajax({
		url : "{{ url('welcome/test') }}/?table=pmb_pilihan_pendaftaran&field=nama",
		type: "POST",
		data: {
			checkID: $("input:checkbox[name='checkID[]']:checked").map(function(){
				return this.value;
			}).get(),
			_token: "{{ csrf_token() }}"
		},
		success: function(data){
			$('.data_name').html(data);
		}
	});
});
function bukatutup(chek){
	var aha = $(chek).bootstrapSwitch('state');
	var val = $(chek).val();

	if(aha == true){
	var buka = 1;
	var tutup = 0;
	}

	if(aha == false){
	var tutup = 1;
	var buka = 0;
	}

	toastr.options = {
	  "closeButton": false,
	  "debug": false,
	  "newestOnTop": false,
	  "progressBar": false,
	  "positionClass": "toast-top-left",
	  "preventDuplicates": false,
	  "onclick": null,
	  "showDuration": "300",
	  "hideDuration": "1000",
	  "timeOut": "5000",
	  "extendedTimeOut": "1000",
	  "showEasing": "swing",
	  "hideEasing": "linear",
	  "showMethod": "fadeIn",
	  "hideMethod": "fadeOut"
	}

	$.ajax({
		url:"{{ url('pilihan_pendaftaran_pmb/aktif') }}",
		data:{ buka : buka, tutup:tutup, val : val},
		type:"POST",
		beforeSend: function() {
			$('.loading').fadeIn('fast');
		},
		success: function(data) {
			$('.loading').fadeOut('fast');
			toastr["success"]("", "Update Data Berhasil");
			filter("{{ url('pilihan_pendaftaran_pmb/search') }}/{{ $offset ?? 0 }}");
		},
		error: function(){
			$('.loading').fadeOut('fast');
			toastr["danger"]("", "Update Data Gagal");
		}
	});
}

</script>

<!------------------------------------------------------------------ End Javascript Area ------------------------------------------------------------------>
