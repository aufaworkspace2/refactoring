<div class="table-responsive mb-2">
	<table class="table table-bordered table-hover" id="dataTable1">
		<thead class="bg-primary text-white">
			<th width="1%" class="text-center">No.</th>
			<th width="20%" class="text-center">Dosen</th>
			<th width="5%" class="text-center">Kode MK</th>
			<th width="20%" class="text-center">Mata Kuliah</th>
			<th width="7%" class="text-center">Kelas</th>
			<th width="5%" class="text-center">Status Input</th>
			<th width="5%" class="text-center">Persentase (%)</th>
		</thead>
		<tbody>
		@php $no = 0; @endphp
		@foreach($query as $row)
		<tr>
			<td class="text-center align-middle">{{ ++$no }}</td>
			<td class="align-middle">
				@php
					$dosen = get_id($row->dosenID, 'dosen');
					$titleDosen = (!empty($dosen->Title) ? $dosen->Title.', ' : '');
					$gelarDosen = (!empty($dosen->Gelar) ? ', '.$dosen->Gelar : '');
					$namaDosen = $titleDosen.ucwords($dosen->Nama ?? '').$gelarDosen;
					$dosenAnggotaExp = explode(',', $row->dosenAnggota ?? '');
					$countDosen = empty($row->dosenAnggota) ? 0 : count($dosenAnggotaExp);
				@endphp
				@if(!empty($namaDosen))
					<strong>{{ $dosen->NIP ?? '' }}</strong><br>
					{{ $namaDosen }} &nbsp;<label class="badge badge-info">K</label>
				@endif
				@if($countDosen > 0 && !empty($row->dosenAnggota))
					<br><br><strong>Dosen Anggota :</strong>
					@foreach($dosenAnggotaExp as $dosenAnggotaId)
						@php
							$dosenAng = get_id(trim($dosenAnggotaId), 'dosen');
						@endphp
						@if($dosenAng)
							<strong>{{ $dosenAng->NIP ?? '' }}</strong><br>
							{{ $dosenAng->Title ?? '' }} {{ $dosenAng->Nama ?? '' }} {{ $dosenAng->Gelar ?? '' }}
						@endif
					@endforeach
				@endif
			</td>
			<td class="align-middle">{{ $row->mkkode ?? '' }}</td>
			<td class="align-middle">
				{{ $row->namaMatkul ?? '' }}
				@if($row->gabungan == 'YA')
					<br><span class='badge badge-success'>Jadwal Gabungan</span>
				@endif
			</td>
			<td class="align-middle text-center">
				{{ get_field($row->kelasID, 'kelas') }}
			</td>
			<td class="align-middle text-center">
				@if($row->persentaseNilai > 0)
					<span class="badge badge-success font-size-12">Sudah</span>
				@else
					<span class="badge badge-danger font-size-12">Belum</span>
				@endif
			</td>
			<td class="align-middle text-center">
				{{ $row->persentaseNilai ?? 0 }} %
			</td>
		</tr>
		@endforeach
		@if(empty($query))
		<tr>
			<td colspan="7" class="text-center">Maaf jadwal yang anda cari tidak ditemukan</td>
		</tr>
		@endif
		</tbody>
	</table>
</div>
<span class='badge badge-warning'>G</span> = Kelas Gabungan
<span class='badge badge-primary'>K</span> = Dosen Kordinator (Apabila Teamteaching)
