@extends('layouts.template1')
@section('content')

@php
if(empty($row)) {
    $row = [
        'ID' => '',
        'Nama' => ''
    ];
    $judul = __('app.title_add');
    $slog = __('app.slog_add');
    $btn = __('app.add');
} else {
    // Convert object to array if needed
    if(is_object($row)) {
        $row = (array) $row;
    }
    $judul = __('app.title_view');
    $slog = __('app.slog_view').'<b>'.($row['Nama'] ?? '').'</b>';
    $btn = __('app.edit');
}
@endphp

<div class="card">
    <div class="card-body">
        <form id="f_jenis_kategori" onsubmit="savedata(this); return false;"
              action="{{ url('jenis_kategori_skpi/save/'.$save) }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="ID" id="ID" value="{{ $row['ID'] ?? '' }}">

            <h3>{{ $judul }}</h3>

            <div class="form-row mt-3">
                <div class="form-group col-md-12">
                    <label class="control-label" for="Nama">{{ __('Nama') }} *</label>
                    <div class="controls">
                        <input type="text" id="Nama" name="Nama" required class="form-control"
                               value="{{ $row['Nama'] ?? '' }}" />
                    </div>
                </div>
            </div>

            <button onClick="btnEdit({{$save}},1)" type="button"
                    class="btn btn-bordered-success waves-effect width-md waves-light btnEdit">
                {{$btn}} Data
            </button>
            <button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">
                {{ __('save') }} Data
            </button>
            <button type="button" onClick="back()"
                    class="btn btn-bordered-danger waves-effect width-md waves-light">
                {{ __('back') }}
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
    btnEdit({{$save}});
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
            if(data == 'gagal'){
                alertfail();
                berhasil();
            } else {
                @if($save == 1)
                    window.location.href = "{{ url('jenis_kategori_skpi') }}";
                @endif
                
                @if($save == 2)
                    window.location.href = "{{ url('jenis_kategori_skpi/view/'.$row['ID']) }}";
                @endif
                
                berhasil();
                alertsuccess();
            }
        },
        error: function(data){
            $(".btnSave").html('{{ __('save') }} Data <icon class="icon-check icon-white-t"></icon>');
            $(".btnSave").removeAttr("disabled");
            
            $(".alert-error").animate({ backgroundColor: "#ec9b9b" }, 1000);
            $(".alert-error").animate({ backgroundColor: "#df3d3d" }, 1000);
            $(".alert-error").animate({ backgroundColor: "#ec9b9b" }, 1000);
            $(".alert-error").animate({ backgroundColor: "#df3d3d" }, 1000);

            $(".alert-error").show();
            $(".alert-error-content").html("{{ __('alert-error') }}");
            window.setTimeout(function() { $(".alert-error").slideUp("slow"); }, 6000);
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
    window.location.href = "{{ url('jenis_kategori_skpi') }}";
}
</script>
@endpush

@endsection
