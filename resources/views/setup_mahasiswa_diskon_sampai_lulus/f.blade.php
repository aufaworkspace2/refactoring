@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <h3>Form Beasiswa Mahasiswa</h3>
        <div class="alert alert-info">
            <strong><i class="fa fa-question-circle"></i> </strong> Form Ini Untuk Men Set Mahasiswa Berhak Diskon / Beasiswa Sesuai Periode Yang Ditentukan
        </div>
        <form id="f_transkdiskon" onsubmit="savedata(this); return false;"
              action="{{ url('setup_mahasiswa_diskon_sampai_lulus/save/'.$save) }}"
              enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="ID" id="ID" value="{{ ($row->ID) ?? '' }}" />
            <div class="form-row mt-3">
                <div class="form-group col-md-12">
                    <label class="col-form-label">Set Per Tahun Akademik ? *</label>
                    <div class="controls">
                        <input type="radio" onchange="showHide(1)" class="tahunSemester" id="PerTahunID_1" name="PerTahunID" value="1"> Ya <br />
                        <input type="radio" onchange="showHide(0)" class="tahunSemester2" id="PerTahunID_0" name="PerTahunID" value="0"> Tidak
                    </div>
                </div>
                <div class="form-group col-md-12" id="contentTahun">
                    <label class="col-form-label">Tahun Akademik *</label>
                    <div class="controls">
                        <select class="form-control ListTahunID" name="ListTahunID[]" id="ListTahunID" multiple>
                            @foreach(DB::table('tahun')->orderBy('TahunID', 'DESC')->get() as $raw)
                                @php
                                    $aktif = ($raw->ProsesBuka == 1) ? '(Aktif)' : '';
                                @endphp
                                <option value="{{ $raw->ID }}">{{ $raw->Nama }} {{ $aktif }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="col-form-label">Pilihan Diskon *</label>
                    <div class="controls">
                        <div class="table-responsive" id="dataPilihan">
                            <table class="table table-bordered table-hovered">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>Komponen Biaya</th>
                                        <th style="width: 40%;">Jenis Diskon</th>
                                        <th style="width: 15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyPilihan">
                                    <tr id="item_0">
                                        <td>
                                            <select class="form-control" name="JenisBiayaID[0]" required>
                                                <option value=""> -- Pilih --</option>
                                                @foreach(get_all('jenisbiaya') as $row_jenisbiaya)
                                                    <option value="{{ $row_jenisbiaya->ID }}">{{ $row_jenisbiaya->Nama }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-control ListMasterDiskonID" name="ListMasterDiskonID[0][]" multiple required>
                                                @foreach($master_diskon as $rowx)
                                                    @php
                                                        $hrg = ($rowx->Tipe == 'nominal') ? rupiah($rowx->Jumlah) : $rowx->Jumlah . ' %';
                                                        $view = $rowx->Nama;
                                                        $prodi = $rowx->prodi;
                                                    @endphp
                                                    <option value="{{ $rowx->ID }}"> {{ $prodi }} -- {{ $view }} {{ $hrg }} </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="center">
                                            <button type="button" class="btn btn-bordered-danger waves-effect waves-light btn-block btn-pilihan" onClick="deleteRow(0);">
                                                <i class="mdi mdi-delete"></i> Hapus
                                            </button>
                                        </td>
                                    </tr>
                                    <input type="hidden" id="totalPilihan" value="1" />
                                </tbody>
                                <tfoot id="actionPilihan">
                                    <tr>
                                        <td colspan="2">Tambah</td>
                                        <td class="center">
                                            <button type="button" class="btn btn-bordered-success waves-effect waves-light btn-block" onclick="addPilihan();">
                                                <i class="mdi mdi-plus"></i> Tambah
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="col-form-label">Set Per Mahasiswa ? *</label>
                    <div class="controls">
                        <input type="radio" onchange="isPerMhsx(1)" class="permahasiswa1" id="isPerMhs_1" name="isPerMhs" value="1"> Ya <br />
                        <input type="radio" onchange="isPerMhsx(0)" class="permahasiswa2" id="isPerMhs_0" name="isPerMhs" value="0"> Tidak
                    </div>
                </div>
                <div class="form-group col-md-12" id="parm_prodi" style="display: none;">
                    <label class="col-form-label">Program Studi *</label>
                    <div class="controls">
                        <select class="form-control ProdiID" id="ProdiID" name="ProdiID" onchange="filter();">
                            <option value="">-- Pilih Program Studi --</option>
                            @foreach(DB::table('programstudi')->get() as $row)
                                <option value="{{ $row->ID }}">{{ $row->Nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-12" id="parm_prog" style="display: none;">
                    <label class="col-form-label">Program *</label>
                    <div class="controls">
                        <select class="form-control ProgramID" id="ProgramID" name="ProgramID" onchange="filter();">
                            <option value="">-- Pilih Program --</option>
                            @foreach(DB::table('program')->get() as $row)
                                <option value="{{ $row->ID }}">{{ $row->Nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-12" id="parm_angkatan" style="display: none;">
                    <label class="col-form-label">Tahun Masuk *</label>
                    <div class="controls">
                        <select id="TahunMasuk" name="TahunMasuk" onchange="filter();" class="form-control" multiple>
                            @foreach(DB::table('mahasiswa')->select('TahunMasuk')->where('TahunMasuk', '!=', '')->groupBy('TahunMasuk')->get() as $row)
                                <option value="{{ $row->TahunMasuk }}">{{ $row->TahunMasuk }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-12" id="mhs">
                    <label class="col-form-label">Pilih Mahasiswa *</label>
                    <div class="controls">
                        <select class="key form-control" id="key" name="npm">
                            <option value="" selected="selected">Masukan NPM / Nama Mhs</option>
                        </select>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">
                {{ __('app.save') }} Data
            </button>
            <button type="button" onClick="back()" class="btn btn-bordered-danger waves-effect width-md waves-light">
                {{ __('app.back') }}
            </button>
            <div id="konten"></div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
    // Initialize Select2
    if (typeof $.fn.select2 !== 'undefined') {
        $("#ListTahunID").select2({ placeholder: "Pilih Tahun Akademik", allowClear: true });
        $("#ProdiID").select2({ placeholder: "Pilih Program Studi", allowClear: true });
        $("#TahunMasuk").select2({ placeholder: "Pilih Tahun Masuk", allowClear: true });
        autocompletebyclass("ListMasterDiskonID");

        // Student search Select2
        $('#key').select2({
            ajax: {
                url: '{{ url("c_mahasiswa/json_mahasiswa") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term,
                        page: params.page,
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page * 30) < data.total_count
                        }
                    };
                },
                cache: true
            }
        });
    }

    // Set default radio buttons for add mode
    @if($save == 1)
        $(".permahasiswa1").prop("checked", true);
        $(".tahunSemester").prop("checked", true);
    @else
        $(".permahasiswa2").prop("checked", true);
        $(".tahunSemester2").prop("checked", true);
    @endif
});

// Master diskon data for JavaScript
var master_diskon_data = @json($master_diskon);
var jenisbiaya_data = @json(get_all('jenisbiaya'));

function rupiah(angka) {
    return 'Rp ' + parseInt(angka).toLocaleString('id-ID');
}

function addPilihan() {
    var nomor = parseInt($('#totalPilihan').val()) + 1;
    $('#totalPilihan').val(nomor);
    var temp = '';

    temp = '<tr id="item_' + nomor + '">';
    temp += '<td>';
    temp += "<select name='JenisBiayaID[" + nomor + "]' class='form-control' required>";
    temp += "<option value=''>-- Pilih --</option>";
    $.each(jenisbiaya_data, function(key_jb, value_jb) {
        temp += "<option value='" + value_jb.ID + "'>" + value_jb.Nama + "</option>";
    });
    temp += "</select>";
    temp += '</td>';
    temp += '<td>';
    temp += "<select class='form-control ListMasterDiskonID' name='ListMasterDiskonID[" + nomor + "][]' multiple required>";
    temp += "<option value=''>-- Pilih --</option>";
    $.each(master_diskon_data, function(key_diskon, value_diskon) {
        var hrg = (value_diskon['Tipe'] == 'nominal') ? rupiah(value_diskon['Jumlah']) : value_diskon['Jumlah'] + ' %';
        temp += "<option value='" + value_diskon['ID'] + "'>" + value_diskon['prodi'] + " -- " + value_diskon['Nama'] + " " + hrg + "</option>";
    });
    temp += "</select>";
    temp += '</td>';
    temp += '<td class="center">';
    temp += '<button type="button" class="btn btn-bordered-danger waves-effect waves-light btn-block btn-pilihan" onClick="deleteRow(' + nomor + ');"><i class="mdi mdi-delete"></i> Hapus</button>';
    temp += '</td>';
    temp += '</tr>';

    $('#bodyPilihan').append(temp);

    if (typeof $.fn.select2 !== 'undefined') {
        autocompletebyclass("ListMasterDiskonID");
    }
}

function deleteRow(id) {
    swal({
        title: "Apakah anda yakin ?",
        text: "Anda akan menghapus item komponen biaya dan diskon.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Ya",
        cancelButtonText: "Batal"
    }).then(function() {
        $('#item_' + id).remove();
    });
}

function showHide(param) {
    if (param == 1) {
        $("#contentTahun").show();
        $("#ListTahunID").prop("required", true);
    } else if (param == 0) {
        $("#contentTahun").hide();
        $("#ListTahunID").prop("required", false);
        $("#ListTahunID").val('').trigger("change");
    }
}

function isPerMhsx(param) {
    if (param == 1) {
        $("#mhs").show();
        $("#parm_angkatan").hide();
        $("#parm_prog").hide();
        $("#parm_prodi").hide();
        $("#konten").html("");
    } else if (param == 0) {
        $("#mhs").hide();
        $("#parm_angkatan").show();
        $("#parm_prog").show();
        $("#parm_prodi").show();
    }
}

function filter(url) {
    if (url == null)
        url = "{{ url('setup_mahasiswa_diskon_sampai_lulus/filtermhs') }}";

    $.ajax({
        type: "POST",
        url: url,
        data: {
            TahunMasuk: $("#TahunMasuk").val(),
            ProgramID: $(".ProgramID").val(),
            ProdiID: $(".ProdiID").val(),
            KelasID: $(".KelasID").val(),
            _token: "{{ csrf_token() }}"
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
            if (data == 'gagal') {
                swal('Pemberitahuan', 'Maaf, Mahasiswa ini Sudah ada Diskon Aktif', 'warning');
                berhasil();
            } else if (data == 'tahun_kosong') {
                swal('Pemberitahuan', 'Maaf, Harap untuk memilih Tahun Akademik', 'warning');
                berhasil();
            } else {
                if ({{ $save }} == '1') {
                    window.location = "{{ url('setup_mahasiswa_diskon_sampai_lulus') }}";
                }
                if ({{ $save }} == '2') {
                    window.location = "{{ url('setup_mahasiswa_diskon_sampai_lulus') }}";
                }
                berhasil();
                alertsuccess();
            }
        },
        error: function(data) {
            $(".btnSave").html('{{ __("app.save") }} Data');
            $(".btnSave").removeAttr("disabled");
            $(".alert-error").show();
            $(".alert-error-content").html("{{ __('app.alert-error') }}");
            window.setTimeout(function() { $(".alert-error").slideUp("slow"); }, 6000);
        }
    });
}
</script>
@endpush
