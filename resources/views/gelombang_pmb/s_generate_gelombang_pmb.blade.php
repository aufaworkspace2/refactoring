<div class="row mb-2"><div class="col-md-12">{!! $total_row !!}</div></div>
<form id="f_generate_gelombang_pmb" action="{{ url('gelombang_pmb/proses_generate_gelombang') }}" ><input type="hidden" name="gelombang_id" id="gelombang_id" value="{{ $gelombang_id ?? '' }}">
<div class="table-responsive">
    <table class="table table-bordered mb-0 table-hover">
		<thead class="bg-primary text-white">
			<tr>
				@if($Delete == 'YA')<th><div class="checkbox checkbox-info"><input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.forms.namedItem('f_generate_gelombang_pmb')); show_btnDelete();"><label for="checkAll"></label></div></th>@endif
				<th class="text-center">No.</th><th>Program</th><th>Prodi</th><th>Jalur</th><th>Jenis Pendaftaran</th><th>Biaya Pendaftaran</th><th>Biaya Semester</th><th>Status</th>
			</tr>
		</thead>
		<tbody>
            @php $no=$offset; $i=0; @endphp
            @foreach($query as $row) @php $row = (object) $row; @endphp
				<tr class="gelombang_pmb_{{ $row->id ?? '' }}">
				@if($Delete == 'YA')
				  @if($row->gelombang_detail_id == null)<td><div class="checkbox checkbox-info"><input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnDelete()" value="{{ $row->program_id ?? '' }};{{ $row->prodi_id ?? '' }};{{ $row->jalur ?? '' }};{{ $row->jenis_pendaftaran ?? '' }};{{ $row->biaya_semester_satu_id ?? '' }};{{ $row->biaya_pendaftaran ?? '' }}" ><label for="checkID{{ $i }}"></label></div></td>@php $i++; @endphp
				  @else<td class="text-center">-</td>@endif
				  @endif
					<td class="text-center align-midle">{{ ++$no }}.</td>
					<td>{{ $program[$row->program_id] ?? '' }}</td>
					<td>{{ $prodi[$row->prodi_id] ?? '' }}</td>
					<td>{{ $jalur[$row->jalur] ?? '' }}</td>
					<td>{{ $jenisPendaftaran[$row->jenis_pendaftaran] ?? '' }}</td>
					<td>Rp.{{ number_format($biaya[$row->biaya_pendaftaran] ?? 0, 0, ',', '.') }}</td>
					<td>Rp.{{ number_format($biayaSemester[$row->biaya_semester_satu_id] ?? 0, 0, ',', '.') }}</td>
					<td>@if($row->gelombang_detail_id != null)<span class="badge badge-success text-white">Periode sudah dibuat</span>@else<span class="badge badge-danger text-white">Periode belum dibuat</span>@endif</td>
				</tr>
				@endforeach
				@if(count($query) == 0)<tr><td colspan="12" class="text-center align-midle">Belum Ada Periode Detail yang di setting</td></tr>@endif
		</tbody>
	</table>
</div>
<div class="row"><div class="col-md-12">{!! $link !!}</div></div>
</form>

<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
function checkall(chkAll,checkid) { if (checkid != null) { if (checkid.length == null) checkid.checked = chkAll.checked; else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked; $("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger'); $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger'); } }
window.show_btnDelete = function(){ i=0; hasil = false; while(document.getElementsByName('checkID[]').length > i){ var el = document.getElementById('checkID'+i); if(el && el.checked){ hasil = true; } i++; } if(hasil == true) { $('#btnProses').removeAttr('disabled'); $('#btnProses').attr('href', '#proses'); } else { $('#btnProses').attr('disabled','disabled'); $('#btnProses').attr('href','#'); } }
show_btnDelete();
$("input:checkbox[name='checkID[]']").click(function(){ if(this.checked == true){ $(this).parents('tr').addClass('table-danger'); } else { $(this).parents('tr').removeClass('table-danger'); } });
$('#btnProses').click(function(){ var checkCount = $("input:checkbox[name='checkID[]']:checked").length; $('.data_name').html(checkCount + ' data akan diproses'); });
$('#btnConfirmProses').click(function(){ $('#proses').modal('hide'); $.ajax({ type: "POST", url: "{{ url('gelombang_pmb/proses_generate_gelombang') }}", data: $("#f_generate_gelombang_pmb").serialize(), dataType: "JSON", beforeSend: function() { silahkantunggu(); }, success: function(respond){ if(respond.status == 1){ berhasil(); alertsuccess(); filter(); }else{ alertfail(); berhasil(); } }, error: function(data){ alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.'); } }); });
</script>
