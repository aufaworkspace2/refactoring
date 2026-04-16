<div class="table-responsive">
    <table class="table table-bordered mb-0 table-hover tablesorter">
        <thead class="bg-primary text-white">
            <tr>
                <td width="5%;">No</td>
                <td width="5%;">Prodi Lama</td>
                <td width="5%;">Prodi Baru</td>
                <td width="5%;">Waktu Edit</td>
                <td width="5%;">Oleh</td>
            </tr>
        </thead>
        <tbody>
            @if(count($query ?? []) > 0)
                @php $no = 0; @endphp
                @foreach($query as $row)
                    @php
                    $prodiLama = $all_prodi[$row->ProdiLama] ?? null;
                    $prodiBaru = $all_prodi[$row->ProdiBaru] ?? null;
                    $user = \DB::table('user')->where('ID', $row->UserID ?? '')->value('Nama') ?? '-';
                    @endphp
                    <tr>
                        <td>{{ ++$no }}</td>
                        <td>{{ $prodiLama->NamaJenjang ?? '' }} {{ $prodiLama->Nama ?? '-' }}</td>
                        <td>{{ $prodiBaru->NamaJenjang ?? '' }} {{ $prodiBaru->Nama ?? '-' }}</td>
                        <td>{{ $row->createdAt ?? '-' }}</td>
                        <td>{{ $user }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5" class="text-center">Belum Ada Transaksi Pindah Prodi Sebelumnya</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
