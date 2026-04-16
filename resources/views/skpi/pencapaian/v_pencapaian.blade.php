@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="button-list">
                    @if($Create == 'YA')
                        <a href="{{ url('skpi/pencapaian/addPencapaian') }}" class="btn btn-bordered-primary waves-effect width-md waves-light">
                            <i class="mdi mdi-plus"></i> {{ __('app.add') }} Data
                        </a>
                    @endif
                    @if($Delete == 'YA')
                        <button class="btn btn-bordered-danger waves-effect width-md waves-light" id="btnDelete" data-placement="top" title="Silahkan pilih data terlebih dahulu." data-toggle="modal" disabled>
                            <i class="mdi mdi-delete"></i> {{ __('app.delete') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="col-form-label mt-2">
                    <h5 class="m-0">Program Studi</h5>
                </label>
                <select class="ProdiID form-control" onchange="filter(null, $('#key').val())">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @foreach($data_prodi as $row)
                        <option value="{{ $row['ID'] ?? '' }}">
                            {{ $row['jenjangNama'] ?? '' }} || {{ $row['Nama'] ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-8">
                <label class="col-form-label mt-2">
                    <h5 class="m-0">{{ __('app.keyword_legend') }}</h5>
                </label>
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
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

function filter(url) {
    if (url == null) {
        url = "{{ url('skpi/pencapaian/searchCapaian') }}";
    }

    $.ajax({
        type: "POST",
        url: url,
        data: {
            ProdiID: $(".ProdiID").val(),
            keyword: $(".keyword").val()
        },
        success: function(data) {
            $("#konten").html(data);
        }
    });
    return false;
}

function checkall(chkAll,checkid) {
    if (checkid != null) {
        if (checkid.length == null) checkid.checked = chkAll.checked;
        else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;

        $("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
        $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
    }
}
filter();
</script>
@endpush
