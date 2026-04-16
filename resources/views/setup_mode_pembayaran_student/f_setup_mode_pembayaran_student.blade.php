@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <form onsubmit="savedata(this); return false;" id="f_setting"
              action="{{ url('setup_mode_pembayaran_student/set_publish_all/'.$save) }}"
              enctype="multipart/form-data">
            @csrf
            <h3>{{ $title }}</h3>
            <div class="form-row mt-3">
                <div class="form-group col-md-12">
                    <label class="col-form-label">&nbsp;</label>
                    <div class="controls">
                        <div class="table-responsive" id="dataMode">
                            <table class="table table-bordered table-hovered">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>List Prodi</th>
                                        <th style="width: 20%;">Cicilan Termin</th>
                                        <th style="width: 20%;">Cicilan Bebas</th>
                                        <th style="width: 15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyMode">
                                @php
                                    $totalMode = count($all_data);
                                    $idx = 0;
                                @endphp
                                @foreach($all_data as $k)
                                    <tr id="item_{{ $idx }}">
                                        <td>
                                            <input type="hidden" name="ID_list[{{ $idx }}]" value="{{ $k->ID }}">
                                            <select required name="ProdiID_list[{{ $idx }}][]" multiple class="form-control select2">
                                                <option value="">Pilih</option>
                                                @php
                                                    $prodi_k = explode(",", $k->ProdiID_list);
                                                @endphp
                                                @foreach($all_prodi as $raw)
                                                    @php
                                                        $nama_jenjang = $arr_nama_jenjang[$raw->JenjangID] ?? '';
                                                    @endphp
                                                    <option {{ in_array($raw->ID, $prodi_k) ? 'selected' : '' }} value="{{ $raw->ID }}">
                                                        {{ $nama_jenjang }} || {{ $raw->Nama }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <div class="form-check">
                                                <input type="radio" id="CicilTermin1_{{ $idx }}" name="CicilTermin[{{ $idx }}]" class="form-check-input" {{ ($k->CicilTermin == 1) ? 'checked' : '' }} value="1" required />
                                                <label class="form-check-label" for="CicilTermin1_{{ $idx }}">Aktif</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="CicilTermin0_{{ $idx }}" name="CicilTermin[{{ $idx }}]" class="form-check-input" {{ ($k->CicilTermin == 0) ? 'checked' : '' }} value="0" />
                                                <label class="form-check-label" for="CicilTermin0_{{ $idx }}">Tidak Aktif</label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check">
                                                <input type="radio" id="CicilBebas1_{{ $idx }}" name="CicilBebas[{{ $idx }}]" class="form-check-input" {{ ($k->CicilBebas == 1) ? 'checked' : '' }} value="1" required />
                                                <label class="form-check-label" for="CicilBebas1_{{ $idx }}">Aktif</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="CicilBebas0_{{ $idx }}" name="CicilBebas[{{ $idx }}]" class="form-check-input" {{ ($k->CicilBebas == 0) ? 'checked' : '' }} value="0" />
                                                <label class="form-check-label" for="CicilBebas0_{{ $idx }}">Tidak Aktif</label>
                                            </div>
                                        </td>
                                        <td class="center">
                                            <button type="button" class="btn btn-bordered-danger waves-effect waves-light btn-block btn-pilihan" onClick="deleteRow({{ $idx }});">
                                                <i class="mdi mdi-delete"></i> Hapus
                                            </button>
                                        </td>
                                    </tr>
                                    @php $idx++; @endphp
                                @endforeach
                                <input type="hidden" id="totalMode" value="{{ $idx }}" />
                                </tbody>
                                <tfoot id="actionMode">
                                    <tr>
                                        <td colspan="3"></td>
                                        <td class="center">
                                            <button type="button" class="btn btn-bordered-success waves-effect waves-light btn-block" onclick="addMode();">
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
            <br>
            <button type="submit" class="btn btn-primary btn-phone-block btnSave">
                {{ __('app.save') }} Data <icon class="icon-check icon-white-t"></icon>
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
let all_prodi = @json($all_prodi);
let arr_nama_jenjang = @json($arr_nama_jenjang);

$(document).ready(function() {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            placeholder: "Pilih",
            allowClear: true
        });
    }
});

function addMode() {
    let nomor = $('#totalMode').val();
    let temp = '';

    temp = '<tr id="item_' + nomor + '">';
    temp += '<td>';
    temp += '<input type="hidden" name="ID_list[' + nomor + ']" value="">';
    temp += '<select required name="ProdiID_list[' + nomor + '][]" multiple class="form-control select2">';
    temp += '<option value="">Pilih</option>';
    $.each(all_prodi, function(index, value) {
        let nama_jenjang = arr_nama_jenjang[value.JenjangID] || '';
        temp += '<option value="' + value.ID + '">' + nama_jenjang + ' || ' + value.Nama + '</option>';
    });
    temp += '</select>';
    temp += '</td>';
    temp += '<td>';
    temp += '<div class="form-check">';
    temp += '<input type="radio" id="CicilTermin1_' + nomor + '" name="CicilTermin[' + nomor + ']" class="form-check-input" checked value="1" required/>';
    temp += '<label class="form-check-label" for="CicilTermin1_' + nomor + '">Aktif</label>';
    temp += '</div>';
    temp += '<div class="form-check">';
    temp += '<input type="radio" id="CicilTermin0_' + nomor + '" name="CicilTermin[' + nomor + ']" class="form-check-input" value="0" />';
    temp += '<label class="form-check-label" for="CicilTermin0_' + nomor + '">Tidak Aktif</label>';
    temp += '</div>';
    temp += '</td>';
    temp += '<td>';
    temp += '<div class="form-check">';
    temp += '<input type="radio" id="CicilBebas1_' + nomor + '" name="CicilBebas[' + nomor + ']" class="form-check-input" value="1" required/>';
    temp += '<label class="form-check-label" for="CicilBebas1_' + nomor + '">Aktif</label>';
    temp += '</div>';
    temp += '<div class="form-check">';
    temp += '<input type="radio" id="CicilBebas0_' + nomor + '" name="CicilBebas[' + nomor + ']" class="form-check-input" checked value="0" />';
    temp += '<label class="form-check-label" for="CicilBebas0_' + nomor + '">Tidak Aktif</label>';
    temp += '</div>';
    temp += '</td>';
    temp += '<td class="center">';
    temp += '<button type="button" class="btn btn-bordered-danger waves-effect waves-light btn-block btn-pilihan" onClick="deleteRow(' + nomor + ');"><i class="mdi mdi-delete"></i> Hapus</button>';
    temp += '</td>';
    temp += '</tr>';

    nomor++;
    $('#totalMode').val(nomor);
    $('#bodyMode').append(temp);

    if (typeof $.fn.select2 !== 'undefined') {
        $('#item_' + (nomor - 1) + ' .select2').select2({
            placeholder: "Pilih",
            allowClear: true
        });
    }
}

function deleteRow(id) {
    swal({
        title: "Apakah anda yakin ?",
        text: "Anda akan menghapus item.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Ya",
        cancelButtonText: "Batal"
    }).then(function() {
        $('#item_' + id).remove();
    });
}

function savedata(formz) {
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
            load_content('{{ url("setup_mode_pembayaran_student") }}');
            berhasil();
            alertsuccess();
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
