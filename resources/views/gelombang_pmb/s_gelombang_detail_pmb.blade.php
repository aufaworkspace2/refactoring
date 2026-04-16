<div class="row mb-2"><div class="col-md-12">{!! $total_row !!}</div></div>
<form id="f_delete_gelombang_pmb" action="{{ url('gelombang_pmb/delete_detail') }}" >
<div class="table-responsive">
    <table class="table table-bordered mb-0 table-hover tablesorter">
		<thead class="bg-primary text-white">
			<tr>
				@if($Delete == 'YA')<th><div class="checkbox checkbox-info"><input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.forms.namedItem('f_delete_gelombang_pmb')); show_btnDelete();"><label for="checkAll"></label></div></th>@endif
				<th class="text-center">No.</th><th>Biaya</th><th>Pilihan Pendaftaran</th><th>Program</th><th>Prodi</th><th>Jalur</th><th>Jenis Pendaftaran</th><th>Tanggal Buka Pendaftaran</th>
			</tr>
		</thead>
		<tbody>
			@php $gelombang = \DB::table('pmb_tbl_gelombang')->where('id', $gelombang_id)->first(); $nama_tahun_gelombang = function_exists('get_field') && $gelombang ? get_field($gelombang->tahun_id,'tahun') : ''; @endphp
			<tr bgcolor="lightgoldenrodyellow"><td colspan="10" class="text-center align-midle"><b>{{ $gelombang->nama ?? '' }} || Tahun Akademik {{ $nama_tahun_gelombang }}</b></td></tr>
            @php $no=$offset; $i=0; @endphp
            @foreach($query as $row)
				@php $row = (object) $row; $mahasiswa = \DB::table('mahasiswa')->where('gelombang_detail_pmb', $row->id)->first(); @endphp
				<tr class="gelombang_pmb_{{ $row->id ?? '' }}">
				@if($Delete == 'YA')
				  @if(empty($mahasiswa))<td><div class="checkbox checkbox-info"><input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnDelete()" value="{{ $row->id ?? '' }}" ><label for="checkID{{ $i }}"></label></div></td>@php $i++; @endphp
				  @else<td class="text-center">-</td>@endif
				  @endif
					<td class="text-center align-midle">{{ ++$no }}.</td>
					<td>@if($Update == 'YA')<a href="{{ url('gelombang_pmb/view_detail/' . ($row->id ?? '')) }}?gelombang_id={{ $row->gelombang_id ?? '' }}" >{{ number_format($row->biaya ?? 0, 0, ',', '.') }}</a>@else{{ number_format($row->biaya ?? 0, 0, ',', '.') }}@endif</td>
					<td>@php $pilihan_pendaftaran_nama = function_exists('get_field') && isset($row->pilihan_pendaftaran_id) ? get_field($row->pilihan_pendaftaran_id,'pmb_pilihan_pendaftaran','nama') : ''; @endphp{{ $pilihan_pendaftaran_nama }}</td>
					<td>@php if(isset($row->program_id) && $row->program_id) { echo "<ol>"; $program = explode(",", $row->program_id); foreach($program as $p) { $prog_nama = function_exists('get_field') ? get_field($p,'program') : ''; echo "<li>".$prog_nama."</li>"; } echo "</ol>"; } @endphp</td>
					<td>@php if(isset($row->prodi_id) && $row->prodi_id) { $p = $row->prodi_id; $prodi_arr = \DB::table('programstudi')->where('ID', $p)->first(); $jenjang = $prodi_arr && function_exists('get_field') ? get_field($prodi_arr->JenjangID,'jenjang') : ''; echo "".$jenjang."-".($prodi_arr->Nama ?? ''); } @endphp</td>
					<td>@php if(isset($row->jalur) && $row->jalur) { echo "<ol>"; $jalur = explode(",", $row->jalur); foreach($jalur as $p) { $jalur_nama = function_exists('get_field') ? get_field($p,'pmb_edu_jalur_pendaftaran') : ''; echo "<li>".$jalur_nama."</li>"; } echo "</ol>"; } @endphp</td>
					<td>@php if(isset($row->jenis_pendaftaran) && $row->jenis_pendaftaran) { echo "<ol>"; $jenis_pendaftaran = explode(",", $row->jenis_pendaftaran); foreach($jenis_pendaftaran as $p) { $jenis_nama = function_exists('get_field') ? get_field($p,'jenis_pendaftaran') : ''; echo "<li>".$jenis_nama."</li>"; } echo "</ol>"; } @endphp</td>
					<td class="text-center align-midle">
						@php 
							$date_start = isset($row->date_start) ? $row->date_start : ''; 
							$date_end = isset($row->date_end) ? $row->date_end : '';
							$start_display = !empty($date_start) ? date('d/m/Y', strtotime($date_start)) : '-';
							$end_display = !empty($date_end) ? date('d/m/Y', strtotime($date_end)) : '-';
							$status_text = 'Sudah Selesai';
							$status_color = 'red';
							if (!empty($date_start) && !empty($date_end)) {
								if ($date_start > date('Y-m-d')){ 
									$status_text = 'Belum Dimulai';
									$status_color = 'grey';
								} elseif ($date_start <= date('Y-m-d') && $date_end >= date('Y-m-d')){ 
									$status_text = 'Sedang Berlangsung';
									$status_color = 'green';
								}
							}
						@endphp
						{{ $start_display }} <br>s/d <br>{{ $end_display }}<br>
						<p align="center" style="color:{{ $status_color }}">{{ $status_text }}</p>
					</td>
				</tr>
				@endforeach
				@if(count($query) == 0)<tr><td colspan="10" class="text-center align-midle">Belum Ada Gelombang Detail yang di setting</td></tr>@endif
		</tbody>
	</table>
