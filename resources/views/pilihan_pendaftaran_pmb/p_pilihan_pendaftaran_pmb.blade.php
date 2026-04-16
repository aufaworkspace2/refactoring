<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Pilihan Pendaftaran PMB</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        h3 { text-align: center; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #4CAF50; color: white; text-align: center; }
        .no { text-align: center; width: 5%; }
    </style>
</head>
<body>
    <h3>DATA PILIHAN PENDAFTARAN PMB</h3>
    
    <table>
        <thead>
            <tr>
                <th class="no">No.</th>
                <th>Nama</th>
                <th>Tahun</th>
                <th>Program</th>
                <th>Jalur</th>
                <th>Jenis Pendaftaran</th>
                <th>Beasiswa/Diskon</th>
                <th>Aktif</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($query as $row)
                @php $row = (object) $row; @endphp
                <tr>
                    <td class="no">{{ $no++ }}</td>
                    <td>{{ $row->nama ?? '' }}</td>
                    <td>{{ get_field($row->tahun_id ?? '', 'tahun') ?? '' }}</td>
                    <td>{{ $row->program_id ?? '' }}</td>
                    <td>{{ $row->jalur ?? '' }}</td>
                    <td>{{ $row->jenis_pendaftaran ?? '' }}</td>
                    <td>{{ $row->master_diskon_id_list ?? '' }}</td>
                    <td class="text-center">{{ ($row->aktif == 1) ? 'Aktif' : 'Tidak Aktif' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
