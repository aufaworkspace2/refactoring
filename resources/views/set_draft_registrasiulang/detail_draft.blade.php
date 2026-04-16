<div class="table-responsive">
<table class="table table-bordered mb-0 table-hover tablesorter">
    <thead class="bg-primary text-white">
        <tr>
            <td width="3%;">No</td>
            <td width="15%;">Komponen Biaya</td>
            <td width="15%;">Jumlah Tagihan</td>
            <td width="15%;">Komponen Diskon</td>
            <td width="15%;">Jumlah Diskon</td>
            <td width="15%;">Jumlah</td>
        </tr>
    </thead>
    <tbody>
        @if(count($query ?? []) > 0)
        @php
        $JumlahDiskonTotal = 0;
        $JumlahTagihanTotal = 0;
        $JumlahTotal = 0;
        $no=0;
        @endphp
        @foreach($query as $DraftTagihanMahasiswaSemesterID => $query2)

        <tr>
            <td colspan="6" class="text-center font-weight-bold bg-light">Semester {{ get_field($DraftTagihanMahasiswaSemesterID,'draft_tagihan_mahasiswa_semester','Semester') ?? '-' }}</td>
        </tr>
        @foreach($query2 as $row)
            @php
            $row = (object) $row;
            $nama_jenisbiaya = $jenisbiaya[$row->JenisBiayaID]->Nama ?? '-';
            @endphp
            <tr>
                <td>{{ ++$no }}</td>
                <td>{{ $nama_jenisbiaya }}</td>
                <td>{{ number_format($row->TotalTagihan ?? 0, 0, ',', '.') }}</td>
                <td><?php
                if($row->MasterDiskonID){
                    $exp_diskon = explode(",",$row->MasterDiskonID);
                    echo "<ol>";
                    foreach($exp_diskon as $dis){
                        if($dis){
                            $row_masterdiskon = $master_diskon[$dis] ?? null;
                            $hrg = '';
                            if($row_masterdiskon && $row_masterdiskon->Tipe == 'persen'){
                                $hrg = $row_masterdiskon->Jumlah.'%';
                            }else if($row_masterdiskon){
                                $hrg = number_format($row_masterdiskon->Jumlah ?? 0, 0, ',', '.');
                            }
                            $view_diskon = ($row_masterdiskon->Nama ?? '').' '.$hrg;
                            echo "<li>".$view_diskon." </li>";
                        }
                    }
                    echo "</ol>";
                }else{
                    echo "-";
                }
                ?></td>
                <td>{{ number_format($row->JumlahDiskon ?? 0, 0, ',', '.') }}</td>
                <td>{{ number_format($row->Jumlah ?? 0, 0, ',', '.') }}</td>
            </tr>
            @php
            $JumlahTagihanTotal += $row->TotalTagihan ?? 0;
            $JumlahDiskonTotal += $row->JumlahDiskon ?? 0;
            $JumlahTotal += $row->Jumlah ?? 0;
            @endphp
            @endforeach
            <tr>
                <td colspan="2">Total</td>
                <td>
                    {{ number_format($JumlahTagihanTotal, 0, ',', '.') }}
                </td>
                <td>

                </td>
                <td>
                    {{ number_format($JumlahDiskonTotal, 0, ',', '.') }}
                </td>
                <td>
                    {{ number_format($JumlahTotal, 0, ',', '.') }}
                </td>
            </tr>
        @endforeach
        @else
        <tr>
            <td colspan="6" class="text-center">Belum Ada Draft Tagihan</td>
        </tr>
        @endif
    </tbody>
</table>
</div>
