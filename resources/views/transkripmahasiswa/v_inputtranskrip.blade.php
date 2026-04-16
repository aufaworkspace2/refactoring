@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Input Transkrip</h5>
    </div>
    <div class="card-body">
        <form id="f_transkrip_add" action="{{ url('transkripmahasiswa/save') }}">
            @csrf
            <legend>Nama : {{ get_field($d_mhs->ID ?? '', 'mahasiswa', 'Nama') }} || NPM : {{ $d_mhs->NPM ?? '' }}</legend>
            <input type="hidden" value="{{ $MhswID }}" name="MhswID">
            
            <div class="form-group row">
                <label class="col-md-3 col-form-label">Semester</label>
                <div class="col-md-9">
                    <select class="form-control" name="Semester">
                        @for ($i = 1; $i <= 8; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 col-form-label">Mata Kuliah</label>
                <div class="col-md-9">
                    <select class="form-control select2" name="detailkurikulumid" id="detailkurikulum_select">
                        <option value=""> -- Pilih Mata Kuliah -- </option>
                        @foreach ($detail ?? [] as $r)
                            <option value="{{ $r->ID }}">{{ $r->MKKode }} || {{ $r->Nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 col-form-label">Bobot SKS</label>
                <div class="col-md-9">
                    <div class="input-group">
                        <input type="text" class="form-control" name="TotalSKS" id="TotalSKS">
                        <div class="input-group-append">
                            <span class="input-group-text">SKS</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 col-form-label">Nilai Huruf</label>
                <div class="col-md-9">
                    <select class="form-control" name="NilaiHuruf">
                        @foreach ($bobots ?? [] as $th)
                            <option value="{{ $th->Nilai }}">{{ $th->Nilai }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-9 offset-md-3">
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2();
        }

        $("#f_transkrip_add").submit(function(e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: $(this).attr('action'),
                data: $(this).serialize(),
                success: function(data) {
                    swal({
                        title: "Berhasil !",
                        text: "Data berhasil di simpan...",
                        type: 'success'
                    }).then(function() {
                        // Return to edit transcript page
                        window.location.href = "{{ url('transkripmahasiswa/edit_transkrip') }}/{{ $MhswID }}";
                    });
                },
                error: function(data) {
                    swal("Gagal !", "Terjadi kesalahan saat menyimpan data.", "error");
                }
            });
        });
    });
</script>
@endpush
