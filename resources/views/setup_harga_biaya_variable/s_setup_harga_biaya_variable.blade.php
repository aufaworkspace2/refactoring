<p>{!! $total_row !!}</p>
<form id="f_delete_setup_harga_biaya_variable" action="{{ url('setup_harga_biaya_variable/delete') }}">
    @csrf
    <div class="table-responsive">
        <table class="table table-bordered mb-0 table-hover tablesorter">
            <thead class="bg-primary text-white">
                <tr>
                @if($Delete == 'YA')
                    <th width="2%" class="sorterfalse">
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkAll" id="checkAll"
                                   onClick="checkall(this, document.forms.namedItem('f_delete_setup_harga_biaya_variable')); show_btnDelete();">
                            <label for="checkAll"></label>
                        </div>
                    </th>
                @endif
                    <th class="text-center" width="2%">No.</th>
                    <th width="20%">Program Kuliah</th>
                    <th width="20%">Program Studi</th>
                    <th width="20%">Angkatan</th>
                    <th width="20%">Jenis Pendaftaran</th>
                    <th width="20%">Jenis</th>
                    <th width="10%">Nominal</th>
                </tr>
            </thead>
            <tbody>
            @php
                $no = $offset;
                $i = 0;
                $default = [1];
            @endphp
            @foreach($query as $row)
                <tr class="setup_harga_biaya_variable_{{ $row->ID }}">
                @if($Delete == 'YA')
                    @if(!in_array($row->ID, $default))
                        <td class="align-middle">
                            <div class="checkbox checkbox-info">
                                <input type="checkbox" name="checkID[]" id="checkID{{ $i }}"
                                       onclick="show_btnDelete()" value="{{ $row->ID }}">
                                <label for="checkID{{ $i }}"></label>
                            </div>
                        </td>
                        @php $i++; @endphp
                    @else
                        <td>-</td>
                    @endif
                @endif
                    <td class="text-center">{{ ++$no }}.</td>
                    <td>
                        @if($Update == 'YA')
                            <a href="{{ url('setup_harga_biaya_variable/view/'.$row->ID) }}">
                                {{ ($row->ProgramID == '0') ? 'Semua Program Kuliah' : get_field($row->ProgramID, 'program') }}
                            </a>
                        @else
                            {{ ($row->ProgramID == '0') ? 'Semua Program Kuliah' : get_field($row->ProgramID, 'program') }}
                        @endif
                    </td>
                    <td style="text-align:center">
                        {{ ($row->ProdiID === '0') ? 'Semua Program Studi' : get_field($row->ProdiID, 'programstudi') }}
                    </td>
                    <td style="text-align:center">
                        {{ ($row->TahunMasuk === '0') ? 'Semua Tahun Masuk' : $row->TahunMasuk }}
                    </td>
                    <td style="text-align:center">
                        @if ($row->JenisPendaftaran != null || $row->JenisPendaftaran === '0')
                            @if ($row->JenisPendaftaran === '0')
                                Semua Jenis Pendaftaran
                            @else
                                {{ DB::table('jenis_pendaftaran')->where('Kode', $row->JenisPendaftaran)->value('Nama') ?? '' }}
                            @endif
                        @endif
                    </td>
                    <td style="text-align:center">
                        {{ $row->Jenis }}
                        @if($row->Jenis == 'Cuti')
                            <br><span class='badge badge-secondary'>{{ tgl($row->TanggalMulai, '02') }} s/d {{ tgl($row->TanggalSelesai, '02') }}</span>
                        @endif
                    </td>
                    <td style="text-align:left">
                        @if($row->Jenis == 'SKS')
                            @if($row->HitungPraktek == 1)
                                <span class='badge badge-secondary'>Per SKS Teori: {{ rupiah($row->Nominal) }}</span><br>
                                <span class='badge badge-secondary'>Per SKS Praktek: {{ rupiah($row->NominalPraktek) }}</span><br>
                            @else
                                <span class='badge badge-secondary'>Per SKS: {{ rupiah($row->Nominal) }}</span><br>
                            @endif
                            <span class='badge badge-secondary'>Paket: {{ rupiah($row->NominalPaket) }}</span><br>
                            <span class='badge badge-secondary'>Skripsi: {{ rupiah($row->NominalSkripsi) }}</span><br>
                        @else
                            <span class='badge badge-success font-size-13'>{{ rupiah($row->Nominal) }}</span>
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
$("#f_delete_setup_harga_biaya_variable").submit(function() {
    $.ajax({
        type: "POST",
        url: $("#f_delete_setup_harga_biaya_variable").attr('action'),
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

// Initialize on load
show_btnDelete();
</script>

<script src="{{ asset('assets/theme/scripts/responsive-table.js') }}"></script>
