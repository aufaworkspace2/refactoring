<link rel="stylesheet" type="text/css" href="{{ asset('assets/theme/css/pdf.css') }}" />
<page backtop="33mm" backbottom="0mm" backleft="4mm" backright="0mm">
	<page_header>
		{!! cetak_header() !!}
	</page_header>
	<h4 style="text-align:center;">{{ strtoupper('Rekap Referensi Daftar') }}</h4>
	<table class="table table-bordered mb-0 table-hover tablesorter">
		<thead class="bg-primary text-white">
			<tr>
				<th class="text-center" style="text-align:center;width:10px">No.</th>
				<th style="text-align:center;width:500px">Referensi</th>
				<th style="text-align:center;width:140px">Jumlah Mahasiswa</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($query as $key => $row)
				<tr class="mhsw_{{ $row['id_ref_daftar'] ?? '' }}">
					<td style="width:10px" rowspan="{{ $rowprodi[$row['id_ref_daftar']] ?? 1 }}" class="text-center">{{ $arr_no[$row['id_ref_daftar']] ?? '' }}.</td>
					<td style="width:500px" rowspan="{{ $rowprodi[$row['id_ref_daftar']] ?? 1 }}">{{ $row['nama_ref'] ?? '' }}</td>
					<td style="width:140px">{{ $row['JumlahPendaftar'] ?? '' }}</td>
				</tr>
			@endforeach

			@if($jumlah_tidak_ada_referensi > 0)
				<tr class="mhsw_0">
					<td style="width:10px" class="text-center">{{ (end($arr_no) ?? 0) + 1 }}.</td>
					<td style="width:500px">Tidak Diisi</td>
					<td style="width:140px">{{ $jumlah_tidak_ada_referensi }}</td>
				</tr>
			@endif

			<tr>
				<th colspan='2' style="text-align:right">Total</th>
				<th>{{ $TotalJumlahPendaftar ?? '' }}</th>
			</tr>
		</tbody>
	</table>
</page>
