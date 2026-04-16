@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="button-list">
                    <a href="javascript:void(0);" onclick="excel()" class="btn btn-success"><i class="icon-file"></i> Cetak Excel</a>
                    <button href="#modal-table-approve" onclick="$('#btn-approve').attr('data-url','{{ route('lihat_catatan_krs_tidak_aktif.approve', 1) }}');
                    $('#div_catatan').hide();$('#CatatanTambahan').val('');" data-toggle="modal" role="button" type="button" class="btn btn-warning btnApprove" disabled>
                        <i class="icon-refresh"></i>
                        <span>Set Status KRS Ya</span>
                    </button>
                    <button href="#modal-table-approve" onclick="$('#btn-approve').attr('data-url','{{ route('lihat_catatan_krs_tidak_aktif.approve', 0) }}');
                    $('#div_catatan').show();$('#CatatanTambahan').val('');" data-toggle="modal" role="button" type="button" class="btn btn-danger btnApprove" disabled>
                        <i class="icon-refresh"></i>
                        <span>Set Status KRS Tidak</span>
                    </button>

                    <div id="modal-table-approve" class="modal fade" tabindex="-1" style="display: none;" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Konfirmasi</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <div class="row" id="div_catatan">
                                        <div class="form-group col-md-12">
                                            <label class="col-form-label" for="CatatanTambahan">Catatan Admin *</label>
                                            <div class="controls">
                                                <textarea rows="5" cols="10" class="form-control" name="CatatanTambahan" id="CatatanTambahan" placeholder=""></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        Anda Yakin Mengubah Status Pengajuan Semua Data Yang Dipilih ?
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-small btn-success" type="button" id="btn-approve" data-url='' onclick="approve()">
                                        <i class="icon-ok"></i> Ya
                                    </button>
                                    <button class="btn btn-small btn-danger" type="button" data-dismiss="modal">
                                        <i class="icon-remove"></i> Tidak
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label class="col-form-label"><h5 class="mb-0">Tahun Akademik</h5></label>
                <select class="TahunID form-control" onchange="filter()">
                    @foreach ($dataTahun as $riw)
                        <option value="{{ $riw->ID }}" {{ $riw->ProsesBuka == 1 ? 'selected' : '' }}>
                            {{ $riw->Nama }} {{ $riw->ProsesBuka == 1 ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label class="col-form-label"><h5 class="mb-0">Program</h5></label>
                <select class="ProgramID form-control" onchange="filter()">
                    <option value=""> -- Semua -- </option>
                    @foreach(DB::table('program')->get() as $row)
                        <option value="{{ $row->ID }}">{{ $row->ProgramID }} || {{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label class="col-form-label"><h5 class="mb-0">Program Studi</h5></label>
                <select class="ProdiID form-control" onchange="filter();">
                    <option value=""> -- Pilih Semua -- </option>
                    @foreach(DB::table('programstudi')->get() as $row)
                        <option value="{{ $row->ID }}">
                            {{ $row->ProdiID }} || {{ DB::table('jenjang')->where('ID', $row->JenjangID)->value('Nama') ?? '' }} || {{ $row->Nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label class="col-form-label"><h5 class="mb-0">Angkatan</h5></label>
                <select class="TahunMasuk form-control" onchange="filter()" name="TahunMasuk">
                    <option value="">-- Semua Tahun --</option>
                    @foreach($angkatan as $thn)
                        <option value="{{ $thn->TahunMasuk }}">{{ $thn->TahunMasuk }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label class="col-form-label"><h5 class="mb-0">Status Dapat KRS</h5></label>
                <select class="SetKRSYa form-control" onchange="filter()" name="SetKRSYa">
                    <option value="">-- Semua Status --</option>
                    <option value="1">Ya</option>
                    <option value="0">Tidak</option>
                </select>
            </div>

            <div class="form-group col-md-3">
                <label class="col-form-label"><h5 class="mb-0">Dari Tanggal Buat</h5></label>
                <input type='date' id='Tgl1' class="form-control" value="{{ date('Y-m-d') }}" onchange="filter()">
            </div>

            <div class="form-group col-md-3">
                <label class="col-form-label"><h5 class="mb-0">Sampai Tanggal Buat</h5></label>
                <input type='date' id='Tgl2' class="form-control" value="{{ date('Y-m-d') }}" onchange="filter()">
            </div>

            <div class="form-group col-md-3">
                <label class="col-form-label"><h5 class="mb-0">Pencarian</h5></label>
                <input type='text' id='keyword' class="keyword form-control" onkeyup="filter()">
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div id="konten"></div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

filter();

function filter(url) {
    if(url == null)
        url = "{{ route('lihat_catatan_krs_tidak_aktif.search') }}";

    $.ajax({
        type: "POST",
        url: url,
        beforeSend: function() {
            $('#konten').html('<center><i class="fa fa-spin fa-spinner"></i> Silahkan Tunggu...</center>');
        },
        data: {
            TahunMasuk: $(".TahunMasuk").val(),
            TahunID: $(".TahunID").val(),
            ProgramID: $(".ProgramID").val(),
            ProdiID: $(".ProdiID").val(),
            SetKRSYa: $(".SetKRSYa").val(),
            Tgl1: $("#Tgl1").val(),
            Tgl2: $("#Tgl2").val(),
            keyword: $(".keyword").val()
        },
        success: function(data) {
            $("#konten").html(data);
        }
    });
    return false;
}

function checkall(chkAll, checkid) {
    if (checkid != null) {
        if (checkid.length == null) checkid.checked = chkAll.checked;
        else for (i = 0; i < checkid.length; i++) checkid[i].checked = chkAll.checked;

        $("input:checkbox[name='checkID[]']").parents('tr').removeClass('checked_tabel');
        $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('checked_tabel');
    }
}

function show_btnApprove(){
    var i = 0;
    var hasil = false;
    while(document.getElementsByName('checkID[]').length > i) {
        var checkname = document.getElementById('checkID' + i).checked;
        if(checkname == true) {
            hasil = true;
        }
        i++;
    }
    if(hasil == true) {
        $('.btnApprove').removeAttr('disabled');
        $('.btnApprove').removeAttr('title');
    } else {
        $('.btnApprove').attr('disabled','disabled');
        $('.btnApprove').attr('title', 'Pilih dahulu data yang akan di diubah');
    }
}
show_btnApprove();

function approve() {
    var url = $('#btn-approve').attr('data-url');
    var CatatanTambahan = '';

    if(url == "{{ route('lihat_catatan_krs_tidak_aktif.approve', 0) }}") {
        CatatanTambahan = $('#CatatanTambahan').val();
        if(!CatatanTambahan) {
            swal('Info', 'Catatan harus diisi!', 'warning');
            return;
        }
    }

    $.ajax({
        url: url,
        type: "POST",
        data: $("input:checkbox[name='checkID[]']:checked").serialize() + '&CatatanTambahan=' + encodeURIComponent(CatatanTambahan),
        success: function(data) {
            if(data.status == 1) {
                $('#modal-table-approve').modal('hide');
                filter();
                swal('Berhasil', "Status Pengajuan Berhasil diubah", 'success');
            } else {
                $('#modal-table-approve').modal('hide');
                filter();
                swal('Error', data.message || 'Terjadi kesalahan', 'error');
            }
        },
        error: function(data) {
            $('#modal-table-approve').modal('hide');
            filter();
            swal('Error', 'Karena Kesalahan Sistem', 'error');
        }
    });
}

function excel() {
    var TahunMasuk = $(".TahunMasuk").val();
    var TahunID = $(".TahunID").val();
    var ProgramID = $(".ProgramID").val();
    var ProdiID = $(".ProdiID").val();
    var SetKRSYa = $(".SetKRSYa").val();
    var Tgl1 = $("#Tgl1").val();
    var Tgl2 = $("#Tgl2").val();
    var keyword = $(".keyword").val();

    var link = 'TahunMasuk=' + TahunMasuk
             + '&TahunID=' + TahunID
             + '&ProgramID=' + ProgramID
             + '&ProdiID=' + ProdiID
             + '&SetKRSYa=' + SetKRSYa
             + '&Tgl1=' + Tgl1
             + '&Tgl2=' + Tgl2
             + '&keyword=' + keyword;

    window.open("{{ route('lihat_catatan_krs_tidak_aktif.excel') }}?" + link, "_Blank");
}
</script>
@endpush
