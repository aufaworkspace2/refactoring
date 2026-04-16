<?php

namespace App\Http\Controllers;

use App\Services\PublishNilaiUtsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PublishNilaiUtsController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(PublishNilaiUtsService $service)
    {
        $this->service = $service;

        $this->middleware(function ($request, $next) {
            // Language setup
            $lang = Session::get('language');
            if (!$lang && !request()->cookie('language')) {
                $lang = 'indonesia';
                Session::put('language', $lang);
            }

            // Map legacy language names to Laravel locales
            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);

            $levelUser = Session::get('LevelUser');

            $this->Create = cek_level($levelUser, 'c_publish_nilai_uts', 'Create');
            $this->Update = cek_level($levelUser, 'c_publish_nilai_uts', 'Update');
            $this->Delete = cek_level($levelUser, 'c_publish_nilai_uts', 'Delete');

            return $next($request);
        });
    }

    /**
     * Main index page
     * CI3: C_publish_nilai_uts->index()
     */
    public function index(Request $request)
    {
        $data['Create'] = $this->Create;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Jadwal');
        }

        return view('publish_nilai_uts.v_publish_nilai_uts_mengajar_dosen', $data);
    }

    /**
     * Search publish nilai UTS mengajar dosen
     * CI3: C_publish_nilai_uts->search_publish_nilai_uts_mengajar_dosen()
     */
    public function search_publish_nilai_uts_mengajar_dosen(Request $request, $offset = 0)
    {
        $programID = $request->input('ProgramID', '');
        $prodiID = $request->input('ProdiID', '');
        $tahunID = $request->input('TahunID', '');
        $dosenID = $request->input('DosenID', '');
        $statusPublish = $request->input('Publish', '');
        $statusInput = $request->input('Input', '');
        $mkID = $request->input('MKID', '');

        // Get jadwal dosen
        $listJadwal = $this->service->get_jadwal_dosen($dosenID, $programID, $prodiID, $tahunID, $mkID);

        // Get jadwal gabungan
        $jadwalGabungan = $this->service->get_list_jadwal_gabungan($tahunID, $mkID);
        $query = [];
        $listJadwalGabungan = [];

        foreach ($jadwalGabungan as $value) {
            $listJadwalGabungan[$value->jadwalID] = $value;
            $jadwalExp = explode(',', $value->jadwalGabungan);
            foreach ($jadwalExp as $values) {
                $listJadwalGabungan[$values] = $value;
            }
        }

        $cekhari = [];
        $cekwaktu = [];
        $cekruang = [];
        $cektanggal = [];

        foreach ($listJadwal as $value) {
            $value->belumPublish = 0;
            $value->validasiDosen = 0;
            $value->inputNilai = 0;
            $query[$value->jadwalID] = $value;

            if ($value->jadwalID) {
                $getJadwalWaktu = $this->service->get_jadwal_waktu($value->jadwalID);

                $keyJadwalID = $value->jadwalID;

                foreach ($getJadwalWaktu as $rowJadwalWaktu) {
                    if (isset($rowJadwalWaktu->ID)) {
                        if (empty($cektanggal[$keyJadwalID][$rowJadwalWaktu->HariID])) {
                            $cektanggal[$keyJadwalID][$rowJadwalWaktu->HariID] = $rowJadwalWaktu->Tanggal;
                        }
                        if (empty($cekhari[$keyJadwalID][$rowJadwalWaktu->HariID])) {
                            $cekhari[$keyJadwalID][$rowJadwalWaktu->HariID] = $rowJadwalWaktu->HariID;
                        }
                        if (empty($cekwaktu[$keyJadwalID][$rowJadwalWaktu->HariID])) {
                            $cekwaktu[$keyJadwalID][$rowJadwalWaktu->HariID] = $rowJadwalWaktu->WaktuID;
                        }
                        if (empty($cekruang[$keyJadwalID][$rowJadwalWaktu->HariID])) {
                            $cekruang[$keyJadwalID][$rowJadwalWaktu->HariID] = $rowJadwalWaktu->RuangID;
                        }
                    }
                }
            }
        }

        // Get bobot mahasiswa data
        $listJadwalID = array_map(function($v) {
            return $v->jadwalID;
        }, $query);

        $queryData = [];

        if (count($listJadwalID) > 0) {
            $getBobotMahasiswa = $this->service->get_bobot_mahasiswa_for_jadwal($listJadwalID);

            foreach ($getBobotMahasiswa as $rowBobotMahasiswa) {
                if (empty($rowBobotMahasiswa->Publish)) {
                    $query[$rowBobotMahasiswa->JadwalID]->belumPublish += 1;
                }
                if (!empty($rowBobotMahasiswa->BobotMahasiswaID)) {
                    $query[$rowBobotMahasiswa->JadwalID]->inputNilai += 1;
                }
                if ($rowBobotMahasiswa->ValidasiDosen == '1') {
                    $query[$rowBobotMahasiswa->JadwalID]->validasiDosen += 1;
                }
            }
        }

        // Filter by status
        foreach ($query as $jadwalID => $dataNilai) {
            if (!empty($statusInput) && !empty($statusPublish)) {
                if ($statusInput == 1 && $statusPublish == 1) {
                    if ($dataNilai->inputNilai > 0 && $dataNilai->belumPublish == 0) {
                        $queryData[$jadwalID] = $dataNilai;
                    }
                } elseif ($statusInput == 1 && $statusPublish == 2) {
                    if ($dataNilai->inputNilai > 0 && $dataNilai->belumPublish > 0) {
                        $queryData[$jadwalID] = $dataNilai;
                    }
                } elseif ($statusInput == 2 && $statusPublish == 1) {
                    if ($dataNilai->inputNilai == 0 && $dataNilai->belumPublish == 0) {
                        $queryData[$jadwalID] = $dataNilai;
                    }
                } else {
                    if ($dataNilai->inputNilai == 0 && $dataNilai->belumPublish > 0) {
                        $queryData[$jadwalID] = $dataNilai;
                    }
                }
            } elseif ($statusInput && empty($statusPublish)) {
                if ($statusInput == 1) {
                    if ($dataNilai->inputNilai > 0) {
                        $queryData[$jadwalID] = $dataNilai;
                    }
                } else {
                    if ($dataNilai->inputNilai == 0) {
                        $queryData[$jadwalID] = $dataNilai;
                    }
                }
            } elseif (empty($statusInput) && $statusPublish) {
                if ($statusPublish == 1) {
                    if ($dataNilai->belumPublish == 0) {
                        $queryData[$jadwalID] = $dataNilai;
                    }
                } else {
                    if ($dataNilai->belumPublish > 0) {
                        $queryData[$jadwalID] = $dataNilai;
                    }
                }
            } else {
                $queryData[$jadwalID] = $dataNilai;
            }
        }

        $jml = count($queryData);

        $data['query'] = array_slice($queryData, $offset, 10);
        $data['cekhari'] = $cekhari;
        $data['dosenID'] = $dosenID;
        $data['cekwaktu'] = $cekwaktu;
        $data['cekruang'] = $cekruang;
        $data['cektanggal'] = $cektanggal;
        $data['jadwalGabungan'] = $listJadwalGabungan;
        $data['statusPublish'] = $statusPublish;
        $data['link'] = load_pagination($jml, 10, $offset, 'search_publish_nilai_uts_mengajar_dosen', 'filter');
        $data['total_row'] = total_row($jml, 10, $offset);
        $data['offset'] = $offset;

        return view('publish_nilai_uts.s_publish_nilai_uts_mengajar_dosen', $data);
    }

    /**
     * Detail publish nilai UTS mahasiswa
     * CI3: C_publish_nilai_uts->detail_publish_nilai_uts_mhsw()
     */
    public function detail_publish_nilai_uts_mhsw($jadwalID, $kelasID = '', $dosenID = '')
    {
        $jadwal = DB::table('jadwal')->where('ID', $jadwalID)->first();

        if (!$jadwal) {
            return redirect()->back()->with('error', 'Jadwal tidak ditemukan.');
        }

        $data['jadwal'] = $jadwal;
        $data['JadwalID'] = $jadwalID;
        $data['dosenID'] = $dosenID;
        $data['pesertakrs'] = DB::table('rombel')->where('JadwalID', $jadwalID)->first();
        $data['ProdiID'] = $jadwal->ProdiID;
        $data['TahunID'] = $jadwal->TahunID;
        $data['DetailKurikulumID'] = $jadwal->DetailKurikulumID;

        $whereJadwal[] = $jadwal->ID;
        $data['query'] = $this->service->get_peserta_krs($jadwalID, $kelasID);

        return view('publish_nilai_uts.detailjadwalmhsw', $data);
    }

    /**
     * Publish/validate all UTS nilai
     * CI3: C_publish_nilai_uts->publish_all_uts()
     */
    public function publish_all_uts(Request $request)
    {
        $jadwalID = $request->input('jadwalID');
        $tahunID = $request->input('tahunID');
        $tipe = $request->input('tipe', 'Publish');
        $selected = $request->input('selected', []);
        $valid = $request->input('valid');

        $result = $this->service->publish_all_uts($jadwalID, $tahunID, $selected, $valid, $tipe, Session::get('UserID'));

        return response()->json($result);
    }
}
