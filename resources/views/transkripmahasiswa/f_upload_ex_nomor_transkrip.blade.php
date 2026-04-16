@extends('layouts.template1')
@section('content')
<form id="f_upload_excel_nomor_transkrip" action="{{ url('transkripmahasiswa/upload_excel_nomor') }}" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h4>Upload Data Nomor Transkrip</h4>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label class="col-form-label" for="Nama">Unduh Template *</label>
                            <div class="controls">
                                <a href="{{ url('transkripmahasiswa/template_upload_nomor') }}" download class='btn btn-bordered-success waves-effect waves-light'>Unduh</a>
                            </div>
                        </div>
                        <div class="form-group col-md-12">
                            <label class="col-form-label" for="Nama">Pilih File *</label>
                            <div class="controls">
                                <input type="file" id="file_excel" name="file_excel" class="form-control"
                                accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                                />
                            </div>
                        </div>
                        <div class="form-group col-md-12">
                            <a download href="{{ url('excel_up/upload/list_data_detail_transkrip_gagal.xlsx') }}" class="d-block mt-2">
                                <div class="file-template-down error d-none" id="divFailed">
                                    <div class="d-flex">
                                        <i class="mdi mdi-file-remove-outline mr-2 text-danger icon-file"></i>
                                        <p class="mb-0 align-self-center text-dark">list_data_detail_transkrip_gagal.xlsx</p>
                                    </div>
                                    <i class="mdi mdi-cloud-download icon-download text-dark"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                    <button onClick="btnEdit({{ $save ?? 1 }},1)" type="button" class="btn btn-bordered-success waves-effect width-md waves-light btnEdit">Tambah Data</button>
                    <button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">{{ __('Simpan') }} Data</button>
                    <button type="button" onClick="back()" class="btn btn-bordered-danger waves-effect width-md waves-light">{{ __('Kembali') }}</button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Cara Penggunaan</h4>
                            <ol>
                                <li>Unduh template</li>
                                <li>Isi data sesuai dengan template</li>
                                <li>Unggah kembali template ke aplikasi</li>
                                <li>Simpan Data</li>
                            </ol>
                        </div>
                        <div class="col-md-6">
                            <h4>Catatan</h4>
                            <ol>
                                <li>File yang diterima hanya sesuai dengan template.</li>
                                <li>Tidak boleh mengubah header file.</li>
                                <li>Mengubah atau menambahkan header file dapat membuat data tidak singkron.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        $("#f_upload_excel_nomor_transkrip").submit(function(e) {
            e.preventDefault();
            $('#divFailed').addClass('d-none');
            var formData = new FormData(this);
            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: formData,
                cache: false,
                contentType: false,
                dataType: 'json',
                processData: false,
                beforeSend: function(a) {
                    $(".btnSave").attr("disabled", "disabled");
                },
                success: function(data) {
                    let persen = data.Persen;
                    if (persen != 100) {
                        $('#divFailed').removeClass('d-none');
                    }

                    swal(data.title, data.message, data.type);

                    if (data.status == 1) {
                        $(".btnSave").removeAttr("disabled");
                    } else {
                        $(".btnSave").removeAttr("disabled");
                    }
                },
                error: function(data) {
                    swal("Gagal !", "Terjadi kesalahan saat mengunggah data.", "error");
                    $(".btnSave").removeAttr("disabled");
                }
            });
        });
    });

    function btnEdit(type, checkid) {
        $("input:text").attr('disabled', true);
        $("input:file").attr('disabled', true);
        $("input:radio").attr('disabled', true);
        $("button:submit").attr('disabled', true);
        $("select").attr('disabled', true);
        $("textarea").attr('disabled', true);
        $(".btnSave").css('display', 'none');

        if (checkid == 1) {
            $("input:text").removeAttr('disabled');
            $("input:file").removeAttr('disabled');
            $("input:radio").removeAttr('disabled');
            $("select").removeAttr('disabled');
            $("textarea").removeAttr('disabled');
            $("button:submit").removeAttr('disabled');
            $(".btnEdit").fadeOut(0);
            $(".btnSave").fadeIn(0);
        }
    }

    function back() {
        window.location.href = "{{ url('transkripmahasiswa') }}";
    }

    btnEdit({{ $save ?? 1 }});
</script>
@endpush
