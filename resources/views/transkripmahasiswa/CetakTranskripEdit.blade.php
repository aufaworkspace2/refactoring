<!DOCTYPE html>
<html>
<head>
    <title>Transkrip Akademik - {{ $NPM ?? '' }}</title>
    <style>
        @page {
            margin: 30mm 20mm 2mm 2mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 13px;
            line-height: 1.2;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        td {
            vertical-align: top;
        }
        .header-table td {
            font-weight: bold;
        }
        .title {
            text-align: center;
            font-size: 25px;
            font-weight: bold;
            margin: 20px 0;
        }
        .info-table td {
            padding: 2px 0;
        }
        .data-table {
            margin-top: 20px;
        }
        .data-table th, .data-table td {
            border: 1px solid black;
            padding: 4px;
            text-align: center;
            font-size: 12px;
        }
        .data-table th {
            font-weight: bold;
        }
        .data-table .text-left {
            text-align: left;
        }
        .border-none {
            border: none !important;
        }
        .td-mid {
            border-top: none !important;
            border-bottom: none !important;
        }
        .td-end {
            border-top: none !important;
            border-bottom: 2px solid black !important;
        }
        .footer-table {
            margin-top: 30px;
        }
        .signature {
            margin-top: 30px;
            margin-left: 60%;
            text-align: center;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 250px;">Nomor Seri Transkrip Akademik</td>
            <td>: &nbsp;&nbsp; {{ $Nomor ?? '' }}</td>
        </tr>
    </table>

    <div class="title">TRANSKRIP AKADEMIK</div>

    <table class="info-table">
        <tr>
            <td style="width: 23%;"><b>Nama Mahasiswa</b></td>
            <td><b>: &nbsp; {{ $Nama ?? '' }}</b></td>
        </tr>
        <tr>
            <td><b>NPM</b></td>
            <td>: &nbsp; {{ $NPM ?? '' }}</td>
        </tr>
        <tr>
            <td><b>Tempat / Tanggal Lahir</b></td>
            <td>: &nbsp; {{ $TempatLahir ?? '' }} / {{ tgl($TanggalLahir ?? '', '02') }}</td>
        </tr>
        <tr>
            <td><b>Fakultas</b></td>
            <td>: &nbsp; {{ $NamaFakultas ?? '' }}</td>
        </tr>
        <tr>
            <td><b>Program Studi</b></td>
            <td>: &nbsp; {{ $ProdiID ?? '' }}</td>
        </tr>
        <tr>
            <td><b>Program Pendidikan</b></td>
            <td>: &nbsp; {{ $jenjang->Nama ?? '' }} ({{ $jenjang->NamaPanjang2 ?? '' }})</td>
        </tr>
        <tr>
            <td><b>Status</b></td>
            <td>: &nbsp; Terakreditasi Nomor : <b>{{ $NomorAkreditasi ?? '' }}</b></td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20px;">No</th>
                <th style="width: 200px;">Nama Mata Kuliah</th>
                <th style="width: 25px;">SKS</th>
                <th style="width: 35px;">Nilai</th>
                <th class="border-none" style="width: 10px; background: white;"></th>
                <th style="width: 20px;">No</th>
                <th style="width: 200px;">Nama Mata Kuliah</th>
                <th style="width: 25px;">SKS</th>
                <th style="width: 35px;">Nilai</th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 0; $i < ($max_tablerow ?? 0); $i++)
                @php
                    $rows = $data_transkrip[$i] ?? [null, null];
                    $row1 = $rows[0] ? (object) $rows[0] : null;
                    $row2 = $rows[1] ? (object) $rows[1] : null;
                @endphp
                <tr>
                    @if ($row1)
                        <td>{{ $i + 1 }}</td>
                        <td class="text-left">{{ $row1->NamaMataKuliah }}</td>
                        <td>{{ $row1->TotalSKS }}</td>
                        <td>{{ $row1->NilaiHuruf }}</td>
                    @else
                        <td></td><td></td><td></td><td></td>
                    @endif

                    <td class="border-none"></td>

                    @if ($row2)
                        <td>{{ $i + 1 + ($breakpoint ?? 0) }}</td>
                        <td class="text-left">{{ $row2->NamaMataKuliah }}</td>
                        <td>{{ $row2->TotalSKS }}</td>
                        <td>{{ $row2->NilaiHuruf }}</td>
                    @else
                        <td></td><td></td><td></td><td></td>
                    @endif
                </tr>
            @endfor
        </tbody>
    </table>

    <table class="footer-table">
        <tr>
            <td style="width: 120px;">TOTAL SKS</td>
            <td style="width: 70px;"> : &nbsp;&nbsp; {{ $skstotal ?? 0 }}</td>
            <td style="width: 220px;">INDEKS PRESTASI KUMULATIF</td>
            <td style="width: 80px;"> : &nbsp;&nbsp; {{ $ipk_hitung_titik ?? '0.00' }}</td>
            <td style="width: 80px;">PREDIKAT</td>
            <td> : &nbsp;&nbsp; {{ ucwords(strtolower($predikat ?? '')) }}</td>
        </tr>
        <tr>
            <td>TANGGAL LULUS</td>
            <td colspan="5"> : &nbsp;&nbsp; {{ tgl($TanggalLulus ?? '', '02') }}</td>
        </tr>
        <tr>
            <td>KARYA ILMIAH</td>
            <td colspan="5"> : &nbsp;&nbsp; {{ $JudulSkripsi ?? '' }}</td>
        </tr>
    </table>

    <div class="signature">
        DEKAN<br><br><br><br><br><br>
        {{ $Dekan ?? '' }}
    </div>
</body>
</html>
