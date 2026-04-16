<!DOCTYPE html>
<html>
<head>
    <title>Laporan Status Input Nilai</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .header { text-align: center; font-weight: bold; font-size: 14px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="header">
        LAPORAN STATUS INPUT NILAI
    </div>

    <table>
        <thead>
            <tr class="text-center">
                <th width="3%">No.</th>
                <th width="25%">Dosen</th>
                <th width="8%">Kode MK</th>
                <th width="25%">Mata Kuliah</th>
                <th width="10%">Kelas</th>
                <th width="10%">Status Input</th>
                <th width="10%">Persentase (%)</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 0; @endphp
            @foreach($query as $row)
            <tr>
                <td class="text-center">{{ ++$no }}</td>
                <td>
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
                        {{ $namaDosen }} [K]
                    @endif
                    @if($countDosen > 0 && !empty($row->dosenAnggota))
                        <br><br><strong>Dosen Anggota :</strong><br>
                        @foreach($dosenAnggotaExp as $dosenAnggotaId)
                            @php
                                $dosenAng = get_id(trim($dosenAnggotaId), 'dosen');
                            @endphp
                            @if($dosenAng)
                                <strong>{{ $dosenAng->NIP ?? '' }}</strong><br>
                                {{ $dosenAng->Title ?? '' }} {{ $dosenAng->Nama ?? '' }} {{ $dosenAng->Gelar ?? '' }}<br>
                            @endif
                        @endforeach
                    @endif
                </td>
                <td class="text-center">{{ $row->mkkode ?? '' }}</td>
                <td>
                    {{ html_entity_decode($row->namaMatkul ?? '') }}
                    @if($row->gabungan == 'YA')
                        <br>(Jadwal Gabungan)
                    @endif
                </td>
                <td class="text-center">{{ get_field($row->kelasID, 'kelas') }}</td>
                <td class="text-center">
                    @if($row->persentaseNilai > 0)
                        Sudah
                    @else
                        Belum
                    @endif
                </td>
                <td class="text-center">{{ $row->persentaseNilai ?? 0 }}%</td>
            </tr>
            @endforeach
            @if(empty($query))
            <tr>
                <td colspan="7" class="text-center">Maaf jadwal yang anda cari tidak ditemukan</td>
            </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
