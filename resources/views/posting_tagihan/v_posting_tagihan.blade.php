@extends('layouts.template1')

@section('content')
<style>
    .disabled {
        pointer-events: none;
        cursor: default;
    }
</style>

<div class="card">
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-3">
                <label class="col-form-label mt-2"><h4 class="m-0">Tahun Semester</h4></label>
                <select class="tahunID form-control" id="tahunID" onchange="filter();">
                    <option value="">-- Pilih Tahun --</option>
                    @foreach(DB::table('tahun')->orderBy('TahunID', 'DESC')->get() as $row)
                        <option value="{{ $row->ID }}" {{ $row->ProsesBuka == 1 ? 'selected' : '' }}>
                            {{ $row->Nama }} {{ $row->ProsesBuka == 1 ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label mt-2"><h4 class="m-0">Program Kuliah</h4></label>
                <select class="programID form-control" id="programID" onchange="filter();">
                    <option value="">-- Semua --</option>
                    @foreach(DB::table('program')->get() as $row)
                        <option value="{{ $row->ID }}">{{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label mt-2"><h4 class="m-0">Program Studi</h4></label>
                <select class="prodiID form-control" id="prodiID" onchange="filter();">
                    <option value="">-- Semua --</option>
                    @foreach(DB::table('programstudi')->get() as $row)
                        <option value="{{ $row->ID }}">
                            {{ $row->ProdiID }} || {{ DB::table('jenjang')->where('ID', $row->JenjangID)->value('Nama') ?? '' }} || {{ $row->Nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label class="col-form-label mt-2"><h4 class="m-0">Tahun Masuk</h4></label>
                <select class="tahunMasuk form-control" id="tahunMasuk" onchange="filter()">
                    @foreach(DB::table('mahasiswa')->select('TahunMasuk')->distinct()->orderBy('TahunMasuk', 'DESC')->get() as $row)
                        <option value="{{ $row->TahunMasuk }}">{{ $row->TahunMasuk }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label mt-2"><h4 class="m-0">Jalur Pendaftaran</h4></label>
                <select class="jalurPendaftaran form-control" id="jalurPendaftaran" onchange="filter()">
                    <option value="">-- Semua --</option>
                    @foreach(DB::table('pmb_edu_jalur_pendaftaran')->where('aktif', '1')->get() as $row)
                        <option value="{{ $row->id }}">{{ $row->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label mt-2"><h4 class="m-0">Jenis Pendaftaran</h4></label>
                <select class="jenisPendaftaran form-control" id="jenisPendaftaran" onchange="filter()">
                    <option value="">-- Semua --</option>
                    @foreach(DB::table('jenis_pendaftaran')->where('Aktif', 'Ya')->get() as $jp)
                        <option value="{{ $jp->Kode }}">{{ $jp->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label mt-2"><h4 class="m-0">Gelombang Ke</h4></label>
                <select class="GelombangKe form-control" id="GelombangKe" onchange="filter()">
                    <option value="">-- Semua --</option>
                    @foreach(DB::table('gelombang_ke')->get() as $gel)
                        <option value="{{ $gel->GelombangKe }}">{{ $gel->Nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label class="col-form-label mt-2"><h4 class="m-0">Semester Masuk</h4></label>
                <select class="SemesterMasuk form-control" id="SemesterMasuk" onchange="filter()">
                    <option value="">-- Semua --</option>
                    @foreach(DB::table('semester_masuk')->get() as $sm)
                        <option value="{{ $sm->SemesterMasuk }}">{{ $sm->Nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label class="col-form-label mt-2"><h4 class="m-0">Status Draft</h4></label>
                <select class="statusDraft form-control" id="statusDraft" onchange="filter()">
                    <option value="">-- Semua --</option>
                    <option value="belum">Belum</option>
                    <option value="sudah">Sudah</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label mt-2"><h4 class="m-0">Status Posting</h4></label>
                <select class="statusPosting form-control" id="statusPosting" onchange="filter()">
                    <option value="">-- Semua --</option>
                    <option value="belum">Belum</option>
                    <option value="sudah">Sudah</option>
                </select>
            </div>
            <div class="form-group col-md-5">
                <label class="col-form-label mt-2"><h4 class="m-0">Pencarian</h4></label>
                <input type="text" class="form-control keyword" id="keyword" onkeyup="filter()" placeholder="Cari NPM/Nama..">
            </div>
            <div class="form-group col-md-1">
                <label class="col-form-label mt-2"><h5 class="m-0">&nbsp;</h5></label>
                <div class="mt-0">
                    <button type="button" onclick="filter()" class="btn btn-primary waves-effect waves-light"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="button-list">
                    <div class="btn-group">
                        <button type="button" class="btn btn-info dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false">
                            <i class="mdi mdi-book-plus-multiple-outline mr-1"></i> Draft Tagihan <i class="mdi mdi-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a onclick="create_draft_all()" href="javascript:void(0);" class="dropdown-item disabled" id="btn_create_all_draft">
                                <i class="mdi mdi-book-plus-outline"></i> Input Draft Tagihan yang Dipilih
                            </a>
                            <a onclick="posting_all(0)" href="javascript:void(0);" class="dropdown-item disabled" id="btn_delete_all_draft">
                                <i class="mdi mdi-book-remove-outline"></i> Hapus Draft Tagihan yang Dipilih
                            </a>
                        </div>
                    </div>
                    <button class="btn btn-bordered-success waves-effect width-md waves-light" onclick="posting_all(1)" id="btn_posting_all" disabled>
                        Posting Draft Tagihan yang Dipilih
                    </button>&nbsp;
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div id="konten">
            <center>
                <h3>-- Klik Tombol Cari Untuk Menampilkan Data --</h3>
            </center>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    function filter(url)
    {
        if(url == null)
            url = "{{ route('posting_tagihan.search') }}";

        $.ajax({
            type: "POST",
            url: url,
            beforeSend: function() {
                $('#konten').html('<center><i class="fa fa-spin fa-spinner"></i> Silahkan Tunggu...</center>');
            },
            data: {
                programID       : $("#programID").val(),
                prodiID         : $("#prodiID").val(),
                jalurPendaftaran: $("#jalurPendaftaran").val(),
                tahunMasuk      : $("#tahunMasuk").val(),
                jenisPendaftaran: $("#jenisPendaftaran").val(),
                tahunID         : $("#tahunID").val(),
                keyword         : $("#keyword").val(),
                GelombangKe     : $("#GelombangKe").val(),
                SemesterMasuk   : $("#SemesterMasuk").val(),
                statusPosting   : $("#statusPosting").val(),
                statusDraft     : $("#statusDraft").val(),
            },
            success: function(data) {
                $("#konten").html(data);
            }
        });
        return false;
    }

    function posting(id, tahunID, posting)
    {
        var text;
        if(posting == 1){
            text = "Untuk Posting Draft Tagihan yang Dipilih ";
        } else if(posting == 0){
            text = "Untuk Menghapus Draft Tagihan yang Dipilih ";
        }
        swal({
            title: "Apakah Anda Yakin ?",
            text: text,
            type: "warning",
            showCloseButton: true,
            showCancelButton: true,
            focusConfirm: false
        }).then(function(isConfirm) {
            if (isConfirm) {
                $.ajax({
                    type: "POST",
                    dataType: 'JSON',
                    url: "{{ route('posting_tagihan.posting') }}",
                    beforeSend: function() {
                        $('#konten').html('<center><i class="fa fa-spin fa-spinner"></i> Silahkan Tunggu...</center>');
                    },
                    data: {
                        id      : id,
                        tahunID : tahunID,
                        posting : posting,
                    },
                    success: function(data) {
                        if (data.status == 1) {
                            alertsuccess(data.message);
                            filter();
                        } else {
                            swal('Pemberitahuan', data.message, 'error');
                            filter();
                        }
                    },
                    error: function (data) {
                        swal('Pemberitahuan', 'Maaf, data gagal diproses!.', 'error');
                        filter();
                    }
                });
            }
        });

        return false;
    }

    function create_draft_all()
    {
        var selected = [];
        $('input:checkbox[name="checkID[]"]:checked').each(function() {
            selected.push($(this).val());
        });

        var text = "Untuk Buat Draft Tagihan yang Dipilih ";

        swal({
            title: "Apakah Anda Yakin ?",
            text: text,
            type: "warning",
            showCloseButton: true,
            showCancelButton: true,
            focusConfirm: false
        }).then(function(isConfirm) {
            if (isConfirm) {
                $.ajax({
                    type: "POST",
                    dataType: 'JSON',
                    url: "{{ route('posting_tagihan.draftAll') }}",
                    beforeSend: function() {
                        $('#konten').html('<center><i class="fa fa-spin fa-spinner"></i> Silahkan Tunggu...</center>');
                    },
                    data: {
                        selected : selected,
                        tahunID  : $('#valTahunID').val()
                    },
                    success: function(data) {
                        if (data.status != 1) {
                            swal('Pemberitahuan', data.message.replace('|', '\n'), 'error');
                        } else {
                            swal('Pemberitahuan', data.message.replace('|', '\n'), 'success');
                        }
                        filter();
                    },
                    error: function (data) {
                        swal('Pemberitahuan', 'Maaf, data gagal diproses!.', 'error');
                        filter();
                    }
                });
            }
        });
        return false;
    }

    function posting_all(posting)
    {
        var selected = [];
        $('input:checkbox[name="checkID[]"]:checked').each(function() {
            selected.push($(this).val());
        });

        var text;
        if(posting == 1){
            text = "Untuk Posting Draft Tagihan yang Dipilih ";
        } else if(posting == 0){
            text = "Untuk Menghapus Draft Tagihan yang Dipilih ";
        }
        swal({
            title: "Apakah Anda Yakin ?",
            text: text,
            type: "warning",
            showCloseButton: true,
            showCancelButton: true,
            focusConfirm: false
        }).then(function(isConfirm) {
            if (isConfirm) {
                $.ajax({
                    type: "POST",
                    dataType: 'JSON',
                    url: "{{ route('posting_tagihan.postingAll') }}",
                    beforeSend: function() {
                        $('#konten').html('<center><i class="fa fa-spin fa-spinner"></i> Silahkan Tunggu...</center>');
                    },
                    data: {
                        selected : selected,
                        tahunID  : $('#valTahunID').val(),
                        posting  : posting,
                    },
                    success: function(data) {
                        if (data.status == 1) {
                            alertsuccess(data.message);
                            filter();
                        } else {
                            swal('Pemberitahuan', data.message, 'error');
                            filter();
                        }
                    },
                    error: function (data) {
                        swal('Pemberitahuan', 'Maaf, data gagal diproses!.', 'error');
                        filter();
                    }
                });
            }
        });
        return false;
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
