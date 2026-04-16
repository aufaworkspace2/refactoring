<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="bg-primary text-white">
            <tr>
                <th width="2%">
                    <div class="checkbox checkbox-info">
                        <input type="checkbox" name="checkAll" id="checkAll"
                               onClick="checkall(this, document.getElementsByClassName('checkID')); show_btnSave();">
                        <label for="checkAll"></label>
                    </div>
                </th>
                <th width="2%">No</th>
                <th width="10%">NPM</th>
                <th width="20%">Nama</th>
                <th width="10%">Program</th>
                <th width="10%">Prodi</th>
                <th width="10%">Angkatan</th>
                <th width="12%">Jumlah Tagihan</th>
                <th width="12%">Jumlah Diskon</th>
                <th width="12%">Sisa Tagihan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 0; @endphp
            @foreach($get_mhs as $row)
                @php $no++; @endphp
                <tr>
                    <td>
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkID[]" id="checkID{{ $no }}"
                                   class="checkID" value="{{ $row->ID }}"
                                   onclick="show_btnSave();">
                            <label for="checkID{{ $no }}"></label>
                        </div>
                    </td>
                    <td class="text-center">{{ $no }}</td>
                    <td>{{ $row->NPM }}</td>
                    <td>{{ $row->Nama }}</td>
                    <td>{{ get_field($row->ProgramID, 'program') }}</td>
                    <td>{{ get_field($row->ProdiID, 'programstudi') }}</td>
                    <td>{{ $row->TahunMasuk }}</td>
                    <td class="text-right">{{ rupiah($row->JumlahTagihan ?? 0) }}</td>
                    <td class="text-right">{{ rupiah($row->JumlahDiskon ?? 0) }}</td>
                    <td class="text-right">{{ rupiah(($row->JumlahTagihan ?? 0) - ($row->JumlahDiskon ?? 0)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if(count($query_jenisbiaya) > 0)
<div class="card mt-3">
    <div class="card-body">
        <h5>Pilih Komponen Biaya dan Diskon</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="bg-info text-white">
                    <tr>
                        <th width="30%">Komponen Biaya</th>
                        <th width="70%">Pilih Diskon / Beasiswa</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($query_jenisbiaya as $jb)
                        <tr>
                            <td>{{ $jb->Nama }}</td>
                            <td>
                                <select name="DiscountID[{{ $jb->ID }}][]" class="form-control discount-select" multiple>
                                    @foreach($diskon as $disc)
                                        @php
                                            $nom = ($disc->Tipe == 'nominal')
                                                ? rupiah($disc->Jumlah)
                                                : $disc->Jumlah . ' %';
                                        @endphp
                                        <option value="{{ $disc->ID }}">
                                            {{ $disc->Nama }} - {{ $nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @php
            $jb_ids = [];
            foreach($query_jenisbiaya as $jb) {
                $jb_ids[] = $jb->ID;
            }
        @endphp
        <input type="hidden" name="JenisBiayaID" value="{{ implode(',', $jb_ids) }}">
    </div>
</div>
@endif

<script>
function checkall(chkAll, checkid) {
    if (checkid != null) {
        if (checkid.length == null) {
            checkid.checked = chkAll.checked;
        } else {
            for (i = 0; i < checkid.length; i++) {
                checkid[i].checked = chkAll.checked;
            }
        }

        $("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
        $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');

        show_btnSave();
    }
}

window.show_btnSave = function() {
    i = 0; hasil = false;
    while(document.getElementsByName('checkID[]').length > i) {
        var checkname = document.getElementById('checkID' + i);
        if(checkname && checkname.checked == true) {
            hasil = true;
        }
        i++;
    }
    if(hasil == true) {
        $('#btnSave').removeAttr('disabled');
        $('#btnSave').removeAttr('title');
    } else {
        $('#btnSave').attr('disabled', 'disabled');
        $('#btnSave').attr('title', 'Pilih dahulu mahasiswa');
    }
}

// Initialize select2 if available
$(document).ready(function() {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.discount-select').select2({
            placeholder: "Pilih Diskon / Beasiswa",
            allowClear: true
        });
    }
});
</script>
