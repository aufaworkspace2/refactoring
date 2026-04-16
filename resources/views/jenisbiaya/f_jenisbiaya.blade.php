@extends('layouts.template1')

@section('content')
@php
    if(empty($row)) {
        $row = new stdClass();
        $row->ID = '';
        $row->Nama = '';
        $row->frekuensi = '';
        $row->Prodi = 0;
        $row->Program = 0;
        $row->TahunMasuk = 0;
        $row->StatusHide = 0;
        $row->TipeMhsw = 'mhsw';

        $judul = __('app.title_add');
        $slog = __('app.slog_add');
        $btn = __('app.add');
    } else {
        $judul = __('app.title_view');
        $slog = __('app.slog_view') . '<b>' . ($row->Nama ?? '') . '</b>';
        $btn = __('app.edit');
    }
@endphp

<div class="card">
    <div class="card-body">
        <form id="f_jenisbiaya" onsubmit="savedata(this); return false;"
              action="{{ url('jenisbiaya/save/'.$save) }}"
              enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="ID" id="ID" value="{{ $row->ID }}">
            <input type="hidden" data-postdel="true" name="IDx" value="{{ $row->ID }}">
            <h3>{{ __('app.title') }}</h3>
            <div class="form-row mt-3">
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="Urut">{{ __('app.Urut') }} *</label>
                    <div class="controls">
                        <input type="text" required id="Urut" name="Urut" class="form-control" value="{{ $row->Urut }}" />
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="Kode">Kode *</label>
                    <div class="controls">
                        <input type="text" id="Kode" required name="Kode" class="form-control" value="{{ $row->Kode }}" />
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="Nama">{{ __('app.Nama') }} *</label>
                    <div class="controls">
                        <input type="text" id="Nama" required name="Nama" class="form-control" value="{{ $row->Nama }}" />
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <div class="controls">
                        <table class="table table-hover table-bordered tablesorter">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th width="20px">#</th>
                                    <th>Nama Sub Biaya</th>
                                </tr>
                            </thead>
                            <tbody id="f_sub_biaya">
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" style="border-right:0px;">
                                        <button type="button" onclick="tambah_baru()" class="btn btn-success">
                                            <i class="mdi mdi-plus"></i> Tambah Sub Biaya
                                        </button>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="frekuensi">{{ __('app.frekuensi') }} *</label>
                    <div class="controls">
                        @php
                            $arr = ["Per Semester","Satu Kali","Variable"];
                        @endphp
                        <select class="form-control frekuensi" required name="frekuensi">
                            @foreach($arr as $item)
                                <option value="{{ $item }}" {{ ($item == ($row->frekuensi ?? '')) ? 'selected' : '' }}>
                                    {{ $item }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <label class="col-form-label">Program Kuliah *</label>
                    <div class="row">
                        @php
                            $program_arr = get_all('program');
                            $program_sel = ($row->Program != NULL && $row->Program != 0) ? explode(',', $row->Program) : [];
                            $count_program = count($program_sel);
                        @endphp
                        <div class="form-group col-md-12">
                            <label class="radio-inline">
                                <input required type="radio" class="pilihradio mr-1" name="PilihProgram" value="0" {{ ($count_program == 0) ? 'checked' : '' }}>Semua
                            </label>
                            <label class="radio-inline">
                                <input type="radio" class="pilihradio mr-1" name="PilihProgram" value="1" {{ ($count_program > 0) ? 'checked' : '' }}>Spesifik
                            </label>
                        </div>
                        <div class="form-group col-md-12" id="select_program" {{ ($count_program == 0) ? 'style="display:none;"' : '' }}>
                            <select class="Program multi_select2 form-control" name="Program[]" multiple="multiple">
                                @foreach($program_arr as $data)
                                    <option value="{{ $data->ID }}" {{ in_array($data->ID, $program_sel) ? 'selected' : '' }}>
                                        {{ $data->ProgramID }}  ||  {{ $data->Nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <label class="col-form-label">Program Studi *</label>
                    <div class="row">
                        @php
                            $prodi_arr = get_all('programstudi');
                            $prodi_sel = ($row->Prodi != NULL && $row->Prodi != 0) ? explode(',', $row->Prodi) : [];
                            $count_prodi = count($prodi_sel);
                        @endphp
                        <div class="form-group col-md-12">
                            <label class="radio-inline">
                                <input required type="radio" class="pilihradio mr-1" name="PilihProdi" value="0" {{ ($count_prodi == 0) ? 'checked' : '' }}>Semua
                            </label>
                            <label class="radio-inline">
                                <input type="radio" class="pilihradio mr-1" name="PilihProdi" value="1" {{ ($count_prodi > 0) ? 'checked' : '' }}>Spesifik
                            </label>
                        </div>
                        <div id="select_prodi" class="form-group col-md-12" {{ ($count_prodi == 0) ? 'style="display:none;"' : '' }}>
                            <select class="Prodi multi_select2 form-control" name="Prodi[]" multiple="multiple">
                                @foreach($prodi_arr as $data)
                                    <option value="{{ $data->ID }}" {{ in_array($data->ID, $prodi_sel) ? 'selected' : '' }}>
                                        {{ $data->Nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <label class="col-form-label">Tahun Masuk *</label>
                    <div class="row">
                        @php
                            $tahun_sel = ($row->TahunMasuk != NULL && $row->TahunMasuk != 0) ? explode(',', $row->TahunMasuk) : [];
                            $count_tahun = count($tahun_sel);
                        @endphp
                        <div class="form-group col-md-12">
                            <label class="radio-inline">
                                <input required type="radio" class="pilihradio mr-1" name="PilihTahunMasuk" value="0" {{ ($count_tahun == 0) ? 'checked' : '' }}>Semua
                            </label>
                            <label class="radio-inline">
                                <input type="radio" class="pilihradio mr-1" name="PilihTahunMasuk" value="1" {{ ($count_tahun > 0) ? 'checked' : '' }}>Spesifik
                            </label>
                        </div>
                        <div id="select_tahunmasuk" class="form-group col-md-12" {{ ($count_tahun == 0) ? 'style="display:none;"' : '' }}>
                            <select class="TahunMasuk multi_select2 form-control" name="TahunMasuk[]" multiple="multiple">
                                @for($n = 0; $n <= 8; $n++)
                                    <option {{ in_array((date("Y") - $n), $tahun_sel) ? 'selected' : '' }}>
                                        {{ date("Y") - $n }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="col-form-label">
                        Hide ? * <br>
                        (di Modul Generate Tagihan Komponen Biaya Ini Tidak Dimunculkan.)
                    </label>
                    <div class="controls">
                        <span class="span2">
                            <label class="radio-inline">
                                <input required type="radio" class="pilihradio mr-1" name="StatusHide" value="0" {{ ($row->StatusHide == 0) ? 'checked' : '' }}>Tidak
                            </label>
                            <label class="radio-inline">
                                <input type="radio" class="pilihradio mr-1" name="StatusHide" value="1" {{ ($row->StatusHide == 1) ? 'checked' : '' }}>Ya
                            </label>
                        </span>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">
                {{ __('app.save') }} Data
            </button>
            <button type="button" onClick="back()" class="btn btn-bordered-danger waves-effect width-md waves-light">
                {{ __('app.back') }}
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$(".multi_select2").select2({
    placeholder: "-- Pilih --",
    allowClear: true,
});

$('[name=PilihProdi]').change(function() {
    if (this.value == 0) {
        $('#select_prodi').hide();
        $('.multi_select2').trigger('change');
    } else if (this.value == 1) {
        $('#select_prodi').show();
        $('.multi_select2').trigger('change');
    } else {
        $('#select_prodi').hide();
        $('.multi_select2').trigger('change');
    }
});

$('[name=PilihProgram]').change(function() {
    if (this.value == 0) {
        $('#select_program').hide();
        $('.multi_select2').trigger('change');
    } else if (this.value == 1) {
        $('#select_program').show();
        $('.multi_select2').trigger('change');
    }
});

$('[name=PilihTahunMasuk]').change(function() {
    if (this.value == 0) {
        $('#select_tahunmasuk').hide();
        $('.multi_select2').trigger('change');
    } else if (this.value == 1) {
        $('#select_tahunmasuk').show();
        $('.multi_select2').trigger('change');
    }
});

function tambah_baru() {
    var tampil = '<tr>';
    tampil += '<td class="align-middle"><a href="javascript:void(0)" class="cursor-pointer hapus-jenis-detail" data-toggle="tooltip" data-placement="top" title="hapus sub biaya"><span class="mdi mdi-delete"></span></a></td>';
    tampil += '<td>';
    tampil += '<input type="text" class="NamaSubBiaya form-control" name="NamaSubBiaya[]" required value="">';
    tampil += '</td>';
    tampil += '</tr>';

    $('#f_sub_biaya').append(tampil);
    $("[data-toggle='tooltip']").tooltip();
}

$(document).on("click", ".hapus-jenis-detail", function() {
    let $this = $(this);

    swal({
        title: "Konfirmasi Hapus Data",
        text: "Apakah anda yakin akan menghapus data yang di pilih ?",
        type: "question",
        showCancelButton: true
    }).then(function(data) {
        if (typeof $this.attr("data-key") !== 'undefined') {
            var url = '{{ url("jenisbiaya/jenisbiaya_detail_delete") }}';
            $.ajax({
                type: "POST",
                url: url,
                data: {
                    IDx: $("[name='IDx']").val(),
                    ID: $this.attr('data-key'),
                    _token: "{{ csrf_token() }}"
                },
                success: function(data) {
                    $this.parents("tr:first").remove();
                }
            });
        } else {
            $this.parents("tr:first").remove();
        }
    });
});

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
            console.log(data);
            if(data == 'gagal') {
                alertfail();
                berhasil();
            } else {
                if({{ $save }} == '1') {
                    window.location = "{{ url('jenisbiaya') }}";
                }

                if({{ $save }} == '2') {
                    load_content('{{ url("jenisbiaya/view/".$row->ID) }}');
                }
                berhasil();
                alertsuccess();
            }
        },
        error: function(data) {
            $(".btnSave").html('{{ __("app.save") }} Data <icon class="icon-check icon-white-t"></icon>');
            $(".btnSave").removeAttr("disabled");
            $(".alert-error").show();
            $(".alert-error-content").html("{{ __('app.alert-error') }}");
            window.setTimeout(function() { $(".alert-error").slideUp("slow"); }, 6000);
        }
    });
}

@if($save == 2)
load_list_komponen({{ $row->ID }});
@endif

function load_list_komponen(ID) {
    $.ajax({
        type: "POST",
        url: "{{ url('jenisbiaya/load_list_komponen') }}",
        dataType: 'json',
        data: {
            JenisBiayaID: ID,
            _token: "{{ csrf_token() }}"
        },
        beforeSend: function() {
            $('#f_sub_biaya').html('<i class="fa fa-spinner fa-spin"></i>');
        },
        success: function(data) {
            list_komponen(data);
        }
    });
    return false;
}

function list_komponen(data_json) {
    $('#f_sub_biaya').empty();
    if(data_json) {
        $.each(data_json, function(i, data) {
            var tampil = '<tr>';
            tampil += '<td class="align-middle"><a href="javascript:void(0)" class="cursor-pointer hapus-jenis-detail" data-toggle="tooltip" data-placement="top" title="hapus sub biaya" data-key=' + data.ID + '><span class="mdi mdi-delete"></span></a></td>';
            tampil += '<td>';
            tampil += '<input type="hidden" name="SubJenisBiayaID[]" value="' + data.ID + '">';
            tampil += '<input type="text" class="NamaSubBiaya form-control" required name="NamaSubBiaya[]" value="' + data.Nama + '">';
            tampil += '</td>';
            tampil += '</tr>';
            $('#f_sub_biaya').append(tampil);
        });
        $("[data-toggle='tooltip']").tooltip();
    }
}
</script>
@endpush
