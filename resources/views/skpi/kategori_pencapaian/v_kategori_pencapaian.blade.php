@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="button-list">
                    @if($Create == 'YA')
                        <a href="{{ url('skpi/kategoriPencapaian/addKategoriPencapaian') }}" id="tambah" class="btn btn-bordered-primary waves-effect waves-light">
                            <i class="mdi mdi-plus"></i> {{ __('app.add') }} Data
                        </a>
                    @endif
                    @if($Delete == 'YA')
                        <button type="button" disabled class="btn btn-bordered-danger waves-effect waves-light" id="btnDelete" data-placement="top" title="Silahkan pilih data terlebih dahulu." data-toggle="modal">
                            <i class="mdi mdi-delete"></i> {{ __('app.delete') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-12">
                <label class="col-form-label">
                    <h5 class="mb-0">{{ __('app.keyword_legend') }}</h5>
                </label>
                <input type="text" class="form-control keyword" onkeyup="filter(null, $('#key').val())" placeholder="{{ __('app.keyword') }} .." />
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
        url = "{{ url('skpi/kategoriPencapaian/searchKategoriCapaian') }}";
    }

    $.ajax({
        type: "POST",
        url: url,
        data: {
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
