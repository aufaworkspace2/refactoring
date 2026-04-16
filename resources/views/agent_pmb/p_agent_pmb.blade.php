<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Agent PMB</title>
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
    <h3>DATA AGENT PMB</h3>
    
    <table>
        <thead>
            <tr>
                <th class="no">No</th>
                <th>Nama</th>
                <th>Institusi</th>
                <th>No Telepon</th>
                <th>Email</th>
                <th>Kode Referal</th>
                <th>Link Daftar</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($query as $row)
                @php $row = (object) $row; @endphp
                <tr>
                    <td class="no">{{ $no++ }}</td>
                    <td>{{ $row->nama ?? '' }}</td>
                    <td class="text-center">{{ $row->institusi ?? '' }}</td>
                    <td class="text-center">{{ $row->no_telepon ?? '' }}</td>
                    <td class="text-center">{{ $row->email ?? '' }}</td>
                    <td class="text-center">{{ $row->kode_referal ?? '' }}</td>
                    <td class="text-center">{{ config('app.pmb_url', getenv('PMB_URL')) }}/registrasi?rf={{ $row->kode_referal ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
