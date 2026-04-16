@extends('layouts.template1')
@section('content')

@php
if(empty($row ?? null)) {
    $row = [
        'ID' => '',
        'Kode' => '',
        'ProdiID' => '',
        'Indonesia' => '',
        'Inggris' => ''
    ];
    $judul = __('app.title_add');
    $btn = __('app.add');
} else {
    $judul = __('app.title_view');
    $btn = __('app.edit');
}
@endphp

<div class="card">
    <div class="card-body">
        <form id="f_program" onsubmit="savedata(this); return false;"
              action="{{ url('skpi/informasi/saveInformasi/'.$save) }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="ID" id="ID" value="{{ $row['ID'] ?? '' }}">

            <h3>Informasi Tambahan</h3>

            <div class="form-row mt-3">
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="Kode">Kode *</label>
                    <div class="controls">
                        <input type="text" required id="Kode" name="Kode" class="form-control"
                               value="{{ $row['Kode'] ?? '' }}" maxlength="6" placeholder="Maksimal Karakter : 6 digit" />
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label class="col-form-label" for="ProdiID">Program Studi*</label>
                    <div class="controls">
                        <select class="form-control" required id="ProdiID" name="ProdiID[]" multiple>
                            <option value="">-- Pilih Program Studi--</option>
                            @foreach(($data_prodi ?? []) as $rew)
                                @php
                                    $selectedProdi = explode(',', $row['ProdiID'] ?? '');
                                    $select = in_array($rew['ID'], $selectedProdi) ? 'selected' : '';
                                @endphp
                                <option value="{{ $rew['ID'] ?? '' }}" {{ $select }}>
                                    {{ $rew['jenjangNama'] ?? '' }} | {{ $rew['Nama'] ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label class="col-form-label" for="Indonesia">Bahasa Indonesia *</label>
                    <div class="controls">
                        <textarea required id="Indonesia" name="Indonesia" class="form-control">{{ $row['Indonesia'] ?? '' }}</textarea>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label class="col-form-label" for="Inggris">Bahasa Inggris *</label>
                    <div class="controls">
                        <textarea required id="Inggris" name="Inggris" class="form-control">{{ $row['Inggris'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <button onClick="btnEdit({{$save}},1)" type="button"
                    class="btn btn-bordered-success waves-effect width-md waves-light btnEdit">
                {{$btn}} Data
            </button>
            <button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">
                {{ __('app.save') }} Data
            </button>
            <button type="button" onClick="back()"
                    class="btn btn-bordered-danger waves-effect width-md waves-light">
                {{ __('app.back') }}
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
    btnEdit({{$save}});
    autocomplete('ProdiID');
});

function savedata(formz){
    var formData = new FormData(formz);
    $.ajax({
        type:'POST',
        url: $(formz).attr('action'),
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function(r){
            silahkantunggu();
        },
        success:function(data){
            if(data.status == 'gagal' || data == 'gagal'){
                alertfail();
                berhasil();
            } else {
                @if($save == 1)
                    window.location.href = "{{ url('skpi/informasi') }}";
                @endif

                @if($save == 2)
                    window.location.href = "{{ url('skpi/informasi/viewInformasi/'.$row['ID']) }}";
                @endif

                berhasil();
                alertsuccess();
            }
        },
        error: function(data){
            $(".btnSave").html('{{ __('app.save') }} Data <icon class="icon-check icon-white-t"></icon>');
            $(".btnSave").removeAttr("disabled");
            alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.');
        }
    });
}

function btnEdit(type,checkid) {
    $("input:text").attr('disabled',true);
    $("input:file").attr('disabled',true);
    $("input:radio").attr('disabled',true);
    $("button:submit").attr('disabled',true);
    $("select").attr('disabled',true);
    $("textarea").attr('disabled',true);
    $(".btnSave").css('display','none');

    if (checkid == 1) {
        $("input:text").removeAttr('disabled');
        $("input:file").removeAttr('disabled');
        $("input:radio").removeAttr('disabled');
        $("select").removeAttr('disabled');
        $("textarea").removeAttr('disabled');
        $("button:submit").removeAttr('disabled');
        $(".btnEdit").fadeOut(0);
        $(".btnSave").fadeIn(0);
    }
}

function back() {
    window.location.href = "{{ url('skpi/informasi') }}";
}
</script>
@endpush

@endsection
