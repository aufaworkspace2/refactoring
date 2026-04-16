@php
    $SKSTeori = $mk->SKSTatapMuka;
    $SKSPraktik = $mk->SKSPraktikum + $mk->SKSPraktekLap;

    $KategoriJenisBobot = 0;
    if(empty($SKSPraktik) && $SKSTeori > 0){
        $KategoriJenisBobot = 1;
    }elseif(empty($SKSTeori) && $SKSPraktik > 0){
        $KategoriJenisBobot = 2;
    }

    $kategori_query = DB::table('kategori_jenisbobot');
    if(!empty($KategoriJenisBobot)) $kategori_query->where("ID", $KategoriJenisBobot);
    $kategori_jenisbobot = $kategori_query->get();
    $KategoriIDs = $kategori_jenisbobot->pluck('ID')->toArray();

    $bobot_query = DB::table('bobotnilai')
        ->select('bobotnilai.Persen', 'bobotnilai.JenisBobotID', 'jenisbobot.Nama as jenisnama', 'jenisbobot.Modify', 'jenisbobot.KategoriJenisBobotID')
        ->join('jenisbobot', 'jenisbobot.ID', '=', 'bobotnilai.JenisBobotID')
        ->where('bobotnilai.Persen', '>', 0)
        ->where('bobotnilai.JadwalID', $jadwalID)
        ->whereIn('jenisbobot.KategoriJenisBobotID', $KategoriIDs)
        ->orderBy('jenisbobot.Urut', 'ASC');
    
    $Q2 = $bobot_query->get();
    $jumlah = $Q2->count();
    $dataAllBobotNew = $Q2->groupBy('KategoriJenisBobotID');
    $ada_nilai = 0;
    $opsi = 1; // Default to detail mode
@endphp

