{{--
FILE: resources/views/views/v_level.blade.php

Refactored dari CI3: v_level.php → Laravel Blade

RULES APPLIED:
✅ LOGIC PRESERVED 100% - Semua logic tetap sama
✅ TIDAK RINGKAS LOGIC - Setiap kondisi tetap eksplisit
✅ VARIABLE NAMES SAME - Semua naming tetap sama
--}}

<div class="card-box">
    <div class="row">
        <div class="col-md-12">
            <div class="button-list">
                {{-- LOGIC 1: Add button (CI3 Line 7) --}}
                <a href="#{{ request()->segment(1) }}/add"
                   class="btn btn-bordered-primary waves-effect width-md waves-light">
                    <i class="mdi mdi-plus"></i> {{ trans('messages.add') }} Data
                </a>

                {{-- LOGIC 2: Delete button (CI3 Line 8) --}}
                <button class="btn btn-bordered-danger waves-effect width-md waves-light"
                        id="btnDelete" data-placement="top"
                        title="Silahkan pilih data terlebih dahulu." data-toggle="modal">
                    <i class="mdi mdi-delete"></i> {{ trans('messages.delete') }}
                </button>

                {{-- LOGIC 3: PDF button (CI3 Line 9) --}}
                <a href="javascript:;" onclick="pdf()"
                   class="btn btn-outline-primary waves-effect waves-light btn-md">
                    <i class="mdi mdi-printer pr-1"></i> {{ trans('messages.pdf') }}
                </a>

                {{-- LOGIC 4: Excel button (CI3 Line 10) --}}
                <a href="javascript:;" onclick="excel()"
                   class="btn btn-outline-success waves-effect waves-light btn-md">
                    <i class="mdi mdi-printer pr-1"></i> {{ trans('messages.excel') }}
                </a>
            </div>
        </div>
    </div>

    {{-- LOGIC 5: Search/Filter form (CI3 Line 12-17) --}}
    <div class="form-row">
        <div class="form-group col-md-12">
            <label class="col-form-label mt-2">
                <h4 class="m-0">{{ trans('messages.keyword_legend') }}</h4>
            </label>
            {{-- LOGIC 6: Keyword input dengan onkeyup event (CI3 Line 15) --}}
            <input type="text" class="form-control keyword" onkeyup="filter()"
                   placeholder="{{ trans('messages.keyword') }} ..">
        </div>
    </div>
</div>

{{-- LOGIC 7: Content container (CI3 Line 19) --}}
<div class="card-box">
    <div id="konten"></div>
</div>

{{-- ============================================================================ --}}
{{-- JAVASCRIPT AREA (CI3 Line 23-) --}}
{{-- ============================================================================ --}}

<script type="text/javascript">
    {{-- LOGIC 8: filter function (CI3 Line 25) --}}
    function filter(url) {
        {{-- LOGIC 9: Set default URL jika tidak ada (CI3 Line 26-27) --}}
        if (url == null) {
            url = "{{ route('c_level.search') }}";
        }

        {{-- LOGIC 10: AJAX post dengan keyword (CI3 Line 29-35) --}}
        $.ajax({
            type: "POST",
            url: url,
            data: {
                keyword: $(".keyword").val(),
            },
            {{-- LOGIC 11: Success → update konten (CI3 Line 36-38) --}}
            success: function(data) {
                $("#konten").html(data);
            }
        });
        {{-- LOGIC 12: Return false (CI3 Line 40) --}}
        return false;
    }

    {{-- LOGIC 13: pdf function (CI3 Line 43) --}}
    function pdf() {
        {{-- LOGIC 14: Open window dengan URL & keyword (CI3 Line 44) --}}
        window.open('{{ route('c_level.pdf') }}?keyword=' + $(".keyword").val(), "_Blank");
    }

    {{-- LOGIC 15: excel function (CI3 Line 48) --}}
    function excel() {
        {{-- LOGIC 16: Open window dengan URL & keyword (CI3 Line 49) --}}
        window.open('{{ route('c_level.excel') }}?keyword=' + $(".keyword").val(), "_Blank");
    }

    {{-- LOGIC 17: checkall function (CI3 Line 53) --}}
    function checkall(chkAll, checkid) {
        {{-- LOGIC 18: IF checkid not null (CI3 Line 54) --}}
        if (checkid != null) {
            {{-- LOGIC 19: Check if single or array (CI3 Line 56-57) --}}
            if (checkid.length == null) {
                {{-- LOGIC 20: Single checkbox (CI3 Line 56) --}}
                checkid.checked = chkAll.checked;
            } else {
                {{-- LOGIC 21: Multiple checkboxes loop (CI3 Line 57-58) --}}
                for (i = 0; i < checkid.length; i++) {
                    checkid[i].checked = chkAll.checked;
                }
            }

            {{-- LOGIC 22: Remove all table-danger (CI3 Line 60) --}}
            $("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
            {{-- LOGIC 23: Add table-danger ke checked (CI3 Line 61) --}}
            $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
        }
    }

    {{-- LOGIC 24: Call filter on page load (CI3 Line 64) --}}
    filter();
</script>

{{-- ============================================================================ --}}
{{-- END JAVASCRIPT AREA --}}
{{-- ============================================================================ --}}
