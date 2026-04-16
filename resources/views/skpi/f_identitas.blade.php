@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <form id="f_wisudawan" class="form-horizontal">
            @csrf
            <div class="row-fluid">
                <div class="span3">
                    <legend>{{ __('app.ProdiID') }}</legend>
                    <select class="ProdiID form-control" name="ProdiID" onchange="changemahasiswa()" style="width: 100%">
                        <option value="">-- Pilih Program Studi --</option>
                        @foreach(DB::table('programstudi')->orderBy('Nama', 'ASC')->get() as $row)
                            <option value="{{ $row->ID }}">{{ $row->ProdiID }} || {{ $row->Nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="span6">
                    <legend>{{ __('app.NPM') }}</legend>
                    <select class="MhswID form-control" id="MhswID" name="MhswID" onchange="" style="width: 100%" >
                        <option value="">-- Pilih Program Studi Terlebih Dahulu --</option>
                    </select>
                </div>

                <div class="span3">
                    <legend>&nbsp;</legend>
                    <button type="button" onclick="filter()" class="btn btn-primary">
                        <i class="mdi mdi-magnify"></i> {{ __('app.search') }} Data
                    </button>
                    <button type="button" onClick="back()" class="btn btn-danger">
                        {{ __('app.back') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="load_form">
    <!-- Form will be loaded here via AJAX -->
</div>
@endsection

@push('scripts')
<script type="text/javascript">
function changemahasiswa() {
    $.ajax({
        type: "POST",
        url: "{{ url('skpi/changemahasiswaprodi') }}",
        data: {
            ProdiID: $(".ProdiID").val(),
            _token: "{{ csrf_token() }}"
        },
        success: function(data) {
            $(".MhswID").html(data);
        }
    });
    return false;
}

function filter() {
    if($("#MhswID").val()) {
        $('.err').remove();
        $.ajax({
            type: "POST",
            url: "{{ url('skpi/load_form_identitas') }}",
            data: {
                MhswID: $("#MhswID").val(),
                ProdiID: $(".ProdiID").val(),
                _token: "{{ csrf_token() }}"
            },
            success: function(data) {
                $("#load_form").html(data);
            }
        });
    } else {
        $("#MhswID").after('<span style="color:red;" class="text-danger err">Harap isi NIM Mahasiswa</span>');
    }
}

function back() {
    window.location.href = "{{ url('skpi') }}";
}

// Initialize on load
changemahasiswa();
</script>
@endpush

@endsection
