@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-3">
                <label class="col-form-label">
                    <h5 class="mb-0">Program</h5>
                </label>
                <select class="ProgramID form-control" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @foreach($data_program as $row)
                        <option value="{{ $row['ProgramID'] ?? '' }}">{{ $row['ProgramID'] ?? '' }} || {{ $row['Nama'] ?? '' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label class="col-form-label">
                    <h5 class="mb-0">Program Studi</h5>
                </label>
                <select class="ProdiID form-control" onchange="filter()">
                    <option value="">-- {{ __('app.view_all') }} --</option>
                    @foreach($data_prodi as $row)
                        <option value="{{ $row['ID'] ?? '' }}">
                            {{ $row['ProdiID'] ?? '' }} || {{ $row['jenjangNama'] ?? '' }} || {{ $row['Nama'] ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label class="col-form-label">
                    <h5 class="mb-0">Kelas</h5>
                </label>
                <select class="KelasID form-control" onchange="filter()">
                    <option value=""> -- Pilih -- </option>
                    @foreach($data_kelas as $row)
                        <option value="{{ $row['ID'] ?? '' }}">{{ $row['Nama'] ?? '' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label class="col-form-label">
                    <h5 class="mb-0">Status Mahasiswa</h5>
                </label>
                <select class="StatusMhswID form-control" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @foreach($data_status as $row)
                        <option value="{{ $row['ID'] ?? '' }}">{{ $row['Nama'] ?? '' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label class="col-form-label">
                    <h5 class="mb-0">Tahun Masuk</h5>
                </label>
                <select class="TahunMasuk form-control" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @foreach($data_tahun as $row)
                        <option value="{{ $row['TahunMasuk'] ?? '' }}">{{ $row['TahunMasuk'] ?? '' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label class="col-form-label">
                    <h5 class="mb-0">Urutkan dengan</h5>
                </label>
                <div class="row">
                    <div class="col-md-6">
                        <select name="orderby" class="form-control orderby" id="orderby" onchange="filter()">
                            <option value="mahasiswa.Nama">Nama</option>
                            <option value="mahasiswa.NPM">NPM</option>
                            <option value="t_informasi_baru.ID">Input Informasi Tambahan SKPI</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <select name="descasc" class="form-control descasc" id="descasc" onchange="filter()">
                            <option value="DESC">Z-A</option>
                            <option value="ASC">A-Z</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group col-md-6">
                <label class="col-form-label mt-2">
                    <h5 class="m-0">{{ __('app.keyword_legend') }}</h5>
                </label>
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

function filter(url) {
    if (url == null) {
        url = "{{ url('skpi/approveInformasi/searchApproveInformasi') }}";
    }

    $.ajax({
        type: "POST",
        url: url,
        data: {
            ProgramID: $(".ProgramID").val(),
            ProdiID: $(".ProdiID").val(),
            StatusMhswID: $(".StatusMhswID").val(),
            TahunMasuk: $(".TahunMasuk").val(),
            KelasID: $(".KelasID").val(),
            keyword: $(".keyword").val(),
            orderby: $("#orderby").val(),
            descasc: $("#descasc").val()
        },
        success: function(data) {
            $("#konten").html(data);
        }
    });
    return false;
}
filter();
</script>
@endpush
