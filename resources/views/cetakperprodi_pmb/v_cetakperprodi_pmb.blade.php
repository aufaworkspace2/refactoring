@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="col-form-label"><h5 class="m-0">Gelombang</h5></label>
                <select class="gelombang form-control select2" id="gelombang" onchange="filter();">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @foreach($data_gelombang as $row)
                        <option value="{{ $row->id }}">{{ $row->kode }} || {{ $row->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-8">
                <label class="col-form-label"><h5 class="m-0">{{ __('app.keyword_legend') }}</h5></label>
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

$(document).ready(function() {
    $('.select2').select2({
        placeholder: function(){
            $(this).data('placeholder');
        },
        allowClear: true,
        width: '100%'
    });
    filter();
});

function filter(url) {
    if(url == null) url = "{{ url('cetakperprodi_pmb/search') }}";
    $.ajax({
        type: "POST",
        url: url,
        data: {
            gelombang : $(".gelombang").val(),
            keyword : $(".keyword").val()
        },
        success: function(data) {
            $("#konten").html(data);
        }
    });
    return false;
}

function cetak(prodi, gelombang, awal, akhir){
    var link = "prodi="+prodi+"&gelombang="+gelombang+"&awal="+awal+"&akhir="+akhir;
    window.open("{{ url('cetakperprodi_pmb/cetak') }}?"+link, "_Blank");
}
</script>
@endpush
