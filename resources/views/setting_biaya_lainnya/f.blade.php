@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <form id="f_setting_biaya_lainnya" onsubmit="savedata(this); return false;"
              action="{{ url('setting_biaya_lainnya/save/' . $save) }}"
              enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="ID" id="ID" value="{{ $row->ID }}">
            <h3>Setting Biaya Lainnya</h3>
            <div class="form-row mt-3">

                <div class="form-group col-md-12">
                    <label class="col-form-label" for="JenisBiayaID">Komponen Biaya *</label>
                    <div class="controls">
                        <select id="JenisBiayaID" required name="JenisBiayaID" class="form-control">
                            <option value="">-- {{ __('app.select') }} --</option>
                            @foreach(get_all('jenisbiaya') as $riw)
                                <option value="{{ $riw->ID }}" {{ ($row->JenisBiayaID == $riw->ID) ? 'selected' : '' }}>
                                    {{ $riw->Nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label class="col-form-label" for="Gambar">Gambar *</label>
                    <div class="controls">
                        @if($row->Gambar)
                            <img class="mb-2" src="{{ asset('client/biaya_lainnya/gambar/' . $row->Gambar) }}" alt="Gambar" title="{{ $row->Nama }}">
                        @endif
                        <input class="form-control" type="file" accept=".jpg,.jpeg,.png" name="Gambar" id="Gambar">
                        <input type="hidden" name="NamaGambar" id="NamaGambar" value="{{ $row->Gambar }}">
                        <small>Rekomendasi : 286 X 180 pixel (Gambar Akan di resize menjadi ukuran ini)</small>
                        <br>
                        <small>Format didukung : jpg,jpeg,png </small>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label class="col-form-label" for="Harga">Harga *</label>
                    <div class="controls">
                        <input type="text" required id="Harga" name="Harga" class="form-control currency" value="{{ $row->Harga }}" />
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label class="col-form-label" for="Deskripsi">Deskripsi</label>
                    <div class="controls">
                        <textarea name="Deskripsi" id="Deskripsi" class="form-control" rows="5">{{ $row->Deskripsi }}</textarea>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label class="col-form-label" for="TanggalMulai">Tanggal Mulai *</label>
                    <div class="controls">
                        <input type="date" id="TanggalMulai" name="TanggalMulai" class="form-control" value="{{ $row->TanggalMulai }}" />
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label class="col-form-label" for="TanggalSelesai">Tanggal Selesai *</label>
                    <div class="controls">
                        <input type="date" id="TanggalSelesai" name="TanggalSelesai" class="form-control" value="{{ $row->TanggalSelesai }}" />
                    </div>
                </div>

            </div>
            <button onClick="btnEdit({{ $save }},1)" type="button"
                    class="btn btn-bordered-success waves-effect width-md waves-light btnEdit">
                {{ ($save == 1) ? __('app.add') : __('app.edit') }} Data <i class="icon-ok-circle icon-white-t"></i>
            </button>
            <button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">
                {{ __('app.save') }} Data <i class="icon-check icon-white-t"></i>
            </button>
            <button type="button" onClick="back()" class="btn btn-bordered-danger waves-effect width-md waves-light">
                {{ __('app.back') }} <i class="icon-share-alt icon-white-t"></i>
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$('.currency').mask('#.##0', {reverse: true});
$('.currency').trigger('input');

function savedata(formz) {
    $('.currency').unmask('#.##0', {reverse: true});

    var formData = new FormData(formz);
    $.ajax({
        type: 'POST',
        url: $(formz).attr('action'),
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function(r) {
            silahkantunggu();
        },
        success: function(data) {
            if(data == 'gagal') {
                alertfail();
                berhasil();
            } else {
                if({{ $save }} == '1') {
                    window.location = "{{ url('setting_biaya_lainnya') }}";
                }

                if({{ $save }} == '2') {
                    window.location = "{{ url('setting_biaya_lainnya') }}";
                }
                berhasil();
                alertsuccess();

                $('.currency').mask('#.##0', {reverse: true});
                $('.currency').trigger('input');
            }
        },
        error: function(data) {
            $(".btnSave").html('{{ __("app.save") }} Data <i class="icon-check icon-white-t"></i>');
            $(".btnSave").removeAttr("disabled");
            alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.');

            $('.currency').mask('#.##0', {reverse: true});
            $('.currency').trigger('input');
        }
    });
}

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

btnEdit({{ $save }});
</script>
@endpush
