<div class="row mb-2">
	<div class="col-md-12">
		{!! $total_row !!}
	</div>
</div>
<form id="f_delete_gelombang_pmb" action="{{ url('gelombang_pmb/delete') }}" >
<div class="table-responsive">
    <table class="table table-bordered mb-0 table-hover tablesorter">
		<thead class="bg-primary text-white">
			<tr>
				@if($Delete == 'YA')
				<th>
					<div class="checkbox checkbox-info">
						<input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.forms.namedItem('f_delete_gelombang_pmb')); show_btnDelete();">
						<label for="checkAll"></label>
					</div>
				</th>
				@endif
				<th class="text-center">No.</th>
				<th>Kode</th>
				<th>Nama</th>
				<th>Tahun Akademik</th>
				<th>Tahun Masuk Mahasiswa</th>
				<th>Gelombang Ke</th>
				<th>Status Pendaftaran</th>
				<th>Aksi</th>
			</tr>
		</thead>
		<tbody>
            @php $no=$offset; $i=0; @endphp
            @foreach($query as $row)
				@php 
					$row = (object) $row;
					$gelombang_detail = \DB::table('pmb_tbl_gelombang_detail')->where('gelombang_id', $row->id)->first();
				@endphp
				<tr class="gelombang_pmb_{{ $row->id ?? '' }}">
				@if($Delete == 'YA')
					@if(empty($gelombang_detail))
						<td>
							<div class="checkbox checkbox-info">
								<input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnDelete()" value="{{ $row->id ?? '' }}" >
								<label for="checkID{{ $i }}"></label>
							</div>
						</td>
						@php $i++; @endphp
					@else
						<td class="text-center">-</td>
					@endif
				  @endif
					<td class="text-center">{{ ++$no }}.</td>
					<td class="text-center">{{ $row->kode ?? '' }}</td>
					<td>
					@if($Update == 'YA')
					<a href="{{ url('gelombang_pmb/view/' . ($row->id ?? '')) }}" >{{ $row->nama ?? '' }}</a>
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
					<td>{{ $row->tahunmasuk ?? '' }}</td>
					<td>{{ $row->GelombangKe ?? '' }}</td>
					<td class="text-center">
						@if(isset($row->PendaftaranTerbuka) && $row->PendaftaranTerbuka > 0)
							<span class="badge badge-success">{{ $row->PendaftaranTerbuka }} Pendaftaran terbuka</span>
						@else
							<span class="badge badge-secondary">Tidak ada pendaftaran terbuka</span>
						@endif
					</td>
					<td>
						<a class="btn btn-bordered-warning waves-effect  width-md waves-light" href="{{ url('gelombang_pmb/detail') }}?gelombang_id={{ $row->id ?? '' }}"><i class="fa fa-eye"></i>&nbsp; Lihat Detail</a>
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

<!-------------------------------------------------------------------- Javascript Area -------------------------------------------------------------------->

<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

tablesorter();
$("#f_delete_gelombang_pmb").submit(function(){
		$.ajax({
		type: "POST",
		url: $("#f_delete_gelombang_pmb").attr('action'),
		data: $("#f_delete_gelombang_pmb").serialize(),
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
			$(".alert-success-content").html("{{ __('app.alert-success-delete') }}");
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
			$(".alert-error-content").html("{{ __('app.alert-error-delete') }}");
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
		url : "{{ url('welcome/test') }}/?table=gelombang_pmb&field=nama",
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
</script>

<!------------------------------------------------------------------ End Javascript Area ------------------------------------------------------------------>
