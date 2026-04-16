<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="bg-primary text-white">
            <tr>
                <th width="5%">No</th>
                <th width="20%">Jenis Biaya</th>
                <th width="20%">Jumlah Tagihan</th>
                <th width="20%">Jumlah Diskon</th>
                <th width="20%">Jumlah</th>
                <th width="15%">Master Diskon</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 0; @endphp
            @foreach($query as $row)
                @php $no++; @endphp
                <tr>
                    <td class="text-center">{{ $no }}</td>
                    <td>{{ $row->NamaJenisBiaya }}</td>
                    <td class="text-right">{{ rupiah($row->JumlahTagihan) }}</td>
                    <td class="text-right">{{ rupiah($row->JumlahDiskon) }}</td>
                    <td class="text-right">{{ rupiah($row->Jumlah) }}</td>
                    <td>
                        @php
                            $diskon_list = explode(",", $row->MasterDiskonID_list);
                        @endphp
                        @foreach($diskon_list as $d)
                            @if($d && isset($diskon[$d]))
                                <span class="badge badge-info">{{ $diskon[$d]->Nama }}</span>
                            @endif
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
