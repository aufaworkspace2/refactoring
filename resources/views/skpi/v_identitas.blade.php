@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <input type="hidden" value="{{ $Jenis ?? 'Transaksi' }}" id="Jenis" name="Jenis"/>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label class="col-form-label"><h5 class="mb-0">{{ __('app.ProgramID') }}</h5></label>
                <select class="ProgramID form-control" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @foreach(DB::table('program')->orderBy('Nama', 'ASC')->get() as $row)
                        <option value="{{ $row->ID }}">{{ $row->ProgramID }} || {{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label class="col-form-label"><h5 class="mb-0">{{ __('app.ProdiID') }}</h5></label>
                <select class="ProdiID form-control" onchange="filter(); changekelas();">
                    <option value="">-- {{ __('app.view_all') }} --</option>
                    @foreach(DB::table('programstudi')->orderBy('Nama', 'ASC')->get() as $row)
                        <option value="{{ $row->ID }}">{{ $row->ProdiID }} || {{ DB::table('jenjang')->where('ID', $row->JenjangID)->value('Nama') ?? '' }} || {{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-2">
                <label class="col-form-label"><h5 class="mb-0">Kelas</h5></label>
                <select class="KelasID form-control" onchange="filter()">
                    <option value=""> -- Pilih -- </option>
                </select>
            </div>

            <div class="form-group col-md-2">
                <label class="col-form-label"><h5 class="mb-0">{{ __('app.StatusMhswID') }}</h5></label>
                <select class="StatusMhswID form-control" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @foreach(DB::table('statusmahasiswa')->whereIn('Nama', ['aktif', 'lulus', 'cuti'])->orderBy('Nama', 'ASC')->get() as $row)
                        <option value="{{ $row->ID }}">{{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-2">
                <label class="col-form-label"><h5 class="mb-0">{{ __('app.TahunMasuk') }}</h5></label>
                <select class="TahunMasuk form-control" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @foreach(DB::table('mahasiswa')->select('TahunMasuk')->distinct()->orderBy('TahunMasuk', 'DESC')->get() as $row)
                        <option value="{{ $row->TahunMasuk }}">{{ $row->TahunMasuk }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-12">
                <label class="col-form-label"><h5 class="mb-0">{{ __('app.keyword_legend') }}</h5></label>
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

<!-- Modal for SKPI Print - Moved to main view so it's always available -->
<div id="mdla" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="mdla" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="mdla">Masukan data berikut :</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body" id="mdla-body">
                <!-- Will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-info waves-effect waves-light" data-dismiss="modal">Close</button>
                <button type="button" onclick="pdf()" class="btn btn-danger waves-effect">Cetak SKPI</button>
                <button type="button" onclick="save()" class="btn btn-success waves-effect">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
function changekelas(){
    $.ajax({
        url: "{{ url('welcome/test') }}",
        type: "POST",
        data: {
            ProdiID: $(".ProdiID").val(),
            table: 'kelas',
            field: 'Nama',
            _token: "{{ csrf_token() }}"
        },
        success: function(data){
            if($(".ProdiID").val() == '') {
                $('.KelasID').attr('disabled', true);
            } else {
                $('.KelasID').removeAttr('disabled');
            }
            $(".KelasID").html(data);
            filter();
        }
    });
}
changekelas();

function filter(url) {
    if (url == null) {
        var jenis = $("#Jenis").val();
        url = "{{ url('skpi/search') }}";
    }

    $.ajax({
        type: "POST",
        url: url,
        data: {
            ProgramID: $(".ProgramID").val(),
            ProdiID: $(".ProdiID").val(),
            ProdiID2: $(".ProdiID2").val(),
            TahunMasuk: $(".TahunMasuk").val(),
            KelasID: $(".KelasID").val(),
            StatusMhswID: $(".StatusMhswID").val(),
            keyword: $(".keyword").val(),
            _token: "{{ csrf_token() }}"
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
        $("input:checkbox[name='checkID[]']").parents('tr').removeClass('checked_tabel');
        $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('checked_tabel');
    }
}
filter();

// SKPI Print Functions - Available globally

// PDF print function
function pdf(){
    var id = $("#mhwsID").val();
    var nomor_skpi = $("#nomor_skpi").val();
    window.open('{{ url("skpi/pdf") }}?ID=' + id + '&nomor_skpi=' + nomor_skpi, "_Blank");
}

// Save SKPI function
function save(){
    $.ajax({
        type: "POST",
        url: "{{ url('skpi/save_parameter_cetak') }}",
        data: {
            nomor_skpi: $("#nomor_skpi").val(),
            MhswID: $("#mhwsID").val(),
            _token: "{{ csrf_token() }}"
        },
        success: function(data) {
            toastr.options = {
                "closeButton": false,
                "debug": false,
                "newestOnTop": false,
                "progressBar": false,
                "positionClass": "toast-top-left",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            }
            toastr["success"]("", "Update Data Berhasil");
        }
    });
    return false;
}

// Open modal function - THIS IS THE MAIN FUNCTION FOR CETAK BUTTON
function opnmdl(id, type){
    $.ajax({
        url: "{{ url('skpi/loadinfo') }}/" + id,
        type: "GET",
        success: function(data){
            $("#mdla-body").html(data);
            $("#mdla").modal('show');
            if(type == 'pdf') {
                $(".cetak").attr('onclick', 'pdf()');
            } else {
                $(".cetak").attr('onclick', 'word()');
            }
        }
    });
}
</script>
@endpush
