<!DOCTYPE html>
<html>
<head>
	<title>Kartu Rencana Studi - Batch</title>
	<style>
		body { font-family: sans-serif; font-size: 11px; }
		table { width: 100%; border-collapse: collapse; margin-top: 5px; }
		th, td { border: 1px solid #000; padding: 3px; text-align: left; }
		.no-border td, .no-border th { border: none; }
		.header { text-align: center; }
        .text-center { text-align: center; }
        .bg-grey { background-color: #ddd; }
        .page-break { page-break-after: always; }
	</style>
</head>
<body>
    @foreach ($data_mahasiswa as $mhswdata)
    <div class="{{ !$loop->last ? 'page-break' : '' }}">
        <div class="header">
            <h3>Kartu Rencana Studi</h3>
        </div>

        <table class="no-border">
            <tr>
                <td width="10%">N I M</td>
                <td width="2%">:</td>
                <td width="40%">{{ strtoupper($mhswdata['NPM'] ?? '') }}</td>
                <td width="16%">Prog/Jen Studi</td>
                <td width="2%">:</td>
                <td width="30%">{{ $mhswdata['ProdiID'] ?? '' }} - {{ get_field($mhswdata['JenjangID'] ?? 0, 'jenjang') }}</td>
            </tr>
            <tr>
                <td>N a m a</td>
                <td>:</td>
                <td>{{ strtoupper($mhswdata['Nama'] ?? '') }}</td>
                <td>Tahun Akademik</td>
                <td>:</td>
                <td>{{ substr($Tahun->Nama ?? '', 0, 9) }} - {{ $Tahun->Semester ?? '' }}</td>
            </tr>
            <tr>
                <td>Semester</td>
                <td>:</td>
                <td>{{ get_semester($mhswdata['MhswID'] ?? 0, $Tahun->ID ?? 0)->Semester ?? '' }}</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </table>

        <table>
            <thead>
                <tr class="bg-grey">
                    <th class="text-center" width="5%">No</th>
                    <th class="text-center" width="10%">Kode</th>
                    <th class="text-center" width="35%">Mata Kuliah</th>
                    <th class="text-center" width="5%">SKS</th>
                    <th class="text-center" width="5%">T</th>
                    <th class="text-center" width="5%">P</th>
                    <th class="text-center" width="10%">Kelas</th>
                    <th class="text-center" width="10%">Ruang</th>
                    <th class="text-center" width="15%">Waktu</th>
                </tr>
            </thead>
            <tbody>
                @php $jumlahsks = 0; @endphp
                @if(count($mhswdata['query']) > 0)
                    @foreach($mhswdata['query'] as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $row->MKKode ?? '' }}</td>
                        <td>{{ $row->NamaMatakuliah ?? '' }}</td>
                        <td class="text-center">{{ $row->TotalSKS ?? 0 }}</td>
                        <td class="text-center">{{ $row->SKSTatapMuka ?? 0 }}</td>
                        <td class="text-center">{{ ($row->SKSPraktikum ?? 0) + ($row->SKSPraktekLap ?? 0) }}</td>
                        <td class="text-center">{{ get_field($row->KelasID ?? 0, 'kelas') }}</td>
                        <td class="text-center">
                            @php
                                $jadwal = DB::table('jadwal')->where('ID', $row->JadwalID)->first();
                                if($jadwal){
                                    $jadwalwaktu = DB::table('jadwalwaktu')->where('JadwalID', $jadwal->ID)->groupBy('RuangID')->get();
                                    foreach($jadwalwaktu as $jw){
                                        echo get_field($jw->RuangID, 'ruang') . "<br>";
                                    }
                                }
                            @endphp
                        </td>
                        <td class="text-center">
                            @php
                                if($jadwal){
                                    $jadwalwaktu = DB::table('jadwalwaktu')->where('JadwalID', $jadwal->ID)->get();
                                    $displayed_waktu = [];
                                    foreach($jadwalwaktu as $jw){
                                        $kodewaktu = DB::table('kodewaktu')->where('ID', $jw->WaktuID)->first();
                                        $hari = get_field($jw->HariID, 'hari');
                                        $wkt = $hari . ' ' . substr($kodewaktu->JamMulai ?? '', 0, 5) . "-" . substr($kodewaktu->JamSelesai ?? '', 0, 5);
                                        if(!in_array($wkt, $displayed_waktu)){
                                            echo $wkt . "<br>";
                                            $displayed_waktu[] = $wkt;
                                        }
                                    }
                                }
                            @endphp
                        </td>
                    </tr>
                    @php $jumlahsks += ($row->TotalSKS ?? 0); @endphp
                    @endforeach
                @else
                    <tr>
                        <td colspan="9" class="text-center">TIDAK ADA DATA DI TEMUKAN</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <table class="no-border">
            <tr>
                <td colspan="3">Jumlah SKS : {{ $jumlahsks }}</td>
            </tr>
            <tr>
                <td width="33%" style="text-align: center; vertical-align: top;">
                    Mengetahui/Menyetujui,<br>
                    {{ ($mhswdata['prodi']->JenjangID ?? '') == '35' ? 'Direktur Pasca Sarjana' : 'Ketua Program Studi' }}<br><br><br><br><br>
                    ________________________________<br>
                    @php
                        $kaProdi = get_id($mhswdata['prodi']->KaProdiID ?? 0, 'dosen');
                        $titleKa = !empty($kaProdi->Title) ? $kaProdi->Title . ', ' : '';
                        $gelarKa = !empty($kaProdi->Gelar) ? ', ' . $kaProdi->Gelar : '';
                        $namaKa	= $titleKa . strtoupper($kaProdi->Nama ?? '') . $gelarKa;
                    @endphp
                    {{ $namaKa }}<br>
                    NIK : {{ $kaProdi->NIP ?? '' }}
                </td>
                <td width="33%" style="text-align: center; vertical-align: top;">
                    Mengetahui/Menyetujui,<br>
                    Pembimbing Akademik<br><br><br><br><br>
                    ________________________________<br>
                    @php
                        $setpembimbing = DB::table('setpembimbing')->where('MhswID', $mhswdata['MhswID'] ?? 0)->first();
                        $pembimbing = $setpembimbing ? DB::table('dosen')->where('ID', $setpembimbing->DosenID)->first() : null;
                        $titlePem = !empty($pembimbing->Title) ? $pembimbing->Title . ', ' : '';
                        $gelarPem = !empty($pembimbing->Gelar) ? ', ' . $pembimbing->Gelar : '';
                        $namaPem = $titlePem . strtoupper($pembimbing->Nama ?? '') . $gelarPem;
                    @endphp
                    {{ $namaPem }}<br>
                    NIDN : {{ $pembimbing->NIP ?? '' }}
                </td>
                <td width="33%" style="text-align: center; vertical-align: top;">
                    @php
                        $identitas = get_id(1, 'identitas');
                        $kota = get_wilayah($identitas->KotaPT ?? 0)->Kota ?? '';
                    @endphp
                    {{ substr($kota, 5) }}, {{ tgl($mhswdata['tgl_cetak'] ?? date('Y-m-d'), '02') }}<br>
                    Mahasiswa<br><br><br><br><br>
                    ________________________________<br>
                    {{ $mhswdata['Nama'] ?? '' }}<br>
                    NIM : {{ $mhswdata['NPM'] ?? '' }}
                </td>
            </tr>
        </table>
    </div>
    @endforeach
</body>
</html>
