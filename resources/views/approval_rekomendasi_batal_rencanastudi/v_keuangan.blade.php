@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="col-form-label mt-2"><h4 class="m-0">Tahun Semester</h4></label>
                <select class="tahunID form-control" id="tahunID" onchange="filter();">
                    <option value=""> -- Lihat Semua -- </option>
                    @foreach(DB::table('tahun')->orderBy('TahunID', 'DESC')->get() as $row)
                        <option value="{{ $row->ID }}" {{ $row->ProsesBuka == 1 ? 'selected' : '' }}>
                            {{ $row->Nama }} {{ $row->ProsesBuka == 1 ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="col-form-label mt-2"><h4 class="m-0">Program Kuliah</h4></label>
                <select class="programID form-control" id="programID" onchange="changeKurikulum(); filter();">
                    <option value=""> -- Lihat Semua -- </option>
                    @foreach(DB::table('program')->get() as $row)
                        <option value="{{ $row->ID }}">{{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="col-form-label mt-2"><h4 class="m-0">Program Studi</h4></label>
                <select class="prodiID form-control" id="prodiID" onchange="changeKurikulum(); filter();">
                    <option value="">-- Semua --</option>
                    @foreach(DB::table('programstudi')->get() as $row)
                        <option value="{{ $row->ID }}">
                            {{ $row->ProdiID }} || {{ DB::table('jenjang')->where('ID', $row->JenjangID)->value('Nama') ?? '' }} || {{ $row->Nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="col-form-label mt-2"><h4 class="m-0">Kurikulum</h4></label>
                <select class="kurikulumID form-control" id="kurikulumID" onchange="filter()">
                    <option value=""> -- Lihat Semua -- </option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="col-form-label mt-2"><h4 class="m-0">Tahun Masuk</h4></label>
                <select class="tahunMasuk form-control" id="tahunMasuk" onchange="filter()">
                    <option value=""> -- Lihat Semua -- </option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="col-form-label mt-2"><h4 class="m-0">Status Pembatalan</h4></label>
                <select class="statusPembatalan form-control" id="statusPembatalan" onchange="filter()">
                    <option value=""> -- Lihat Semua -- </option>
                    <option value="1">Sudah</option>
                    <option value="0">Belum</option>
                </select>
            </div>
            <div class="form-group col-md-12">
                <label class="col-form-label mt-2"><h4 class="m-0">Kata Kunci</h4></label>
                <input type="text" class="form-control keyword" id="keyword" onkeyup="filter()" placeholder="Cari NPM/Nama ..">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="button-list">
                    <button class="btn btn-bordered-info waves-effect width-md waves-light" onclick="rekomendasi_keuangan_all(1)" id="btn_rekomendasi_keuangan_all" disabled>Setujui Semua Rekomendasi</button>&nbsp;
                    <button class="btn btn-bordered-warning waves-effect width-md waves-light" onclick="rekomendasi_keuangan_all(2)" id="btn_notrekomendasi_keuangan_all" disabled>Tidak Setujui Semua Rekomendasi</button>&nbsp;
                    <button class="btn btn-bordered-danger waves-effect width-md waves-light" onclick="rekomendasi_keuangan_all(0)" id="btn_unrekomendasi_keuangan_all" disabled>Batalkan Semua Rekomendasi</button>&nbsp;
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

<!-- Modal Lihat Nilai -->
<div id="lihatNilai" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="lihatNilai" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Detail Nilai</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body" id="bodyLihatNilai"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary waves-effect waves-light" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

changeKurikulum();
changeTahunMasuk();
filter();

function changeKurikulum()
{
    $.ajax({
        type: "POST",
        url: "{{ route('approval_rekomendasi_batal_rencanastudi_keuangan.changeKurikulum') }}",
        data: {
            programID: $("#programID").val(),
            prodiID: $("#prodiID").val()
        },
        success: function(data) {
            $("#kurikulumID").html(data);
        }
    });
    return false;
}

function changeTahunMasuk()
{
    $.ajax({
        type: "POST",
        url: "{{ route('approval_rekomendasi_batal_rencanastudi_keuangan.changeTahunMasuk') }}",
        data: {
            programID: $("#programID").val(),
            prodiID: $("#prodiID").val()
        },
        success: function(data) {
            $("#tahunMasuk").html(data);
        }
    });
    return false;
}

function filter(url)
{
    if(url == null)
        url = "{{ route('approval_rekomendasi_batal_rencanastudi_keuangan.search') }}";

    $.ajax({
        type: "POST",
        url: url,
        beforeSend: function() {
            $('#konten').html('<center><i class="fa fa-spin fa-spinner"></i> Silahkan Tunggu...</center>');
        },
        data: {
            programID: $("#programID").val(),
            prodiID: $("#prodiID").val(),
            kurikulumID: $("#kurikulumID").val(),
            tahunMasuk: $("#tahunMasuk").val(),
            statusPembatalan: $("#statusPembatalan").val(),
            tahunID: $("#tahunID").val(),
            keyword: $("#keyword").val()
        },
        success: function(data) {
            $("#konten").html(data);
        }
    });
    return false;
}

function rekomendasi_keuangan(id, rekomendasi_keuangan)
{
    var text;
    if(rekomendasi_keuangan == 1){
        text = "Untuk Menyetujui Rekomendasi Pembatalan KRS ";
    }else if(rekomendasi_keuangan == 2){
        text = "Untuk Tidak Menyetujui Rekomendasi Pembatalan KRS ";
    }else if(rekomendasi_keuangan == 0){
        text = "Untuk Membatalkan Persetujuan Rekomendasi Pembatalan KRS ";
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
            run_rekomendasi_keuangan(id, rekomendasi_keuangan);
        }
    });
    return false;
}

function run_rekomendasi_keuangan(id, rekomendasi_keuangan)
{
    $.ajax({
        type: "POST",
        dataType: 'JSON',
        url: "{{ route('approval_rekomendasi_batal_rencanastudi_keuangan.rekomendasiKeuangan') }}",
        beforeSend: function() {
            $('#konten').html('<center><i class="fa fa-spin fa-spinner"></i> Silahkan Tunggu...</center>');
        },
        data: {
            id: id,
            rekomendasi_keuangan: rekomendasi_keuangan
        },
        success: function(data) {
            if (data.status == 1) {
                alertsuccess(data.message);
                filter();
            } else {
                swal('Pemberitahuan', data.message, 'error');
            }
        },
        error: function (data) {
            swal('Pemberitahuan', 'Maaf, data gagal diproses!.', 'error');
        }
    });
}

function rekomendasi_keuangan_all(rekomendasi_keuangan)
{
    var selected = [];
    $('input:checkbox[name="checkID[]"]:checked').each(function() {
        selected.push($(this).val());
    });

    var text;
    if(rekomendasi_keuangan == 1){
        text = "Untuk Menyetujui Rekomendasi Pembatalan KRS yang dipilih";
    }else if(rekomendasi_keuangan == 2){
        text = "Untuk Tidak Menyetujui Rekomendasi Pembatalan KRS yang dipilih";
    }else if(rekomendasi_keuangan == 0){
        text = "Untuk Membatalkan Persetujuan Rekomendasi Pembatalan KRS yang dipilih";
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
                url: "{{ route('approval_rekomendasi_batal_rencanastudi_keuangan.rekomendasiKeuanganAll') }}",
                beforeSend: function() {
                    $('#konten').html('<center><i class="fa fa-spin fa-spinner"></i> Silahkan Tunggu...</center>');
                },
                data: {
                    selected: selected,
                    rekomendasi_keuangan: rekomendasi_keuangan
                },
                success: function(data) {
                    if (data.status == 1) {
                        alertsuccess(data.message);
                        filter();
                    } else {
                        swal('Pemberitahuan', data.message, 'error');
                    }
                },
                error: function (data) {
                    swal('Pemberitahuan', 'Maaf, data gagal diproses!.', 'error');
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

function show_btnDelete(){
    i=0; hasil = false;
    while(document.getElementsByName('checkID[]').length > i) {
        var checkname = document.getElementById('checkID'+i).checked;
        if(checkname == true) {
            hasil = true;
        }
        i++;
    }
    if(hasil == true) {
        $('#btn_rekomendasi_keuangan_all').removeAttr('disabled');
        $('#btn_rekomendasi_keuangan_all').removeAttr('title');
        $('#btn_notrekomendasi_keuangan_all').removeAttr('disabled');
        $('#btn_notrekomendasi_keuangan_all').removeAttr('title');
        $('#btn_unrekomendasi_keuangan_all').removeAttr('disabled');
        $('#btn_unrekomendasi_keuangan_all').removeAttr('title');
    } else {
        $('#btn_rekomendasi_keuangan_all').attr('disabled','disabled');
        $('#btn_rekomendasi_keuangan_all').attr('title', 'Pilih dahulu data yang akan di setujui semua');
        $('#btn_notrekomendasi_keuangan_all').attr('disabled','disabled');
        $('#btn_notrekomendasi_keuangan_all').attr('title', 'Pilih dahulu data yang akan di tidak setujui semua');
        $('#btn_unrekomendasi_keuangan_all').attr('disabled','disabled');
        $('#btn_unrekomendasi_keuangan_all').attr('title', 'Pilih dahulu data yang akan di batalkan semua');
    }
}
show_btnDelete();

$("input:checkbox[name='checkID[]']").click(function(){
    if(this.checked == true){
        $(this).parents('tr').addClass('table-danger');
    } else {
        $(this).parents('tr').removeClass('table-danger');
    }
});

function lihatNilai(rencanastudi){
    $("#bodyLihatNilai").html(`<div class="text-center"><i class="fa fa-spin fa-spinner"></i> Sedang Mengambil Data</div>`);

    $.ajax({
        url: "{{ route('approval_rekomendasi_batal_rencanastudi_keuangan.getDataNilai') }}?rencanastudi=" + rencanastudi,
        dataType: "JSON",
        success: function(data){
            var bodymodal = ``;
            if(data.status){
                var listdatanilai = '';
                $.each(data.data, function(ind, val){
                    listdatanilai += `
                    <tr>
                        <td><strong>${val.MKKode}</strong><br>${val.NamaMataKuliah}</td>
                        <td class="text-center align-middle">${val.NilaiAkhir}</td>
                        <td class="text-center align-middle">${val.NilaiHuruf}</td>
                    </tr>
                    `;
                });

                bodymodal = `
                <table class="table table-condensed table-striped">
                    <thead>
                        <tr>
                            <th>Mata Kuliah</th>
                            <th class="text-center">Nilai Akhir</th>
                            <th class="text-center">Nilai Huruf</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${listdatanilai}
                    </tbody>
                </table>
                `;
            }else{
                bodymodal = `<div class="text-center"><i class="fa fa-exclamation"></i> ${data.message}.</div>`;
            }

            $("#bodyLihatNilai").html(bodymodal);
            $('#lihatNilai').modal("show");
        },
        error: function(){
            $("#bodyLihatNilai").html(`<div class="text-center"><i class="fa fa-exclamation"></i> Data Gagal Diambil.</div>`);
        }
    });
}
</script>
@endpush
