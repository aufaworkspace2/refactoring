@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            Transkrip Mahasiswa
        </h5>
    </div>
    <div class="card-body">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-borderless">
                    <tr>
                        <td width="20%"><strong>Nama</strong></td>
                        <td><strong>{{ $d_mhs['Nama'] ?? '' }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>NPM</strong></td>
                        <td><strong>{{ $d_mhs['NPM'] ?? '' }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Program Studi</strong></td>
                        <td><strong>{{ $d_mhs['NamaProdi'] ?? '' }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Program</strong></td>
                        <td><strong>{{ $d_mhs['NamaProgram'] ?? '' }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="col-md-12 text-right">
            <button type="button" class="btn btn-primary waves-effect waves-light" onclick="generateTranskrip()"><i class="fa fa-refresh"></i> Generate Transkrip</button>
            <button type="button" class="btn btn-warning waves-effect waves-light" id="edit_mk"><i class="fa fa-pencil"></i> Edit Nilai</button>
            <button type="button" class="btn btn-success waves-effect waves-light" onclick="tampilkan()"><i class="fa fa-download"></i> Cetak Transkrip</button>
            <a href="{{ url('transkripmahasiswa') }}" class="btn btn-danger waves-effect waves-light"><i class="fa fa-backward"></i> Kembali</a>
        </div>
    </div>
</div>

<form id="f_transkrip">
    @csrf
    <div class="card">
        <div class="card-body">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table width="100%" class="table table-hover table-bordered tablesorter">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="text-center" width="2%">No.</th>
                                <th class="text-center" width="2%">Semester</th>
                                <th class="text-center" width="10%">MKKode</th>
                                <th class="text-center" width="35%">Mata Kuliah</th>
                                <th class="text-center" width="7%">SKS</th>
                                <th class="text-center" width="10%">Nilai</th>
                                <th class="text-center" width="7%">Total Bobot</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 0; @endphp
                            @foreach($query ?? [] as $row)
                                @php $row = (object) $row; @endphp
                                <tr class="mahasiswa_{{ $row->ID ?? '' }}">
                                    <td class="text-center">
                                        {{ ++$no }}.
                                        <input data-mk="{{ $row->ID ?? '' }}" type="hidden" name="ID[]" value="{{ $row->ID ?? '' }}"/>
                                    </td>
                                    <td class="text-center">
                                        <input data-mk="{{ $row->ID ?? '' }}" name="Semester[]" type="number" min="1" max="8" maxlength="1" value="{{ $row->Semester ?? '' }}" disabled class="form-control"/>
                                    </td>
                                    <td class="text-center">
                                        <input data-mk="{{ $row->ID ?? '' }}" name="MKKode[]" type="text" value="{{ $row->MKKode ?? '' }}" disabled class="form-control" id="MKKode{{ $row->ID ?? '' }}"/>
                                    </td>
                                    <td>
                                        <input data-mk="{{ $row->ID ?? '' }}" name="NamaMataKuliah[]" type="text" value="{{ $row->NamaMataKuliah ?? '' }}" disabled class="form-control" id="namaMataKuliah{{ $row->ID ?? '' }}"/>
                                    </td>
                                    <td class="text-center">
                                        <input data-count="true" data-mk="{{ $row->ID ?? '' }}" name="TotalSKS[]" type="text" value="{{ $row->TotalSKS ?? '' }}" disabled class="form-control"/>
                                    </td>
                                    <td class="text-center">
                                        <select data-count="true" data-mk="{{ $row->ID ?? '' }}" name="Nilai[]" class="form-control" disabled>
                                            <option value="T"></option>
                                            @foreach($nilai_bobot ?? [] as $th)
                                                @php $th = (object) $th; @endphp
                                                <option data-bobot="{{ $th->Bobot ?? 0 }}" {{ (($row->NilaiHuruf ?? '') == ($th->Nilai ?? '')) ? 'selected' : '' }} value="{{ $th->Nilai ?? '' }}">{{ ($th->Nilai ?? '') . ' (' . ($th->Bobot ?? 0) . ')' }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-center"><b data-mk="{{ $row->ID ?? '' }}" data-tot_bobot="true">{{ number_format((($row->Bobot ?? 0) * ($row->TotalSKS ?? 0)), 2, '.', ' ') }}</b></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="col-md-6 col-sm-12 text-left">
                <table class="table table-bordered">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>Total Jumlah Bobot</th>
                            <th>Total Jumlah SKS</th>
                            <th>Total IPK</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center"><b data-jml_bobot="true">{{ number_format($bobot_total ?? 0, 2, '.', ' ') }}</b></td>
                            <td class="text-center"><b data-jml_sks="true">{{ $sks_total ?? 0 }}</b></td>
                            <td class="text-center"><b data-ipk="true">{{ number_format($ipk ?? 0, 2, '.', ' ') }}</b></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-12 text-right">
                <button type="submit" class="btn btn-primary waves-effect waves-light"><i class="fa fa-save"></i> Simpan Perubahan</button>
            </div>
        </div>
    </div>
</form>

<!-- MODAL -->
<div class="modal" id="transkrip_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Data Transkrip</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                @php
                    $mahasiswa_row = (object) get_id($MhswID, 'mahasiswa');
                    $skripsi = DB::table('skripsi')->where('MhswID', $MhswID)->first();
                    
                    $JudulSkripsi = $mahasiswa_row->JudulSkripsi ?? '';
                    $JudulSkripsiEn = $mahasiswa_row->JudulSkripsi_eng ?? '';

                    if($skripsi){
                        $JudulSkripsi = $skripsi->JudulSidang ?: ($skripsi->Judul ?: $JudulSkripsi);
                        $JudulSkripsiEn = $skripsi->JudulSidangEn ?: ($skripsi->Judul_eng ?: $JudulSkripsiEn);
                    }
                @endphp
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label class="col-form-label"><h5 class="mb-0">Nomor Ijazah Nasional (PIN) *</h5></label>
                        <input type="text" id="nomor" class="form-control" value="{{ $mahasiswa_row->NoIjazahNasional ?? '' }}">
                    </div>
                    <div class="form-group col-md-12">
                        <label class="col-form-label"><h5 class="mb-0">Nomor Seri Ijazah *</h5></label>
                        <input type="text" id="nomorSeriIjazah" class="form-control" value="{{ $mahasiswa_row->NoSeriIjazah ?? '' }}">
                    </div>
                    <div class="form-group col-md-12">
                        <label class="col-form-label"><h5 class="mb-0">Nomor Seri Transkrip *</h5></label>
                        <input type="text" id="transkrip" class="form-control" value="{{ $mahasiswa_row->NoTranskrip ?? '' }}">
                    </div>
                    <div class="form-group col-md-12">
                        <label class="col-form-label"><h5 class="mb-0">Judul Skripsi *</h5></label>
                        <textarea rows="3" class="form-control" id="JudulSkripsi">{{ $JudulSkripsi }}</textarea>
                    </div>
                    <div class="form-group col-md-12">
                        <label class="col-form-label"><h5 class="mb-0">Judul Skripsi Inggris *</h5></label>
                        <textarea rows="3" class="form-control" id="JudulSkripsiEn">{{ $JudulSkripsiEn }}</textarea>
                    </div>
                    <div class="form-group col-md-12">
                        <label class="col-form-label"><h5 class="mb-0">Tanggal Yudisium*</h5></label>
                        <input type="date" id="tgl" class="form-control" value="{{ $mahasiswa_row->TanggalLulus ?? '' }}">
                    </div>
                    <div class="form-group col-md-12">
                        <label class="col-form-label"><h5 class="mb-0">Tanggal Cetak*</h5></label>
                        <input type="date" id="tgl_cetak" class="form-control" value="{{ $mahasiswa_row->TglCetakTranskripNilai ?? '' }}">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="savet()">Transkrip PDF</button>
                <button type="button" class="btn btn-success" onclick="savet_excel()">Transkrip Excel</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function tampilkan() {
        $('#transkrip_modal').modal('show');
    }

    $('[data-count="true"]').change(function() {
        var data_mk = $(this).data('mk');
        var data_bobot = $('[name="Nilai[]"][data-mk="' + data_mk + '"]').find('option:selected').data('bobot') || 0;
        var data_sks = $('[name="TotalSKS[]"][data-mk="' + data_mk + '"]').val() || 0;
        var bobot_val = parseFloat(data_bobot) * parseFloat(data_sks);

        $('[data-mk="' + data_mk + '"][data-tot_bobot="true"]').html(bobot_val.toFixed(2));

        var total_bobot = 0;
        var total_sks = 0;

        $('[name="TotalSKS[]"]').each(function() {
            var mkid = $(this).data('mk');
            var nilai_elem = $('[name="Nilai[]"][data-mk="' + mkid + '"]');
            var val_nilai = nilai_elem.val();
            var val_bobot = nilai_elem.find('option:selected').data('bobot') || 0;

            var no_count = ['T', '', null];
            if (no_count.indexOf(val_nilai) === -1) {
                total_sks += parseFloat($(this).val() || 0);
                total_bobot += (parseFloat($(this).val() || 0) * parseFloat(val_bobot));
            }
        });

        var ipk = (total_sks > 0) ? (total_bobot / total_sks) : 0;
        
        $('[data-jml_bobot="true"]').html(total_bobot.toFixed(2));
        $('[data-jml_sks="true"]').html(total_sks);
        $('[data-ipk="true"]').html(ipk.toFixed(2));
    });

    $('#edit_mk').click(function() {
        $('#f_transkrip').find('input,select').removeAttr('disabled');
    });

    $('#f_transkrip').submit(function(e) {
        e.preventDefault();
        var url = "{{ url('transkripmahasiswa/saverevisinilai') }}";
        $.ajax({
            type: "POST",
            url: url,
            dataType: 'json',
            data: $(this).serialize(),
            success: function(data) {
                swal({
                    title: "Notifikasi !",
                    text: data.success + ' data berhasil di update !',
                    type: 'success'
                }).then(function() {
                    location.reload();
                });
            }
        });
    });

    function savet() {
        var params = {
            nomor: $("#nomor").val(),
            nomorSeriIjazah: $("#nomorSeriIjazah").val(),
            transkrip: $("#transkrip").val(),
            tgl: $("#tgl").val(),
            tgl_cetak: $("#tgl_cetak").val(),
            JudulSkripsi: $("#JudulSkripsi").val(),
            JudulSkripsiEn: $("#JudulSkripsiEn").val()
        };
        var queryString = $.param(params);
        var link = "{{ url('transkripmahasiswa/cetak') }}/{{ $MhswID }}/ASLI/1?" + queryString;
        window.open(link);
    }

    function savet_excel() {
        var params = {
            nomor: $("#nomor").val(),
            nomorSeriIjazah: $("#nomorSeriIjazah").val(),
            transkrip: $("#transkrip").val(),
            tgl: $("#tgl").val(),
            tgl_cetak: $("#tgl_cetak").val(),
            JudulSkripsi: $("#JudulSkripsi").val(),
            JudulSkripsiEn: $("#JudulSkripsiEn").val()
        };
        var queryString = $.param(params);
        var link = "{{ url('transkripmahasiswa/excel') }}/{{ $MhswID }}/ASLI/1?" + queryString;
        window.open(link);
    }

    function generateTranskrip() {
        swal({
            title: 'Pilihan Pengambilan data?',
            type: 'warning',
            input: 'radio',
            showCancelButton: true,
            inputOptions: {
                '0': 'Ambil Data Awal (Hapus & Generate Ulang)',
                '1': 'Perbaharui Data (Hanya Tambah yang Belum Ada)'
            },
            inputValidator: function(result) {
                return new Promise(function(resolve, reject) {
                    if (result) {
                        resolve();
                    } else {
                        reject('Pilih salah satu opsi!');
                    }
                });
            }
        }).then(function(result) {
            if (result) {
                processTranskrip(result);
            }
        });
    }

    function processTranskrip(type) {
        $.ajax({
            type: "POST",
            dataType: 'JSON',
            url: "{{ url('transkripmahasiswa/getTranskrip') }}",
            data: {
                _token: "{{ csrf_token() }}",
                MhswID: '{{ $MhswID }}',
                type: type
            },
            success: function(data) {
                swal({
                    title: "Informasi !",
                    text: data.message,
                    type: data.status == '1' ? 'success' : 'info'
                }).then(function() {
                    if (data.status == '1') {
                        location.reload();
                    }
                });
            }
        });
    }
</script>
@endpush
