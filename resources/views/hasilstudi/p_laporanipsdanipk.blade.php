<!DOCTYPE html>
<html>
<head>
	<title>Laporan IPS dan IPK</title>
	<style>
		body { font-family: sans-serif; font-size: 11px; }
		table { width: 100%; border-collapse: collapse; margin-top: 5px; }
		th, td { border: 1px solid #000; padding: 3px; text-align: left; }
		.header { text-align: center; }
        .text-center { text-align: center; }
        .bg-grey { background-color: #ddd; }
	</style>
</head>
<body>
    <div class="header">
        <h4>
            LAPORAN AKADEMIK MAHASISWA<br />
            KELAS {{ strtoupper(get_field($programID ?? 0, 'program')) }} || PROGRAM STUDI {{ strtoupper(get_field($prodiID ?? 0, 'programstudi')) }}<br />
            TAHUN SEMESTER {{ strtoupper(get_field($tahunID ?? 0, 'tahun')) }}
        </h4>
    </div>

	<table>
		<thead>
		<tr class="bg-grey">
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
				<td>{{ $row['nama'] ?? '' }}</td>
				<td class="text-center">{{ number_to_romanic($row['semesterMahasiswa'] ?? 0) }}</td>
				<td class="text-center">{{ $row['sksSemester'] ?? 0 }} SKS</td>
				<td class="text-center">{{ $row['ips'] ?? 0 }}</td>
				<td class="text-center">{{ $row['sksKumulatif'] ?? 0 }} SKS</td>
				<td class="text-center">{{ $row['ipk'] ?? 0 }}</td>
			</tr>
			@endforeach
		@else
			<tr>
				<td colspan="8" class="text-center">Belum ada data</td>
			</tr>
		@endif
		</tbody>
	</table>
</body>
</html>
