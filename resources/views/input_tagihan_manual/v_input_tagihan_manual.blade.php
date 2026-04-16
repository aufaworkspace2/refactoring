@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Input Tagihan Lainnya</h3>
    </div>
    <div class="card-body">
        <form id="form" action="{{ route('input_tagihan_manual.inputTagihanManual') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row-fluid">
                <div class="span12">
                    <div class="tab-pane active" id="tab-details">
                        <div class="form-row mt-3">
                            <input id="MhswID" type="hidden" value="{{ $MhswID ?? '' }}">
                            <input id="TahunMasukMhsw" type="hidden" value="{{ $TahunMasuk ?? '' }}">

                            <div class="form-group col-md-12">
                                <label class="col-form-label" for="PeriodeID">Tahun Semester *</label>
                                <select name="PeriodeID" id="PeriodeID" class="form-control TahunID" onchange="filter(); changeMahasiswa();" required>
                                    @foreach(DB::table('tahun')->orderBy('ID', 'DESC')->get() as $r)
                                        <option value="{{ $r->ID }}" {{ $r->ProsesBuka == 1 ? 'selected' : '' }}>
                                            {{ $r->Nama }} {{ $r->ProsesBuka == 1 ? '(Aktif)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-12">
                                <label class="col-form-label" for="ProgramID">Program *</label>
                                <select name="ProgramID" class="form-control ProgramID" onchange="changeKelas();" required>
                                    @foreach(DB::table('program')->get() as $r)
                                        <option value="{{ $r->ID }}">{{ $r->Nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-12">
                                <label class="col-form-label" for="ProdiID">Programstudi *</label>
                                <select name="ProdiID" class="form-control ProdiID" onchange="changeKelas();" required>
                                    @foreach(DB::table('programstudi')->get() as $r)
                                        <option value="{{ $r->ID }}">
                                            {{ DB::table('jenjang')->where('ID', $r->JenjangID)->value('Nama') ?? '' }} || {{ $r->Nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-12">
                                <label class="col-form-label" for="Angkatan">Angkatan *</label>
                                <select name="Angkatan" class="form-control Angkatan" onchange="filter(); changeMahasiswa();" required>
                                    <option>{{ date('Y') }}</option>
                                </select>
                            </div>

                            <div class="form-group col-md-12">
                                <label class="col-form-label">Jalur Pendaftaran *</label>
                                <select id="JalurPendaftaran" name="JalurPendaftaran" class="JalurPendaftaran form-control" onchange="filter(); changeMahasiswa();" required>
                                    @foreach(DB::table('pmb_edu_jalur_pendaftaran')->where('aktif', '1')->get() as $raw)
                                        <option value="{{ $raw->id }}">{{ $raw->nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-12">
                                <label class="col-form-label">Jenis Pendaftaran *</label>
                                <select id="JenisPendaftaran" name="JenisPendaftaran" class="JenisPendaftaran form-control" onchange="filter(); changeMahasiswa();" required>
                                    @foreach(DB::table('jenis_pendaftaran')->where('Aktif', 'ya')->get() as $raw)
                                        <option value="{{ $raw->Kode }}">{{ $raw->Nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-12">
                                <label class="col-form-label"><h5 class="m-0">Semester Masuk *</h5></label>
                                <select id="SemesterMasuk" name="SemesterMasuk" class="form-control SemesterMasuk" onchange="filter(); changeMahasiswa();" required>
                                    @foreach(DB::table('semester_masuk')->get() as $raw)
                                        <option value="{{ $raw->SemesterMasuk }}">{{ $raw->Nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <p class="ml-1" id="jumlah_mahasiswa"></p>

                            <div class="form-group col-md-12">
                                <label class="col-form-label">Input Tagihan Untuk ?</label>
                                <select id="tipe" name="tipe" class="form-control" onchange="showHide(); changeMahasiswa()">
                                    <option value="1">Semua Mahasiswa</option>
                                    <option value="2">Per Mahasiswa</option>
                                </select>
                            </div>

                            <div class="form-group col-md-12" id="contentMahasiswa" style="display: none;">
                                <label class="col-form-label">Mahasiswa</label>
                                <select id="mhswID" name="mhswID[]" class="span4" style="width: 49%;" multiple></select>
                            </div>

                            <div class="form-group col-md-12">
                                <label class="col-form-label">Tanggal Draft</label>
                                <input type="date" value="{{ date('Y-m-d') }}" name="TanggalTagihan" class="TanggalTagihan form-control" id="TanggalTagihan" required>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-bordered tablesorter">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <td>Jenis Biaya</td>
                                            <td>Total</td>
                                        </tr>
                                    </thead>
                                    <tbody id="isi"></tbody>
                                </table>
                            </div>

                            <div class="form-group col-md-12">
                                <label class="col-form-label">Total</label>
                                <input type="text" class="currency form-control col-md-4 col-sm-12" readonly id="total_tagihan" name="total_tagihan">
                            </div>
                        </div>

                        <button class="btn btn-bordered-success waves-effect width-md waves-light btn_generate" type="submit" id="buttonGenerate2">
                            <i class="mdi mdi-refresh"></i> &nbsp; Input Tagihan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card" id="hide-table" style="display: none;">
    <div class="card-header">
        <h4>Data Draft Tagihan</h4>
    </div>
    <div class="col-md-12 card-body">
        <a href="javascript:void(0);" onclick="excel()" class="btn btn-outline-success mb-1">
            <i class="icon-download-alt"></i> Download Excel Draft Tagihan
        </a>
        <div class="table-responsive">
            <table width="100%" class="table table-hover table-bordered tablesorter">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="text-center" width="2%">No.</th>
                        <th class="text-center">No Invoice</th>
                        <th class="text-center">NPM</th>
                        <th class="text-center">Nama</th>
                        <th class="text-center">Total Tagihan</th>
                        <th class="text-center">Tanggal Transaksi</th>
                    </tr>
                </thead>
                <tbody id="isi_tabel"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Mahasiswa Tidak Aktif -->
<div class="hide modal fade in" id="mdl_tidak_aktif">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Daftar Mahasiswa Pernah Tidak Aktif</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body" style="overflow-x: hidden; max-height: 300px !important;">
                <div class="table-responsive">
                    <table width="100%" class="table table-hover table-bordered">
                        <thead class="bg-primary text-white">
                            <tr>
                                <td class="text-center" style="width: 3%">No.</td>
                                <td class="text-center" style="width: 10%">NPM</td>
                                <td class="text-center" style="width: 20%">Nama</td>
                            </tr>
                        </thead>
                        <tbody id="list_tidak_aktif"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <a href="javascript:void(0);" class="btn btn-danger float-right" data-dismiss="modal"><i class="mdi mdi-close"></i> Tutup</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

showHide();
changeMahasiswa();
changeAngkatan();
changeKelas();

function excel() {
    window.open("{{ route('input_tagihan_manual.excel') }}", "_Blank");
}

$("#form").submit(function(e){
    e.preventDefault();

    var form = $(this);
    $('.currency').unmask();

    $.ajax({
        type: 'POST',
        url: form.attr('action'),
        data: form.serialize(),
        dataType: "json",
        beforeSend: function() {
            $("#buttonGenerate2").attr('disabled', true);
            $("#buttonGenerate2").html('<i class="fa fa-spinner fa-spin"></i> Silahkan Tunggu');
        },
        success: function(data) {
            $("#buttonGenerate2").removeAttr('disabled');
            $("#buttonGenerate2").html('<i class="mdi mdi-refresh"></i> Input Tagihan');
            $('.currency').mask('#.##0', {reverse: true});
            $('.currency').trigger('input');

            if(data.status == '1') {
                swal('Pemberitahuan', data.message.replace('||', '\n'));

                var tampil = '';
                $("#hide-table").show();

                var no = 1;
                if (data.jumlah_peserta == 0) {
                    tampil += '<tr><td colspan="6" class="text-center">Mahasiswa Sudah Tertagihkan Sebelumnya</td></tr>';
                } else {
                    for (i = 1; i <= data.jumlah_peserta; i++) {
                        tampil += '<tr>';
                        tampil += '<td>' + no + '</td>';
                        tampil += '<td>' + (data.NoInvoice[i] || '') + '</td>';
                        tampil += '<td>' + (data.NPM[i] || '') + '</td>';
                        tampil += '<td>' + (data.Nama[i] || '') + '</td>';
                        tampil += '<td>' + (data.TotalTagihan[i] || '') + '</td>';
                        tampil += '<td>' + (data.TglTransaksi[i] || '') + '</td>';
                        tampil += '</tr>';
                        no++;
                    }
                }

                $("#isi_tabel").html(tampil);
            } else {
                swal('Pemberitahuan', data.message, 'error');
            }
        },
        error: function(xhr, status, error) {
            $("#buttonGenerate2").removeAttr('disabled');
            $("#buttonGenerate2").html('<i class="mdi mdi-refresh"></i> Input Tagihan');
            swal('Pemberitahuan', 'Tagihan Gagal di Generate, terjadi kesalahan pada sistem.', 'error');
        }
    });
});

function changeMahasiswa() {
    $.ajax({
        type: "POST",
        url: "{{ route('input_tagihan_manual.searchMahasiswa') }}",
        data: {
            ProgramID: $(".ProgramID").val(),
            TahunID: $(".TahunID").val(),
            ProdiID: $(".ProdiID").val(),
            Angkatan: $(".Angkatan").val(),
            JenisPendaftaran: $(".JenisPendaftaran").val(),
            JalurPendaftaran: $(".JalurPendaftaran").val(),
            SemesterMasuk: $(".SemesterMasuk").val()
        },
        success: function(data) {
            $('#jumlah_mahasiswa').html("Jumlah Mahasiswa berdasarkan filter: <b>" + data.jumlah + "</b>");
            $("#mhswID").html(data.temp);
            autocomplete('mhswID','Pilih Data')
            if(data.jumlah == 0){
                $('#buttonGenerate2').attr('disabled', 'disabled');
            } else {
                $('#buttonGenerate2').removeAttr('disabled');
            }
        }
    });
    return false;
}

function changeAngkatan() {
    $.ajax({
        type: "POST",
        url: "{{ route('input_tagihan_manual.changeAngkatan') }}",
        data: { test: 'test' },
        success: function(data) {
            $(".Angkatan").empty();
            $(".Angkatan").html(data);
        }
    });
    return false;
}

function showHide() {
    var tipe = $('#tipe').val();
    if (tipe == 1) {
        $('#mhswID').removeAttr('required');
        $('#contentMahasiswa').css('display', 'none');
    } else {
        $('#mhswID').attr('required', 'true');
        $('#contentMahasiswa').css('display', '');
    }
}

function filter() {
    $.ajax({
        type: 'POST',
        url: "{{ route('input_tagihan_manual.contentBiaya') }}",
        data: {
            ProgramID: $('.ProgramID').val(),
            ProdiID: $('.ProdiID').val(),
            TahunID: $(".TahunID").val(),
            Angkatan: $('.Angkatan').val(),
            JenisPendaftaran: $(".JenisPendaftaran").val(),
            JalurPendaftaran: $(".JalurPendaftaran").val(),
            SemesterMasuk: $(".SemesterMasuk").val()
        },
        success: function(data) {
            $('#isi').html(data);
        }
    });
}
filter();

function changeKelas() {
    filter();
    changeMahasiswa();  
}
</script>
@endpush
