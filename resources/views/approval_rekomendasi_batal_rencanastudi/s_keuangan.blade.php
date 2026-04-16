<p>{!! $total_row !!}</p>
<form id="f_set_rpr" action="{{ route('approval_rekomendasi_batal_rencanastudi_keuangan.index') }}">
    <div class="table-responsive">
        <table class="table table-bordered mb-0 table-hover tablesorter">
            <thead class="bg-primary text-white">
                <tr>
                    @if($Update == 'YA')
                        <th width="2%">
                            <div class="checkbox checkbox-info">
                                <input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.forms.namedItem('f_set_rpr')); show_btnDelete();">
                                <label for="checkAll"></label>
                            </div>
                        </th>
                    @endif
                    <th rowspan="1" class="text-center" width="2%">No.</th>
                    <th rowspan="1" style="width: 2%;" class="text-center">NIM</th>
                    <th rowspan="1" style="width: 5%;" class="text-center">Nama</th>
                    <th rowspan="1" style="width: 10%;" class="text-center">Mata Kuliah</th>
                    <th rowspan="1" style="width: 3%;" class="text-center">Tahun<br>Semester</th>
                    <th rowspan="1" style="width: 3%;" class="text-center">Diajukan Oleh</th>
                    <th rowspan="1" style="width: 5%;" class="text-center">Alasan Pembatalan</th>
                    <th rowspan="1" style="width: 3%;" class="text-center">Hapus Dengan<br>Nilai</th>
                    <th rowspan="1" style="width: 3%;" class="text-center">Approve Prodi</th>
                    <th rowspan="1" style="width: 3%;" class="text-center">Approve Keuangan</th>
                    <th rowspan="1" style="width: 3%;" class="text-center">Sudah Dibatalkan?</th>
                    <th rowspan="1" style="width: 3%;" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $nomor = $offset;
                    $i = 0;
                @endphp
                @if(count($query) > 0)
                    @foreach ($query as $value)
                        <tr>
                            @if($Update == 'YA')
                                @if($value->StatusBatal != 1)
                                    <td class="align-middle">
                                        <div class="checkbox checkbox-info">
                                            <input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnDelete()" value="{{ $value->ID }}">
                                            <label for="checkID{{ $i }}"></label>
                                        </div>
                                    </td>
                                    @php $i++; @endphp
                                @else
                                    <td>-</td>
                                @endif
                            @endif

                            <td class="text-center">{{ ++$nomor }}.</td>
                            <td class="text-center"><b>{{ $value->npm }}</b></td>
                            <td>{{ $value->namaMahasiswa }}</td>
                            <td><strong>{{ $value->MKKode }}</strong> <br> {{ $value->NamaMataKuliah }}</td>
                            <td><span class="badge badge-secondary">{{ $value->KodeTahun }}</span></td>
                            <td class="text-center">
                                @if($value->DiajukanOleh == 'mahasiswa')
                                    Mahasiswa
                                @elseif($value->DiajukanOleh == 'dosen')
                                    Dosen Wali
                                @elseif($value->DiajukanOleh == 'karyawan')
                                    {{ DB::table('user')->where('ID', $value->UserID)->value('Nama') ?? '' }}
                                @endif
                            </td>
                            <td class="text-center"><span class="badge badge-secondary">{{ $value->AlasanPembatalan }}</span></td>
                            <td class="text-center">
                                @if($value->HapusNilai == '1')
                                    <span class="badge badge-success">Ya</span>
                                    <br>
                                    <a href="javascript:void(0);" onclick="lihatNilai('{{ $value->rencanastudiID }}')"><i class="mdi mdi-eye"></i> Lihat Nilai</a>
                                @else
                                    <span class="badge badge-secondary">Tidak</span>
                                @endif
                            </td>
                            <td>
                                @if($value->rekomendasi_prodi == 1)
                                    <span class="badge badge-success">Sudah Disetujui</span>
                                @elseif($value->rekomendasi_prodi == 2)
                                    <span class="badge badge-danger">Tidak Disetujui</span>
                                @else
                                    <span class="badge badge-secondary">Belum Disetujui</span>
                                @endif
                            </td>
                            <td>
                                @if($value->rekomendasi_keuangan == 1)
                                    <span class="badge badge-success">Sudah Disetujui</span>
                                @elseif($value->rekomendasi_keuangan == 2)
                                    <span class="badge badge-danger">Tidak Disetujui</span>
                                @elseif($value->rekomendasi_keuangan == 0)
                                    <span class="badge badge-secondary">Belum Disetujui</span>
                                @elseif($value->rekomendasi_keuangan == 3)
                                    <span class="badge badge-light">Tanpa Persetujuan Keuangan</span>
                                @endif
                            </td>
                            <td>
                                @if($value->StatusBatal == 1)
                                    <span class="badge badge-success">Sudah</span>
                                @else
                                    <span class="badge badge-secondary">Belum</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($value->StatusBatal != 1)
                                    @if($value->rekomendasi_keuangan == 0)
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-info dropdown-toggle waves-effect waves-light btn-sm" data-toggle="dropdown" aria-expanded="false"> Action <i class="mdi mdi-chevron-down"></i></button>
                                            <div class="dropdown-menu">
                                                <a onclick="rekomendasi_keuangan('{{ $value->ID }}', 1);" href="javascript:void(0);" class="dropdown-item">Setujui</a>
                                                <a onclick="rekomendasi_keuangan('{{ $value->ID }}', 2);" href="javascript:void(0);" class="dropdown-item">Tidak Setujui</a>
                                            </div>
                                        </div>
                                    @elseif($value->rekomendasi_keuangan == 1 || $value->rekomendasi_keuangan == 2)
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-info dropdown-toggle waves-effect waves-light btn-sm" data-toggle="dropdown" aria-expanded="false"> Lainnya <i class="mdi mdi-chevron-down"></i></button>
                                            <div class="dropdown-menu">
                                                <a onclick="rekomendasi_keuangan('{{ $value->ID }}', 0);" href="javascript:void(0);" class="dropdown-item">Batal Persetujuan</a>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="13" class="text-center">Tidak ada data Sesuai Filter diatas</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</form>
<div class="row">
    <div class="col-md-12">
        {!! $link !!}
    </div>
</div>