<div class="table-responsive">
    <table width="100%" class="table table-bordered table-hover checkboxs">
        <thead class="bg-primary text-white">
            <tr>
                <th rowspan="2" class="text-center" width="2%">
                    <div class="checkbox checkbox-info">
                        <input type="checkbox" name="checkAll" id="checkAll" class="selectAll" />
                        <label for="checkAll"></label>
                    </div>
                </th>
                <th rowspan="2" class="text-center" width="3%">No.</th>
                <th rowspan="2" class="text-center" width="10%">Nama</th>
                @if($opsi == 1 && $jumlah > 0)
                    @foreach($kategori_jenisbobot as $kat_jb)
                        @php $cols = $Q2->where('KategoriJenisBobotID', $kat_jb->ID)->count(); @endphp
                        @if($cols > 0)
                            <th class="text-center" colspan="{{ $cols }}">{{ $kat_jb->Nama }}</th>
                        @endif
                    @endforeach
                @endif
                <th rowspan="2" class="text-center" width="12%">Nilai Akhir</th>  
                <th rowspan="2" class="text-center" width="12%">Nilai Huruf<br><small>(Grade)</small></th>
                <th rowspan="2" class="text-center" width="8%">Status Publish</th>
            </tr>
            @if($opsi == 1)
            <tr>
                @foreach($kategori_jenisbobot as $kat_jb)
                    @foreach($Q2->where('KategoriJenisBobotID', $kat_jb->ID) as $hasil)
                        <th class="text-center" width="11%">{{ $hasil->jenisnama }}<br>({{ $hasil->Persen }}%)</th>
                    @endforeach
                @endforeach
            </tr>
            @endif
        </thead>
        <tbody>
            @foreach($query as $index => $row)
                @php 
                    if(!empty($row->nilaiID)) $ada_nilai++;
                    $cek_grade = $arr_cek_grade[$row->MhswID] ?? [];
                    $a = $index + 1;
                @endphp
                <tr style="background: {{ $row->jadwalID != $jadwalID ? 'lightgoldenrodyellow' : 'white' }}">
                    <td class="text-center">
                        @if(!empty($row->nilaiID) && count($cek_grade) > 0)
                            <div class="checkbox checkbox-info">
                                <input type="checkbox" name="checkID[]" value="{{ $row->MhswID }}" class="checkID" id="checkID{{ $a }}" onclick="show_dropdown_edit()">
                                <label for="checkID{{ $a }}"></label>
                            </div>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">{{ $a }}</td>
                    <td>
                        <small><b>{{ $row->npm }}</b><br>{{ $row->nama }}</small>
                        <input type="hidden" name="mhswID[]" value="{{ $row->MhswID }}" />
                        <input type="hidden" name="rencanastudiID[{{ $row->MhswID }}]" value="{{ $row->rencanastudiID }}" />
                    </td>

                    @if($opsi == 1)
                        @if(count($cek_grade) > 0)
                            @foreach($kategori_jenisbobot as $kat_jb)
                                @foreach($Q2->where('KategoriJenisBobotID', $kat_jb->ID) as $hasil)
                                    @php 
                                        $ambil = DB::table('bobot_mahasiswa')
                                            ->where('MhswID', $row->MhswID)
                                            ->where('DetailKurikulumID', $DetailKurikulumID)
                                            ->where('TahunID', $TahunID)
                                            ->where('JenisBobotID', $hasil->JenisBobotID)
                                            ->first();
                                        $nilai = $ambil->Nilai ?? 0;
                                        if($hasil->JenisBobotID == 1 && empty($nilai)) {
                                            $nilai = $persentasePresensi[$row->MhswID] ?? 0;
                                        }
                                    @endphp
                                    <td class="text-center align-middle">
                                        @if($row->ValidasiDosen == 1 || $row->Lock == 1)
                                            {{ $nilai }}
                                        @else
                                            <input type="hidden" name="jenisBobot[{{ $row->MhswID }}][{{ $kat_jb->ID }}][]" value="{{ $hasil->JenisBobotID }}">
                                            <input type="hidden" name="persenBobot[{{ $row->MhswID }}][{{ $kat_jb->ID }}][]" value="{{ $hasil->Persen }}">
                                            <input type="number" class="form-control form-control-sm text-center" 
                                                   name="nilaiBobot[{{ $row->MhswID }}][{{ $kat_jb->ID }}][]" 
                                                   value="{{ $nilai }}" 
                                                   onblur="proses('{{ $row->MhswID }}', '{{ $a }}', '{{ $kat_jb->ID }}');"
                                                   step="0.01" max="100">
                                        @endif
                                    </td>
                                @endforeach
                            @endforeach
                        @else
                            <td colspan="{{ $jumlah }}">Bobot belum diatur</td>
                        @endif
                    @endif

                    <td class="text-center align-middle">
                        @if($row->ValidasiDosen == 1 || $row->Lock == 1)
                            <b>{{ $row->akhirNilai }}</b>
                        @else
                            <input class="text-center form-control form-control-sm" value="{{ $row->akhirNilai }}" type="text" id="c{{ $a }}" name="nilaiAkhir[{{ $row->MhswID }}]" readonly />
                        @endif
                    </td>
                    <td class="text-center align-middle">
                        @if($row->ValidasiDosen == 1 || $row->Lock == 1)
                            <b>{{ $row->hurufNilai }}</b>
                        @else
                            <input class="text-center form-control form-control-sm" value="{{ $row->hurufNilai }}" readonly type="text" id="b{{ $a }}" name="nilaiHuruf[{{ $row->MhswID }}]" />
                        @endif
                    </td>
                    <td class="text-center align-middle">
                        <span class="badge {{ ($row->PublishTranskrip == '1' && $row->PublishKHS == '1') ? 'badge-success' : 'badge-secondary' }}">
                            {{ ($row->PublishTranskrip == '1' && $row->PublishKHS == '1') ? 'Terbit' : 'Belum' }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    $('.selectAll').click(function(e){
        var table = $(e.target).closest('table');
        $('td input:checkbox', table).prop('checked', $(this).prop("checked"));
        show_dropdown_edit();
    });

    function show_dropdown_edit(){
        var anyChecked = $('.checkID:checked').length > 0;
        if(anyChecked) {
            $('#dropdown_edit').removeAttr('disabled').attr('href', '#edit');
        } else {
            $('#dropdown_edit').attr('disabled', 'disabled').attr('href', '#');
        }
    }
</script>
