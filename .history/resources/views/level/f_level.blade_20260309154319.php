{{--
FILE: resources/views/forms/f_level.blade.php

Refactored dari CI3: f_level.php → Laravel Blade

RULES APPLIED:
✅ LOGIC PRESERVED 100% - Semua if/else tetap sama jumlah cabang
✅ TIDAK RINGKAS LOGIC - Setiap kondisi tetap eksplisit
✅ VARIABLE NAMES SAME - Semua naming tetap sama
--}}

@php
    // ================================================================
    // LOGIC 1: Check if $row empty (CI3 Line 3-16)
    // ================================================================
    if (empty($row)) {
        // LOGIC 2: Set default values jika add mode (CI3 Line 4-7)
        $row = new \stdClass();
        $row->ID = '';
        $row->Nama = '';
        $row->Urut = '';

        // LOGIC 3: Set judul untuk add mode (CI3 Line 9-11)
        $judul = trans('messages.title_add');
        $slog = trans('messages.slog_add');
        $btn = trans('messages.add');
    }
    // LOGIC 4: ELSE → set untuk edit mode (CI3 Line 12-16)
    else {
        // LOGIC 5: Set judul untuk edit mode (CI3 Line 13-15)
        $judul = trans('messages.title_view');
        $slog = trans('messages.slog_view') . '<b>' . $row->Nama . '</b>';
        $btn = trans('messages.edit');
    }
@endphp

<div class="card">
    <div class="card-body">
        <form id="f_level" action="{{ route('c_level.save', $save) }}" enctype="multipart/form-data">
            @csrf
            <input class="span12" type="hidden" name="ID" id="ID" value="{{ $row->ID }}">

            {{-- ============================================================ --}}
            {{-- LOGIC 6: Tab Content - Title (CI3 Line 27) --}}
            {{-- ============================================================ --}}
            <h3>{{ trans('messages.title') }}</h3>

            <div class="form-row mt-3">
                {{-- ================================================== --}}
                {{-- LOGIC 7: Form group - Urut (CI3 Line 30-35) --}}
                {{-- ================================================== --}}
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="Urut">No Urut *</label>
                    <div class="controls">
                        <input type="text" id="Urut" name="Urut" class="form-control"
                               value="{{ $row->Urut }}" />
                    </div>
                </div>

                {{-- ================================================== --}}
                {{-- LOGIC 8: Form group - Nama (CI3 Line 36-41) --}}
                {{-- ================================================== --}}
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="Nama">{{ trans('messages.Nama') }} *</label>
                    <div class="controls">
                        <input type="text" id="Nama" name="Nama" class="form-control"
                               value="{{ $row->Nama }}" />
                    </div>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- LOGIC 9: Button group (CI3 Line 44-47) --}}
            {{-- ============================================================ --}}
            <button onClick="btnEdit({{ $save }},1)" type="button"
                    class="btn btn-bordered-success waves-effect width-md waves-light btnEdit">
                {{ $btn }} Data <icon class="icon-ok-circle icon-white-t"></icon>
            </button>

            <button type="submit" id="save_level"
                    class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">
                {{ trans('messages.save') }} Data <icon class="icon-check icon-white-t"></icon>
            </button>

            <button type="button" id="backbut" onclick="back()"
                    class="btn btn-bordered-danger waves-effect width-md waves-light">
                {{ trans('messages.back') }} <icon class="icon-share-alt icon-white-t"></icon>
            </button>
        </form>
    </div>
</div>

{{-- ============================================================================ --}}
{{-- JAVASCRIPT AREA (CI3 Line 50-) --}}
{{-- ============================================================================ --}}

