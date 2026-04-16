<!DOCTYPE html>
<html>
<head>
    <title>Transkrip Sementara</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 2px 5px; }
        .no-border td, .no-border th { border: none; }
        .border { border: 1px solid #000; }
        .border td, .border th { border: 1px solid #000; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .header { text-align: center; font-weight: bold; font-size: 15px; padding-bottom: 17px; }
        .label_header td { margin: 0; padding: 1px 2px; font-size: 13px; vertical-align: top; }
        .body_transkrip th {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            text-align: center;
            font-size: 13px;
            vertical-align: middle;
            padding: 2px;
        }
        .body_transkrip td {
            vertical-align: middle;
            text-align: center;
            font-size: 13px;
            padding: 2px;
        }
        .body_transkrip tr:last-child td {
            border-bottom: 1px solid #000;
        }
        .body_transkrip td.text-left {
            text-align: left;
        }
        tfoot { font-weight: bold; }
    </style>
</head>
<body>
    {{-- Header Universitas --}}
    @if(function_exists('cetak_header'))
        {!! cetak_header() !!}
    @endif

    <table class="no-border" style="width: 100%; margin-top: 10px;">
        <tr>
            <td class="header">TRANSKRIP SEMENTARA</td>
        </tr>
    </table>

    <table class="no-border label_header" style="width: 100%; font-size: 13px;">
        <tr>
            <td class="text-left" style="width: 120px;">Nama Mahasiswa</td>
            <td class="text-left" style="width: 10px;">:</td>
            <td class="text-left" style="width: 340px;">{{ ucwords(strtolower($mhs->Nama ?? '')) }}</td>
            <td class="text-left" style="width: 120px;">Tempat Lahir</td>
            <td class="text-left" style="width: 10px;">:</td>
            <td class="text-left" style="width: 119px;">{{ $mhs->TempatLahir ?? '' }}</td>
        </tr>
        <tr>
            <td class="text-left">No. Induk Mahasiswa</td>
            <td class="text-left">:</td>
            <td class="text-left">{{ $mhs->NPM ?? '' }}</td>
            <td class="text-left">Tanggal Lahir</td>
            <td class="text-left">:</td>
            <td class="text-left">{{ tgl($mhs->TanggalLahir ?? '', '02') }}</td>
        </tr>
        <tr>
            <td class="text-left">Jenjang Studi</td>
            <td class="text-left">:</td>
            <td class="text-left">{{ get_field($mhs->JenjangID ?? '', 'jenjang') }}</td>
            <td class="text-left">Tahun Masuk</td>
            <td class="text-left">:</td>
            <td class="text-left">{{ $mhs->TahunMasuk ?? '' }}</td>
        </tr>
        <tr>
            <td class="text-left">Program Studi</td>
            <td class="text-left">:</td>
            <td class="text-left">{{ get_field($mhs->ProdiID ?? '', 'programstudi') }}</td>
            <td class="text-left" colspan="3"></td>
        </tr>
    </table>

    <table class="border body_transkrip" style="width: 100%; margin-top: 10px;" cellspacing="0">
        <thead>
            <tr>
                <th style="text-align: center; width: 15px;">NO</th>
                <th style="text-align: center; width: 89px;">KODE MK</th>
                <th style="text-align: center; width: 300px;">MATA KULIAH</th>
                <th style="text-align: center; width: 60px;">BOBOT SKS</th>
                <th style="text-align: center; width: 60px;">NILAI</th>
                <th style="text-align: center; width: 60px;">LAMBANG</th>
                <th style="text-align: center; width: 90px;">NILAI x BOBOT</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_sks = 0;
                $total_bobot = 0;
                $no = 0;
            @endphp
            @foreach($query as $row)
                <tr>
                    <td style="text-align: center;">{{ ++$no }}</td>
                    <td style="text-align: center;">{{ $row->MKKode ?? '' }}</td>
                    <td class="text-left">{{ $row->NamaMataKuliah ?? '' }}</td>
                    <td style="text-align: center;">{{ $row->TotalSKS ?? 0 }}</td>
                    <td style="text-align: center;">{{ $row->Bobot ?? 0 }}</td>
                    <td style="text-align: center;">{{ $row->NilaiHuruf ?? '' }}</td>
                    <td style="text-align: center;">{{ ($row->TotalSKS ?? 0) * ($row->Bobot ?? 0) }}</td>
                </tr>
                @php
                    $total_sks += ($row->TotalSKS ?? 0);
                    $total_bobot += (($row->TotalSKS ?? 0) * ($row->Bobot ?? 0));
                @endphp
            @endforeach
            <tr>
                <td class="text-right" colspan="3">Jumlah</td>
                <td style="text-align: center;">{{ $total_sks }}</td>
                <td colspan="2"></td>
                <td style="text-align: center;">{{ $total_bobot }}</td>
            </tr>
            <tr>
                <td class="text-right" colspan="3">Indeks Prestasi Kumulatif</td>
                <td colspan="4" style="text-align: center;">
                    {{ $total_sks > 0 ? number_format($total_bobot / $total_sks, 2, '.', '') : '0.00' }}
                </td>
            </tr>
        </tbody>
    </table>

    <table class="no-border" style="width: 100%; font-size: 13px; margin-top: 0;">
        <tr>
            <td style="width: 60%;"></td>
            <td style="vertical-align: bottom;">
                @php
                    $kota = $kota ?? null;
                    $kotaText = $kota ? substr($kota->Kota ?? '', 4) : '';
                    $tglCetak = $tgl_cetak ?? date('Y-m-d');
                @endphp
                {{ $kotaText }}, {{ tgl($tglCetak, '02') }}<br>
                Ka. Program Studi<br>
                <br><br><br><br><br><br><br>
                @php
                    $prodi = DB::table('programstudi')->where('ID', $mhs->ProdiID ?? 0)->first();
                    $kaProdiId = $prodi->KAProdiID ?? 0;
                    $dekan = $kaProdiId ? DB::table('dosen')->where('ID', $kaProdiId)->first() : null;
                    $namaDekan = '................................';
                    if ($dekan) {
                        $namaDekan = ($dekan->Title ? $dekan->Title.' ' : '').($dekan->Nama ?? '').($dekan->Gelar ? ' '.$dekan->Gelar : '');
                    }
                @endphp
                {{ $namaDekan }}
            </td>
        </tr>
    </table>
</body>
</html>
