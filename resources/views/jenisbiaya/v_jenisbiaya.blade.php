@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="button-list">
                    @if($Create == 'YA')
                        <a href="{{ url('jenisbiaya/add') }}" class="btn btn-bordered-primary waves-effect width-md waves-light">
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
            <div class="form-group col-md-6">
                <label class="col-form-label">
                    <h5 class="mb-0">Program</h5>
                </label>
                <select class="Program form-control" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @foreach(get_all('program') as $row)
                        <option value="{{ $row->ID }}">{{ $row->ProgramID }} || {{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-6">
                <label class="col-form-label">
                    <h5 class="mb-0">Prodi</h5>
                </label>
                <select class="Prodi form-control" onchange="filter()">
                    <option value="">-- {{ __('app.view_all') }} --</option>
                    @foreach(get_all('programstudi') as $row)
                        <option value="{{ $row->ID }}">{{ $row->ProdiID }} || {{ get_field($row->JenjangID, 'jenjang') }} || {{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="col-form-label">
                    <h5 class="mb-0">Tahun Masuk</h5>
                </label>
                <select class="TahunMasuk form-control" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @for($n = 0; $n <= 10; $n++)
                        <option>{{ date("Y") - $n }}</option>
                    @endfor
                </select>
            </div>
            <div class="form-group col-md-8">
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
        url = "{{ url('jenisbiaya/search') }}";
    }

    $.ajax({
        type: "POST",
        url: url,
        data: {
            keyword: $(".keyword").val(),
            Program: $(".Program").val(),
            Prodi: $(".Prodi").val(),
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

function pdf(){
    window.open('{{ url("jenisbiaya/pdf") }}/?keyword=' + $(".keyword").val(), "_Blank");
}

function excel(){
    window.open('{{ url("jenisbiaya/excel") }}/?keyword=' + $(".keyword").val(), "_Blank");
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
