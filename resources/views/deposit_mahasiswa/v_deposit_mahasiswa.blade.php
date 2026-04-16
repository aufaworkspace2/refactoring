@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="button-list">
                    @if($Create == 'YA')
                        <a href="{{ route('deposit_mahasiswa.add') }}" class="btn btn-bordered-primary waves-effect width-md waves-light"><i class="mdi mdi-plus"></i> Tambah Data</a>
                    @endif
                    <a onclick="excel()" class="btn btn-outline-success waves-effect waves-light"><i class="mdi mdi-download"></i> Excel</a>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="col-form-label mt-2"><h5 class="m-0">Program Kuliah</h5></label>
                <select class="ProgramID form-control" onchange="filter()" style="width: 100%">
                    <option value=""> -- Semua -- </option>
                    @foreach(DB::table('program')->get() as $row)
                        <option value="{{ $row->ID }}">{{ $row->ProgramID }} || {{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="col-form-label mt-2"><h5 class="m-0">Program Studi</h5></label>
                <select class="ProdiID form-control" onchange="filter();" style="width: 100%">
                    <option value="">-- Semua --</option>
                    @foreach(DB::table('programstudi')->get() as $row)
                        <option value="{{ $row->ID }}">{{ $row->ProdiID }} || {{ DB::table('jenjang')->where('ID', $row->JenjangID)->value('Nama') ?? '' }} || {{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="col-form-label mt-2"><h5 class="m-0">Angkatan</h5></label>
                <select class="TahunMasuk form-control" onchange="filter()" style="width: 100%">
                    <option value=""> -- Semua -- </option>
                    @foreach(DB::table('mahasiswa')->select('TahunMasuk')->distinct()->orderBy('TahunMasuk', 'DESC')->get() as $row)
                        <option value="{{ $row->TahunMasuk }}">{{ $row->TahunMasuk }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-12">
                <label class="col-form-label mt-2"><h5 class="m-0">Pencarian</h5></label>
                <input type="text" class="form-control keyword" id="keyword" onkeyup="filter()" placeholder="Cari NPM/Nama .." />
                <div class="clearfix"></div>
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

filter();

function excel(){
    var ProgramID = $(".ProgramID").val();
    var ProdiID = $(".ProdiID").val();
    var TahunMasuk = $(".TahunMasuk").val();
    var keyword = $(".keyword").val();

    var link = "?1";
    link += "&ProgramID="+ProgramID;
    link += "&ProdiID="+ProdiID;
    link += "&TahunMasuk="+TahunMasuk;
    link += "&keyword="+keyword;
    window.open("{{ route('deposit_mahasiswa.excel') }}/"+link,"_Blank");
}

function filter(url) {
    if(url == null)
        url = "{{ route('deposit_mahasiswa.search') }}";

    $.ajax({
        type: "POST",
        url: url,
        data: {
            ProgramID: $(".ProgramID").val(),
            ProdiID: $(".ProdiID").val(),
            TahunMasuk: $(".TahunMasuk").val(),
            keyword: $(".keyword").val()
        },
        beforeSend: function(data) {
            $("#konten").html("<center><h3><i class='fa fa-spin fa-spinner'></i> Loading ..</h3></center>");
        },
        success: function(data) {
            $("#konten").html(data);
        }
    });
    return false;
}
</script>
@endpush
