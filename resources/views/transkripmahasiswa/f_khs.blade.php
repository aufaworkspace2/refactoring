@extends('layouts.template1')
@section('content')
<form id="add_khs_form" action="{{ url('transkripmahasiswa/save_khs') }}">
    @csrf
    <input type="hidden" value="{{ $MhswID }}" name="MhswID">
    
    <div class="form-group row">
        <label class="col-md-3 col-form-label">Tahun Semester</label>
        <div class="col-md-9">
            <select class="form-control" name="TahunID" id="TahunID_khs">
                @foreach ($tahuns ?? [] as $thn)
                    @php $thn = (object) $thn; @endphp
                    <option value="{{ $thn->ID }}">{{ $thn->TahunID }} | {{ $thn->Nama }} {{ ($thn->ProsesBuka ?? 0) == 1 ? '(Aktif)' : '' }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-form-label">Mata Kuliah</label>
        <div class="col-md-9">
            <select class="form-control select2" name="detailkurikulumid" id="detailkurikulum_khs" style="width:100%;" required>
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
            <input type="text" class="form-control" name="TotalSKS" id="TotalSKS_khs">
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-form-label">Nilai Huruf</label>
        <div class="col-md-9">
            <select class="form-control" name="NilaiHuruf">
                @foreach ($bobots ?? [] as $th)
                    @php $th = (object) $th; @endphp
                    <option value="{{ $th->Nilai }}_{{ $th->Bobot }}">{{ $th->Nilai }} ({{ $th->Bobot }})</option>
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
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('#detailkurikulum_khs').select2({
                dropdownParent: $('#modal_header')
            });
        }

        $("#add_khs_form").submit(function(e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: $(this).attr('action'),
                data: $(this).serialize(),
                success: function(data) {
                    swal("Berhasil !", "Data KHS berhasil ditambahkan.", "success").then(function() {
                        $('.modal').modal('hide');
                        if (typeof change_nilai === "function") {
                            change_nilai();
                        } else {
                            location.reload();
                        }
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
