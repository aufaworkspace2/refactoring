<p>{!! $total_row ?? '' !!}</p>
<?php
    use Illuminate\Support\Facades\Session;
    use Illuminate\Support\Facades\DB;

    $userID = Session::get('UserID');
    $Qlvl = DB::table('leveluser')->where('USerID', $userID)->first();
?>

<form id="f_delete_mahasiswa" action="{{ url('skpi/pencapaian/deletePencapaian') }}" >
    @csrf
    <div class="table-responsive">
        <table class="table table-hover table-bordered tablesorter">
            <thead class="bg-primary text-white">
                <tr>
                    @if($Delete == 'YA')
                    <th width="2%">
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.forms.namedItem('f_delete_mahasiswa')); show_btnDelete();">
                            <label for="checkAll"></label>
                        </div>
                    </th>
                    @endif

                    <th class="text-center" width="2%">No.</th>
                    <th class="text-center" width="7%">Kode</th>
                    <th class="text-center" width="10%">Kategori Pencapaian</th>
                    <th class="text-center" width="20%">Bahasa Indonesia</th>
                    <th class="text-center" width="20%">Bahasa Inggris</th>
                    <th class="text-center" width="15%">Program Studi</th>
                    <th class="text-center" width="8%">Lihat Mahasiswa</th>
                </tr>
            </thead>
            <tbody>
            @php $no=$offset ?? 0; $i=0; @endphp
            @foreach(($query ?? []) as $row)
                <tr class="capaian_{{ $row['ID'] ?? '' }}">
                    @if($Delete == 'YA')
                    <td>
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnDelete()" value="{{ $row['ID'] ?? '' }}">
                            <label for="checkID{{ $i }}"></label>
                        </div>
                    </td>
                    @endif

                    <td class="text-center">{{ ++$no }}.</td>
                    <td>
                        @if($Update == 'YA')
                            <a href="{{ url('skpi/pencapaian/viewPencapaian/'.$row['ID']) }}">{{ $row['Kode'] ?? '' }}</a>
                        @else
                            {{ $row['Kode'] ?? '' }}
                        @endif
                    </td>
                    <td>
                        {{ ($kategori[$row['KategoriPencapaianID']]['Nama'] ?? '') }}<br>
                        ({{ ($kategori[$row['KategoriPencapaianID']]['NamaInggris'] ?? '') }})
                    </td>
                    <td>{{ $row['Indonesia'] ?? '' }}</td>
                    <td>{{ $row['Inggris'] ?? '' }}</td>
                    <td>
                        @php
                            $prodiIDs = explode(',', $row['ProdiID'] ?? '');
                        @endphp
                        @foreach($prodiIDs as $prodiID)
                            @if(isset($prodi[$prodiID]))
                                - ({{ $prodi[$prodiID]['jenjangNama'] ?? '' }}) {{ $prodi[$prodiID]['Nama'] ?? '' }}<br>
                            @endif
                        @endforeach
                    </td>
                    <td>
                        <a href="javascript:void(0)" onclick="loadForm('{{ $row['ID'] ?? '' }}')" class="btn btn-primary btn-block" data-toggle="modal">
                            <i class="fa fa-eye"></i> Lihat
                        </a>
                    </td>
                </tr>
                @php $i++; @endphp
            @endforeach
            </tbody>
        </table>

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

        <div id="daftar_mahasiswa" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="daftar_mahasiswa" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="daftar_mahasiswa">Data Mahasiswa</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body">
                        <p class="data_mahasiswa"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger waves-effect waves-light" data-dismiss="modal">{{ __('app.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            {!! $link ?? '' !!}
        </div>
    </div>
</form>

<script>
tablesorter();

// Fungsi global untuk show_btnDelete
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

$("#f_delete_mahasiswa").submit(function(){
    $.ajax({
        type: "POST",
        url: $("#f_delete_mahasiswa").attr('action'),
        data: $("#f_delete_mahasiswa").serialize(),
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
            filter(null, 2);
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
        url: "{{ url('welcome/test') }}/?table=m_pencapaian&field=Kode",
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

function loadForm(id) {
    $.ajax({
        url: "{{ url('skpi/pencapaian/searchMahasiswa') }}",
        type: "POST",
        data: {CapaianID : id},
        success: function(data){
            $('.data_mahasiswa').html(data);
            $('#daftar_mahasiswa').modal('show');
        }
    });
}
</script>

<script src="{{ asset('assets/theme/scripts/responsive-table.js') }}"></script>
