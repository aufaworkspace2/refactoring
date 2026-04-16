@extends('layouts.template1')

@section('content')
@php
    if(empty($row)) {
        $row = new stdClass();
        $row->ID = '';
        $row->Jenis = '';
        $row->ProdiID = '';
        $row->ProgramID = '';
        $row->TahunMasuk = '';
        $row->JenisPendaftaran = '';
        $row->Nominal = '';
        $row->NominalPaket = '';
        $row->NominalSkripsi = '';
        $row->HitungPraktek = '0';
        $row->NominalPraktek = '';
        $row->TanggalMulai = '';
        $row->TanggalSelesai = '';

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
        <form id="f_setup_harga_biaya_variable" onsubmit="savedata(this); return false;"
              action="{{ url('setup_harga_biaya_variable/save/'.$save) }}"
              enctype="multipart/form-data">
            @csrf
            <input class="col-md-12" type="hidden" name="ID" id="ID" value="{{ $row->ID }}">
            <h3>{{ $btn }} Setup Harga Variable</h3>
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
                <div class="form-group col-md-12">
                    <label class="col-form-label">Jenis Pendaftaran *</label>
                    <div class="controls">
                        <select id="JenisPendaftaran" name="JenisPendaftaran" class="form-control" required>
                            <option value="0" selected="selected">-- Pilih Untuk Semua Jenis Pendaftaran --</option>
                            @foreach(get_all('jenis_pendaftaran') as $raw)
                                <option {{ ($raw->Kode == ($row->JenisPendaftaran ?? '')) ? 'selected' : '' }} value="{{ $raw->Kode }}">
                                    {{ $raw->Nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="Jenis">Jenis *</label>
                    <div class="controls">
                        <select id="Jenis" required name="Jenis" class="form-control" onchange="showhidetanggal()">
                            <option value="">-- Pilih Jenis --</option>
                            <option value="SKS" {{ (($row->Jenis ?? '') == 'SKS') ? 'selected' : '' }}>SKS</option>
                            <option value="PKL" {{ (($row->Jenis ?? '') == 'PKL') ? 'selected' : '' }}>PKL</option>
                            <option value="KKN" {{ (($row->Jenis ?? '') == 'KKN') ? 'selected' : '' }}>KKN</option>
                            <option value="Komprehensif" {{ (($row->Jenis ?? '') == 'Komprehensif') ? 'selected' : '' }}>Komprehensif</option>
                            <option value="Skripsi" {{ (($row->Jenis ?? '') == 'Skripsi') ? 'selected' : '' }}>Skripsi</option>
                            <option value="Wisuda" {{ (($row->Jenis ?? '') == 'Wisuda') ? 'selected' : '' }}>Wisuda</option>
                            <option value="Cuti" {{ (($row->Jenis ?? '') == 'Cuti') ? 'selected' : '' }}>Cuti</option>
                            <option value="Kesehatan" {{ (($row->Jenis ?? '') == 'Kesehatan') ? 'selected' : '' }}>Biaya Tes Kesehatan</option>
                        </select>
                    </div>
                </div>
                <div id="div_tanggal" class="col-md-12" style="display:none;">
                    <div class="form-group">
                        <label class="col-form-label" for="TanggalMulai">Tanggal Mulai *</label>
                        <div class="controls">
                            <input type="date" id="TanggalMulai" name="TanggalMulai" class="form-control" value="{{ $row->TanggalMulai ?? '' }}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-form-label" for="TanggalSelesai">Tanggal Selesai *</label>
                        <div class="controls">
                            <input type="date" id="TanggalSelesai" name="TanggalSelesai" class="form-control" value="{{ $row->TanggalSelesai ?? '' }}" />
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="Nominal"><span id="txt_nominal">Nominal</span> *</label>
                    <div class="controls">
                        <input type="text" required id="Nominal" name="Nominal" class="form-control currency" value="{{ $row->Nominal ?? '' }}" />
                    </div>
                </div>
                <div id="div_sks" class="col-md-12" style="display:none;">
                    <div class="form-group">
                        <label class="col-form-label" for="HitungPraktek">Bedakan SKS Teori dan Praktek?</label>
                        <div class="controls">
                            <select class="form-control" name="HitungPraktek" id="HitungPraktek" onchange="changehitungpraktek()">
                                <option value="0" {{ (($row->HitungPraktek ?? '0') == '0') ? 'selected' : '' }}>Tidak</option>
                                <option value="1" {{ (($row->HitungPraktek ?? '0') == '1') ? 'selected' : '' }}>Ya</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" id="div_sks_praktek" style="display:none;">
                        <label class="col-form-label" for="NominalPraktek">Nominal Per SKS Praktek *</label>
                        <div class="controls">
                            <input type="text" id="NominalPraktek" name="NominalPraktek" class="form-control currency" value="{{ $row->NominalPraktek ?? '' }}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-form-label" for="NominalPaket">Nominal SKS Paket *</label>
                        <div class="controls">
                            <input type="text" id="NominalPaket" name="NominalPaket" class="form-control currency" value="{{ $row->NominalPaket ?? '' }}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-form-label" for="NominalSkripsi">Nominal SKS Skripsi *</label>
                        <div class="controls">
                            <input type="text" id="NominalSkripsi" name="NominalSkripsi" class="form-control currency" value="{{ $row->NominalSkripsi ?? '' }}" />
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
    if (typeof $.fn.mask !== 'undefined') {
        $('.currency').mask('#.##0', {reverse: true});
        $('.currency').trigger('input');
    }
    showhidetanggal();
    changehitungpraktek();
});

function showhidetanggal() {
    let jenis = $('#Jenis').val();

    $('#div_sks').hide();
    $('#div_tanggal').hide();
    $('#txt_nominal').text('Nominal');

    if (jenis == 'Cuti') {
        $('#div_tanggal').show();
    } else if (jenis == 'SKS') {
        $('#div_sks').show();
        $('#txt_nominal').text('Nominal Per SKS');
    }
}

function changehitungpraktek() {
    let jenis = $('#Jenis').val();

    if (jenis == 'SKS') {
        let hitung_praktek = $('#HitungPraktek').val();

        if (hitung_praktek == 1) {
            $('#div_sks_praktek').show();
            $('#txt_nominal').text('Nominal Per SKS Teori');
        } else {
            $('#div_sks_praktek').hide();
            $('#txt_nominal').text('Nominal Per SKS');
        }
    }
}

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
            if (data == 'gagal') {
                alertfail();
                berhasil();
            } else {
                if ({{ $save }} == '1') {
                    window.location = "{{ url('setup_harga_biaya_variable') }}";
                }

                if ({{ $save }} == '2') {
                    load_content('{{ url("setup_harga_biaya_variable/view/".$row->ID) }}');
                }
                berhasil();
                alertsuccess();
            }
            if (typeof $.fn.mask !== 'undefined') {
                $('.currency').mask('#.##0', {reverse: true});
                $('.currency').trigger('input');
            }
        },
        error: function(data) {
            $(".btnSave").html('{{ __("app.save") }} Data');
            $(".btnSave").removeAttr("disabled");
            alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.');
            if (typeof $.fn.mask !== 'undefined') {
                $('.currency').mask('#.##0', {reverse: true});
                $('.currency').trigger('input');
            }
        }
    });
}
</script>
@endpush
