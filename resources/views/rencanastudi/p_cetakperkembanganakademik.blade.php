<!DOCTYPE html>
<html>
<head>
    <title>Perkembangan Akademik</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th, td { border: 1px solid #000; padding: 2px; text-align: left; }
        .no-border td, .no-border th { border: none; }
        .text-center { text-align: center; }
        .header { text-align: center; font-weight: bold; font-size: 12px; margin-bottom: 10px; }
        .bg-grey { background-color: #eee; }
        .semester-header { background-color: #ddd; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        LAPORAN PERKEMBANGAN AKADEMIK MAHASISWA
    </div>

    <table class="no-border">
        <tr>
            <td width="100px">Nama Mahasiswa</td>
            <td width="10px">:</td>
            <td width="250px">{{ $Nama ?? '' }}</td>
            <td width="100px">Tempat Lahir</td>
            <td width="10px">:</td>
            <td>{{ $TempatLahir ?? '' }}</td>
        </tr>
        <tr>
            <td>NPM</td>
            <td>:</td>
            <td>{{ $NPM ?? '' }}</td>
            <td>Tanggal Lahir</td>
            <td>:</td>
            <td>{{ tgl($TanggalLahir ?? '', '02') }}</td>
        </tr>
        <tr>
            <td>Program Studi</td>
            <td>:</td>
            <td>{{ $ProdiID ?? '' }}</td>
            <td>Jenjang</td>
            <td>:</td>
            <td>{{ $JenjangID ?? '' }}</td>
        </tr>
    </table>

    @foreach($query_all as $tahunId => $items)
    <table>
        <thead>
            <tr class="semester-header">
                <td colspan="6">Tahun Akademik: {{ $tahunId }}</td>
            </tr>
            <tr class="bg-grey">
                <th width="30px" class="text-center">No</th>
                <th width="80px" class="text-center">Kode MK</th>
                <th>Mata Kuliah</th>
                <th width="40px" class="text-center">SKS</th>
                <th width="40px" class="text-center">Nilai</th>
                <th width="40px" class="text-center">Bobot</th>
            </tr>
        </thead>
        <tbody>
            @php $sub_sks = 0; @endphp
            @foreach($items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $item->MKKode ?? '' }}</td>
                <td>{{ $item->NamaMataKuliah ?? '' }}</td>
                <td class="text-center">{{ $item->TotalSKS ?? 0 }}</td>
                <td class="text-center">{{ $item->NilaiHuruf ?? '-' }}</td>
                <td class="text-center">{{ $item->Bobot ?? 0 }}</td>
            </tr>
            @php $sub_sks += ($item->TotalSKS ?? 0); @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" style="text-align: right;">Total SKS Semester:</th>
                <th class="text-center">{{ $sub_sks }}</th>
                <th colspan="2"></th>
            </tr>
        </tfoot>
    </table>
    @endforeach

    <table class="no-border" style="margin-top: 20px;">
        <tr>
            <td width="60%"></td>
            <td class="text-center">
                {{ substr($kota->Kota ?? '', 5) }}, {{ date('d-m-Y') }}<br>
                Ketua Program Studi<br><br><br><br><br>
                <strong>{{ $KA ?? '................................' }}</strong><br>
                NIP : {{ $NIPKA ?? '' }}
            </td>
        </tr>
    </table>
</body>
</html>
