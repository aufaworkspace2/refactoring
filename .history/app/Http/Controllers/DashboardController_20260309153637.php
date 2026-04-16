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

    public function index($offset = 0)
    {

        $identitas = DB::table("identitas")->first();

        $terisi = 0;
        $total = 10;

        DB::select("
            SELECT
            IFNULL(fakultas.FakultasID, '') AS Kode_Fakultas,
            IFNULL(fakultas.Nama, '') AS Nama_Fakultas,
            IFNULL(identitas.ID,'') AS Identias_PT,
            fakultas.ID
            FROM fakultas
            LEFT JOIN identitas ON identitas.ID=fakultas.IdentitasID
            GROUP BY fakultas.ID
        ");

        $fakultas = DB::table("fakultas")
            ->selectRaw("IFNULL(fakultas.FakultasID, '') AS Kode_Fakultas,
            IFNULL(fakultas.Nama, '') AS Nama_Fakultas,
            IFNULL(identitas.ID,'') AS Identias_PT,
            fakultas.ID")
            ->leftJoin("identitas", "identitas.ID", "=", "fakultas.IdentitasID")
            ->groupBy("fakultas.ID")
            ->get()
            ->toArray();

        if (count($fakultas) > 0) {

            $fakultasHasNull = 0;
            $arrID_Fakultas = [];

            foreach ($fakultas as $row_fakultas) {

                $row_fakultas = (array)$row_fakultas;

                $fakultasNullValue = array_filter(
                    $row_fakultas,
                    function ($v, $k) {
                        if ($v == '') {
                            return $k;
                        }
                    },
                    ARRAY_FILTER_USE_BOTH
                );

                if (count($fakultasNullValue) > 0) {

                    $fakultasHasNull += 1;
                    $data_fakultas[$row_fakultas['Nama_Fakultas']] = $fakultasNullValue;
                    $arrID_Fakultas[$row_fakultas['Nama_Fakultas']] = $row_fakultas["ID"];
                }
            }

            if ($fakultasHasNull == 0) {
                $terisi += 1;
            } else {

                $list_data[3]['alert'] = "Data Mandatory Fakultas Belum Terisi Semua.";

                foreach ($data_fakultas as $NamaFakultas => $rowNullFakultas) {

                    $row_fakultas_detail[] = "<div class='d-flex justify-content-between'>
                        <h4 class='mt-0'>Fakultas $NamaFakultas</h4>
                        <a class='hidemodal-alert btn btn-success btn-sm' href='" . url('/') . "#c_fakultas/view/" . $arrID_Fakultas[$NamaFakultas] . "'>Lihat disini</a>
                        </div>";

                    foreach ($rowNullFakultas as $fieldfakultas => $rnf) {

                        $row_fakultas_detail[] = "&bullet; <b>" . str_replace('_', ' ', $fieldfakultas) . "</b> Belum Diisi<br>";
                    }

                    $fakultas_detail[] = implode('', $row_fakultas_detail);
                    unset($row_fakultas_detail);
                }

                $list_data[3]['detail'] = implode('<br/>', $fakultas_detail);
            }
        } else {

            $list_data[3]['alert'] = "Data Fakultas Belum Terisi.";
        }


        // $prodi = DB::table("programstudi as ps")
        //     ->selectRaw("
        //         ps.ID,
        //         IFNULL(ps.ProdiID,'') AS Kode_Prodi,
        //         IFNULL(ps.ProdiDiktiID,'') AS Kode_Dikti,
        //         IFNULL(ps.Nama,'') AS Nama_Programstudi,
        //         IFNULL(j.ID,'') AS Jenjang,
        //         IFNULL(ps.Akreditasi,'') AS Akreditasi,
        //         IFNULL(ps.Email,'') AS Email,
        //         IF(ps.TglBerdiri='0000-00-00','',ps.TglBerdiri) AS Tanggal_Berdiri,
        //         IFNULL(ps.Gelar,'') AS Gelar,
        //         IFNULL(ps.SingkatanGelar,'') AS Singkatan_Gelar,
        //         IFNULL(ps.KodePMB,'') AS Kode_PMB,
        //         IFNULL(ps.TandaTanganKetuaProdi,'') AS Tanda_Tangan_Ketua_Prodi,
        //         j.JumlahSemester as Jumlah_Semester
        //     ")
        //     ->leftJoin("jenjang as j", "j.ID", "=", "ps.JenjangID")
        //     ->groupBy("ps.ID")
        //     ->get()
        //     ->toArray();


        $jml_dosen = DB::table("dosen")->count();

        if ($jml_dosen > 5) {
            $terisi += 1;
        } else {

            $list_data[5]['alert'] = "Jumlah Dosen Belum Memenuni Kuota Minimum.";
            $list_data[5]['detail'] = "Jumlah Dosen di Aplikasi : <b>$jml_dosen</b> Orang. <br>Jumlah Minimum Dosen : <b>5</b> Orang.";
        }


        $jml_mahasiswa = DB::table("mahasiswa")->count();

        if ($jml_mahasiswa > 100) {

            $terisi += 1;
        } else {

            $list_data[6]['alert'] = "Jumlah Mahasiswa Belum Memenuhi Kuota Minimum.";
            $list_data[6]['detail'] = "Jumlah Mahasiswa di Aplikasi : <b>$jml_mahasiswa</b> Orang. <br>Jumlah Minimum Mahasiswa : <b>100</b> Orang.";
        }


        $jml_bank = DB::table("bank")->count();

        if ($jml_bank > 0) {

            $terisi += 1;
        } else {

            $list_data[9]['alert'] = "Data Bank Belum Tersedia.";
        }


        $jml_komponenbiaya = DB::table("jenisbiaya")->count();

        if ($jml_komponenbiaya > 0) {

            $terisi += 1;
        } else {

            $list_data[10]['alert'] = "Data Komponen Biaya Belum Tersedia.";
        }


        $tahun_akademik = DB::table("tahun")->get()->toArray();

        $ind_tahun_aktif = array_search('1', array_column($tahun_akademik, 'ProsesBuka'));

        $tahun_aktif = $tahun_akademik[$ind_tahun_aktif] ?? null;

        if (count($tahun_akademik) > 0 && !empty($tahun_aktif->ID)) {

            $terisi += 1;

        } else {

            $list_data[11]['alert'] = "Belum ada Tahun Akademik yang Aktif.";
        }


        $data['progress_percent'] = (int)(($terisi / $total) * 100);
        $data['list_alert_progress'] = $list_data ?? [];


        $ClientID = $identitas->ClientID;

        $client = DB::table(env('DB_MASTER_NAME') . ".client")
            ->where('id', $ClientID)
            ->first();

        $mode_migrasi = $client->mode_migrasi;
        $persen_migrasi = (int)$client->persen_migrasi;

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


        $get_setup_crp = DB::table(env('DB_MASTER_AIS_NAME') . ".setup_app")
            ->where("tipe_setup", "setup_crp")
            ->first();

        $data['setup_crp'] = json_decode($get_setup_crp->metadata, true);


        $data['show_setup_crp'] = session("cek_superadmin")
            ? count(array_unique(array_filter(array_map(function ($v) {
                return ($v['status_setup'] != 'done') ? $v['status_setup'] : "";
            }, $data['setup_crp']))))
            : 0;


        return view('dashboard1', $data);
    }


    public function migrasi()
    {
        return view('migrasi');
    }


    public function testcase()
    {

        $arr_migrasi = [
            "MIGRASI" => [
                "progress" => "100",
                "status_setup" => "done"
            ]
        ];

        update_setup_crp($arr_migrasi);
    }
}
