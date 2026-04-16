@extends('layouts.template1')
@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-5">
                <div class="button-list">
                    <a href="{{ url('transkripmahasiswa/add_upload_nomor') }}" class="btn btn-bordered-success waves-light waves-effect"><i class="mdi mdi-upload"></i> Upload Data Nomor Transkrip</a>
                   <button class="btn btn-bordered-info" type="button" id="batch_khs" onclick="tampilkan();">
                        <i class="fa fa-download"></i> Cetak Batch KHS
                    </button>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="col-form-label">
                    <h5 class="mb-0">{{ __('Program') }}</h5>
                </label>
                <select class="ProgramID  form-control" onchange="filter()">
                    <option value=""> -- {{ __('Semua') }} -- </option>
                    @foreach ($programs ?? [] as $row)
                        <option value="{{ $row->ID ?? '' }}">{{ $row->Nama ?? '' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-4">
                <label class="col-form-label">
                    <h5 class="mb-0">{{ __('Program Studi') }}</h5>
                </label>
                <select class="ProdiID form-control" onchange="filter()">
                    <option value="">-- {{ __('Semua') }} --</option>
                    @foreach ($prodis ?? [] as $row)
                        <option value="{{ $row->ID ?? '' }}">{{ $row->ProdiID ?? '' }} || {{ get_field($row->JenjangID, 'jenjang') }} || {{ $row->Nama ?? '' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-4">
                <label class="col-form-label">
                    <h5 class="mb-0">Status Mahasiswa</h5>
                </label>
                <select class="StatusMhswID form-control" onchange="filter()">
                    <option value=""> -- {{ __('Semua') }} -- </option>
                    @foreach ($statuses ?? [] as $row)
                        <option value="{{ $row->ID ?? '' }}">{{ $row->Nama ?? '' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-4">
                <label class="col-form-label">
                    <h5 class="mb-0">Tahun Masuk</h5>
                </label>
                <select class="TahunMasuk form-control" onchange="filter()">
                    <option value=""> -- {{ __('Semua') }} -- </option>
                    @foreach ($tahun_masuk ?? [] as $row)
                        <option value="{{ $row->TahunMasuk ?? '' }}">{{ $row->TahunMasuk ?? '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="col-form-label">
                    <h5 class="mb-0">Semester Masuk</h5>
                </label>
                <select class="SemesterMasuk form-control" onchange="filter()">
                    <option value=""> -- {{ __('Semua') }} -- </option>
                    <option value="1">Ganjil</option>
                    <option value="2">Genap</option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="col-form-label">
                    <h5 class="mb-0">{{ __('Kata Kunci') }}</h5>
                </label>
                <input type="text" class="form-control keyword" onkeyup="filter()" placeholder="{{ __('Cari') }} .." />
            </div>
        </div>
        <!-- End Row  -->
    </div>
</div>

<!-- Load Isi Konten Disini -->
<div class="card">
    <div class="card-body">
        <div id="konten">
        </div>
    </div>
</div>

<div class="modal" id="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="mdl">Filter Data Cetak KHS Batch</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label class="col-form-label">
                                    <h5 class="mb-0">
                                        {{ __('Tahun Akademik') }}
                                    </h5>
                                </label>
                                <select class="TahunID form-control" id="TahunID">
                                    @foreach ($tahuns ?? [] as $row)
                                        <option value="{{ $row->ID ?? '' }}">{{ $row->Nama ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-12">
                                <label class="col-form-label">
                                    <h5 class="m-0">Program</h5>
                                </label>
                                <div class="controls">
                                    <select id="ProgramID2" class="form-control ProgramID">
                                        @foreach ($programs ?? [] as $row)
                                            <option value="{{ $row->ID ?? '' }}">{{ $row->Nama ?? '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-md-12">
                                <label class="col-form-label">
                                    <h5 class="m-0">Program Studi</h5>
                                </label>
                                <div class="controls">
                                    <select id="ProdiID2" class="form-control ProdiID">
                                        @foreach ($prodis ?? [] as $row)
                                            <option value="{{ $row->ID ?? '' }}">{{ get_field($row->JenjangID, 'jenjang') }} | {{ $row->Nama ?? '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-md-12">
                                <label class="col-form-label">
                                    <h5 class="mb-0">Tahun Masuk</h5>
                                </label>
                                <select id="TahunMasuk2" class="form-control">
                                    @foreach ($tahun_masuk ?? [] as $row)
                                        <option value="{{ $row->TahunMasuk ?? '' }}">{{ $row->TahunMasuk ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onClick='savet()'>Cetak Data KHS</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
    function filter(url) {
        if (url == null)
            url = "{{ url('transkripmahasiswa/search') }}";

        $.ajax({
            type: "POST",
            url: url,
            data: {
                _token: "{{ csrf_token() }}",
                ProgramID: $(".ProgramID").val(),
                ProdiID: $(".ProdiID").val(),
                TahunMasuk: $(".TahunMasuk").val(),
                StatusMhswID: $(".StatusMhswID").val(),
                SemesterMasuk: $(".SemesterMasuk").val(),
                keyword: $(".keyword").val()
            },
            success: function(data) {
                $("#konten").html(data);
            }
        });
        return false;
    }

    function checkall(chkAll, checkid) {
        if (checkid != null) {
            if (checkid.length == null) checkid.checked = chkAll.checked;
            else
                for (i = 0; i < checkid.length; i++) checkid[i].checked = chkAll.checked;

            $("input:checkbox[name='checkID[]']").parents('tr').removeClass('checked_tabel');
            $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('checked_tabel');
        }
    }

    function tampilkan() {
        $('#modal').modal('show');
    }

    $(document).ready(function() {
        filter();
    });

    function savet() {
        var ProdiID = $("#ProdiID2").val();
        var TahunMasuk = $("#TahunMasuk2").val();
        var TahunID = $("#TahunID").val();
        var ProgramID = $("#ProgramID2").val();

        var link = "{{ url('transkripmahasiswa/cetak_all') }}?ProdiID=" + ProdiID + "&TahunMasuk=" + TahunMasuk + "&TahunID=" + TahunID + "&ProgramID=" + ProgramID;
        window.open(link);
    }
</script>
@endpush
