@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="col-md-12">
            <h4>Filter Tagihan</h4>
        </div>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-12">
                <label class="col-form-label" for="PeriodeID">Pilih Tahun *</label>
                <div class="controls">
                    <select data-post="true" name="PeriodeID" id="PeriodeID" class="form-control PeriodeID" onchange="changeangkatan(); changeprogram(); changeprodi();">
                        <option value="">-- Pilih Tahun --</option>
                        @foreach(DB::table('tahun')->orderBy('ID', 'DESC')->get() as $r)
                            <option value="{{ $r->ID }}" {{ $r->ProsesBuka == 1 ? 'selected' : '' }}>{{ $r->Nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group col-md-12">
                <label class="col-form-label" for="ProgramID">Program Kuliah *</label>
                <div class="controls">
                    <select data-post="true" name="ProgramID" id="ProgramID" class="form-control ProgramID" onchange="changemahasiswa()"></select>
                </div>
            </div>
            <div class="form-group col-md-12">
                <label class="col-form-label" for="ProdiID">Programstudi *</label>
                <div class="controls">
                    <select data-post="true" name="ProdiID" id="ProdiID" class="form-control ProdiID" onchange="changemahasiswa()"></select>
                </div>
            </div>
            <div class="form-group col-md-12">
                <label class="col-form-label" for="Angkatan">Angkatan *</label>
                <div class="controls">
                    <select data-post="true" name="Angkatan" id="Angkatan" class="form-control Angkatan" onchange="changemahasiswa()"></select>
                </div>
            </div>
            <div class="form-group col-md-12">
                <label class="col-form-label" for="JenisBiayaID">Komponen Biaya *</label>
                <div class="controls">
                    <select data-post="true" name="JenisBiayaID" id="JenisBiayaID" class="form-control JenisBiayaID" onchange="changemahasiswa()">
                        <option value="">-- Lihat Semua Komponen --</option>
                        @foreach(DB::table('jenisbiaya')->orderBy('Urut', 'ASC')->get() as $row)
                            <option value="{{ $row->ID }}">{{ $row->Nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group col-md-12">
                <label class="col-form-label" for="MhswID">Mahasiswa *</label>
                <div class="controls">
                    <select data-post="true" name="MhswID" id="MhswID" class="span6"></select>
                </div>
            </div>
            <div class="form-group col-md-12">
                <div class="controls">
                    <button class="btn btn-primary" type="button" onclick="filter();"><i class="fa fa-search"></i> Cari Data</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div id="konten"></div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

changeprodi();
changeprogram();
changeangkatan();

function filter(url) {
    if(url == null)
        url = "{{ route('batal_tagihan.searchTagihan') }}";

    $.ajax({
        type: "POST",
        url: url,
        data: {
            PeriodeID: $("#PeriodeID").val(),
            ProdiID: $("#ProdiID").val(),
            Angkatan: $("#Angkatan").val(),
            ProgramID: $("#ProgramID").val(),
            MhswID: $("#MhswID").val(),
            JenisBiayaID: $("#JenisBiayaID").val()
        },
        beforeSend: function(data) {
            $("#konten").html("<center><h3><i class='fa fa-spin fa-spinner'></i> Sedang Memuat Data ...</h3></center>");
        },
        success: function(data) {
            $("#konten").html(data);
        }
    });
    return false;
}

function changeprodi(){
    $.ajax({
        url: "{{ route('batal_tagihan.changeProdi') }}",
        type: "post",
        data: { TahunID: $('#PeriodeID').val() },
        success: function(data){
            $('#ProdiID').html(data);
            changemahasiswa();
        }
    });
}

function changeprogram(){
    $.ajax({
        url: "{{ route('batal_tagihan.changeProgram') }}",
        type: "post",
        data: { TahunID: $('#PeriodeID').val() },
        success: function(data){
            $('#ProgramID').html(data);
            changemahasiswa();
        }
    });
}

function changeangkatan(){
    $.ajax({
        url: "{{ route('batal_tagihan.changeAngkatan') }}",
        type: "post",
        data: { TahunID: $('#PeriodeID').val() },
        success: function(data){
            $('#Angkatan').html(data);
            changemahasiswa();
        }
    });
}

function changemahasiswa(){
    $.ajax({
        url: "{{ route('batal_tagihan.changeMahasiswa') }}",
        type: "post",
        data: {
            TahunID: $('#PeriodeID').val(),
            ProdiID: $('#ProdiID').val(),
            Angkatan: $('#Angkatan').val(),
            ProgramID: $('#ProgramID').val(),
            JenisBiayaID: $('#JenisBiayaID').val()
        },
        success: function(data){
            $('#MhswID').html(data);
            autocomplete('MhswID','--Pilih Data--');
        }
    });
}

function checkall(chkAll, checkid) {
    if (checkid != null) {
        if (checkid.length == null) checkid.checked = chkAll.checked;
        else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;

        $("input:checkbox[name='checkID[]']").parents('tr').removeClass('checked_tabel');
        $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('checked_tabel');
    }
}

function show_btnDelete(){
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
        $('#btnDelete').attr('title', 'Pilih dahulu data yang akan di hapus');
    }
}
show_btnDelete();

$("input:checkbox[name='checkID[]']").click(function(){
    if(this.checked == true){
        $(this).parents('tr').addClass('checked_tabel');
    } else {
        $(this).parents('tr').removeClass('checked_tabel');
    }
});

// Handle form submit with AJAX - prevent default form submission
$(document).on('submit', '#f_delete_mahasiswa', function(e){
    e.preventDefault();
    e.stopImmediatePropagation();
    
    var form = $(this);
    
    $.ajax({
        type: "POST",
        url: form.attr('action'),
        data: form.serialize(),
        success: function(data){
            if(data.status == 1) {
                alertsuccess(data.message);
                $('#hapus').modal('hide');
                setTimeout(function() {
                    filter();
                }, 500);
            } else {
                swal('Pemberitahuan', data.message, 'error');
            }
        },
        error: function(data){
            swal('Pemberitahuan', 'Maaf, data gagal diproses!.', 'error');
        }
    });
    
    return false;
});
</script>
@endpush
