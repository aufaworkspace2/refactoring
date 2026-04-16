@if(empty($TahunMasuk) || empty($ProdiID) || empty($ProgramID) || empty($JalurPendaftaran) || empty($JenisPendaftaran) || empty($SemesterMasuk) || empty($GelombangKe))
    <center><h4>-- Tagihan Biaya untuk pilihan di atas belum tersetting --</h4></center>
    @php exit; @endphp
@endif

@php
    $prodi = get_id($ProdiID, 'programstudi');
    $jenjang = $prodi ? get_id($prodi->JenjangID, 'jenjang') : null;

    if (empty($jenjang)) {
        echo "<center><h4>-- Kolom Jenjang untuk data program studi " . ($prodi->Nama ?? '') . " belum tersetting --</h4></center>";
        exit;
    }

    // Ensure data_biaya is an array
    if (!is_array($data_biaya)) {
        $data_biaya = (array) $data_biaya;
    }

    if (!isset($data_biaya[2])) {
        $save = 1;
    } else {
        $save = 2;
    }
@endphp

<style>
.td_jumlah_diskon { display: none; }
.td_total { display: none; }
</style>

<h4 align="center" style="margin-top:0px;">
    {{ ($ProdiID != '') ? " " . $jenjang->Nama . ' | ' . $prodi->Nama : '' }}
    {{ ($ProgramID != '') ? " / " . get_field($ProgramID, 'program') : '' }}
    {{ ($TahunMasuk != '') ? " / Tahun Masuk " . $TahunMasuk : '' }}
    {{ ($JalurPendaftaran != '') ? "<br> " . get_field($JalurPendaftaran, 'pmb_edu_jalur_pendaftaran') : '' }}
    {{ ($JenisPendaftaran != '') ? " / " . get_nama($JenisPendaftaran, 'jenis_pendaftaran') : '' }}
    {{ ($SemesterMasuk != '') ? " / " . get_field($SemesterMasuk, 'semester_masuk') : '' }}
    {{ ($GelombangKe != '') ? " / Gelombang Ke-" . $GelombangKe : '' }}
</h4>

<div class="mt-4">
    <div class="nav nav-tabs">
        <li class="nav-item">
            <a id="nav_default" href="javascript:void(0)" class="nav-link active">Set Biaya</a>
        </li>
        @if(count($data_biaya) > 0)
            <li class="nav-item">
                <a id="nav_tahap_semester" href="javascript:void(0)" onclick="view_set_tahap()" class="nav-link">
                    Set Tahap Pembayaran Per semester
                </a>
            </li>
            <li class="nav-item">
                <a id="nav_tahap_total" href="javascript:void(0)" onclick="view_set_tahap_total()" class="nav-link">
                    Set Tahap Pembayaran Keseluruhan
                </a>
            </li>
        @endif
    </div>
</div>

