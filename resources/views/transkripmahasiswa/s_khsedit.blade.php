@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            KHS Mahasiswa
        </h5>
    </div>
    <div class="card-body">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-borderless">
                    <tr>
                        <td width="20%"><strong>Nama</strong></td>
                        <td><strong>{{ $d_mhs->Nama ?? '' }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>NPM</strong></td>
                        <td><strong>{{ $d_mhs->NPM ?? '' }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Program Studi</strong></td>
                        <td><strong>{{ $d_mhs->NamaProdi ?? '' }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Program</strong></td>
                        <td><strong>{{ $d_mhs->NamaProgram ?? '' }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Tahun Akademik</strong></td>
                        <td>
                            <select class="form-control" id="TahunID" onchange="change_nilai()">
                                @foreach($tahuns ?? [] as $thn)
                                    @php $thn = (object) $thn; @endphp
                                    <option value="{{ $thn->ID ?? '' }}">
                                        {{ $thn->TahunID ?? '' }} | {{ $thn->Nama ?? '' }} {{ ($thn->ProsesBuka ?? 0) == 1 ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="col-md-12 text-right">
            <button type="button" class="btn btn-primary waves-effect waves-light" onclick="generateKHS()"><i class="fa fa-refresh"></i> Generate KHS</button>
            <button type="button" class="btn btn-warning waves-effect waves-light" id="edit_mk"><i class="fa fa-pencil"></i> Edit Nilai</button>
            <button type="button" class="btn btn-success waves-effect waves-light" onclick="tampilkan_khs('{{ $d_mhs->ID ?? '' }}')"><i class="fa fa-download"></i> Cetak KHS</button>
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
                        <tbody id="list_khs">
                            <!-- Loaded via AJAX -->
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
                            <th>Total IPS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center"><b data-jml_bobot="true">0.00</b></td>
                            <td class="text-center"><b data-jml_sks="true">0</b></td>
                            <td class="text-center"><b data-ipk="true">0.00</b></td>
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
                <h4 class="modal-title">Data KHS</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label class="col-form-label"><h5 class="mb-0">Tanggal Cetak*</h5></label>
                        <input type="date" id="tgl_cetak" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success" onclick="cetakKHSMhsw()">Cetak KHS</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var nilai_bobot = @json($nilai_bobot ?? []);

    function tampilkan_khs() {
        $('#transkrip_modal').modal('show');
    }

    function change_nilai() {
        $.ajax({
            url: "{{ url('transkripmahasiswa/search_edit_khs') }}",
            data: {
                _token: "{{ csrf_token() }}",
                MhswID: '{{ $d_mhs->ID ?? '' }}',
                TahunID: $('#TahunID').val()
            },
            type: "POST",
            dataType: 'json',
            success: function(data) {
                $('#list_khs').empty();
                var no = 0;
                $.each(data, function(i, v) {
                    no++;
                    var html = '<tr class="baris_khs">';
                    html += '<td class="text-center">' + no + '<input type="hidden" name="ID[]" value="' + v.ID + '"/></td>';
                    html += '<td class="text-center"><input name="Semester[]" type="text" value="' + v.Semester + '" disabled class="form-control"/></td>';
                    html += '<td class="text-center"><input name="MKKode[]" type="text" value="' + v.MKKode + '" disabled class="form-control"/></td>';
                    html += '<td><input name="NamaMatakuliah[]" type="text" value="' + v.NamaMataKuliah + '" disabled class="form-control"/></td>';
                    html += '<td class="text-center"><input onchange="count_bobot(this)" data-count="true" data-mk="' + v.ID + '" name="TotalSKS[]" type="text" value="' + v.TotalSKS + '" disabled class="form-control"/></td>';
                    html += '<td class="text-center">';
                    html += '<select onchange="count_bobot(this)" data-count="true" data-mk="' + v.ID + '" name="Nilai[]" class="form-control" disabled>';
                    html += '<option value="T"></option>';
                    $.each(nilai_bobot, function(idx, nb) {
                        var selected = (v.NilaiHuruf == nb.Nilai) ? 'selected' : '';
                        html += '<option ' + selected + ' data-bobot="' + nb.Bobot + '" value="' + nb.Nilai + '">' + nb.Nilai + ' (' + nb.Bobot + ')</option>';
                    });
                    html += '</select>';
                    html += '<input data-mk="' + v.ID + '" type="hidden" name="Bobot[]" value="' + v.Bobot + '"/>';
                    html += '</td>';
                    html += '<td class="text-center"><b data-mk="' + v.ID + '" data-tot_bobot="true">' + (parseFloat(v.NilaiBobot) || 0).toFixed(2) + '</b></td>';
                    html += '</tr>';
                    $('#list_khs').append(html);
                });
                update_totals();
            }
        });
    }

    function count_bobot(ini) {
        var data_mk = $(ini).data('mk');
        var row = $(ini).closest('tr');
        var data_bobot = row.find('select[name="Nilai[]"]').find('option:selected').data('bobot') || 0;
        var data_sks = row.find('input[name="TotalSKS[]"]').val() || 0;
        
        var bobot_val = (parseFloat(data_bobot) * parseFloat(data_sks));
        row.find('[data-tot_bobot="true"]').html(bobot_val.toFixed(2));
        row.find('input[name="Bobot[]"]').val(data_bobot);
        
        update_totals();
    }

    function update_totals() {
        var total_bobot = 0;
        var total_sks = 0;
        
        $('#list_khs tr').each(function() {
            var row = $(this);
            var val_nilai = row.find('select[name="Nilai[]"]').val();
            var val_bobot = row.find('select[name="Nilai[]"]').find('option:selected').data('bobot') || 0;
            var val_sks = parseFloat(row.find('input[name="TotalSKS[]"]').val() || 0);
            
            var no_count = ['T', '', null];
            if (no_count.indexOf(val_nilai) === -1) {
                total_sks += val_sks;
                total_bobot += (val_sks * parseFloat(val_bobot));
            }
        });
        
        var ipk = (total_sks > 0) ? (total_bobot / total_sks) : 0;
        
        $('[data-jml_bobot="true"]').html(total_bobot.toFixed(2));
        $('[data-jml_sks="true"]').html(total_sks);
        $('[data-ipk="true"]').html(ipk.toFixed(2));
    }

    $('#edit_mk').click(function() {
        $('#f_transkrip').find('input,select').removeAttr('disabled');
    });

    $('#f_transkrip').submit(function(e) {
        e.preventDefault();
        var url = "{{ url('transkripmahasiswa/saverevisinilaikhs') }}";
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
                    change_nilai();
                });
            }
        });
    });

    function generateKHS() {
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
                processKHS(result);
            }
        });
    }

    function processKHS(type) {
        $.ajax({
            type: "POST",
            url: "{{ url('transkripmahasiswa/gen_khs') }}",
            data: {
                _token: "{{ csrf_token() }}",
                MhswID: '{{ $d_mhs->ID ?? '' }}',
                TahunID: $('#TahunID').val(),
                type: type
            },
            success: function(data) {
                if (data == '0') {
                    swal("Informasi !", "Mahasiswa ini tidak memiliki data Rencana Studi untuk di proses.", "info");
                } else {
                    swal("Informasi !", data + " data transkrip telah berhasil diproses !", "success").then(function() {
                        change_nilai();
                    });
                }
            }
        });
    }

    function cetakKHSMhsw() {
        var thn = $("#TahunID").val();
        var id = '{{ $d_mhs->ID ?? '' }}';
        var tgl_cetak = $("#tgl_cetak").val();
        // Adjust the URL below to your actual KHS print route
        window.open("{{ url('hasilstudi/filterPDF') }}?TahunID=" + thn + "&MhswID=" + id + "&tgl_cetak=" + tgl_cetak, "_blank");
    }

    $(document).ready(function() {
        change_nilai();
    });
</script>
@endpush