</div>
<div id="hapus" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="hapus" aria-hidden="true">
	<div class="modal-dialog"><div class="modal-content">
		<div class="modal-header"><h4 class="modal-title" id="hapus">{{ __('app.confirm_header') }}</h4><button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button></div>
		<div class="modal-body"><p>{{ __('app.confirm_message') }}</p><p class="data_name"></p></div>
		<div class="modal-footer"><button type="submit" class="btn btn-danger waves-effect" >{{ __('app.delete') }}</button><button type="button" class="btn btn-primary waves-effect waves-light" data-dismiss="modal">{{ __('app.close') }}</button></div>
	</div></div>
</div>
<div class="row"><div class="col-md-12">{!! $link !!}</div></div>
</form>

<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

var gelombang_id = '{{ $gelombang_id ?? '' }}';

function filter(url) {
	if(url == null) url = "{{ url('gelombang_pmb/search_detail') }}";
	$.ajax({ type: "POST", url: url, data: { gelombang_id : gelombang_id, keyword : $('#keyword').val() }, success: function(data) { $("#konten").html(data); } });
	return false;
}

tablesorter();
$("#f_delete_gelombang_pmb").submit(function(){
	$.ajax({ type: "POST", url: $("#f_delete_gelombang_pmb").attr('action'), data: $("#f_delete_gelombang_pmb").serialize(),
		success:function(data){ $("#hapus").modal("hide"); setTimeout(function(){ $("body").removeClass("modal-open"); $(".modal-backdrop").remove(); }, 300); filter(); $(".alert-success").show(); $(".alert-success-content").html("{{ __('app.alert-success-delete') }}"); window.setTimeout(function() { $(".alert-success").slideUp(); }, 10000); },
		error: function(data){ $(".alert-error").show(); $(".alert-error-content").html("{{ __('app.alert-error-delete') }}"); window.setTimeout(function() { $(".alert-error").slideUp( "slow" ); }, 6000); }
	}); return false;
});
window.show_btnDelete = function(){ i=0; hasil = false; while(document.getElementsByName('checkID[]').length > i){ var el = document.getElementById('checkID'+i); if(el && el.checked){ hasil = true; } i++; } if(hasil == true) { $('#btnDelete').removeAttr('disabled'); $('#btnDelete').attr('href', '#hapus'); } else { $('#btnDelete').attr('disabled','disabled'); $('#btnDelete').attr('href','#'); } }
show_btnDelete();
$("input:checkbox[name='checkID[]']").click(function(){ if(this.checked == true){ $(this).parents('tr').addClass('checked_tabel'); } else { $(this).parents('tr').removeClass('checked_tabel'); } });
$('#btnDelete').click(function(){ $.ajax({ url : "{{ url('welcome/test') }}/?table=gelombang_pmb&field=nama", type: "POST", data: { checkID: $("input:checkbox[name='checkID[]']:checked").map(function(){ return this.value; }).get(), _token: "{{ csrf_token() }}" }, success: function(data){ $('.data_name').html(data); } }); });
</script>