<div id="panel_set_tagihan">
    <br>
    <div class="alert alert-info">
        <span>Setelah Melakukan Perubahan Jangan Lupa untuk klik tombol <strong>[Simpan Data]</strong> di sebelah kanan bawah</span>
    </div>

    <form id="f_setting_biaya_pendaftaran_pmb" onsubmit="savedata(this); return false;"
          action="{{ url('biaya/save/'.$save) }}" enctype="multipart/form-data">
        <input type="hidden" name="smt" value="{{ (count($data_biaya) > 1) ? count($data_biaya) : 1 }}">
        <input type="hidden" name="TahunMasuk" value="{{ $TahunMasuk }}">
        <input type="hidden" name="ProgramID" value="{{ $ProgramID }}">
        <input type="hidden" name="ProdiID" value="{{ $ProdiID }}">
        <input type="hidden" name="JalurPendaftaran" value="{{ $JalurPendaftaran }}">
        <input type="hidden" name="JenisPendaftaran" value="{{ $JenisPendaftaran }}">
        <input type="hidden" name="SemesterMasuk" value="{{ $SemesterMasuk }}">
        <input type="hidden" name="GelombangKe" value="{{ $GelombangKe }}">
        <input type="hidden" name="MaxSemester" value="{{ $jenjang->BatasSemester }}">

        <div class="button-list">
            <a href="javascript:void(0);" onclick="tambah_semester()" class="btn btn-bordered-secondary">
                <i class="fa fa-plus mr-1"></i> Tambah Semester
            </a>
            @if(count($data_biaya) > 0)
                <a href="javascript:void(0);" onclick="resetBiaya()" class="btn btn-bordered-danger">
                    <i class="fa fa-trash mr-1"></i> Reset Biaya Keseluruhan
                </a>
            @endif
        </div>

        <div class="mt-3">
            <div class="grup accordion custom-accordion" id="accordionSemester">
                @for ($i = 1; $i <= $i_loop; $i++)
                    <div class="card mb-2" id="row_semester_{{ $i }}">
                        <a href="#" class="text-white" data-toggle="collapse" data-target="#div_all_Semester_{{ $i }}"
                           aria-expanded="true" aria-controls="div_all_Semester">
                            <div class="card-header bg-info" data-toggle="collapse" data-target="#div_all_Semester_{{ $i }}"
                                 aria-expanded="true" aria-controls="div_all_Semester">
                                <div class="d-flex justify-content-between">
                                    <h5 class="card-title m-0 text-white">
                                        Biaya Semester {{ $i }}
                                    </h5>
                                    <i class="fa fa-chevron-down"></i>
                                </div>
                            </div>
                        </a>
                        <div id="div_all_Semester_{{ $i }}"
                             class="collapse {{ ($save == 1 && $i == 1) ? 'show' : '' }}"
                             data-parent="#accordionSemester">
                            <div class="card-body">
                                <div class="text-right">
                                    <button type="button" id="add_{{ $i }}" class="btn btn-bordered-success btn-sm"
                                            onclick="addKomponen({{ $i }});">
                                        <i class="fa fa-plus mr-1"></i> Tambah Komponen Biaya
                                    </button>
                                </div>
                                <div class="table-responsive mt-3">
                                    <table class="table table-bordered table-hover" id="tabel_smt_{{ $i }}">
                                        <thead class="bg-primary text-white">
                                            <tr>
                                                <th class="center" width="1%">No.</th>
                                                <th width="25%">Komponen Biaya</th>
                                                <th width="25%">Jumlah Tagihan</th>
                                                <th>Pilih Diskon</th>
                                                <th class="td_jumlah_diskon">Jumlah Diskon</th>
                                                <th class="td_total">Total</th>
                                                <th width="25%">Pilih Termin</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody_{{ $i }}">
                                            @php
                                                $totalTagihan = 0;
                                                $totalDiskon = 0;
                                                $no = 0;
                                            @endphp

                                            @if(!isset($data_biaya[$i]) && $i == 1)
                                                <tr id="tr_{{ $i }}_{{ ++$no }}">
                                                    <td>{{ $no }}</td>
                                                    <td>
                                                        <input type="hidden" name="jenisbiaya[{{ $i }}][]" value="{{ $id_jb_formulir }}">
                                                        @php
                                                            $jb_formulir = DB::table('jenisbiaya')
                                                                ->select('Nama', 'frekuensi')
                                                                ->where('ID', $id_jb_formulir)
                                                                ->first();
                                                        @endphp
                                                        {{ $jb_formulir->Nama }}<br>
                                                        <label class="badge badge-warning" id="label_frekuensi_{{ $i }}_{{ $no }}">
                                                            {{ $jb_formulir->frekuensi }}
                                                        </label>
                                                    </td>
                                                    <td>
                                                        <input type="text" min="0"
                                                               id='JumlahTagihan_jb_{{ $i }}_{{ $no }}'
                                                               onkeyup="changeJumlahdiskon({{ $i }},{{ $no }});changeTermin({{ $i }},{{ $no }});"
                                                               class="form-control currency"
                                                               name="JumlahTagihan_jb[{{ $i }}][]" value="">
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="PenandaDiskon[{{ $i }}][{{ $no }}]" value="1">
                                                        <select name="MasterDiskonID[{{ $i }}][{{ $no }}][]" multiple
                                                                id="changeJumlahdiskon{{ $i }}_{{ $no }}"
                                                                class="form-control MasterDiskonID"
                                                                onchange="changeJumlahdiskon({{ $i }},{{ $no }})">
                                                            @foreach($master_diskon as $row_master_diskon)
                                                                <option value="{{ $row_master_diskon->ID }}"
                                                                        Tipe="{{ $row_master_diskon->Tipe }}"
                                                                        Jumlah="{{ $row_master_diskon->Jumlah }}">
                                                                    {{ $row_master_diskon->Nama . ' ' . (($row_master_diskon->Tipe == 'nominal') ? rupiah($row_master_diskon->Jumlah) : $row_master_diskon->Jumlah . ' %') }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="td_jumlah_diskon">
                                                        <input readonly type="text" min="0"
                                                               id='JumlahDiskon_jb_{{ $i }}_{{ $no }}'
                                                               onkeyup="add_sum({{ $i }})"
                                                               class="form-control currency"
                                                               name="JumlahDiskon_jb[{{ $i }}][]" value="">
                                                    </td>
                                                    <td class="td_total">
                                                        <input type="text" min="0"
                                                               id='Jumlah_jb_{{ $i }}_{{ $no }}'
                                                               onkeyup="add_sum({{ $i }})"
                                                               class="form-control currency"
                                                               name="Jumlah_jb[{{ $i }}][]" value="0" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="Termin_jb[{{ $i }}][]"
                                                               id="Termin_jb_{{ $i }}_{{ $no }}" value="1">
                                                        <div id="Termin_div_{{ $i }}_{{ $no }}" style="display:none;">
                                                            <input type="hidden"
                                                                   id="JumlahTermin_detail_{{ $i }}_{{ $no }}_1"
                                                                   name="JumlahTermin_detail[{{ $i }}][{{ $no }}][]" value="">
                                                        </div>
                                                        1 Termin
                                                    </td>
                                                </tr>
                                            @endif

                                            @foreach(($data_biaya_jb[$i] ?? []) as $key => $row)
                                                @php
                                                    $totalTagihan += $row->JumlahTagihan;
                                                    $totalDiskon += $row->JumlahDiskon;

                                                    $sudah_tahap_semester = isset($id_sudah_set_tahap_per_semester[$row->ID]) ? 1 : 0;
                                                    $sudah_tahap_total = isset($id_sudah_set_tahap_total[$row->BiayaSemesterID]) ? 1 : 0;
                                                    $sudah_set_ke_mahasiswa = isset($id_sudah_set_ke_mahasiswa[$row->ID]) ? 1 : 0;
                                                @endphp
                                                <tr id="tr_{{ $i }}_{{ ++$no }}">
                                                    <td>{{ $no }}</td>
                                                    <td>
                                                        @if($row->JenisBiayaID != $id_jb_formulir)
                                                            @if($sudah_tahap_semester == 1 || $sudah_tahap_total == 1 || $sudah_set_ke_mahasiswa == 1)
                                                                <input type="hidden" value="{{ $row->JenisBiayaID }}"
                                                                       id="jenisbiaya_{{ $i }}_{{ $no }}"
                                                                       name="jenisbiaya[{{ $i }}][]">
                                                                @foreach($jenisbiaya as $key_jb => $value_jb)
                                                                    @if($value_jb->ID == $row->JenisBiayaID)
                                                                        {{ $value_jb->Nama }}
                                                                    @endif
                                                                @endforeach
                                                            @else
                                                                <select id="jenisbiaya_{{ $i }}_{{ $no }}"
                                                                        name="jenisbiaya[{{ $i }}][]"
                                                                        class="form-control"
                                                                        onchange="changefrekuensi({{ $i }},{{ $no }},this.value)" required>
                                                                    @foreach($jenisbiaya as $key_jb => $value_jb)
                                                                        @php
                                                                            $s = ($value_jb->ID == $row->JenisBiayaID) ? 'selected' : '';
                                                                        @endphp
                                                                        <option value="{{ $value_jb->ID }}" {{ $s }}>
                                                                            {{ $value_jb->Nama }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <input type="text" min="0"
                                                               id='JumlahTagihan_jb_{{ $i }}_{{ $no }}'
                                                               onkeyup="changeJumlahdiskon({{ $i }},{{ $no }});changeTermin({{ $i }},{{ $no }});"
                                                               class="form-control currency"
                                                               name="JumlahTagihan_jb[{{ $i }}][]"
                                                               value="{{ $row->JumlahTagihan }}">
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="PenandaDiskon[{{ $i }}][{{ $no }}]" value="1">
                                                        <select name="MasterDiskonID[{{ $i }}][{{ $no }}][]" multiple
                                                                id="changeJumlahdiskon{{ $i }}_{{ $no }}"
                                                                class="form-control MasterDiskonID"
                                                                onchange="changeJumlahdiskon({{ $i }},{{ $no }})">
                                                            @foreach($master_diskon as $row_master_diskon)
                                                                @php
                                                                    $s = (in_array($row_master_diskon->ID, explode(",", $row->MasterDiskonID_list ?? ''))) ? 'selected' : '';
                                                                @endphp
                                                                <option value="{{ $row_master_diskon->ID }}"
                                                                        Tipe="{{ $row_master_diskon->Tipe }}"
                                                                        Jumlah="{{ $row_master_diskon->Jumlah }}" {{ $s }}>
                                                                    {{ $row_master_diskon->Nama . ' ' . (($row_master_diskon->Tipe == 'nominal') ? rupiah($row_master_diskon->Jumlah) : $row_master_diskon->Jumlah . ' %') }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="td_jumlah_diskon">
                                                        <input readonly type="text" min="0"
                                                               id='JumlahDiskon_jb_{{ $i }}_{{ $no }}'
                                                               onkeyup="add_sum({{ $i }})"
                                                               class="form-control currency"
                                                               name="JumlahDiskon_jb[{{ $i }}][]"
                                                               value="{{ $row->JumlahDiskon }}">
                                                    </td>
                                                    <td class="td_total">
                                                        <input type="text" min="0"
                                                               id='Jumlah_jb_{{ $i }}_{{ $no }}'
                                                               onkeyup="add_sum({{ $i }})"
                                                               class="form-control currency"
                                                               name="Jumlah_jb[{{ $i }}][]"
                                                               value="{{ $row->Jumlah }}" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="Termin_jb[{{ $i }}][]"
                                                               id="Termin_jb_{{ $i }}_{{ $no }}"
                                                               value="{{ $row->JumlahTermin ?? 1 }}">
                                                        <div id="Termin_div_{{ $i }}_{{ $no }}">
                                                            @for($iter = 1; $iter <= ($row->JumlahTermin ?? 1); $iter++)
                                                                <input type="hidden"
                                                                       id="JumlahTermin_detail_{{ $i }}_{{ $no }}_{{ $iter }}"
                                                                       name="JumlahTermin_detail[{{ $i }}][{{ $no }}][]"
                                                                       value="{{ ($data_biaya_jb_termin[$i][$row->JenisBiayaID][$iter-1]->JumlahTagihan) ?? 0 }}">
                                                            @endfor
                                                        </div>
                                                        {{ $row->JumlahTermin ?? 1 }} Termin
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="2" class="text-right"><strong>Total</strong></td>
                                                <td><input type="text" class="form-control currency" id="TotalTagihan_{{ $i }}" value="{{ $totalTagihan }}" readonly></td>
                                                <td></td>
                                                <td class="td_jumlah_diskon"><input type="text" class="form-control currency" id="TotalDiskon_{{ $i }}" value="{{ $totalDiskon }}" readonly></td>
                                                <td class="td_total"><input type="text" class="form-control currency" id="Total_{{ $i }}" value="{{ $totalTagihan - $totalDiskon }}" readonly></td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        <div class="text-right mt-3">
            <button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">
                Simpan Data
            </button>
        </div>
    </form>
</div>

<!-- Panel Set Tahap Pembayaran Per Semester -->
<div id="panel_set_tahap_semester" style="display:none;">
    <br>
    <div class="alert alert-info">
        <span>Setelah Melakukan Perubahan Jangan Lupa untuk klik tombol <strong>[Simpan Data]</strong> di sebelah kanan bawah</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="bg-primary text-white">
                <tr>
                    <th class="text-center">No.</th>
                    <th class="text-center">Komponen Biaya</th>
                    <th class="text-center">Jumlah Tagihan</th>
                    <th class="text-center">Pilih Termin</th>
                </tr>
            </thead>
            <tbody>
                @foreach(($data_biaya_jb[1] ?? []) as $key => $row)
                    <tr>
                        <td class="text-center">{{ $key + 1 }}</td>
                        <td>{{ get_field($row->JenisBiayaID, 'jenisbiaya') }}</td>
                        <td class="text-right">{{ rupiah($row->JumlahTagihan) }}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-info" onclick="setTerminPerSemester({{ $row->ID }}, {{ $row->Semester }})">
                                <i class="fa fa-cog"></i> Set Termin
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="text-right mt-3">
        <button type="button" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">
            Simpan Data
        </button>
    </div>
</div>

<!-- Panel Set Tahap Pembayaran Keseluruhan -->
<div id="panel_set_tahap_total" style="display:none;">
    <br>
    <div class="alert alert-info">
        <span>Setelah Melakukan Perubahan Jangan Lupa untuk klik tombol <strong>[Simpan Data]</strong> di sebelah kanan bawah</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="bg-primary text-white">
                <tr>
                    <th class="text-center">No.</th>
                    <th class="text-center">Semester</th>
                    <th class="text-center">Total Tagihan</th>
                    <th class="text-center">Pilih Termin Keseluruhan</th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 1; $i <= $i_loop; $i++)
                    <tr>
                        <td class="text-center">{{ $i }}</td>
                        <td class="text-center">Semester {{ $i }}</td>
                        <td class="text-right">{{ rupiah($total_semester[$i] ?? 0) }}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-info" onclick="setTerminKeseluruhan({{ $i }})">
                                <i class="fa fa-cog"></i> Set Termin
                            </button>
                        </td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
    <div class="text-right mt-3">
        <button type="button" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">
            Simpan Data
        </button>
    </div>
</div>

<script>
// Initialize currency mask and Select2
$(document).ready(function() {
    if (typeof $.fn.mask !== 'undefined') {
        $('.currency').mask('#.##0', {reverse: true});
    }

    // Initialize Select2 for Master Diskon dropdowns
    if (typeof $.fn.select2 !== 'undefined') {
        $('.MasterDiskonID').select2({
            placeholder: "Pilih Diskon",
            allowClear: true,
            width: '100%'
        });
    }

    // Tab switching
    $('#nav_default').click(function() {
        $('#panel_set_tagihan').show();
        $('#panel_set_tahap_semester').hide();
        $('#panel_set_tahap_total').hide();
        $('.nav-link').removeClass('active');
        $(this).addClass('active');
    });

    $('#nav_tahap_semester').click(function() {
        $('#panel_set_tagihan').hide();
        $('#panel_set_tahap_semester').show();
        $('#panel_set_tahap_total').hide();
        $('.nav-link').removeClass('active');
        $(this).addClass('active');
    });

    $('#nav_tahap_total').click(function() {
        $('#panel_set_tagihan').hide();
        $('#panel_set_tahap_semester').hide();
        $('#panel_set_tahap_total').show();
        $('.nav-link').removeClass('active');
        $(this).addClass('active');
    });

    // Initialize totals
    @for ($i = 1; $i <= $i_loop; $i++)
        add_sum({{ $i }});
    @endfor
});

// Add new cost component
function addKomponen(semester) {
    var tbody = $('#tbody_' + semester);
    var no = tbody.find('tr').length + 1;
    
    var row = '<tr id="tr_' + semester + '_' + no + '">' +
        '<td>' + no + '</td>' +
        '<td>' +
            '<select id="jenisbiaya_' + semester + '_' + no + '" name="jenisbiaya[' + semester + '][]" class="form-control" onchange="changefrekuensi(' + semester + ',' + no + ',this.value)" required>' +
            '<option value="">-- Pilih Komponen Biaya --</option>' +
            @foreach($jenisbiaya as $jb)
                '<option value="{{ $jb->ID }}">{{ $jb->Nama }}</option>' +
            @endforeach
            '</select>' +
        '</td>' +
        '<td>' +
            '<input type="text" min="0" id="JumlahTagihan_jb_' + semester + '_' + no + '" onkeyup="changeJumlahdiskon(' + semester + ',' + no + ');changeTermin(' + semester + ',' + no + ');" class="form-control currency" name="JumlahTagihan_jb[' + semester + '][]" value="">' +
        '</td>' +
        '<td>' +
            '<input type="hidden" name="PenandaDiskon[' + semester + '][' + no + ']" value="1">' +
            '<select name="MasterDiskonID[' + semester + '][' + no + '][]" multiple id="changeJumlahdiskon' + semester + '_' + no + '" class="form-control MasterDiskonID" onchange="changeJumlahdiskon(' + semester + ',' + no + ')">' +
            @foreach($master_diskon as $row_master_diskon)
                '<option value="{{ $row_master_diskon->ID }}" Tipe="{{ $row_master_diskon->Tipe }}" Jumlah="{{ $row_master_diskon->Jumlah }}">{{ $row_master_diskon->Nama }} {{ ($row_master_diskon->Tipe == 'nominal') ? rupiah($row_master_diskon->Jumlah) : $row_master_diskon->Jumlah . ' %' }}</option>' +
            @endforeach
            '</select>' +
        '</td>' +
        '<td class="td_jumlah_diskon">' +
            '<input readonly type="text" min="0" id="JumlahDiskon_jb_' + semester + '_' + no + '" onkeyup="add_sum(' + semester + ')" class="form-control currency" name="JumlahDiskon_jb[' + semester + '][]" value="">' +
        '</td>' +
        '<td class="td_total">' +
            '<input type="text" min="0" id="Jumlah_jb_' + semester + '_' + no + '" onkeyup="add_sum(' + semester + ')" class="form-control currency" name="Jumlah_jb[' + semester + '][]" value="0" readonly>' +
        '</td>' +
        '<td>' +
            '<button type="button" class="btn btn-sm btn-danger" onclick="deleteRow(' + semester + ',' + no + ')"><i class="fa fa-trash"></i></button>' +
            '<input type="hidden" name="Termin_jb[' + semester + '][]" id="Termin_jb_' + semester + '_' + no + '" value="1">' +
            '<div id="Termin_div_' + semester + '_' + no + '" style="display:none;">' +
                '<input type="hidden" id="JumlahTermin_detail_' + semester + '_' + no + '_1" name="JumlahTermin_detail[' + semester + '][' + no + '][]" value="">' +
            '</div>' +
            '1 Termin' +
        '</td>' +
    '</tr>';
    
    tbody.append(row);
    
    // Re-initialize Select2 for new dropdown
    if (typeof $.fn.select2 !== 'undefined') {
        $('#changeJumlahdiskon' + semester + '_' + no).select2({
            placeholder: "Pilih Diskon",
            allowClear: true,
            width: '100%'
        });
    }
    
    // Re-initialize currency mask
    if (typeof $.fn.mask !== 'undefined') {
        $('#JumlahTagihan_jb_' + semester + '_' + no).mask('#.##0', {reverse: true});
    }
}

// Add new semester
function tambah_semester() {
    var currentCount = $('.grup .card').length;
    var newSemester = currentCount + 1;
    var maxSemester = $('input[name="MaxSemester"]').val();
    
    if (newSemester > maxSemester) {
        alert('Maksimal semester adalah ' + maxSemester);
        return;
    }
    
    var semesterHtml = '<div class="card mb-2" id="row_semester_' + newSemester + '">' +
        '<a href="#" class="text-white" data-toggle="collapse" data-target="#div_all_Semester_' + newSemester + '" aria-expanded="true" aria-controls="div_all_Semester">' +
            '<div class="card-header bg-info" data-toggle="collapse" data-target="#div_all_Semester_' + newSemester + '" aria-expanded="true" aria-controls="div_all_Semester">' +
                '<div class="d-flex justify-content-between">' +
                    '<h5 class="card-title m-0 text-white">Biaya Semester ' + newSemester + '</h5>' +
                    '<i class="fa fa-chevron-down"></i>' +
                '</div>' +
            '</div>' +
        '</a>' +
        '<div id="div_all_Semester_' + newSemester + '" class="collapse" data-parent="#accordionSemester">' +
            '<div class="card-body">' +
                '<div class="text-right">' +
                    '<button type="button" id="add_' + newSemester + '" class="btn btn-bordered-success btn-sm" onclick="addKomponen(' + newSemester + ')">' +
                        '<i class="fa fa-plus mr-1"></i> Tambah Komponen Biaya' +
                    '</button>' +
                '</div>' +
                '<div class="table-responsive mt-3">' +
                    '<table class="table table-bordered table-hover" id="tabel_smt_' + newSemester + '">' +
                        '<thead class="bg-primary text-white">' +
                            '<tr>' +
                                '<th class="center" width="1%">No.</th>' +
                                '<th width="25%">Komponen Biaya</th>' +
                                '<th width="25%">Jumlah Tagihan</th>' +
                                '<th>Pilih Diskon</th>' +
                                '<th class="td_jumlah_diskon">Jumlah Diskon</th>' +
                                '<th class="td_total">Total</th>' +
                                '<th width="25%">Pilih Termin</th>' +
                            '</tr>' +
                        '</thead>' +
                        '<tbody id="tbody_' + newSemester + '">' +
                        '</tbody>' +
                        '<tfoot>' +
                            '<tr>' +
                                '<td colspan="2" class="text-right"><strong>Total</strong></td>' +
                                '<td><input type="text" class="form-control currency" id="TotalTagihan_' + newSemester + '" value="0" readonly></td>' +
                                '<td></td>' +
                                '<td class="td_jumlah_diskon"><input type="text" class="form-control currency" id="TotalDiskon_' + newSemester + '" value="0" readonly></td>' +
                                '<td class="td_total"><input type="text" class="form-control currency" id="Total_' + newSemester + '" value="0" readonly></td>' +
                                '<td></td>' +
                            '</tr>' +
                        '</tfoot>' +
                    '</table>' +
                '</div>' +
            '</div>' +
        '</div>' +
    '</div>';
    
    $('#accordionSemester').append(semesterHtml);
    
    // Update hidden input
    $('input[name="smt"]').val(newSemester);
}

// Reset all biaya
function resetBiaya() {
    if (confirm('Apakah Anda yakin ingin mereset biaya keseluruhan? Semua data biaya akan dihapus.')) {
        unset_currency();
        
        $.ajax({
            type: 'POST',
            url: "{{ url('biaya/reset') }}",
            data: {
                TahunMasuk: $('input[name="TahunMasuk"]').val(),
                ProgramID: $('input[name="ProgramID"]').val(),
                ProdiID: $('input[name="ProdiID"]').val(),
                JalurPendaftaran: $('input[name="JalurPendaftaran"]').val(),
                JenisPendaftaran: $('input[name="JenisPendaftaran"]').val(),
                SemesterMasuk: $('input[name="SemesterMasuk"]').val(),
                GelombangKe: $('input[name="GelombangKe"]').val(),
                _token: "{{ csrf_token() }}"
            },
            success: function(data) {
                alert('Data biaya berhasil direset');
                location.reload();
            },
            error: function() {
                alert('Gagal mereset data biaya');
            }
        });
    }
}

// Delete row
function deleteRow(semester, no) {
    if (confirm('Hapus komponen biaya ini?')) {
        $('#tr_' + semester + '_' + no).remove();
        add_sum(semester);
    }
}

// Calculate discount
function changeJumlahdiskon(semester, no) {
    var jumlahTagihan = parseFloat($('#JumlahTagihan_jb_' + semester + '_' + no).cleanVal()) || 0;
    var jumlahDiskon = 0;
    
    $('#changeJumlahdiskon' + semester + '_' + no + ' option:selected').each(function() {
        var tipe = $(this).attr('Tipe');
        var jumlah = parseFloat($(this).attr('Jumlah')) || 0;
        
        if (tipe == 'nominal') {
            jumlahDiskon += jumlah;
        } else if (tipe == 'persen') {
            jumlahDiskon += (jumlahTagihan * jumlah) / 100;
        }
    });
    
    if (jumlahDiskon > jumlahTagihan) {
        jumlahDiskon = jumlahTagihan;
    }
    
    $('#JumlahDiskon_jb_' + semester + '_' + no).val(jumlahDiskon).trigger('input');
    $('#Jumlah_jb_' + semester + '_' + no).val(jumlahTagihan - jumlahDiskon).trigger('input');
    
    add_sum(semester);
}

// Calculate termin
function changeTermin(semester, no) {
    // Implementation for termin calculation
    console.log('Change termin:', semester, no);
}

// Calculate totals
function add_sum(semester) {
    var totalTagihan = 0;
    var totalDiskon = 0;
    
    $('#tbody_' + semester + ' tr').each(function() {
        var tagihan = parseFloat($(this).find('[id^="JumlahTagihan_jb_' + semester + '_"]').cleanVal()) || 0;
        var diskon = parseFloat($(this).find('[id^="JumlahDiskon_jb_' + semester + '_"]').cleanVal()) || 0;
        totalTagihan += tagihan;
        totalDiskon += diskon;
    });
    
    $('#TotalTagihan_' + semester).val(totalTagihan).trigger('input');
    $('#TotalDiskon_' + semester).val(totalDiskon).trigger('input');
    $('#Total_' + semester).val(totalTagihan - totalDiskon).trigger('input');
}

// Change frequency label
function changefrekuensi(semester, no, value) {
    // Get frequency from selected jenisbiaya
    @foreach($jenisbiaya as $jb)
        if (value == '{{ $jb->ID }}') {
            $('#label_frekuensi_' + semester + '_' + no).text('{{ $jb->frekuensi }}');
        }
    @endforeach
}

// View set tahap per semester
function view_set_tahap() {
    $('#panel_set_tagihan').hide();
    $('#panel_set_tahap_semester').show();
    $('#panel_set_tahap_total').hide();
    $('.nav-link').removeClass('active');
    $('#nav_tahap_semester').addClass('active');
}

// View set tahap total
function view_set_tahap_total() {
    $('#panel_set_tagihan').hide();
    $('#panel_set_tahap_semester').hide();
    $('#panel_set_tahap_total').show();
    $('.nav-link').removeClass('active');
    $('#nav_tahap_total').addClass('active');
}

function unset_currency() {
    if (typeof $.fn.unmask !== 'undefined') {
        $('.currency').unmask();
    }
}

function set_currency() {
    if (typeof $.fn.mask !== 'undefined') {
        $('.currency').mask('#.##0', {reverse: true});
    }
}

// Form submit handler
$('#f_setting_biaya_pendaftaran_pmb').submit(function(e) {
    e.preventDefault();
    unset_currency();
    
    var formData = new FormData(this);
    
    $.ajax({
        type: 'POST',
        url: $(this).attr('action'),
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function() {
            $('.btnSave').attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
        },
        success: function(data) {
            alert('Data berhasil disimpan');
            location.reload();
        },
        error: function() {
            alert('Gagal menyimpan data');
            $('.btnSave').removeAttr('disabled').html('Simpan Data');
            set_currency();
        }
    });
});
</script>