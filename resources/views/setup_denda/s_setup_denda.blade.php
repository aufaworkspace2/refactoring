<p>{!! $total_row !!}</p>
<form id="f_delete_setup_denda" action="{{ url('setup_denda/delete') }}">
    @csrf
    <div class="table-responsive">
        <table class="table table-bordered mb-0 table-hover tablesorter">
            <thead class="bg-primary text-white">
                <tr>
                @if($Delete == 'YA')
                    <th width="2%">
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkAll" id="checkAll"
                                   onClick="checkall(this, document.forms.namedItem('f_delete_setup_denda')); show_btnDelete();">
                            <label for="checkAll"></label>
                        </div>
                    </th>
                @endif
                    <th class="text-center" width="2%">No.</th>
                    <th width="20%">Tahun Akademik</th>
                    <th width="20%">Program Kuliah</th>
                    <th width="20%">Program Studi</th>
                    <th width="20%">Angkatan</th>
                    <th width="20%">Komponen Biaya</th>
                    <th width="10%">Tipe</th>
                    <th width="10%">Telat Berapa Hari?</th>
                    <th width="10%">Jumlah</th>
                </tr>
            </thead>
            <tbody>
            @php
                $no = $offset;
                $i = 0;
                $default = [];

                $arr_prodi = [];
                foreach(DB::table('programstudi')->select('ID', 'ProdiID', 'Nama', 'JenjangID')->get() as $row_prodi) {
                    $arr_prodi[$row_prodi->ID] = $row_prodi;
                }

                $arr_nama_jenjang = [];
                foreach(get_all('jenjang') as $row_jenjang) {
                    $arr_nama_jenjang[$row_jenjang->ID] = $row_jenjang->Nama;
                }
            @endphp
            @foreach($query as $row)
                <tr class="setup_denda_{{ $row->ID }}">
                @if($Delete == 'YA')
                    <td class="align-middle">
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
                        @if($Update == 'YA')
                            <a href="{{ url('setup_denda/view/'.$row->ID) }}">
                                {{ get_field($row->TahunID, 'tahun') }}
                            </a>
                        @else
                            {{ get_field($row->TahunID, 'tahun') }}
                        @endif
                    </td>
                    <td class="text-center">
                        {{ ($row->ProgramID == '0') ? 'Semua Program Kuliah' : get_field($row->ProgramID, 'program') }}
                    </td>
                    <td class="text-center">
                        @php
                            $programstudi = $arr_prodi[$row->ProdiID] ?? null;
                        @endphp
                        @if($row->ProdiID === '0')
                            Semua Program Studi
                        @elseif($programstudi)
                            {{ ($arr_nama_jenjang[$programstudi->JenjangID] ?? '') . '-' . $programstudi->Nama }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        {{ ($row->TahunMasuk === '0') ? 'Semua Tahun Masuk' : $row->TahunMasuk }}
                    </td>
                    <td>{{ $row->NamaJenisBiaya }}</td>
                    <td>{{ ucwords($row->Tipe) }}</td>
                    <td>{{ $row->Hari }}</td>
                    <td style="font-weight: bold; text-align: right;">
                        @if ($row->Tipe == 'nominal')
                            {{ rupiah($row->Jumlah) }}
                        @else
                            {{ $row->Jumlah }} %
                        @endif
                    </td>
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
        $('#btnDelete').attr('title', '{{ __("app.Pilih dahulu data yang akan di hapus") }}');
    }
}

// Form submit handler
$("#f_delete_setup_denda").submit(function() {
    $.ajax({
        type: "POST",
        url: $("#f_delete_setup_denda").attr('action'),
        data: {
            checkID: $("input:checkbox[name='checkID[]']:checked").map(function() {
                return this.value;
            }).get(),
            _token: "{{ csrf_token() }}"
        },
        dataType: 'json',
        success: function(response) {
            if(response.status === 'success' && response.removed_ids) {
                response.removed_ids.forEach(function(id) {
                    var className = '.' + response.class_prefix + id;
                    $(className).remove();
                });
            }

            // Hide modal and cleanup
            $("#hapus").modal("hide");
            $("body").removeClass("modal-open");
            $(".modal-backdrop").remove();

            // Show success alert
            $(".alert-success").show();
            $(".alert-success-content").html("{{ __('app.alert-success-delete') }}");
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
        url: "{{ url('welcome/test') }}/?table=setup_denda&field=Nama",
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

// Initialize on load
show_btnDelete();
</script>

<script src="{{ asset('assets/theme/scripts/responsive-table.js') }}"></script>
