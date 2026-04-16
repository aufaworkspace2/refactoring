<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Daftar Data Nilai Kegiatan SKPI' }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h5 { text-align: center; margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 8px; text-align: center; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .header { text-align: center; margin-bottom: 30px; }
        td.left { text-align: left; }
    </style>
</head>
<body>
    <div class="header">
        <h5>{{ $title ?? 'Daftar Data Nilai Kegiatan SKPI' }}</h5>
    </div>
    <table>
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="50%">Kegiatan</th>
                <th width="20%">Tingkat/Sebagai Kegiatan</th>
                <th width="10%">Jenis</th>
                <th width="10%">Point</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 0; @endphp
            @foreach($query as $row)
            <tr>
                <td>{{ ++$no }}.</td>
                <td class="left">{{ $row['namaKegiatan'] ?? '' }}</td>
                <td>{{ $row['namaKategori'] ?? '' }}</td>
                <td>{{ $row['namaJenis'] ?? '' }}</td>
                <td>{{ $row['Point'] ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
