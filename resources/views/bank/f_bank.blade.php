@extends('layouts.template1')

@section('content')
@php
    if(empty($row)) {
        $row = new stdClass();
        $row->ID = '';
        $row->NamaBank = '';
        $row->NoRekening = '';
        $row->NamaPemilik = '';
        $row->ChannelPembayaranID_list = '';

        $judul = __('app.title_add');
        $slog = __('app.slog_add');
        $btn = __('app.add');
    } else {
        $judul = __('app.title_view');
        $slog = __('app.slog_view') . '<b>' . ($row->Nama ?? '') . '</b>';
        $btn = __('app.edit');
    }

    // Prepare channel pembayaran options
    $exp = explode(",", $row->ChannelPembayaranID_list ?? '');
    $all_metode_bayar = [];
    foreach(\Illuminate\Support\Facades\DB::table('metode_pembayaran')->get() as $r){
        $all_metode_bayar[$r->ID] = $r->Nama;
    }
@endphp

<div class="card">
    <div class="card-body">
        <form id="f_bank" onsubmit="savedata(this); return false;" 
              action="{{ url('bank/save/'.$save) }}" 
              enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="ID" value="{{ $row->ID }}">
            <h3>{{ __('app.title') }}</h3>
            <div class="form-row mt-3">
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="NamaBank">{{ __('app.NamaBank') }} *</label>
                    <div class="controls">
                        <input type="text" id="NamaBank" required name="NamaBank" 
                               class="form-control" value="{{ $row->NamaBank }}" />
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label class="col-form-label" for="NoRekening">{{ __('app.NoRekening') }} *</label>
                    <div class="controls">
                        <input type="text" id="NoRekening" required name="NoRekening" 
                               class="form-control" value="{{ $row->NoRekening }}" />
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label class="col-form-label" for="NamaPemilik">{{ __('app.NamaPemilik') }} *</label>
                    <div class="controls">
                        <input type="text" id="NamaPemilik" required name="NamaPemilik" 
                               class="form-control" value="{{ $row->NamaPemilik }}" />
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label class="col-form-label" for="ChannelPembayaranID_list">Channel Pembayaran</label>
                    <div class="controls">
                        <select id="ChannelPembayaranID_list" class="ChannelPembayaranID_list form-control" 
                                name="ChannelPembayaranID_list[]" multiple>
                            @foreach($ChannelPembayaranList as $r)
                                <option value="{{ $r->ID }}" {{ in_array($r->ID, $exp) ? 'selected' : '' }}>
                                    {{ ($all_metode_bayar[$r->MetodePembayaranID] ?? '') }} - {{ $r->Nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <button onClick="btnEdit({{ $save }}, 1)" type="button" 
                    class="btn btn-bordered-success waves-effect width-md waves-light btnEdit">
                {{ $btn }} Data
            </button>
            <button type="submit" 
                    class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">
                {{ __('app.save') }} Data
            </button>
            <button type="button" onClick="back()" 
                    class="btn btn-bordered-danger waves-effect width-md waves-light">
                {{ __('app.back') }}
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
    autocomplete('ChannelPembayaranID_list', '', 'Pilih Channel Pembayaran');
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
            if(data == 'gagal') {
                alertfail();
                berhasil();
            } else {
                if({{ $save }} == '1') {
                    window.location = "{{ url('bank') }}";
                }

                if({{ $save }} == '2') {
                    load_content('bank/view/{{ $row->ID }}');
                }
                berhasil();
                alertsuccess();
            }
        },
        error: function(data) {
            $(".btnSave").html('{{ __("app.save") }} Data <icon class="icon-check icon-white-t"></icon>');
            $(".btnSave").removeAttr("disabled");
            $(".alert-error").animate({
                backgroundColor: "#ec9b9b"
            }, 1000);
            $(".alert-error").animate({
                backgroundColor: "#df3d3d"
            }, 1000);
            $(".alert-error").animate({
                backgroundColor: "#ec9b9b"
            }, 1000);
            $(".alert-error").animate({
                backgroundColor: "#df3d3d"
            }, 1000);

            $(".alert-error").show();
            $(".alert-error-content").html("{{ __('app.alert-error') }}");
            window.setTimeout(function() { $(".alert-error").slideUp("slow"); }, 6000);
        }
    });
}

function btnEdit(type, checkid) {
    $("input:text").attr('disabled', true);
    $("input:file").attr('disabled', true);
    $("input:radio").attr('disabled', true);
    $("button:submit").attr('disabled', true);
    $("select").attr('disabled', true);
    $("textarea").attr('disabled', true);
    $(".btnSave").css('display', 'none');

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

btnEdit({{ $save }});
</script>
@endpush
