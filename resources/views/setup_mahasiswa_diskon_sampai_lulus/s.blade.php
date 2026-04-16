<p>{!! $total_row !!}</p>

<form id="f_delete_setup_mahasiswa_diskon_sampai_lulus" action="{{ url('setup_mahasiswa_diskon_sampai_lulus/delete') }}">
    @csrf
    <div class="table-responsive">
        <table class="table table-bordered mb-0 table-hover tablesorter">
            <thead class="bg-primary text-white">
                <tr>
                @if($Delete == 'YA')
                    <th width="2%">
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkAll" id="checkAll"
                                   onClick="checkall(this, document.forms.namedItem('f_delete_setup_mahasiswa_diskon_sampai_lulus')); show_btnDelete();">
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
                </tr>
            </thead>
            <tbody>
            @php
                $query_master_diskon = DB::table('master_diskon')->get();
                $list_master_diskon = [];
                foreach($query_master_diskon as $row_master_diskon) {
                    $list_master_diskon[$row_master_diskon->ID] = $row_master_diskon;
                }

                $query_jenisbiaya = DB::table('jenisbiaya')->get();
                $list_jenisbiaya = [];
                foreach($query_jenisbiaya as $row_jenisbiaya) {
                    $list_jenisbiaya[$row_jenisbiaya->ID] = $row_jenisbiaya;
                }

                $query_jenjang = DB::table('jenjang')->select('ID', 'Nama')->get();
                $jenjang = [];
                foreach($query_jenjang as $row_jenjang) {
                    $jenjang[$row_jenjang->ID] = $row_jenjang->Nama;
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
                    @php $i++; @endphp
                @endif
                    <td class="text-center">{{ ++$no }}.</td>
                    <td>
                        @if ($row->PerTahunID == 1)
                            @php
                                $expl_tahunid = explode(',', $row->ListTahunID);
                            @endphp
                            <ul>
                                @foreach($expl_tahunid as $tahunid)
                                    @php
                                        $tahunx = DB::table('tahun')->where('ID', $tahunid)->first();
                                    @endphp
                                    @if($tahunx)
                                        <li>{{ $tahunx->Nama }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        @else
                            == Di Semua Tahun Akademik ==
                        @endif
                    </td>
                    <td>{{ $program[$row->ProgramID] ?? '-' }}</td>
                    <td>
                        @php
                            $row_prodi = $arr_programstudi[$row->ProdiID] ?? null;
                            $nama_jenjang = $row_prodi ? ($jenjang[$row_prodi->JenjangID] ?? '') : '';
                        @endphp
                        @if($row_prodi)
                            {{ $nama_jenjang }} {{ $row_prodi->Nama }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @php
                            $ListDiskon = json_decode($row->ListDiskon, true);
                        @endphp
                        @if(is_array($ListDiskon))
                            @foreach($ListDiskon as $row_diskon)
                                @php
                                    $jenisbiaya_nama = $list_jenisbiaya[$row_diskon['JenisBiayaID']]->Nama ?? '';
                                @endphp
                                <strong>-{{ $jenisbiaya_nama }}</strong><br>
                                <ul>
                                    @php
                                        $expl_discount = $row_diskon['ListMasterDiskonID'] ?? [];
                                    @endphp
                                    @foreach($expl_discount as $disc_id)
                                        @php
                                            $master_diskon = $list_master_diskon[$disc_id] ?? null;
                                            if ($master_diskon) {
                                                $nom = ($master_diskon->Tipe == 'nominal') ? rupiah($master_diskon->Jumlah) : $master_diskon->Jumlah . ' %';
                                            }
                                        @endphp
                                        @if($master_diskon)
                                            <li>{{ $master_diskon->Nama }} - {{ $nom }}</li>
                                        @endif
                                    @endforeach
                                </ul>
                            @endforeach
                        @endif
                    </td>
                    <td class="text-center"><b>{{ $row->NPM }}</b></td>
                    <td>
                        @if($Update == 'YA' && $row->StatusAktif == 1)
                            <a href="{{ url('setup_mahasiswa_diskon_sampai_lulus/view/'.$row->ID) }}">{{ $row->Nama }}</a>
                        @else
                            {{ $row->Nama }}
                        @endif
                    </td>
                    <td class="text-center">
                        @if($row->StatusAktif == 1)
                            Aktif
                        @else
                            Nonaktif <br>
                            <button type="button" onclick="aktifkan({{ $row->ID }})" class="btn btn-sm btn-primary">Aktifkan</button>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <!-- Nonaktif Modal -->
        <div id="hapus" class="modal" tabindex="-1" role="dialog" aria-labelledby="hapus" aria-hidden="true">
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
        $('#btnDelete').attr('title', 'Pilih dahulu data yang akan di nonaktifkan');
    }
}

// Form submit handler for nonaktif
$("#f_delete_setup_mahasiswa_diskon_sampai_lulus").submit(function() {
    $.ajax({
        type: "POST",
        url: $("#f_delete_setup_mahasiswa_diskon_sampai_lulus").attr('action'),
        data: {
            checkID: $("input:checkbox[name='checkID[]']:checked").map(function() {
                return this.value;
            }).get(),
            _token: "{{ csrf_token() }}"
        },
        dataType: 'json',
        success: function(response) {
            // Hide modal and cleanup
            $("#hapus").modal("hide");
            $("body").removeClass("modal-open");
            $(".modal-backdrop").remove();

            // Show success alert
            $(".alert-success").show();
            $(".alert-success-content").html("Data berhasil dinonaktifkan");
            window.setTimeout(function() { $(".alert-success").slideUp(); }, 10000);

            // Refresh filter
            filter();
        },
        error: function(data) {
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
        url: "{{ url('welcome/test') }}/?table=setup_mahasiswa_diskon_sampai_lulus&field=Nama",
        type: "POST",
        data: {
            checkID: $("input:checkbox[name='checkID[]']:checked").map(function() {
                return this.value;
            }).get(),
            _token: "{{ csrf_token() }}"
        },
        success: function(data) {
            $('.data_name').html(data);
        }
    });
});

// Aktifkan function
function aktifkan(id) {
    swal({
        'title': 'Peringatan',
        'text': 'Apakah Anda Yakin Mengaktifkan Kembali Beasiswa Ini ?',
        'type': 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Tidak',
    }).then(function() {
        $.ajax({
            url: "{{ url('setup_mahasiswa_diskon_sampai_lulus/aktifkan') }}/" + id,
            type: "GET",
            success: function(data) {
                swal('Berhasil', 'Beasiswa Berhasil Diaktikan kembali !', 'success');
                filter();
            },
            error: function(data) {
                swal('Gagal', 'Beasiswa Gagal Diaktikan kembali !', 'error');
                filter();
            }
        });
    });
}

// Initialize on load
show_btnDelete();
</script>

<script src="{{ asset('assets/theme/scripts/responsive-table.js') }}"></script>
