<!DOCTYPE html>
<html>
<head>
    <title>Daftar Status Mahasiswa</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #000; padding: 5px; }
        .text-center { text-align: center; }
        .bg-light { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <h4 class="text-center">DAFTAR STATUS MAHASISWA</h4>
    <table class="table">
        <thead>
            <tr class="bg-light">
                <th class="text-center" style="width:30px">No.</th>
                <th>Mahasiswa</th>
                <th>Status</th>
                <th>Nomor Surat</th>
                @if($StatusMhswID == 2)
                    <th>Mulai Semester</th>
                    <th>Akhir Semester</th>
                @endif
                <th>Alasan</th>
                <th>Tahun Semester</th>
                <th>Tanggal</th>		
            </tr>
        </thead>
        <tbody>
            @php $no = $offset; @endphp
            @forelse($query as $row)
                @php $row = (object) $row; @endphp
                <tr>
                    <td class="text-center">{{ ++$no }}.</td>
                    <td>{{ get_field($row->MhswID, 'mahasiswa') }}</td>
                    <td>{{ $row->Status ?? '' }}</td> 
                    <td>{{ $row->Nomor_Surat ?? '' }}</td>
                    @if($StatusMhswID == 2)
                        <td>{{ $row->Mulai_Semester ?? '' }}</td>
                        <td>{{ $row->Akhir_Semester ?? '' }}</td>
                    @endif
                    <td>{{ $row->Alasan ?? '' }}</td>
                    <td>{{ get_field($row->TahunID, 'tahun') }}</td>
                    <td>{{ !empty($row->Tgl) ? tgl($row->Tgl, '02') : '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $StatusMhswID == 2 ? 8 : 6 }}" class="text-center">Belum ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
