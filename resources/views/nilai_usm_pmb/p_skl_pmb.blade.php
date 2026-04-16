<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Keterangan Lulus (SKL)</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            margin: 2cm;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 16pt;
            font-weight: bold;
        }
        .header p {
            margin: 3px 0;
            font-size: 11pt;
        }
        .content {
            text-align: justify;
            margin-top: 20px;
        }
        .data-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        .data-table td {
            padding: 5px;
            vertical-align: top;
        }
        .data-table td:first-child {
            width: 30%;
            font-weight: bold;
        }
        .data-table td:nth-child(2) {
            width: 5%;
        }
        .nilai-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .nilai-table th, .nilai-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        .nilai-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .signature {
            margin-top: 50px;
            float: right;
            text-align: center;
            width: 250px;
        }
        .signature-space {
            height: 80px;
        }
        .clear {
            clear: both;
        }
        @media print {
            body {
                margin: 1.5cm;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right; margin-bottom: 10px;">
        <button onclick="window.print()" style="padding: 5px 15px; cursor: pointer;">
            <i class="fa fa-print"></i> Print
        </button>
        <button onclick="window.close()" style="padding: 5px 15px; cursor: pointer;">
            Close
        </button>
    </div>

    <div class="header">
        <h2>SURAT KETERANGAN LULUS</h2>
        <p>Nomor: {{ $row->noujian_pmb ?? '' }}/SKL/{{ date('Y') }}</p>
    </div>

    <div class="content">
        <p>Yang bertanda tangan di bawah ini, Direktur PMB Universitas/Institut, menyatakan bahwa:</p>

        <table class="data-table">
            <tr>
                <td>Nama Lengkap</td>
                <td>:</td>
                <td>{{ $row->Nama ?? '' }}</td>
            </tr>
            <tr>
                <td>Tempat, Tanggal Lahir</td>
                <td>:</td>
                <td>{{ $row->TempatLahir ?? '' }}, {{ $row->TanggalLahir ? date('d F Y', strtotime($row->TanggalLahir)) : '' }}</td>
            </tr>
            <tr>
                <td>No. Identitas (KTP/NIK)</td>
                <td>:</td>
                <td>{{ $row->NoIdentitas ?? '' }}</td>
            </tr>
            <tr>
                <td>No. Ujian</td>
                <td>:</td>
                <td>{{ $row->noujian_pmb ?? '' }}</td>
            </tr>
            <tr>
                <td>Program Studi</td>
                <td>:</td>
                <td>{{ $row->prodi_nama ?? '' }} - {{ $row->program_nama ?? '' }}</td>
            </tr>
            <tr>
                <td>Gelombang</td>
                <td>:</td>
                <td>{{ $row->gelombang_nama ?? '' }}</td>
            </tr>
        </table>

        <p>Telah dinyatakan <strong>LULUS</strong> Ujian Saringan Masuk (USM) dengan rincian nilai sebagai berikut:</p>

        <table class="nilai-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori</th>
                    <th>Jumlah Soal</th>
                    <th>Benar</th>
                    <th>Salah</th>
                    <th>Nilai</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $kategori_nilai = [];
                    $total_soal = 0;
                    $total_benar = 0;
                    
                    foreach($detail_nilai ?? [] as $detail) {
                        $kategori = $detail['kategori_nama'] ?? 'Umum';
                        if (!isset($kategori_nilai[$kategori])) {
                            $kategori_nilai[$kategori] = ['soal' => 0, 'benar' => 0];
                        }
                        $kategori_nilai[$kategori]['soal']++;
                        $total_soal++;
                        
                        if ($detail['jawaban_dipilih'] == $detail['jawaban']) {
                            $kategori_nilai[$kategori]['benar']++;
                            $total_benar++;
                        }
                    }
                    
                    $grand_total_nilai = $total_soal > 0 ? ($total_benar / $total_soal) * 100 : 0;
                @endphp

                @foreach($kategori_nilai as $kategori => $nilai)
                    @php
                        $nilai_kategori = $nilai['soal'] > 0 ? ($nilai['benar'] / $nilai['soal']) * 100 : 0;
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td style="text-align: left;">{{ $kategori }}</td>
                        <td>{{ $nilai['soal'] }}</td>
                        <td>{{ $nilai['benar'] }}</td>
                        <td>{{ $nilai['soal'] - $nilai['benar'] }}</td>
                        <td>{{ number_format($nilai_kategori, 2) }}</td>
                    </tr>
                @endforeach

                <tr style="font-weight: bold; background-color: #f0f0f0;">
                    <td colspan="2">TOTAL</td>
                    <td>{{ $total_soal }}</td>
                    <td>{{ $total_benar }}</td>
                    <td>{{ $total_soal - $total_benar }}</td>
                    <td>{{ number_format($grand_total_nilai, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <p>Demikian surat keterangan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>
    </div>

    <div class="signature">
        <p>Jakarta, {{ date('d F Y') }}</p>
        <p>Direktur PMB</p>
        <div class="signature-space"></div>
        <p><strong>(________________________)</strong></p>
        <p>NIP. -</p>
    </div>

    <div class="clear"></div>
</body>
</html>
