<table>
    <tr>
        <td style="border:none;">
            <span class="label label-success" style="background:#BCF5A9;border:1px solid #000;border-radius:3px;">&nbsp;&nbsp;&nbsp;</span>
            &nbsp; {{ __('Sudah ada relasi dengan Tingkat Kegiatan (Tidak Bisa Dihapus)') }}<br>
        </td>
        <td style="border:none;">
            <span class="label" style="background:#fff;border:1px solid #000;border-radius:3px;">&nbsp;&nbsp;&nbsp;</span>
            &nbsp; {{ __('Belum ada relasi dengan Tingkat Kegiatan (Bisa Dihapus)') }}
        </td>
    </tr>
</table>
<p class="mt-2">{!! $total_row ?? '' !!}</p>
<form id="f_delete_jenis_kategori" action="{{ url('jenis_kategori_skpi/delete') }}" >
    @csrf
    <div class="table-responsive">
        <table class="table table-hover table-bordered tablesorter">
            <thead class="bg-primary text-white">
                <tr>
                    @if(($Delete ?? '') == 'YA')
                    <th width="2%">
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkAll" id="checkAll" 
                                   onClick="checkall(this,document.forms.namedItem('f_delete_jenis_kategori')); show_btnDelete();">
                            <label for="checkAll"></label>
                        </div>
                    </th>
                    @endif
                    <th class="text-center" width="2%">No.</th>
                    <th width="93%">{{ __('Nama') }}</th>
                </tr>
            </thead>
            <tbody>
                @php $no=$offset ?? 0; $i=0; @endphp
                @foreach(($query ?? []) as $row)
                    @php
                        $hasRelation = \App\Services\JenisKategoriSkpiService::class;
                        $service = app($hasRelation);
                        $hasRel = $service->hasRelation($row['ID'] ?? 0);
                    @endphp
                    <tr class="jenis_kategori_{{$row['ID'] ?? ''}}" @if($hasRel) bgcolor="#BCF5A9" @endif>
                        @if(($Delete ?? '') == 'YA' && !$hasRel)
                            <td>
                                <div class="checkbox checkbox-info">
                                    <input type="checkbox" name="checkID[]" id="checkID{{$i}}" 
                                           onclick="show_btnDelete()" value="{{$row['ID'] ?? ''}}">
                                    <label for="checkID{{$i}}"></label>
                                </div>
                            </td>
                            @php $i++; @endphp
                        @else
                            <td></td>
                        @endif

                        <td class="text-center">{{++$no}}.</td>
                        <td>
                            @if(($Update ?? '') == 'YA')
                                <a href="{{ url('jenis_kategori_skpi/view/'.$row['ID']) }}">{{$row['Nama'] ?? ''}}</a>
                            @else
                                {{$row['Nama'] ?? ''}}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="row">
        <div class="col-md-2">
            {!! $link ?? '' !!}
        </div>
    </div>
    <div class="row">
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
</form>

<script>
tablesorter();

// Fungsi global untuk show_btnDelete agar bisa dipanggil dari inline onclick
window.show_btnDelete = function(){
    i=0; hasil = false;
    while(document.getElementsByName('checkID[]').length > i) {
        var checkname = document.getElementById('checkID'+i).checked;
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
        $('#btnDelete').attr('disabled','disabled');
        $('#btnDelete').attr('href','#');
        $('#btnDelete').attr('title', '{{ __('app.Pilih dahulu data yang akan di hapus') }}');
    }
}

$("#f_delete_jenis_kategori").submit(function(){
    $.ajax({
        type: "POST",
        url: $("#f_delete_jenis_kategori").attr('action'),
        data: $("#f_delete_jenis_kategori").serialize(),
        dataType: 'json',
        success:function(response){
            // Remove rows based on response
            if(response.status === 'success' && response.removed_ids) {
                response.removed_ids.forEach(function(id) {
                    var className = '.' + response.class_prefix + id;
                    $(className).remove();
                });
            }
            
            $("#hapus").modal("hide");

            $(".alert-success").animate({ backgroundColor: "#dff0d8" }, 1000);
            $(".alert-success").animate({ backgroundColor: "#b6ef9e" }, 1000);
            $(".alert-success").animate({ backgroundColor: "#dff0d8" }, 1000);
            $(".alert-success").animate({ backgroundColor: "#b6ef9e" }, 1000);

            $(".alert-success").show();
            $(".alert-success-content").html("{{ __('app.alert-success-delete') }}");
            window.setTimeout(function() { $(".alert-success").slideUp(); }, 10000);
            
            // Refresh filter to update pagination
            filter();
        },
        error: function(data){
            $(".alert-error").animate({ backgroundColor: "#ec9b9b" }, 1000);
            $(".alert-error").animate({ backgroundColor: "#df3d3d" }, 1000);
            $(".alert-error").animate({ backgroundColor: "#ec9b9b" }, 1000);
            $(".alert-error").animate({ backgroundColor: "#df3d3d" }, 1000);

            $(".alert-error").show();
            $(".alert-error-content").html("{{ __('app.alert-error-delete') }}");
            window.setTimeout(function() { $(".alert-error").slideUp('slow'); }, 6000);
        }
    });
    return false;
});

// Panggil show_btnDelete saat pertama kali load
show_btnDelete();

$("input:checkbox[name='checkID[]']").click(function(){
    if(this.checked == true){
        $(this).parents('tr').addClass('table-danger');
    } else {
        $(this).parents('tr').removeClass('table-danger');
    }
});

$('#btnDelete').click(function(){
    $.ajax({
        url: "{{ url('welcome/test') }}/?table=jenis_kategori_kegiatan&field=Nama",
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
</script>

<script src="{{ asset('assets/theme/scripts/responsive-table.js') }}"></script>
