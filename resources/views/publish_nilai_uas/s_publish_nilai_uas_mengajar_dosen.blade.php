<p>{{ $total_row ?? 0 }}</p>

<div class="table-responsive">
    <table class="table table-bordered table-hover" id="dataTable1">
        <thead class="bg-primary text-white">
            <tr>
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
            </tr>
        </thead>
        <tbody>
        @php 
            $row_jadwal_terpilih = [];
            $x = 0; // for dosen loop
        @endphp
        @forelse($query as $row)
            @php
                if(($row->jadwalID ?? '') == ($JadwalID ?? '')){
                    $row_jadwal_terpilih = $row;
                }
                $totalPeserta = ($row->gabungan == 'YA' ? ($jadwalGabungan[$row->jadwalID]->jumlahPeserta ?? 0) : ($row->totalPeserta ?? 0));
                $cek_belum_publish = $row->belumPublish ?? 0;
                $akses = 1;

                if(!empty($statusPublish)){
                    if($statusPublish == 1){
                        if($cek_belum_publish > 0) $akses = 0;
                    } else if($statusPublish == 2){
                        if($cek_belum_publish == 0) $akses = 0;
                    }
                }
            @endphp

            @if($akses == 1)
            <tr>
                <td class="text-center align-middle">{{ ++$offset }}</td>
                <td class="align-middle">{{ $row->mkkode ?? '' }}</td>
                <td class="align-middle">
                    {{ $row->namaMatkul ?? '' }}
                    @if(($row->gabungan ?? '') == 'YA')
                        <br><span class='badge badge-success'>Jadwal Gabungan</span>
                    @endif
                </td>
                <td class="align-middle" align="center">
                    @php
                        $prodi = get_id($row->prodiID, 'programstudi');
                        $jenjang = get_id($prodi->JenjangID ?? 0, 'jenjang');
                    @endphp
                    {{ $jenjang->Nama ?? '' }} - {{ $prodi->Nama ?? '' }}
                </td>
                <td class="align-middle" align="center">
                    {{ get_field($row->kelasID, 'kelas') }}
                </td>
                <td class="align-middle">
                    @php
                        $dosen = get_id($row->dosenID, 'dosen');
                        $namaDosen = ($dosen ? (($dosen->Title ? $dosen->Title.', ' : '').($dosen->Nama ?? '').($dosen->Gelar ? ', '.$dosen->Gelar : '')) : '');
                        $dosenAnggotaExp = explode(',', $row->dosenAnggota ?? '');
                        $dosenAnggotaExp = array_filter($dosenAnggotaExp);
                        $count_dosen = count($dosenAnggotaExp);
                    @endphp
                    
                    @if($dosen && !empty($dosen->Nama))
                        {{ $namaDosen }} &nbsp;<label class="badge badge-info">K</label>
                        <br>
                    @endif

                    @foreach($dosenAnggotaExp as $idx => $val)
                        @php $d = get_id($val, 'dosen'); @endphp
                        @if($d)
                            {{ ($d->Title ? $d->Title.' ' : '').($d->Nama ?? '').($d->Gelar ? ' '.$d->Gelar : '') }}
                            @if($idx < $count_dosen - 1) | @endif
                        @endif
                    @endforeach
                </td>
                <td>
                    <div id="label_tanggal_{{ $row->jadwalID }}">
                        @if(isset($cektanggal[$row->jadwalID]))
                            @foreach($cektanggal[$row->jadwalID] as $idx => $restanggal)
                                {{ $loop->iteration }}.{{ tgl($restanggal, "01") }}<br>
                            @endforeach
                        @endif
                    </div>
                </td>
                <td>
                    <div class="ruang_{{ $row->jadwalID }}">
                        @if(isset($cekruang[$row->jadwalID]))
                            @foreach($cekruang[$row->jadwalID] as $ru)
                                {{ get_field($ru, 'ruang') }}<br>
                            @endforeach
                        @endif
                    </div>
                </td>
                <td>
                    <div class="waktu_{{ $row->jadwalID }}">
                        @if(isset($cekwaktu[$row->jadwalID]))
                            @foreach($cekwaktu[$row->jadwalID] as $waktu)
                                @php $kodewaktu = DB::table('kodewaktu')->where('ID', $waktu)->first(); @endphp
                                {{ $loop->iteration }}. {{ $kodewaktu->JamMulai ?? '' }} - {{ $kodewaktu->JamSelesai ?? '' }}<br>
                            @endforeach
                        @endif
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
                    <button type="button" class="btn btn-success btn-sm" onclick="load_modalLarge('<i class=\'fa fa-eye\'></i> Lihat Detail Nilai UAS ', 'publish_nilai_uas/detail_publish_nilai_uas_mhsw/{{ $row->jadwalID }}/{{ $row->kelasID ?? 0 }}/{{ $dosenID ?? 0 }}')">Detail Nilai</button>
                </td>
            </tr>
            @endif
        @empty
            <tr>
                <td colspan="12" class="text-center">Maaf jadwal yang anda cari tidak ditemukan</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    {!! $link ?? '' !!}
</div>
<span class='badge badge-warning'>G</span> = Kelas Gabungan
<span class='badge badge-primary'>K</span> = Dosen Kordinator (Apabila Teamteaching)

@if(!empty($row_jadwal_terpilih))
<script>
    $(document).ready(function(){
        // load_modalLarge('<i class=fa fa-eye></i> Lihat Detail Nilai UAS ', 'publish_nilai_uas/detail_publish_nilai_uas_mhsw/{{ $row_jadwal_terpilih->jadwalID }}/{{ $row_jadwal_terpilih->kelasID ?? 0 }}/0')
    });
</script>
@endif
