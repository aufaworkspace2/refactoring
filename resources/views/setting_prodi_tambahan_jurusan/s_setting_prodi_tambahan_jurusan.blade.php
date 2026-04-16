<div class="row mb-2">
	<div class="col-md-12">
		{!! $total_row !!}
	</div>
</div>
<form id="f_delete_setting_pilihan_jurusan" action="{{ url('setting_prodi_tambahan_jurusan/delete') }}" >
	<div class="table-responsive">
		<table class="table table-bordered mb-0 table-hover tablesorter">
			<thead class="bg-primary text-white">
				<tr>
					@if($Delete == 'YA')
					<th>
						<div class="checkbox checkbox-info">
							<input type="checkbox" name="checkAll" id="checkAll" onclick="checkall(this,document.forms.namedItem('f_delete_setting_pilihan_jurusan')); show_btnDelete();">
							<label for="checkAll"></label>
						</div>
					</th>
					@endif
					<th class="text-center">No.</th>
					<th>Jalur Pendaftaran</th>
					<th>Pilihan Program Studi 1</th>
					<th>Tambahan Program Studi</th>
					<th>Pilihan Program Studi 2</th>
					<th>Pilihan Program Studi 3</th>
				</tr>
			</thead>
			<tbody>
			@php $no=$offset; $i=0; @endphp
			@foreach($query as $row)
				@php $row = (object) $row; @endphp
				<tr class="setting_pilihan_jurusan_{{ $row->ID ?? '' }}">
						@if($Delete == 'YA')
						<td>
							<div class="checkbox checkbox-info">
								<input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnDelete()" value="{{ $row->ID ?? '' }}" >
								<label for="checkID{{ $i }}"></label>
							</div>
						</td>
						@endif
					<td class="text-center">{{ ++$no }}.</td>
					<td>
					@if($Update == 'YA')
						<a href="{{ url('setting_prodi_tambahan_jurusan/view/' . ($row->ID ?? '')) }}" >
							<ul>
							@php $data_jalur = isset($row->JalurID) ? explode(",",$row->JalurID) : []; @endphp
							@foreach($data_jalur as $index => $id)
								@php $nama = function_exists('get_field') ? get_field($id, "pmb_edu_jalur_pendaftaran","nama") : ''; @endphp
								<li>{{ $nama }}</li>
							@endforeach
							</ul>
						</a>
					@else
							<ul>
								@php $data_jalur = isset($row->JalurID) ? explode(",",$row->JalurID) : []; @endphp
								@foreach($data_jalur as $index => $id)
									@php $nama = function_exists('get_field') ? get_field($id, "pmb_edu_jalur_pendaftaran","nama") : ''; @endphp
									<li>{{ $nama }}</li>
								@endforeach
							</ul>
					@endif
					</td>
					<td>
						<ul>
							@php $prodi_pilihan = isset($row->ProdiID) ? explode(",",$row->ProdiID) : []; @endphp
							@foreach($prodi_pilihan as $index => $id)
								@php 
									$jenjang_id = \DB::table('programstudi')->where('ID', $id)->value('JenjangID');
									$jenjang = function_exists('get_field') ? get_field($jenjang_id,"jenjang") : '';
									$prodi_nama = function_exists('get_field') ? get_field($id, "programstudi") : '';
								@endphp
								<li>{{ $jenjang }} || {{ $prodi_nama }}</li>
							@endforeach
						</ul>
					</td>
					<td>
						<p style="text-align: center;">{{ $row->JumlahProdiTambahan ?? '' }} Pilihan</p>
					</td>
					<td>
						@if(isset($row->ListProdi2) && $row->ListProdi2)
						<ul>
							@php $prodi_pilihan = explode(",",$row->ListProdi2); @endphp
							@foreach($prodi_pilihan as $index => $id)
								@php 
									$jenjang_id = \DB::table('programstudi')->where('ID', $id)->value('JenjangID');
									$jenjang = function_exists('get_field') ? get_field($jenjang_id,"jenjang") : '';
									$prodi_nama = function_exists('get_field') ? get_field($id, "programstudi") : '';
								@endphp
								<li>{{ $jenjang }} || {{ $prodi_nama }}</li>
							@endforeach
						</ul>
						@else
							<p style="text-align: center;">-</p>
						@endif
					</td>
					<td>
						@if(isset($row->ListProdi3) && $row->ListProdi3)
						<ul>
							@php $prodi_pilihan = explode(",",$row->ListProdi3); @endphp
							@foreach($prodi_pilihan as $index => $id)
								@php 
									$jenjang_id = \DB::table('programstudi')->where('ID', $id)->value('JenjangID');
									$jenjang = function_exists('get_field') ? get_field($jenjang_id,"jenjang") : '';
									$prodi_nama = function_exists('get_field') ? get_field($id, "programstudi") : '';
								@endphp
								<li>{{ $jenjang }} || {{ $prodi_nama }}</li>
							@endforeach
						</ul>
						@else
							<p style="text-align: center;">-</p>
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

<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

tablesorter();
$("#f_delete_setting_pilihan_jurusan").submit(function(){
	$.ajax({
		type: "POST",
		url: $("#f_delete_setting_pilihan_jurusan").attr('action'),
		data: $("#f_delete_setting_pilihan_jurusan").serialize(),
		success:function(data){
			$("#hapus").modal("hide");
			setTimeout(function(){
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
		url : "{{ url('welcome/test', ['table' => request()->segment(1), 'field' => 'nama']) }}",
		type: "POST",
		data: $("input:checkbox[name='checkID[]']:checked").serialize(),
		success: function(data){ $('.data_name').html(data); }
	});
});
</script>
