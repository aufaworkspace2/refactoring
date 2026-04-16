@php
$query_prodi = DB::table('programstudi')->get();
$all_prodi = [];
$all_jenjang = [];
foreach ($query_prodi as $row_prodi) {
    if (!isset($all_jenjang[$row_prodi->JenjangID])) {
        $all_jenjang[$row_prodi->JenjangID] = DB::table('jenjang')->where('ID', $row_prodi->JenjangID)->first();
    }
    $jenjang = $all_jenjang[$row_prodi->JenjangID];
    $row_prodi->NamaJenjang = $jenjang->Nama ?? '';
    $all_prodi[$row_prodi->ID] = $row_prodi;
}
@endphp

<p>{!! $total_row ?? '' !!}</p>
<form id="f_delete_calon_mahasiswa" action="{{ url('calon_mahasiswa/delete') }}">
    @csrf
    <div class="table-responsive">
        <table class="table table-bordered mb-0 table-hover tablesorter">
            <thead class="bg-primary text-white">
                <tr>
                    @if($Delete == 'YA')
                    <th width="2%" class="sorterfalse">
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.forms.namedItem('f_delete_calon_mahasiswa')); show_btnDelete();">
                            <label for="checkAll"></label>
                        </div>
                    </th>
                    @endif
                    <th class="text-center" width="1%">No.</th>
                    @if($bayar == 1)
                    <th width="10%">No Registrasi</th>
                    @endif
                    <th width="10%">Nama</th>
                    <th width="13%">Pilihan</th>
                    <th width="8%">Email & Password</th>
                    <th width="8%">No Hp</th>
                    <th width="6%">Petugas</th>
                    <th width="10%">Persentase Kelengkapan Data</th>
                    @if($bayar == 1)
                    <th width="10%">Persentase Kelengkapan Upload Dokumen Persyaratan</th>
                    @endif
                    <th width="10%">Bayar</th>
                    @if($bayar == 0)
                    <th width="10%">Konfirmasi Bayar</th>
                    @else
                    <th width="10%">Lihat Konfirmasi Bayar</th>
                    @endif
                    <th width="10%">Tanggal Daftar</th>
                    <th width="10%">Kode Referal</th>
                    <th width="10%">Agent PMB</th>
                    @if($bayar == 1)
                    <th style="width:10%">Lulus USM</th>
                    <th style="width:10%">Lulus Kesehatan</th>
                    @endif
                    <th width="15%">Action</th>
                </tr>
            </thead>
            <tbody>
                @php $no = $offset; $ch = 0; $ite = 0; @endphp
                @foreach($query ?? [] as $row)
                    @php $row = (object) $row; @endphp
                    <tr class="calon_mahasiswa_{{ $row->ID ?? '' }}">
                        @if($Delete == 'YA')
                            @if($row->statuslulus_pmb != 1)
                            <td>
                                <div class="checkbox checkbox-info">
                                    <input type="checkbox" name="checkID[]" id="checkID{{ $ite }}" onclick="show_btnDelete(); checkSingle()" value="{{ $row->ID ?? '' }}">
                                    <label for="checkID{{ $ite }}"></label>
                                </div>
                            </td>
                            @php $ite++; @endphp
                            @else
                            <td>-</td>
                            @endif
                        @endif
                        <td class="text-center">{{ ++$no }}.</td>
                        
                        @if($bayar == 1)
                        <td style="text-align:center">{{ $row->noujian_pmb ?? '-' }}</td>
                        @endif
                        
                        <td>
                            @if($Update == 'YA')
                                <a href="{{ url('calon_mahasiswa/view/' . ($row->ID ?? '')) }}">{{ $row->Nama ?? '' }}</a>
                            @else
                                {{ $row->Nama ?? '' }}
                            @endif
                            
                            @if($row->harus_verifikasi_file_pmb == 1)
                                @if($row->status_verifikasi_file_pmb == 1)
                                    <span class="badge badge-success">Upload Dokumen Disetujui</span>
                                @elseif($row->status_verifikasi_file_pmb == 2)
                                    <span class="badge badge-secondary">Upload Dokumen Tidak Disetujui</span>
                                @else
                                    <span class="badge badge-purple">Upload Dokumen Belum Disetujui</span>
                                @endif
                            @endif
                        </td>
                        
                        <td>
                            @php
                            $pilihan1 = $all_prodi[$row->pilihan1] ?? null;
                            $pilihan2 = $all_prodi[$row->pilihan2] ?? null;
                            $pilihan3 = $all_prodi[$row->pilihan3] ?? null;
                            @endphp
                            1. {{ $pilihan1->NamaJenjang ?? '' }} {{ $pilihan1->Nama ?? '' }}
                            @if($row->pilihan2)
                                <br>2. {{ $pilihan2->NamaJenjang ?? '' }} {{ $pilihan2->Nama ?? '' }}
                            @endif
                            @if($row->pilihan3)
                                <br>3. {{ $pilihan3->NamaJenjang ?? '' }} {{ $pilihan3->Nama ?? '' }}
                            @endif
                        </td>
                        
                        <td>
                            {{ $row->Email ?? '' }}
                            <hr style="margin: 2px 0;">
                            @php
                            $pass = $row->passwordasli_pmb ?? $row->passwordrandom_pmb ?? '';
                            @endphp
                            <span class="hidepass" style="cursor:pointer;" data-bs-toggle="tooltip" data-bs-placement="top" title="Klik 2 kali untuk melihat password" type="text" data-oldtext="{{ $pass }}">{{ str_repeat('*', strlen($pass)) }}</span>
                        </td>
                        
                        <td>{{ $row->HP ?? '' }}</td>
                        
                        <td>{{ $row->namauser ?? '-' }}</td>
                        
                        <td class="text-center">
                            <label @if(($row->persentase_kelengkapan_data ?? 0) < 100) data-toggle="tooltip" title="Klik untuk melihat data yang belum diisi." @endif class="badge badge-{{ ($row->persentase_kelengkapan_data ?? 0) == 100 ? 'success' : 'warning' }} font-size-14">
                                @if(($row->persentase_kelengkapan_data ?? 0) == 100)
                                    {{ $row->persentase_kelengkapan_data ?? 0 }}%
                                @else
                                    <span class="cursor-pointer" data-toggle="modal" data-target="#modal-belumlengkap-{{ $row->ID ?? '' }}">{{ $row->persentase_kelengkapan_data ?? 0 }}%</span>
                                @endif
                            </label>
                            
                            <div id="modal-belumlengkap-{{ $row->ID ?? '' }}" class="modal fade" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-body text-left">
                                            <div class="row-fluid">
                                                <h4 class="mb-3">Daftar isian yang belum lengkap</h4>
                                                <ul>
                                                    @foreach($row->list_belum_lengkap ?? [] as $val_belumlengkap)
                                                        <li>{{ $val_belumlengkap }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button class="btn btn-small btn-danger" data-dismiss="modal">
                                                <i class="icon-remove"></i> Tutup
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        
                        @if($bayar == 1)
                        <td class="text-center">
                            @if(($row->persentase_kelengkapan_dokumen_persyaratan ?? 0) != 0)
                                <label class="badge badge-success font-size-14">
                                    {{ number_format($row->persentase_kelengkapan_dokumen_persyaratan ?? 0, 0) }} %
                                </label>
                            @else
                                <label class="badge badge-danger font-size-14">
                                    0%
                                </label>
                            @endif
                        </td>
                        @endif
                        
                        <td>
                            @if($bayar == 1)
                                @if($row->statuslulus_pmb == 1 || ($row->metode_pembayaran ?? '') == '3')
                                    <a href="javascript:void(0);" class="badge badge-success text-white">Sudah Bayar</a>
                                @else
                                    <a href="javascript:void(0);" onclick="updateStatus({{ $row->ID ?? 0 }},0)" class="badge badge-success">Sudah Bayar</a>
                                @endif
                            @else
                                @if($row->statuslulus_pmb == 1 || ($row->metode_pembayaran ?? '') == '3' || ($row->jml_konfirmasi ?? 0) == 0)
                                    <a href="javascript:void(0);" class="badge badge-secondary text-white">Belum Bayar</a>
                                @else
                                    <a href="javascript:void(0);" onclick="updateStatus({{ $row->ID ?? 0 }},1,{{ ($row->jumlahbayar_pmb ?? 0) + ($row->biaya_tambahan_formulir_pmb ?? 0) }})" class="badge badge-secondary text-white">Belum Bayar</a>
                                @endif
                            @endif
                        </td>
                        
                        <td>
                            @if(($row->jml_konfirmasi ?? 0) > 0)
                                <a class="blue" href="#modal-detail{{ $row->ID ?? '' }}" data-toggle="modal" role="button">
                                    <i class="icon-zoom-in bigger-130"></i> Detail
                                </a>
                                
                                <!-- Modal Detail Konfirmasi -->
                                <div id="modal-detail{{ $row->ID ?? '' }}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal-detail{{ $row->ID ?? '' }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title" id="modal-detail{{ $row->ID ?? '' }}">Konfirmasi Pembayaran</h4>
                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                            </div>
                                            <div class="modal-body">
                                                @foreach($row->data_konfirmasi ?? [] as $idkonfirmasi => $k)
                                                    <table class="table table-bordered table-striped">
                                                        <tr>
                                                            <th>Tanggal Transfer</th>
                                                            <td>{{ $k['tanggal_transfer'] ?? '' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Jumlah Biaya</th>
                                                            <td>{{ number_format($k['jumlah'] ?? 0, 0, ',', '.') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Nama Calon Mahasiswa</th>
                                                            <td>{{ $row->Nama ?? '' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Nama Pengirim / Pemilik Rekening</th>
                                                            <td>{{ $k['nama'] ?? '' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Nomor Rekening</th>
                                                            <td>{{ $k['nomor'] ?? '' }}</td>
                                                        </tr>
                                                        @if(($row->jml_konfirmasi ?? 0) == 1 && $bayar == 0)
                                                        <tr>
                                                            <th>Status</th>
                                                            <td><span class="badge badge-danger">Belum Konfirmasi</span></td>
                                                        </tr>
                                                        @elseif($bayar == 1)
                                                        <tr>
                                                            <th>Status</th>
                                                            <td><span class="badge badge-success">Sudah Konfirmasi</span></td>
                                                        </tr>
                                                        @endif
                                                        <tr>
                                                            <th>Bukti Bayar</th>
                                                            <td>
                                                                @if(!empty($k['fileasli']))
                                                                    <a href="{{ $k['file'] ?? '#' }}" target="_blank">Lihat File</a>
                                                                @else
                                                                    <span class="badge badge-danger">Belum Upload</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    </table>
                                                @endforeach
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-danger waves-effect waves-light" data-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif(($row->jumlahbayar_pmb ?? 0) > 0)
                                <span class="badge badge-danger">Belum Konfirmasi</span>
                            @else
                                <span class="badge badge-primary">Gratis Formulir</span>
                            @endif
                        </td>
                        
                        <td>{{ $row->TglBuat ? \Carbon\Carbon::parse($row->TglBuat)->format('d/m/Y') : '-' }}</td>
                        
                        <td>
                            @if($row->kode_referal_pmb)
                                {{ $row->kode_referal_pmb }}
                            @else
                                -
                            @endif
                        </td>
                        
                        <td>{{ $row->nama_agent ?? '-' }}</td>
                        
                        @if($bayar == 1)
                        <td style="text-align:center">
                            @php
                            if ($row->statuslulus_pmb == "1") {
                                $statuslulus_str = "Lulus";
                            } elseif ($row->statuslulus_pmb == "2") {
                                $statuslulus_str = "Tidak Lulus";
                            } else {
                                $statuslulus_str = "Belum Lulus";
                            }
                            @endphp
                            {{ $statuslulus_str }}
                        </td>
                        <td style="text-align:center">
                            @php
                            if ($row->kesehatan_pmb == "1") {
                                $statusluluskesehatan_str = "Lulus";
                            } elseif ($row->kesehatan_pmb == "2") {
                                $statusluluskesehatan_str = "Tidak Lulus";
                            } else {
                                $statusluluskesehatan_str = "Belum Lulus";
                            }
                            @endphp
                            {{ $statusluluskesehatan_str }}
                        </td>
                        @endif
                        
                        <td>
                            <div class="btn-group dropleft">
                                <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false">
                                    <i class="mdi mdi-chevron-left"></i> Action
                                </button>
                                <div class="dropdown-menu">
                                    <a href="#detailinfo{{ $no }}" data-toggle="modal" role="button" class="dropdown-item green">Detail</a>
                                    <a href="{{ getenv('PMB_URL') }}/panel/cetakregister/{{ md5($row->ID ?? '') }}/cmsz/{{ session('UserID') }}" role="button" target="_blank" class="dropdown-item blue">Cetak Status Registrasi</a>
                                    <div class="dropdown-divider"></div>
                                    <a href="#modal-dokumen{{ $row->ID ?? '' }}" data-toggle="modal" role="button" class="dropdown-item green">Lihat Dokumen Persyaratan Jalur</a>
                                    @if($row->harus_verifikasi_file_pmb == 1)
                                        <a href="#modal-dokumen-beasiswa{{ $row->ID ?? '' }}" data-toggle="modal" role="button" class="dropdown-item green">Lihat Dokumen Persyaratan Beasiswa</a>
                                    @endif
                                   <a href="javascript:void(0);" onclick="load_modal('Histori Pindah Prodi Calon Mahasiswa ({{ addslashes($row->Nama ?? '') }})', '{{ url('calon_mahasiswa/histori_pindah_prodi/' . ($row->ID ?? '')) }}')" role="button" class="dropdown-item green">Lihat Histori Pindah Prodi Calon Maba</a>

                                    <div class="dropdown-divider"></div>

                                    @if($row->statusbayar_pmb == 0)
                                        <a href="javascript:void(0);" onclick="load_modal('Pindah Channel Pembayaran Calon Mahasiswa ({{ addslashes($row->Nama ?? '') }})', '{{ url('calon_mahasiswa/lihat_pindah_channel/' . ($row->ID ?? '')) }}')" role="button" class="dropdown-item green">Pindah Channel Pembayaran Formulir</a>
                                    @endif
                                    @if($bayar == 1)
                                        <a target="_blank" href="{{ getenv('PMB_URL') }}/panel/print/pmb/{{ md5($row->Email ?? '') }}/{{ session('UserID') }}" role="button" class="dropdown-item blue">Cetak Formulir</a>
                                        @if($row->statusbayar_pmb == 1)
                                            <a target="_blank" href="{{ getenv('PMB_URL') }}/panel/print/ujian/{{ md5($row->Email ?? '') }}" role="button" class="dropdown-item blue">Cetak Kartu Ujian</a>
                                            @if(($row->jumlahbayar_pmb ?? 0) + ($row->biaya_tambahan_formulir_pmb ?? 0) > 0)
                                                <a target="_blank" href="{{ getenv('PMB_URL') }}/cetakkwitansi/{{ md5($row->Email ?? '') }}/{{ session('UserID') }}" role="button" class="dropdown-item blue">Cetak Kwitansi</a>
                                            @endif
                                        @endif
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Modal Detail Info -->
                            <div class="modal fade" id="detailinfo{{ $no }}" tabindex="-1" role="dialog" aria-labelledby="detailinfo{{ $no }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title" id="detailinfo{{ $no }}">Detail Calon Mahasiswa</h4>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <ul class="nav nav-tabs">
                                                <li class="nav-item">
                                                    <a href="#home{{ $no }}" data-toggle="tab" aria-expanded="false" class="nav-link active">
                                                        <span class="d-sm-block">Peminatan</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="#profile{{ $no }}" data-toggle="tab" aria-expanded="true" class="nav-link">
                                                        <span class="d-sm-block">Data Calon Mahasiswa</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="#messages{{ $no }}" data-toggle="tab" aria-expanded="false" class="nav-link">
                                                        <span class="d-sm-block">Data Orangtua</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="#settings{{ $no }}" data-toggle="tab" aria-expanded="false" class="nav-link">
                                                        <span class="d-sm-block">Data Asal Sekolah</span>
                                                    </a>
                                                </li>
                                            </ul>

                                            <div class="tab-content border-none">
                                                <!-- Tab Peminatan -->
                                                <div role="tabpanel" class="tab-pane fade show active" id="home{{ $no }}">
                                                    <table class="table table-bordered table-striped table-user-information">
                                                        <tbody>
                                                            <tr>
                                                                <td>Program</td>
                                                                <td>{{ $row->programNama ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Jalur Pendaftar</td>
                                                                <td>{{ DB::table('pmb_edu_jalur_pendaftaran')->where('id', $row->jalur_pmb ?? '')->value('nama') ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Gelombang</td>
                                                                <td>{{ $row->gelombangNama ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Pendidikan Terakhir</td>
                                                                <td>
                                                                    @php
                                                                    try {
                                                                        echo DB::table('jenissekolah')->where('ID', $row->JenisSekolahID ?? '')->value('Nama') ?? '-';
                                                                    } catch (\Exception $e) {
                                                                        echo '-';
                                                                    }
                                                                    @endphp
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Tipe Lulusan</td>
                                                                <td>
                                                                    @php
                                                                    try {
                                                                        echo DB::table('jurusansekolah')->where('ID', $row->JurusanSekolahID ?? '')->value('Nama') ?? '-';
                                                                    } catch (\Exception $e) {
                                                                        echo '-';
                                                                    }
                                                                    @endphp
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Pilihan Prodi</td>
                                                                <td>{{ $pilihan1->NamaJenjang ?? '' }} || {{ $pilihan1->Nama ?? '' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Mengetahui Dari?</td>
                                                                <td>
                                                                    @php
                                                                    $refDaftar = DB::table('pmb_tbl_referensi_daftar')->where('id_ref_daftar', $row->ref_daftar ?? '')->first();
                                                                    @endphp
                                                                    {{ $refDaftar->nama_ref ?? '-' }}
                                                                    @if($row->ref_daftar == 10)
                                                                        [ {{ $row->alternatif_ref_daftar ?? '' }} ]
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Kode Referal</td>
                                                                <td>
                                                                    @if($row->kode_referal_pmb)
                                                                        {{ $row->kode_referal_pmb }}
                                                                    @else
                                                                        Tidak Ada
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <!-- Tab Data Calon Mahasiswa -->
                                                <div role="tabpanel" class="tab-pane fade" id="profile{{ $no }}">
                                                    <table class="table table-user-information table-bordered table-striped">
                                                        <tbody>
                                                            <tr>
                                                                <td>NO KTP/ NIK</td>
                                                                <td>{{ $row->NoIdentitas ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Nama Lengkap</td>
                                                                <td>{{ $row->Nama ?? '' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Tempat, Tanggal Lahir</td>
                                                                <td>
                                                                    {{ strtoupper($row->TempatLahir ?? '') }}, {{ $row->TanggalLahir ? \Carbon\Carbon::parse($row->TanggalLahir)->format('d M Y') : '-' }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Jenis Kelamin</td>
                                                                <td>{{ $row->Kelamin ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Tinggi Badan</td>
                                                                <td>{{ $row->TinggiBadan ?? '-' }} cm</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Berat Badan</td>
                                                                <td>{{ $row->Berat ?? '-' }} Kg</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Agama</td>
                                                                <td>{{ $row->agamaNama ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Kewarganegaraan</td>
                                                                <td>
                                                                    @php
                                                                    try {
                                                                        $negara = DB::table('negara')->where('Kode', $row->Kewarganegaraan ?? '')->value('Nama');
                                                                        echo $negara ?? $row->Kewarganegaraan ?? '-';
                                                                    } catch (\Exception $e) {
                                                                        echo $row->Kewarganegaraan ?? '-';
                                                                    }
                                                                    @endphp
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Status Sipil</td>
                                                                <td>{{ $row->statussipilNama ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Alamat</td>
                                                                <td>{{ $row->Alamat ?? '' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Dusun</td>
                                                                <td>{{ $row->Dusun ?? '' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>RT / RW</td>
                                                                <td>{{ $row->RT ?? '' }} / {{ $row->RW ?? '' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Kelurahan</td>
                                                                <td>{{ $row->Kelurahan ?? '' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Kecamatan</td>
                                                                <td>
                                                                    @php
                                                                    try {
                                                                        echo DB::table('kecamatan')->where('Kode', $row->KecamatanID ?? '')->value('Nama') ?? '-';
                                                                    } catch (\Exception $e) {
                                                                        echo '-';
                                                                    }
                                                                    @endphp
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Kota</td>
                                                                <td>
                                                                    @php
                                                                    try {
                                                                        echo DB::table('kota')->where('Kode', $row->KotaID ?? '')->value('Nama') ?? '-';
                                                                    } catch (\Exception $e) {
                                                                        echo '-';
                                                                    }
                                                                    @endphp
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Propinsi</td>
                                                                <td>
                                                                    @php
                                                                    try {
                                                                        echo DB::table('propinsi')->where('Kode', $row->PropinsiID ?? '')->value('Nama') ?? '-';
                                                                    } catch (\Exception $e) {
                                                                        echo '-';
                                                                    }
                                                                    @endphp
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Kode Pos</td>
                                                                <td>{{ $row->KodePos ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>HP</td>
                                                                <td>{{ $row->HP ?? '' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Email</td>
                                                                <td>{{ $row->Email ?? '' }}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <!-- Tab Data Orangtua -->
                                                <div role="tabpanel" class="tab-pane fade" id="messages{{ $no }}">
                                                    <h5 class="mb-3">Data Ayah Kandung</h5>
                                                    <table class="table table-bordered table-striped table-user-information mb-4">
                                                        <tbody>
                                                            <tr>
                                                                <td>Nama Ayah</td>
                                                                <td>{{ $row->Ayah_Nama ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Tempat Lahir</td>
                                                                <td>{{ strtoupper($row->Ayah_TempatLahir ?? '-') }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Tanggal Lahir</td>
                                                                <td>
                                                                    @if(!empty($row->Ayah_TanggalLahir))
                                                                        {{ \Carbon\Carbon::parse($row->Ayah_TanggalLahir)->format('d M Y') }}
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Agama</td>
                                                                <td>
                                                                    @php
                                                                    try {
                                                                        if (!empty($row->Ayah_AgamaID)) {
                                                                            echo DB::table('agama')->where('ID', $row->Ayah_AgamaID)->value('Nama') ?? '-';
                                                                        } else {
                                                                            echo '-';
                                                                        }
                                                                    } catch (\Exception $e) {
                                                                        echo '-';
                                                                    }
                                                                    @endphp
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Alamat</td>
                                                                <td>{{ $row->Ayah_Alamat ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Kelurahan</td>
                                                                <td>{{ $row->Ayah_Kelurahan ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Kecamatan</td>
                                                                <td>
                                                                    @php
                                                                    try {
                                                                        if (!empty($row->Ayah_KecamatanID)) {
                                                                            echo DB::table('kecamatan')->where('Kode', $row->Ayah_KecamatanID)->value('Nama') ?? '-';
                                                                        } else {
                                                                            echo '-';
                                                                        }
                                                                    } catch (\Exception $e) {
                                                                        echo '-';
                                                                    }
                                                                    @endphp
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Kota</td>
                                                                <td>
                                                                    @php
                                                                    try {
                                                                        if (!empty($row->Ayah_KotaID)) {
                                                                            echo DB::table('kota')->where('Kode', $row->Ayah_KotaID)->value('Nama') ?? '-';
                                                                        } else {
                                                                            echo '-';
                                                                        }
                                                                    } catch (\Exception $e) {
                                                                        echo '-';
                                                                    }
                                                                    @endphp
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Propinsi</td>
                                                                <td>
                                                                    @php
                                                                    try {
                                                                        if (!empty($row->Ayah_PropinsiID)) {
                                                                            echo DB::table('propinsi')->where('Kode', $row->Ayah_PropinsiID)->value('Nama') ?? '-';
                                                                        } else {
                                                                            echo '-';
                                                                        }
                                                                    } catch (\Exception $e) {
                                                                        echo '-';
                                                                    }
                                                                    @endphp
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Kode Pos</td>
                                                                <td>{{ $row->Ayah_KodePos ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Telepon</td>
                                                                <td>{{ $row->Ayah_Telepon ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>HP</td>
                                                                <td>{{ $row->Ayah_HP ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Pendidikan Terakhir</td>
                                                                <td>
                                                                    @php
                                                                    try {
                                                                        if (!empty($row->Ayah_JenisSekolahID)) {
                                                                            echo DB::table('jenissekolah')->where('ID', $row->Ayah_JenisSekolahID)->value('Nama') ?? '-';
                                                                        } else {
                                                                            echo '-';
                                                                        }
                                                                    } catch (\Exception $e) {
                                                                        echo '-';
                                                                    }
                                                                    @endphp
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Pekerjaan</td>
                                                                <td>
                                                                    @php
                                                                    try {
                                                                        if (!empty($row->Ayah_PekerjaanID)) {
                                                                            echo DB::table('pekerjaan')->where('ID', $row->Ayah_PekerjaanID)->value('Nama') ?? '-';
                                                                        } else {
                                                                            echo '-';
                                                                        }
                                                                    } catch (\Exception $e) {
                                                                        echo '-';
                                                                    }
                                                                    @endphp
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Penghasilan</td>
                                                                <td>
                                                                    @php
                                                                    try {
                                                                        if (!empty($row->Ayah_PenghasilanID)) {
                                                                            echo DB::table('penghasilan')->where('ID', $row->Ayah_PenghasilanID)->value('Nama') ?? '-';
                                                                        } else {
                                                                            echo '-';
                                                                        }
                                                                    } catch (\Exception $e) {
                                                                        echo '-';
                                                                    }
                                                                    @endphp
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Instansi</td>
                                                                <td>{{ $row->Ayah_NamaInstansi ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Alamat Instansi</td>
                                                                <td>{{ $row->Ayah_AlamatInstansi ?? '-' }}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>

                                                    <h5 class="mb-3">Data Ibu Kandung</h5>
                                                    <table class="table table-bordered table-striped table-user-information">
                                                        <tbody>
                                                            <tr>
                                                                <td>Nama Ibu</td>
                                                                <td>{{ $row->Ibu_Nama ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Alamat</td>
                                                                <td>{{ $row->Ibu_Alamat ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Kelurahan</td>
                                                                <td>{{ $row->Ibu_Kelurahan ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Kecamatan</td>
                                                                <td>
                                                                    @php
                                                                    try {
                                                                        if (!empty($row->Ibu_KecamatanID)) {
                                                                            echo DB::table('kecamatan')->where('Kode', $row->Ibu_KecamatanID)->value('Nama') ?? '-';
                                                                        } else {
                                                                            echo '-';
                                                                        }
                                                                    } catch (\Exception $e) {
                                                                        echo '-';
                                                                    }
                                                                    @endphp
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Kota</td>
                                                                <td>
                                                                    @php
                                                                    try {
                                                                        if (!empty($row->Ibu_KotaID)) {
                                                                            echo DB::table('kota')->where('Kode', $row->Ibu_KotaID)->value('Nama') ?? '-';
                                                                        } else {
                                                                            echo '-';
                                                                        }
                                                                    } catch (\Exception $e) {
                                                                        echo '-';
                                                                    }
                                                                    @endphp
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Propinsi</td>
                                                                <td>
                                                                    @php
                                                                    try {
                                                                        if (!empty($row->Ibu_PropinsiID)) {
                                                                            echo DB::table('propinsi')->where('Kode', $row->Ibu_PropinsiID)->value('Nama') ?? '-';
                                                                        } else {
                                                                            echo '-';
                                                                        }
                                                                    } catch (\Exception $e) {
                                                                        echo '-';
                                                                    }
                                                                    @endphp
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Kode Pos</td>
                                                                <td>{{ $row->Ibu_KodePos ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Telepon</td>
                                                                <td>{{ $row->Ibu_Telepon ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>HP</td>
                                                                <td>{{ $row->Ibu_HP ?? '-' }}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <!-- Tab Data Asal Sekolah -->
                                                <div role="tabpanel" class="tab-pane fade" id="settings{{ $no }}">
                                                    <table class="table table-bordered table-striped table-user-information">
                                                        <tbody>
                                                            <tr>
                                                                <td>Nama Sekolah / Kampus</td>
                                                                <td>
                                                                    @php
                                                                    try {
                                                                        echo DB::table('sekolah')->where('ID', $row->SekolahID ?? '')->value('Nama') ?? '-';
                                                                    } catch (\Exception $e) {
                                                                        echo '-';
                                                                    }
                                                                    @endphp
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>NIS / NIM</td>
                                                                <td>{{ $row->AsalNIM ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Alamat Sekolah</td>
                                                                <td>{{ $row->AlamatSekolah ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Kecamatan</td>
                                                                <td>
                                                                    @php
                                                                    try {
                                                                        echo DB::table('kecamatan')->where('Kode', $row->KecamatanSekolah ?? '')->value('Nama') ?? '-';
                                                                    } catch (\Exception $e) {
                                                                        echo '-';
                                                                    }
                                                                    @endphp
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Kota</td>
                                                                <td>
                                                                    @php
                                                                    try {
                                                                        echo DB::table('kota')->where('Kode', $row->KotaSekolah ?? '')->value('Nama') ?? '-';
                                                                    } catch (\Exception $e) {
                                                                        echo '-';
                                                                    }
                                                                    @endphp
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Propinsi</td>
                                                                <td>
                                                                    @php
                                                                    try {
                                                                        echo DB::table('propinsi')->where('Kode', $row->ProvinsiSekolah ?? '')->value('Nama') ?? '-';
                                                                    } catch (\Exception $e) {
                                                                        echo '-';
                                                                    }
                                                                    @endphp
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Kode Pos</td>
                                                                <td>{{ $row->KodePosSekolah ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Telepon Sekolah</td>
                                                                <td>{{ $row->TeleponSekolah ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Tahun Lulus</td>
                                                                <td>{{ $row->TahunLulus ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>No Ijazah</td>
                                                                <td>{{ $row->NoIjazah ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Nilai IPK / Rata-Rata</td>
                                                                <td>{{ $row->Nilaiunas ?? '-' }}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Modal Detail Info -->
                            
                            <!-- Modal Dokumen Persyaratan -->
                            <div id="modal-dokumen{{ $row->ID ?? '' }}" class="modal fade" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-body">
                                            <div class="row-fluid">
                                                <h4 class="mb-3">Dokumen persyaratan jalur</h4>
                                                <table class="table-bordered" width="100%">
                                                    @if(!empty($row->datasyarat[$row->ID ?? '']))
                                                        @foreach($row->datasyarat[$row->ID ?? ''] as $id => $i)
                                                            <tr>
                                                                <td>{{ $i['syarat'] ?? '' }}</td>
                                                                <td style="text-align:center">
                                                                    @if(!empty($i['namafile']))
                                                                        <a href="{{ $i['link'] ?? '#' }}" target="_blank">Lihat File</a>
                                                                    @else
                                                                        <a role="button" style="color:red;pointer-events: none;cursor: default;">Belum upload dokumen</a>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        <tr><td colspan="2" class="text-center">Tidak ada dokumen persyaratan</td></tr>
                                                    @endif
                                                </table>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button class="btn btn-small btn-danger" data-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div id="hapus" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="hapus" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="hapus">{{ __('app.confirm_header') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <p>{{ __('app.confirm_message') }}</p>
                    <p class="data_name"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="hapusdata()" class="btn btn-danger waves-effect">{{ __('app.delete') }}</button>
                    <button type="button" class="btn btn-primary waves-effect waves-light" data-dismiss="modal">{{ __('app.close') }}</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">{!! $link ?? '' !!}</div>
    </div>
</form>

<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

window.show_btnDelete = function(){
    i=0; hasil = false;
    var checkElements = document.getElementsByName('checkID[]');
    while(checkElements.length > i) {
        var checkname = document.getElementById('checkID'+i);
        if(checkname && checkname.checked){ hasil = true; }
        i++;
    }
    if(hasil == true) {
        if($('#btnDelete').length) {
            $('#btnDelete').removeAttr('disabled');
            $('#btnDelete').attr('href','#hapus');
        }
    } else {
        if($('#btnDelete').length) {
            $('#btnDelete').attr('disabled','disabled');
            $('#btnDelete').attr('href','#');
        }
    }
}
show_btnDelete();

$("input:checkbox[name='checkID[]']").click(function(){
    if(this.checked == true){ $(this).parents('tr').addClass('table-danger'); }
    else { $(this).parents('tr').removeClass('table-danger'); }
});

function checkall(chkAll,checkid){
    if (checkid != null){
        if (checkid.length == null) checkid.checked = chkAll.checked;
        else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;
        $("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
        $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
    }
}

function updateStatus(id, status, tagihan_formulir=0) {
    var text_tambahan = "";
    if(status == 1 && tagihan_formulir > 0){
        text_tambahan = "Isi Terlebih Dahulu dibawah ini: <br>"+
        '<div style="display: block;" class="swal2-radio">'+
        '<label>Nominal Bayar Formulir &nbsp;&nbsp;</label>'+
        '<input type="text" data-gen_post="true" name="jumlah_bayar" class="form-control currency" id="jumlah_bayar" value="'+tagihan_formulir+'" />' +
        '</div>';
    }
    
    swal({
        title: "Peringatan",
        text: "Apa Anda Yakin Akan Mengubah Status Calon Mahasiswa Ini ? <br>"+text_tambahan,
        icon: "warning",
        showCloseButton: true,
        showCancelButton: true,
        focusConfirm: false,
    }).then(function(result) {
        if (result) {
            var jumlah_bayar = tagihan_formulir;
            
            $.ajax({
                type: "POST",
                url: "{{ url('calon_mahasiswa/updateStatusBayar') }}",
                dataType: "JSON",
                data: {
                    ID: id,
                    status: status,
                    nominal_jumlah_bayar: jumlah_bayar,
                },
                success: function(data) {
                    if(data.status){
                        swal("Berhasil", data.message, "success").then(function() {
                            filter();
                        });
                    } else {
                        swal("Gagal", data.message, "error");
                    }
                }
            });
        }
    });
}
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
function load_modal(title, url) {
    if (!url.startsWith('http://') && !url.startsWith('https://')) {
        url = '{{ url('/') }}' + url;
    }
  
    $.ajax({
        type: 'GET',
        url: url,
        success: function(data) {
            $('#modal-dynamic .modal-title').html(title);
            $('#modal-dynamic .modal-body').html(data);
            $('#modal-dynamic').modal('show');
        },
        error: function(xhr, status, error) {
            console.error('Modal load error:', error);
            $('#modal-dynamic .modal-title').html('Error');
            $('#modal-dynamic .modal-body').html('<div class="alert alert-danger">Gagal memuat data: ' + error + '</div>');
            $('#modal-dynamic').modal('show');
        }
    });
    
    return false;
}

// Add modal-dynamic to v_calon_mahasiswa.blade.php if not exists
if ($('#modal-dynamic').length == 0) {
    $('body').append('<div id="modal-dynamic" class="modal fade" tabindex="-1" role="dialog"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h4 class="modal-title"></h4><button type="button" class="close" data-dismiss="modal">×</button></div><div class="modal-body"></div></div></div></div>');
}
</script>
