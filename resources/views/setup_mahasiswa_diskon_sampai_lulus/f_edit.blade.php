@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <h3>Edit Beasiswa Mahasiswa</h3>
        <div class="alert alert-info">
            <strong><i class="fa fa-question-circle"></i> </strong> Edit Data Mahasiswa: {{ ($row_data->NPM ?? '') }} - {{ ($row_data->Nama ?? '') }}
        </div>
        <form id="f_transkdiskon_edit" onsubmit="savedata_edit(this); return false;"
              action="{{ url('setup_mahasiswa_diskon_sampai_lulus/save_alone/'.$save) }}"
              enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="ID" id="ID" value="{{ ($row->ID) ?? '' }}" />
            <input type="hidden" name="npm" value="{{ ($row_data->NPM) ?? '' }}" />
            <div class="form-row mt-3">
                <div class="form-group col-md-12">
                    <label class="col-form-label">Set Per Tahun Akademik ? *</label>
                    <div class="controls">
                        <input type="radio" onchange="showHide(1)" class="tahunSemester" id="PerTahunID_1" name="PerTahunID" value="1" {{ (($row->PerTahunID ?? '') == '1') ? 'checked' : '' }}> Ya <br />
                        <input type="radio" onchange="showHide(0)" class="tahunSemester2" id="PerTahunID_0" name="PerTahunID" value="0" {{ (($row->PerTahunID ?? '') == '0') ? 'checked' : '' }}> Tidak
                    </div>
                </div>
                <div class="form-group col-md-12" id="contentTahun">
                    <label class="col-form-label">Tahun Akademik *</label>
                    <div class="controls">
                        <select class="form-control ListTahunID" name="ListTahunID[]" id="ListTahunID" multiple>
                            @php
                                $selected_tahun = explode(',', $row->ListTahunID ?? '');
                            @endphp
                            @foreach(DB::table('tahun')->orderBy('TahunID', 'DESC')->get() as $raw)
                                @php
                                    $aktif = ($raw->ProsesBuka == 1) ? '(Aktif)' : '';
                                    $selected = in_array($raw->ID, $selected_tahun) ? 'selected' : '';
                                @endphp
                                <option {{ $selected }} value="{{ $raw->ID }}">{{ $raw->Nama }} {{ $aktif }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="col-form-label">Pilihan Diskon *</label>
                    <div class="controls">
                        <div class="table-responsive" id="dataPilihan">
                            <table class="table table-bordered table-hovered">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>Komponen Biaya</th>
                                        <th style="width: 40%;">Jenis Diskon</th>
                                        <th style="width: 15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyPilihan">
                                    @php
                                        $query_jenisbiaya = get_all('jenisbiaya');
                                        $arr_ex = json_decode($row->ListDiskon, true);
                                        $totalPilihan = count($arr_ex ?? []);
                                        $loop = 0;
                                    @endphp
                                    @if(count($arr_ex ?? []) > 0)
                                        @foreach($arr_ex as $k)
                                            <tr id="item_{{ $loop }}">
                                                <td>
                                                    <select class="form-control" name="JenisBiayaID[{{ $loop }}]" required>
                                                        <option value=""> -- Pilih --</option>
                                                        @foreach($query_jenisbiaya as $row_jenisbiaya)
                                                            @php
                                                                $s = ($row_jenisbiaya->ID == $k['JenisBiayaID']) ? 'selected' : '';
                                                            @endphp
                                                            <option value="{{ $row_jenisbiaya->ID }}" {{ $s }}>{{ $row_jenisbiaya->Nama }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-control ListMasterDiskonID" name="ListMasterDiskonID[{{ $loop }}][]" multiple required>
                                                        @php
                                                            $list_diskon_id = $k['ListMasterDiskonID'] ?? [];
                                                            if (!is_array($list_diskon_id)) {
                                                                $list_diskon_id = explode(',', $list_diskon_id);
                                                            }
                                                        @endphp
                                                        @foreach($master_diskon as $rowx)
                                                            @php
                                                                $hrg = ($rowx->Tipe == 'nominal') ? rupiah($rowx->Jumlah) : $rowx->Jumlah . ' %';
                                                                $view = $rowx->Nama;
                                                                $prodi = $rowx->prodi;
                                                                $s = in_array($rowx->ID, $list_diskon_id) ? 'selected' : '';
                                                            @endphp
                                                            <option {{ $s }} value="{{ $rowx->ID }}"> {{ $prodi }} -- {{ $view }} {{ $hrg }} </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="center">
                                                    <button type="button" class="btn btn-bordered-danger waves-effect waves-light btn-block btn-pilihan" onClick="deleteRow({{ $loop }});">
                                                        <i class="mdi mdi-delete"></i> Hapus
                                                    </button>
                                                </td>
                                            </tr>
                                            @php $loop++; @endphp
                                        @endforeach
                                    @endif
                                    <input type="hidden" id="totalPilihan" value="{{ $loop }}" />
                                </tbody>
                                <tfoot id="actionPilihan">
                                    <tr>
                                        <td colspan="2">Tambah</td>
                                        <td class="center">
                                            <button type="button" class="btn btn-bordered-success waves-effect waves-light btn-block" onclick="addPilihan();">
                                                <i class="mdi mdi-plus"></i> Tambah
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">
                {{ __('app.save') }} Data
            </button>
            <button type="button" onClick="back()" class="btn btn-bordered-danger waves-effect width-md waves-light">
                {{ __('app.back') }}
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
    if (typeof $.fn.select2 !== 'undefined') {
        $("#ListTahunID").select2({ placeholder: "Pilih Tahun Akademik", allowClear: true });
        autocompletebyclass("ListMasterDiskonID");
    }

    // Trigger showHide based on checked radio
    $("[name=PerTahunID]:checked").trigger("change");
});

