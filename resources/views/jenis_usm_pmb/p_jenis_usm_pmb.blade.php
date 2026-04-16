<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Jenis USM PMB</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h3 { text-align: center; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #4CAF50; color: white; text-align: center; }
        .no { text-align: center; width: 5%; }
    </style>
</head>
<body>
    <h3>DATA JENIS USM PMB</h3>
    
    <table>
        <thead>
            <tr>
                <th class="no">No.</th>
                <th>Kode</th>
                <th>Nama</th>
                <th>Jenis</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($query as $row)
                @php $row = (object) $row; @endphp
                <tr>
                    <td class="no">{{ $no++ }}</td>
                    <td>{{ $row->kode ?? '' }}</td>
                    <td>{{ $row->nama ?? '' }}</td>
                    <td>{{ $row->jenis ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
