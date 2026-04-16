<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class WelcomeService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function cek_logout($post, $level)
    {
        if (!session('username')) {
            $status = ['status' => 'login'];
        } else {

            $get_setup_crp = DB::table(env("DB_MASTER_AIS_NAME") . ".setup_app")
                ->where("tipe_setup", "setup_crp")
                ->first();

            $metadata_setup_crp = json_decode($get_setup_crp->metadata, true);

            $StatusMigrasi = (int)(
                $metadata_setup_crp['MIGRASI']['progress'] == "100" ||
                $metadata_setup_crp['MIGRASI']['link'] == 'migrasi_feeder'
            );

            $arrLinkMigrasi = [
                "dashboard/migrasi",
                "migrasi_excel",
                "migrasi_feeder"
            ];

            if ($StatusMigrasi == 1) {
                $status = ['status' => 'login'];
            } else {
                $status = [
                    'status' => 'migrasi',
                    'link_migrasi' => (
                        $metadata_setup_crp['MIGRASI']['link'] &&
                        in_array($metadata_setup_crp['MIGRASI']['link'], $arrLinkMigrasi)
                    )
                        ? $metadata_setup_crp['MIGRASI']['link']
                        : ''
                ];
            }
        }

        $uri = explode("/", $post['uri']);
        
        // Panggilan cek_level dari helper
        $akses = cek_level($level, $uri[0], 'Read');

        $allowed_url = [
            "c_konversi/add_internal/prodi", "c_generate_moodle", "c_generatenim_rpl",
            "c_setregistrasiulang_rpl", "c_set_draft_registrasiulang_rpl",
            "c_setup_harga_biaya_variable_rpl", "c_jadwal_asesmen_banding_rpl",
            "mockup_hasil_asesmen", "c_info_alumni", "c_persyaratan_upload_dokumen_mahasiswa",
            "mockup_wizard", "c_enrollment_elearning", "c_import_excel", "c_feeder_import",
            "mockupdashboard", "mockupdashboardmonitoring", "mockupdashboardfinance",
            "Mockuppmb", "c_bkd_karyawan", "Dashboardutama", "Dashboardexecutive",
            "loadingsample", "mockupfeeder", "mockupgeneratejadwal", "dashboardkaryawan",
            "planningmaster", "democalendar", "c_alumni_testimonial", "c_skpi",
            "c_approval_rekomendasi_batal_rencanastudi", "c_alumni_lamaran", "c_range_sidang",
            "rekapabsendosenController", "rekapabsenmhsController", "welcome", "c_comming_soon",
            "c_block", "dashboard", "c_levelmodul", "c_programstudi", "c_approval_izinkaryawan",
            "c_approval_izindosen", "c_pembimbingskripsi", "c_riwayatpendidikandosen",
            "c_riwayatpenelitian", "c_pendidikan_pengajaran", "c_penelitian_pengembanganilmu",
            "c_pengabdian_masyarakat", "c_penunjang_tridarma", "c_plan_dosen", "c_publikasidosen",
            "c_approval_pengajuan_skripsi", "c_tugasakhir", "c_karyawan", "c_pembimbing",
            "c_jabatanstruktural", "C_approval_pengajuan_sidang", "c_cli",
            "lappengajuanskripsiController", "c_laporan_layak_skripsi", "c_bobot_sidang",
            "c_setup_pertemuan_ujian", "c_approval_pengajuan_proposal", "c_payroll",
            "c_riwayat_pendidikan_karyawan", "c_tunjangan_tidak_tetap", "c_pengajaran_karyawan",
            "c_setup_approve_krs", "c_setup_lintas_prodi", "c_rencanastudi",
            "c_setting_redaksi_pmb", "c_setting_template_email", "c_materi_uji_rpl",
            "c_calon_mahasiswa_rpl2", "c_calon_asesor_rpl", "c_calon_asesor_baru_rpl",
            "setup_header_cetak", "sinkron_bpd_bali"
        ];

        if (in_array($uri[0], $allowed_url) || in_array($post['uri'], $allowed_url)) {
            $akses = 'YA';
        }

        $status['akses'] = $akses;

        return $status;
    }

    public function get_modul_grup($url)
    {
        $uri = explode("/", $url);

        $cek = DB::table('submodul')
            ->select('submodul.*', 'modul.MdlGrpID')
            ->join('modul', 'modul.ID', '=', 'submodul.ModulID')
            ->where('submodul.Script', $uri[0])
            ->first();

        if ($cek) {
            $status['akses'] = 'YA';
            $status['MdlGrpID'] = $cek->MdlGrpID;
        } else {
            $cek = DB::table('modul')
                ->select('modul.MdlGrpID')
                ->where('modul.Script', $uri[0])
                ->first();

            if ($cek) {
                $status['akses'] = 'YA';
                $status['MdlGrpID'] = $cek->MdlGrpID;
            } else {
                $status['akses'] = 'TIDAK';
            }
        }

        return $status;
    }

    public function getSidebarMenu($userId, $modulgrup)
    {
        // 1. Get List of Submenus and calculate access rights
        $menus = [];

        // DB Raw Query Translation from CI3 (Modul)
        $modulQuery = DB::select("
            SELECT DISTINCT levelmodul.ModulID, modul.*, levelmodul.Read, levelmodul.Type
            FROM modul
            JOIN levelmodul ON modul.ID = levelmodul.ModulID
            WHERE modul.MdlGrpID = ?
            AND levelmodul.Read = 'YA'
            AND levelmodul.type = 'modul'
            AND levelmodul.LevelID IN (
                SELECT LevelID FROM leveluser WHERE UserID = ?
            )
            ORDER BY modul.Urut, modul.Nama ASC
        ", [$modulgrup, $userId]);

        foreach ($modulQuery as $row) {
            
            // Perlakuan khusus untuk modul 29 (dari CI3 script asli)
            $tutup_akses = 0;
            if ($row->ModulID == 29 && session('devmode') != 1) {
                $tutup_akses = 1;
            }

            if ($tutup_akses != 1) {
                // DB Raw Query Translation from CI3 (Submodul)
                $submodulQuery = DB::select("
                    SELECT DISTINCT levelmodul.ModulID, submodul.*, levelmodul.Read, levelmodul.Type
                    FROM submodul
                    JOIN levelmodul ON submodul.ID = levelmodul.ModulID
                    WHERE submodul.ModulID = ?
                    AND levelmodul.Read = 'YA'
                    AND levelmodul.type = 'submodul'
                    AND levelmodul.LevelID IN (
                        SELECT LevelID FROM leveluser WHERE UserID = ?
                    )
                    ORDER BY submodul.Urut, submodul.Nama ASC
                ", [$row->ModulID, $userId]);

                // Hitung verifikasi khusus modul 430
                $badgeVerif = '';
                if ($row->ModulID == 430) {
                    $jmlVerifResult = DB::select("
                        SELECT count(mahasiswa.ID) as c 
                        FROM mahasiswa 
                        INNER JOIN pmb_tbl_gelombang_detail 
                        ON mahasiswa.gelombang_detail_pmb=pmb_tbl_gelombang_detail.id
                        INNER JOIN pmb_tbl_gelombang 
                        ON pmb_tbl_gelombang_detail.gelombang_id=pmb_tbl_gelombang.id
                        WHERE (mahasiswa.jenis_mhsw='calon' OR mahasiswa.statuslulus_pmb='1') 
                        AND mahasiswa.statusbayar_pmb='0'
                    ");
                    $jmlVerif = $jmlVerifResult[0]->c ?? 0;
                    $badgeVerif = '<span class="badge badge-pill badge-danger" id="divJmlVerif">'.$jmlVerif.'</span>';
                }

                // Submenus array processing
                $submenus = [];
                $levelUserArr = explode(',', session('LevelUser') ?? '');

                foreach ($submodulQuery as $row2) {
                    $namaSubModul = $row2->Nama;
                    // Logic khusus level user 72 dan submodul 283
                    if (in_array('72', $levelUserArr)) {
                        if ($row2->ModulID == 283) {
                            $namaSubModul = 'Cetak Slip Gaji';
                        }
                    }

                    $submenus[] = [
                        'ModulID' => $row2->ModulID,
                        'Nama'    => $namaSubModul,
                        'Script'  => $row2->Script
                    ];
                }

                $menus[] = [
                    'ModulID'    => $row->ModulID,
                    'Nama'       => $row->Nama,
                    'Script'     => $row->Script,
                    'BadgeVerif' => $badgeVerif,
                    'Submenus'   => $submenus
                ];
            }
        }

        return $menus;
    }
}