var master_diskon_data = @json($master_diskon);
var jenisbiaya_data = @json(get_all('jenisbiaya'));

function rupiah(angka) {
    return 'Rp ' + parseInt(angka).toLocaleString('id-ID');
}

function addPilihan() {
    var nomor = parseInt($('#totalPilihan').val()) + 1;
    $('#totalPilihan').val(nomor);
    var temp = '';

    temp = '<tr id="item_' + nomor + '">';
    temp += '<td>';
    temp += "<select name='JenisBiayaID[" + nomor + "]' class='form-control' required>";
    temp += "<option value=''>-- Pilih --</option>";
    $.each(jenisbiaya_data, function(key_jb, value_jb) {
        temp += "<option value='" + value_jb.ID + "'>" + value_jb.Nama + "</option>";
    });
    temp += "</select>";
    temp += '</td>';
    temp += '<td>';
    temp += "<select class='form-control ListMasterDiskonID' name='ListMasterDiskonID[" + nomor + "][]' multiple required>";
    $.each(master_diskon_data, function(key_diskon, value_diskon) {
        var hrg = (value_diskon['Tipe'] == 'nominal') ? rupiah(value_diskon['Jumlah']) : value_diskon['Jumlah'] + ' %';
        temp += "<option value='" + value_diskon['ID'] + "'>" + value_diskon['prodi'] + " -- " + value_diskon['Nama'] + " " + hrg + "</option>";
    });
    temp += "</select>";
    temp += '</td>';
    temp += '<td class="center">';
    temp += '<button type="button" class="btn btn-bordered-danger waves-effect waves-light btn-block btn-pilihan" onClick="deleteRow(' + nomor + ');"><i class="mdi mdi-delete"></i> Hapus</button>';
    temp += '</td>';
    temp += '</tr>';

    $('#bodyPilihan').append(temp);
    if (typeof $.fn.select2 !== 'undefined') {
        autocompletebyclass("ListMasterDiskonID");
    }
}

function deleteRow(id) {
    swal({
        title: "Apakah anda yakin ?",
        text: "Anda akan menghapus item komponen biaya dan diskon.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Ya",
        cancelButtonText: "Batal"
    }).then(function() {
        $('#item_' + id).remove();
    });
}

function showHide(param) {
    if (param == 1) {
        $("#contentTahun").show();
        $("#ListTahunID").prop("required", true);
    } else if (param == 0) {
        $("#contentTahun").hide();
        $("#ListTahunID").prop("required", false);
    }
}

function savedata_edit(formz) {
    var formData = new FormData(formz);

    $.ajax({
        type: 'POST',
        url: $(formz).attr('action'),
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function(r) {
            silahkantunggu();
        },
        success: function(data) {
            if (data == 'gagal') {
                swal('Pemberitahuan', 'Gagal menyimpan data', 'warning');
                berhasil();
            } else {
                window.location = "{{ url('setup_mahasiswa_diskon_sampai_lulus') }}";
                berhasil();
                alertsuccess();
            }
        },
        error: function(data) {
            $(".btnSave").html('{{ __("app.save") }} Data');
            $(".btnSave").removeAttr("disabled");
            $(".alert-error").show();
            $(".alert-error-content").html("{{ __('app.alert-error') }}");
            window.setTimeout(function() { $(".alert-error").slideUp("slow"); }, 6000);
        }
    });
}
</script>
@endpush
