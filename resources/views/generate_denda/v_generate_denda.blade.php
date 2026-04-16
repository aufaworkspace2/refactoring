@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-4">
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
            <div class="form-group col-md-4">
                <label class="col-form-label mt-2"><h4 class="m-0">Program Kuliah</h4></label>
                <select class="programID form-control" id="programID" onchange="filter();">
                    <option value="">-- Semua --</option>
                    @foreach(DB::table('program')->get() as $row)
                        <option value="{{ $row->ID }}">{{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
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

            <div class="form-group col-md-4">
                <label class="col-form-label mt-2"><h4 class="m-0">Tahun Masuk</h4></label>
                <select class="tahunMasuk form-control" id="tahunMasuk" onchange="filter()">
                    <option value="">-- Semua --</option>
                    @foreach(DB::table('mahasiswa')->select('TahunMasuk')->distinct()->orderBy('TahunMasuk', 'DESC')->get() as $row)
                        <option value="{{ $row->TahunMasuk }}">{{ $row->TahunMasuk }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="col-form-label mt-2"><h4 class="m-0">Jalur Pendaftaran</h4></label>
                <select class="jalurPendaftaran form-control" id="jalurPendaftaran" onchange="filter()">
                    <option value="">-- Semua --</option>
                    @foreach(DB::table('pmb_edu_jalur_pendaftaran')->where('aktif', '1')->get() as $raw)
                        <option value="{{ $raw->id }}">{{ $raw->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="col-form-label mt-2"><h4 class="m-0">Jenis Pendaftaran</h4></label>
                <select class="jenisPendaftaran form-control" id="jenisPendaftaran" onchange="filter()">
                    <option value="">-- Semua --</option>
                    @foreach(DB::table('jenis_pendaftaran')->where('Aktif', 'Ya')->get() as $jp)
                        <option value="{{ $jp->Kode }}">{{ $jp->Nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-11">
                <label class="col-form-label mt-2"><h4 class="m-0">Pencarian</h4></label>
                <input type="text" class="form-control keyword" id="keyword" onkeyup="filter()" placeholder="Cari NPM/Nama ..">
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
                    <button class="btn btn-bordered-info waves-effect width-md waves-light" onclick="posting_all(1)" id="btn_posting_all" disabled>Posting Denda Dari Tagihan yang Dipilih</button>&nbsp;
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
        url = "{{ route('generate_denda.search') }}";

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
        text = "Untuk Posting Denda Dari Tagihan yang Dipilih ";
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
                url: "{{ route('generate_denda.posting') }}",
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
                    swal('Pemberitahuan', 'Maaf, data gagal diproses !.', 'error');
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
        text = "Untuk Posting Denda Dari Tagihan yang Dipilih ";
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
                url: "{{ route('generate_denda.postingAll') }}",
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
                    swal('Pemberitahuan', 'Maaf, data gagal diproses !.', 'error');
                    filter();
                }
            });
        }
    });
    return false;
}

function checkall(chkAll, checkid) {
    if (checkid != null) {
        if (checkid.length == null) checkid.checked = chkAll.checked;
        else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;

        $("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
        $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
    }
}
</script>
@endpush
