@extends('layouts.template1')

@section('content')
@php
if($save == 1) {
    $judul = 'Tambah Diskon Mahasiswa';
} else {
    $judul = 'Edit Diskon Mahasiswa';
}
@endphp

<div class="card">
    <div class="card-body">
        <form id="f_diskon" onsubmit="savedata(this); return false;">
            <input type="hidden" name="TahunID" id="TahunID" value="{{ request('TahunID') }}">
            <h3>{{ $judul }}</h3>
            
            <div class="form-row mt-3">
                <div class="form-group col-md-12">
                    <label class="col-form-label">Filter Mahasiswa</label>
                    <div id="filter-container">
                        <!-- Filter will be loaded here via AJAX -->
                    </div>
                </div>
                
                <div class="form-group col-md-12">
                    <label class="col-form-label">Daftar Mahasiswa</label>
                    <div id="mahasiswa-container">
                        <!-- Mahasiswa list will be loaded here via AJAX -->
                    </div>
                </div>
                
                <div class="form-group col-md-12">
                    <label class="col-form-label">Pilih Jenis Biaya & Diskon</label>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Jenis Biaya</th>
                                <th>Master Diskon</th>
                                <th>Nominal (Rp)</th>
                            </tr>
                        </thead>
                        <tbody id="jenisbiaya-container">
                            <!-- Jenis biaya will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">
                {{ __('app.save') }} Data
            </button>
            <button type="button" onClick="back()" class="btn btn-bordered-danger waves-effect  width-md waves-light">
                {{ __('app.back') }}
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

$(document).ready(function() {
    loadFilter();
});

function loadFilter() {
    $.ajax({
        type: "POST",
        url: "{{ url('mahasiswa_diskon_pmb/filtermhs') }}",
        data: {
            TahunID: $('#TahunID').val(),
            ProdiID: $('.ProdiID').val(),
            ProgramID: $('.ProgramID').val(),
        },
        success: function(data) {
            $('#filter-container').html(data);
        }
    });
}

function loadMahasiswa() {
    $.ajax({
        type: "POST",
        url: "{{ url('mahasiswa_diskon_pmb/filtermhs') }}",
        data: $("#f_filter").serialize(),
        success: function(data) {
            $('#mahasiswa-container').html(data);
        }
    });
}

function loadJenisBiaya() {
    $.ajax({
        type: "POST",
        url: "{{ url('mahasiswa_diskon_pmb/filtermhs') }}",
        data: $("#f_filter").serialize(),
        success: function(data) {
            $('#jenisbiaya-container').html(data);
        }
    });
}

function changenominal(PemberiDiskonID, row) {
    $.ajax({
        type: "POST",
        url: "{{ url('mahasiswa_diskon_pmb/changenominal') }}",
        data: {
            PemberiDiskonID: PemberiDiskonID
        },
        success: function(data) {
            $(row).closest('tr').find('.nominal-input').val(data.Nominal);
            $(row).closest('tr').find('.DiscountID').val(data.DiscountID);
        }
    });
}

function savedata(formz) {
    var formData = new FormData(formz);
    $.ajax({
        type:'POST',
        url: "{{ url('mahasiswa_diskon_pmb/save') }}",
        data: formData,
        cache:false,
        contentType: false,
        processData: false,
        beforeSend: function(r){
            silahkantunggu();
        },
        success:function(data){
            if(data.status == 1){
                berhasil();
                alertsuccess();
                window.location = "{{ url('mahasiswa_diskon_pmb') }}";
            } else {
                alert(data.message);
                berhasil();
            }
        },
        error: function(data){
            $(".btnSave").html("{{ __('app.save') }} Data");
            $(".btnSave").removeAttr("disabled");
            alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.');
        }
    });
}
</script>
@endpush
