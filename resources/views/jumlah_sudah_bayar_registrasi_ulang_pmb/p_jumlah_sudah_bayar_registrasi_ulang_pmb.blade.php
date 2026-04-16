<link rel="stylesheet" type="text/css" href="{{ asset('assets/theme/css/pdf.css') }}" />
<page backtop="33mm" backbottom="0mm" backleft="0mm" backright="0mm">
	<page_header>
		{!! cetak_header() !!}
	</page_header>
<h4 style="text-align:center;">{{ strtoupper('Rekap Jumlah Sudah Bayar Registrasi Ulang') }}</h4>
<table width="100%" class="table table-hover table-bordered table-responsive block" align="center">
			<thead>
				<tr>
					<th class="center" width="2%">No.</th>
					<th width="30%">Program Studi Pilihan 1</th>
					<th>Program</th>
					<th>Jumlah Sudah Bayar Registrasi Ulang</th>
				</tr>
			</thead>
			<tbody>
				@php $arr_prodi_display = []; @endphp
				@foreach($query as $key => $row)
				<tr class="mhsw_{{ $row['ID'] ?? '' }}">
					@if(!in_array($row['prodiID'], $arr_prodi_display))
					<td rowspan="{{ $rowprodi[$row['prodiID']] ?? 1 }}" class="center">{{ $arr_no[$row['prodiID']] ?? '' }}.</td>
					<td rowspan="{{ $rowprodi[$row['prodiID']] ?? 1 }}">{{ $row['prodiNama'] ?? '' }}</td>
					@php $arr_prodi_display[] = $row['prodiID']; @endphp
					@endif
					<td>{{ $row['programNama'] ?? '' }}</td>
					<td>{{ $row['JumlahSudahBayar'] ?? '' }}</td>
				</tr>
				@endforeach
				@if(count($query) == 0)
					<tr>
						<th colspan="4" style="text-align:center">Belum ada data</th>
					</tr>
				@else
				<tr>
					<th colspan='3' style="text-align:right">Total</th>
					<th>{{ $TotalJumlahSudahBayar }}</th>
				</tr>
				@endif
			</tbody>
		</table>
		</page>
