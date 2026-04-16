<input type="hidden" id="tahunID" name="tahunID" value="{{ $tahunID }}" />
<input type="hidden" id="programID" name="programID" value="{{ $programID }}" />
<input type="hidden" id="prodiID" name="prodiID" value="{{ $prodiID }}" />
<input type="hidden" id="statusMhswID" name="statusMhswID" value="{{ $statusMhsw }}" />
<input type="hidden" id="tahunMasuk" name="tahunMasuk" value="{{ $tahunMasuk }}" />
<input type="hidden" id="pilihan" name="pilihan" value="{{ $pilihan }}" />
<input type="hidden" id="keyword" name="keyword" value="{{ $keyword }}" />
<input type="hidden" id="statusBayar" name="statusBayar" value="{{ $statusBayar }}" />

@if ($pilihan != 'on')
    <button id="btnProses" type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light float-right" style="display: none;">Proses</button>
@else
    <button id="btnProses" type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light float-right">Proses</button>
@endif

<p>{!! $total_row !!}</p>

<div class="table-responsive mt-3">
    <table class="table table-hover table-bordered">
        <thead class="bg-primary text-white">
            <tr>
                @if ($pilihan != 'on')
                    <th width="2%">
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.getElementsByClassName('checkID')); show_btnDelete();" />
                            <label for="checkAll"></label>
                        </div>
                    </th>
                @endif
                <th class="text-center" width="2%">No.</th>
                <th class="text-center" width="10%">{{ __('app.NPM') }}</th>
                <th class="text-center" width="30%">{{ __('app.Nama') }}</th>
                <th class="text-center" width="10%">Program</th>
                <th class="text-center" width="15%">Program Studi</th>
                <th class="text-center" width="10%">Jumlah Tagihan</th>
                <th class="text-center" width="10%">Jumlah Bayar</th>
                <th class="text-center" width="10%">Status</th>
                <th class="text-center">KRS</th>
                <th class="text-center">UTS</th>
                <th class="text-center">UAS</th>
                @if ($buka_opsi_nilai == 1)
                    <th class="text-center">NILAI</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @php $no = $offset; $i = 0; @endphp
            @foreach ($query as $row)
                <tr>
                @if ($pilihan != 'on')
                    <td class="align-middle">
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnDelete()" class="checkID" value="{{ $row->ID }}">
                            <label for="checkID{{ $i }}"></label>
                        </div>
                    </td>
                @endif
                    <td class="text-center">{{ ++$no }}.</td>
                    <td class="text-center">{{ $row->NPM }}</td>
                    <td>{{ $row->Nama }}</td>
                    <td class="text-center">{{ $row->Program }}</td>
                    <td class="text-center">{{ $row->Prodi }}</td>
                    <td class="text-center">{{ rupiah($row->TotalTagihan) }}</td>
                    <td class="text-center">{{ rupiah($row->TotalCicilan) }}</td>
                    <td class="text-center">
                        @if ($row->TotalTagihan)
                            @if ($row->StatusBayar == 1)
                                <span class="badge badge-success">Sudah Lunas</span>
                            @else
                                <span class="badge badge-danger">Belum Lunas</span>
                            @endif
                        @else
                            <span class="badge badge-dark">Belum Ada Tagihan</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($row->KRS == 1)
                            <span class="badge badge-success"><i class="fa fa-check"></i></span>
                        @else
                            <span class="badge badge-danger"><i class="fa fa-times"></i></span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($row->UTS == 1)
                            <span class="badge badge-success"><i class="fa fa-check"></i></span>
                        @else
                            <span class="badge badge-danger"><i class="fa fa-times"></i></span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($row->UAS == 1)
                            <span class="badge badge-success"><i class="fa fa-check"></i></span>
                        @else
                            <span class="badge badge-danger"><i class="fa fa-times"></i></span>
                        @endif
                    </td>
                    @if ($buka_opsi_nilai == 1)
                        <td class="text-center">
                            @if ($row->KHS == 1 && $row->TRANSKRIP == 1)
                                <span class="badge badge-success"><i class="fa fa-check"></i></span>
                            @else
                                <span class="badge badge-danger"><i class="fa fa-times"></i></span>
                            @endif
                        </td>
                    @endif
                </tr>
                @php $i++; @endphp
            @endforeach
        </tbody>
    </table>
</div>
<div class="row">
    <div class="col-md-12">
        {!! $link !!}
    </div>
</div>

<script>
window.show_btnDelete = function() {
    i = 0;
    hasil = false;
    while (document.getElementsByName('checkID[]').length > i) {
        var checkname = document.getElementById('checkID' + i);
        if (checkname && checkname.checked == true) {
            hasil = true;
        }
        i++;
    }

    if (hasil == true) {
        $('#btnProses').css('display', '');
    } else {
        $('#btnProses').css('display', 'none');
    }
}

$("input:checkbox[name='checkID[]']").click(function() {
    if (this.checked == true) {
        $(this).parents('tr').addClass('table-danger');
    } else {
        $(this).parents('tr').removeClass('table-danger');
    }
});
</script>
