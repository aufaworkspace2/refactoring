{{--
FILE: resources/views/search/s_level.blade.php

Refactored dari CI3: s_level.php → Laravel Blade

RULES APPLIED:
✅ LOGIC PRESERVED 100% - Semua if/else loop tetap sama
✅ TIDAK RINGKAS LOGIC - Setiap kondisi tetap eksplisit
✅ LOOP PRESERVED - Setiap loop ada padanannya
✅ VARIABLE NAMES SAME - Semua naming tetap sama
--}}

<p>{{ $total_row }}</p>

<form id="f_delete_level" action="{{ route('c_level.delete') }}">
    @csrf

    <div class="table-responsive">
        <table class="table table-bordered mb-0 table-hover tablesorter">
            <thead class="bg-primary text-white">
                <tr>
                    {{-- LOGIC 1: Header checkbox untuk select all (CI3 Line 5-10) --}}
                    <th>
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkAll" id="checkAll"
                                   onClick="checkall(this,document.forms.namedItem('f_delete_level')); show_btnDelete();">
                            <label for="checkAll"></label>
                        </div>
                    </th>

                    {{-- LOGIC 2: Header columns (CI3 Line 11-16) --}}
                    <th class="text-center" width="2%">No.</th>
                    <th class="text-center" width="2%">No Urut</th>
                    <th width="80%">{{ trans('messages.Nama') }}</th>
                    <th class="text-center" width="16%">Level Modul</th>
                </tr>
            </thead>

            <tbody>
                {{-- LOGIC 3: Loop semua query hasil search (CI3 Line 19) --}}
                @php
                    $no = $offset;
                    $i = 0;
                @endphp

                @foreach($query as $row)
                    <tr class="level_{{ $row->ID }}">
                        {{-- LOGIC 4: Checkbox untuk setiap row (CI3 Line 21-26) --}}
                        <td>
                            {{-- LOGIC 5: IF row bukan special level (CI3 Line 22) --}}
                            @if($row->Nama != 'SUPER USER' && $row->Nama != 'Dosen KBK' &&
                                $row->Nama != 'ADMINISTRATOR' && $row->Nama != 'Staff')
                                {{-- LOGIC 6: Show checkbox (CI3 Line 23-27) --}}
                                <div class="checkbox checkbox-info">
                                    <input type="checkbox" name="checkID[]" id="checkID{{ $i }}"
                                           onclick="show_btnDelete()" value="{{ $row->ID }}">
                                    <label for="checkID{{ $i }}"></label>
                                </div>
                                @php $i++; @endphp
                            {{-- LOGIC 7: ELSE → skip checkbox --}}
                            @endif
                        </td>

                        {{-- LOGIC 8: No urut (CI3 Line 28) --}}
                        <td class="text-center">{{ ++$no }}.</td>

                        {{-- LOGIC 9: Urut value (CI3 Line 29) --}}
                        <td class="text-center">{{ $row->Urut }}</td>

                        {{-- LOGIC 10: Nama dengan conditional link (CI3 Line 30-36) --}}
                        <td>
                            {{-- LOGIC 11: IF row bukan special level (CI3 Line 31) --}}
                            @if($row->Nama != 'SUPER USER' && $row->Nama != 'Dosen KBK' &&
                                $row->Nama != 'ADMINISTRATOR' && $row->Nama != 'Staff')
                                {{-- LOGIC 12: THEN show link (CI3 Line 32) --}}
                                <a href="#c_level/view/{{ $row->ID }}">{{ $row->Nama }}</a>
                            {{-- LOGIC 13: ELSE show text only (CI3 Line 33-36) --}}
                            @else
                                {{ $row->Nama }}
                            @endif
                        </td>

                        {{-- LOGIC 14: Level Modul button (CI3 Line 37-42) --}}
                        <td class="text-center">
                            {{-- LOGIC 15: IF row bukan special level OR devmode (CI3 Line 38) --}}
                            @if(($row->Nama != 'SUPER USER' && $row->Nama != 'Dosen KBK' &&
                                 $row->Nama != 'ADMINISTRATOR' && $row->Nama != 'Staff') ||
                                session('devmode') == 1)
                                {{-- LOGIC 16: THEN show button (CI3 Line 39-40) --}}
                                <a href="#c_levelmodul/?level={{ $row->ID }}"
                                   class="btn btn-primary waves-effect waves-light btn-sm">
                                    <i class="mdi mdi-format-list-bulleted"></i> Level Modul
                                </a>
                            {{-- LOGIC 17: ELSE show dash (CI3 Line 41-43) --}}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- LOGIC 18: Modal Hapus (CI3 Line 45-59) --}}
        <div id="hapus" class="modal fade" tabindex="-1" role="dialog"
             aria-labelledby="hapus" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    {{-- LOGIC 19: Modal header (CI3 Line 47-49) --}}
                    <div class="modal-header">
                        <h4 class="modal-title" id="hapus">{{ trans('messages.confirm_header') }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>

                    {{-- LOGIC 20: Modal body (CI3 Line 50-52) --}}
                    <div class="modal-body">
                        <p>{{ trans('messages.confirm_message') }}</p>
                        <p class="data_name"></p>
                    </div>

                    {{-- LOGIC 21: Modal footer (CI3 Line 53-57) --}}
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger waves-effect">
                            {{ trans('messages.delete') }}
                        </button>
                        <button type="button" class="btn btn-primary waves-effect waves-light"
                                data-dismiss="modal">
                            {{ trans('messages.close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- LOGIC 22: Pagination links (CI3 Line 64) --}}
    <div class="row">
        <div class="col-md-12">
            {!! $link !!}
        </div>
    </div>
</form>

{{-- ============================================================================ --}}
{{-- JAVASCRIPT AREA (CI3 Line 69-) --}}
{{-- ============================================================================ --}}

<script>
    {{-- LOGIC 23: Initialize tablesorter (CI3 Line 71) --}}
    tablesorter();

    {{-- LOGIC 24: Form delete submit handler (CI3 Line 72) --}}
    $("#f_delete_level").submit(function() {
        {{-- LOGIC 25: AJAX post delete (CI3 Line 73-79) --}}
        $.ajax({
            type: "POST",
            url: $("#f_delete_level").attr('action'),
            data: $("#f_delete_level").serialize(),
            {{-- LOGIC 26: Success handler (CI3 Line 80) --}}
            success: function(data) {
                {{-- LOGIC 27: Update konten (CI3 Line 81) --}}
                $("#isi_load").html(data);
                {{-- LOGIC 28: Hide modal (CI3 Line 82) --}}
                $("#hapus").modal("hide");

                {{-- LOGIC 29: Animate alert-success (CI3 Line 84-92) --}}
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

                {{-- LOGIC 30: Show alert-success (CI3 Line 94-97) --}}
                $(".alert-success").show();
                $(".alert-success-content").html("{{ trans('messages.alert-success-delete') }}");
                window.setTimeout(function() {
                    $(".alert-success").slideUp();
                }, 10000);
            },
            {{-- LOGIC 31: Error handler (CI3 Line 98) --}}
            error: function(data) {
                {{-- LOGIC 32: Animate alert-error (CI3 Line 99-107) --}}
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

                {{-- LOGIC 33: Show alert-error (CI3 Line 109-112) --}}
                $(".alert-error").show();
                $(".alert-error-content").html("{{ trans('messages.alert-error-delete') }}");
                window.setTimeout(function() {
                    $(".alert-error").slideUp("slow");
                }, 6000);
            }
        });
        return false;
    });

    {{-- LOGIC 34: show_btnDelete function (CI3 Line 115) --}}
    function show_btnDelete() {
        {{-- LOGIC 35: Initialize loop var (CI3 Line 116) --}}
        i = 0;
        hasil = false;

        {{-- LOGIC 36: Loop semua checkID[] (CI3 Line 117-124) --}}
        while (document.getElementsByName('checkID[]').length > i) {
            {{-- LOGIC 37: Check if checkbox checked (CI3 Line 118) --}}
            var checkname = document.getElementById('checkID' + i).checked;

            {{-- LOGIC 38: IF checked → hasil = true (CI3 Line 120-123) --}}
            if (checkname == true) {
                hasil = true;
            }
            i++;
        }

        {{-- LOGIC 39: IF ada yang dipilih → enable delete button (CI3 Line 125-130) --}}
        if (hasil == true) {
            $('#btnDelete').removeAttr('disabled');
            $('#btnDelete').removeAttr('href');
            $('#btnDelete').removeAttr('title');
            $('#btnDelete').attr('href', '#hapus');
        }
        {{-- LOGIC 40: ELSE → disable delete button (CI3 Line 131-137) --}}
        else {
            $('#btnDelete').attr('disabled', 'disabled');
            $('#btnDelete').attr('href', '#');
            $('#btnDelete').attr('title', 'Pilih dahulu data yang akan di hapus');
        }
    }

    {{-- LOGIC 41: Call show_btnDelete on load (CI3 Line 139) --}}
    show_btnDelete();

    {{-- LOGIC 42: Row highlight on checkbox change (CI3 Line 141-148) --}}
    $("input:checkbox[name='checkID[]']").click(function() {
        {{-- LOGIC 43: IF checked → add table-danger class (CI3 Line 142-144) --}}
        if (this.checked == true) {
            $(this).parents('tr').addClass('table-danger');
        }
        {{-- LOGIC 44: ELSE → remove table-danger class (CI3 Line 145-148) --}}
        else {
            $(this).parents('tr').removeClass('table-danger');
        }
    });

    {{-- LOGIC 45: btnDelete click handler (CI3 Line 149) --}}
    $('#btnDelete').click(function() {
        {{-- LOGIC 46: AJAX untuk get selected names (CI3 Line 150-160) --}}
        $.ajax({
            url: "{{ route('welcome.test') }}?table=level&field=Nama",
            type: "POST",
            data: $("input:checkbox[name='checkID[]']:checked").serialize(),
            {{-- LOGIC 47: Success → update data_name (CI3 Line 161-163) --}}
            success: function(data) {
                $('.data_name').html(data);
            }
        });
    });
</script>

{{-- ============================================================================ --}}
{{-- END JAVASCRIPT AREA --}}
{{-- ============================================================================ --}}
