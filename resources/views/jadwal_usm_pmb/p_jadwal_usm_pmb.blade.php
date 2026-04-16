<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Jadwal Ujian PMB</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        h3 { text-align: center; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #4CAF50; color: white; text-align: center; }
        .no { text-align: center; width: 5%; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h3>DATA JADWAL UJIAN PMB</h3>
    
    <table>
        <thead>
            <tr>
                <th class="no">No.</th>
                <th>Gelombang</th>
                <th>Kode Jadwal</th>
                <th>Tanggal Ujian</th>
                <th>Jam Ujian</th>
                <th>Ruangan</th>
                <th>Jenis Ujian</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($query as $row)
                @php $row = (object) $row; @endphp
                <tr>
                    <td class="no">{{ $no++ }}</td>
                    <td>{{ $row->namagelombang ?? '' }}</td>
                    <td class="text-center">{{ $row->kode ?? '' }}</td>
                    <td class="text-center">{{ $row->tgl_ujian ? date('d/m/Y', strtotime($row->tgl_ujian)) : '' }}</td>
                    <td class="text-center">{{ $row->jam_mulai ?? '' }} - {{ $row->jam_selesai ?? '' }}</td>
                    <td class="text-center">{{ strip_tags($row->ruangan ?? '') }}</td>
                    <td class="text-center">{{ strip_tags($row->jenis_ujin_text ?? '') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
