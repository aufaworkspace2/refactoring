@extends('layouts.template1')

@section('content')
<style type="text/css">
    .select2-container.select2-container-disabled .select2-choice {
        background-color: #ddd;
        border-color: #a8a8a8;
    }

    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type=number] {
        -moz-appearance:textfield;
    }
</style>

@php
if(empty($row)) { 
    $row = new stdClass(); 
    $row->id = ''; 
    $row->gelombang = ''; 
    $row->kode = ''; 
    $row->ruang = []; 
    $row->jenis_ujin = []; 
    $row->tgl_ujian = ''; 
    $row->jam_mulai = ''; 
    $row->jam_selesai = '';
    $btn = __('app.add'); 
} else { 
    $row = (object) $row; 
    $btn = __('app.edit');
    $ruang = !empty($row->ruang) ? explode(',', $row->ruang) : [];
    $jenis_ujin = !empty($row->jenis_ujin) ? explode(',', $row->jenis_ujin) : [];
}
@endphp

<div class="card">
    <div class="card-body">
    <form id="f_jadwal_usm" autocomplete="off" onsubmit="savedata(this); return false;" action="{{ url('jadwal_usm_pmb/save/' . ($save ?? 1)) }}" enctype="multipart/form-data">
        <input type="hidden" name="id" id="id" value="{{ $row->id ?? '' }}">
            <h3>Jadwal USM PMB</h3>
            <div class="form-row mt-3">
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="gelombang">Gelombang *</label>
                    <div class="controls">
                        <select name="gelombang" required id="gelombang" class="gelombang form-control select2" {{ $save == 1 ? 'onchange="change_jenisusm()"' : '' }}>
                            <option value="">-- Pilih --</option>
                            @foreach($data_gelombang as $g)
                                <option value="{{ $g->id }}" {{ ($row->gelombang == $g->id) ? 'selected' : '' }}>{{ $g->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label class="col-form-label" for="kode">Kode *</label>
                    <div class="controls">
                        <input type="text" class="form-control" name="kode" required value="{{ $row->kode ?? '' }}">
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label class="col-form-label" for="ruang">Ruangan</label>
                    <div class="controls">
                        <select name="ruang[]" class="ruang form-control select2" multiple>
                            <option value="">-- Pilih --</option>
                            @foreach($data_ruangan as $r)
                                <option value="{{ $r->ID }}" {{ (in_array($r->ID, $ruang ?? [])) ? 'selected' : '' }}>{{ $r->Nama }}</option>
                            @endforeach
                        </select>
                        <small class="text-info">Hold Ctrl/Cmd untuk pilih multiple</small>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label class="col-form-label" for="jenis_ujin">Jenis Ujian *</label>
                    <div class="controls">
                        <select name="jenis_ujin[]" class="jenis_ujin form-control select2" multiple required>
                            <option value="">-- Pilih --</option>
                            @foreach($data_jenis_usm as $ju)
                                <option value="{{ $ju->id }}" {{ (in_array($ju->id, $jenis_ujin ?? [])) ? 'selected' : '' }}>{{ $ju->nama }}</option>
                            @endforeach
                        </select>
                        <small class="text-info">Hold Ctrl/Cmd untuk pilih multiple</small>
                    </div>
                </div>

                <div class="form-group col-md-6">
                    <label class="col-form-label" for="tgl_ujian">Tanggal Ujian *</label>
                    <input type="date" id="tgl_ujian" required name="tgl_ujian" class="form-control" value="{{ $row->tgl_ujian ?? '' }}" />
                </div>
                <div class="form-group col-md-3">
                    <label class="col-form-label" for="jam_mulai">Jam Mulai *</label>
                    <input type="time" id="jam_mulai" required name="jam_mulai" class="form-control" value="{{ $row->jam_mulai ?? '' }}" />
                </div>
                <div class="form-group col-md-3">
                    <label class="col-form-label" for="jam_selesai">Jam Selesai *</label>
                    <input type="time" id="jam_selesai" required name="jam_selesai" class="form-control" value="{{ $row->jam_selesai ?? '' }}" />
                </div>
            </div>
            <button onClick="btnEdit({{ $save ?? 1 }},1)" type="button" class="btn btn-bordered-success waves-effect width-md waves-light btnEdit">{{ $btn }} Data</button>
            <button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">{{ __('app.save') }} Data</button>
            <button type="button" onClick="back()" class="btn btn-bordered-danger waves-effect width-md waves-light">{{ __('app.back') }}</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

// Initialize Select2
$(document).ready(function() {
    $('.select2').select2({
        placeholder: function(){
            $(this).data('placeholder');
        },
        allowClear: true
    });
    
    // Set default placeholder
    $('.select2').each(function() {
        var placeholder = $(this).find('option[value=""]').text() || '-- Pilih --';
        $(this).data('placeholder', placeholder);
    });
});

function savedata(formz){
    var formData = new FormData(formz);
    $.ajax({
        type:'POST',
        url: $(formz).attr('action'),
        data:formData,
        cache:false,
        contentType: false,
        processData: false,
        beforeSend: function(r){ silahkantunggu(); },
        success:function(data){
            if(data == 'gagal'){
                alertfail();
                berhasil();
            }else{
                if({{ $save ?? 1 }} == '1'){
                    window.location="{{ url('jadwal_usm_pmb') }}";
                }
                if({{ $save ?? 1 }} == '2'){
                    window.location.href = "{{ url('jadwal_usm_pmb/view') }}/{{ $row->id ?? '' }}";
                }
                berhasil();
                alertsuccess();
            }
        },
        error: function(data){
            $(".btnSave").html("{{ __('app.save') }} Data");
            $(".btnSave").removeAttr("disabled");
            alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.');
        }
    });
}

function btnEdit(type,checkid) {
    $("input:text, input:file, .num, input:radio, select, textarea, button:submit").attr('disabled',true);
    $(".btnSave").css('display','none');
    if (checkid == 1){
        $("input:text, input:file, .num, input:radio, select, textarea, button:submit").removeAttr('disabled');
        $(".btnEdit").fadeOut(0);
        $(".btnSave").fadeIn(0);
    }
}
btnEdit({{ $save ?? 1 }});

// CI3 compatibility - change_jenisusm function
function change_jenisusm() {
    // Optional: Add AJAX call to filter jenis ujian based on gelombang
    // For now, just reload all jenis ujian
}
</script>
@endpush
