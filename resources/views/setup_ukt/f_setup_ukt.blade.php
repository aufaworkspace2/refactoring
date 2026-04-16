@extends('layouts.template1')

@section('content')
@php
    if(empty($row)) {
        $row = new stdClass();
        $row->ID = '';
        $row->ProdiID = '';
        $row->ProgramID = '';
        $row->TahunMasuk = '';

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
        <form id="f_setup_ukt" onsubmit="savedata(this); return false;"
              action="{{ url('setup_ukt/save/'.$save) }}"
              enctype="multipart/form-data">
            @csrf
            <input class="col-md-12" type="hidden" name="ID" id="ID" value="{{ $row->ID }}">
            <h3>{{ $btn }} Setup Prodi UKT</h3>
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
                    <label class="col-form-label" for="TahunMasuk">Angkatan *</label>
                    <div class="controls">
                        <select id="TahunMasuk" required name="TahunMasuk" class="form-control">
                            <option value="0" selected="selected">-- Pilih Untuk Semua Angkatan --</option>
                            @php
                                $list_tahun = DB::table('mahasiswa')->select('TahunMasuk')->where('TahunMasuk', '!=', '')->groupBy('TahunMasuk')->get();
                                $tahun_pertama = date('Y');
                                foreach ($list_tahun as $tahun) {
                                    $tahun_pertama = $tahun->TahunMasuk;
                                    break;
                                }
                                $tahun_kedua = $tahun_pertama + 5;
                            @endphp
                            @while ($tahun_kedua >= $tahun_pertama)
                                <option {{ ($tahun_kedua == ($row->TahunMasuk ?? '')) ? 'selected' : '' }} value="{{ $tahun_kedua }}">
                                    {{ $tahun_kedua }}
                                </option>
                                @php $tahun_kedua--; @endphp
                            @endwhile
                            @foreach ($list_tahun as $raw)
                                <option {{ ($raw->TahunMasuk == ($row->TahunMasuk ?? '')) ? 'selected' : '' }} value="{{ $raw->TahunMasuk }}">
                                    {{ $raw->TahunMasuk }}
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
                    window.location = "{{ url('setup_ukt') }}";
                }

                if ({{ $save }} == '2') {
                    load_content('{{ url("setup_ukt/view/".$row->ID) }}');
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
