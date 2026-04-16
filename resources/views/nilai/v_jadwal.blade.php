@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-3">
                <label class="col-form-label"><h5 class="mb-0">Program</h5></label>
                <select class="ProgramID form-control" onchange="changeprodi();" style="width: 100%">
                    @foreach($programs as $row)
                        <option value="{{ $row->ID }}">{{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label"><h5 class="mb-0">Tahun Akademik</h5></label>
                <select class="TahunID form-control" onchange="changeprodi();" style="width: 100%">
                    @foreach($tahuns as $raw)
                        <option {{ $raw->ProsesBuka == '1' ? 'selected' : '' }} value="{{ $raw->ID }}">{{ $raw->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label"><h5 class="mb-0">Program Studi</h5></label>
                <select class="ProdiID form-control select2" onchange="changekurikulum(); changekelas();" style="width: 100%">
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label"><h5 class="mb-0">Kurikulum</h5></label>
                <select class="KurikulumID form-control" onchange="changekonsentrasi();" style="width: 100%">
                    <option value=""> -- Semua Kurikulum -- </option>
                </select>
            </div>
        
            <div class="form-group col-md-4">
                <label class="col-form-label"><h5 class="mb-0">Konsentrasi</h5></label>
                <select class="KonsentrasiID form-control" onchange="changesemester();" style="width: 100%">
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="col-form-label"><h5 class="mb-0">Kelas</h5></label>
                <select class="KelasID form-control" onchange="filterNilai()" style="width: 100%">
                    <option value=""> -- Semua Kelas -- </option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="col-form-label"><h5 class="mb-0">Semester</h5></label>
                <select class="Semester form-control" onchange="filterNilai()" style="width: 100%">
                    <option value=""> -- Semua Semester -- </option>
                </select>
            </div>
        
            <div class="form-group col-md-6">
                <label class="col-form-label"><h5 class="mb-0">Dosen</h5></label>
                <select class="DosenID form-control" id="DosenID" onchange="filterNilai()" style="width: 100%" multiple>
                    @foreach($dosens as $row)
                        <option value="{{ $row->ID }}">{{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-6">
                <label class="col-form-label"><h5 class="mb-0">Kata Kunci</h5></label>
                <input type="text" class="form-control keyword" onkeyup="filterNilai()" placeholder="Masukan kata kunci untuk melakukan pencarian .." />
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div id="kontenNilai">
            <center>Pilih filter untuk menampilkan data</center>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
    changeprodi();
    autocomplete('DosenID')
    autocompletebyclass('ProdiID')
    filterNilai()
});

function changeprodi(){
    $.ajax({
        type: "POST",
        url: "{{ url('programstudi/changeprodi') }}",
        data: {
            _token: "{{ csrf_token() }}",
            ProgramID : $(".ProgramID").val(),
            TahunID : $(".TahunID").val()
        },
        success: function(data) {
            $(".ProdiID").html(data);
            changekurikulum();
        }
    });
}

function changekonsentrasi(){    
    $.ajax({
        url:"{{ url('detailkurikulum/changekonsentrasi') }}",
        type:"POST",
        data: {
            _token: "{{ csrf_token() }}",
            ProdiID : $(".ProdiID").val()
        },
        success: function(data){
            $(".KonsentrasiID").html(data);
            changekelas();
        }
    });
}

function changekelas(){    
    $.ajax({
        url:"{{ url('kelas/changekelas') }}",
        type:"POST",
        data: {
            _token: "{{ csrf_token() }}",
            ProdiID : $(".ProdiID").val()
        },
        success: function(data){
            $(".KelasID").html(data);
            changesemester();
        }
    });
}

function changekurikulum(){    
    $.ajax({
        url:"{{ url('kurikulum/onchange') }}",
        type:"POST",
        data: {
            _token: "{{ csrf_token() }}",
            ProdiID : $(".ProdiID").val(),
            ProgramID : $(".ProgramID").val()
        },
        success: function(data){
            $(".KurikulumID").html(data);
            changekonsentrasi();
        }
    });
}

function changesemester(){    
    $.ajax({
        url:"{{ url('jadwal/changesemester') }}",
        type:"POST",
        data: {
            _token: "{{ csrf_token() }}",
            prodiID : $(".ProdiID").val(),
            programID : $(".ProgramID").val(),
            kurikulumID : $(".KurikulumID").val(),
            konsentrasiID : $(".KonsentrasiID").val()
        },
        success: function(data){
            $(".Semester").html(data);
            filterNilai();
        }
    });
}

function filterNilai() {
    $.ajax({
        type: "POST",
        url: "{{ url('nilai/search') }}",
        beforeSend: function() {
            $('#kontenNilai').html('<center><i class="fa fa-spin fa-spinner"></i> Silahkan Tunggu...</center>');
        },
        data: {
            _token: "{{ csrf_token() }}",
            tahunID : $(".TahunID").val(),
            prodiID : $(".ProdiID").val(),
            kurikulumID : $(".KurikulumID").val(),
            programID : $(".ProgramID").val(),
            konsentrasiID : $(".KonsentrasiID").val(),
            kelasID : $(".KelasID").val(),
            semester : $(".Semester").val(),
            keyword : $(".keyword").val(),
            dosenID : $(".DosenID").val(),
        },
        success: function(data) {
            $("#kontenNilai").html(data);
        }
    });
    return false;
}

function templateNilai(){
    if($(".Semester").val()) {
        var params = $.param({
            TahunID: $(".TahunID").val(),
            ProdiID: $(".ProdiID").val(),
            KurikulumID: $(".KurikulumID").val(),
            ProgramID: $(".ProgramID").val(),
            KonsentrasiID: $(".KonsentrasiID").val(),
            KelasID: $(".KelasID").val(),
            Semester: $(".Semester").val(),
            keyword: $(".keyword").val()
        });
        window.open("{{ url('nilai/excel_nilai') }}?" + params, "_BLANK");
    } else {
        swal("Peringatan", "Semester harus dipilih jika ingin mendownload template !", "warning");
    }
}
</script>
@endpush
