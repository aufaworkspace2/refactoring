<div class="row mb-3">
	<div class="col-md-12">
		{!! $total_row ?? '' !!}
	</div>
</div>
<div class="table-responsive mb-2">
	<table class="table table-bordered table-hover" id="dataTable1">
		<thead class="bg-primary text-white">
			<tr>
				<th width="1%" class="text-center"><input type="checkbox" id="checkAll" /></th>
				<th width="3%" class="text-center">No.</th>
				<th width="8%" class="text-center">Kode</th>
				<th width="8%" class="text-center">NIM</th>
				<th width="20%" class="text-center">Nama</th>
				<th colspan="2" class="text-center">Nilai PT Asal</th>
				<th colspan="2" class="text-center">Konversi Nilai PT Baru (diakui)</th>
				<th width="12%" class="text-center">Status</th>
			</tr>
			<tr>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th class="text-center">Total MK</th>
				<th class="text-center">Total SKS</th>
				<th class="text-center">Total MK</th>
				<th class="text-center">Total SKS</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			@php $no = ($offset ?? 0) + 1; @endphp
			@foreach($query ?? [] as $row)
			<tr class="konversi_{{ $row->ID }}">
				<td class="text-center"><input type="checkbox" name="checkID[]" value="{{ $row->ID }}" /></td>
				<td class="text-center">{{ $no++ }}.</td>
				<td class="text-center">{{ $row->KodeKonversi ?? '' }}</td>
				<td class="text-center">{{ $row->NPM ?? '' }}</td>
				<td>{{ $row->Nama ?? '' }}</td>
				<td class="text-center">{{ $row->TotalMKAsal ?? 0 }}</td>
				<td class="text-center">{{ $row->TotalSKSAsal ?? 0 }}</td>
				<td class="text-center">{{ $row->TotalMKTujuan ?? 0 }}</td>
				<td class="text-center">{{ $row->TotalSKSTujuan ?? 0 }}</td>
				<td class="text-center">
					@if($row->statuskonversi == 1)
						<span class="badge badge-success">Sudah di Konversi</span><br/>
						<button type="button" onclick="batalKonversi({{ $row->ID }})" class="btn btn-danger btn-sm mt-1">
							Batalkan
						</button>
					@else
						<button type="button" onclick="genKonversi({{ $row->ID }})" class="btn btn-primary btn-sm">
							Konversikan
						</button>
					@endif
				</td>
			</tr>
			@endforeach
			@if(count($query ?? []) == 0)
			<tr>
				<td colspan="10" class="text-center">Data tidak ditemukan</td>
			</tr>
			@endif
		</tbody>
	</table>
</div>
<div class="row mt-3">
	<div class="col-md-12">
		{!! $link ?? '' !!}
	</div>
</div>
