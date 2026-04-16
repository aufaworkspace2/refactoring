@extends('layouts.template1')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h2>Pengaturan Mahasiswa</h2>
        <p class="lead">Pengaturan KRS/UTS/UAS{{ ($buka_opsi_nilai == 1) ? '/Nilai' : '' }} Mahasiswa</p>
        <hr />
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="button-list">
                    <div class="btn-group">
                        <button type="button" class="btn btn-info dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false">
                            <i class="mdi mdi-printer mr-1"></i> Cetak <i class="mdi mdi-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a onclick="excel()" href="javascript:void(0);" class="dropdown-item">
                                <i class="mdi mdi-printer"></i> {{ __('app.excel') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label class="col-form-label mt-2">
                    <h4 class="m-0">Tahun Akademik</h4>
                </label>
                <select class="tahunID form-control" onchange="filter()">
                    @foreach(get_all('tahun') as $row)
                        @php
                            $s = ($row->ProsesBuka == 1) ? 'selected' : '';
                        @endphp
                        <option {{ $s }} value="{{ $row->ID }}">
                            {{ $row->Nama }}{{ ($row->ProsesBuka == 1) ? ' (Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label mt-2">
                    <h4 class="m-0">{{ __('app.ProgramID') }}</h4>
                </label>
                <select class="programID form-control" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @foreach(get_all('program') as $row)
                        <option value="{{ $row->ID }}">{{ $row->ProgramID }} || {{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label mt-2">
                    <h4 class="m-0">{{ __('app.ProdiID') }}</h4>
                </label>
                <select class="prodiID form-control" onchange="filter()">
                    <option value="">-- {{ __('app.view_all') }} --</option>
                    @foreach(get_all('programstudi') as $row)
                        <option value="{{ $row->ID }}">{{ $row->ProdiID }} || {{ get_field($row->JenjangID, 'jenjang') }} || {{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label mt-2">
                    <h4 class="m-0">{{ __('app.StatusMhswID') }}</h4>
                </label>
                <select class="statusMhswID form-control" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @foreach(get_all('statusmahasiswa') as $row)
                        <option value="{{ $row->ID }}">{{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label mt-2">
                    <h4 class="m-0">{{ __('app.TahunMasuk') }}</h4>
                </label>
                <select class="tahunMasuk form-control" onchange="filter()">
                    @foreach($tahunMasuk as $row)
                        <option value="{{ $row->TahunMasuk }}">{{ $row->TahunMasuk }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label">
                    <h5 class="mb-0">{{ __('app.SemesterMasuk') }}</h5>
                </label>
                <select class="SemesterMasuk form-control" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    <option value="1">Ganjil</option>
                    <option value="2">Genap</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label mt-2">
                    <h4 class="m-0">Status Input Tagihan</h4>
                </label>
                <select class="statusInput form-control" onchange="filter()">
                    <option value="">-- Lihat Semua --</option>
                    <option value="1">Sudah</option>
                    <option value="0">Belum</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label mt-2">
                    <h4 class="m-0">Status Bayar</h4>
                </label>
                <select class="statusBayar form-control" onchange="filter()">
                    <option value="">-- Lihat Semua --</option>
                    <option value="1">Sudah Lunas</option>
                    <option value="0">Belum Lunas</option>
                </select>
            </div>
            <div class="form-group col-md-12">
                <label class="col-form-label mt-2">
                    <h4 class="m-0">{{ __('app.keyword_legend') }}</h4>
                </label>
                <input type="text" class="form-control keyword" onkeyup="filter()" placeholder="{{ __('app.keyword') }} .." />
            </div>
        </div>
    </div>
</div>
<form id="f_opsi_mahasiswa" onsubmit="savedata(this); return false;" action="{{ url('opsi_mahasiswa/save') }}">
    <div class="card">
        <div class="card-header">Form Settingan</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th class="text-center">KRS</th>
                            <th class="text-center">UTS</th>
                            <th class="text-center">UAS</th>
                            @if ($buka_opsi_nilai == 1)
                                <th class="text-center">NILAI</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">
                                <input id="krsOption" name="krsOption" type="checkbox" data-on-color="success" data-off-color="danger" checked />
                            </td>
                            <td class="text-center">
                                <input id="utsOption" name="utsOption" type="checkbox" data-on-color="success" data-off-color="danger" checked />
                            </td>
                            <td class="text-center">
                                <input id="uasOption" name="uasOption" type="checkbox" data-on-color="success" data-off-color="danger" checked />
                            </td>
                            @if ($buka_opsi_nilai == 1)
                                <td class="text-center">
                                    <input id="nilaiOption" name="nilaiOption" type="checkbox" data-on-color="success" data-off-color="danger" checked />
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <td colspan="5" class="text-center">
                                <p>
                                    <font style="color: black;">
                                        Apakah Settingan Ini Untuk Semua Mahasiswa Sesuai Filter ?
                                    </font>
                                </p>
                                <input id="v_pilihan" name="pilihan" type="checkbox" onchange="filter()" class="pilihan" data-on-color="success" data-off-color="danger" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div id="konten"></div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

$('#krsOption').bootstrapSwitch();
$('#utsOption').bootstrapSwitch();
$('#uasOption').bootstrapSwitch();
@if ($buka_opsi_nilai == 1)
    $('#nilaiOption').bootstrapSwitch();
@endif
$('#v_pilihan').bootstrapSwitch();

filter();

function savedata(formz) {
    var formData = new FormData(formz);
    $.ajax({
        type: "POST",
        dataType: 'JSON',
        url: $(formz).attr('action'),
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: function(data) {
            filter();
            if (data.status == 1) {
                $(".alert-success").show();
                $(".alert-success-content").html(data.message);
                window.setTimeout(function() {
                    $(".alert-success").slideUp();
                }, 10000);
            } else {
                $(".alert-error").show();
                $(".alert-error-content").html(data.message);
                window.setTimeout(function() {
                    $(".alert-error").slideUp("slow");
                }, 6000);
            }
        },
        error: function(data) {
            $(".alert-error").show();
            $(".alert-error-content").html("{{ __('app.alert-error') }}");
            window.setTimeout(function() {
                $(".alert-error").slideUp("slow");
            }, 6000);
        }
    });
    return false;
}

function filter(url) {
    if (url == null)
        url = "{{ url('opsi_mahasiswa/search') }}";

    var check = '';
    if ($('#v_pilihan').bootstrapSwitch("state")) {
        check = 'on';
    } else {
        check = 'off';
    }

    $.ajax({
        type: "POST",
        url: url,
        beforeSend: function(data) {
            $('.loading').fadeIn();
        },
        data: {
            programID: $(".programID").val(),
            prodiID: $(".prodiID").val(),
            tahunMasuk: $(".tahunMasuk").val(),
            statusMhswID: $(".statusMhswID").val(),
            tahunID: $(".tahunID").val(),
            SemesterMasuk: $(".SemesterMasuk").val(),
            statusBayar: $(".statusBayar").val(),
            statusInput: $(".statusInput").val(),
            type: check,
            keyword: $(".keyword").val(),
            _token: "{{ csrf_token() }}"
        },
        success: function(data) {
            $('.loading').fadeOut();
            $("#konten").html(data);
        },
        error: function(v) {
            $('.loading').fadeOut();
        }
    });
    return false;
}

function excel() {
    var programID = $(".programID").val();
    var prodiID = $(".prodiID").val();
    var tahunMasuk = $(".tahunMasuk").val();
    var statusMhswID = $(".statusMhswID").val();
    var tahunID = $(".tahunID").val();
    var SemesterMasuk = $(".SemesterMasuk").val();
    var statusBayar = $(".statusBayar").val();
    var statusInput = $(".statusInput").val();
    var keyword = $(".keyword").val();

    var link = "programID=" + programID;
    link += "&prodiID=" + prodiID;
    link += "&tahunMasuk=" + tahunMasuk;
    link += "&statusMhswID=" + statusMhswID;
    link += "&tahunID=" + tahunID;
    link += "&SemesterMasuk=" + SemesterMasuk;
    link += "&statusBayar=" + statusBayar;
    link += "&statusInput=" + statusInput;
    link += "&keyword=" + keyword;

    window.open('{{ url("opsi_mahasiswa/excel") }}/?' + link, '_Blank');
}

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
    }
}
</script>
@endpush
