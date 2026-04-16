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
        <form id="f_diskon_edit" onsubmit="savedata_edit(this); return false;">
            <input type="hidden" name="MhswDiskonID" value="{{ $row->MhswDiskonID ?? '' }}">
            <h3>{{ $judul }}</h3>
            
            <div class="form-row mt-3">
                <div class="form-group col-md-6">
                    <label class="col-form-label">Mahasiswa</label>
                    <input type="text" class="form-control" value="{{ $row_data->Nama ?? '' }}" readonly>
                </div>
                
                <div class="form-group col-md-6">
                    <label class="col-form-label">No. Ujian</label>
                    <input type="text" class="form-control" value="{{ $row_data->noujian_pmb ?? '' }}" readonly>
                </div>
                
                <div class="form-group col-md-12">
                    <label class="col-form-label">Jenis Biaya</label>
                    <select name="JenisBiayaID" class="form-control" required>
                        <option value="">-- Pilih --</option>
                        @foreach($master_diskon as $md)
                            <option value="{{ $md['ID'] }}" {{ ($row->JenisBiayaID ?? '') == $md['ID'] ? 'selected' : '' }}>
                                {{ $md['Nama'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group col-md-12">
                    <label class="col-form-label">Master Diskon</label>
                    <select name="MasterDiskonID" class="form-control" required>
                        <option value="">-- Pilih --</option>
                        @foreach($master_diskon as $md)
                            <option value="{{ $md['ID'] }}" {{ ($row->MasterDiskonID ?? '') == $md['ID'] ? 'selected' : '' }}>
                                {{ $md['Nama'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group col-md-12">
                    <label class="col-form-label">Nominal (Rp)</label>
                    <input type="number" name="Nominal" class="form-control" value="{{ $row->Nominal ?? 0 }}" required>
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

function savedata_edit(formz) {
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
