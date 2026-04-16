@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-header"><h4 class="my-0">Setting Wording - Info Pembayaran Formulir</h4></div>
    <div class="card-body">
        <div id="kata-kunci">
            <p class="card-text mb-0">Catatan :</p>
            <p class="text-info"><i>* Gunakan kata kunci dibawah ini agar sistem dapat secara otomatis menampilkan data perguruan tinggi atau data pendaftar.</i></p>
        </div>
        <table class="table table-condensed">
            <tr id="kata-kunci1"><td>Singkatan Institusi Perguruan Tinggi</td><td><strong>[SINGKATAN_INSTITUSI]</strong></td></tr>
            <tr><td>Handphone Institusi Perguruan Tinggi</td><td><strong>[HP_INSTITUSI]</strong></td></tr>
            <tr><td>Alamat Institusi Perguruan Tinggi</td><td><strong>[ALAMAT_INSTITUSI]</strong></td></tr>
            <tr><td>Telepon Institusi Perguruan Tinggi</td><td><strong>[TELEPON_INSTITUSI]</strong></td></tr>
            <tr><td>Faksimile Institusi Perguruan Tinggi</td><td><strong>[FAX_INSTITUSI]</strong></td></tr>
            <tr><td>Email Institusi Perguruan Tinggi</td><td><strong>[EMAIL_INSTITUSI]</strong></td></tr>
            <tr><td>Website Institusi Perguruan Tinggi</td><td><strong>[WEB_INSTITUSI]</strong></td></tr>
            <tr><td>Nama Pendaftar</td><td><strong>[NAMA_PENDAFTAR]</strong></td></tr>
            <tr><td>Tempat Tanggal Lahir Pendaftar</td><td><strong>[TTL_PENDAFTAR]</strong></td></tr>
            <tr><td>Program Studi Pilihan Pertama Pendaftar</td><td><strong>[PROGRAMSTUDI_PILIHAN_1]</strong></td></tr>
            <tr><td>Program Studi Pilihan Kedua Pendaftar</td><td><strong>[PROGRAMSTUDI_PILIHAN_2]</strong></td></tr>
            <tr><td>Program Studi Pilihan Ketiga Pendaftar</td><td><strong>[PROGRAMSTUDI_PILIHAN_3]</strong></td></tr>
            <tr><td>Program Studi Lulus Pendaftar</td><td><strong>[PROGRAMSTUDI_LULUS]</strong></td></tr>
            <tr><td>Program Kuliah Pendaftar</td><td><strong>[PROGRAM_KULIAH]</strong></td></tr>
            <tr><td>Jalur Pendaftaran Pendaftar</td><td><strong>[JALUR_PMB_PENDAFTAR]</strong></td></tr>
            <tr><td>Nomor Ujian Pendaftar</td><td><strong>[NO_UJIAN_PENDAFTAR]</strong></td></tr>
            <tr><td>Nomor Telepon Pendaftar</td><td><strong>[NO_HP_PENDAFTAR]</strong></td></tr>
            <tr><td>Alamat Pendaftar</td><td><strong>[ALAMAT_PENDAFTAR]</strong></td></tr>
            <tr><td>Tahun Akademik Gelombang Pendaftaran</td><td><strong>[TAHUN_AKADEMIK_GELOMBANG_PENDAFTARAN]</strong></td></tr>
            <tr><td>Biaya Pembayaran Formulir Pendaftaran</td><td><strong>[BIAYA_FORMULIR_PENDAFTARAN]</strong></td></tr>
            <tr><td>Biaya Registrasi Ulang</td><td><strong>[BIAYA_REGISTRASI_ULANG]</strong></td></tr>
            <tr><td>Tanggal Hari Ini</td><td><strong>[TGL_HARI_INI]</strong></td></tr>
            <tr><td>Tanggal Ujian USM</td><td><strong>[TGL_USM]</strong></td></tr>
            <tr><td>Status Kelulusan USM</td><td><strong>[KELULUSAN_USM]</strong></td></tr>
        </table>
        <hr>
        <form id="f_infopembayaranformulir" onsubmit="simpandata(this);return false;">
            <div>
                <ul class="nav nav-tabs" id="content-setting">
                    <li class="nav-item"><a href="#formulir" data-toggle="tab" class="nav-link active">Informasi Pembayaran Formulir</a></li>
                    <li class="nav-item"><a href="#formulirgratis" data-toggle="tab" class="nav-link">Informasi Pembayaran Formulir (Gratis)</a></li>
                </ul>
                <div class="tab-content mb-3">
                    <div class="tab-pane show active" id="formulir">
                        @if($Update == 'YA')<button type="button" class="btn btn-outline-success btn-sm mb-3" onclick="setDefaultContent('infopembayaranformulir_bayar')">Gunakan template default</button>@endif
                        <textarea name="infopembayaranformulir_bayar" id="infopembayaranformulir_bayar" class="form-control tinymce" rows="10">{{ $infopembayaranformulir_bayar ?? '' }}</textarea>
                    </div>
                    <div class="tab-pane" id="formulirgratis">
                        @if($Update == 'YA')<button type="button" class="btn btn-outline-success btn-sm mb-3" onclick="setDefaultContent('infopembayaranformulir_gratis')">Gunakan template default</button>@endif
                        <textarea name="infopembayaranformulir_gratis" id="infopembayaranformulir_gratis" class="form-control tinymce" rows="10">{{ $infopembayaranformulir_gratis ?? '' }}</textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light">Simpan</button>
            </div>
        </form>
    </div>
