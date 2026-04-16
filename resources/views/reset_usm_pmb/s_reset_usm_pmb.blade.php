<div class="row mb-2">
	<div class="col-md-12">
		{!! $total_row !!}
	</div>
</div>
<form id="f_delete_reset_usm" >
	<div class="table-responsive">
		<table class="table table-bordered mb-0 table-hover tablesorter">
			<thead class="bg-primary text-white">
				<tr>
					@if($Delete == 'YA')<th width="2%"><div class="checkbox checkbox-info"><input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.forms.namedItem('f_delete_reset_usm')); show_btnDelete();"><label for="checkAll"></label></div></th>@endif
					<th class="text-center" width="2%">No.</th>
					<th>Nama</th>
					<th>No. Ujian</th>
					<th>Gelombang</th>
					<th>Program</th>
					<th>Prodi</th>
					<th>Status Lulus</th>
					<th>Jumlah Ujian</th>
					<th>Jumlah Selesai</th>
				</tr>
			</thead>
			<tbody>
			@php $no=$offset; $i=0; @endphp
			@foreach($query as $row)
				<tr class="reset_usm_{{ $row->ID ?? '' }}">
					@if($Delete == 'YA')
					<td>
						@if(!empty($row->id_hasil_test))
						<div class="checkbox checkbox-info">
							<input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnDelete()" value="{{ $row->ID ?? '' }}" >
							<label for="checkID{{ $i }}"></label>
						</div>
						@php $i++; @endphp
						@else
						-
						@endif
					</td>
					@endif
					<td class="text-center">{{ ++$no }}.</td>
					<td>{{ $row->Nama ?? '' }}</td>
					<td>{{ $row->noujian_pmb ?? '' }}</td>
					<td>{{ $row->gelombangNama ?? '' }}</td>
					<td>{{ $row->programNama ?? '' }}</td>
					<td>{{ $row->prodiNama ?? '' }}</td>
					<td>{!! $row->statuslulus_str ?? '' !!}</td>
					<td class="text-center">{{ $row->jumlahUjian ?? 0 }}</td>
					<td class="text-center">{{ $row->jumlahSelesai ?? 0 }}</td>
				</tr>
			@endforeach
			</tbody>
		</table>
	</div>
	<div id="hapus" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="hapus" aria-hidden="true">
		<div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h4 class="modal-title" id="hapus">{{ __('app.confirm_header') }}</h4><button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button></div><div class="modal-body"><p>{{ __('app.confirm_message') }}</p><p class="data_name"></p></div><div class="modal-footer"><button type="button" onclick="resetnilai()" class="btn btn-danger waves-effect" >Reset</button><button type="button" class="btn btn-primary waves-effect waves-light" data-dismiss="modal">{{ __('app.close') }}</button></div></div></div>
	</div>
	<div class="row"><div class="col-md-12">{!! $link !!}</div></div>
</form>

<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
window.show_btnDelete = function(){ i=0; hasil = false; while(document.getElementsByName('checkID[]').length > i){ var el = document.getElementById('checkID'+i); if(el && el.checked){ hasil = true; } i++; } if(hasil == true){ $('#btnDelete').removeAttr('disabled'); $('#btnDelete').attr('href','#hapus'); }else{ $('#btnDelete').attr('disabled','disabled'); $('#btnDelete').attr('href','#'); $('#btnDelete').attr('title', '{{ __('app.Pilih dahulu data yang akan di hapus') }}'); } }
show_btnDelete();
window.checkall = function(chkAll,checkid){ if (checkid != null){ if (checkid.length == null) checkid.checked = chkAll.checked; else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked; $("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger'); $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger'); } }
function resetnilai(){ $.ajax({ type: "POST", url: "{{ url('reset_usm_pmb/save') }}", data: $("#f_delete_reset_usm").serialize(), success: function(data){ $("#hapus").modal("hide"); $("body").removeClass("modal-open"); $(".modal-backdrop").remove(); filter(); $(".alert-success").show(); $(".alert-success-content").html("Data berhasil direset"); window.setTimeout(function() { $(".alert-success").slideUp(); }, 10000); } }); }
</script>
