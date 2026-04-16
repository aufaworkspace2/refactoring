<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Keterangan Pendamping Ijazah</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 5px 0; font-size: 14pt; }
        .header p { margin: 2px 0; font-size: 10pt; }
        .content { margin-top: 20px; }
        .section { margin-bottom: 15px; }
        .section-title { font-weight: bold; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 5px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .signature { margin-top: 30px; }
        .signature-table { width: 100%; }
        .signature-table td { border: none; vertical-align: top; }
        .signature-left { width: 50%; text-align: left; }
        .signature-right { width: 50%; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>SURAT KETERANGAN PENDAMPING IJAZAH</h2>
        <p>DIPLOMA SUPPLEMENT</p>
    </div>

    <div class="content">
        <!-- Section 1: Information about the Awarding Institution -->
        <div class="section">
            <div class="section-title">1. INFORMASI TENTANG PERGURUAN TINGGI YANG MENERBITKAN IJAZAH</div>
            <div class="section-title">1. INFORMATION ABOUT THE AWARDING INSTITUTION</div>
            <p>{{ $identitas->Nama ?? '' }}</p>
        </div>

        <!-- Section 2: Information about the Qualification -->
        <div class="section">
            <div class="section-title">2. INFORMASI TENTANG JENJANG KUALIFIKASI</div>
            <div class="section-title">2. INFORMATION ABOUT THE QUALIFICATION</div>
            <table>
                <tr>
                    <td width="30%">2.1 Nama</td>
                    <td width="70%">{{ $mahasiswa->Nama ?? '' }}</td>
                </tr>
                <tr>
                    <td>2.2 Tanggal Lahir</td>
                    <td>{{ $mahasiswa->TanggalLahir ? \Carbon\Carbon::parse($mahasiswa->TanggalLahir)->format('d M Y') : '' }}</td>
                </tr>
                <tr>
                    <td>2.3 Nomor Induk Mahasiswa</td>
                    <td>{{ $mahasiswa->NPM ?? '' }}</td>
                </tr>
                <tr>
                    <td>2.4 Nomor Ijazah</td>
                    <td>{{ $dataWisuda->NoIjazah ?? $row->NoIjazah ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <!-- Section 3: Level of the Qualification -->
        <div class="section">
            <div class="section-title">3. JENJANG KUALIFIKASI</div>
            <div class="section-title">3. LEVEL OF THE QUALIFICATION</div>
            <table>
                <tr>
                    <td width="30%">3.1 Jenjang</td>
                    <td width="70%">{{ $jenjang->Nama ?? '' }}</td>
                </tr>
                <tr>
                    <td>3.2 Lama Studi</td>
                    <td>{{ $row->LamaStudi ?? '-' }}</td>
                </tr>
                <tr>
                    <td>3.3 Jumlah SKS</td>
                    <td>{{ $row->SKS ?? '0' }}</td>
                </tr>
                <tr>
                    <td>3.4 Indeks Prestasi Kumulatif</td>
                    <td>{{ $row->IPK ?? '0' }}</td>
                </tr>
            </table>
        </div>

        <!-- Section 4: Contents and Results Gained -->
        <div class="section">
            <div class="section-title">4. ISI DAN HASIL YANG DICAPAI</div>
            <div class="section-title">4. CONTENTS AND RESULTS GAINED</div>

            @if(!empty($M_Pencapaian))
                @foreach($M_Pencapaian as $kategoriId => $pencapaianList)
                    @php
                        $kategori = DB::table('tbl_kategori_pencapaian')->where('ID', $kategoriId)->first();
                    @endphp
                    <div style="margin-top: 10px;">
                        <strong>{{ $kategori->Nama ?? '' }}</strong><br>
                        <em>{{ $kategori->NamaInggris ?? '' }}</em>
                    </div>
                    <table style="margin-top: 5px;">
                        <thead>
                            <tr>
                                <th width="10%">No</th>
                                <th width="15%">Kode</th>
                                <th width="75%">Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pencapaianList as $index => $pencapaian)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $pencapaian->Kode ?? '' }}</td>
                                    <td>
                                        {{ $pencapaian->IsiIndonesia ?? $pencapaian->Indonesia ?? '' }}<br>
                                        <em>{{ $pencapaian->IsiInggris ?? $pencapaian->Inggris ?? '' }}</em>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
            @endif
        </div>

        <!-- Section 5: Certification -->
        <div class="section">
            <div class="section-title">5. SERTIFIKASI</div>
            <div class="section-title">5. CERTIFICATION</div>
            <p>Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p>
        </div>

        <!-- Signatures -->
        <div class="signature">
            <table class="signature-table">
                <tr>
                    <td class="signature-left">
                        @if(!empty($data_kelulusan->TanggalLulus))
                            <p>Ditetapkan di: {{ $identitas->Kota ?? '' }}</p>
                            <p>Pada Tanggal: {{ \Carbon\Carbon::parse($data_kelulusan->TanggalLulus)->format('d M Y') }}</p>
                        @endif
                    </td>
                    <td class="signature-right">
                        <p>{{ $prodi->Nama ?? '' }}</p>
                        <p>Ketua Program Studi,</p>
                        <br><br><br>
                        <p><strong>{{ $NamaKetuaProdi ?? '' }}</strong></p>
                        <p>NIP. {{ $NIP ?? '' }}</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
