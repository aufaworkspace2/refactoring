@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <h4>Setting Termin Pembayaran Mahasiswa</h4>
    </div>
    <div class="card-body">
        <form id="form" action="{{ url('setting_termin_pembayaran_mahasiswa/set_opsi') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-row">
                <div class="col-md-12">
                    <div class="tab-pane active" id="tab-details">
                        <div class="well form-horizontal">
                            <div class="form-group">
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered tablesorter" width="100%">
                                        <thead class="bg-primary text-white">
                                            <tr>
                                                <td style="width:30%">Nama</td>
                                                <td>Opsi</td>
                                            </tr>
                                        </thead>
                                        <tbody id="isi">
                                            <tr>
                                                <td colspan="2" class="center">
                                                    <h3><i class="fa fa-spinner fa-spin"></i> Loading ...</h3>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <button class="btn btn-primary waves-effect waves-light btn_generate" type="submit" id="buttonGenerate">
                                <icon class="mdi mdi-refresh"></icon> &nbsp; Set Simpan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

$("#form").submit(function(e) {
    e.preventDefault();

    var formData = new FormData(this);

    $.ajax({
        type: 'POST',
        url: $(this).attr('action'),
        data: formData,
        dataType: "json",
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function(xhr) {
            $("#buttonGenerate").attr('disabled', true);
            $("#buttonGenerate").html('<icon class="fa fa-spinner fa-spin"></icon> Silahkan Tunggu');
        },
        success: function(data) {
            $("#buttonGenerate").removeAttr('disabled');
            $("#buttonGenerate").html('<icon class="icon-refresh"></icon> Set Simpan');

            if (data.status == '1') {
                swal('Pemberitahuan', data.message);
            } else {
                swal('Pemberitahuan', data.message);
            }
            filter();
        },
        error: function(xhr, status, error) {
            $("#buttonGenerate").removeAttr('disabled');
            $("#buttonGenerate").html('<icon class="icon-refresh"></icon> Set Simpan');
            swal('Pemberitahuan', 'Data Gagal Disimpan, terjadi ada kesalahan pada sistem.', 'error');
        }
    });
});

function filter() {
    $.ajax({
        type: 'POST',
        url: '{{ url("setting_termin_pembayaran_mahasiswa/content_opsi") }}',
        data: {
            _token: "{{ csrf_token() }}"
        },
        success: function(data) {
            $('#isi').html(data);
        }
    });
}

filter();
</script>
@endpush
