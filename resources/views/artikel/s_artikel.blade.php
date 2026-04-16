<div class="row mb-2">
	<div class="col-md-12">
		{!! $total_row !!}
	</div>
</div>
<div class="table-responsive">
    <table class="table table-bordered mb-0 table-hover tablesorter">
		<thead class="bg-primary text-white">
			<tr>
				@if($Delete == 'YA')
				<th>
					<div class="checkbox checkbox-info">
						<input type="checkbox" name="checkAll" id="checkAll" onclick="checkall(this,document.forms.namedItem('f_delete_artikel')); show_btnDelete();">
						<label for="checkAll"></label>
					</div>
				</th>
				@endif
				<th class="align-midle text-center" width="2%">No.</th>
				<th class="align-midle text-center" width="8%">Gambar</th>
				<th class="align-midle text-center" width="8%">Judul</th>
				<th class="align-midle text-center" width="42%">Isi</th>
				<th class="align-midle text-center" width="10%">Publish</th>
				<th class="align-midle text-center" width="30%">Status</th>
			</tr>
		</thead>
		<tbody>
			@php $no=$offset; $i=0; @endphp
			@foreach($query as $row)
				@php $row = (object) $row; @endphp
				<tr class="agenda_{{ $row->id ?? '' }}">
					@if($Delete == 'YA')
					<td>
						<div class="checkbox checkbox-info">
							<input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnDelete()" value="{{ $row->id ?? '' }}" >
							<label for="checkID{{ $i }}"></label>
						</div>
					</td>
					@endif
					<td class="align-midle text-center">{{ ++$no }}.</td>
					<td class="align-midle text-center">
						@if(empty($row->gambar))
							No Image
						@else
							<img width="150px" src="{{ asset('pmb/artikel/' . ($row->gambar ?? '')) }}">
						@endif
					</td>
					<td class="align-midle text-center">
					@if($Update == 'YA')
					<a href="{{ url('artikel_pmb/view/' . ($row->id ?? '')) }}" >{{ $row->judul ?? '' }}</a>
					@else
					{{ $row->judul ?? '' }}
					@endif
					</td>
					<td class="align-midle text-center">{{ Str::limit(strip_tags($row->isi ?? ''), 200) }} [...]</td>
					<td class="align-midle text-center">{{ ($row->publish ?? 0) == 1 ? 'Sudah Dipublish' : 'Belum Dipublish' }}</td>
					@php
						if (($row->status ?? 0) == '1') { $status = "Artikel"; }
						elseif (($row->status ?? 0) == '2') { $status = "Berita"; }
						elseif (($row->status ?? 0) == '3') { $status = "Pengumuman"; }
						else { $status = "Tidak Ada Keterangan"; }
					@endphp
					<td class="align-midle text-center">{{ $status }}</td>
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
				<button type="button" onclick="hapusartikel()" class="btn btn-danger waves-effect" >{{ __('app.delete') }}</button>
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

<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

window.show_btnDelete = function(){
	i=0; hasil = false;
	while(document.getElementsByName('checkID[]').length > i) {
		var el = document.getElementById('checkID'+i);
		if(el && el.checked) { hasil = true; }
		i++;
	}
	if(hasil == true) {
		$('#btnDelete').removeAttr('disabled');
		$('#btnDelete').removeAttr('title');
		$('#btnDelete').attr('href','#hapus');
	} else {
		$('#btnDelete').attr('disabled','disabled');
		$('#btnDelete').attr('href','#');
		$('#btnDelete').attr('title', '{{ __('app.Pilih dahulu data yang akan di hapus') }}');
	}
}
show_btnDelete();

window.checkall = function(chkAll,checkid) {
	if (checkid != null) {
		if (checkid.length == null) checkid.checked = chkAll.checked;
		else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;
		$("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
		$("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
	}
}

function hapusartikel() {
	$.ajax({ type: "POST", url: "{{ url('artikel_pmb/delete') }}", data: $("#f_delete_artikel").serialize(), success: function(data) { $("#hapus").modal("hide"); setTimeout(function(){ $("body").removeClass("modal-open"); $(".modal-backdrop").remove(); }, 300); filter(); $(".alert-success").show(); $(".alert-success-content").html("{{ __('app.alert-success-delete') }}"); window.setTimeout(function() { $(".alert-success").slideUp(); }, 10000); } });
}

function filter(url) {
	if(url == null) url = "{{ url('artikel_pmb/search') }}";
	$.ajax({ type: "POST", url: url, data: { keyword : $(".keyword").val() }, success: function(data) { $("#konten").html(data); } });
	return false;
}
</script>
