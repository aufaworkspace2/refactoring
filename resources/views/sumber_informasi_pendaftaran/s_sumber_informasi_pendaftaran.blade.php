<div class="row mb-2"><div class="col-md-12">{!! $total_row !!}</div></div>
<form id="f_delete_jalur_pendaftaran_pmb" action="{{ url('sumber_informasi_pendaftaran/delete') }}">
<div class="table-responsive">
	<table class="table table-bordered mb-0 table-hover tablesorter">
		<thead class="bg-primary text-white">
			<tr>
				@if($Delete == 'YA')<th style="width: 2%;"><div class="checkbox checkbox-info"><input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.forms.namedItem('f_delete_jalur_pendaftaran_pmb')); show_btnDelete();"><label for="checkAll"></label></div></th>@endif
				<th style="width: 2%;" class="text-center">No.</th><th>Nama</th>
			</tr>
		</thead>
		<tbody>
			@php $no = $offset; $i = 0; @endphp
			@foreach($query as $row) @php $row = (object) $row; @endphp
			<tr class="sumber_informasi_pendaftaran_{{ $row->id_ref_daftar ?? '' }}">
				@if($Delete == 'YA')
					@if(($row->jumlah_pilihan_pendaftaran ?? 0) == 0)<td style="width: 2%;" class="align-middle"><div class="checkbox checkbox-info"><input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnDelete()" value="{{ $row->id_ref_daftar ?? '' }}"><label for="checkID{{ $i }}"></label></div></td>@php $i++; @endphp
					@else<td>-</td>@endif
				@endif
				<td style="width: 2%;" class="text-center">{{ ++$no }}.</td>
				<td>@if($Update == 'YA')<a href="{{ url('sumber_informasi_pendaftaran/view/' . ($row->id_ref_daftar ?? '')) }}">{{ $row->nama_ref ?? '' }}</a>@else{{ $row->nama_ref ?? '' }}@endif</td>
			</tr>
			@endforeach
		</tbody>
	</table>
</div>
<div id="hapus" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="hapus" aria-hidden="true">
	<div class="modal-dialog"><div class="modal-content">
		<div class="modal-header"><h4 class="modal-title" id="hapus">{{ __('app.confirm_header') }}</h4><button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button></div>
		<div class="modal-body"><p>{{ __('app.confirm_message') }}</p><p class="data_name"></p></div>
		<div class="modal-footer"><button type="submit" class="btn btn-danger waves-effect">{{ __('app.delete') }}</button><button type="button" class="btn btn-primary waves-effect waves-light" data-dismiss="modal">{{ __('app.close') }}</button></div>
	</div></div>
</div>
<div class="row"><div class="col-md-12">{!! $link !!}</div></div>
</form>

<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
tablesorter();
$("#f_delete_jalur_pendaftaran_pmb").submit(function() {
	$.ajax({ type: "POST", url: $("#f_delete_jalur_pendaftaran_pmb").attr('action'), data: $("#f_delete_jalur_pendaftaran_pmb").serialize(),
		success: function(data) { $("#hapus").modal("hide"); setTimeout(function(){ $("body").removeClass("modal-open"); $(".modal-backdrop").remove(); }, 300); filter(); $(".alert-success").show(); $(".alert-success-content").html("{{ __('app.alert-success-delete') }}"); window.setTimeout(function() { $(".alert-success").slideUp(); }, 10000); },
		error: function(data) { $(".alert-error").show(); $(".alert-error-content").html("{{ __('app.alert-error-delete') }}"); window.setTimeout(function() { $(".alert-error").slideUp("slow"); }, 6000); }
	}); return false;
});
window.show_btnDelete = function() { i=0; hasil=false; while(document.getElementsByName('checkID[]').length > i) { var el = document.getElementById('checkID'+i); if(el && el.checked) { hasil = true; } i++; } if(hasil == true) { $('#btnDelete').removeAttr('disabled'); $('#btnDelete').attr('href', '#hapus'); } else { $('#btnDelete').attr('disabled','disabled'); $('#btnDelete').attr('href','#'); $('#btnDelete').attr('title', '{{ __('app.Pilih dahulu data yang akan di hapus') }}'); } }
show_btnDelete();
$("input:checkbox[name='checkID[]']").click(function() { if(this.checked == true) { $(this).parents('tr').addClass('table-danger'); } else { $(this).parents('tr').removeClass('table-danger'); } });
</script>
