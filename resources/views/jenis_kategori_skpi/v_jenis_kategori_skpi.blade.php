@extends('layouts.template1')
@section('content')

<div class="card-box">
    <div class="row">
        <div class="col-md-12">
            <div class="button-list">
                @if($Create == 'YA')
                    <a href="{{ url('jenis_kategori_skpi/add') }}" class="btn btn-bordered-primary waves-effect width-md waves-light">
                        <i class="mdi mdi-plus"></i> {{ __('app.add') }} Data
                    </a>
                @endif
                <button class="btn btn-bordered-danger waves-effect width-md waves-light" id="btnDelete" 
                        data-placement="top" title="{{ __('Silahkan pilih data terlebih dahulu.') }}" 
                        data-toggle="modal">
                    <i class="mdi mdi-delete"></i> {{ __('app.delete') }}
                </button>
            </div>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-12">
            <label class="col-form-label mt-2">
                <h4 class="m-0">{{ __('app.keyword_legend') }}</h4>
            </label>
            <input type="text" class="form-control keyword" onkeyup="filter()" 
                   placeholder="{{ __('keyword') }} ..">
        </div>
    </div>
</div>
<div class="card-box">
    <div id="konten"></div>
</div>

@push('scripts')
<script type="text/javascript">
function filter(url) {
    if(url == null) {
        url = "{{ url('jenis_kategori_skpi/search') }}";
    }

    $.ajax({
        type: "POST",
        url: url,
        data: {
            keyword: $(".keyword").val(),
            _token: "{{ csrf_token() }}"
        },
        success: function(data) {
            $("#konten").html(data);
        }
    });
    return false;
}

function pdf(){
    window.open("{{ url('jenis_kategori_skpi/pdf') }}?keyword="+$(".keyword").val(), "_Blank");
}

function excel(){
    window.open("{{ url('jenis_kategori_skpi/excel') }}?keyword="+$(".keyword").val(), "_Blank");
}

function checkall(chkAll,checkid) {
    if (checkid != null) {
        if (checkid.length == null) {
            checkid.checked = chkAll.checked;
        } else {
            for (i=0; i<checkid.length; i++) {
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

@endsection
