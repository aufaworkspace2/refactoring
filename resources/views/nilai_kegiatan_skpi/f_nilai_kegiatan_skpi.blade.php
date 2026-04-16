@extends('layouts.template1')
@section('content')

@php
if(empty($row ?? null)) {
    $row = [
        'ID' => '',
        'KegiatanID' => '',
        'KategoriKegiatanID' => '',
        'Point' => ''
    ];
    $judul = __('app.title_add');
    $slog = __('app.slog_add');
    $btn = __('app.add');
} else {
    $judul = __('app.title_view');
    $slog = __('app.slog_view').'<b>'.($row['namaKegiatan'] ?? '').'</b>';
    $btn = __('app.edit');
}
@endphp

<div class="card">
    <div class="card-body">
        <form id="f_nilai_kegiatan_skpi" onsubmit="savedata(this); return false;"
              action="{{ url('nilai_kegiatan_skpi/save/'.$save) }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="ID" id="ID" value="{{ $row['ID'] ?? '' }}">

            <h3>Form Nilai Kegiatan</h3>

            <div class="form-row mt-3">
                <div class="form-group col-md-12">
                    <label class="control-label" for="KegiatanID">Kegiatan *</label>
                    <div class="controls">
                        <select name="KegiatanID" required id="KegiatanID" class="form-control">
                            <option value="">-- Pilih Kegiatan --</option>
                            @foreach(($data_kegiatan ?? []) as $raw)
                                <option value="{{ $raw['ID'] ?? '' }}" {{ ($row['KegiatanID'] ?? '') == ($raw['ID'] ?? '') ? 'selected':'' }}>
                                    {{ $raw['Nama'] ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="control-label" for="KategoriKegiatanID">Tingkat/Sebagai *</label>
                    <div class="controls">
                        <select name="KategoriKegiatanID" required id="KategoriKegiatanID" class="form-control">
                            <option value="">-- Pilih Tingkat/Sebagai --</option>
                            @foreach(($data_kategori ?? []) as $raw)
                                <option value="{{ $raw['ID'] ?? '' }}" {{ ($row['KategoriKegiatanID'] ?? '') == ($raw['ID'] ?? '') ? 'selected':'' }}>
                                    {{ $raw['Nama'] ?? '' }} ({{ $raw['namaJenis'] ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="control-label" for="Point">Point *</label>
                    <div class="controls">
                        <input type="text" id="Point" name="Point" required class="form-control"
                               value="{{ $row['Point'] ?? '' }}" />
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
            if(data.status == 'gagal'){
                alertfail();
                berhasil();
            } else {
                @if($save == 1)
                    window.location.href = "{{ url('nilai_kegiatan_skpi') }}";
                @endif

                @if($save == 2)
                    window.location.href = "{{ url('nilai_kegiatan_skpi/view/'.$row['ID']) }}";
                @endif

                berhasil();
                alertsuccess();
            }
        },
        error: function(data){
            $(".btnSave").html('{{ __('app.save') }} Data <icon class="icon-check icon-white-t"></icon>');
            $(".btnSave").removeAttr("disabled");

            $(".alert-error").animate({ backgroundColor: "#ec9b9b" }, 1000);
            $(".alert-error").animate({ backgroundColor: "#df3d3d" }, 1000);
            $(".alert-error").animate({ backgroundColor: "#ec9b9b" }, 1000);
            $(".alert-error").animate({ backgroundColor: "#df3d3d" }, 1000);

            $(".alert-error").show();
            $(".alert-error-content").html("{{ __('app.alert-error') }}");
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
    window.location.href = "{{ url('nilai_kegiatan_skpi') }}";
}
</script>
@endpush

@endsection
