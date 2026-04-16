<!DOCTYPE html>
<html>
<head>
    <title>Transkrip Akademik</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 3px; text-align: left; }
        .no-border td, .no-border th { border: none; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .header { text-align: center; font-weight: bold; font-size: 14px; margin-bottom: 10px; }
        .bg-grey { background-color: #eee; }
    </style>
</head>
<body>
    <div class="header">
        TRANSKRIP AKADEMIK
    </div>

    <table class="no-border">
        <tr>
            <td width="150px">Nama Mahasiswa</td>
            <td width="10px">:</td>
            <td width="300px">{{ strtoupper($mhs->Nama ?? '') }}</td>
            <td width="120px">Tempat Lahir</td>
            <td width="10px">:</td>
            <td>{{ $mhs->TempatLahir ?? '' }}</td>
        </tr>
        <tr>
            <td>No. Induk Mahasiswa</td>
            <td>:</td>
            <td>{{ $mhs->NPM ?? '' }}</td>
            <td>Tanggal Lahir</td>
            <td>:</td>
            <td>{{ tgl($mhs->TanggalLahir ?? '', '02') }}</td>
        </tr>
        <tr>
            <td>Jenjang Studi</td>
            <td>:</td>
            <td>{{ $mhs->NamaJenjang ?? '' }}</td>
            <td>Tahun Masuk</td>
            <td>:</td>
            <td>{{ $mhs->TahunMasuk ?? '' }}</td>
        </tr>
        <tr>
            <td>Program Studi</td>
            <td>:</td>
            <td>{{ $mhs->NamaProdi ?? '' }}</td>
            <td>Tahun Lulus</td>
            <td>:</td>
            <td>{{ tgl($mhs->TanggalLulus ?? '', '02') }}</td>
        </tr>
        <tr>
            <td>No Seri Transkrip</td>
            <td>:</td>
            <td>{{ $Nomor ?? '' }}</td>
            <td colspan="3"></td>
        </tr>
    </table>

    <table>
        <thead>
            <tr class="bg-grey">
                <th class="text-center" width="30px">NO</th>
                <th class="text-center" width="80px">KODE MK</th>
                <th class="text-center">MATA KULIAH</th>
                <th class="text-center" width="60px">BOBOT SKS</th>
                <th class="text-center" width="60px">NILAI</th>
                <th class="text-center" width="60px">LAMBANG</th>
                <th class="text-center" width="80px">NILAI x BOBOT</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $total_sks = 0; 
                $total_bobot = 0;
                $no = 1;
            @endphp
            @foreach($query as $row)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td class="text-center">{{ $row->MKKode ?? '' }}</td>
                    <td>{{ $row->NamaMataKuliah ?? '' }}</td>
                    <td class="text-center">{{ $row->TotalSKS ?? 0 }}</td>
                    <td class="text-center">{{ $row->Bobot ?? 0 }}</td>
                    <td class="text-center">{{ $row->NilaiHuruf ?? '' }}</td>
                    <td class="text-center">{{ ($row->TotalSKS ?? 0) * ($row->Bobot ?? 0) }}</td>
                </tr>
                @php 
                    $total_sks += ($row->TotalSKS ?? 0);
                    $total_bobot += (($row->TotalSKS ?? 0) * ($row->Bobot ?? 0));
                @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-grey">
                <th colspan="3" class="text-right">Jumlah</th>
                <th class="text-center">{{ $total_sks }}</th>
                <th colspan="2"></th>
                <th class="text-center">{{ $total_bobot }}</th>
            </tr>
            <tr class="bg-grey">
                <th colspan="3" class="text-right">Indeks Prestasi Kumulatif</th>
                <th colspan="4" class="text-center">
                    {{ $total_sks > 0 ? number_format($total_bobot / $total_sks, 2) : '0.00' }}
                </th>
            </tr>
        </tfoot>
    </table>

    <table class="no-border" style="margin-top: 10px;">
        <tr>
            <td colspan="3">
                <strong>Judul {{ ($mhs->NamaJenjang ?? '') == 'S1' ? 'Skripsi' : 'Tesis' }} : </strong><br>
                {{ $Judul ?? '' }}
            </td>
        </tr>
    </table>

    <table class="no-border" style="margin-top: 20px;">
        <tr>
            <td width="60%"></td>
            <td class="text-center">
                {{ substr($kota->Kota ?? '', 5) }}, {{ tgl($tgl_cetak ?? date('Y-m-d'), '02') }}<br>
                @php 
                    $prodi = DB::table('programstudi')->where('ID', $mhs->ProdiID)->first();
                    $dekan = DB::table('dosen')->where('ID', $prodi->KAProdiID ?? 0)->first();
                    $namaDekan = ($dekan->Title ?? '').' '.($dekan->Nama ?? '').($dekan->Gelar ? ', '.$dekan->Gelar : '');
                @endphp
                Ka. Program Studi<br><br><br><br><br>
                <strong>{{ $namaDekan }}</strong><br>
                NIDN : {{ $dekan->NIDN ?? '' }}
            </td>
        </tr>
    </table>
</body>
</html>
