<div class="table-responsive">
	<table class="table table-hover table-bordered">
		<thead class="bg-primary text-white">
		<tr>
			<th class="text-center">No</th>
			<th class="text-center">NPM</th>
			<th class="text-center">Nama</th>
			<th class="text-center">SEMESTER</th>
			<th class="text-center">SKS SEMESTER</th>
			<th class="text-center">IPS</th>
			<th class="text-center">TOTAL SKS</th>
			<th class="text-center">IPK</th>
		</tr>
		</thead>
		<tbody>
		@if(count($query) > 0)
			@foreach($query as $index => $row)
			<tr>
				<td class="text-center">{{ $index + 1 }}</td>
				<td class="text-center">{{ $row['npm'] ?? '' }}</td>
				<td><a href="{{ url('mahasiswa/view/' . ($row['mhswID'] ?? 0)) }}">{{ $row['nama'] ?? '' }}</a></td>
				<td class="text-center">{{ $row['semesterMahasiswa'] ?? '' }}</td>
				<td class="text-center">{{ $row['sksSemester'] ?? 0 }} SKS</td>
				<td class="text-center">{{ $row['ips'] ?? 0 }}</td>
				<td class="text-center">{{ $row['sksKumulatif'] ?? 0 }} SKS</td>
				<td class="text-center">{{ $row['ipk'] ?? 0 }}</td>
			</tr>
			@endforeach
		@else
		<tr>
			<td colspan="8" class="text-center">Mohon maaf data tidak ditemukan !</td>
		</tr>
		@endif
		</tbody>
	</table>
</div>
