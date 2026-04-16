@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Input Draft Tagihan Mahasiswa</h3>
    </div>
    <div class="card-body">
        <form id="form" action="{{ url('generate_tagihan/generate_tagihan') }}" enctype="multipart/form-data">
            @csrf
            <div class="row-fluid">
                <div class="span12">
                    <div class="tab-pane active" id="tab-details">
                        <div class="form-row mt-3">
                            <div class="form-group col-md-12">
                                <label class="col-form-label" for="PeriodeID">Tahun Semester *</label>
                                <div class="controls">
                                    <select name="PeriodeID" id="PeriodeID" class="form-control TahunID" onchange="filter();changeMahasiswa();" required>
                                        @php
                                            $tahun_list = DB::table('tahun')->orderBy('ID', 'DESC')->get();
                                        @endphp
                                        @foreach($tahun_list as $r)
                                            @php
                                                $aktif = ($r->ProsesBuka == 1) ? " (Aktif)" : "";
                                                $select = ($r->ProsesBuka == 1) ? " selected" : "";
                                            @endphp
                                            <option value="{{ $r->ID }}" {{ $select }}>
                                                {{ $r->Nama }} &nbsp;{{ $aktif }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-md-12">
                                <label class="col-form-label" for="ProgramID">Program *</label>
                                <div class="controls">
                                    <select name="ProgramID" class="form-control ProgramID" onchange="filter();changeMahasiswa();" required>
                                        @foreach(DB::table('program')->get() as $r)
                                            <option value="{{ $r->ID }}">{{ $r->Nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-md-12">
                                <label class="col-form-label" for="ProdiID">Programstudi *</label>
                                <div class="controls">
                                    <select name="ProdiID" class="form-control ProdiID" onchange="filter();changekonsentrasi();" required>
                                        @php
                                            $nama_jenjang = [];
                                            foreach(DB::table('programstudi')->get() as $r) {
                                                if(!isset($nama_jenjang[$r->JenjangID])) {
                                                    $nama_jenjang[$r->JenjangID] = get_field($r->JenjangID, 'jenjang');
                                                }
                                        @endphp
                                            <option value="{{ $r->ID }}">{{ $nama_jenjang[$r->JenjangID] }} || {{ $r->Nama }}</option>
                                        @php } @endphp
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-md-12">
                                <label class="col-form-label" for="Angkatan">Angkatan *</label>
                                <div class="controls">
                                    <select name="Angkatan" class="form-control Angkatan" onchange="filter();changeMahasiswa();" required>
                                        <option>{{ date("Y"); }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-md-12">
                                <label class="col-form-label">Jalur Pendaftaran *</label>
                                <div class="controls">
                                    <select id="JalurPendaftaran" name="JalurPendaftaran" class="JalurPendaftaran form-control" onchange="filter();changeMahasiswa();" required>
                                        <option value="">-- Pilih Semua Jalur Pendaftaran --</option>
                                        @foreach(DB::table('pmb_edu_jalur_pendaftaran')->where('aktif', '1')->get() as $raw)
                                            <option value="{{ $raw->id }}">{{ $raw->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-md-12">
                                <label class="col-form-label">Jenis Pendaftaran *</label>
                                <div class="controls">
                                    <select id="JenisPendaftaran" name="JenisPendaftaran" class="JenisPendaftaran form-control" onchange="filter();changeMahasiswa();" required>
                                        <option value="">-- Pilih Semua Jenis Pendaftaran --</option>
                                        @foreach(DB::table('jenis_pendaftaran')->where('Aktif', 'ya')->get() as $raw)
                                            <option value="{{ $raw->Kode }}">{{ $raw->Nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-md-12">
                                <label class="col-form-label mt-2"><h5 class="m-0">Semester Masuk *</h5></label>
                                <div class="controls">
                                    <select id="SemesterMasuk" name="SemesterMasuk" class="form-control SemesterMasuk" onchange="filter();changeMahasiswa();" required>
                                        <option value="">-- Pilih Semua Semester Masuk --</option>
                                        @foreach(DB::table('semester_masuk')->get() as $raw)
                                            <option value="{{ $raw->SemesterMasuk }}">{{ $raw->Nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-md-12">
                                <label class="col-form-label mt-2"><h5 class="m-0">Gelombang Ke *</h5></label>
                                <div class="controls">
                                    <select id="GelombangKe" name="GelombangKe" class="form-control GelombangKe" onchange="filter();changeMahasiswa();">
                                        <option value="">-- Pilih Semua Gelombang --</option>
                                        @foreach(DB::table('gelombang_ke')->get() as $raw)
                                            <option value="{{ $raw->GelombangKe }}">{{ $raw->Nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <p class="ml-1" id="jumlah_mahasiswa"></p>

                            <div class="form-group col-md-12">
                                <label class="col-form-label" for="Angkatan">Input Draft Untuk ? </label>
                                <div class="controls">
                                    <select id="tipe" name="tipe" class="form-control" onchange="showHide();changeMahasiswa()">
                                        <option value="1">Semua Mahasiswa</option>
                                        <option value="2">Per Mahasiswa</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-md-12" id="contentMahasiswa" style="display:none;">
                                <label class="col-form-label" for="Nama">Mahasiswa</label>
                                <div class="controls">
                                    <select id="mhswID" name="mhswID[]" class="span4" style='width:49%;' multiple></select>
                                </div>
                            </div>

                            <div class="form-group col-md-12">
                                <label class="col-form-label" for="TanggalTagihan">Tanggal Draft</label>
                                <div class="controls">
                                    <input type="date" value="{{ date('Y-m-d') }}" name="TanggalTagihan" class="TanggalTagihan form-control" id="TanggalTagihan" required>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-bordered tablesorter">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <td>Jenis Biaya</td>
                                            <td>Total</td>
                                            <td width="40%" style="display:none;">Beasiswa</td>
                                            <td style="display:none;">Dikalikan SKS</td>
                                        </tr>
                                    </thead>
                                    <tbody id="isi">
                                    </tbody>
                                </table>
                            </div>

                            <div class="form-group col-md-12">
                                <label class="col-form-label">Total</label>
                                <div class="controls">
                                    <input type="text" class="currency form-control col-md-4 col-sm-12" readonly id="total_tagihan" name="total_tagihan">
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-bordered-success waves-effect width-md waves-light btn_generate" type="submit" id="buttonGenerate2">
                            <i class="mdi mdi-refresh icon-white-t"></i> &nbsp; Input Draft
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card" id="hide-table" style="display:none;">
    <div class="card-header">
        <h4>Data Draft Tagihan</h4>
    </div>
    <div class="col-md-12 card-body">
        <a href="javascript:void(0);" onclick="excel()" class="btn btn-outline-success mb-1">
            <i class="icon-download-alt"></i> Download Excel Draft Tagihan
        </a>
        <div class="table-responsive">
            <table width="100%" class="table table-hover table-bordered table-responsive block tablesorter">
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
                <tbody id="isi_tabel">
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for mahasiswa tidak aktif -->
<div class="hide modalLarge fade in mdl_aing" id="mdl_tidak_aktif">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4><span class="modal_header" id="hm">Daftar Mahasiswa Pernah Tidak Aktif</span></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body" style="overflow-x:hidden;max-height: 300px !important;">
                <div class="table-responsive">
                    <table width="100%" class="table table-hover table-bordered table-responsive block tablesorter">
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
                <a href="javascript:void(0);" class="btn btn-danger float-right" data-dismiss="modal">
                    <i class="mdi mdi-close"></i> {{ __('app.close') }}
                </a>
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

function excel() {
    window.open('{{ url("generate_tagihan/excel") }}', "_Blank");
}

$("#form").submit(function(e) {
    e.preventDefault();
    $('.currency').unmask();
    var formData = new FormData(this);

    $.ajax({
        type: 'POST',
        url: $(this).attr('action'),
        data: formData,
        dataType: "json",
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function(xhr) {
            $("#buttonGenerate2").attr('disabled', true);
            $("#buttonGenerate2").html('<i class="fa fa-spinner fa-spin"></i> Silahkan Tunggu');
        },
        success: function(data) {
            $("#buttonGenerate2").removeAttr('disabled');
            $("#buttonGenerate2").html('<i class="mdi mdi-refresh"></i> Input Draft');
            $('.currency').mask('#.##0', {reverse: true});
            $('.currency').trigger('input');

            if(data.status == '1') {
                alert(data.message.replace('||', '\n'));

                var tampil = '';
                $("#hide-table").show();

                var no = 1;
                if (data.jumlah_peserta == 0) {
                    tampil += '<tr>';
                    tampil += '<td colspan="6" class="text-center">Mahasiswa Sudah Tertagihkan Sebelumnya</td>';
                    tampil += '</tr>';
                } else {
                    for (i = 1; i <= data.jumlah_peserta; i++) {
                        tampil += '<tr>';
                        tampil += '<td style=color:black>' + no + '</td>';
                        tampil += '<td style=color:black>' + (data.NoInvoice[i] || '') + '</td>';
                        tampil += '<td style=color:black>' + (data.NPM[i] || '') + '</td>';
                        tampil += '<td style=color:black>' + (data.Nama[i] || '') + '</td>';
                        tampil += '<td style=color:black>' + (data.TotalTagihan[i] || '') + '</td>';
                        tampil += '<td style=color:black>' + (data.TglTransaksi[i] || '') + '</td>';
                        tampil += '</tr>';
                        no++;
                    }
                }

                $("#isi_tabel").html(tampil);
            } else {
                alert(data.message);
            }
        },
        error: function(xhr, status, error) {
            $("#buttonGenerate2").removeAttr('disabled');
            $("#buttonGenerate2").html('<i class="mdi mdi-refresh"></i> Input Draft');
            alert('Tagihan Gagal di Generate, terjadi ada kesalahan pada sistem. ' + xhr.responseText);
        }
    });
});

function changekonsentrasi() {
    $.ajax({
        url: "{{ url('detailkurikulum/changekonsentrasi') }}",
        type: "POST",
        data: { ProdiID: $(".ProdiID").val(), _token: "{{ csrf_token() }}" },
        success: function(data) {
            $(".KonsentrasiID").html(data);
            changeMahasiswa();
        }
    });
}

function changeMahasiswa() {
    $.ajax({
        type: "POST",
        url: "{{ url('generate_tagihan/searchMahasiswa') }}",
        data: {
            ProgramID: $(".ProgramID").val(),
            TahunID: $(".TahunID").val(),
            ProdiID: $(".ProdiID").val(),
            Angkatan: $(".Angkatan").val(),
            JenisPendaftaran: $(".JenisPendaftaran").val(),
            JalurPendaftaran: $(".JalurPendaftaran").val(),
            GelombangKe: $(".GelombangKe").val(),
            SemesterMasuk: $(".SemesterMasuk").val(),
            KonsentrasiID: $(".KonsentrasiID").val(),
            _token: "{{ csrf_token() }}"
        },
        success: function(data) {
            $('#jumlah_mahasiswa').html("Jumlah Mahasiswa berdasarkan filter : <b>" + data.jumlah + "</b>");
            $("#mhswID").html(data.temp);
            autocomplete('mhswID','--Pilih Data--')
            if(data.jumlah == 0) {
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
        url: "{{ url('generate_tagihan/changeAngkatan') }}",
        data: { test: 'test', _token: "{{ csrf_token() }}" },
        success: function(data) {
            $(".Angkatan").empty();
            $(".Angkatan").html(data);
            $(".Angkatan").trigger('change');
        }
    });
    return false;
}

changeAngkatan();
changeMahasiswa();
showHide();

function showHide() {
    var tipe = $('#tipe').val();
    if (tipe == 1) {
        $('#contentMahasiswa').css('display', 'none');
    } else {
        $('#contentMahasiswa').css('display', '');
    }
}

function filter() {
    $.ajax({
        type: 'POST',
        url: '{{ url("generate_tagihan/content_biaya") }}',
        data: {
            ProgramID: $('.ProgramID').val(),
            ProdiID: $('.ProdiID').val(),
            TahunID: $(".TahunID").val(),
            Angkatan: $('.Angkatan').val(),
            JenisPendaftaran: $(".JenisPendaftaran").val(),
            JalurPendaftaran: $(".JalurPendaftaran").val(),
            GelombangKe: $(".GelombangKe").val(),
            SemesterMasuk: $(".SemesterMasuk").val(),
            KonsentrasiID: $(".KonsentrasiID").val(),
            _token: "{{ csrf_token() }}"
        },
        success: function(data) {
            $('#isi').html(data);
        }
    });
}

filter();
</script>
@endpush
