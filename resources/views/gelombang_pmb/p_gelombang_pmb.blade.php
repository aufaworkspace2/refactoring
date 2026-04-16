<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Gelombang PMB</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        h3 { text-align: center; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #4CAF50; color: white; text-align: center; }
        .no { text-align: center; width: 5%; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h3>DATA GELOMBANG PMB</h3>
    
    <table>
        <thead>
            <tr>
                <th class="no">No.</th>
                <th>Kode</th>
                <th>Nama</th>
                <th>Tahun Akademik</th>
                <th>Tahun Masuk</th>
                <th>Gelombang Ke</th>
                <th>Status Pendaftaran</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($query as $row)
                @php $row = (object) $row; @endphp
                <tr>
                    <td class="no">{{ $no++ }}</td>
                    <td class="text-center">{{ $row->kode ?? '' }}</td>
                    <td>{{ $row->nama ?? '' }}</td>
                    <td class="text-center">{{ get_field($row->tahun_id ?? '', 'tahun') ?? '' }}</td>
                    <td class="text-center">{{ $row->tahunmasuk ?? '' }}</td>
                    <td class="text-center">{{ $row->GelombangKe ?? '' }}</td>
                    <td class="text-center">{{ ($row->PendaftaranTerbuka > 0) ? $row->PendaftaranTerbuka . ' Pendaftaran terbuka' : 'Tidak ada' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
