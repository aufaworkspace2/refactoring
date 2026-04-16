@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <form id="f_setup_minimal_bayar_cicilan_bebas_student" onsubmit="savedata(this); return false;"
              action="{{ url('setup_minimal_bayar_cicilan_bebas_student/save/' . $save) }}"
              enctype="multipart/form-data">
            @csrf
            <input class="col-md-12" type="hidden" name="ID" id="ID" value="{{ $row->ID }}">
            <h3>{{ ($save == 1) ? __('app.add') : __('app.edit') }} Setup Persentase Bayar</h3>
            <div class="form-row mt-3">

                <div class="form-group col-md-12">
                    <label class="col-form-label" for="ProgramID"> Program Kuliah *</label>
                    <div class="controls">
                        <select id="ProgramID" required name="ProgramID" class="form-control">
                            <option value="0" {{ ($row->ProgramID == '0') ? 'selected' : '' }}>
                                -- Pilih Untuk Semua Program Kuliah --
                            </option>
                            @foreach(get_all('program') as $raw)
                                <option value="{{ $raw->ID }}" {{ ($raw->ID == $row->ProgramID) ? 'selected' : '' }}>
                                    {{ $raw->Nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label class="col-form-label" for="ProdiID"> Program Studi *</label>
                    <div class="controls">
                        <select id="ProdiID" required name="ProdiID" class="form-control">
                            <option value="0" {{ ($row->ProdiID == '0') ? 'selected' : '' }}>
                                -- Pilih Untuk Semua Program Studi --
                            </option>
                            @php
                                $arr_nama_jenjang = [];
                                foreach(get_all('programstudi') as $raw) {
                                    if(!isset($arr_nama_jenjang[$raw->JenjangID])) {
                                        $arr_nama_jenjang[$raw->JenjangID] = get_field($raw->JenjangID, 'jenjang');
                                    }
                                    $nama_jenjang = $arr_nama_jenjang[$raw->JenjangID];
                            @endphp
                            <option value="{{ $raw->ID }}" {{ ($raw->ID == $row->ProdiID) ? 'selected' : '' }}>
                                {{ $nama_jenjang }} || {{ $raw->Nama }}
                            </option>
                            @php } @endphp
                        </select>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label class="col-form-label" for="TahunMasuk"> Angkatan *</label>
                    <div class="controls">
                        <select id="TahunMasuk" required name="TahunMasuk" class="form-control">
                            <option value="0" {{ ($row->TahunMasuk == '0') ? 'selected' : '' }}>
                                -- Pilih Untuk Semua Angkatan --
                            </option>
                            @php
                                $arr_tahun_angkatan = [];
                                $get_tahun = DB::table('mahasiswa')
                                    ->select('TahunMasuk')
                                    ->distinct()
                                    ->get();
                                foreach($get_tahun as $arr) {
                                    $arr_tahun_angkatan[$arr->TahunMasuk] = $arr->TahunMasuk;
                                }

                                if(!empty($arr_tahun_angkatan)) {
                                    $angkatan_terakhir = max($arr_tahun_angkatan);
                                    $angkatan_terakhir_plus = $angkatan_terakhir + 2;

                                    for($i = $angkatan_terakhir; $i <= $angkatan_terakhir_plus; $i++) {
                                        $arr_tahun_angkatan[$i] = $i;
                                    }
                                } else {
                                    $current_year = date('Y');
                                    $arr_tahun_angkatan[$current_year] = $current_year;
                                    $arr_tahun_angkatan[$current_year + 1] = $current_year + 1;
                                    $arr_tahun_angkatan[$current_year + 2] = $current_year + 2;
                                }

                                rsort($arr_tahun_angkatan);
                            @endphp
                            @foreach($arr_tahun_angkatan as $tahun_angkatan)
                                @if(trim($tahun_angkatan))
                                    <option value="{{ $tahun_angkatan }}" {{ ($tahun_angkatan == $row->TahunMasuk) ? 'selected' : '' }}>
                                        {{ $tahun_angkatan }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label class="col-form-label" for="Jumlah">Jumlah *</label>
                    <div class="controls">
                        <input type="text" required id="Jumlah" name="Jumlah" class="form-control currency"
                               value="{{ $row->Jumlah }}" />
                    </div>
                </div>

            </div>
            <button onClick="btnEdit({{ $save }},1)" type="button"
                    class="btn btn-bordered-success waves-effect width-md waves-light btnEdit">
                {{ ($save == 1) ? __('app.add') : __('app.edit') }} Data <i class="icon-ok-circle icon-white-t"></i>
            </button>
            <button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">
                {{ __('app.save') }} Data <i class="icon-check icon-white-t"></i>
            </button>
            <button type="button" onClick="back()" class="btn btn-bordered-danger waves-effect width-md waves-light">
                {{ __('app.back') }} <i class="icon-share-alt icon-white-t"></i>
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
function set_currency() {
    $('.currency').mask('#.##0', {reverse: true});
    $('.currency').trigger('input');
}

function unset_currency() {
    $('.currency').unmask();
}

set_currency();

function savedata(formz) {
    unset_currency();

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
                    window.location = "{{ url('setup_minimal_bayar_cicilan_bebas_student') }}";
                }

                if({{ $save }} == '2') {
                    window.location = "{{ url('setup_minimal_bayar_cicilan_bebas_student') }}";
                }
                berhasil();
                alertsuccess();
            }
        },
        error: function(data) {
            set_currency();
            $(".btnSave").html('{{ __("app.save") }} Data <i class="icon-check icon-white-t"></i>');
            $(".btnSave").removeAttr("disabled");
            alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.');
        }
    });
}

function btnEdit(type, checkid) {
    $("input:text").attr('disabled', true);
    $(".num").attr('disabled', true);
    $("input:file").attr('disabled', true);
    $("input:radio").attr('disabled', true);
    $("button:submit").attr('disabled', true);
    $("select").attr('disabled', true);
    $("textarea").attr('disabled', true);
    $(".btnSave").css('display', 'none');

    if (checkid == 1) {
        $("input:text").removeAttr('disabled');
        $(".num").removeAttr('disabled');
        $("input:file").removeAttr('disabled');
        $("input:radio").removeAttr('disabled');
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
