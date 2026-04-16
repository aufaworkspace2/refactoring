<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Master Format NIM PMB</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h3 { text-align: center; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #4CAF50; color: white; text-align: center; }
        .no { text-align: center; width: 5%; }
        .text-left { text-align: left; }
    </style>
</head>
<body>
    <h3>DATA MASTER FORMAT NIM PMB</h3>
    
    <table>
        <thead>
            <tr>
                <th class="no">No</th>
                <th>Kode</th>
                <th>Field</th>
                <th>Table</th>
                <th>Relation</th>
                <th>Digit</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($query as $row)
                @php $row = (object) $row; @endphp
                <tr>
                    <td class="no">{{ $no++ }}</td>
                    <td>{{ $row->kode ?? '' }}</td>
                    @if($row->sumber == 'dari_database')
                        <td class="text-left">{{ $row->field ?? '' }}</td>
                        <td>{{ $row->table ?? '' }}</td>
                        <td>{{ $row->relasi ?? '' }}</td>
                    @else
                        <td colspan="3">Isian Hardcode: <b>{{ $row->isi_hardcode ?? '' }}</b></td>
                    @endif
                    <td class="text-center">{{ $row->digit ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
