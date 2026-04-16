<div class="row mb-2">
	<div class="col-md-12">
		{!! $total_row !!}
	</div>
</div>
<form id="f_delete_jadwal" >
	<div class="table-responsive">
		<table class="table table-bordered mb-0 table-hover tablesorter">
			<thead class="bg-primary text-white">
				<tr>
					@if($Delete == 'YA')<th width="2%"><div class="checkbox checkbox-info"><input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.forms.namedItem('f_delete_jadwal')); show_btnDelete();"><label for="checkAll"></label></div></th>@endif
					<th class="text-center" width="2%">No.</th>
					<th>Gelombang</th>
					<th>Kode Jadwal</th>
					<th>Tanggal Ujian</th>
					<th>Jam Ujian</th>
					<th>Ruangan</th>
					<th>Jenis Ujian</th>
					<th>Aksi</th>
				</tr>
			</thead>
			<tbody>
			@php $no=$offset; $i=0; @endphp
			@foreach($query as $row)
				@php 
					$row = (object) $row;
					$jadwalusm_detail_count = \DB::table('pmb_edu_jadwalusm_detail')->where('jadwalusm_id', $row->id ?? '')->count();
				@endphp
				<tr class="jadwal_usm_pmb_{{ $row->id ?? '' }}">
					@if($Delete == 'YA')<td>@if($jadwalusm_detail_count == 0)<div class="checkbox checkbox-info"><input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnDelete()" value="{{ $row->id ?? '' }}"><label for="checkID{{ $i }}"></label></div>@else-@endif</td>@endif
					<td class="text-center">{{ ++$no }}.</td>
					<td>{{ $row->namagelombang ?? '' }}</td>
					<td>{{ $row->kode ?? '' }}</td>
					<td>{{ $row->tgl_ujian ? date('d/m/Y', strtotime($row->tgl_ujian)) : '' }}</td>
					<td>{{ $row->jam_mulai ?? '' }} - {{ $row->jam_selesai ?? '' }}</td>
					<td>{!! $row->ruangan ?? '' !!}</td>
					<td>{!! $row->jenis_ujin_text ?? '' !!}</td>
					<td><a href="{{ url('jadwal_usm_pmb/detail') }}?jadwalusm_id={{ $row->id ?? '' }}" class="btn btn-sm btn-primary">Detail</a></td>
				</tr>
				@php $i++; @endphp
			@endforeach
			</tbody>
		</table>
	</div>
	<div id="hapus" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="hapus" aria-hidden="true">
		<div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h4 class="modal-title" id="hapus">{{ __('app.confirm_header') }}</h4><button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button></div><div class="modal-body"><p>{{ __('app.confirm_message') }}</p><p class="data_name"></p></div><div class="modal-footer"><button type="button" onclick="hapusdata()" class="btn btn-danger waves-effect" >{{ __('app.delete') }}</button><button type="button" class="btn btn-primary waves-effect waves-light" data-dismiss="modal">{{ __('app.close') }}</button></div></div></div>
	</div>
	<div class="row"><div class="col-md-12">{!! $link !!}</div></div>
</form>

<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
window.show_btnDelete = function(){ i=0; hasil = false; while(document.getElementsByName('checkID[]').length > i){ var el = document.getElementById('checkID'+i); if(el && el.checked){ hasil = true; } i++; } if(hasil == true){ $('#btnDelete').removeAttr('disabled'); $('#btnDelete').attr('href','#hapus'); }else{ $('#btnDelete').attr('disabled','disabled'); $('#btnDelete').attr('href','#'); } }
show_btnDelete();
function checkall(chkAll,checkid){ if (checkid != null){ if (checkid.length == null) checkid.checked = chkAll.checked; else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked; $("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger'); $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger'); } }
function hapusdata(){ $.ajax({ type: "POST", url: "{{ url('jadwal_usm_pmb/delete') }}", data: $("#f_delete_jadwal").serialize(), success: function(data){ var res = JSON.parse(data); $("#hapus").modal("hide"); setTimeout(function(){ $("body").removeClass("modal-open"); $(".modal-backdrop").remove(); }, 300); filter(); if(res.status==1){ $(".alert-success").show(); $(".alert-success-content").html(res.message); }else{ $(".alert-error").show(); $(".alert-error-content").html(res.message); } } }); }
</script>
