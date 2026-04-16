<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Mahasiswa</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 9px; }
        h3 { text-align: center; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
        th { background-color: #4CAF50; color: white; text-align: center; }
        .no { text-align: center; width: 4%; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h3>DATA MAHASISWA</h3>
    
    <table>
        <thead>
            <tr>
                <th class="no">No.</th>
                <th>Nama</th>
                <th>NPM</th>
                <th>Tahun Masuk</th>
                <th>Program</th>
                <th>Prodi</th>
                <th>Jenjang</th>
                <th>Kurikulum</th>
                <th>Ketua Kelas</th>
                <th>Konsentrasi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($query as $row)
                @php $row = (object) $row; @endphp
                <tr>
                    <td class="no">{{ $no++ }}</td>
                    <td>{{ $row->Nama ?? '' }}</td>
                    <td class="text-center">{{ $row->NPM ?? '' }}</td>
                    <td class="text-center">{{ $row->TahunMasuk ?? '' }}</td>
                    <td class="text-center">{{ get_field($row->ProgramID ?? '', 'program') ?? '' }}</td>
                    <td class="text-center">{{ get_field($row->ProdiID ?? '', 'programstudi') ?? '' }}</td>
                    <td class="text-center">{{ get_field($row->JenjangID ?? '', 'jenjang') ?? '' }}</td>
                    <td class="text-center">{{ $row->Kurikulum ?? '' }}</td>
                    <td class="text-center">{{ $row->KetuaKelas ?? '' }}</td>
                    <td class="text-center">{{ $row->Konsentrasi ?? '' }}</td>
                    <td class="text-center">{{ get_field($row->StatusMhswID ?? '', 'status_mhsw') ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
