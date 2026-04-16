@extends('layouts.template1')

@section('content')
@php
    if(empty($row)) {
        $row = new stdClass();
        $row->ID = '';
        $row->Persen = '';
        $row->Nama = '';
        $row->ProdiID = '';
        $row->ProgramID = '';
        $row->TahunMasuk = '';
        $row->SemesterMasuk = '';
        $row->Tipe = 'persen';
        $row->JenisBiayaID_list = '';

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
        <form id="f_setup_persentase_bayar" onsubmit="savedata(this); return false;"
              action="{{ url('setup_persentase_bayar/save/'.$save) }}"
              enctype="multipart/form-data">
            @csrf
            <input class="col-md-12" type="hidden" name="ID" id="ID" value="{{ $row->ID }}">
            <h3>{{ $btn }} Setup Persentase Bayar</h3>
            <div class="form-row mt-3">
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="Nama">Nama *</label>
                    <div class="controls">
                        <select id="Nama" required name="Nama" class="form-control">
                            <option value="" selected="selected">-- Pilih --</option>
                            @foreach(get_all('nama_opsi') as $raw)
                                <option {{ ($raw->Nama == ($row->Nama ?? '')) ? 'selected' : '' }} value="{{ $raw->Nama }}">
                                    {{ $raw->Nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
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
                    <label class="col-form-label" for="TahunMasuk">Angkatan *</label>
                    <div class="controls">
                        <select id="TahunMasuk" required name="TahunMasuk" class="form-control">
                            <option value="0" selected="selected">-- Pilih Untuk Semua Angkatan --</option>
                            @foreach(($arr_tahun_angkatan ?? []) as $tahun_angkatan)
                                @if(trim($tahun_angkatan))
                                    <option {{ ($tahun_angkatan == ($row->TahunMasuk ?? '')) ? 'selected' : '' }} value="{{ $tahun_angkatan }}">
                                        {{ $tahun_angkatan }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="SemesterMasuk">Semester Masuk *</label>
                    <div class="controls">
                        <select id="SemesterMasuk" required name="SemesterMasuk" class="form-control">
                            <option value="0" selected="selected">-- Pilih Untuk Semua Semester Masuk --</option>
                            <option value="1" {{ (($row->SemesterMasuk ?? '') == '1') ? 'selected' : '' }}>Ganjil</option>
                            <option value="2" {{ (($row->SemesterMasuk ?? '') == '2') ? 'selected' : '' }}>Genap</option>
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="Tipe">Tipe *</label>
                    <div class="controls">
                        <select id="Tipe" required name="Tipe" class="form-control" onchange="text_persen()">
                            <option value="persen" {{ (($row->Tipe ?? '') == 'persen') ? 'selected' : '' }}>Persen (%)</option>
                            <option value="nominal" {{ (($row->Tipe ?? '') == 'nominal') ? 'selected' : '' }}>Nominal (Rp.)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="Persen"><div id="text_persen">Persen (%) *</div></label>
                    <div class="controls">
                        <input type="text" required onkeypress="return event.charCode > 47 && event.charCode < 58;"
                               id="Persen" name="Persen" class="form-control num" value="{{ $row->Persen ?? '' }}" />
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
    // Initialize Select2 for multi-select
    if (typeof $.fn.select2 !== 'undefined') {
        $('#JenisBiayaID_list').select2({
            placeholder: "Pilih Komponen Biaya",
            allowClear: true
        });
    }
});

function text_persen() {
    var Tipe = $('#Tipe').val();
    if(Tipe == 'persen') {
        $('#text_persen').text('Persen (%) *');
    } else {
        $('#text_persen').text('Nominal (Rp.) *');
    }
}

text_persen();

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
            if(data == 'gagal') {
                alertfail();
                berhasil();
            } else {
                if({{ $save }} == '1') {
                    window.location = "{{ url('setup_persentase_bayar') }}";
                }

                if({{ $save }} == '2') {
                    load_content('{{ url("setup_persentase_bayar/view/".$row->ID) }}');
                }
                berhasil();
                alertsuccess();
            }
        },
        error: function(data) {
            $(".btnSave").html('{{ __("app.save") }} Data <icon class="icon-check icon-white-t"></icon>');
            $(".btnSave").removeAttr("disabled");
            alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.');
        }
    });
}
</script>
@endpush
