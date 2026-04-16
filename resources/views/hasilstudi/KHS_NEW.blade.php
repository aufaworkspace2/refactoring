<!DOCTYPE html>
<html>
<head>
	<title>Kartu Hasil Studi</title>
	<style>
		body { font-family: sans-serif; font-size: 12px; }
		table { width: 100%; border-collapse: collapse; margin-top: 10px; }
		th, td { border: 1px solid #000; padding: 5px; text-align: left; }
		.no-border td { border: none; }
		.header { text-align: center; }
        .text-center { text-align: center; }
        .bg-grey { background-color: #ddd; }
	</style>
</head>
<body>
	<div class="header">
		<h3>Kartu Hasil Studi</h3>
	</div>

	<table class="no-border">
		<tr>
			<td width="15%">N P M</td>
			<td width="2%">:</td>
			<td width="33%">{{ strtoupper($NPM ?? '') }}</td>
			<td width="15%">Prog/Jen Studi</td>
			<td width="2%">:</td>
			<td width="33%">{{ $ProdiID ?? '' }} - {{ get_field($JenjangID ?? 0, 'jenjang') }}</td>
		</tr>
		<tr>
			<td>N a m a</td>
			<td>:</td>
			<td>{{ strtoupper($Nama ?? '') }}</td>
			<td>Tahun Akademik</td>
			<td>:</td>
			<td>{{ substr($Tahun->Nama ?? '', 0, 9) }} - {{ $Tahun->Semester ?? '' }}</td>
		</tr>
	</table>

	<table>
		<thead>
			<tr class="bg-grey">
				<th class="text-center" width="5%">No</th>
				<th class="text-center" width="15%">Kode</th>
				<th class="text-center" width="40%">Mata Kuliah</th>
				<th class="text-center" width="10%">SKS</th>
				<th class="text-center" width="10%">Nilai</th>
				<th class="text-center" width="10%">Mutu</th>
				<th class="text-center" width="10%">Keterangan</th>
			</tr>
		</thead>
		<tbody>
			@php $jumlahsks = 0; @endphp
			@foreach($query as $index => $row)
			<tr>
				<td class="text-center">{{ $index + 1 }}</td>
				<td class="text-center">{{ $row->MKKode ?? '' }}</td>
				<td>{{ $row->NamaMatakuliah ?? '' }}</td>
				<td class="text-center">{{ $row->TotalSKS ?? 0 }}</td>
				<td class="text-center">{{ $row->NilaiHuruf ?? '' }}</td>
				<td class="text-center">{{ $row->NilaiBobot ?? 0 }}</td>
				<td></td>
			</tr>
			@php $jumlahsks += ($row->TotalSKS ?? 0); @endphp
			@endforeach
		</tbody>
	</table>

	<table class="no-border">
		<tr>
			<td width="70%" style="vertical-align: top;">
				Jumlah SKS : {{ $jumlahsks }}<br><br>
				Index Prestasi Sementara : {{ $ips->IPS ?? 0 }}, Index Prestasi Kumulatif : {{ $ipk->IPK ?? 0 }}
				<br><br>
				@php $grade = ''; @endphp
				@foreach ($grade_nilai as $data)
					@php $grade .= ($data->Nilai ?? '') . " = " . ($data->Bobot ?? '') . " "; @endphp
				@endforeach
				{{ $grade }}
			</td>
			<td width="30%" style="text-align: center;">
				@php
					$identitas = get_id(1, 'identitas');
					$kota = get_wilayah($identitas->KotaPT ?? 0)->Kota ?? '';
				@endphp
				{{ substr($kota, 5) }}, {{ tgl($tgl_cetak ?? date('Y-m-d')) }}<br>
				Ka. Program Studi <br><br><br><br><br>
				@php
					$titleKa = !empty($kaProdi->Title) ? $kaProdi->Title . ', ' : '';
					$gelarKa = !empty($kaProdi->Gelar) ? ', ' . $kaProdi->Gelar : '';
					$namaKa	= $titleKa . strtoupper($kaProdi->Nama ?? '') . $gelarKa;
				@endphp
				({{ $namaKa }})
			</td>
		</tr>
	</table>
</body>
</html>
