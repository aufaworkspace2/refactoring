@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="button-list">
                    @if($Create == 'YA')
                        <a href="{{ url('calon_mahasiswa/add') }}" class="btn btn-bordered-primary waves-effect width-md waves-light">
                            <i class="mdi mdi-plus"></i> {{ __('app.add') }} Data
                        </a>
                    @endif
                    
                    <div class="btn-group">
                        <button type="button" class="btn btn-info dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false">
                            <i class="mdi mdi-printer mr-1"></i> Cetak <i class="mdi mdi-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a onclick="excel()" href="javascript:void(0);" class="dropdown-item">
                                <i class="mdi mdi-printer"></i> {{ __('app.excel') }}
                            </a>
                            @if($bayar != 0)
                                <a onclick="excel_referal()" href="javascript:void(0);" class="dropdown-item">
                                    <i class="mdi mdi-printer"></i> {{ __('app.excel') }} Data Referal Agent
                                </a>
                            @endif
                        </div>
                    </div>
                    
                    @if($bayar == 0)
                        <button class="btn btn-bordered-danger waves-effect width-md waves-light" id="btnDelete" data-placement="top" title="Silahkan pilih data terlebih dahulu." data-toggle="modal">
                            <i class="mdi mdi-delete"></i> {{ __('app.delete') }}
                        </button>
                        <a href="{{ url('calon_mahasiswa/add_upload') }}" class="btn btn-bordered-warning waves-light waves-effect">
                            <i class="mdi mdi-upload"></i> Upload Data Calon Mahasiswa Baru Dari Excel
                        </a>
                        
                        @php
                            $attrVerif = 'href="#modal-table-verif" data-toggle="modal"';
                            if(empty($format_pmb)){
                                $attrVerif = 'onclick="swal(\'Pemberitahuan\',\'Format noujian belum di setting\');"';
                            }
                        @endphp
                        
                        <button {!! $attrVerif !!} role="button" type="button" class="btn btn-bordered-success" id="btnVerif">
                            <i class="icon-check"></i>
                            <span>Verifikasi Yang Dipilih</span>
                        </button>

                        <div id="modal-table-verif" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal-table-verif" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" id="modal-table-verif">Konfirmasi</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Anda Yakin Memverifikasi Semua Data Yang Dipilih ?</p>
                                        <p>Sekaligus Set Calon Mahasiswa membayar lunas biaya formulir.</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" onclick="verifikasi_all()" class="btn btn-success waves-effect">YA</button>
                                        <button type="button" class="btn btn-danger waves-effect waves-light" data-dismiss="modal">Tidak</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @if ($bayar == 1)
                        <div id="modal-table-ujian" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal-table-ujian" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" id="modal-table-ujian">Konfirmasi</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Anda Yakin Mengubah Status Ujian Online Semua Data Yang Dipilih ?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" id="btn-ujian" url="" onclick="setujian()" class="btn btn-success waves-effect">YA</button>
                                        <button type="button" class="btn btn-danger waves-effect waves-light" data-dismiss="modal">Tidak</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group col-md-3">
                <label class="col-form-label"><h5 class="mb-0">Gelombang</h5></label>
                <select class="gelombang form-control select2" onchange="change_gelombang_detail_pmb()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @foreach($data_gelombang as $g)
                        <option value="{{ $g->id }}" {{ $selected_gelombang == $g->id ? 'selected' : '' }}>
                            {{ $g->kode }} || {{ $g->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group col-md-3">
                <label class="col-form-label"><h5 class="mb-0">Gelombang Detail</h5></label>
                <select class="gelombang_detail form-control select2" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                </select>
            </div>
            
            <div class="form-group col-md-3">
                <label class="col-form-label"><h5 class="mb-0">Jenis Pendaftar</h5></label>
                <select class="jenis_pendaftaran form-control select2" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @foreach($data_jenis_pendaftaran as $jp)
                        <option value="{{ $jp->Kode ?? '' }}">{{ $jp->Kode ?? '' }} || {{ $jp->Nama ?? '' }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group col-md-3">
                <label class="col-form-label"><h5 class="mb-0">Jalur Pendaftaran</h5></label>
                <select class="jalur_pendaftaran form-control select2" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @foreach($data_jalur as $j)
                        <option value="{{ $j->id ?? '' }}">{{ $j->kode ?? '' }} || {{ $j->nama ?? '' }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group col-md-3">
                <label class="col-form-label"><h5 class="mb-0">Program</h5></label>
                <select class="ProgramID form-control select2" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @foreach($data_program as $p)
                        <option value="{{ $p->ID ?? '' }}">{{ $p->ProgramID ?? '' }} || {{ $p->Nama ?? '' }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group col-md-3">
                <label class="col-form-label"><h5 class="mb-0">Pilihan Pertama</h5></label>
                <select class="pilihan1 form-control select2" onchange="filter()">
                    <option value="">-- {{ __('app.view_all') }} --</option>
                    @foreach($data_programstudi as $ps)
                        <option value="{{ $ps->ID ?? '' }}">{{ $ps->ProdiID ?? '' }} || {{ get_field($ps->JenjangID ?? '', 'jenjang') }} || {{ $ps->Nama ?? '' }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group col-md-3">
                <label class="col-form-label"><h5 class="mb-0">Pilihan Kedua</h5></label>
                <select class="pilihan2 form-control select2" onchange="filter()">
                    <option value="">-- {{ __('app.view_all') }} --</option>
                    @foreach($data_programstudi as $ps)
                        <option value="{{ $ps->ID ?? '' }}">{{ $ps->ProdiID ?? '' }} || {{ get_field($ps->JenjangID ?? '', 'jenjang') }} || {{ $ps->Nama ?? '' }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group col-md-2">
                <label class="col-form-label"><h5 class="mb-0">Status Ujian Online</h5></label>
                <select class="ujian_online_pmb form-control select2" onchange="filter()">
                    <option value="">-- {{ __('app.view_all') }} --</option>
                    <option value="1">Bisa Ujian</option>
                    <option value="2">Tidak Bisa Ujian</option>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group col-md-2">
                <label class="col-form-label"><h5 class="mb-0">Ikut Ujian</h5></label>
                <select class="ikut_ujian_pmb form-control select2" onchange="filter()">
                    <option value="">-- {{ __('app.view_all') }} --</option>
                    <option value="1">Ikut Ujian</option>
                    <option value="2">Tidak Ikut Ujian</option>
                </select>
            </div>
            
            <div class="form-group col-md-10">
                <label class="col-form-label"><h5 class="mb-0">{{ __('app.keyword_legend') }}</h5></label>
                <input type="text" class="form-control keyword" onkeyup="filter()" placeholder="{{ __('app.keyword') }} .." />
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div id="konten"></div>
    </div>
</div>

<input type="hidden" id="offset" value="0">
<input type="hidden" id="bayar" value="{{ $bayar ?? 0 }}">
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

// Define functions FIRST
function change_gelombang_detail_pmb() {
    var selectedGelombang = $('.gelombang').val();
    if (selectedGelombang) {
        filterGelombangDetail(selectedGelombang);
    } else {
        $('.gelombang_detail').html('<option value=""> -- {{ __('app.view_all') }} -- </option>');
        filter();
    }
}

function filterGelombangDetail(gelombang_id) {
    $.ajax({
        url: "{{ url('calon_mahasiswa/get_gelombang_detail') }}",
        type: "GET",
        data: { gelombang_id: gelombang_id },
        success: function(data) {
            $('.gelombang_detail').html('<option value=""> -- {{ __('app.view_all') }} -- </option>' + data);
            $('.gelombang_detail').trigger('change');
            filter();
        }
    });
}

function filter(url) {
    if(url == null) url = "{{ url('calon_mahasiswa/search') }}";
    
    $.ajax({
        type: "POST",
        url: url,
        data: {
            keyword : $(".keyword").val(),
            bayar : $('#bayar').val(),
            gelombang : $(".gelombang").val(),
            gelombang_detail : $(".gelombang_detail").val(),
            jenis_pendaftaran : $(".jenis_pendaftaran").val(),
            jalur_pendaftaran : $(".jalur_pendaftaran").val(),
            pilihan1 : $(".pilihan1").val(),
            pilihan2 : $(".pilihan2").val(),
            program : $(".ProgramID").val(),
            ujian : $(".ujian_online_pmb").val(),
            ikut_ujian : $(".ikut_ujian_pmb").val(),
        },
        beforeSend:function(data){
            $('#konten').html('<center><i class="fa fa-spin fa-spinner"></i> Silahkan Tunggu...</center>');
        },
        success: function(data) {
            $("#konten").html(data);
        }
    });
    return false;
}

function checkall(chkAll,checkid) {
    if (checkid != null) {
        if (checkid.length == null) checkid.checked = chkAll.checked;
        else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;
        $("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
        $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
    }
}

window.show_btnDelete = function(){
    i=0; hasil = false;
    var checkElements = document.getElementsByName('checkID[]');
    while(checkElements.length > i) {
        var checkname = document.getElementById('checkID'+i);
        if(checkname && checkname.checked){ hasil = true; }
        i++;
    }
    if(hasil == true) {
        if($('#btnDelete').length) {
            $('#btnDelete').removeAttr('disabled');
            $('#btnDelete').removeAttr('href');
            $('#btnDelete').removeAttr('title');
            $('#btnDelete').attr('href', '#hapus');
        }
    } else {
        if($('#btnDelete').length) {
            $('#btnDelete').attr('disabled','disabled');
            $('#btnDelete').attr('href','#');
            $('#btnDelete').attr('title','Pilih dahulu data yang akan di hapus');
        }
    }
}
show_btnDelete();

$("input:checkbox[name='checkID[]']").click(function(){
    if(this.checked == true){ $(this).parents('tr').addClass('table-danger'); }
    else { $(this).parents('tr').removeClass('table-danger'); }
});

function verifikasi_all() {
    $.ajax({
        type: "POST",
        url: "{{ url('calon_mahasiswa/verifikasi_all') }}",
        data: {
            checkID: $("input:checkbox[name='checkID[]']:checked").map(function(){
                return this.value;
            }).get()
        },
        success: function(data) {
            if(data.status == 1){
                $("#modal-table-verif").modal("hide");
                filter();
                toastr.success(data.message);
            } else {
                toastr.error(data.message);
            }
        }
    });
}

function setujian() {
    var url = $('#btn-ujian').attr('url');
    $.ajax({
        type: "POST",
        url: url,
        data: {
            checkID: $("input:checkbox[name='checkID[]']:checked").map(function(){
                return this.value;
            }).get()
        },
        success: function(data) {
            if(data == 1){
                $("#modal-table-ujian").modal("hide");
                filter();
                toastr.success("Status ujian berhasil diubah");
            }
        }
    });
}

function excel() {
    var params = [];
    params.push('bayar=' + $('#bayar').val());
    params.push('gelombang=' + $(".gelombang").val());
    params.push('gelombang_detail=' + $(".gelombang_detail").val());
    params.push('jenis_pendaftaran=' + $(".jenis_pendaftaran").val());
    params.push('jalur_pendaftaran=' + $(".jalur_pendaftaran").val());
    params.push('program=' + $(".ProgramID").val());
    params.push('pilihan1=' + $(".pilihan1").val());
    params.push('pilihan2=' + $(".pilihan2").val());
    params.push('ujian=' + $(".ujian_online_pmb").val());
    params.push('ikut_ujian=' + $(".ikut_ujian_pmb").val());
    params.push('keyword=' + $(".keyword").val());
    
    window.location.href = "{{ url('calon_mahasiswa/excel') }}?" + params.join('&');
}

function excel_referal() {
    var params = [];
    params.push('bayar=' + $('#bayar').val());
    params.push('gelombang=' + $(".gelombang").val());
    params.push('gelombang_detail=' + $(".gelombang_detail").val());
    params.push('jenis_pendaftaran=' + $(".jenis_pendaftaran").val());
    params.push('jalur_pendaftaran=' + $(".jalur_pendaftaran").val());
    params.push('program=' + $(".ProgramID").val());
    params.push('pilihan1=' + $(".pilihan1").val());
    params.push('pilihan2=' + $(".pilihan2").val());
    params.push('ujian=' + $(".ujian_online_pmb").val());
    params.push('ikut_ujian=' + $(".ikut_ujian_pmb").val());
    params.push('keyword=' + $(".keyword").val());
    
    window.location.href = "{{ url('calon_mahasiswa/excel_referal') }}?" + params.join('&');
}

// Initialize Select2
$(document).ready(function() {
    $('.select2').select2({
        placeholder: function(){
            $(this).data('placeholder');
        },
        allowClear: true,
        width: '100%'
    });

    filter();
    var selectedGelombang = $('.gelombang').val();
    if (selectedGelombang) {
        filterGelombangDetail(selectedGelombang);
    }
});
</script>
@endpush
