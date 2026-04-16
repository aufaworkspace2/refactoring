<div class="row">
    <div class="col-md-6">
        <i class="badge badge-info">K &nbsp; &nbsp; Dosen Koordinator.</i>
    </div>
    <div class="col-md-6 text-right">
        @if(isset($Semester) && $Semester)
        <div class="btn-group">
            <button class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                <i class="mdi mdi-upload"></i> Upload Nilai
                <span class="mdi mdi-chevron-down"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="javascript:void(0);" onclick="templateNilai()"> <i class="mdi mdi-download"></i> Download Template</a>
                <a class="dropdown-item" href="#mdl-upload" data-toggle="modal"><i class="mdi mdi-upload"></i> Upload Excel</a>
            </div>
        </div>
        @else
        <button type="button" class="btn btn-info" onclick="swal('Pemberitahuan','Pilih Semester Terlebih Dahulu', 'info')">
            <i class="mdi mdi-upload"></i> Upload Nilai
        </button>
        @endif

        <!-- Upload Modal -->
        <div class="modal fade" id="mdl-upload" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form id="f_excelnilai" onsubmit="uploadExcel(this); return false;" method="post" action="{{ url('nilai/upload_excel') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h4 class="modal-title">Upload File Excel</h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body text-left">
                            <input type="hidden" name="TahunID" value="{{ $TahunID }}">
                            <div class="alert alert-info">
                                <strong>Catatan:</strong>
                                <ul class="mb-0">
                                    <li>Jangan merubah header Excel</li>
                                    <li>Jangan mengubah DetailKurikulumID</li>
                                </ul>
                            </div>
                            <div class="form-group">
                                <label>Pilih File Excel</label>
                                <input type="file" name="file_excel" class="form-control" accept=".csv, .xls, .xlsx" required>
                            </div>
                            <div id="lihat_hasil_upload" style="display:none;">
                                <a target="_blank" href="{{ url('nilai/lihat_hasil_upload') }}" class="btn btn-block btn-info mt-2">Download Hasil Status Upload Nilai</a>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary btnSave">Upload</button>
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-2">
    <div class="col-md-12">
        <div class="table-responsive">
            <table width="100%" class="table table-hover table-bordered">
                <thead class="bg-primary text-white">
                    <tr>
                        <th width="2%" rowspan="2" class="text-center">No.</th>
                        <th rowspan="2" width="20%" class="text-center">Mata Kuliah</th>
                        <th rowspan="2" width="5%" class="text-center">Total<br>SKS</th>
                        <th colspan="6" class="text-center">Jadwal</th>
                    </tr>
                    <tr>
                        <th class="text-center">Kelas</th>
                        <th class="text-center" width="20%">Dosen</th>
                        <th class="text-center">Peserta</th>
                        <th class="text-center">Komponen Bobot</th>
                        <th class="text-center">Validasi</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 0; @endphp
                    @foreach($jadwal as $semester => $data)
                        @php $cek_mk = []; @endphp
                        @foreach($data as $row)
                            @php 
                                $background = ($row->Aktif == 'Ya' ? 'style="background:rgb(233, 255, 228);"' : '');
                                $totalPeserta = ($row->gabungan == 'YA' && isset($jadwalGabungan[$row->jadwalID])) ? $jadwalGabungan[$row->jadwalID]->jumlahPeserta : $row->totalPeserta;
                            @endphp
                            <tr {!! $background !!}>
                                @if(!in_array($row->matkulID, $cek_mk))
                                    <td class="text-center" rowspan="{{ $rowSpan['mk'][$row->matkulID] }}">{{ ++$no }}</td>
                                    <td rowspan="{{ $rowSpan['mk'][$row->matkulID] }}">
                                        <b>{{ $row->mkkode }}</b><br>
                                        {{ $row->namaMatkul }}<br>
                                        <span class="badge badge-success">Semester {{ $row->semester }}</span>
                                        @if($row->gabungan == 'YA')
                                            <span class="badge badge-info">Gabungan</span>
                                        @endif
                                    </td>
                                    <td class="text-center" rowspan="{{ $rowSpan['mk'][$row->matkulID] }}">{{ $row->totalSKS }}</td>
                                @endif

                                <td class="text-center">{{ $row->namaKelas }}</td>
                                <td>
                                    1. {{ $row->title ? $row->title . ' ' : '' }}{{ $row->namaDosen }}{{ $row->gelar ? ', ' . $row->gelar : '' }}
                                    @if($row->dosenAnggota)
                                        <span class="badge badge-primary">(K)</span>
                                        @php 
                                            $anggota = explode(',', $row->dosenAnggota);
                                            $idx = 2;
                                        @endphp
                                        @foreach($anggota as $aid)
                                            @php $d = DB::table('dosen')->where('ID', $aid)->first(); @endphp
                                            @if($d)
                                                <br>{{ $idx++ }}. {{ $d->Title ? $d->Title . ' ' : '' }}{{ $d->Nama }}{{ $d->Gelar ? ', ' . $d->Gelar : '' }}
                                            @endif
                                        @endforeach
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="javascript:void(0);" class="badge {{ $totalPeserta > 0 ? 'badge-success' : 'badge-dark' }}" 
                                       onclick="{{ $totalPeserta > 0 ? "lihat_peserta('$row->jadwalID', '$row->matkulID', '$row->kurikulumID')" : "" }}">
                                        {{ $totalPeserta }} Orang
                                    </a>
                                </td>
                                <td>
                                    @if(isset($bobotnilai[$row->jadwalID]) && count($bobotnilai[$row->jadwalID]) > 0)
                                        <ul class="pl-3 mb-0">
                                            @foreach($bobotnilai[$row->jadwalID] as $bn)
                                                @if($bn->JenisBobotID != 1)
                                                    <li>{{ $bn->jenisnama }} ({{ $bn->Persen }}%)</li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <small>
                                        <strong>KHS:</strong> {{ ($validasiKHS[$row->jadwalID] ?? 0) == $totalPeserta ? 'OK' : ($validasiKHS[$row->jadwalID] ?? 0) .'/'. $totalPeserta }}<br>
                                        <strong>Trnskp:</strong> {{ ($validasiTranskrip[$row->jadwalID] ?? 0) == $totalPeserta ? 'OK' : ($validasiTranskrip[$row->jadwalID] ?? 0) .'/'. $totalPeserta }}<br>
                                        <strong>Dosen:</strong> {{ ($validasiDosen[$row->jadwalID] ?? 0) == $totalPeserta ? 'OK' : ($validasiDosen[$row->jadwalID] ?? 0) .'/'. $totalPeserta }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group dropleft">
                                        <button class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown">Opsi</button>
                                        <div class="dropdown-menu">
                                            @if($totalPeserta > 0)
                                                <a class="dropdown-item" target="_blank" href="{{ url('nilai/add/'.$row->jadwalID) }}"><i class="mdi mdi-pencil"></i> Input Nilai</a>
                                            @endif
                                            <a class="dropdown-item" href="javascript:void(0);" onclick="detailJadwal('{{ $row->jadwalID }}')"><i class="mdi mdi-calendar"></i> Detail Jadwal</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @php $cek_mk[] = $row->matkulID; @endphp
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Participants -->
<div class="modal fade" id="peserta_krs_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Daftar Peserta Kuliah</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>No</th>
                                <th>NPM</th>
                                <th>Nama</th>
                                <th>Prodi</th>
                                <th>Kelas</th>
                            </tr>
                        </thead>
                        <tbody id="list_peserta_krs"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function uploadExcel(form) {
    var formData = new FormData(form);
    $.ajax({
        type: 'POST',
        url: $(form).attr('action'),
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function() {
            swal({ title: "Mohon Tunggu", text: "Sedang mengunggah data...", icon: "info", buttons: false });
        },
        success: function(data) {
            swal("Berhasil", "Data berhasil diunggah", "success");
            $('#mdl-upload').modal('hide');
            $('#lihat_hasil_upload').show();
        },
        error: function() {
            swal("Gagal", "Terjadi kesalahan saat mengunggah", "error");
        }
    });
}

function lihat_peserta(jadwalID, matkulID, kurikulumID) {
    $.ajax({
        url: "{{ url('jadwal/cetakpeserta') }}",
        type: "POST",
        data: { _token: "{{ csrf_token() }}", JadwalID: jadwalID, DetailKurikulumID: matkulID, KurikulumID: kurikulumID },
        success: function(data) {
            var html = '';
            $.each(data, function(i, v) {
                html += '<tr><td>'+(i+1)+'</td><td>'+v.npm+'</td><td>'+v.nama+'</td><td>'+v.namaProdi+'</td><td>'+v.namaKelas+'</td></tr>';
            });
            $('#list_peserta_krs').html(html);
            $('#peserta_krs_modal').modal('show');
        }
    });
}

function detailJadwal(jadwalID) {
    $.ajax({
        type: "POST",
        url: "{{ url('rencanastudi/detailJadwal') }}",
        data: { _token: "{{ csrf_token() }}", jadwalID: jadwalID },
        success: function(data) {
            if (data.status == '1') {
                // ... show details logic ...
                swal("Info", "Detail Jadwal Loaded", "info");
            }
        }
    });
}
</script>
