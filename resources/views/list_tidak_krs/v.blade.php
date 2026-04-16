@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="button-list">
                    <div class="btn-group">
                        <button type="button" class="btn btn-info dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false">
                            <i class="mdi mdi-printer mr-1"></i> Cetak <i class="mdi mdi-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a onclick="pdf()" href="javascript:void(0);" class="dropdown-item">
                                <i class="mdi mdi-printer"></i> {{ __('app.pdf') }}
                            </a>
                            <a onclick="excel()" href="javascript:void(0);" class="dropdown-item">
                                <i class="mdi mdi-printer"></i> {{ __('app.excel') }}
                            </a>
                        </div>
                    </div>
                    @if($Update == 'YA')
                    <div class="btn-group">
                        <button class="btn btn-secondary dropdown-toggle waves-effect waves-light" data-toggle="dropdown">
                            Action <i class="mdi mdi-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item btnDelete" href="#hapus" data-toggle="modal" onclick="$('#f_set').attr('action','{{ url('list_tidak_krs/set_statusall/2') }}')">
                                <i class="mdi mdi-pencil"></i> Set Cuti
                            </a>
                            <a class="dropdown-item btnDelete" href="#hapus" data-toggle="modal" onclick="$('#f_set').attr('action','{{ url('list_tidak_krs/set_statusall/6') }}')">
                                <i class="mdi mdi-pencil"></i> Set Non Aktif
                            </a>
                            <a class="dropdown-item btnDelete" href="#hapus" data-toggle="modal" onclick="$('#f_set').attr('action','{{ url('list_tidak_krs/set_statusall/3') }}')">
                                <i class="mdi mdi-pencil"></i> Set Aktif
                            </a>
                        </div>
                    </div>
                    @endif

                    @if($Create == 'YA')
                        <button class="btn btn-danger waves-effect waves-light" onclick="update_data()">
                            <i class="mdi mdi-refresh"></i> Update Data
                        </button>
                    @endif
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label class="col-form-label mt-2"><h5 class="m-0">Program Kuliah</h5></label>
                <select class="ProgramID form-control" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @foreach(get_all('program') as $row)
                        <option value="{{ $row->ID }}">{{ $row->ProgramID }} || {{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label class="col-form-label mt-2"><h5 class="m-0">Program Studi</h5></label>
                <select class="ProdiID form-control" onchange="filter();">
                    <option value="">-- {{ __('app.view_all') }} --</option>
                    @foreach(get_all('programstudi') as $row)
                        <option value="{{ $row->ID }}">{{ $row->ProdiID }} || {{ get_field($row->JenjangID, 'jenjang') }} || {{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label class="col-form-label mt-2"><h5 class="m-0">Tahun Masuk</h5></label>
                <select class="TahunMasuk form-control" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @php
                        $get_tahun = DB::table('mahasiswa')
                            ->select('TahunMasuk')
                            ->distinct()
                            ->orderBy('TahunMasuk', 'DESC')
                            ->get();
                    @endphp
                    @foreach($get_tahun as $row)
                        <option value="{{ $row->TahunMasuk }}">{{ $row->TahunMasuk }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label class="col-form-label mt-2"><h5 class="m-0">Tahun / Semester</h5></label>
                <select class="TahunID form-control" onchange="filter()">
                    @php
                        $tahun_list = DB::table('tahun')
                            ->orderBy('TahunID', 'DESC')
                            ->whereIn('Semester', [1, 2])
                            ->get();
                    @endphp
                    @foreach($tahun_list as $raw)
                        <option value="{{ $raw->ID }}" {{ ($raw->ProsesBuka == 1) ? 'selected' : '' }}>
                            {{ $raw->Nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label class="col-form-label mt-2"><h5 class="m-0">Lama Tidak KRS</h5></label>
                <select class="TidakKRS form-control" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @php
                        $tidakkrs = DB::select("SELECT DISTINCT COUNT(tmp_tidak_krs.ID) AS jumlah FROM `tmp_tidak_krs` GROUP BY `MhswID` ORDER BY `jumlah` ASC");
                        foreach($tidakkrs as $list) {
                            echo "<option value='".$list->jumlah."'>".$list->jumlah." Semester</option>";
                        }
                    @endphp
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label mt-2"><h5 class="m-0">Status Mahasiswa</h5></label>
                <select class="StatusMhswID form-control" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @foreach(get_all('statusmahasiswa') as $row)
                        @if(in_array($row->ID, [3, 6, 2]))
                            @php $select = ($row->ID == 3) ? 'selected' : ''; @endphp
                            <option value="{{ $row->ID }}" {{ $select }}>{{ $row->Nama }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-6">
                <label class="col-form-label mt-2"><h5 class="m-0">{{ __('app.keyword_legend') }}</h5></label>
                <input type="text" class="form-control keyword" onkeyup="filter()" placeholder="{{ __('app.keyword') }} .." />
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
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

function filter(url) {
    if(url == null)
        url = "{{ url('list_tidak_krs/search') }}";

    $.ajax({
        type: "POST",
        url: url,
        data: {
            ProgramID   : $(".ProgramID").val(),
            ProdiID     : $(".ProdiID").val(),
            TahunMasuk  : $(".TahunMasuk").val(),
            TahunID     : $(".TahunID").val(),
            TidakKRS    : $(".TidakKRS").val(),
            statusMhswID: $(".StatusMhswID").val(),
            keyword     : $(".keyword").val(),
            _token: "{{ csrf_token() }}"
        },
        beforeSend: function(data) {
            $("#konten").html("<center><h3><i class='fa fa-spin fa-spinner'></i> Loading.. </h3></center>");
        },
        success: function(data) {
            $("#konten").html(data);
        }
    });
    return false;
}

filter();

function pdf() {
    var programID   = $(".ProgramID").val();
    var prodiID     = $(".ProdiID").val();
    var tahunMasuk  = $(".TahunMasuk").val();
    var statusMhswID= $(".StatusMhswID").val();
    var TahunID     = $(".TahunID").val();
    var TidakKRS    = $(".TidakKRS").val();
    var keyword     = $(".keyword").val();

    if (prodiID == '') {
        alert("Maaf, anda harus memilih program studi untuk cetak ke PDF, Karena PDF Tidak dapat memuat data yang banyak.");
    } else if(tahunMasuk == '') {
        alert("Maaf, anda harus memilih Tahun Masuk untuk cetak ke PDF, Karena PDF Tidak dapat memuat data yang banyak.");
    } else {
        var link = 'ProgramID=' + programID
                 + '&ProdiID=' + prodiID
                 + '&StatusMhswID=' + statusMhswID
                 + '&TahunMasuk=' + tahunMasuk
                 + '&TidakKRS=' + TidakKRS
                 + '&TahunID=' + TahunID
                 + '&keyword=' + keyword;

        window.open("{{ url('list_tidak_krs/pdf') }}?" + link, "_Blank");
    }
}

function excel() {
    var programID   = $(".ProgramID").val();
    var prodiID     = $(".ProdiID").val();
    var tahunMasuk  = $(".TahunMasuk").val();
    var statusMhswID= $(".StatusMhswID").val();
    var TahunID     = $(".TahunID").val();
    var TidakKRS    = $(".TidakKRS").val();
    var keyword     = $(".keyword").val();

    var link = 'ProgramID=' + programID
             + '&ProdiID=' + prodiID
             + '&StatusMhswID=' + statusMhswID
             + '&TahunMasuk=' + tahunMasuk
             + '&TidakKRS=' + TidakKRS
             + '&TahunID=' + TahunID
             + '&keyword=' + keyword;

    window.open("{{ url('list_tidak_krs/excel') }}?" + link, "_Blank");
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

        $("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
        $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
    }
}

function update_data() {
    if (!confirm('Apakah Anda Yakin? Untuk Mengupdate Data List Mahasiswa Tidak KRS')) return;

    $.ajax({
        type: "POST",
        url: "{{ url('list_tidak_krs/update_data') }}",
        data: {
            _token: "{{ csrf_token() }}"
        },
        beforeSend: function() {
            silahkantunggu();
        },
        success: function(data) {
            alert(data + ' data mahasiswa telah berhasil diproses !');
            window.location.reload();
        },
        error: function(data) {
            $(".alert-error").show();
            $(".alert-error-content").html("Terjadi kesalahan dalam proses update data mahasiswa tidak krs. Jika berlanjut hubungi Administrator.");
            window.setTimeout(function() { $(".alert-error").slideUp("slow"); }, 6000);
        }
    });
    return false;
}
</script>
@endpush
