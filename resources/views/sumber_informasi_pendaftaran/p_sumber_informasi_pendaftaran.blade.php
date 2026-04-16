<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Sumber Informasi Pendaftaran</title>
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
    <h3>DATA SUMBER INFORMASI PENDAFTARAN</h3>
    
    <table>
        <thead>
            <tr>
                <th class="no">No</th>
                <th>Nama</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($query as $row)
                @php $row = (object) $row; @endphp
                <tr>
                    <td class="no">{{ $no++ }}</td>
                    <td>{{ $row->nama_ref ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
