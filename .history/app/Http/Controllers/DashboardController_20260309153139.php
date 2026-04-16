<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Cookie;

class DashboardController extends Controller
{
    public function __construct(Request $request)
    {
        // if (!$request->session()->has('username')) {
        //     return redirect()->route('login');
        // }

        // if (!Cookie::get('language')) {
        //     Session::put('language', 'indonesia');
        // }

    }

    public function index(Request $request, $offset = 0)
    {
        try {
            // ================================================================
            // BAGIAN 1: Get Identitas
            // ================================================================

            // CI 3: $identitas = $this->db->get("identitas")->row();
            // Laravel 12:
            $identitas = DB::table("identitas")->first();

            $terisi = 0;
            $total = 10;

            // ================================================================
            // BAGIAN 2: Check Fakultas
            // ================================================================

            // CI 3 Query builder:
            // $this->db->select(...);
            // $this->db->join(...);
            // $this->db->group_by(...);
            // $fakultas = $this->db->get("fakultas")->result_array();

            // Laravel 12:
            $fakultas = DB::table("fakultas as f")
                ->selectRaw("IFNULL(f.FakultasID, '') AS Kode_Fakultas,
                             IFNULL(f.Nama, '') AS Nama_Fakultas,
                             IFNULL(i.ID,'') AS Identias_PT,
                             f.ID")
                ->leftJoin("identitas as i", "i.ID", "=", "f.IdentitasID")
                ->groupBy("f.ID")
                ->get()
                ->toArray();

            if (count($fakultas) > 0) {
                $fakultasHasNull = 0;
                $arrID_Fakultas = [];
                $data_fakultas = [];

                foreach ($fakultas as $row_fakultas) {
                    // Filter null values (sama logic CI 3)
                    $row_arr = (array) $row_fakultas;
                    $fakultasNullValue = array_filter($row_arr, function($v, $k) {
                        if ($v == '') {
                            return $k;
                        }
                    }, ARRAY_FILTER_USE_BOTH);

                    if (count($fakultasNullValue) > 0) {
                        $fakultasHasNull += 1;
                        $data_fakultas[$row_fakultas->Nama_Fakultas] = $fakultasNullValue;
                        $arrID_Fakultas[$row_fakultas->Nama_Fakultas] = $row_fakultas->ID;
                    }
                }

                if ($fakultasHasNull == 0) {
                    $terisi += 1;
                } else {
                    $list_data[3]['alert'] = "Data Mandatory Fakultas Belum Terisi Semua.";
                    $row_fakultas_detail = [];
                    $fakultas_detail = [];

                    foreach ($data_fakultas as $NamaFakultas => $rowNullFakultas) {
                        $row_fakultas_detail[] = "<div class='d-flex justify-content-between'>
                                                    <h4 class='mt-0'>Fakultas $NamaFakultas</h4>
                                                    <a class='hidemodal-alert btn btn-success btn-sm' href='" .
                                                    route('c_fakultas.view', $arrID_Fakultas[$NamaFakultas]) . "'>Lihat disini</a>
                                                  </div>";

                        foreach ($rowNullFakultas as $fieldfakultas => $rnf) {
                            $row_fakultas_detail[] = "&bullet; <b>" . str_replace('_', ' ', $fieldfakultas) . "</b> Belum Diisi<br>";
                        }

                        $fakultas_detail[] = implode('', $row_fakultas_detail);
                        unset($row_fakultas_detail);
                        $row_fakultas_detail = [];
                    }

                    $list_data[3]['detail'] = implode('<br/>', $fakultas_detail);
                }
            } else {
                $list_data[3]['alert'] = "Data Fakultas Belum Terisi.";
            }

            // ================================================================
            // BAGIAN 3: Check Program Studi
            // ================================================================

            $prodi = DB::table("programstudi as ps")
                ->selectRaw("ps.ID,
                            IFNULL(ps.ProdiID,'') AS Kode_Prodi,
                            IFNULL(ps.ProdiDiktiID,'') AS Kode_Dikti,
                            IFNULL(ps.Nama,'') AS Nama_Programstudi,
                            IFNULL(j.ID,'') AS Jenjang,
                            IFNULL(ps.Akreditasi,'') AS Akreditasi,
                            IFNULL(ps.Email,'') AS Email,
                            IF(ps.TglBerdiri='0000-00-00','',ps.TglBerdiri) AS Tanggal_Berdiri,
                            IFNULL(ps.Gelar,'') AS Gelar,
                            IFNULL(ps.SingkatanGelar,'') AS Singkatan_Gelar,
                            IFNULL(ps.KodePMB,'') AS Kode_PMB,
                            IFNULL(ps.TandaTanganKetuaProdi,'') AS Tanda_Tangan_Ketua_Prodi,
                            j.JumlahSemester as Jumlah_Semester")
                ->leftJoin("jenjang as j", "j.ID", "=", "ps.JenjangID")
                ->groupBy("ps.ID")
                ->get()
                ->toArray();

            if (count($prodi) > 0) {
                $prodiHasNull = 0;
                $arrID_Prodi = [];
                $data_prodi = [];

                foreach ($prodi as $row_prodi) {
                    $arr_prodi['ID'] = $row_prodi->ID;
                    $arr_prodi['Kode_Prodi'] = $row_prodi->Kode_Prodi;
                    $arr_prodi['Kode_Dikti'] = $row_prodi->Kode_Dikti;
                    $arr_prodi['Nama_Programstudi'] = $row_prodi->Nama_Programstudi;
                    $arr_prodi['Jenjang'] = $row_prodi->Jenjang;
                    $arr_prodi['Akreditasi'] = $row_prodi->Akreditasi;
                    $arr_prodi['Email'] = $row_prodi->Email;
                    $arr_prodi['Tanggal_Berdiri'] = $row_prodi->Tanggal_Berdiri;
                    $arr_prodi['Gelar'] = $row_prodi->Gelar;
                    $arr_prodi['Singkatan_Gelar'] = $row_prodi->Singkatan_Gelar;
                    $arr_prodi['Kode_PMB'] = $row_prodi->Kode_PMB;
                    $arr_prodi['Tanda_Tangan_Ketua_Prodi'] = $row_prodi->Tanda_Tangan_Ketua_Prodi;

                    $prodiNullValue = array_filter($arr_prodi, function($v, $k) {
                        if ($v == '') {
                            return $k;
                        }
                    }, ARRAY_FILTER_USE_BOTH);

                    if (count($prodiNullValue) > 0) {
                        $prodiHasNull += 1;
                        $data_prodi[$row_prodi->Nama_Programstudi] = $prodiNullValue;
                        $arrID_Prodi[$row_prodi->Nama_Programstudi] = $arr_prodi["ID"];
                    }
                }

                if ($prodiHasNull == 0) {
                    $terisi += 1;
                } else {
                    $list_data[4]['alert'] = "Data Mandatory Program Studi Belum Terisi Semua.";
                    $rowdata_prodi = [];
                    $prodi_detail = [];

                    foreach ($data_prodi as $namaProdi => $rowNullProdi) {
                        $rowdata_prodi[] = "
                            <div class='d-flex justify-content-between'>
                                <h4 class='mt-0'>Programstudi $namaProdi</h4>
                                <a class='hidemodal-alert btn btn-success btn-sm' href='" .
                                route('c_programstudi.view', $arrID_Prodi[$namaProdi]) . "'>Lihat disini</a>
                            </div>";

                        foreach ($rowNullProdi as $fieldProdi => $valProdi) {
                            $rowdata_prodi[] = "&bullet; <b>" . str_replace('_', ' ', $fieldProdi) . "</b> Belum Diisi.<br>";
                        }

                        $prodi_detail[] = implode('', $rowdata_prodi);
                        unset($rowdata_prodi);
                        $rowdata_prodi = [];
                    }

                    $list_data[4]['detail'] = implode("<br/>", $prodi_detail);
                }
            } else {
                $list_data[4]['alert'] = "Data Program Studi Belum Terisi.";
            }

            // ================================================================
            // BAGIAN 4: Check Jumlah Dosen
            // ================================================================

            $jml_dosen = DB::table("dosen")->count();
            if ($jml_dosen > 5) {
                $terisi += 1;
            } else {
                $list_data[5]['alert'] = "Jumlah Dosen Belum Memenuni Kuota Minimum.";
                $list_data[5]['detail'] = "Jumlah Dosen di Aplikasi : <b>$jml_dosen</b> Orang. <br>Jumlah Minimum Dosen : <b>5</b> Orang.";
            }

            // ================================================================
            // BAGIAN 5: Check Jumlah Mahasiswa
            // ================================================================

            $jml_mahasiswa = DB::table("mahasiswa")->count();
            if ($jml_mahasiswa > 100) {
                $terisi += 1;
            } else {
                $list_data[6]['alert'] = "Jumlah Mahasiswa Belum Memenuhi Kuota Minimum.";
                $list_data[6]['detail'] = "Jumlah Mahasiswa di Aplikasi : <b>$jml_mahasiswa</b> Orang. <br>Jumlah Minimum Mahasiswa : <b>100</b> Orang.";
            }

            // ================================================================
            // BAGIAN 6: Check Kurikulum & Matakuliah per Prodi
            // ================================================================

            if (count($prodi) > 0) {
                $hasKurikulum = 0;
                $hasMatakuliah = 0;
                $noKurikulum = [];
                $noMatakuliah = [];

                foreach ($prodi as $row_prodi) {
                    // Check kurikulum
                    $getCountKurikulum = DB::table("kurikulum")
                        ->where('Prodi2', $row_prodi->ID)
                        ->count();

                    if ($getCountKurikulum > 0) {
                        $hasKurikulum += 1;
                    } else {
                        $noKurikulum[] = $row_prodi;
                    }

                    // Check matakuliah per semester
                    $getCountSemesterMatakuliah = DB::table("detailkurikulum")
                        ->where('ProdiID', $row_prodi->ID)
                        ->groupBy("Semester")
                        ->count();

                    $row_prodi->Jumlah_Semester_MK = $getCountSemesterMatakuliah;

                    if ($getCountSemesterMatakuliah >= $row_prodi->Jumlah_Semester) {
                        $hasMatakuliah += 1;
                    } else {
                        $noMatakuliah[] = $row_prodi;
                    }
                }

                if (empty($noKurikulum)) {
                    $terisi += 1;
                } else {
                    $list_data[7]['alert'] = count($noKurikulum) . " Program Studi tidak memiliki Kurikulum.";
                    $kurikulum_detail = [];
                    foreach ($noKurikulum as $NK) {
                        $kurikulum_detail[] = "&bullet; Programstudi <b>" . $NK->Nama_Programstudi . "</b> Belum Memiliki Kurikulum";
                    }
                    $list_data[7]['detail'] = implode('<br/>', $kurikulum_detail);
                }

                if (empty($noMatakuliah)) {
                    $terisi += 1;
                } else {
                    $list_data[8]['alert'] = count($noMatakuliah) . " Program Studi memiliki Jumlah Semester Mata Kuliah yang Belum Sesuai.";
                    $matakuliah_detail = [];
                    foreach ($noMatakuliah as $NMK) {
                        $matakuliah_detail[] = "&bullet; Jumlah Semester Programstudi <b>" . $NMK->Nama_Programstudi . "</b> adalah " . $NMK->Jumlah_Semester_MK . " Semester, Jumlah Seharusnya adalah " . $NMK->Jumlah_Semester . " Semester";
                    }
                    $list_data[8]['detail'] = implode("<br/>", $matakuliah_detail);
                }
            }

            // ================================================================
            // BAGIAN 7: Check Data Bank
            // ================================================================

            $jml_bank = DB::table("bank")->count();
            if ($jml_bank > 0) {
                $terisi += 1;
            } else {
                $list_data[9]['alert'] = "Data Bank Belum Tersedia.";
            }

            // ================================================================
            // BAGIAN 8: Check Komponen Biaya
            // ================================================================

            $jml_komponenbiaya = DB::table("jenisbiaya")->count();
            if ($jml_komponenbiaya > 0) {
                $terisi += 1;
            } else {
                $list_data[10]['alert'] = "Data Komponen Biaya Belum Tersedia.";
            }

            // ================================================================
            // BAGIAN 9: Check Tahun Akademik Aktif
            // ================================================================

            $tahun_akademik = DB::table("tahun")->get()->toArray();
            $ind_tahun_aktif = array_search('1', array_column($tahun_akademik, 'ProsesBuka'));
            $tahun_aktif = isset($tahun_akademik[$ind_tahun_aktif]) ? $tahun_akademik[$ind_tahun_aktif] : null;

            if (count($tahun_akademik) > 0 && !empty($tahun_aktif) && !empty($tahun_aktif->ID)) {
                $terisi += 1;
            } else {
                $list_data[11]['alert'] = "Belum ada Tahun Akademik yang Aktif.";
            }

            // ================================================================
            // BAGIAN 10: Check Event-Event Utama
            // ================================================================

            if (!empty($tahun_aktif) && !empty($tahun_aktif->ID)) {
                $getEvent = DB::table("events as e")
                    ->selectRaw("e.ID as EventID, e.Judul, ed.ID as EventDetailID")
                    ->where("e.Hapus", "Tidak")
                    ->leftJoin("events_detail as ed", function($join) use ($tahun_aktif) {
                        $join->on("ed.EventID", "=", "e.ID")
                             ->where("ed.TahunID", "=", $tahun_aktif->ID);
                    })
                    ->groupBy("e.ID")
                    ->get()
                    ->toArray();

                $nullDetail = [];
                $dataEvent = [];

                foreach ($getEvent as $rowEvent) {
                    if (empty($rowEvent->EventDetailID)) {
                        $nullDetail[] = $rowEvent->EventID;
                    }
                    $dataEvent[$rowEvent->EventID] = $rowEvent;
                }

                if (count($getEvent) > 0 && count($nullDetail) == 0) {
                    $terisi += 1;
                } else {
                    $list_data[12]['alert'] = "Data Event-Event Utama Belum Diset di Kalender Akademik.";
                    $event_detail = [];
                    foreach ($nullDetail as $eventID) {
                        $event_detail[] = "&bullet; <b>" . $dataEvent[$eventID]->Judul . "</b> Belum diset di Kalender Akademik</b> ";
                    }
                    $list_data[12]['detail'] = implode("<br/>", $event_detail);
                }
            } else {
                $list_data[11]['alert'] = "Belum ada Tahun Akademik yang Aktif.";
            }

            // ================================================================
            // BAGIAN 11: Calculate Progress & Migration Info
            // ================================================================

            $data['progress_percent'] = (int)(($terisi / $total) * 100);
            $data['list_alert_progress'] = $list_data ?? [];

            // Get client info untuk migrasi
            $ClientID = $identitas->ClientID ?? null;

            if ($ClientID) {
                $client = DB::connection('mysql')
                    ->table(env('DB_MASTER_NAME') . '.client')
                    ->where('id', $ClientID)
                    ->first();

                if ($client) {
                    $mode_migrasi = $client->mode_migrasi;
                    $persen_migrasi = (int) $client->persen_migrasi;

                    $link_migrasi = 'dashboard/migrasi';
                    $ket_migrasi = 'Migrasi Data Aplikasi';

                    if ($mode_migrasi == 'excel') {
                        $ket_migrasi = 'Migrasi Data dari Excel';
                        $link_migrasi = 'migrasi_excel';
                    } elseif ($mode_migrasi == 'feeder') {
                        $ket_migrasi = 'Migrasi Data dari Feeder';
                        $link_migrasi = 'migrasi_feeder';
                    }

                    $label_migrasi = 'MULAI';
                    if ($persen_migrasi == 100) {
                        $label_migrasi = 'SELESAI';
                    } elseif ($persen_migrasi > 0) {
                        $label_migrasi = 'DIPROSES';
                    }

                    $data['mode_migrasi'] = $mode_migrasi;
                    $data['persen_migrasi'] = $persen_migrasi;
                    $data['label_migrasi'] = $label_migrasi;
                    $data['ket_migrasi'] = $ket_migrasi;
                    $data['link_migrasi'] = $link_migrasi;
                }
            }

            // Get setup CRP
            $get_setup_crp = DB::connection('mysql')
                ->table(env("DB_MASTER_AIS_NAME") . ".setup_app")
                ->where("tipe_setup", "setup_crp")
                ->first();

            if ($get_setup_crp) {
                $data['setup_crp'] = json_decode($get_setup_crp->metadata, true);
            }

            // Check if superadmin untuk tampilkan setup CRP
            $isSuperAdmin = Session::has('cek_superadmin') ? Session::get('cek_superadmin') : false;
            if ($isSuperAdmin && isset($data['setup_crp'])) {
                $data['show_setup_crp'] = count(array_unique(array_filter(array_map(function($v) {
                    return ($v['status_setup'] != 'done') ? $v['status_setup'] : "";
                }, $data['setup_crp']))));
            } else {
                $data['show_setup_crp'] = 0;
            }

            return view('dashboard1', $data);

        } catch (\Exception $e) {
            return view('error', [
                'message' => 'Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    /**
     * Migrasi page
     */
    public function migrasi()
    {
        return view('migrasi');
    }

    /**
     * Test case - untuk development/testing
     */
    public function testcase()
    {
        $arr_migrasi = array(
            "MIGRASI" => array(
                "progress" => "100",
                "status_setup" => "done",
            )
        );
        // Call helper function
        if (function_exists('update_setup_crp')) {
            update_setup_crp($arr_migrasi);
        }
    }
}
