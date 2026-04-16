<p>{!! $total_row !!}</p>
<form id="f_delete_master_diskon" action="{{ url('master_diskon/delete') }}" >
    @csrf
    <div class="table-responsive">
        <table class="table table-bordered mb-0 table-hover tablesorter">
            <thead class="bg-primary text-white">
                <tr>
                @if($Delete == 'YA')
                    <th width="2%">
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkAll" id="checkAll"
                                   onClick="checkall(this, document.forms.namedItem('f_delete_master_diskon')); show_btnDelete();">
                            <label for="checkAll"></label>
                        </div>
                    </th>
                @endif
                    <th class="text-center" width="2%">No.</th>
                    <th>Nama Diskon</th>
                    <th>Prodi</th>
                    <th style="width: 10%;">Tipe</th>
                    <th>Potongan</th>
                    <th>Kategori</th>
                    <th>Jenis</th>
                </tr>
            </thead>
            <tbody>
            @php
                $no = $offset;
                $i = 0;
                $arr_prodi = [];
                $arr_nama_jenjang = [];
            @endphp
            @foreach($query as $row)
                <tr class="master_diskon_{{ $row->ID }}">
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
                    <td class="text-center">{{ ++$no }}.</td>
                    <td>
                        @if($Update == 'YA')
                            <a href="{{ url('master_diskon/view/'.$row->ID) }}">{{ $row->Nama }}</a>
                        @else
                            {{ $row->Nama }}
                        @endif
                    </td>
                    <td>
                        @if($row->ProdiID)
                            @php
                                if(!isset($arr_prodi[$row->ProdiID])){
                                    $arr_prodi[$row->ProdiID] = DB::table('programstudi')->where('ID', $row->ProdiID)->first();
                                }
                                $prodi = $arr_prodi[$row->ProdiID];

                                if($prodi && !isset($arr_nama_jenjang[$prodi->JenjangID])){
                                    $arr_nama_jenjang[$prodi->JenjangID] = get_field($prodi->JenjangID, 'jenjang');
                                }
                                $nama_jenjang = $prodi ? ($arr_nama_jenjang[$prodi->JenjangID] ?? '') : '';
                                echo $nama_jenjang . ' | ' . ($prodi->Nama ?? '');
                            @endphp
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ ucwords($row->Tipe) }}</td>
                    <td style="font-weight: bold; text-align: right;">
                        @if ($row->Tipe == 'nominal')
                            {{ rupiah($row->Jumlah) }}
                        @else
                            {{ $row->Jumlah }} %
                        @endif
                    </td>
                    <td>
                        @if($row->BiayaAwalID)
                            {{ get_field($row->BiayaAwalID, 'biaya_awal') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($row->JenisDiskon == 'potong_dari_total')
                            Potong Dari Nominal Tagihan
                        @elseif($row->JenisDiskon == 'potong_dari_sisa')
                            Pembayaran Diskon
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="row">
        <div class="col-md-12">
            {!! $link !!}
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="row">
        <div id="hapus" class="modal" tabindex="-1" role="dialog" aria-labelledby="hapus" aria-hidden="true">
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
</form>

<script>
tablesorter();

// Global function for show_btnDelete
window.show_btnDelete = function(){
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
        $('#btnDelete').attr('disabled','disabled');
        $('#btnDelete').attr('href','#');
        $('#btnDelete').attr('title', '{{ __("app.Pilih dahulu data yang akan di hapus") }}');
    }
}

// Form submit handler
$("#f_delete_master_diskon").submit(function(){
    $.ajax({
        type: "POST",
        url: $("#f_delete_master_diskon").attr('action'),
        data: {
            checkID: $("input:checkbox[name='checkID[]']:checked").map(function(){
                return this.value;
            }).get(),
            _token: "{{ csrf_token() }}"
        },
        dataType: 'json',
        success:function(response){
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
        error: function(data){
            $(".alert-error").show();
            $(".alert-error-content").html("{{ __('app.alert-error-delete') }}");
            window.setTimeout(function() { $(".alert-error").slideUp('slow'); }, 6000);
        }
    });
    return false;
});

// Checkbox row highlight
$("input:checkbox[name='checkID[]']").click(function(){
    if(this.checked == true){
        $(this).parents('tr').addClass('table-danger');
    } else {
        $(this).parents('tr').removeClass('table-danger');
    }
});

// Get selected data names for modal
$('#btnDelete').click(function(){
    $.ajax({
        url: "{{ url('welcome/test') }}/?table=master_diskon&field=Nama",
        type: "POST",
        data: {
            checkID: $("input:checkbox[name='checkID[]']:checked").map(function(){
                return this.value;
            }).get(),
            _token: "{{ csrf_token() }}"
        },
        success: function(data){
            $('.data_name').html(data);
        }
    });
});

// Initialize on load
show_btnDelete();
</script>

<script src="{{ asset('assets/theme/scripts/responsive-table.js') }}"></script>
