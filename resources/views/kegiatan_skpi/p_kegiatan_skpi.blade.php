<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Data Kegiatan SKPI' }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .header { text-align: center; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $title ?? 'Data Kegiatan SKPI' }}</h2>
    </div>
    <table>
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="95%">Nama</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($query as $row)
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $row->Nama }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
