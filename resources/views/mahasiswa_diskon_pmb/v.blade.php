@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="button-list">
                    @if($Create == 'YA')
                        <a href="{{ url('mahasiswa_diskon_pmb/add') }}" class="btn btn-bordered-primary waves-effect  width-md waves-light">
                            <i class="mdi mdi-plus"></i> {{ __('app.add') }} Data
                        </a>
                    @endif
                    <button class="btn btn-bordered-danger waves-effect  width-md waves-light" id="btnDelete" data-placement="top" title="Silahkan pilih data terlebih dahulu." data-toggle="modal" disabled>
                        <i class="mdi mdi-delete"></i> {{ __('app.delete') }}
                    </button>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="col-form-label"><h5 class="mb-0">Tahun Akademik</h5></label>
                <select class="TahunID form-control" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="col-form-label"><h5 class="mb-0">Program</h5></label>
                <select class="ProgramID form-control" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="col-form-label"><h5 class="mb-0">{{ __('app.keyword_legend') }}</h5></label>
                <input type="text" class="form-control keyword" onkeyup="filter()" placeholder="{{ __('app.keyword') }} .."/>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div id="konten"></div>
    </div>
</div>

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
                <button type="button" onclick="hapusdata()" class="btn btn-danger waves-effect" >{{ __('app.delete') }}</button>
                <button type="button" class="btn btn-primary waves-effect waves-light" data-dismiss="modal">{{ __('app.close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

$(document).ready(function() {
    loadTahun();
    loadProgram();
    filter();
});

function loadTahun() {
    $.ajax({
        url: "{{ url('mahasiswa_diskon_pmb/get_tahun') }}",
        type: "GET",
        success: function(data) {
            $('.TahunID').html('<option value=""> -- {{ __('app.view_all') }} -- </option>' + data);
        }
    });
}

function loadProgram() {
    $.ajax({
        url: "{{ url('mahasiswa_diskon_pmb/get_program') }}",
        type: "GET",
        success: function(data) {
            $('.ProgramID').html('<option value=""> -- {{ __('app.view_all') }} -- </option>' + data);
        }
    });
}

function filter(url) {
    if(url == null) url = "{{ url('mahasiswa_diskon_pmb/search') }}";
    $.ajax({
        type: "POST",
        url: url,
        data: {
            TahunID : $(".TahunID").val(),
            ProgramID : $(".ProgramID").val(),
            keyword : $(".keyword").val(),
        },
        success: function(data) {
            $("#konten").html(data);
        }
    });
    return false;
}

$('.keyword').keyup(fncDelay(function (e) {
    filter();
}, 500));

function checkall(chkAll,checkid) {
    if (checkid != null) {
        if (checkid.length == null) checkid.checked = chkAll.checked;
        else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;
        $("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
        $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
    }
}

window.show_btnDelete = function(){
    i=0; hasil = false;
    while(document.getElementsByName('checkID[]').length > i) {
        var el = document.getElementById('checkID'+i);
        if(el && el.checked){ hasil = true; }
        i++;
    }
    if(hasil == true) {
        $('#btnDelete').removeAttr('disabled');
        $('#btnDelete').attr('href','#hapus');
    } else {
        $('#btnDelete').attr('disabled','disabled');
        $('#btnDelete').attr('href','#');
    }
}
show_btnDelete();

$("input:checkbox[name='checkID[]']").click(function(){
    if(this.checked == true){ $(this).parents('tr').addClass('table-danger'); }
    else { $(this).parents('tr').removeClass('table-danger'); }
});

function hapusdata(){
    $.ajax({
        type: "POST",
        url: "{{ url('mahasiswa_diskon_pmb/delete') }}",
        data: $("#f_delete_diskon").serialize(),
        success: function(data) {
            if(data.status == 1){
                $("#hapus").modal("hide");
                filter();
                $(".alert-success").show();
                $(".alert-success-content").html(data.message);
                window.setTimeout(function() { $(".alert-success").slideUp(); }, 10000);
            } else {
                alert(data.message);
            }
        },
        error: function(data) {
            alert('Terjadi kesalahan saat menghapus data');
        }
    });
}
</script>
@endpush