</div>
<div class="modal fade modal-custom" id="modal-info" tabindex="-1" role="dialog" aria-hidden="true">
    <nav class="navbar navbar-expand-lg bar-custom"><a class="navbar-brand text-light text-truncate custom-bar-text mr-auto" data-dismiss="modal"><i class="fa fa-arrow-left mr-3 text-light font-size-16"></i><span class="font-size-16">Info Pembayaran Formulir</span></a></nav>
    <div class="modal-dialog custom"><div class="modal-content custom"><img class="mx-auto d-block img-fluid" style="width: 80%;margin-top: 30px;" src="{{ asset('assets/template1/assets/images/wording-sample/info_pembayaran_formulir.png') }}"></div></div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

function initTinyMCE() {
    if (typeof tinymce === 'undefined') { setTimeout(initTinyMCE, 100); return; }
    tinymce.EditorManager.editors = [];
    tinymce.init({
        selector: 'textarea.tinymce',
        height: 300,
        plugins: 'image link code table',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | image link code',
        file_picker_types: 'image',
        file_picker_callback: function(cb, value, meta) {
            var input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.onchange = function() {
                var file = this.files[0];
                var reader = new FileReader();
                reader.onload = function() {
                    var id = 'blobid' + (new Date()).getTime();
                    var blobCache = tinymce.activeEditor.editorUpload.blobCache;
                    var base64 = reader.result.split(',')[1];
                    var blobInfo = blobCache.create(id, file, base64);
                    blobCache.add(blobInfo);
                    cb(blobInfo.blobUri(), { title: file.name });
                };
                reader.readAsDataURL(file);
            };
            input.click();
        }
    });
}
$(document).ready(function() { initTinyMCE(); });
function setDefaultContent(tipe) { $.ajax({ type: 'POST', url: "{{ url('setting_redaksi_pmb/get_default_setting_redaksi_pmb') }}", data: { tipe: tipe }, success: function(data) { if (tinymce.get(tipe)) { tinymce.get(tipe).setContent(data); } } }); }
function simpandata(formz) { if (typeof tinymce !== 'undefined') { tinymce.triggerSave(); } var formData = new FormData(formz); $.ajax({ type: 'POST', url: "{{ url('setting_redaksi_pmb/save_info_pembayaran_formulir') }}", data: formData, cache: false, contentType: false, processData: false, beforeSend: function() { silahkantunggu(); }, success: function(data) { if (data == '1') { berhasil(); alertsuccess(); } else { alertfail(); berhasil(); } }, error: function() { alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.'); } }); }
</script>
@endpush
