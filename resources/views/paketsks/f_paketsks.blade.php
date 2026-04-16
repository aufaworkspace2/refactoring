@extends('layouts.template1')

@section('content')
@php
    if(empty($query)) {
        $query = new stdClass();
        $query->SemesterPaket = '';
    }
@endphp

<div class="card">
    <div class="card-body">
        <form id="f_paketsks" onsubmit="savedata(this); return false;"
              action="{{ url('paketsks/save/'.$save) }}"
              enctype="multipart/form-data">
            @csrf
            <input class="span12" type="hidden" name="ID" id="ID" value="{{ $ProdiID }}">
            <h3>Setting Paket Semester SKS</h3>
            <div class="form-row mt-3">
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="ProdiID">{{ __('app.ProdiID') }}*</label>
                    <div class="controls">
                        <select id="ProdiID" disabled class="form-control" name="ProdiID">
                            @foreach(get_all('programstudi') as $raw)
                                <option value="{{ $raw->ID }}" {{ ($raw->ID == $ProdiID) ? 'selected' : '' }}>
                                    {{ $raw->ProdiID }} | {{ $raw->Nama }} | {{ get_field($raw->JenjangID, 'jenjang') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="SemesterPaket">Daftar Semester Paket</label>
                    <div class="controls">
                        <select id="SemesterPaket" class="form-control" multiple name="SemesterPaket[]">
                            @php
                                $arr = explode(",", $query->SemesterPaket ?? '');
                            @endphp
                            @for($n = 1; $n <= 8; $n++)
                                <option {{ in_array($n, $arr) ? 'selected' : '' }} value="{{ $n }}">
                                    Semester {{ $n }}
                                </option>
                            @endfor
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
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
    // Initialize Select2 for multi-select
    if (typeof $.fn.select2 !== 'undefined') {
        $('#SemesterPaket').select2({
            placeholder: "Pilih Semester Paket",
            allowClear: true
        });
    }
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
            if (data == 'gagal') {
                alertfail();
                berhasil();
            } else {
                if ({{ $save }} == '1') {
                    window.location = "{{ url('paketsks') }}";
                }

                if ({{ $save }} == '2') {
                    load_content('{{ url("paketsks/view/".$ProdiID) }}');
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
</script>
@endpush
