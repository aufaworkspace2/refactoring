<link rel="stylesheet" type="text/css" href="{{ asset('assets/theme/css/pdf.css') }}" />
<page backtop="33mm" backbottom="0mm" backleft="0mm" backright="0mm">
	<page_header>
		{!! cetak_header() !!}
	</page_header>
	<h4 style="text-align:center;">REKAPITULASI PENDAFTAR</h4>
	<table width="100%" class="table table-hover table-bordered table-responsive block" align="center">
		<thead>
			<tr>
				<th rowspan="2" class="center" style="width: 10px;">No.</th>
				<th rowspan="2" style="width: 250px;">Program Studi</th>
				<th rowspan="2" style="width: 150px;" class="align-middle" width="60%">Program</th>
				<th rowspan="2" class="align-middle" width="150px">Jalur Pendaftaran</th>
				<th colspan='1' style="width: 70px;">Jumlah Peserta</th>
			</tr>
			<tr>
				<td>Pilihan 1</td>
			</tr>
		</thead>
		<tbody>
			<?php $no = $offset ?? 0; $i = 0; $total = 0; ?>
			@php $arr_prodi = []; $arrProdi = []; @endphp
			@foreach($query as $row)
				<?php $total += $row['jumlah'] ?? 0 ?>
				<tr class="agama_{{ $row['ID'] ?? '' }}">
					@if(!in_array($row['pilihan1'], $arr_prodi))
						<td rowspan="{{ $rowprodi[$row['pilihan1']] ?? 1 }}" class="text-center" style="width: 10px;">{{ ++$no }}.</td>
						<td rowspan="{{ $rowprodi[$row['pilihan1']] ?? 1 }}" style="width: 250px;">
							@if($row['pilihan1'] ?? '')
								@foreach(explode(",", $row['pilihan1']) as $key => $value)
									@if(!isset($arrProdi[$value]))
										<?php
											$getprodi = get_id($value, 'programstudi');
											$arrProdi[$value] = get_field($getprodi->JenjangID ?? '', "jenjang") . " " . ($getprodi->Nama ?? '');
										?>
									@endif
									{{ $arrProdi[$value] }}<br>
								@endforeach
							@endif
						</td>
						<?php $arr_prodi[] = $row['pilihan1']; ?>
					@endif
					<td style="width: 150px;">{{ get_field($row['ProgramID'] ?? '', 'program') }}</td>
					<td>{{ get_field($row['jalur_pmb'] ?? '','pmb_edu_jalur_pendaftaran','nama') }}</td>
					<td style="width: 100px;">{{ $row['jumlah'] ?? '' }}</td>
				</tr>
			@endforeach
			<tr>
				<th colspan='4'>Total</th>
				<th>{{ $total }}</th>
			</tr>
		</tbody>
	</table>
</page>
