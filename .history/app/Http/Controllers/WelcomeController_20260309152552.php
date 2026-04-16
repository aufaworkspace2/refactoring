<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WelcomeController extends Controller
{
    public function __construct() {}

    public function index()
    {
        return view('welcome');
    }

    public function cek_logout(Request $request)
    {
        $post = $request->all();

        echo "tes";
        exit;

        if (!session('username')) {

            $status = ['status' => 'logout'];
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

        $level = session('LevelUser');

        $akses = cek_level($level, $uri[0], 'Read');

        $allowed_url = [
            "c_konversi/add_internal/prodi",
            "c_generate_moodle",
            "c_generatenim_rpl",
            "c_setregistrasiulang_rpl",
            "c_set_draft_registrasiulang_rpl",
            "c_setup_harga_biaya_variable_rpl",
            "c_jadwal_asesmen_banding_rpl",
            "mockup_hasil_asesmen",
            "c_info_alumni",
            "c_persyaratan_upload_dokumen_mahasiswa",
            "mockup_wizard",
            "c_enrollment_elearning",
            "c_import_excel",
            "c_feeder_import",
            "mockupdashboard",
            "mockupdashboardmonitoring",
            "mockupdashboardfinance",
            "Mockuppmb",
            "c_bkd_karyawan",
            "Dashboardutama",
            "Dashboardexecutive",
            "loadingsample",
            "mockupfeeder",
            "mockupgeneratejadwal",
            "dashboardkaryawan",
            "planningmaster",
            "democalendar",
            "mockupdashboardmonitoring",
            "mockupdashboardfinance",
            "mockupdashboard",
            "c_alumni_testimonial",
            "c_skpi",
            "c_approval_rekomendasi_batal_rencanastudi",
            "c_alumni_lamaran",
            "c_range_sidang",
            "rekapabsendosenController",
            "rekapabsenmhsController",
            "welcome",
            "c_comming_soon",
            "c_block",
            "dashboard",
            "c_levelmodul",
            "c_programstudi",
            "c_approval_izinkaryawan",
            "c_approval_izindosen",
            "c_pembimbingskripsi",
            "c_riwayatpendidikandosen",
            "c_riwayatpenelitian",
            "c_pendidikan_pengajaran",
            "c_penelitian_pengembanganilmu",
            "c_pengabdian_masyarakat",
            "c_penunjang_tridarma",
            "c_plan_dosen",
            "c_publikasidosen",
            "c_approval_pengajuan_skripsi",
            "c_tugasakhir",
            "c_karyawan",
            "c_pembimbing",
            "c_jabatanstruktural",
            "C_approval_pengajuan_sidang",
            "c_cli",
            "lappengajuanskripsiController",
            "c_laporan_layak_skripsi",
            "c_bobot_sidang",
            "c_setup_pertemuan_ujian",
            "c_approval_pengajuan_proposal",
            "c_payroll",
            "c_riwayat_pendidikan_karyawan",
            "c_tunjangan_tidak_tetap",
            "c_pengajaran_karyawan",
            "c_setup_approve_krs",
            "c_setup_lintas_prodi",
            "c_rencanastudi",
            "c_setting_redaksi_pmb",
            "c_setting_template_email",
            "c_materi_uji_rpl",
            "c_calon_mahasiswa_rpl2",
            "c_calon_asesor_rpl",
            "c_calon_asesor_baru_rpl",
            "setup_header_cetak",
            "sinkron_bpd_bali"
        ];

        if (in_array($uri[0], $allowed_url) || in_array($post['uri'], $allowed_url)) {
            $akses = 'YA';
        }

        $status['akses'] = $akses;

        return response()->json($status);
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
}
