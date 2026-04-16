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
						<input type="checkbox" name="checkAll" id="checkAll" onclick="checkall(this,document.forms.namedItem('f_delete_agenda')); show_btnDelete();">
						<label for="checkAll"></label>
					</div>
				</th>
				@endif
				<th class="align-midle text-center">No.</th>
				<th class="align-midle text-center">Gambar</th>
				<th class="align-midle text-center">Judul</th>
				<th class="align-midle text-center">Isi</th>
				<th class="align-midle text-center">Keterangan</th>
				<th class="align-midle text-center">Publish</th>
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
							<img width="150px" src="{{ asset('pmb/agenda/' . ($row->gambar ?? '')) }}">
						@endif
					</td>
					<td class="align-midle text-center">
					@if($Update == 'YA')
					<a href="{{ url('agenda_pmb/view/' . ($row->id ?? '')) }}" >{{ $row->judul ?? '' }}</a>
					@else
					{{ $row->judul ?? '' }}
					@endif
					</td>
					<td class="align-midle text-center">{{ Str::limit(strip_tags($row->isi ?? ''), 300) }}</td>
					<td class="left">
						<strong>Tanggal : </strong>{{ $row->tanggal ?? '' }}<br>
						<strong>Waktu : </strong>{{ $row->waktu ?? '' }}<br>
						<strong>Tempat : </strong>{{ $row->tempat ?? '' }}
					</td>
					<td class="align-midle text-center">{{ ($row->publish ?? 0) == 1 ? 'Sudah Dipublish' : 'Belum Dipublish' }}</td>
				</tr>
				@php $i++; @endphp
			@endforeach
		</tbody>
	</table>
</div>
<div class="row">
	<div class="col-md-12">
		{!! $link !!}
	</div>
</div>

<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

// Global function for show_btnDelete
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

// Global function for checkall
window.checkall = function(chkAll,checkid) {
	if (checkid != null) {
		if (checkid.length == null) checkid.checked = chkAll.checked;
		else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;
		$("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
		$("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
	}
}
</script>
	