<script type="text/javascript">
    {{-- LOGIC 10: Get hash dari URL (CI3 Line 52-53) --}}
    var hash = window.location.hash.substr(1);
    var hashArr = hash.split("/");

    {{-- LOGIC 11: Check if hashArr[0] == 'c_karyawan' (CI3 Line 56-58) --}}
    if (hashArr[0] == 'c_karyawan') {
        $('#backbut').hide();
    }

    {{-- LOGIC 12: jQuery validate form (CI3 Line 59) --}}
    $("#f_level").validate({
        rules: {
            {{-- LOGIC 13: Nama wajib diisi (CI3 Line 61-64) --}}
            Nama: {
                required: true
            },
        },
        {{-- LOGIC 14: Submit handler (CI3 Line 65) --}}
        submitHandler: function(form) {
            var formData = new FormData(form);

            {{-- LOGIC 15: AJAX post (CI3 Line 68-73) --}}
            $.ajax({
                type: 'POST',
                url: $(form).attr('action'),
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                {{-- LOGIC 16: Success handler (CI3 Line 74) --}}
                success: function(data) {
                    {{-- LOGIC 17: Check hashArr[0] != 'c_karyawan' (CI3 Line 75) --}}
                    if (hashArr[0] != 'c_karyawan') {
                        {{-- LOGIC 18: IF save == 1 (add mode) (CI3 Line 76-80) --}}
                        if ({{ $save }} == '1') {
                            window.location = "{{ route('c_level.index') }}";
                        }

                        {{-- LOGIC 19: IF save == 2 (edit mode) (CI3 Line 81-85) --}}
                        if ({{ $save }} == '2') {
                            load_content('c_level/view/{{ $row->ID }}');
                        }

                        {{-- LOGIC 20: Animate alert-success (CI3 Line 86-94) --}}
                        $(".alert-success").animate({
                            backgroundColor: "#dff0d8"
                        }, 1000);
                        $(".alert-success").animate({
                            backgroundColor: "#b6ef9e"
                        }, 1000);
                        $(".alert-success").animate({
                            backgroundColor: "#dff0d8"
                        }, 1000);
                        $(".alert-success").animate({
                            backgroundColor: "#b6ef9e"
                        }, 1000);

                        {{-- LOGIC 21: Show alert-success (CI3 Line 96-99) --}}
                        $(".alert-success").show();
                        $(".alert-success-content").html("{{ trans('messages.alert-success') }}");
                        window.setTimeout(function() {
                            $(".alert-success").slideUp();
                        }, 10000);

                    }
                    {{-- LOGIC 22: ELSE (hashArr[0] == 'c_karyawan') (CI3 Line 100-102) --}}
                    else {
                        $('#nama_level_' + '{{ $row->ID }}').text(data);
                        $('#load_modal_large_ubah_editlevel').modal('hide');
                    }
                },
                {{-- LOGIC 23: Error handler (CI3 Line 103) --}}
                error: function(data) {
                    {{-- LOGIC 24: Check hashArr[0] != 'c_karyawan' (CI3 Line 104) --}}
                    if (hashArr[0] != 'c_karyawan') {

                        {{-- LOGIC 25: Animate alert-error (CI3 Line 105-113) --}}
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

                        {{-- LOGIC 26: Show alert-error (CI3 Line 115-118) --}}
                        $(".alert-error").show();
                        $(".alert-error-content").html("{{ trans('messages.alert-error') }}");
                        window.setTimeout(function() {
                            $(".alert-error").slideUp("slow");
                        }, 6000);

                    }
                    {{-- LOGIC 27: ELSE (hashArr[0] == 'c_karyawan') (CI3 Line 119-120) --}}
                    else {
                        $('#load_modal_large_ubah_editlevel').modal('hide');
                    }
                }
            });
        }
    });

    {{-- LOGIC 28: btnEdit function (CI3 Line 122-137) --}}
    function btnEdit(type, checkid) {
        {{-- LOGIC 29: Disable semua input (CI3 Line 123-128) --}}
        $("input:text").attr('disabled', true);
        $("input:file").attr('disabled', true);
        $("input:radio").attr('disabled', true);
        $("select").attr('disabled', true);
        $("textarea").attr('disabled', true);
        $("#save_level").css('display', 'none');

        {{-- LOGIC 30: IF checkid == 1 → enable input (CI3 Line 129-138) --}}
        if (checkid == 1) {
            $("input:text").removeAttr('disabled');
            $("input:file").removeAttr('disabled');
            $("input:radio").removeAttr('disabled');
            $("select").removeAttr('disabled');
            $("textarea").removeAttr('disabled');
            $("button:submit").removeAttr('disabled');
            $(".btnEdit").fadeOut(0);
            $("#save_level").fadeIn(0);
        }
    }

    {{-- LOGIC 31: Call btnEdit on load (CI3 Line 141) --}}
    btnEdit({{ $save }});
</script>

{{-- ============================================================================ --}}
{{-- END JAVASCRIPT AREA --}}
{{-- ============================================================================ --}}
