@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <h3>Form Beasiswa Mahasiswa</h3>
        <div class="alert alert-info">
            <strong><i class="fa fa-question-circle"></i> </strong> Form Ini Untuk Men Set Mahasiswa Berhak Diskon / Beasiswa Sesuai Periode Yang Ditentukan
        </div>
        <form id="f_transkdiskon" onsubmit="savedata(this); return false;"
              action="{{ url('mahasiswa_diskon/save/' . $save) }}"
              enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="ID" id="ID" value="{{ ($row->ID) ?? '' }}">
            <div class="form-row mt-3">
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="NamaBank">Set Per Tahun Akademik ? *</label>
                    <div class="controls">
                        <input type="radio" onchange="showHide(1)" class="tahunSemester" id="PerTahunID1"
                               name="PerTahunID" value="1" {{ (($row->PerTahunID ?? 0) == 1) ? 'checked' : '' }}> Ya <br/>
                        <input type="radio" onchange="showHide(0)" class="tahunSemester2" id="PerTahunID0"
                               name="PerTahunID" value="0" {{ (($row->PerTahunID ?? 0) == 0) ? 'checked' : '' }}> Tidak
                    </div>
                </div>

                <div class="form-group col-md-12" id="contentTahun">
                    <label class="col-form-label" for="NamaBank">Tahun Akademik *</label>
                    <div class="controls">
                        <select class="form-control ListTahunID" name="ListTahunID[]" id="ListTahunID" multiple>
                            @php
                                $arr_tahun_id = explode(",", $row->ListTahunID ?? '');
                                $this_tahun = DB::table('tahun')->orderBy('TahunID', 'DESC')->get();
                            @endphp
                            @foreach($this_tahun as $raw)
                                @php
                                    $aktif = ($raw->ProsesBuka == 1) ? '(Aktif)' : '';
                                @endphp
                                <option value="{{ $raw->ID }}" {{ (in_array($raw->ID, $arr_tahun_id)) ? 'selected' : '' }}>
                                    {{ $raw->Nama }} {{ $aktif }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label class="col-form-label" for="NamaBank"> Jenis Diskon / Beasiswa*</label>
                    <div class="controls">
                        <select class="form-control ListMasterDiskonID" id="ListMasterDiskonID" name="ListMasterDiskonID[]" multiple required>
                            @php
                                $list_diskon_id = explode(",", $row->ListMasterDiskonID ?? '');
                            @endphp
                            @foreach($master_diskon as $rowx)
                                @php
                                    $hrg = ($rowx->Tipe == 'nominal')
                                        ? rupiah($rowx->Jumlah)
                                        : $rowx->Jumlah . ' %';
                                    $view = $rowx->Nama;
                                    $s = (in_array($rowx->ID, $list_diskon_id)) ? 'selected' : '';
                                @endphp
                                <option {{ $s }} value="{{ $rowx->ID }}">
                                    {{ $view }} {{ $hrg }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group col-md-12" id="mhs">
                    <label class="col-form-label" for="MhswID">Pilih Mahasiswa *</label>
                    <div class="controls">
                        <select class="key form-control" id="key" name="npm" required>
                            @if(($row_data->NPM ?? null) == null)
                                <option value="" selected="selected">Masukan NPM / Nama Mhs</option>
                            @else
                                <option value="{{ $row_data->NPM }}" selected="selected">
                                    {{ $row_data->NPM }} | {{ $row_data->Nama }}
                                </option>
                            @endif
                        </select>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">
                {{ __('app.save') }} Data <i class="icon-check icon-white-t"></i>
            </button>
            <button type="button" onClick="back()" class="btn btn-bordered-danger waves-effect width-md waves-light">
                {{ __('app.back') }} <i class="icon-share-alt icon-white-t"></i>
            </button>

            <div id="konten"></div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

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
                alert('Maaf, Nama Pemberi Sudah ada sebelumnya');
            } else {
                if({{ $save }} == '1') {
                    window.location = "{{ url('mahasiswa_diskon') }}";
                }

                if({{ $save }} == '2') {
                    window.location = "{{ url('mahasiswa_diskon') }}";
                }
                berhasil();
                alertsuccess();
            }
        },
        error: function(data) {
            $(".btnSave").html('{{ __("app.save") }} Data <i class="icon-check icon-white-t"></i>');
            $(".btnSave").removeAttr("disabled");
            $(".alert-error").show();
            $(".alert-error-content").html("{{ __('app.alert-error') }}");
            window.setTimeout(function() { $(".alert-error").slideUp("slow"); }, 6000);
        }
    });
}

function showHide(type) {
    if(type == 1) {
        $('#contentTahun').show();
    } else {
        $('#contentTahun').hide();
    }
}

// Initialize select2
$(document).ready(function() {
    if (typeof $.fn.select2 !== 'undefined') {
        $('#ListTahunID, #ListMasterDiskonID, #key').select2({
            placeholder: "Pilih",
            allowClear: true
        });
    }
});
</script>
@endpush
