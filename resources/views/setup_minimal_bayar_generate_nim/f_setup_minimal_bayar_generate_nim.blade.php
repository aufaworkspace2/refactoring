@extends('layouts.template1')

@section('content')
@php
    if(empty($row)) {
        $row = new stdClass();
        $row->ID = '';
        $row->Jenis = 'Generate NIM';
        $row->ProdiID = '';
        $row->ProgramID = '';
        $row->Nominal = '';
        $row->Jumlah = '';
        $row->Tipe = 'nominal';
        $row->JenisBiayaID_list = '';

        $judul = __('app.title_add');
        $slog = __('app.slog_add');
        $btn = __('app.add');
    } else {
        $judul = __('app.title_view');
        $slog = __('app.slog_view') . '<b>' . ($row->Jenis ?? '') . '</b>';
        $btn = __('app.edit');
    }
@endphp

<div class="card">
    <div class="card-body">
        <form id="f_setup_minimal_bayar_generate_nim" onsubmit="savedata(this); return false;"
              action="{{ url('setup_minimal_bayar_generate_nim/save/'.$save) }}"
              enctype="multipart/form-data">
            @csrf
            <input class="col-md-12" type="hidden" name="ID" id="ID" value="{{ $row->ID }}">
            <input type="hidden" name="Jenis" value="Generate NIM">
            <h3>{{ $btn }} Setup Minimal Bayar Generate NIM</h3>
            <div class="form-row mt-3">
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="ProgramID">Program Kuliah *</label>
                    <div class="controls">
                        <select id="ProgramID" required name="ProgramID" class="form-control">
                            <option value="0" selected="selected">-- Pilih Untuk Semua Program Kuliah --</option>
                            @foreach(get_all('program') as $raw)
                                <option {{ ($raw->ID == ($row->ProgramID ?? '')) ? 'selected' : '' }} value="{{ $raw->ID }}">
                                    {{ $raw->Nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="ProdiID">Program Studi *</label>
                    <div class="controls">
                        <select id="ProdiID" required name="ProdiID" class="form-control">
                            <option value="0" selected="selected">-- Pilih Untuk Semua Program Studi --</option>
                            @php
                                $arr_nama_jenjang = [];
                            @endphp
                            @foreach(get_all('programstudi') as $raw)
                                @php
                                    if(!isset($arr_nama_jenjang[$raw->JenjangID])) {
                                        $arr_nama_jenjang[$raw->JenjangID] = get_field($raw->JenjangID, 'jenjang');
                                    }
                                    $nama_jenjang = $arr_nama_jenjang[$raw->JenjangID];
                                @endphp
                                <option {{ ($raw->ID == ($row->ProdiID ?? '')) ? 'selected' : '' }} value="{{ $raw->ID }}">
                                    {{ $nama_jenjang }} || {{ $raw->Nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="Tipe">Tipe</label>
                    <div class="controls">
                        <label>
                            <input type="radio" class="mr-1" onchange="changeTipe('persen')" id="TipePersen" name="Tipe" {{ (($row->Tipe ?? '') == 'persen') ? 'checked' : '' }} value="persen" />
                            <span>Persen</span>
                        </label>
                        <label>
                            <input type="radio" class="mr-1" onchange="changeTipe('nominal')" id="TipeNominal" name="Tipe" {{ (($row->Tipe ?? '') == 'nominal') ? 'checked' : '' }} value="nominal" />
                            <span>Nominal</span>
                        </label>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="Jumlah" id="labelJumlah">Nominal *</label>
                    <div class="input-group">
                        <div class="input-group-append" id="div_rupiah">
                            <span class="input-group-text" id="basic-addon1">Rp.</span>
                        </div>
                        <input type="text" class="form-control" id="Jumlah" required name="Jumlah" value="{{ $row->Jumlah ?? $row->Nominal ?? '' }}" />
                        <div class="input-group-append" id="div_percent" style="display: none;">
                            <span class="input-group-text" id="basic-addon2">%</span>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="JenisBiayaID_list">Komponen Biaya</label>
                    <div class="controls">
                        <select id="JenisBiayaID_list" name="JenisBiayaID_list[]" class="form-control" multiple>
                            @php
                                $arr_jb = explode(",", $row->JenisBiayaID_list ?? '');
                            @endphp
                            @foreach(get_all('jenisbiaya') as $row_jb)
                                <option value="{{ $row_jb->ID }}" {{ in_array($row_jb->ID, $arr_jb) ? 'selected' : '' }}>
                                    {{ $row_jb->Nama }}
                                </option>
                            @endforeach
                        </select>
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
        $('#JenisBiayaID_list').select2({
            placeholder: "Pilih Komponen Biaya",
            allowClear: true
        });
    }

    changeTipe('{{ $row->Tipe ?? "nominal" }}');
});

function changeTipe(val) {
    if (val == 'persen') {
        $('#labelJumlah').html('Persen *');
        $('#Jumlah').attr('maxlength', 3);
        $('#Jumlah').attr('max', 100);
        $('#Jumlah').prop('maxlength', 3);
        $('#Jumlah').prop('max', 100);
        $('#Jumlah').attr("onkeypress", "return event.charCode > 47 && event.charCode < 58;");
        $('#Jumlah').attr("type", "number");

        // Clear formatting
        $('#Jumlah').val($('#Jumlah').val().replace(/[.,]/g, ''));
        $('#div_rupiah').hide();
        $('#div_percent').show();

    } else {
        $('#labelJumlah').html('Nominal *');
        $('#Jumlah').removeAttr('maxlength');
        $('#Jumlah').removeProp('maxlength');
        $('#Jumlah').removeAttr('max');
        $('#Jumlah').removeProp('max');
        $('#Jumlah').removeAttr('onkeypress');
        $('#Jumlah').attr("type", "text");

        $('#div_rupiah').show();
        $('#div_percent').hide();
    }
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
            if (data == 'gagal') {
                alertfail();
                berhasil();
            } else {
                if ({{ $save }} == '1') {
                    window.location = "{{ url('setup_minimal_bayar_generate_nim') }}";
                }

                if ({{ $save }} == '2') {
                    load_content('{{ url("setup_minimal_bayar_generate_nim/view/".$row->ID) }}');
                }
                berhasil();
                alertsuccess();
            }
        },
        error: function(data) {
            $(".btnSave").html('{{ __("app.save") }} Data');
            $(".btnSave").removeAttr("disabled");
            alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.');
        }
    });
}
</script>
@endpush
