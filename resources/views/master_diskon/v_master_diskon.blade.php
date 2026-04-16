@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="button-list">
                    @if($Create == 'YA')
                        <a href="{{ url('master_diskon/add') }}" class="btn btn-bordered-primary waves-effect width-md waves-light">
                            <i class="mdi mdi-plus"></i> {{ __('app.add') }} Data
                        </a>
                    @endif
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
            <div class="form-group col-md-3">
                <label class="col-form-label">
                    <h5 class="mb-0">Tipe Diskon</h5>
                </label>
                <select class="TipeDiskon form-control" onchange="filter()">
                    <option value="0"> -- {{ __('app.view_all') }} -- </option>
                    <option value="nominal">Nominal</option>
                    <option value="persen">Persen</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label">
                    <h5 class="mb-0">Kategori Diskon</h5>
                </label>
                <select class="BiayaAwalID form-control" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @foreach(DB::table('biaya_awal')->where('kategori_diskon', '1')->get() as $raw)
                        <option value="{{ $raw->ID }}">{{ $raw->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label">
                    <h5 class="mb-0">Program Studi</h5>
                </label>
                <select class="ProdiID form-control" onchange="filter()">
                    <option value="">-- {{ __('app.view_all') }} --</option>
                    @foreach(get_all('programstudi') as $row)
                        <option value="{{ $row->ID }}">{{ $row->ProdiID }} || {{ get_field($row->JenjangID, 'jenjang') }} || {{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label">
                    <h5 class="mb-0">{{ __('app.keyword_legend') }}</h5>
                </label>
                <input type="text" class="form-control keyword" onkeyup="filter()" placeholder="{{ __('app.keyword') }} .." />
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
    if(url == null) {
        url = "{{ url('master_diskon/search') }}";
    }

    $.ajax({
        type: "POST",
        url: url,
        data: {
            keyword: $(".keyword").val(),
            Tipe: $(".TipeDiskon").val(),
            BiayaAwalID: $(".BiayaAwalID").val(),
            ProdiID: $(".ProdiID").val(),
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
