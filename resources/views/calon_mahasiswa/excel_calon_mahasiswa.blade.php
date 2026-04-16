<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th style="background-color: #4CAF50; color: white; text-align: center;">No</th>
            <th style="background-color: #4CAF50; color: white; text-align: center;">No. Ujian</th>
            <th style="background-color: #4CAF50; color: white; text-align: center;">Nama</th>
            <th style="background-color: #4CAF50; color: white; text-align: center;">Program</th>
            <th style="background-color: #4CAF50; color: white; text-align: center;">Prodi</th>
            <th style="background-color: #4CAF50; color: white; text-align: center;">Total Tagihan</th>
            <th style="background-color: #4CAF50; color: white; text-align: center;">Total Diskon</th>
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
                <td>{{ $row->programNama ?? '-' }}</td>
                <td>{{ $row->prodiNama ?? '-' }}</td>
                <td style="text-align: right;">{{ number_format($row->JumlahTagihan ?? 0, 0, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($row->JumlahDiskon ?? 0, 0, ',', '.') }}</td>
                <td style="text-align: center;">
                    @if($row->StatusAktif == 1)
                        Aktif
                    @else
                        Tidak Aktif
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
