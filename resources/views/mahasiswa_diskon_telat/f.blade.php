@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <h3>Form Set Beasiswa Mahasiswa</h3>
        <div class="alert alert-info font-size-12">
            <strong><i class="fa fa-question-circle"></i> </strong> Form Ini Untuk Men Set Mahasiswa Berhak Diskon / Beasiswa Sesuai Periode Yang Ditentukan
            <br>
            <strong><i class="fa fa-question-circle"></i> </strong> Mahasiswa yang dapat di set Diskon / Beasiswa adalah Mahasiswa yang sudah di Input Draft Tagihannya tetapi belum di posting Tagihannya.
        </div>
        <form id="f_transkdiskon" onsubmit="savedata(this); return false;"
              action="{{ url('mahasiswa_diskon_telat/save/' . $save) }}"
              enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="ID" id="ID" value="{{ ($row->ID) ?? '' }}">
            <div class="form-row mt-3">

                <div class="form-group col-md-12" id="contentTahun">
                    <label class="col-form-label" for="TahunID">Tahun Akademik *</label>
                    <div class="controls">
                        <select class="form-control TahunID" name="TahunID" id="TahunID">
                            @php
                                $this_tahun = DB::table('tahun')->orderBy('TahunID', 'DESC')->get();
                            @endphp
                            @foreach($this_tahun as $raw)
                                @php
                                    $aktif = ($raw->ProsesBuka == 1) ? '(Aktif)' : '';
                                @endphp
                                <option value="{{ $raw->ID }}" {{ ($raw->ProsesBuka == 1) ? 'selected' : '' }}>
                                    {{ $raw->Nama }} {{ $aktif }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group col-md-12" id="parm_prog">
                    <label class="col-form-label" for="ProgramID">Program *</label>
                    <div class="controls">
                        <select class="form-control ProgramID" id="ProgramID" name="ProgramID" onchange="filter();">
                            <option value="">-- Pilih Program --</option>
                            @foreach(DB::table('program')->get() as $row)
                                <option value="{{ $row->ID }}">{{ $row->Nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group col-md-12" id="parm_prodi">
                    <label class="col-form-label" for="ProdiID">Program Studi *</label>
                    <div class="controls">
                        <select class="form-control ProdiID" id="ProdiID" name="ProdiID" onchange="filter();">
                            @php
                                $query_jenjang = DB::table('jenjang')->get();
                                $jenjang = [];
                                foreach($query_jenjang as $row_jenjang) {
                                    $jenjang[$row_jenjang->ID] = $row_jenjang->Nama;
                                }
                            @endphp
                            @foreach(DB::table('programstudi')->get() as $row)
                                @php
                                    $nama_jenjang = $jenjang[$row->JenjangID] ?? '';
                                @endphp
                                <option value="{{ $row->ID }}">{{ $nama_jenjang }} || {{ $row->Nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group col-md-12" id="parm_angkatan">
                    <label class="col-form-label" for="TahunMasuk">Tahun Masuk *</label>
                    <div class="controls">
                        <select id="TahunMasuk" name="TahunMasuk" onchange="filter();" class="form-control" multiple>
                            @php
                                $get_tahun = DB::table('mahasiswa')
                                    ->select('TahunMasuk')
                                    ->distinct()
                                    ->orderBy('TahunMasuk', 'DESC')
                                    ->get();
                            @endphp
                            @foreach($get_tahun as $row)
                                <option value="{{ $row->TahunMasuk }}">{{ $row->TahunMasuk }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group col-md-12" id="mhs">
                    <label class="col-form-label" for="keyword"> Pencarian</label>
                    <div class="controls input-group">
                        <input type="text" class="form-control keyword" name="keyword" id="keyword" value=""
                               placeholder="Pencarian dengan NIM / Nama ...">
                        <div class="input-group-append">
                            <a class="btn btn-info waves-effect waves-light" href="javascript:void(0);" onclick="filter()">
                                <i class="fa fa-search"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div id="konten"></div>

            <button type="submit" id="btnSave" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave" disabled>
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
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});
$(document).ready(function() {
    autocompletebyclass('TahunID','--Pilih Data--')
    autocomplete('TahunMasuk','--Pilih Data--')
    autocomplete('ProgramID','--Pilih Data--')
    autocomplete('ProdiID','--Pilih Data--')
});

var save = '{{ $save }}';

function filter(url) {
    if(url == null)
        url = "{{ url('mahasiswa_diskon_telat/filtermhs') }}";

    $.ajax({
        type: "POST",
        url: url,
        data: {
            TahunMasuk: $("#TahunMasuk").val(),
            ProgramID: $(".ProgramID").val(),
            ProdiID: $(".ProdiID").val(),
            KelasID: $(".KelasID").val(),
            TahunID: $(".TahunID").val(),
            keyword: $(".keyword").val(),
            _token: "{{ csrf_token() }}"
        },
        beforeSend: function(data) {
            $("#konten").html("<center><h3><i class='fa fa-spin fa-spinner'></i> Loading.. </h3></center>");
        },
        success: function(data) {
            $("#konten").html(data);
        }
    });
    return false;
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
            if(data == 'gagal') {
                alert('Maaf, Nama Pemberi Sudah ada sebelumnya');
            } else {
                if(save == '1') {
                    window.location = "{{ url('mahasiswa_diskon_telat') }}";
                }

                if(save == '2') {
                    window.location = "{{ url('mahasiswa_diskon_telat') }}";
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

window.show_btnSave = function() {
    i = 0; hasil = false;
    while(document.getElementsByName('checkID[]').length > i) {
        var checkname = document.getElementById('checkID' + i).checked;

        if(checkname == true) {
            hasil = true;
        }
        i++;
    }
    if(hasil == true) {
        $('#btnSave').removeAttr('disabled');
        $('#btnSave').removeAttr('title');
    } else {
        $('#btnSave').attr('disabled', 'disabled');
        $('#btnSave').attr('title', 'Pilih dahulu mahasiswa');
    }
}

// Load filtermhs on page load
filter();
</script>
@endpush
