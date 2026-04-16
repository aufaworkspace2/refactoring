@if(!empty($mahasiswa) && !empty($matakuliah))
<div class="table-responsive">
	<table class="table table-bordered table-hover table-striped">
		<thead class="bg-primary text-white">
			<tr>
				<th class="text-center" rowspan="2" style="vertical-align: middle;">No.</th>
				<th class="text-center" rowspan="2" style="vertical-align: middle;">NPM</th>
				<th class="text-center" rowspan="2" style="vertical-align: middle;">Nama</th>
				@foreach($matakuliah as $mk)
				<th class="text-center">{{ $mk['MKKode'] }}</th>
				@endforeach
				<th class="text-center" style="vertical-align: middle;">Total SKS</th>
			</tr>
			<tr>
				@foreach($matakuliah as $mk)
				<th class="text-center" style="font-size: 11px;">{{ substr($mk['Nama'], 0, 20) }}{{ strlen($mk['Nama']) > 20 ? '...' : '' }}</th>
				@endforeach
			</tr>
		</thead>
		<tbody>
			@php $no = 1; @endphp
			@foreach($mahasiswa as $mhswID => $mhs)
			<tr>
				<td class="text-center">{{ $no++ }}</td>
				<td class="text-center">{{ $mhs['NPM'] ?? '' }}</td>
				<td>{{ $mhs['Nama'] ?? '' }}</td>
				@foreach($matakuliah as $mkID => $mk)
				<td class="text-center">
					{{ $nilai_matkul[$mhswID][$mkID]['NilaiHuruf'] ?? '' }}
				</td>
				@endforeach
				<td class="text-center">{{ $mhs['TotalSKS'] ?? 0 }}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
</div>
@else
<div class="alert alert-info">
	<i class="fa fa-info-circle"></i> Tidak ada data yang ditemukan. Silahkan ubah filter pencarian.
</div>
@endif
