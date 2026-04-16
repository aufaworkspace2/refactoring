<p>{!! $total_row !!}</p>
<form id="f_delete_jenisbiaya" action="{{ url('jenisbiaya/delete') }}" >
    @csrf
    <div class="table-responsive">
        <table class="table table-bordered mb-0 table-hover tablesorter">
            <thead class="bg-primary text-white">
                <tr>
                @if($Delete == 'YA')
                    <th width="2%" class="sorterfalse">
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkAll" id="checkAll"
                                   onClick="checkall(this, document.forms.namedItem('f_delete_jenisbiaya')); show_btnDelete();">
                            <label for="checkAll"></label>
                        </div>
                    </th>
                @endif
                    <th class="text-center" width="2%">No.</th>
                    <th class="text-center">{{ __('app.Nama') }}</th>
                    <th class="text-center" style="width:20%;">{{ __('app.frekuensi') }}</th>
                    <th class="text-center" style="width:20%;">Program</th>
                    <th class="text-center" style="width:20%;">Prodi</th>
                    <th class="text-center" style="width:20%;">Tahun Masuk</th>
                    <th class="text-center" style="width:20%;">Ada Detail ?</th>
                </tr>
            </thead>
            <tbody>
            @php
                $no = $offset;
                $i = 0;
                $default = [32,33,56,57,68,49,69,71,72,73,74,59];
                $default_kode = ['TSKSHTN','RKGRPL'];
            @endphp
            @foreach($query as $row)
                @php
                    $tagihan_mahasiswaID = DB::table('tagihan_mahasiswa')
                        ->where('JenisBiayaID', $row->ID)
                        ->first();

                    if(isset($tagihan_mahasiswaID->ID)) {
                        $bg = "rgb(233, 255, 228) none repeat scroll 0% 0%";
                        $disabled = 'disabled';
                    } else {
                        $bg = "";
                        $disabled = '';
                    }
                @endphp
                <tr class="jenisbiaya_{{ $row->ID }}" style="background:{{ $bg }}">
                @if($Delete == 'YA')
                    @if(!in_array($row->ID, $default) && !in_array($row->Kode, $default_kode) && empty($tagihan_mahasiswaID))
                        <td>
                            <div class="checkbox checkbox-info">
                                <input type="checkbox" name="checkID[]" id="checkID{{ $i }}"
                                       onclick="show_btnDelete()" value="{{ $row->ID }}">
                                <label for="checkID{{ $i }}"></label>
                            </div>
                        </td>
                        @php $i++; @endphp
                    @else
                        <td class='text-center'>-</td>
                    @endif
                @endif
                    <td class="text-center">{{ ++$no }}.</td>
                    <td>
                        @if($Update == 'YA' && !in_array($row->ID, $default) && !in_array($row->Kode, $default_kode))
                            <a href="{{ url('jenisbiaya/view/'.$row->ID) }}">{{ $row->Nama }}</a>
                        @else
                            {{ $row->Nama }}
                        @endif
                    </td>
                    <td>{{ $row->frekuensi }}</td>
                    <td>
                        @if($row->Program != NULL && $row->Program != 0)
                            @php
                                $arr_Program = explode(',', $row->Program);
                                $count_Program = count($arr_Program);
                            @endphp
                            @foreach ($arr_Program as $key => $value)
                                {{ get_field($value, 'program') }}
                                @if($key != $count_Program - 1)
                                    <br>
                                @endif
                            @endforeach
                        @else
                            Semua Program Kuliah
                        @endif
                    </td>
                    <td>
                        @if($row->Prodi != NULL && $row->Prodi != 0)
                            @php
                                $arr_prodi = explode(',', $row->Prodi);
                                $count_prodi = count($arr_prodi);
                            @endphp
                            @foreach ($arr_prodi as $key => $value)
                                {{ get_field($value, 'programstudi') }}
                                @if($key != $count_prodi - 1)
                                    <br>
                                @endif
                            @endforeach
                        @else
                            Semua Program Studi
                        @endif
                    </td>
                    <td>
                        @if($row->TahunMasuk != NULL && $row->TahunMasuk != 0)
                            @php
                                $arr_TahunMasuk = explode(',', $row->TahunMasuk);
                                $count_TahunMasuk = count($arr_TahunMasuk);
                            @endphp
                            @foreach ($arr_TahunMasuk as $key => $value)
                                {{ $value }}
                                @if($key != $count_TahunMasuk - 1)
                                    <br>
                                @endif
                            @endforeach
                        @else
                            Semua TahunMasuk
                        @endif
                    </td>
                    <td>
                        @if(count($get[$row->ID] ?? []) > 0)
                            @php
                                $nama = str_replace("'","\'",$row->Nama);
                            @endphp
                            <a onclick="load_modal('Lihat Detail Komponen Biaya {{ $nama }}','{{ url('jenisbiaya/lihat_detail/'.$row->ID) }}')">Ada</a>
                        @else
                            Tidak Ada
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
$("#f_delete_jenisbiaya").submit(function(){
    $.ajax({
        type: "POST",
        url: $("#f_delete_jenisbiaya").attr('action'),
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
        url: "{{ url('welcome/test') }}/?table=jenisbiaya&field=Nama",
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

function toggle_detail(id){
    $('#detail_'+id).toggle();
    $('#toggle_'+id).text(function(i, text){
          return text === "Tampilkan lebih banyak" ? "Tampilkan lebih sedikit" : "Tampilkan lebih banyak";
    });
}

// Initialize on load
show_btnDelete();
</script>

<script src="{{ asset('assets/theme/scripts/responsive-table.js') }}"></script>
