<form id="f_save_tes_kesehatan" action="{{ url('tes_kesehatan_pmb/set_status_lulus') }}">
    <div class="form-row">
        <div class="form-group col-md-4">
            <select name="action" id="action" class="form-control" onchange="show_btnSubmit()">
                <option value="">Pilih Aksi</option>
                <option value="lulus">Set Lulus Kesehatan</option>
                <option value="tidaklulus">Set Tidak Lulus Kesehatan</option>
                <option value="batalkan">Set Batalkan Lulus Kesehatan</option>
            </select>
        </div>
        <div class="col-md-8">
            <div class="button-list">
                <button disabled id="btnSubmit" name="act" type="submit" class="btn btn-bordered-success small waves-effect waves-light mt-0"> Submit </button>
            </div>
        </div>
    </div>

    <p>{!! $total_row ?? '' !!}</p>
    <div class="table-responsive">
        <table class="table table-bordered mb-0 table-hover tablesorter">
            <thead class="bg-primary text-white">
                <tr>
                    @if($Delete == 'YA')
                    <th class="sorterfalse" width="1%">
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.forms.namedItem('f_save_tes_kesehatan')); show_btnSubmit();">
                            <label for="checkAll"></label>
                        </div>
                    </th>
                    @endif
                    <th class="text-center" width="1%">No.</th>
                    <th width="7%">No Ujian</th>
                    <th width="10%">Nama</th>
                    <th width="14%">Pilihan</th>
                    <th width="10%">Program</th>
                    <th width="5%" class="text-center">Lulus Kesehatan</th>
                </tr>
            </thead>
            <tbody>
                @php $no = $offset ?? 0; $i = 0; @endphp
                @foreach($query ?? [] as $row)
                    @php $row = (object) $row; @endphp
                    <tr class="tes_kesehatan_{{ $row->ID ?? '' }}">
                        @if($Update == 'YA')
                            @if($row->statusdraftregistrasi_pmb != 1)
                                <td class="align-middle">
                                    <div class="checkbox checkbox-info">
                                        <input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnSubmit()" value="{{ $row->ID ?? '' }}">
                                        <label for="checkID{{ $i }}"></label>
                                    </div>
                                </td>
                                @php $i++; @endphp
                            @else
                                <td>-</td>
                            @endif
                        @endif
                        <td class="text-center">{{ ++$no }}.</td>
                        <td class="text-center">{{ $row->noujian_pmb ?? '' }}</td>
                        <td>
                            {{ $row->Nama ?? '' }}
                        </td>
                        <td>
                            @php
                            $pilihanprodilulus = $all_prodi[$row->prodilulus_pmb ?? ''] ?? null;
                            $pilihan1 = $all_prodi[$row->pilihan1 ?? ''] ?? null;
                            $pilihan2 = $all_prodi[$row->pilihan2 ?? ''] ?? null;
                            $pilihan3 = $all_prodi[$row->pilihan3 ?? ''] ?? null;
                            @endphp
                            @if(!empty($row->prodilulus_pmb) && $pilihanprodilulus)
                                Lulus : {{ $pilihanprodilulus->NamaJenjang ?? '' }} {{ $pilihanprodilulus->Nama ?? '' }}<br>
                            @endif
                            1. {{ $pilihan1->NamaJenjang ?? '' }} {{ $pilihan1->Nama ?? '' }}
                            @if(!empty($row->pilihan2) && $pilihan2)
                                <br>2. {{ $pilihan2->NamaJenjang ?? '' }} {{ $pilihan2->Nama ?? '' }}
                            @endif
                            @if(!empty($row->pilihan3) && $pilihan3)
                                <br>3. {{ $pilihan3->NamaJenjang ?? '' }} {{ $pilihan3->Nama ?? '' }}
                            @endif
                        </td>

                        <td>
                            {{ $row->programNama ?? '-' }}
                            <input type="hidden" name="idpend[]" value="{{ $row->ID ?? '' }}" />
                        </td>
                        <td class="text-center">
                            {!! $row->statusluluskesehatan_str ?? '' !!}<br>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div id="hapus" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="hapus" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="hapus">{{ __('app.confirm_header') }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body">
                        <p>{{ __('app.confirm_message') }}</p>
                        <p class="data_name"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger waves-effect">{{ __('app.delete') }}</button>
                        <button type="button" class="btn btn-primary waves-effect waves-light" data-dismiss="modal">{{ __('app.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">{!! $link ?? '' !!}</div>
    </div>
</form>

<script>
// Call tablesorter if function exists
if(typeof tablesorter === 'function') {
    tablesorter();
}

$(document).ready(function() {
    show_btnSubmit();

    $("input:checkbox[name='checkID[]']").click(function(){
        if(this.checked == true){ $(this).parents('tr').addClass('table-danger'); }
        else { $(this).parents('tr').removeClass('table-danger'); }
        show_btnSubmit();
    });
});

function show_btnSubmit() {
    i = 0;
    hasil = false;
    while (document.getElementsByName('checkID[]').length > i) {
        var checkname = document.getElementById('checkID' + i);
        
        if (checkname && checkname.checked) {
            hasil = true;
        }
        i++;
    }
    var action = $('#action').val();
    if (hasil == true && action != '') {
        $('#btnSubmit').removeAttr('disabled');
        $('#btnSubmit').removeAttr('title');
    } else {
        $('#btnSubmit').attr('disabled', 'disabled');
        $('#btnSubmit').attr('title', 'Pilih dahulu data yang akan di simpan');
    }
}

function checkall(chkAll,checkid) {
    if (checkid != null) {
        if (checkid.length == null) checkid.checked = chkAll.checked;
        else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;
        $("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
        $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
    }
}

// disable mousewheel on a input number field when in focus
$('input[type=number]').on('wheel', function(e) {
    return false;
});
</script>
