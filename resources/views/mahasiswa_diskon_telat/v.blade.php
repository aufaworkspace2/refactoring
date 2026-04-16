@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="button-list">
                    @if($Create == 'YA')
                        <a href="{{ url('mahasiswa_diskon_telat/add') }}" class="btn btn-bordered-primary waves-effect width-md waves-light">
                            <i class="mdi mdi-plus"></i> {{ __('app.add') }} Data
                        </a>
                    @endif
                    <button class="btn btn-bordered-danger waves-effect width-md waves-light"
                            id="btnDelete"
                            data-placement="top"
                            title="Silahkan pilih data terlebih dahulu."
                            data-toggle="modal"
                            disabled>
                        <i class="mdi mdi-delete"></i> Nonaktif
                    </button>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label class="col-form-label mt-2"><h4 class="m-0">Tahun Semester</h4></label>
                <select class="TahunID form-control" id="TahunID" onchange="filter()">
                    @php
                        $this_tahun = DB::table('tahun')->orderBy('TahunID', 'DESC')->get();
                    @endphp
                    @foreach($this_tahun as $raw)
                        <option value="{{ $raw->ID }}" {{ ($raw->ProsesBuka == '1') ? 'selected' : '' }}>
                            {{ $raw->Nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label mt-2"><h4 class="m-0">Program</h4></label>
                <select class="ProgramID form-control" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @foreach(get_all('program') as $row)
                        <option value="{{ $row->ID }}">{{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label mt-2"><h4 class="m-0">Program Studi</h4></label>
                <select class="ProdiID form-control" id="ProdiID" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @php
                        $query_jenjang = DB::table('jenjang')->select('ID', 'Nama')->get();
                        $jenjang = [];
                        foreach($query_jenjang as $row_jenjang) {
                            $jenjang[$row_jenjang->ID] = $row_jenjang->Nama;
                        }
                    @endphp
                    @foreach(get_all('programstudi') as $row)
                        <option value="{{ $row->ID }}">{{ $jenjang[$row->JenjangID] ?? '' }} || {{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label mt-2"><h4 class="m-0">Status</h4></label>
                <select class="StatusAktif form-control" id="StatusAktif" onchange="filter()">
                    <option value="1"> AKTIF </option>
                    <option value="0"> NONAKTIF </option>
                </select>
            </div>
            <div class="form-group col-md-12">
                <label class="col-form-label mt-2"><h4 class="m-0">{{ __('app.keyword_legend') }}</h4></label>
                <input type="text" class="form-control keyword" onkeyup="filter()" placeholder="{{ __('app.keyword') }} ..">
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
$(document).ready(function() {
    autocompletebyclass('TahunID','--Pilih Data--')
});


function filter(url) {
    if(url == null)
        url = "{{ url('mahasiswa_diskon_telat/search') }}";

    $.ajax({
        type: "POST",
        url: url,
        data: {
            keyword     : $(".keyword").val(),
            TahunID     : $(".TahunID").val(),
            ProdiID     : $(".ProdiID").val(),
            ProgramID   : $(".ProgramID").val(),
            StatusAktif : $(".StatusAktif").val(),
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
