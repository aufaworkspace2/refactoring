@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="button-list">
                    @if($Create == 'YA')
                        <a href="{{ url('setup_ukt/add') }}" class="btn btn-bordered-primary waves-effect width-md waves-light">
                            <i class="mdi mdi-plus"></i> {{ __('app.add') }} Data
                        </a>
                    @endif
                    <div class="btn-group">
                        <button type="button" class="btn btn-info dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false">
                            <i class="mdi mdi-printer mr-1"></i> Cetak <i class="mdi mdi-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a onclick="pdf()" href="javascript:void(0);" class="dropdown-item">
                                <i class="mdi mdi-printer"></i> {{ __('app.pdf') }}
                            </a>
                            <a onclick="excel()" href="javascript:void(0);" class="dropdown-item">
                                <i class="mdi mdi-printer"></i> {{ __('app.excel') }}
                            </a>
                        </div>
                    </div>
                    <button class="btn btn-bordered-danger waves-effect width-md waves-light"
                            id="btnDelete"
                            data-placement="top"
                            title="{{ __('app.Pilih dahulu data yang akan di hapus') }}"
                            data-toggle="modal"
                            disabled>
                        <i class="mdi mdi-delete"></i> {{ __('app.delete') }}
                    </button>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="col-form-label mt-2">
                    <h4 class="m-0">Program Kuliah</h4>
                </label>
                <select class="ProgramID form-control" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    <option value="0">-- Untuk Semua Program Kuliah --</option>
                    @foreach(get_all('program') as $row)
                        <option value="{{ $row->ID }}">{{ $row->ProgramID }} || {{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="col-form-label mt-2">
                    <h4 class="m-0">Program Studi</h4>
                </label>
                <select class="ProdiID form-control" onchange="filter();">
                    <option value="">-- {{ __('app.view_all') }} --</option>
                    <option value="0">-- Untuk Semua Program Studi --</option>
                    @foreach(get_all('programstudi') as $row)
                        <option value="{{ $row->ID }}">{{ $row->ProdiID }} || {{ get_field($row->JenjangID, 'jenjang') }} || {{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="col-form-label mt-2">
                    <h4 class="m-0">Angkatan</h4>
                </label>
                <select class="TahunMasuk form-control" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    <option value="0">-- Untuk Semua Tahun Masuk --</option>
                    @foreach(DB::table('mahasiswa')->select('TahunMasuk')->where('TahunMasuk', '!=', '')->groupBy('TahunMasuk')->get() as $row)
                        <option value="{{ $row->TahunMasuk }}">{{ $row->TahunMasuk }}</option>
                    @endforeach
                </select>
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
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

function filter(url) {
    if(url == null)
        url = "{{ url('setup_ukt/search') }}";

    $.ajax({
        type: "POST",
        url: url,
        data: {
            ProgramID: $(".ProgramID").val(),
            ProdiID: $(".ProdiID").val(),
            TahunMasuk: $(".TahunMasuk").val(),
            _token: "{{ csrf_token() }}"
        },
        beforeSend: function(data) {
            $("#konten").html("<center><h3><i class='fa fa-spin fa-spinner'></i> Loading.. </h3></center>");
        },
        success: function(data) {
            $("#konten").html(data);
        }
    });
    return false;
}

function pdf() {
    var ProgramID = $(".ProgramID").val();
    var ProdiID = $(".ProdiID").val();
    var TahunMasuk = $(".TahunMasuk").val();

    var link = 'ProgramID=' + ProgramID
                + '&ProdiID=' + ProdiID
                + '&TahunMasuk=' + TahunMasuk;

    window.open('{{ url("setup_ukt/pdf") }}/?' + link, "_Blank");
}

function excel() {
    var ProgramID = $(".ProgramID").val();
    var ProdiID = $(".ProdiID").val();
    var TahunMasuk = $(".TahunMasuk").val();

    var link = 'ProgramID=' + ProgramID
                + '&ProdiID=' + ProdiID
                + '&TahunMasuk=' + TahunMasuk;

    window.open('{{ url("setup_ukt/excel") }}/?' + link, "_Blank");
}

function checkall(chkAll, checkid) {
    if (checkid != null) {
        if (checkid.length == null) {
            checkid.checked = chkAll.checked;
        } else {
            for (i = 0; i < checkid.length; i++) {
                checkid[i].checked = chkAll.checked;
            }
        }

        $("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
        $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
    }
}

filter();
</script>
@endpush
