<p>{!! $total_row !!}</p>

<form id="f_delete_mahasiswa_diskon" action="{{ url('mahasiswa_diskon/delete') }}">
    @csrf
    <div class="table-responsive">
        <table class="table table-bordered mb-0 table-hover tablesorter">
            <thead class="bg-primary text-white">
                <tr>
                @if($Delete == 'YA')
                    <th width="2%">
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkAll" id="checkAll"
                                   onClick="checkall(this, document.forms.namedItem('f_delete_mahasiswa_diskon')); show_btnDelete();">
                            <label for="checkAll"></label>
                        </div>
                    </th>
                @endif
                    <th class="text-center" width="2%">No.</th>
                    <th>Berlaku Tahun</th>
                    <th>Program</th>
                    <th>Program Studi</th>
                    <th>Diskon / Beasiswa</th>
                    <th>NPM</th>
                    <th>Penerima Diskon</th>
                    <th>Status</th>
                    <th>Detail</th>
                </tr>
            </thead>
            <tbody>
            @php
                $query_master_diskon = DB::table('master_diskon')->get();
                $list_master_diskon = [];
                foreach($query_master_diskon as $row_master_diskon) {
                    $list_master_diskon[$row_master_diskon->ID] = $row_master_diskon;
                }

                $query_program = DB::table('program')->select('ID', 'Nama')->get();
                $program = [];
                foreach($query_program as $row_program) {
                    $program[$row_program->ID] = $row_program->Nama;
                }

                $query_programstudi = DB::table('programstudi')->select('ID', 'Nama', 'JenjangID')->get();
                $arr_programstudi = [];
                foreach($query_programstudi as $row_programstudi) {
                    $arr_programstudi[$row_programstudi->ID] = $row_programstudi;
                }

                $query_jenjang = DB::table('jenjang')->select('ID', 'Nama')->get();
                $jenjang = [];
                foreach($query_jenjang as $row_jenjang) {
                    $jenjang[$row_jenjang->ID] = $row_jenjang->Nama;
                }

                $no = $offset;
                $i = 0;
            @endphp
            @foreach($query as $row)
                <tr class="diskon_{{ $row->MhswID }}">
                @if($Delete == 'YA')
                    <td class="align-top">
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkID[]" id="checkID{{ $i }}"
                                   onclick="show_btnDelete()" value="{{ $row->ID }}">
                            <label for="checkID{{ $i }}"></label>
                        </div>
                    </td>
                @endif

                    <td class="text-center">{{ ++$no }}.</td>
                    <td>
                        {{ get_field($row->TahunID, 'tahun') }}
                    </td>
                    <td>{{ $program[$row->ProgramID] ?? '' }}</td>
                    <td>
                        @php
                            $row_prodi = $arr_programstudi[$row->ProdiID] ?? null;
                            $nama_jenjang = $jenjang[$row_prodi->JenjangID] ?? '';
                        @endphp
                        @if($row_prodi)
                            {{ $nama_jenjang }} {{ $row_prodi->Nama }}
                        @endif
                    </td>

                    <td>
                        @php
                            $expl_discount = explode(',', $row->ListMasterDiskonID);
                        @endphp
                        <ul>
                        @foreach($expl_discount as $y => $disc_id)
                            @if(isset($list_master_diskon[$disc_id]))
                                @php
                                    $master_diskon = $list_master_diskon[$disc_id];
                                    $nom = ($master_diskon->Tipe == 'nominal')
                                        ? rupiah($master_diskon->Jumlah)
                                        : $master_diskon->Jumlah . ' %';
                                @endphp
                                <li>{{ $master_diskon->Nama }} - {{ $nom }}</li>
                            @endif
                        @endforeach
                        </ul>
                    </td>
                    <td class="text-center"><b>{{ $row->NPM }}</b></td>

                    <td>{{ $row->Nama }}</td>

                    <td class="text-center">
                        {{ ($row->StatusAktif == 1) ? 'Aktif' : 'Nonaktif' }}
                    </td>
                    <td>
                        <a class="btn btn-info" href="javascript:void(0);"
                           onclick="load_modalLarge('Lihat Detail Beasiswa {{ $row->NPM }}', 'mahasiswa_diskon/lihat_detail/{{ $row->ID }}')">
                            Detail
                        </a>
                    </td>
                </tr>
                @php $i++; @endphp
            @endforeach
            </tbody>
        </table>

        <!-- Delete Confirmation Modal -->
        <div id="hapus" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="hapus" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="hapus">Konfirmasi Nonaktif Data</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah anda yakin akan menonaktif data yang di pilih ?</p>
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
        var checkname = document.getElementById('checkID' + i).checked;

        if(checkname == true) {
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
$("#f_delete_mahasiswa_diskon").submit(function() {
    $.ajax({
        type: "POST",
        url: $("#f_delete_mahasiswa_diskon").attr('action'),
        data: $("#f_delete_mahasiswa_diskon").serialize(),
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
        url: "{{ url('welcome/test') }}/?table=mahasiswa_diskon&field=Nama",
        type: "POST",
        data: $("input:checkbox[name='checkID[]']:checked").serialize(),
        success: function(data) {
            $('.data_name').html(data);
        }
    });
});

// Initialize on load
show_btnDelete();

// Aktifkan function
function aktifkan(id) {
    if (confirm('Apakah Anda Yakin Mengaktifkan Kembali Beasiswa Ini ?')) {
        $.ajax({
            url: "{{ url('mahasiswa_diskon/aktivkan') }}/" + id,
            type: "GET",
            success: function(data) {
                alert('Berhasil - Beasiswa Berhasil Diaktifkan kembali !');
                filter();
            },
            error: function(data) {
                alert('Gagal - Beasiswa Gagal Diaktifkan kembali !');
                filter();
            }
        });
    }
}
</script>
