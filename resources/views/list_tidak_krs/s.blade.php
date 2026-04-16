<p>{!! $total_row !!}</p>

<form id="f_set" action="{{ url('list_tidak_krs/set_statusall') }}">
    @csrf
    <input type="hidden" name="TahunID" id="TahunID" value="{{ $TahunID }}">
    <div class="col-md-12">
        <div class="table-responsive">
            <table width="100%" class="table table-hover table-bordered tablesorter">
                <thead class="bg-primary text-white">
                    <tr>
                    @if($Update == 'YA')
                        <th class="text-center" width="2%">
                            <div class="checkbox checkbox-info">
                                <input type="checkbox" name="checkAll" id="checkAll"
                                       onClick="checkall(this, document.forms.namedItem('f_set')); show_btnDelete();">
                                <label for="checkAll"></label>
                            </div>
                        </th>
                    @endif
                        <th class="text-center" width="2%">No.</th>
                        <th class="text-center" width="33%">Nama</th>
                        <th class="text-center" width="8%">NPM</th>
                        <th class="text-center" width="10%">Program Kuliah</th>
                        <th class="text-center" width="17%">Program Studi</th>
                        <th class="text-center" width="8%">Tahun Masuk</th>
                        <th class="text-center" width="5%">Jumlah Semester Tidak KRS</th>
                        <th class="text-center" width="10%">Action</th>
                    </tr>
                </thead>
                <tbody>
                @php
                    $no = $offset;
                    $i = 0;
                @endphp
                @foreach($query as $mhs)
                    <tr class="mahasiswa_{{ $mhs->ID }}">
                    @if($Update == 'YA')
                        <td class="align-middle">
                            <div class="checkbox checkbox-info">
                                <input type="checkbox" class="checkID" name="checkID[]" id="checkID{{ $i }}"
                                       onclick="show_btnDelete()" value="{{ $mhs->ID }}">
                                <label for="checkID{{ $i++ }}"></label>
                            </div>
                        </td>
                    @endif
                        <td>{{ ++$no }}</td>
                        <td>{{ $mhs->Nama }}</td>
                        <td>{{ $mhs->NPM }}</td>
                        <td>{{ get_field($mhs->ProgramID, "program") }}</td>
                        <td>{{ get_field($mhs->ProdiID, 'programstudi') }}</td>
                        <td>{{ $mhs->TahunMasuk }}</td>
                        <td>{{ $mhs->jumlah ?? 0 }}</td>

                        <td>
                            <div class="btn-group">
                                <button class="btn {{ ($mhs->StatusMhswID == 3) ? 'btn-danger' : 'btn-info' }} dropdown-toggle" data-toggle="dropdown">
                                    Action <span class="mdi mdi-chevron-down"></span>
                                </button>
                                <div class="dropdown-menu float-right">
                                    @if($mhs->StatusMhswID == 3)
                                        <a href="javascript:void(0);" class="dropdown-item" onclick="show_al('{{ $mhs->ID }}', '2')">
                                            <i class="mdi mdi-pencil"></i> Set Cuti
                                        </a>
                                        <a href="javascript:void(0);" class="dropdown-item" onclick="show_al('{{ $mhs->ID }}', '6')">
                                            <i class="mdi mdi-pencil"></i> Set Non Aktif
                                        </a>
                                    @else
                                        <a href="javascript:void(0);" class="dropdown-item" onclick="show_al('{{ $mhs->ID }}', '3')">
                                            <i class="mdi mdi-pencil"></i> Set Aktif
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <!-- Batch Status Change Modal -->
            <div id="hapus" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="hapus" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4>{{ __('app.confirm_header') }}</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        </div>
                        <div class="modal-body">
                            Apakah Anda Yakin akan Merubah Status Mahasiswa yang Dipilih ?
                            <p class="data_name"></p>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary waves-effect waves-light">
                                <i class="fa fa-check"></i> Ya
                            </button>
                            <a href="javascript:void(0);" class="btn btn-danger waves-effect waves-light" data-dismiss="modal">
                                <i class="mdi mdi-close"></i> {{ __('app.close') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Single Student Status Change Modal -->
            <div id="al_set" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="al_set" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4>Konfirmasi Ubah Status</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        </div>
                        <div class="modal-body">
                            Apakah Anda Yakin Akan Mengubah Status Mahasiswa ini ?
                            <input type="hidden" value="" id="mahasiswaid">
                            <input type="hidden" value="" id="status">
                        </div>
                        <div class="modal-footer">
                            <button type="button" onclick="ubah()" class="btn btn-primary waves-effect waves-light">
                                <i class="fa fa-save"></i> Ubah
                            </button>
                            <a href="javascript:void(0);" class="btn btn-danger waves-effect waves-light" data-dismiss="modal">
                                <i class="mdi mdi-close"></i> {{ __('app.close') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        {!! $link !!}
    </div>
</form>

<script>
tablesorter();

// Batch status change form submit
$("#f_set").submit(function(e) {
    if($('.checkID:checkbox:checked').length == 0) {
        alert('Pilih Mahasiswa Terlebih Dahulu');
        e.preventDefault();
        return;
    }

    // Get action URL to determine which status to set
    var actionUrl = $(this).attr('action');
    var status = '';
    if(actionUrl.includes('set_statusall')) {
        // Default to the status from the clicked menu item
        // This is handled by the onclick in the dropdown menu
    }

    $.ajax({
        type: "POST",
        url: $(this).attr('action'),
        data: $(this).serialize(),
        success: function(data) {
            if(data == 2) {
                $('#hapus').modal('hide');
                alert('Tidak bisa Set Karena Biaya Cuti belum disetting.');
            } else {
                // Hide modal and cleanup
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
            }
        },
        error: function(data) {
            // Hide modal and cleanup
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

// Show single student status change modal
function show_al(id, status) {
    $('#mahasiswaid').val(id);
    $('#status').val(status);
    $('#al_set').modal('show');
}

// Update single student status
function ubah() {
    $.ajax({
        type: 'POST',
        url: "{{ url('list_tidak_krs/set_status') }}",
        data: {
            mhswID: $('#mahasiswaid').val(),
            status: $('#status').val(),
            TahunID: $('#TahunID').val(),
            _token: "{{ csrf_token() }}"
        },
        success: function(data) {
            if(data == 2) {
                alert('Tidak bisa Set Karena Biaya Cuti belum disetting.');
                $('#al_set').modal('hide');
                $("body").removeClass("modal-open");
                $(".modal-backdrop").remove();
            } else {
                $('#al_set').modal('hide');
                filter();
            }
        },
        error: function(data) {
            alert('Gagal mengubah status');
            $('#al_set').modal('hide');
        }
    });
}

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

show_btnDelete();

// Checkbox row highlight
$("input:checkbox[name='checkID[]']").click(function() {
    if(this.checked == true) {
        $(this).parents('tr').addClass('table-danger');
    } else {
        $(this).parents('tr').removeClass('table-danger');
    }
});

// Get selected student names for modal
$('.btnDelete').click(function() {
    $.ajax({
        url: "{{ url('welcome/test') }}/?table=mahasiswa&field=Nama",
        type: "POST",
        data: $("input:checkbox[name='checkID[]']:checked").serialize(),
        success: function(data) {
            $('.data_name').html(data);
        }
    });
});
</script>
