<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th style="background-color: #4CAF50; color: white; text-align: center;">No</th>
            <th style="background-color: #4CAF50; color: white; text-align: center;">No. Ujian</th>
            <th style="background-color: #4CAF50; color: white; text-align: center;">Nama</th>
            <th style="background-color: #4CAF50; color: white; text-align: center;">No. Identitas</th>
            <th style="background-color: #4CAF50; color: white; text-align: center;">Tempat Lahir</th>
            <th style="background-color: #4CAF50; color: white; text-align: center;">Tanggal Lahir</th>
            <th style="background-color: #4CAF50; color: white; text-align: center;">Jenis Kelamin</th>
            <th style="background-color: #4CAF50; color: white; text-align: center;">Gelombang</th>
            <th style="background-color: #4CAF50; color: white; text-align: center;">Program</th>
            <th style="background-color: #4CAF50; color: white; text-align: center;">Prodi</th>
            <th style="background-color: #4CAF50; color: white; text-align: center;">Jumlah Ujian</th>
            <th style="background-color: #4CAF50; color: white; text-align: center;">Selesai</th>
            <th style="background-color: #4CAF50; color: white; text-align: center;">Status</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @foreach($query as $row)
            @php $row = (object) $row; @endphp
            <tr>
                <td style="text-align: center;">{{ $no++ }}</td>
                <td style="text-align: center;">{{ $row->noujian_pmb ?? '' }}</td>
                <td>{{ $row->Nama ?? '' }}</td>
                <td style="text-align: center;">{{ $row->NoIdentitas ?? '' }}</td>
                <td>{{ $row->TempatLahir ?? '' }}</td>
                <td style="text-align: center;">{{ $row->TanggalLahir ? date('d/m/Y', strtotime($row->TanggalLahir)) : '' }}</td>
                <td style="text-align: center;">
                    @if($row->Kelamin == 'L')
                        Laki-laki
                    @elseif($row->Kelamin == 'P')
                        Perempuan
                    @else
                        -
                    @endif
                </td>
                <td>{{ $row->gelombangNama ?? '-' }}</td>
                <td>{{ $row->programNama ?? '-' }}</td>
                <td>{{ $row->prodiNama ?? '-' }}</td>
                <td style="text-align: center;">{{ $row->jumlahUjian ?? 0 }}</td>
                <td style="text-align: center;">{{ $row->jumlahSelesai ?? 0 }}</td>
                <td style="text-align: center;">
                    @if($row->jumlahSelesai >= $row->jumlahUjian && $row->jumlahUjian > 0)
                        Selesai
                    @else
                        Belum
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
