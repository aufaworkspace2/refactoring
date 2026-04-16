@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <form id="form" action="{{ url('setting_duedate_pembayaran_keseluruhan/set_opsi') }}" enctype="multipart/form-data">
            @csrf
            <h3>Setting Batas Maksimal Pembayaran Setelah Diajukan</h3>
            <div class="form-row mt-3">
                <div class="form-group col-md-12">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered tablesorter">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <td style="width:30%">Nama</td>
                                    <td>Jumlah</td>
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
            </div>
            <button class="btn btn-bordered-success waves-effect width-md waves-light btn_generate" type="submit" id="buttonGenerate">
                Set Simpan
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
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
            $("#buttonGenerate").html('Set Simpan');

            if (data.status == '1') {
                swal('Pemberitahuan', data.message);
            } else {
                swal('Pemberitahuan', data.message);
            }
            filter();
        },
        error: function(xhr, status, error) {
            $("#buttonGenerate").removeAttr('disabled');
            $("#buttonGenerate").html('Set Simpan');
            swal('Pemberitahuan', 'Data Gagal Disimpan, terjadi ada kesalahan pada sistem.', 'error');
        }
    });
});

function filter() {
    $.ajax({
        type: 'POST',
        url: '{{ url("setting_duedate_pembayaran_keseluruhan/content_opsi") }}',
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
