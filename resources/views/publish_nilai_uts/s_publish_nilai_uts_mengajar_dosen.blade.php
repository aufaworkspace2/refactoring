<p>{!! $total_row ?? '' !!}</p>

<div class="table-responsive">
	<table class="table table-bordered table-hover" id="dataTable1">
		<thead class="bg-primary text-white">
			<th width="1%" align="center">No.</th>
			<th width="5%" class="text-center">Kode</th>
			<th width="20%" class="text-center">Mata Kuliah</th>
			<th width="10%" class="text-center">Program Studi</th>
			<th width="7%" class="text-center">Kelas</th>
			<th width="20%" class="text-center">Dosen</th>
			<th width="10%" class="text-center">Hari/Tanggal</th>
			<th width="6%" class="text-center">Ruang</th>
			<th width="10%" class="text-center">Waktu</th>
			<th width="5%" class="text-center">Peserta</th>
			<th width="5%" class="text-center">Status Input Nilai</th>
			<th width="10%" class="text-center">Detail</th>
		</thead>
		<tbody>
		@php $no = $offset ?? 0; @endphp
		@foreach($query ?? [] as $row)
		@php
			$totalPeserta = ($row->gabungan == 'YA' ? ($jadwalGabungan[$row->jadwalID]->jumlahPeserta ?? 0) : ($row->totalPeserta ?? 0));
			$akses = 1;
			$cek_belum_publish = $row->belumPublish ?? 0;
		@endphp

		<tr>
			<td class="text-center align-middle">{{ ++$no }}</td>
			<td class="align-middle">{{ $row->mkkode ?? '' }}</td>
			<td class="align-middle">
				{{ $row->namaMatkul ?? '' }}
				@if($row->gabungan == 'YA')
					<br><span class='badge badge-success'>Jadwal Gabungan</span>
				@endif
			</td>
			<td class="align-middle" align="center">
				@php
					$prodi = get_id($row->prodiID ?? '', 'programstudi');
					$jenjang = get_id($prodi->JenjangID ?? '', 'jenjang');
					echo ($jenjang->Nama ?? '') . ' - ' . ($prodi->Nama ?? '');
				@endphp
			</td>
			<td class="align-middle" align="center">
				{{ get_field($row->kelasID ?? '', 'kelas') }}
			</td>
			<td class="align-middle">
				@php
					$dosen = get_id($row->dosenID ?? '', 'dosen');
					$titleDosen = (!empty($dosen->Title) ? $dosen->Title . ', ' : '');
					$gelarDosen = (!empty($dosen->Gelar) ? ', ' . $dosen->Gelar : '');
					$namaDosen = $titleDosen . ucwords($dosen->Nama ?? '') . $gelarDosen;
					
					$namaDosenAnggota = ''; // Initialize to avoid PHP 8 undefined variable error
					$dosenAnggotaExp = explode(',', $row->dosenAnggota ?? '');
					$count_dosen = count($dosenAnggotaExp);

					if (count($dosenAnggotaExp) > 0) {
						foreach ($dosenAnggotaExp as $value) {
							$dosenAnggota = get_id($value, 'dosen');
							$titleAnggota = (!empty($dosenAnggota->Title) ? $dosenAnggota->Title . ', ' : '');
							$gelarAnggota = (!empty($dosenAnggota->Gelar) ? ', ' . $dosenAnggota->Gelar : '');
							$namaDosenAnggota .= $titleAnggota . ucwords($dosenAnggota->Nama ?? '') . $gelarAnggota . '<br>';
						}
					} else {
						$dosenAnggota = get_id($row->DosenAnggota ?? '', 'dosen');
						$titleAnggota = (!empty($dosenAnggota->Title) ? $dosenAnggota->Title . ', ' : '');
						$gelarAnggota = (!empty($dosenAnggota->Gelar) ? ', ' . $dosenAnggota->Gelar : '');
						$namaDosenAnggota = $titleAnggota . ucwords($dosenAnggota->Nama ?? '') . $gelarAnggota;
					}

					if (!empty($namaDosen)) {
						echo $namaDosen . ' &nbsp;<label class="badge badge-info">K</label>';
						echo '<br>';
					}

					$x = 0;
					for ($i = 0; $i < $count_dosen; $i++) {
						$dosen = get_id($dosenAnggotaExp[$i] ?? '', 'dosen');
						echo ($dosen->Title ?? '') . ' ' . ($dosen->Nama ?? '') . ' ' . ($dosen->Gelar ?? '');
						if ($count_dosen > ++$x) { echo ' | '; }
					}

					$namaDosen = null;
					$namaDosenAnggota = null;
				@endphp
			</td>
			<td>
				<div id="label_tanggal_{{ $row->jadwalID ?? '' }}">
					@php $nomor = 1; @endphp
					@foreach($cektanggal[$row->jadwalID] ?? [] as $restanggal)
						{{ $nomor++ }}.{{ tgl($restanggal, "01") }}
					@endforeach
				</div>
			</td>
			<td>
				<div class="ruang_{{ $row->jadwalID ?? '' }}">
					@php $nomor = 1; @endphp
					@foreach($cekruang[$row->jadwalID] ?? [] as $ru)
						{{ get_field($ru, 'ruang') }}<br>
						@php $nomor++; @endphp
					@endforeach
				</div>
				<div class="edit_ruang_{{ $row->jadwalID ?? '' }}" style="display: none;">

				</div>
			</td>
			<td>
				<div class="waktu_{{ $row->jadwalID ?? '' }}">
					@php
						$str_idwaktu = '';
						$nomor = 1;
					@endphp
					@foreach($cekwaktu[$row->jadwalID] ?? [] as $waktu)
						@php
							$kodewaktu = DB::table('kodewaktu')->where('ID', $waktu)->first();
						@endphp
						{{ $nomor++ }}. {{ $kodewaktu->JamMulai ?? '' }} - {{ $kodewaktu->JamSelesai ?? '' }}<br>
					@endforeach
				</div>
				<div class="edit_waktu_{{ $row->jadwalID ?? '' }}" style="display: none;">

				</div>
			</td>
			<td align="center" class="align-middle">
				<label class="badge badge-success">{{ $totalPeserta }} Orang</label>
				@if($cek_belum_publish > 0)
					<label class="badge badge-warning">{{ $cek_belum_publish }} Orang <br> Belum <br>Publish<br>Nilai</label>
				@endif
			</td>
			<td>
				<label class="badge badge-{{ ($row->inputNilai ?? 0) > 0 ? 'success' : 'danger' }}">
					Input Nilai: {{ $row->inputNilai ?? 0 }} Orang
				</label>
				<label class="badge badge-{{ ($row->validasiDosen ?? 0) > 0 ? 'success' : 'danger' }}">
					Validasi Dosen: {{ $row->validasiDosen ?? 0 }} Orang
				</label>
			</td>
			<td align="center" class="align-middle">
				<a href="javascript:void(0);" class="btn btn-primary btn-sm" onclick="load_modalLarge('<i class=fa fa-eye></i> Lihat Detail Nilai UTS ','publish_nilai_uts/detail_publish_nilai_uts_mhsw/{{ $row->jadwalID ?? '' }}/{{ $row->kelasID ?? '' }}/{{ $dosenID ?? '' }}')">Detail Nilai UTS </a>
			</td>
		</tr>
		@endforeach
		@if(empty($query))
		<tr>
			<td colspan="12" class="text-center">Maaf jadwal yang anda cari tidak ditemukan</td>
		</tr>
		@endif
		</tbody>
	</table>
	{{ $link ?? '' }}
	</div>
	<span class='badge badge-warning'>G</span> = Kelas Gabungan
	<span class='badge badge-primary'>K</span> = Dosen Kordinator (Apabila Teamteaching)
</div>
