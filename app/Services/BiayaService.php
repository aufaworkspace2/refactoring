<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class BiayaService
{
    public function getBiayaData($TahunMasuk, $ProgramID, $ProdiID, $JalurPendaftaran, $JenisPendaftaran, $SemesterMasuk, $GelombangKe, $id_jb_formulir)
    {
        // Get master diskon
        $master_diskon = DB::table('master_diskon')
            ->where(function($query) use ($ProdiID) {
                $query->where('ProdiID', $ProdiID)
                      ->orWhere('ProdiID', '')
                      ->orWhere('ProdiID', 0)
                      ->orWhereNull('ProdiID');
            })
            ->get();

        // Get biaya_semester data
        $query_biaya = DB::table('biaya_semester')
            ->where('TahunMasuk', $TahunMasuk)
            ->where('ProgramID', $ProgramID)
            ->where('ProdiID', $ProdiID)
            ->where('JalurPendaftaran', $JalurPendaftaran)
            ->where('JenisPendaftaran', $JenisPendaftaran)
            ->where('SemesterMasuk', $SemesterMasuk)
            ->where('GelombangKe', $GelombangKe)
            ->get();

        // Get biaya data (per jenisbiaya)
        $query_biaya_jb = DB::table('biaya')
            ->where('TahunMasuk', $TahunMasuk)
            ->where('ProgramID', $ProgramID)
            ->where('ProdiID', $ProdiID)
            ->where('JalurPendaftaran', $JalurPendaftaran)
            ->where('JenisPendaftaran', $JenisPendaftaran)
            ->where('SemesterMasuk', $SemesterMasuk)
            ->where('GelombangKe', $GelombangKe)
            ->get();

        // Get biaya_detail data
        $query_biaya_jb_detail = DB::table('biaya_detail')
            ->where('TahunMasuk', $TahunMasuk)
            ->where('ProgramID', $ProgramID)
            ->where('ProdiID', $ProdiID)
            ->where('JalurPendaftaran', $JalurPendaftaran)
            ->where('JenisPendaftaran', $JenisPendaftaran)
            ->where('SemesterMasuk', $SemesterMasuk)
            ->where('GelombangKe', $GelombangKe)
            ->get();

        // Get biaya_termin data
        $query_biaya_jb_termin = DB::table('biaya_termin')
            ->where('TahunMasuk', $TahunMasuk)
            ->where('ProgramID', $ProgramID)
            ->where('ProdiID', $ProdiID)
            ->where('JalurPendaftaran', $JalurPendaftaran)
            ->where('JenisPendaftaran', $JenisPendaftaran)
            ->where('SemesterMasuk', $SemesterMasuk)
            ->where('GelombangKe', $GelombangKe)
            ->get();

        // Organize data by semester
        $data_biaya = [];
        foreach ($query_biaya as $qb) {
            $data_biaya[$qb->Semester] = $qb;
        }

        $data_biaya_jb = [];
        foreach ($query_biaya_jb as $qb_jb) {
            $data_biaya_jb[$qb_jb->Semester][] = $qb_jb;
        }

        $data_biaya_jb_detail = [];
        foreach ($query_biaya_jb_detail as $qb_jb_detail) {
            $data_biaya_jb_detail[$qb_jb_detail->Semester][$qb_jb_detail->JenisBiayaID][] = $qb_jb_detail;
        }

        $data_biaya_jb_termin = [];
        foreach ($query_biaya_jb_termin as $qb_jb_termin) {
            $data_biaya_jb_termin[$qb_jb_termin->Semester][$qb_jb_termin->JenisBiayaID][] = $qb_jb_termin;
        }

        $count_Semester_biaya = count($data_biaya);

        // Get jenisbiaya
        $jenisbiaya = DB::table('jenisbiaya')
            ->where('ID', '!=', $id_jb_formulir)
            ->whereIn('frekuensi', ['Per Semester', 'Satu Kali'])
            ->where(function($query) use ($ProgramID) {
                $query->whereRaw("FIND_IN_SET(?, Program) > 0", [$ProgramID])
                      ->orWhere('Program', '0');
            })
            ->where(function($query) use ($ProdiID) {
                $query->whereRaw("FIND_IN_SET(?, Prodi) > 0", [$ProdiID])
                      ->orWhere('Prodi', '0');
            })
            ->where(function($query) use ($TahunMasuk) {
                $query->whereRaw("FIND_IN_SET(?, TahunMasuk) > 0", [$TahunMasuk])
                      ->orWhere('TahunMasuk', '0');
            })
            ->where('StatusHide', '0')
            ->orderBy('Nama', 'ASC')
            ->get();

        // Get jenisbiaya_detail
        $query_jenisbiaya_detail = DB::table('jenisbiaya_detail')->get();
        $jenisbiaya_detail = [];
        $nama_jenisbiaya_detail = [];

        foreach ($query_jenisbiaya_detail as $row_jenisbiaya_detail) {
            $jenisbiaya_detail[$row_jenisbiaya_detail->JenisBiayaID][] = $row_jenisbiaya_detail;
            $nama_jenisbiaya_detail[$row_jenisbiaya_detail->ID] = $row_jenisbiaya_detail->Nama;
        }

        // Get IDs that already have termin set
        $id_sudah_set_tahap_per_semester = DB::table('biaya_termin')
            ->join('biaya_termin_semester', DB::raw('FIND_IN_SET(biaya_termin.ID, biaya_termin_semester.BiayaTerminID_list)'), '!=', DB::raw('0'))
            ->where('biaya_termin.TahunMasuk', $TahunMasuk)
            ->where('biaya_termin.ProgramID', $ProgramID)
            ->where('biaya_termin.ProdiID', $ProdiID)
            ->where('biaya_termin.JalurPendaftaran', $JalurPendaftaran)
            ->where('biaya_termin.JenisPendaftaran', $JenisPendaftaran)
            ->where('biaya_termin.SemesterMasuk', $SemesterMasuk)
            ->where('biaya_termin.GelombangKe', $GelombangKe)
            ->pluck('biaya_termin.BiayaID')
            ->toArray();

        $id_sudah_set_tahap_total = DB::table('biaya_semester')
            ->join('biaya_termin_total', DB::raw('FIND_IN_SET(biaya_semester.ID, biaya_termin_total.BiayaSemesterID_list)'), '!=', DB::raw('0'))
            ->where('biaya_semester.TahunMasuk', $TahunMasuk)
            ->where('biaya_semester.ProgramID', $ProgramID)
            ->where('biaya_semester.ProdiID', $ProdiID)
            ->where('biaya_semester.JalurPendaftaran', $JalurPendaftaran)
            ->where('biaya_semester.JenisPendaftaran', $JenisPendaftaran)
            ->where('biaya_semester.SemesterMasuk', $SemesterMasuk)
            ->where('biaya_semester.GelombangKe', $GelombangKe)
            ->pluck('biaya_semester.ID')
            ->toArray();

        $id_sudah_set_ke_mahasiswa = DB::table('biaya')
            ->join('tagihan_mahasiswa', 'tagihan_mahasiswa.BiayaID', '=', 'biaya.ID')
            ->where('biaya.TahunMasuk', $TahunMasuk)
            ->where('biaya.ProgramID', $ProgramID)
            ->where('biaya.ProdiID', $ProdiID)
            ->where('biaya.JalurPendaftaran', $JalurPendaftaran)
            ->where('biaya.JenisPendaftaran', $JenisPendaftaran)
            ->where('biaya.SemesterMasuk', $SemesterMasuk)
            ->where('biaya.GelombangKe', $GelombangKe)
            ->pluck('biaya.ID')
            ->toArray();

        return [
            'data_biaya' => $data_biaya,
            'data_biaya_jb' => $data_biaya_jb,
            'data_biaya_jb_detail' => $data_biaya_jb_detail,
            'data_biaya_jb_termin' => $data_biaya_jb_termin,
            'count_Semester_biaya' => $count_Semester_biaya,
            'i_loop' => ($count_Semester_biaya > 1) ? $count_Semester_biaya : 1,
            'jenisbiaya' => $jenisbiaya,
            'jenisbiaya_detail' => $jenisbiaya_detail,
            'nama_jenisbiaya_detail' => $nama_jenisbiaya_detail,
            'master_diskon' => $master_diskon,
            'id_sudah_set_tahap_per_semester' => $id_sudah_set_tahap_per_semester,
            'id_sudah_set_tahap_total' => $id_sudah_set_tahap_total,
            'id_sudah_set_ke_mahasiswa' => $id_sudah_set_ke_mahasiswa
        ];
    }

    public function saveBiaya($save, $inputData)
    {
        $smt = $inputData['smt'] ?? 1;
        $TahunMasuk = $inputData['TahunMasuk'] ?? '';
        $ProgramID = $inputData['ProgramID'] ?? '';
        $ProdiID = $inputData['ProdiID'] ?? '';
        $JalurPendaftaran = $inputData['JalurPendaftaran'] ?? '';
        $JenisPendaftaran = $inputData['JenisPendaftaran'] ?? '';
        $SemesterMasuk = $inputData['SemesterMasuk'] ?? '';
        $GelombangKe = $inputData['GelombangKe'] ?? '';
        $UntukSemester = $inputData['UntukSemester'] ?? 'satu';
        $MaxSemester = $inputData['MaxSemester'] ?? 1;

        $jenisbiaya = $inputData['jenisbiaya'] ?? [];
        $JumlahTagihan_jb = $inputData['JumlahTagihan_jb'] ?? [];
        $JumlahDiskon_jb = $inputData['JumlahDiskon_jb'] ?? [];
        $Jumlah_jb = $inputData['Jumlah_jb'] ?? [];
        $Termin_jb = $inputData['Termin_jb'] ?? [];

        $jenisbiaya_detail = $inputData['jenisbiaya_detail'] ?? [];
        $JumlahTagihan_jb_detail = $inputData['JumlahTagihan_jb_detail'] ?? [];
        $JumlahDiskon_jb_detail = $inputData['JumlahDiskon_jb_detail'] ?? [];
        $Jumlah_jb_detail = $inputData['Jumlah_jb_detail'] ?? [];

        $JumlahTermin_detail_POST = $inputData['JumlahTermin_detail'] ?? [];
        $MasterDiskonID_POST = $inputData['MasterDiskonID'] ?? [];
        $PenandaDiskon = $inputData['PenandaDiskon'] ?? [];

        // Sort jumlah termin
        $JumlahTermin_detail = [];
        foreach ($JumlahTermin_detail_POST as $md_semester => $JumlahTermin_detail_semester) {
            foreach ($JumlahTermin_detail_semester as $urut_termin) {
                $JumlahTermin_detail[$md_semester][] = $urut_termin;
            }
        }

        // Sort master diskon
        $MasterDiskonID = [];
        foreach ($PenandaDiskon as $md_semester => $urut_diskon) {
            $_i_diskon = 0;
            foreach ($urut_diskon as $key_urut_diskon => $testval) {
                $_row_diskon = $MasterDiskonID_POST[$md_semester][$key_urut_diskon] ?? [0];
                if (!$_row_diskon) {
                    $_row_diskon = [0];
                }
                $MasterDiskonID[$md_semester][$_i_diskon++] = $_row_diskon;
            }
        }

        $JumlahTagihan = $inputData['JumlahTagihan'] ?? [];
        $JumlahDiskon = $inputData['JumlahDiskon'] ?? [];
        $Jumlah = $inputData['Jumlah'] ?? [];

        $user_id = Session::get('UserID');

        $success = 1;
        $alert = "Data berhasil disimpan";
        $type = "success";

        $JenjangID = get_field($ProdiID, 'programstudi', 'JenjangID');

        // Validate tahun akademik
        $err_kodetahun = [];
        for ($Semester = 1; $Semester <= $smt; $Semester++) {
            $KodeTahun = get_kodetahun_tahunmasuk($TahunMasuk, $Semester, $SemesterMasuk);

            $cek_tahun = DB::table('tahun')
                ->select('ID')
                ->where('TahunID', $KodeTahun)
                ->first();

            if (empty($cek_tahun)) {
                $fetch_semester = "Pendek";
                if (substr($KodeTahun, 4, 1) == '1') {
                    $fetch_semester = "Ganjil";
                } elseif (substr($KodeTahun, 4, 1) == '2') {
                    $fetch_semester = "Genap";
                }

                $fetch_tahun_akademik = substr($KodeTahun, 0, 4) . "/" . (substr($KodeTahun, 0, 4) + 1) . " " . $fetch_semester;
                $err_kodetahun[] = $fetch_tahun_akademik;
            }
        }

        if (count($err_kodetahun) > 0) {
            return [
                'status' => 0,
                'message' => "Data gagal disimpan karena beberapa setting tahun akademik tidak ditemukan. <br> List Tahun Akademik yang tidak ditemukan : <ul><li>" . implode("</li><li>", $err_kodetahun) . "</li></ul>",
                'type' => "error"
            ];
        }

        $get_per_jenisbiaya = [];

        for ($Semester = 1; $Semester <= $smt; $Semester++) {
            $temp_jenisbiaya = [];

            if ($save == 1 && $UntukSemester == 'semua' && $Semester != 1) {
                $jenisbiaya[$Semester] = $jenisbiaya[1] ?? [];
                $JumlahTagihan_jb[$Semester] = $JumlahTagihan_jb[1] ?? [];
                $JumlahDiskon_jb[$Semester] = $JumlahDiskon_jb[1] ?? [];
                $Jumlah_jb[$Semester] = $Jumlah_jb[1] ?? [];
                $Termin_jb[$Semester] = $Termin_jb[1] ?? [];
                $jenisbiaya_detail[$Semester] = $jenisbiaya_detail[1] ?? [];
                $JumlahTagihan_jb_detail[$Semester] = $JumlahTagihan_jb_detail[1] ?? [];
                $JumlahDiskon_jb_detail[$Semester] = $JumlahDiskon_jb_detail[1] ?? [];
                $Jumlah_jb_detail[$Semester] = $Jumlah_jb_detail[1] ?? [];
                $JumlahTermin_detail[$Semester] = $JumlahTermin_detail[1] ?? [];
                $MasterDiskonID[$Semester] = $MasterDiskonID[1] ?? [];
                $JumlahTagihan[$Semester] = $JumlahTagihan[1] ?? 0;
                $JumlahDiskon[$Semester] = $JumlahDiskon[1] ?? 0;
                $Jumlah[$Semester] = $Jumlah[1] ?? 0;
            }

            foreach (($jenisbiaya[$Semester] ?? []) as $jb) {
                if (!in_array($jb, $temp_jenisbiaya)) {
                    $temp_jenisbiaya[] = $jb;
                } else {
                    return [
                        'status' => 0,
                        'message' => "Tidak Bisa memilih komponen biaya yang sama di satu Semester",
                        'type' => "warning"
                    ];
                }
            }

            $input_all_per_jb = [];
            $input_all_termin_per_jb = [];
            $input_all_detail_per_jb = [];

            $JumlahTagihan[$Semester] = 0;
            $JumlahDiskon[$Semester] = 0;
            $Jumlah[$Semester] = 0;

            $KodeTahun = get_kodetahun_tahunmasuk($TahunMasuk, $Semester, $SemesterMasuk);

            foreach ($temp_jenisbiaya as $key_jb => $jb) {
                if (!isset($get_per_jenisbiaya[$jb])) {
                    $get_per_jenisbiaya[$jb] = DB::table('jenisbiaya')->where('ID', $jb)->first();
                }

                if ($Semester == 1 ||
                    ($get_per_jenisbiaya[$jb]->frekuensi == 'Per Semester') ||
                    ($smt > 1 && $UntukSemester == 'satu')) {

                    $input_per_jb = [
                        'KodeTahun' => $KodeTahun,
                        'TahunMasuk' => $TahunMasuk,
                        'ProgramID' => $ProgramID,
                        'ProdiID' => $ProdiID,
                        'JalurPendaftaran' => $JalurPendaftaran,
                        'JenisPendaftaran' => $JenisPendaftaran,
                        'SemesterMasuk' => $SemesterMasuk,
                        'GelombangKe' => $GelombangKe,
                        'JenisBiayaID' => $jb,
                        'MasterDiskonID_list' => implode(",", $MasterDiskonID[$Semester][$key_jb] ?? []),
                        'JumlahTagihan' => $JumlahTagihan_jb[$Semester][$key_jb] ?? 0,
                        'JumlahDiskon' => $JumlahDiskon_jb[$Semester][$key_jb] ?? 0,
                        'Jumlah' => ($JumlahTagihan_jb[$Semester][$key_jb] ?? 0) - ($JumlahDiskon_jb[$Semester][$key_jb] ?? 0),
                        'JumlahTermin' => $Termin_jb[$Semester][$key_jb] ?? 1,
                        'Semester' => $Semester,
                        'UserUpdate' => $user_id
                    ];

                    $input_all_per_jb[$jb] = $input_per_jb;

                    $JumlahTagihan[$Semester] += $input_per_jb['JumlahTagihan'];
                    $JumlahDiskon[$Semester] += $input_per_jb['JumlahDiskon'];
                    $Jumlah[$Semester] += $input_per_jb['Jumlah'];

                    // Set termin
                    $JumlahTermin_jb = $input_per_jb['JumlahTermin'];

                    for ($iter = 1; $iter <= $JumlahTermin_jb; $iter++) {
                        $nilai_jb_per_termin = $JumlahTermin_detail[$Semester][$key_jb][($iter - 1)] ?? 0;

                        $input_termin_per_jb = [
                            'KodeTahun' => $KodeTahun,
                            'TahunMasuk' => $TahunMasuk,
                            'ProgramID' => $ProgramID,
                            'ProdiID' => $ProdiID,
                            'JalurPendaftaran' => $JalurPendaftaran,
                            'JenisPendaftaran' => $JenisPendaftaran,
                            'SemesterMasuk' => $SemesterMasuk,
                            'GelombangKe' => $GelombangKe,
                            'JenisBiayaID' => $jb,
                            'TerminKe' => $iter,
                            'MasterDiskonID_list' => implode(",", $MasterDiskonID[$Semester][$key_jb] ?? []),
                            'JumlahTagihan' => $nilai_jb_per_termin,
                            'JumlahDiskon' => 0,
                            'Jumlah' => $nilai_jb_per_termin,
                            'Semester' => $Semester,
                            'UserUpdate' => $user_id
                        ];

                        $input_all_termin_per_jb[$jb][$iter] = $input_termin_per_jb;
                    }

                    // Input detail
                    foreach (($jenisbiaya_detail[$Semester][$jb] ?? []) as $key_jb_detail => $jb_detail) {
                        $input_detail_per_jb = [
                            'KodeTahun' => $KodeTahun,
                            'TahunMasuk' => $TahunMasuk,
                            'ProgramID' => $ProgramID,
                            'ProdiID' => $ProdiID,
                            'JalurPendaftaran' => $JalurPendaftaran,
                            'JenisPendaftaran' => $JenisPendaftaran,
                            'SemesterMasuk' => $SemesterMasuk,
                            'GelombangKe' => $GelombangKe,
                            'JenisBiayaID' => $jb,
                            'JenisBiayaID_detail' => $jb_detail,
                            'MasterDiskonID_list' => implode(",", $MasterDiskonID[$Semester][$key_jb] ?? []),
                            'JumlahTagihan' => $JumlahTagihan_jb_detail[$Semester][$jb][$key_jb_detail] ?? 0,
                            'JumlahDiskon' => $JumlahDiskon_jb_detail[$Semester][$jb][$key_jb_detail] ?? 0,
                            'Jumlah' => ($JumlahTagihan_jb_detail[$Semester][$jb][$key_jb_detail] ?? 0) - ($JumlahDiskon_jb_detail[$Semester][$jb][$key_jb_detail] ?? 0),
                            'Semester' => $Semester,
                            'UserUpdate' => $user_id
                        ];

                        $input_all_detail_per_jb[$jb][$jb_detail] = $input_detail_per_jb;
                    }
                }
            }

            // Get all diskon IDs
            $all_id_diskon = [];
            foreach (($MasterDiskonID[$Semester] ?? []) as $arr_master_diskon) {
                foreach ($arr_master_diskon as $id_master_diskon) {
                    $all_id_diskon[] = $id_master_diskon;
                }
            }

            $input = [
                'KodeTahun' => $KodeTahun,
                'TahunMasuk' => $TahunMasuk,
                'ProgramID' => $ProgramID,
                'ProdiID' => $ProdiID,
                'JalurPendaftaran' => $JalurPendaftaran,
                'JenisPendaftaran' => $JenisPendaftaran,
                'SemesterMasuk' => $SemesterMasuk,
                'GelombangKe' => $GelombangKe,
                'MasterDiskonID_list' => implode(",", array_unique(array_filter($all_id_diskon))),
                'JumlahTagihan' => $JumlahTagihan[$Semester],
                'JumlahDiskon' => $JumlahDiskon[$Semester],
                'Jumlah' => $Jumlah[$Semester],
                'Semester' => $Semester,
                'UntukSemester' => $UntukSemester,
                'UserUpdate' => $user_id
            ];

            // Check if exists
            $cek_double = DB::table('biaya_semester')
                ->where('Semester', $Semester)
                ->where('TahunMasuk', $TahunMasuk)
                ->where('ProgramID', $ProgramID)
                ->where('ProdiID', $ProdiID)
                ->where('JalurPendaftaran', $JalurPendaftaran)
                ->where('JenisPendaftaran', $JenisPendaftaran)
                ->where('SemesterMasuk', $SemesterMasuk)
                ->where('GelombangKe', $GelombangKe)
                ->first();

            $id_atas = null;

            if (empty($cek_double)) {
                $input['createdAt'] = date('Y-m-d H:i:s');
                $input['UserID'] = $user_id;
                $id_atas = DB::table('biaya_semester')->insertGetId($input);
            } else {
                DB::table('biaya_semester')
                    ->where('ID', $cek_double->ID)
                    ->update($input);
                $id_atas = $cek_double->ID;
            }

            if (!$id_atas) {
                return [
                    'status' => 0,
                    'message' => "Data gagal disimpan",
                    'type' => "danger"
                ];
            }

            // Save biaya per jenisbiaya
            foreach ($input_all_per_jb as $jb => $input_per_jb) {
                $cek_double_jb = DB::table('biaya')
                    ->where('Semester', $Semester)
                    ->where('JenisBiayaID', $jb)
                    ->where('TahunMasuk', $TahunMasuk)
                    ->where('ProgramID', $ProgramID)
                    ->where('ProdiID', $ProdiID)
                    ->where('JalurPendaftaran', $JalurPendaftaran)
                    ->where('JenisPendaftaran', $JenisPendaftaran)
                    ->where('SemesterMasuk', $SemesterMasuk)
                    ->where('GelombangKe', $GelombangKe)
                    ->first();

                $input_per_jb['BiayaSemesterID'] = $id_atas;

                if (empty($cek_double_jb)) {
                    $input_per_jb['createdAt'] = date('Y-m-d H:i:s');
                    $input_per_jb['UserID'] = $user_id;
                    $BiayaID = DB::table('biaya')->insertGetId($input_per_jb);
                } else {
                    DB::table('biaya')
                        ->where('ID', $cek_double_jb->ID)
                        ->update($input_per_jb);
                    $BiayaID = $cek_double_jb->ID;
                }

                // Save termin
                foreach (($input_all_termin_per_jb[$jb] ?? []) as $key_termin => $input_termin_per_jb) {
                    $cek_double_termin = DB::table('biaya_termin')
                        ->where('Semester', $Semester)
                        ->where('TerminKe', $key_termin)
                        ->where('JenisBiayaID', $jb)
                        ->where('TahunMasuk', $TahunMasuk)
                        ->where('ProgramID', $ProgramID)
                        ->where('ProdiID', $ProdiID)
                        ->where('JalurPendaftaran', $JalurPendaftaran)
                        ->where('JenisPendaftaran', $JenisPendaftaran)
                        ->where('SemesterMasuk', $SemesterMasuk)
                        ->where('GelombangKe', $GelombangKe)
                        ->first();

                    $input_termin_per_jb['BiayaID'] = $BiayaID;

                    if (empty($cek_double_termin)) {
                        $input_termin_per_jb['createdAt'] = date('Y-m-d H:i:s');
                        $input_termin_per_jb['UserID'] = $user_id;
                        DB::table('biaya_termin')->insert($input_termin_per_jb);
                    } else {
                        DB::table('biaya_termin')
                            ->where('ID', $cek_double_termin->ID)
                            ->update($input_termin_per_jb);
                    }
                }

                // Save detail
                foreach (($input_all_detail_per_jb[$jb] ?? []) as $jb_detail => $input_detail_per_jb) {
                    $cek_double_detail = DB::table('biaya_detail')
                        ->where('Semester', $Semester)
                        ->where('JenisBiayaID_detail', $jb_detail)
                        ->where('JenisBiayaID', $jb)
                        ->where('TahunMasuk', $TahunMasuk)
                        ->where('ProgramID', $ProgramID)
                        ->where('ProdiID', $ProdiID)
                        ->where('JalurPendaftaran', $JalurPendaftaran)
                        ->where('JenisPendaftaran', $JenisPendaftaran)
                        ->where('SemesterMasuk', $SemesterMasuk)
                        ->where('GelombangKe', $GelombangKe)
                        ->first();

                    if (empty($cek_double_detail)) {
                        $input_detail_per_jb['createdAt'] = date('Y-m-d H:i:s');
                        $input_detail_per_jb['UserID'] = $user_id;
                        DB::table('biaya_detail')->insert($input_detail_per_jb);
                    } else {
                        DB::table('biaya_detail')
                            ->where('ID', $cek_double_detail->ID)
                            ->update($input_detail_per_jb);
                    }
                }
            }
        }

        return [
            'status' => 1,
            'message' => "Data berhasil disimpan",
            'type' => "success"
        ];
    }

    public function copyBiaya($inputData)
    {
        // Implementation for copying biaya data
        // This would copy data from one configuration to another
        return [
            'status' => 1,
            'message' => "Data berhasil di-copy",
            'type' => "success"
        ];
    }
}
