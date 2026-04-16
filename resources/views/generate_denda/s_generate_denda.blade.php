<input type="hidden" id="valTahunID" value="{{ $tahunID }}">
<p>{!! $total_row !!}</p>
<form id="f_generate_denda" action="{{ route('generate_denda.index') }}">
    <div class="table-responsive">
        <table class="table table-bordered mb-0 table-hover tablesorter">
            <thead class="bg-primary text-white">
                <tr>
                    @if($Update == 'YA')
                        <th width="2%">
                            <div class="checkbox checkbox-info">
                                <input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.forms.namedItem('f_generate_denda')); show_btnDelete();">
                                <label for="checkAll"></label>
                            </div>
                        </th>
                    @endif
                    <th rowspan="1" class="text-center" width="2%">No.</th>
                    <th rowspan="1" style="width: 2%;" class="text-center">NIM</th>
                    <th rowspan="1" style="width: 5%;" class="text-center">Nama</th>
                    <th rowspan="1" style="width: 5%;" class="text-center">Komponen Biaya</th>
                    <th rowspan="1" style="width: 3%;" class="text-center">Tahun<br>Semester</th>
                    <th rowspan="1" style="width: 3%;" class="text-center">Jumlah Tagihan</th>
                    <th rowspan="1" style="width: 3%;" class="text-center">Due Date</th>
                    <th rowspan="1" style="width: 3%;" class="text-center">Status</th>
                    <th rowspan="1" style="width: 3%;" class="text-center">Denda</th>
                    <th rowspan="1" style="width: 3%;" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $nomor = $offset;
                    $i = 0;
                @endphp
                @if(count($query) > 0)
                    @foreach ($query as $value)
                        @php
                            $tanggalDuedate = date('Y-m-d', strtotime($value->Duedate));
                            $absDiff = 0;
                            $jumlahDenda = 0;

                            if ($datenow > $tanggalDuedate && empty($value->HistoriDendaID)) {
                                $earlier = new DateTime($tanggalDuedate);
                                $later = new DateTime($datenow);
                                $absDiff = $later->diff($earlier)->format("%a");

                                $jb = $value->JenisBiayaID;
                                $prodiID = $value->ProdiID;
                                $programID = $value->ProgramID;
                                $tahunMasuk = $value->TahunMasuk;
                                $dendaJb = $setup_denda[$jb] ?? null;
                                $dendaJbProgram = null;
                                $dendaJbProgramProdi = null;
                                $dendaJbProgramProdiTahunmasuk = null;

                                if ($dendaJb) {
                                    $dendaJbProgram = $dendaJb[$programID] ?? $dendaJb[0] ?? null;
                                }
                                if ($dendaJbProgram) {
                                    $dendaJbProgramProdi = $dendaJbProgram[$prodiID] ?? $dendaJbProgram[0] ?? null;
                                }
                                if ($dendaJbProgramProdi) {
                                    $dendaJbProgramProdiTahunmasuk = $dendaJbProgramProdi[$tahunMasuk] ?? $dendaJbProgramProdi[0] ?? null;
                                }

                                if ($dendaJbProgramProdiTahunmasuk) {
                                    foreach ($dendaJbProgramProdiTahunmasuk as $hari => $rowDenda) {
                                        if ($absDiff > $hari) {
                                            if ($rowDenda->Tipe == 'persen') {
                                                $jumlahDenda = $value->Jumlah * $rowDenda->Jumlah / 100;
                                            } else if ($rowDenda->Tipe == 'nominal') {
                                                $jumlahDenda = $rowDenda->Jumlah;
                                            }
                                            break;
                                        }
                                    }
                                }
                            } else {
                                $jumlahDenda = $value->JumlahDenda ?? 0;
                            }
                        @endphp

                        <tr>
                            @if($Update == 'YA')
                                @if($datenow > $tanggalDuedate && empty($value->HistoriDendaID) && $jumlahDenda > 0)
                                    <td class="align-middle">
                                        <div class="checkbox checkbox-info">
                                            <input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnDelete()" value="{{ $value->ID }}_{{ $jumlahDenda }}">
                                            <label for="checkID{{ $i }}"></label>
                                        </div>
                                    </td>
                                    @php $i++; @endphp
                                @else
                                    <td>-</td>
                                @endif
                            @endif

                            <td class="text-center">{{ ++$nomor }}.</td>
                            <td class="text-center"><b>{{ $value->NIM }}</b></td>
                            <td>{{ $value->NamaMahasiswa }}</td>
                            <td>{{ $value->NamaBiaya }}</td>
                            <td><span class='badge badge-secondary'>{{ $tahun[$value->Periode]->Nama ?? '' }}</span></td>
                            <td class="text-center">
                                <a href="javascript:void(0);" class="badge badge-light text-dark font-16">{{ number_format($value->Jumlah, 2, ',', '.') }}</a>
                            </td>
                            <td class="text-center">
                                {{ \Carbon\Carbon::parse($value->Duedate)->format('d/m/Y') }}
                            </td>

                            <td>
                                @if($value->HistoriDendaID)
                                    <span class="badge badge-primary">Sudah Digenerate</span>
                                @elseif($datenow > $tanggalDuedate && empty($value->HistoriDendaID))
                                    <span class="badge badge-danger">Telat {{ $absDiff }} Hari</span>
                                @else
                                    <span class="badge badge-secondary">Belum Telat</span>
                                @endif
                            </td>

                            <td>
                                @if($datenow > $tanggalDuedate)
                                    {{ number_format($jumlahDenda, 2, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if($datenow > $tanggalDuedate && empty($value->HistoriDendaID) && $jumlahDenda > 0)
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info dropdown-toggle waves-effect waves-light btn-sm" data-toggle="dropdown" aria-expanded="false"> Action <i class="mdi mdi-chevron-down"></i></button>
                                        <div class="dropdown-menu">
                                            <a onclick="posting('{{ $value->ID }}_{{ $jumlahDenda }}','{{ $value->Periode }}', 1);" href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-publish"></i>&nbsp;Posting Denda</a>
                                        </div>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="11" class="text-center">Tidak ada data Sesuai Filter diatas</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</form>
<div class="row">
    <div class="col-md-12">
        {!! $link !!}
    </div>
</div>

<script type="text/javascript">
function show_btnDelete(){
    i=0; hasil = false;
    while(document.getElementsByName('checkID[]').length > i) {
        var checkname = document.getElementById('checkID'+i).checked;
        if(checkname == true) {
            hasil = true;
        }
        i++;
    }
    if(hasil == true) {
        $('#btn_posting_all').removeAttr('disabled');
        $('#btn_posting_all').removeAttr('title');
    } else {
        $('#btn_posting_all').attr('disabled','disabled');
        $('#btn_posting_all').attr('title', 'Pilih dahulu data yang akan di posting semua');
    }
}
show_btnDelete();

$("input:checkbox[name='checkID[]']").click(function(){
    if(this.checked == true){
        $(this).parents('tr').addClass('table-danger');
    } else {
        $(this).parents('tr').removeClass('table-danger');
    }
});
</script>
