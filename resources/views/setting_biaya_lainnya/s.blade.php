<p>{!! $total_row !!}</p>

<form id="f_delete_setting_biaya_lainnya" action="{{ url('setting_biaya_lainnya/delete') }}">
    @csrf
    <div class="table-responsive">
        <table class="table table-bordered mb-0 table-hover tablesorter">
            <thead class="bg-primary text-white">
                <tr>
                @if($Delete == 'YA')
                    <th>
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkAll" id="checkAll"
                                   onClick="checkall(this, document.forms.namedItem('f_delete_setting_biaya_lainnya')); show_btnDelete();">
                            <label for="checkAll"></label>
                        </div>
                    </th>
                @endif
                    <th class="align-middle text-center" width="2%">No.</th>
                    <th class="align-middle text-center">Gambar</th>
                    <th class="align-middle text-center">Nama</th>
                    <th class="align-middle text-center">Deskripsi</th>
                    <th class="align-middle text-center">Harga</th>
                    <th class="align-middle text-center">Tanggal Mulai</th>
                    <th class="align-middle text-center">Tanggal Selesai</th>
                </tr>
            </thead>
            <tbody>
            @php
                $no = $offset;
                $i = 0;
            @endphp
            @foreach($query as $row)
                <tr class="setting_biaya_lainnya_{{ $row->ID }}">
                @if($Delete == 'YA')
                    <td>
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkID[]" id="checkID{{ $i }}"
                                   onclick="show_btnDelete()" value="{{ $row->ID }}">
                            <label for="checkID{{ $i }}"></label>
                        </div>
                    </td>
                    @php $i++; @endphp
                @endif
                    <td class="center">{{ ++$no }}.</td>
                    <td>
                        @if($row->Gambar)
                            {{ $row->Gambar }}
                            <br>
                            <a href="{{ asset('client/biaya_lainnya/gambar/' . $row->Gambar) }}" target="_blank">
                                (<i class="fa fa-search"></i> Lihat Gambar)
                            </a>
                        @else
                            <i>NO IMAGE</i>
                        @endif
                    </td>
                    <td>
                        @if($Update == 'YA')
                            <a href="{{ url('setting_biaya_lainnya/view/' . $row->ID) }}">{{ $row->NamaJB }}</a>
                        @else
                            {{ $row->NamaJB }}
                        @endif
                    </td>
                    <td>{{ $row->Deskripsi }}</td>
                    <td>{{ rupiah($row->Harga) }}</td>
                    <td>{{ tgl($row->TanggalMulai, '02') }}</td>
                    <td>{{ tgl($row->TanggalSelesai, '02') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <!-- Delete Confirmation Modal -->
        <div id="hapus" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="hapus" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="hapus">{{ __('app.confirm_header') }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body">
                        <p>{{ __('app.confirm_message') }}</p>
                        <p class="data_name"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger waves-effect">{{ __('app.delete') }}</button>
                        <button type="button" class="btn btn-primary waves-effect waves-light" data-dismiss="modal">{{ __('app.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            {!! $link !!}
        </div>
    </div>
</form>

<script>
tablesorter();

// Global function for show_btnDelete
window.show_btnDelete = function() {
    i = 0; hasil = false;
    while(document.getElementsByName('checkID[]').length > i) {
        var checkname = document.getElementById('checkID' + i);
        if(checkname && checkname.checked == true) {
            hasil = true;
        }
        i++;
    }
    if(hasil == true) {
        $('#btnDelete').removeAttr('disabled');
        $('#btnDelete').removeAttr('href');
        $('#btnDelete').removeAttr('title');
        $('#btnDelete').attr('href', '#hapus');
    } else {
        $('#btnDelete').attr('disabled', 'disabled');
        $('#btnDelete').attr('href', '#');
        $('#btnDelete').attr('title', 'Pilih dahulu data yang akan di hapus');
    }
}

// Form submit handler
$("#f_delete_setting_biaya_lainnya").submit(function() {
    $.ajax({
        type: "POST",
        url: $("#f_delete_setting_biaya_lainnya").attr('action'),
        data: $("#f_delete_setting_biaya_lainnya").serialize(),
        success: function(data) {
            // Hide modal and cleanup backdrop
            $("#hapus").modal("hide");
            $("body").removeClass("modal-open");
            $(".modal-backdrop").remove();

            // Show success alert
            $(".alert-success").show();
            $(".alert-success-content").html("{{ __('app.alert-success-delete') }}");
            window.setTimeout(function() { $(".alert-success").slideUp(); }, 10000);

            // Refresh data after modal is fully closed
            setTimeout(function() {
                filter();
            }, 300);
        },
        error: function(data) {
            // Hide modal and cleanup backdrop on error too
            $("#hapus").modal("hide");
            $("body").removeClass("modal-open");
            $(".modal-backdrop").remove();

            $(".alert-error").show();
            $(".alert-error-content").html("{{ __('app.alert-error-delete') }}");
            window.setTimeout(function() { $(".alert-error").slideUp('slow'); }, 6000);
        }
    });
    return false;
});

// Checkbox row highlight
$("input:checkbox[name='checkID[]']").click(function() {
    if(this.checked == true) {
        $(this).parents('tr').addClass('table-danger');
    } else {
        $(this).parents('tr').removeClass('table-danger');
    }
});

// Get selected data names for modal
$('#btnDelete').click(function() {
    $.ajax({
        url: "{{ url('welcome/test') }}/?table=setting_biaya_lainnya&field=Nama",
        type: "POST",
        data: $("input:checkbox[name='checkID[]']:checked").serialize(),
        success: function(data) {
            $('.data_name').html(data);
        }
    });
});

// Initialize on load
show_btnDelete();
</script>

<script src="{{ asset('assets/theme/scripts/responsive-table.js') }}"></script>
