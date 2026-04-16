<div class="row mb-2">
	<div class="col-md-12">
		{!! $total_row !!}
	</div>
</div>
<div class="table-responsive">
    <table class="table table-bordered mb-0 table-hover tablesorter">
		<thead class="bg-primary text-white">
			<tr>
				@if($Delete == 'YA')<th><div class="checkbox checkbox-info"><input type="checkbox" name="checkAll" id="checkAll" onclick="checkall(this,document.forms.namedItem('f_delete_kanal')); show_btnDelete();"><label for="checkAll"></label></div></th>@endif
				<th class="align-midle text-center">No.</th>
				<th class="align-midle text-center">Nama Menu</th>
				<th class="align-midle text-center">Link</th>
				<th class="align-midle text-center">Icon</th>
				<th class="align-midle text-center">URL</th>
				<th class="align-midle text-center">Mega Menu</th>
				<th class="align-midle text-center">Status</th>
			</tr>
		</thead>
		<tbody>
			@php $no=$offset; $i=0; @endphp
			@foreach($query as $row)
				@php $row = (object) $row; @endphp
				<tr class="kanal_{{ $row->id ?? '' }}">
					@if($Delete == 'YA')<td><div class="checkbox checkbox-info"><input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnDelete()" value="{{ $row->id ?? '' }}"><label for="checkID{{ $i }}"></label></div></td>@endif
					<td class="align-midle text-center">{{ ++$no }}.</td>
					<td class="align-midle text-center">@if($Update == 'YA')<a href="{{ url('menu_pmb/view/' . ($row->id ?? '')) }}">{{ $row->namamenu ?? '' }}</a>@else{{ $row->namamenu ?? '' }}@endif</td>
					<td class="align-midle text-center">{{ $row->link ?? '' }}</td>
					<td class="align-midle text-center">{{ $row->icon ?? '' }}</td>
					<td class="align-midle text-center">{{ $row->url ?? '' }}</td>
					<td class="align-midle text-center">{{ ($row->megamenu ?? 0) == 1 ? 'Ya' : 'Tidak' }}</td>
					<td class="align-midle text-center">{{ ($row->status ?? 0) == 1 ? 'Aktif' : 'Tidak Aktif' }}</td>
				</tr>
				@php $i++; @endphp
			@endforeach
		</tbody>
	</table>
</div>
<div id="hapus" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="hapus" aria-hidden="true">
	<div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h4 class="modal-title" id="hapus">{{ __('app.confirm_header') }}</h4><button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button></div><div class="modal-body"><p>{{ __('app.confirm_message') }}</p><p class="data_name"></p></div><div class="modal-footer"><button type="button" onclick="hapuskanal()" class="btn btn-danger waves-effect" >{{ __('app.delete') }}</button><button type="button" class="btn btn-primary waves-effect waves-light" data-dismiss="modal">{{ __('app.close') }}</button></div></div></div>
</div>
<div class="row"><div class="col-md-12">{!! $link !!}</div></div>

<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
window.show_btnDelete = function(){ i=0; hasil=false; while(document.getElementsByName('checkID[]').length > i){ var el = document.getElementById('checkID'+i); if(el && el.checked){ hasil=true; } i++; } if(hasil==true){ $('#btnDelete').removeAttr('disabled'); $('#btnDelete').attr('href','#hapus'); }else{ $('#btnDelete').attr('disabled','disabled'); $('#btnDelete').attr('href','#'); } }
show_btnDelete();
function checkall(chkAll,checkid){ if(checkid!=null){ if(checkid.length==null)checkid.checked=chkAll.checked; else for(i=0;i<checkid.length;i++)checkid[i].checked=chkAll.checked; $("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger'); $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger'); } }
function hapuskanal(){ $.ajax({ type: "POST", url: "{{ url('menu_pmb/delete') }}", data: $("#f_delete_kanal").serialize(), success: function(data) { $("#hapus").modal("hide"); setTimeout(function(){ $("body").removeClass("modal-open"); $(".modal-backdrop").remove(); }, 300); filter(); $(".alert-success").show(); $(".alert-success-content").html("{{ __('app.alert-success-delete') }}"); window.setTimeout(function() { $(".alert-success").slideUp(); }, 10000); } }); }
</script>
