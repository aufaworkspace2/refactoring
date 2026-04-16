<div class="table-responsive">
	<table class="table table-hover table-bordered">
		<thead class="bg-primary text-white">
		<tr>
			<th class="text-center">No</th>
			<th class="text-center">Tahun Masuk</th>
			<th class="text-center">Program</th>
			<th class="text-center">Program Studi</th>
			<th class="text-center">IPS</th>
			<th class="text-center">IPK</th>
		</tr>
		</thead>
		<tbody>
		@if(count($query) > 0)
			@foreach($query as $index => $row)
			<tr>
				<td class="text-center">{{ $index + 1 }}</td>
				<td class="text-center">{{ $row['tahunMasuk'] ?? '' }}</td>
				<td>{{ $row['namaProgram'] ?? '' }}</td>
				<td>{{ $row['namaProdi'] ?? '' }}</td>
				<td class="text-center">{{ $row['ips'] ?? 0 }}</td>
				<td class="text-center">{{ $row['ipk'] ?? 0 }}</td>
			</tr>
			@endforeach
		@else
		<tr>
			<td colspan="6" class="text-center">Mohon maaf data tidak ditemukan !</td>
		</tr>
		@endif
		</tbody>
	</table>
</div>
