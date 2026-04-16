<!DOCTYPE html>
<html>
<head>
	<title>Laporan IPS dan IPK per Angkatan</title>
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
            LAPORAN AKADEMIK MAHASISWA PER ANGKATAN<br />
            KELAS {{ strtoupper(get_field($programID ?? 0, 'program')) }} || PROGRAM STUDI {{ strtoupper(get_field($prodiID ?? 0, 'programstudi')) }}<br />
            TAHUN SEMESTER {{ strtoupper(get_field($tahunID ?? 0, 'tahun')) }}
        </h4>
    </div>

	<table>
		<thead>
			<tr class="bg-grey">
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
				<th colspan="6" style="text-align:center">Belum ada data</th>
			</tr>
		@endif
		</tbody>
	</table>
</body>
</html>
