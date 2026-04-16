@extends('layouts.template1')

@section('content')
@php
    if(empty($row)) {
        $row = new stdClass();
        $row->ID = '';
        $row->Nama = '';
        $row->Tipe = 'nominal';
        $row->Jumlah = '';
        $row->ProdiID = '';
        $row->BiayaAwalID = '';
        $row->RangeAwalNilaiUSM = '';
        $row->RangeAkhirNilaiUSM = '';
        $row->JenisDiskon = 'potong_dari_total';

        $judul = __('app.title_add');
        $slog = __('app.slog_add');
        $btn = __('app.add');
    } else {
        $judul = __('app.title_view');
        $slog = __('app.slog_view') . '<b>' . ($row->Nama ?? '') . '</b>';
        $btn = __('app.edit');
    }
@endphp

<div class="card">
    <div class="card-body">
        <form id="f_master_diskon" onsubmit="savedata(this); return false;"
              action="{{ url('master_diskon/save/'.$save) }}"
              enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="ID" id="ID" value="{{ $row->ID }}">
            <h3>Form Diskon</h3>
            <div class="form-row mt-3">
                <div class="form-group col-md-12" id="Nama">
                    <label class="col-form-label" for="Nama">Nama Diskon</label>
                    <div class="controls">
                        <input type="text" id="Nama" name="Nama" class="form-control" value="{{ $row->Nama }}" />
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="Tipe">Tipe</label>
                    <div class="controls">
                        <label>
                            <input type="radio" class="mr-1" onchange="changeTipe('persen')" id="TipePersen" name="Tipe" {{ ($row->Tipe == 'persen') ? 'checked' : '' }} value="persen" />
                            <span>Persen</span>
                        </label>
                        <label>
                            <input type="radio" class="mr-1" onchange="changeTipe('nominal')" id="TipeNominal" name="Tipe" {{ ($row->Tipe == 'nominal') ? 'checked' : '' }} value="nominal" />
                            <span>Nominal</span>
                        </label>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="JenisDiskon">Jenis</label>
                    <div class="controls">
                        <label>
                            <input type="radio" class="mr-1" id="JenisDiskonTotal" name="JenisDiskon" {{ ($row->JenisDiskon == 'potong_dari_total') ? 'checked' : '' }} value="potong_dari_total" />
                            <span>Potong Dari Nominal Tagihan</span>
                        </label>
                        <label>
                            <input type="radio" class="mr-1" id="JenisDiskonSisa" name="JenisDiskon" {{ ($row->JenisDiskon == 'potong_dari_sisa') ? 'checked' : '' }} value="potong_dari_sisa" />
                            <span>Pembayaran Diskon</span>
                        </label>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="Jumlah" id="labelJumlah">Nominal *</label>
                    <div class="input-group">
                        <div class="input-group-append" id="div_rupiah">
                            <span class="input-group-text" id="basic-addon1">Rp.</span>
                        </div>
                        <input type="text" class="form-control" id="Jumlah" required name="Jumlah" value="{{ $row->Jumlah }}" />
                        <div class="input-group-append" id="div_percent" style="display: none;">
                            <span class="input-group-text" id="basic-addon2">%</span>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="ProdiID">Program Studi</label>
                    <div class="controls">
                        <select id="ProdiID" name="ProdiID" class="form-control">
                            <option value="" {{ empty($row->ProdiID) ? 'selected' : '' }}>-- Pilih Prodi --</option>
                            @php
                                $nama_jenjang = [];
                            @endphp
                            @foreach(get_all('programstudi') as $raw)
                                @php
                                    if(!isset($nama_jenjang[$raw->JenjangID])){
                                        $nama_jenjang[$raw->JenjangID] = get_field($raw->JenjangID, 'jenjang');
                                    }
                                @endphp
                                <option {{ ($raw->ID == $row->ProdiID) ? 'selected' : '' }} value="{{ $raw->ID }}">
                                    {{ $nama_jenjang[$raw->JenjangID] ?? '' }} | {{ $raw->Nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="BiayaAwalID">Kategori Diskon</label>
                    <div class="controls">
                        <select id="BiayaAwalID" name="BiayaAwalID" class="form-control">
                            <option value="" {{ empty($row->BiayaAwalID) ? 'selected' : '' }}>-- Pilih Kategori Diskon --</option>
                            @foreach(DB::table('biaya_awal')->where('kategori_diskon', '1')->get() as $raw)
                                <option {{ ($raw->ID == $row->BiayaAwalID) ? 'selected' : '' }} value="{{ $raw->ID }}">{{ $raw->Nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-12" id="RangeAwalNilaiUSM">
                    <label class="col-form-label" for="RangeAwalNilaiUSM">Range Awal Nilai USM</label>
                    <div class="controls">
                        <input type="number" id="RangeAwalNilaiUSM" name="RangeAwalNilaiUSM" class="form-control number col-md-3" min="0" max="100" value="{{ $row->RangeAwalNilaiUSM }}" />
                    </div>
                </div>
                <div class="form-group col-md-12" id="RangeAkhirNilaiUSM">
                    <label class="col-form-label" for="RangeAkhirNilaiUSM">Range Akhir Nilai USM</label>
                    <div class="controls">
                        <input type="number" id="RangeAkhirNilaiUSM" name="RangeAkhirNilaiUSM" class="form-control number col-md-3" min="0" max="100" value="{{ $row->RangeAkhirNilaiUSM }}" />
                    </div>
                </div>
            </div>
            <button onClick="btnEdit({{ $save }}, 1)" type="button" class="btn btn-bordered-success waves-effect width-md waves-light btnEdit">{{ $btn }} Data</button>
            <button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">{{ __('app.save') }} Data</button>
            <button type="button" onClick="back()" class="btn btn-bordered-danger waves-effect width-md waves-light">{{ __('app.back') }}</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
function changeTipe(val) {
    if (val == 'persen') {
        $('#labelJumlah').html('Persen *');
        $('#labelPersenBelakang').show();
        $('#Jumlah').attr('maxlength', 3);
        $('#Jumlah').attr('max', 100);
        $('#Jumlah').prop('maxlength', 3);
        $('#Jumlah').prop('max', 100);
        $('#Jumlah').attr("onkeypress", "return event.charCode > 47 && event.charCode < 58;");
        if (typeof $.fn.unmask !== 'undefined') {
            $('.currency').unmask();
        }

        $('#Jumlah').removeClass('currency');
        let val = $('#Jumlah').val();
        val = val.replace(".", "");
        $('#Jumlah').val(val);
        $('#div_rupiah').hide();
        $('#div_percent').show();

    } else {
        $('#labelJumlah').html('Nominal *');
        $('#labelPersenBelakang').hide();
        $('#Jumlah').removeAttr('maxlength');
        $('#Jumlah').removeProp('maxlength');
        $('#Jumlah').removeAttr('max');
        $('#Jumlah').removeProp('max');
        $('#Jumlah').removeAttr('onkeypress');
        $('#Jumlah').addClass('currency');
        if (typeof $.fn.mask !== 'undefined') {
            $('.currency').mask('#.##0', {reverse: true});
            $('.currency').trigger('input');
        }
        $('#div_rupiah').show();
        $('#div_percent').hide();
    }
}

changeTipe('{{ $row->Tipe ?? "nominal" }}');

function savedata(formz) {
    if (typeof $.fn.unmask !== 'undefined') {
        $('.currency').unmask();
    }

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
            console.log(data);
            if (data == 'gagal') {
                swal('Maaf, Edit Tidak Diziinkan');
                berhasil();
            } else {
                if ({{ $save }} == '1') {
                    window.location = "{{ url('master_diskon') }}";
                }

                if ({{ $save }} == '2') {
                    window.location = "{{ url('master_diskon') }}";
                }
                berhasil();
                alertsuccess();
            }
        },
        error: function(data) {
            $(".btnSave").html('{{ __("app.save") }} Data <icon class="icon-check icon-white-t"></icon>');
            $(".btnSave").removeAttr("disabled");
            $(".alert-error").show();
            $(".alert-error-content").html("{{ __('app.alert-error') }}");
            window.setTimeout(function() { $(".alert-error").slideUp("slow"); }, 6000);
        }
    });
}

function btnEdit(type, checkid) {
    $("input:text").attr('disabled', true);
    $("input:file").attr('disabled', true);
    $("input:radio").attr('disabled', true);
    $("button:submit").attr('disabled', true);
    $(".number").attr('disabled', true);
    $("select").attr('disabled', true);
    $("textarea").attr('disabled', true);
    $(".btnSave").css('display', 'none');

    if (checkid == 1) {
        $("input:text").removeAttr('disabled');
        $("input:file").removeAttr('disabled');
        $("input:radio").removeAttr('disabled');
        $(".number").removeAttr('disabled');
        $("select").removeAttr('disabled');
        $("textarea").removeAttr('disabled');
        $("button:submit").removeAttr('disabled');
        $(".btnEdit").fadeOut(0);
        $(".btnSave").fadeIn(0);
    }
}

btnEdit({{ $save }});
</script>
@endpush
