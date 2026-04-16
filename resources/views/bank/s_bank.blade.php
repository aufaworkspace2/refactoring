<p>{!! $total_row !!}</p>
<form id="f_delete_bank" action="{{ url('bank/delete') }}" >
    @csrf
    <div class="table-responsive">
        <table class="table table-hover table-bordered tablesorter">
            <thead class="bg-primary text-white">
                <tr>
                @if($Delete == 'YA')
                    <th width="2%" class="sorterfalse">
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkAll" id="checkAll" 
                                   onClick="checkall(this, document.forms.namedItem('f_delete_bank')); show_btnDelete();">
                            <label for="checkAll"></label>
                        </div>
                    </th>
                @endif
                    <th class="text-center">No.</th>
                    <th>{{ __('app.NamaBank') }}</th>
                    <th>{{ __('app.NoRekening') }}</th>
                    <th>{{ __('app.NamaPemilik') }}</th>
                    <th>Channel Pembayaran</th>
                </tr>
            </thead>
            <tbody>
            @php $no = $offset; $i = 0; @endphp
            @foreach($query as $row)
                @php
                    $bgcolor = (empty($row->ChannelPembayaranID_list)) ? '' : 'rgb(233, 255, 228) none repeat scroll 0% 0%';
                @endphp
                <tr class="bank_{{ $row->ID }}" style="background:{{ $bgcolor }}">
                @if($Delete == 'YA')
                    <td class="">
                        @if(empty($row->ChannelPembayaranID_list))
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkID[]" id="checkID{{ $i }}" 
                                   onclick="show_btnDelete()" value="{{ $row->ID }}">
                            <label for="checkID{{ $i }}"></label>
                        </div>
                        @php $i++; @endphp
                        @else
                        -
                        @endif
                    </td>
                @endif
                    <td class="text-center">{{ ++$no }}.</td>
                    <td>
                    @if($Update == 'YA')
                        <a href="{{ url('bank/view/'.$row->ID) }}">{{ $row->NamaBank }}</a>
                    @else
                        {{ $row->NamaBank }}
                    @endif
                    </td>
                    <td>{{ $row->NoRekening }}</td>
                    <td>{{ $row->NamaPemilik }}</td>
                    <td class="text-left">
                    @if($row->ChannelPembayaranID_list)
                        @php
                            $channel = [];
                            foreach(explode(",", $row->ChannelPembayaranID_list) as $row_exp) {
                                if($row_exp && isset($channel_bayar[$row_exp])) {
                                    $channel[$channel_bayar[$row_exp]->MetodePembayaranID][] = $channel_bayar[$row_exp];
                                }
                            }
                        @endphp
                        <ol>
                        @foreach($channel as $id_metode => $channel2)
                            <li><b>{{ $all_metode_bayar[$id_metode] }}</b></li>
                            <ul>
                            @foreach($channel2 as $row_channel)
                                <li>{{ $row_channel->Nama }}</li>
                            @endforeach
                            </ul>
                        @endforeach
                        </ol>
                    @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    
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
    
    <div class="row">
        <div class="col-md-12">
            {!! $link !!}
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
        $('#btnDelete').attr('title', '{{ __('app.Pilih dahulu data yang akan di hapus') }}');
    }
}

// Form submit handler
$("#f_delete_bank").submit(function(){
    $.ajax({
        type: "POST",
        url: $("#f_delete_bank").attr('action'),
        data: {
            checkID: $("input:checkbox[name='checkID[]']:checked").map(function(){
                return this.value;
            }).get(),
            _token: "{{ csrf_token() }}"
        },
        dataType: 'json',
        success: function(response){
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
        url: "{{ url('welcome/test') }}/?table=bank&field=NamaBank",
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
