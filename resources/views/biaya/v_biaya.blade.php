@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-header">Setting Biaya Mahasiswa</div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-12">
                <label class="col-form-label mt-2">
                    <h5 class="m-0">Program Kuliah</h5>
                </label>
                <div class="controls">
                    <select name="ProgramID" id="ProgramID" class="form-control ProgramID">
                        <option value="">-- Pilih Program Kuliah --</option>
                        @foreach(get_all('program') as $row)
                            <option value="{{ $row->ID }}" {{ ($row->ID == $ProgramID) ? 'selected' : '' }}>
                                {{ $row->Nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group col-md-12">
                <label class="col-form-label mt-2">
                    <h5 class="m-0">Program Studi</h5>
                </label>
                <div class="controls">
                    <select name="ProdiID" id="ProdiID" class="form-control ProdiID">
                        <option value="">-- Pilih Program Studi --</option>
                        @foreach(get_all('programstudi') as $row)
                            <option value="{{ $row->ID }}" {{ ($row->ID == $ProdiID) ? 'selected' : '' }}>
                                {{ get_field($row->JenjangID, 'jenjang') }} | {{ $row->Nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group col-md-12">
                <label class="col-form-label mt-2">
                    <h5 class="m-0">Tahun Masuk</h5>
                </label>
                <div class="controls">
                    @php
                        $loop = 0;
                        $query1 = DB::table('identitas')->selectRaw("YEAR(TglBerdiriPT) as TahunBerdiri")->first();
                        $query2 = DB::table(DB::raw('(SELECT YEAR(NOW()) as TahunSekarang) as t'))->first();
                        $tahunberdiri = $query1->TahunBerdiri ?? date('Y');
                        $tahunsekarang = ($query2->TahunSekarang ?? date('Y')) + 3;
                        if ($tahunberdiri) {
                            $loop = $tahunsekarang - $tahunberdiri;
                        }
                    @endphp
                    <select name="TahunMasuk" id="TahunMasuk" class="form-control TahunMasuk">
                        <option value="">-- Pilih Tahun Masuk --</option>
                        @for($i = 0; $i <= $loop; $i++)
                            <option value="{{ $tahunsekarang }}" {{ ($tahunsekarang == $TahunMasuk) ? 'selected' : '' }}>
                                {{ $tahunsekarang }}
                            </option>
                            @php $tahunsekarang--; @endphp
                        @endfor
                    </select>
                </div>
            </div>
            <div class="form-group col-md-12">
                <label class="col-form-label mt-2">
                    <h5 class="m-0">Jalur Pendaftaran</h5>
                </label>
                <div class="controls">
                    <select id="JalurPendaftaran" name="JalurPendaftaran" class="form-control" required>
                        <option value="">-- Pilih Jalur Pendaftaran --</option>
                        @foreach(DB::table('pmb_edu_jalur_pendaftaran')->where('aktif', '1')->get() as $raw)
                            <option value="{{ $raw->id }}" {{ ($raw->id == $JalurPendaftaran) ? 'selected' : '' }}>
                                {{ $raw->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group col-md-12">
                <label class="col-form-label mt-2">
                    <h5 class="m-0">Jenis Pendaftaran</h5>
                </label>
                <div class="controls">
                    <select id="JenisPendaftaran" name="JenisPendaftaran" class="form-control" required>
                        <option value="">-- Pilih Jenis Pendaftaran --</option>
                        @foreach(get_all('jenis_pendaftaran') as $raw)
                            <option value="{{ $raw->Kode }}" {{ ($raw->Kode == $JenisPendaftaran) ? 'selected' : '' }}>
                                {{ $raw->Nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group col-md-12">
                <label class="col-form-label mt-2">
                    <h5 class="m-0">Semester Masuk</h5>
                </label>
                <div class="controls">
                    <select id="SemesterMasuk" name="SemesterMasuk" class="form-control SemesterMasuk" required>
                        <option value="">-- Pilih Semester Masuk --</option>
                        @foreach(DB::table('semester_masuk')->get() as $raw)
                            <option value="{{ $raw->SemesterMasuk }}" {{ ($raw->SemesterMasuk == $SemesterMasuk) ? 'selected' : '' }}>
                                {{ $raw->Nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group col-md-12">
                <label class="col-form-label mt-2">
                    <h5 class="m-0">Gelombang Ke</h5>
                </label>
                <div class="controls">
                    <select id="GelombangKe" name="GelombangKe" class="form-control GelombangKe" required>
                        <option value="">-- Pilih Gelombang Ke Berapa --</option>
                        @foreach(DB::table('gelombang_ke')->get() as $raw)
                            <option value="{{ $raw->GelombangKe }}" {{ ($raw->GelombangKe == $GelombangKe) ? 'selected' : '' }}>
                                {{ $raw->Nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group col-md-12">
                <label class="col-form-label mt-2">
                    <h5 class="m-0">&nbsp;</h5>
                </label>
                <div class="controls">
                    <button class="btn btn-bordered-primary" type="button" onclick="filter();">
                        <icon class="fa fa-pencil"></icon> Input Data _
                    </button>
                    <button class="btn btn-bordered-info" type="button" id="copy_data" onclick="tampilkan();">
                        <icon class="icon-download-alt"></icon> Copy Data
                    </button>
                    <button class="btn btn-bordered-success" type="button" id="cetak_excel" onclick="excel();">
                        <icon class="icon-download-alt"></icon> Export Excel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div id="konten"></div>
    </div>
</div>

<!-- Copy Data Modal -->
<div id="div_modal">
    <div class="modal" id="modal-biaya" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="mdl">Copy Biaya Mahasiswa</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-header">
                            <strong>Copy Data Biaya Dari :</strong>
                        </div>
                        <div class="card-body">
                            <div class="form-group col-md-12">
                                <div class="controls row">
                                    <div class="col-md-8" id="div_jumlah">
                                        <table>
                                            <tr>
                                                <td width="30%">Program Kuliah</td>
                                                <td>:</td>
                                                <td width="70%"> <b><span id="Program_Kuliah"></span></b></td>
                                            </tr>
                                            <tr>
                                                <td>Program Studi</td>
                                                <td>:</td>
                                                <td> <b><span id="Program_Studi"></span></b></td>
                                            </tr>
                                            <tr>
                                                <td>Tahun Masuk</td>
                                                <td>:</td>
                                                <td> <b><span id="Tahun_Masuk"></span></b></td>
                                            </tr>
                                            <tr>
                                                <td>Jalur Pendaftaran</td>
                                                <td>:</td>
                                                <td> <b><span id="Jalur_Pendaftaran"></span></b></td>
                                            </tr>
                                            <tr>
                                                <td>Jenis Pendaftaran</td>
                                                <td>:</td>
                                                <td> <b><span id="Jenis_Pendaftaran"></span></b></td>
                                            </tr>
                                            <tr>
                                                <td>Semester Masuk</td>
                                                <td>:</td>
                                                <td> <b><span id="Semester_Masuk"></span></b></td>
                                            </tr>
                                            <tr>
                                                <td>Gelombang Ke</td>
                                                <td>:</td>
                                                <td> <b><span id="Gelombang_Ke"></span></b></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <strong>Copy Data Biaya Untuk :</strong>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="col-form-label mt-2">
                                        <h5 class="m-0">Program Kuliah</h5>
                                    </label>
                                    <select name="ProgramID2" id="ProgramID2" class="form-control ProgramID2">
                                        <option value="">-- Pilih Program Kuliah --</option>
                                        @foreach(get_all('program') as $row)
                                            <option value="{{ $row->ID }}">{{ $row->Nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="col-form-label mt-2">
                                        <h5 class="m-0">Program Studi</h5>
                                    </label>
                                    <select name="ProdiID2" id="ProdiID2" class="form-control ProdiID2">
                                        <option value="">-- Pilih Program Studi --</option>
                                        @foreach(get_all('programstudi') as $row)
                                            <option value="{{ $row->ID }}">{{ get_field($row->JenjangID, 'jenjang') }} | {{ $row->Nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="col-form-label mt-2">
                                        <h5 class="m-0">Tahun Masuk</h5>
                                    </label>
                                    <select name="TahunMasuk2" id="TahunMasuk2" class="form-control TahunMasuk2">
                                        <option value="">-- Pilih Tahun Masuk --</option>
                                        @for($i = 0; $i <= $loop; $i++)
                                            <option value="{{ $tahunsekarang }}">{{ $tahunsekarang }}</option>
                                            @php $tahunsekarang--; @endphp
                                        @endfor
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="col-form-label mt-2">
                                        <h5 class="m-0">Jalur Pendaftaran</h5>
                                    </label>
                                    <select id="JalurPendaftaran2" name="JalurPendaftaran2" class="form-control">
                                        <option value="">-- Pilih Jalur Pendaftaran --</option>
                                        @foreach(DB::table('pmb_edu_jalur_pendaftaran')->where('aktif', '1')->get() as $raw)
                                            <option value="{{ $raw->id }}">{{ $raw->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="col-form-label mt-2">
                                        <h5 class="m-0">Jenis Pendaftaran</h5>
                                    </label>
                                    <select id="JenisPendaftaran2" name="JenisPendaftaran2" class="form-control">
                                        <option value="">-- Pilih Jenis Pendaftaran --</option>
                                        @foreach(get_all('jenis_pendaftaran') as $raw)
                                            <option value="{{ $raw->Kode }}">{{ $raw->Nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="col-form-label mt-2">
                                        <h5 class="m-0">Semester Masuk</h5>
                                    </label>
                                    <select id="SemesterMasuk2" name="SemesterMasuk2" class="form-control SemesterMasuk2">
                                        <option value="">-- Pilih Semester Masuk --</option>
                                        @foreach(DB::table('semester_masuk')->get() as $raw)
                                            <option value="{{ $raw->SemesterMasuk }}">{{ $raw->Nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="col-form-label mt-2">
                                        <h5 class="m-0">Gelombang Ke</h5>
                                    </label>
                                    <select id="GelombangKe2" name="GelombangKe2" class="form-control GelombangKe2">
                                        <option value="">-- Pilih Gelombang Ke Berapa --</option>
                                        @foreach(DB::table('gelombang_ke')->get() as $raw)
                                            <option value="{{ $raw->GelombangKe }}">{{ $raw->Nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="hidden" id="max_semester" name="max_semester" value="">
                                <div class="form-group col-md-6">
                                    <label class="col-form-label mt-2">
                                        <h5 class="m-0">Copy Untuk Semester?</h5>
                                    </label>
                                    <select id="CopySemester" name="CopySemester" class="form-control CopySemester">
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick='savet()'>Copy Data</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

$("#cetak_excel").hide();
$("#copy_data").hide();

function tampilkan() {
    $('#modal-biaya').modal('show');

    let ProgramID = $('#ProgramID option:selected').text();
    let ProdiID = $('#ProdiID option:selected').text();
    let TahunMasuk = $('#TahunMasuk option:selected').text();
    let JalurPendaftaran = $('#JalurPendaftaran option:selected').text();
    let JenisPendaftaran = $('#JenisPendaftaran option:selected').text();
    let SemesterMasuk = $('#SemesterMasuk option:selected').text();
    let GelombangKe = $('#GelombangKe option:selected').text();

    $.ajax({
        url: "{{ url('biaya/get_semester_biaya') }}",
        type: "post",
        data: {
            TahunMasuk: $("#TahunMasuk").val(),
            JalurPendaftaran: $("#JalurPendaftaran").val(),
            JenisPendaftaran: $("#JenisPendaftaran").val(),
            ProgramID: $("#ProgramID").val(),
            ProdiID: $("#ProdiID").val(),
            SemesterMasuk: $("#SemesterMasuk").val(),
            GelombangKe: $("#GelombangKe").val(),
            _token: "{{ csrf_token() }}"
        },
        dataType: "json",
        success: function(data) {
            $('#CopySemester').html(data.semester_option);
            const maxSemester = data.max_semester?.Semester ?? null;
            $('#max_semester').val(maxSemester);
            $("#Program_Kuliah").html(ProgramID);
            $("#Program_Studi").html(ProdiID);
            $("#Tahun_Masuk").html(TahunMasuk);
            $("#Jalur_Pendaftaran").html(JalurPendaftaran);
            $("#Jenis_Pendaftaran").html(JenisPendaftaran);
            $("#Semester_Masuk").html(SemesterMasuk);
            $("#Gelombang_Ke").html(GelombangKe);
        }
    });
}

function filter(url) {
    if(url == null)
        url = "{{ url('biaya/search') }}";

    $.ajax({
        type: "POST",
        url: url,
        data: {
            TahunMasuk: $("#TahunMasuk").val(),
            JalurPendaftaran: $("#JalurPendaftaran").val(),
            JenisPendaftaran: $("#JenisPendaftaran").val(),
            ProgramID: $("#ProgramID").val(),
            ProdiID: $("#ProdiID").val(),
            SemesterMasuk: $("#SemesterMasuk").val(),
            GelombangKe: $("#GelombangKe").val(),
            _token: "{{ csrf_token() }}"
        },
        beforeSend: function(data) {
            $("#konten").html("<center><h2><i class='fa fa-spin fa-spinner'></i> Memuat Data ... </h2></center>");
        },
        success: function(data) {
            $("#konten").html(data);
        }
    });
    return false;
}

function savedata(formz) {
    if (typeof $.fn.unmask !== 'undefined') {
        unset_currency();
    }

    var formData = new FormData(formz);
    $.ajax({
        type: 'POST',
        url: $(formz).attr('action'),
        data: formData,
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function(r) {
            silahkantunggu();
        },
        success: function(data) {
            if (typeof $.fn.mask !== 'undefined') {
                set_currency();
            }

            if (data.status != 1) {
                swal('Pemberitahuan', data.message, data.type);
                berhasil();
            } else {
                filter();
                berhasil();
                swal('Pemberitahuan', data.message, data.type);
            }
        },
        error: function(data) {
            if (typeof $.fn.mask !== 'undefined') {
                set_currency();
            }

            $(".btnSave").html('Simpan Data <icon class="icon-check icon-white-t"></icon>');
            $(".btnSave").removeAttr("disabled");
            alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.');
        }
    });
}

function savet() {
    let isValid = true;
    let kosong = [];

    const fields = [
        'ProgramID2', 'ProdiID2', 'TahunMasuk2',
        'JalurPendaftaran2', 'JenisPendaftaran2',
        'SemesterMasuk2', 'GelombangKe2'
    ];

    fields.forEach(function(id) {
        const value = $('#' + id).val();
        const label = $('#' + id).closest('.form-group').find('label h5').text();

        if (!value) {
            isValid = false;
            kosong.push(label);
        }
    });

    if (!isValid) {
        swal('Pemberitahuan',
            `Silakan isi inputan berikut:<br><ul style="text-align:left;">` +
            kosong.map(label => `<li>${label}</li>`).join('') +
            `</ul>`, 'warning');
        return;
    }

    $.ajax({
        type: 'POST',
        url: "{{ url('biaya/copy_biaya') }}",
        data: {
            TahunMasuk: $("#TahunMasuk2").val(),
            JalurPendaftaran: $("#JalurPendaftaran2").val(),
            JenisPendaftaran: $("#JenisPendaftaran2").val(),
            ProgramID: $("#ProgramID2").val(),
            ProdiID: $("#ProdiID2").val(),
            SemesterMasuk: $("#SemesterMasuk2").val(),
            GelombangKe: $("#GelombangKe2").val(),
            max_semester: $("#max_semester").val(),
            CopySemester: $("#CopySemester").val(),
            TahunMasukSumber: $("#TahunMasuk").val(),
            JalurPendaftaranSumber: $("#JalurPendaftaran").val(),
            JenisPendaftaranSumber: $("#JenisPendaftaran").val(),
            ProgramIDSumber: $("#ProgramID").val(),
            ProdiIDSumber: $("#ProdiID").val(),
            SemesterMasukSumber: $("#SemesterMasuk").val(),
            GelombangKeSumber: $("#GelombangKe").val(),
            _token: "{{ csrf_token() }}"
        },
        dataType: 'json',
        cache: false,
        beforeSend: function(r) {
            silahkantunggu();
        },
        success: function(data) {
            if (data.status != 1) {
                swal('Pemberitahuan', data.message, data.type);
                berhasil();
            } else {
                filter();
                berhasil();
                swal('Pemberitahuan', data.message, data.type);
            }
        },
        error: function() {
            swal('Pemberitahuan', `Terjadi kesalahan pada server.`, 'error');
        }
    });
}

function excel() {
    var TahunMasuk = $(".TahunMasuk").val();
    var ProgramID = $(".ProgramID").val();
    var ProdiID = $(".ProdiID").val();
    var JalurPendaftaran = $("#JalurPendaftaran").val();
    var JenisPendaftaran = $("#JenisPendaftaran").val();
    var SemesterMasuk = $("#SemesterMasuk").val();
    var GelombangKe = $("#GelombangKe").val();

    var link = "TahunMasuk=" + TahunMasuk;
    link += "&ProgramID=" + ProgramID;
    link += "&ProdiID=" + ProdiID;
    link += "&JalurPendaftaran=" + JalurPendaftaran;
    link += "&JenisPendaftaran=" + JenisPendaftaran;
    link += "&SemesterMasuk=" + SemesterMasuk;
    link += "&GelombangKe=" + GelombangKe;

    window.open('{{ url("biaya/excel") }}/?' + link, '_Blank');
}
</script>
@endpush
