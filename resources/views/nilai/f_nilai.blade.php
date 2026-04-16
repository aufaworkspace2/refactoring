@extends('layouts.template1')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Informasi Mata Kuliah</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr><td width="30%"><strong>Mata Kuliah</strong></td><td>: {{ $mk->MKKode }} - {{ $mk->Nama }} ({{ $mk->TotalSKS }} SKS)</td></tr>
                    <tr><td><strong>Program</strong></td><td>: {{ get_field($mk->ProgramID, 'program') }}</td></tr>
                    <tr><td><strong>Program Studi</strong></td><td>: {{ get_field($mk->ProdiID, 'programstudi') }}</td></tr>
                    <tr><td><strong>Kurikulum</strong></td><td>: {{ get_field($mk->KurikulumID, 'kurikulum') }}</td></tr>
                    <tr><td><strong>Kelas</strong></td><td>: {{ $jadwal->KelasID ? get_field($jadwal->KelasID, 'kelas') : '-' }}</td></tr>
                    <tr><td><strong>Dosen</strong></td><td>: {{ $jadwal->DosenID ? get_field($jadwal->DosenID, 'dosen') : '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Setting Bobot</h5>
            </div>
            <div class="card-body">
                <form id="f_bobot" action="{{ url('nilai/saveBobot') }}" method="post">
                    @csrf
                    <input type="hidden" name="detailkurikulumID" value="{{ $DetailKurikulumID }}">
                    <input type="hidden" name="tahunID" value="{{ $TahunID }}">
                    <input type="hidden" name="jadwalID" value="{{ $jadwal->ID }}">

                    @php
                        $SKSTeori = $mk->SKSTatapMuka;
                        $SKSPraktik = $mk->SKSPraktikum + $mk->SKSPraktekLap;
                        $KategoriID = (empty($SKSPraktik) && $SKSTeori > 0) ? 1 : ((empty($SKSTeori) && $SKSPraktik > 0) ? 2 : 0);
                        
                        $kategori_query = DB::table('kategori_jenisbobot');
                        if($KategoriID) $kategori_query->where("ID", $KategoriID);
                        $kategori_jenisbobot = $kategori_query->get();

                        $existing_weights = DB::table('bobotnilai')
                            ->where('JadwalID', $jadwal->ID)
                            ->pluck('Persen', 'JenisBobotID')->toArray();
                    @endphp

                    <table class="table table-sm table-bordered">
                        @foreach($kategori_jenisbobot as $kjb)
                            <tr class="bg-light">
                                <th colspan="3">{{ $kjb->Nama }}</th>
                                <input type="hidden" name="KategoriJenisBobotID[]" value="{{ $kjb->ID }}">
                            </tr>
                            @php 
                                $jenis_bobot = DB::table('jenisbobot')->where('KategoriJenisBobotID', $kjb->ID)->orderBy('Urut')->get();
                            @endphp
                            @foreach($jenis_bobot as $raw)
                                <tr>
                                    <td width="5%">{{ $loop->iteration }}.</td>
                                    <td>{{ $raw->Nama }}</td>
                                    <td width="30%">
                                        <input type="hidden" name="JenisBobotID[{{ $kjb->ID }}][]" value="{{ $raw->ID }}">
                                        <div class="input-group input-group-sm">
                                            <input type="number" class="form-control isib" 
                                                   name="persen[{{ $kjb->ID }}][{{ $raw->ID }}]" 
                                                   data-kategori="{{ $kjb->ID }}"
                                                   value="{{ $existing_weights[$raw->ID] ?? 0 }}" min="0" max="100">
                                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="2" class="text-right"><strong>Total {{ $kjb->Nama }}</strong></td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="totalBobot-{{ $kjb->ID }}" class="form-control totalBobot" readonly>
                                        <div class="input-group-append"><span class="input-group-text">%</span></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                    <div class="text-center">
                        <button type="button" onclick="saveBobot();" class="btn btn-sm btn-success btn-simpan-bobot">Simpan Bobot</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<form id="f_nilai" action="{{ url('nilai/saveNilai') }}" method="post">
    @csrf
    <input type="hidden" name="detailkurikulumID" value="{{ $DetailKurikulumID }}">
    <input type="hidden" name="tahunID" value="{{ $TahunID }}">
    <input type="hidden" name="jadwalID" value="{{ $jadwal->ID }}">
    <input type="hidden" name="typeEdit" id="typeEdit" value="">

    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="m-0">Daftar Nilai Mahasiswa</h5>
            <div>
                <button type="button" onclick="editMode()" class="btn btn-sm btn-info">Edit Nilai</button>
                <button type="button" onclick="saveNilai()" class="btn btn-sm btn-primary">Simpan Nilai</button>
            </div>
        </div>
        <div class="card-body">
            <div id="load_input">
                <center><i class="fa fa-spinner fa-spin"></i> Memuat data...</center>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    filter();
    $('.isib').keyup(function() { calculateTotals(); });
    calculateTotals();
});

function calculateTotals() {
    $('.totalBobot').each(function() {
        var katID = $(this).attr('id').split('-')[1];
        var sum = 0;
        $('.isib[data-kategori="'+katID+'"]').each(function() {
            sum += parseFloat($(this).val() || 0);
        });
        $(this).val(sum.toFixed(2));
    });
}

function filter() {
    $.ajax({
        type: "POST",
        url: "{{ url('nilai/filter_peserta') }}",
        data: {
            _token: "{{ csrf_token() }}",
            DetailKuri: "{{ $DetailKurikulumID }}",
            TahunID: "{{ $TahunID }}",
            JadwalID: "{{ $jadwal->ID }}",
            opsi: 1
        },
        success: function(data) {
            $("#load_input").html(data);
        }
    });
}

function saveBobot() {
    $.ajax({
        type: 'POST',
        url: $('#f_bobot').attr('action'),
        data: $('#f_bobot').serialize(),
        success: function(data) {
            if (data.status == '1') {
                swal("Berhasil", data.message, "success");
                filter();
            } else {
                swal("Gagal", data.message, "error");
            }
        }
    });
}

function saveNilai() {
    $.ajax({
        type: 'POST',
        url: $('#f_nilai').attr('action'),
        data: $('#f_nilai').serialize(),
        success: function(data) {
            if (data.status) {
                swal("Berhasil", data.message, "success");
                filter();
            } else {
                swal("Gagal", data.message, "error");
            }
        }
    });
}

function editMode() {
    $('#typeEdit').val('edit');
    $('.editr').show();
}

function proses(mhswID, rowIdx, katID) {
    // Basic calculation logic for Nilai Akhir
    var total = 0;
    // ... complex logic to sum up weights ...
    // This part should mimic the 'proses' function from legacy
    // For now, let's keep it simple or implement as needed
}
</script>
@endpush
