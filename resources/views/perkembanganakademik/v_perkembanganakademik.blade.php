@extends('layouts.template1')
@section('content')
<div class="card">
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-3">
                <label class="col-form-label"><h5>Program</h5></label>
                <select class="ProgramID form-control" onchange="filter()">
                    <option value=""> -- Lihat Semua -- </option>
                    @foreach(get_all('program') as $row)
                        <option value="{{ $row->ID }}">{{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label"><h5>Prodi</h5></label>
                <select class="ProdiID form-control" onchange="changekelas(); filter();">
                    <option value=""> -- Lihat Semua -- </option>
                    @foreach(get_all('programstudi') as $row)
                        <option value="{{ $row->ID }}">{{ get_field($row->JenjangID, 'jenjang') }} || {{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label"><h5>Status Mahasiswa</h5></label>
                <select class="StatusMhswID form-control" onchange="filter()">
                    <option value=""> -- Lihat Semua -- </option>
                    @foreach(get_all('statusmahasiswa') as $row)
                        <option value="{{ $row->ID }}">{{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label"><h5>Tahun Masuk</h5></label>
                <select class="TahunMasuk form-control" onchange="filter()">
                    <option value=""> -- Lihat Semua -- </option>
                    @foreach(DB::table('mahasiswa')->select('TahunMasuk')->distinct()->orderBy('TahunMasuk', 'DESC')->get() as $row)
                        @if($row->TahunMasuk)
                            <option value="{{ $row->TahunMasuk }}">{{ $row->TahunMasuk }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label"><h5>Semester Masuk</h5></label>
                <select class="SemesterMasuk form-control" onchange="filter()" id="SemesterMasuk">
                    <option value=""> -- Lihat Semua -- </option>
                    <option value="1">Ganjil</option>
                    <option value="2">Genap</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label"><h5>Kelas</h5></label>
                <select class="KelasID form-control" onchange="filter()" id="KelasID">
                    <option value=''>-- Anda belum memilih prodi --</option>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label class="col-form-label"><h5>Pencarian</h5></label>
                <input type="text" class="form-control keyword" onkeyup="filter()" placeholder="NIM / Nama Mahasiswa .." />
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div id="konten"></div>
    </div>
</div>

@push('scripts')
<script type="text/javascript">
function changekelas() {
    $.ajax({
        url: "{{ url('kelas/changekelas') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            ProdiID: $(".ProdiID").val()
        },
        success: function(data) {
            $(".KelasID").html(data);
        }
    });
}

function filter(url = null) {
    if (url == null) url = "{{ url('perkembanganakademik/search') }}";
    
    $.ajax({
        type: "POST",
        url: url,
        data: {
            _token: "{{ csrf_token() }}",
            ProgramID: $(".ProgramID").val(),
            ProdiID: $(".ProdiID").val(),
            KelasID: $(".KelasID").val(),
            TahunMasuk: $(".TahunMasuk").val(),
            SemesterMasuk: $(".SemesterMasuk").val(),
            StatusMhswID: $(".StatusMhswID").val(),
            keyword: $(".keyword").val()
        },
        beforeSend: function() {
            $("#konten").html('<div class="text-center"><i class="fa fa-spin fa-spinner"></i> Sedang memuat...</div>');
        },
        success: function(data) {
            $("#konten").html(data);
        }
    });
}
filter();
</script>
@endpush
@endsection
