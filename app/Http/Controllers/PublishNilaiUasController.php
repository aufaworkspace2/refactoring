<?php

namespace App\Http\Controllers;

use App\Services\PublishNilaiUasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PublishNilaiUasController extends Controller
{
    protected $service;

    public function __construct(PublishNilaiUasService $service)
    {
        $this->service = $service;

        $this->middleware(function ($request, $next) {
            if (!Session::get('username')) {
                return redirect('/');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $Create = cek_level(Session::get('LevelUser'), 'c_publish_nilai_uas', 'Create');
        log_akses('View', 'Melihat Daftar Data Jadwal');

        $JadwalID = null;
        $ProgramID = null;
        $ProdiID = null;
        $TahunID = null;
        $DosenID = null;

        if ($request->has('j')) {
            $j = $request->query('j');
            $jadwal = get_id($j, 'jadwal');
            if ($jadwal) {
                $JadwalID = $jadwal->ID;
                $ProgramID = $jadwal->ProgramID;
                $ProdiID = $jadwal->ProdiID;
                $TahunID = $jadwal->TahunID;

                if ($jadwal->DosenID) {
                    $DosenID = $jadwal->DosenID;
                } else {
                    $arr_dosen = explode(",", $jadwal->DosenID);
                    $arr_dosen = array_filter($arr_dosen);
                    $DosenID = $arr_dosen[0] ?? null;
                }
            }
        }

        $list_dosen = $this->service->getDosenList();

        return view('publish_nilai_uas.v_publish_nilai_uas_mengajar_dosen', compact(
            'Create', 'JadwalID', 'ProgramID', 'ProdiID', 'TahunID', 'DosenID', 'list_dosen'
        ));
    }

    public function search(Request $request, $offset = 0)
    {
        $programID = $request->input('ProgramID');
        $prodiID = $request->input('ProdiID');
        $tahunID = $request->input('TahunID');
        $dosenID = $request->input('DosenID');
        $statusPublish = $request->input('Publish');
        $statusInput = $request->input('Input');
        $MKID = $request->input('MKID');
        $JadwalID_input = $request->input('JadwalID');

        $limit = 100000; // Legacy limit
        $listJadwal = $this->service->getJadwalDosen($dosenID, $programID, $prodiID, $tahunID, $JadwalID_input, $limit, 0, $MKID);
        $jadwalGabungan = $this->service->getListJadwalGabungan($tahunID);
        
        $query = [];
        $listJadwalGabungan = [];
        
        foreach ($jadwalGabungan as $value) {
            $listJadwalGabungan[$value->jadwalID] = $value;
            $jadwalExp = explode(',', $value->jadwalGabungan);
            foreach ($jadwalExp as $values) {
                $listJadwalGabungan[$values] = $value;
            }
        }

        $cektanggal = [];
        $cekhari = [];
        $cekwaktu = [];
        $cekruang = [];

        foreach ($listJadwal as $value) {
            $value->belumPublish = 0;
            $value->validasiDosen = 0;
            $value->inputNilai = 0;
            $query[$value->jadwalID] = $value;

            if ($value->jadwalID) {
                $get_publish_nilai_uaswaktu = DB::table('jadwalwaktu')
                    ->join('kodewaktu', 'kodewaktu.ID', '=', 'jadwalwaktu.WaktuID')
                    ->select('jadwalwaktu.*', 'kodewaktu.JamMulai', 'kodewaktu.JamSelesai')
                    ->where('JadwalID', $value->jadwalID)
                    ->whereNotIn('Pertemuan', [98, 99])
                    ->orderBy('Pertemuan', 'ASC')
                    ->orderBy('Sesi', 'ASC')
                    ->get();

                foreach ($get_publish_nilai_uaswaktu as $row) {
                    if (empty($cektanggal[$value->jadwalID][$row->HariID])) {
                        $cektanggal[$value->jadwalID][$row->HariID] = $row->Tanggal;
                    }
                    if (empty($cekhari[$value->jadwalID][$row->HariID])) {
                        $cekhari[$value->jadwalID][$row->HariID] = $row->HariID;
                    }
                    if (empty($cekwaktu[$value->jadwalID][$row->HariID])) {
                        $cekwaktu[$value->jadwalID][$row->HariID] = $row->WaktuID;
                    }
                    if (empty($cekruang[$value->jadwalID][$row->HariID])) {
                        $cekruang[$value->jadwalID][$row->HariID] = $row->RuangID;
                    }
                }
            }
        }

        foreach ($listJadwal as $value) {
            $cekJadwalGabungan = $this->service->checkJadwalGabungan($value->ID);
            if (!empty($cekJadwalGabungan->jadwalID)) {
                $getJadwalHeader = $this->service->getJadwalDosen(null, null, null, null, $cekJadwalGabungan->jadwalID);
                if (!empty($getJadwalHeader)) {
                    $query[$value->jadwalID] = $getJadwalHeader[0];
                }
            }
        }

        $listJadwalID = array_keys($query);

        if (count($listJadwalID) > 0) {
            $getBobotMahasiswa = $this->service->getBobotMahasiswaCount($listJadwalID);
            foreach ($getBobotMahasiswa as $rowBobotMahasiswa) {
                if (isset($query[$rowBobotMahasiswa->JadwalID])) {
                    if (empty($rowBobotMahasiswa->PublishKHS) && empty($rowBobotMahasiswa->PublishTranskrip)) {
                        $query[$rowBobotMahasiswa->JadwalID]->belumPublish += 1;
                    }
                    if (!empty($rowBobotMahasiswa->NilaiID)) {
                        $query[$rowBobotMahasiswa->JadwalID]->inputNilai += 1;
                    }
                    if ($rowBobotMahasiswa->ValidasiDosen == '1') {
                        $query[$rowBobotMahasiswa->JadwalID]->validasiDosen += 1;
                    }
                }
            }
        }

        $query_data = [];
        foreach ($query as $jadwalID => $data_nilai) {
            if (!empty($statusInput) && !empty($statusPublish)) {
                if ($statusInput == 1 && $statusPublish == 1) {
                    if ($data_nilai->inputNilai > 0 && $data_nilai->belumPublish == 0) $query_data[$jadwalID] = $data_nilai;
                } elseif ($statusInput == 1 && $statusPublish == 2) {
                    if ($data_nilai->inputNilai > 0 && $data_nilai->belumPublish > 0) $query_data[$jadwalID] = $data_nilai;
                } elseif ($statusInput == 2 && $statusPublish == 1) {
                    if ($data_nilai->inputNilai == 0 && $data_nilai->belumPublish == 0) $query_data[$jadwalID] = $data_nilai;
                } else {
                    if ($data_nilai->inputNilai == 0 && $data_nilai->belumPublish > 0) $query_data[$jadwalID] = $data_nilai;
                }
            } elseif ($statusInput && empty($statusPublish)) {
                if ($statusInput == 1) {
                    if ($data_nilai->inputNilai > 0) $query_data[$jadwalID] = $data_nilai;
                } else {
                    if ($data_nilai->inputNilai == 0) $query_data[$jadwalID] = $data_nilai;
                }
            } elseif (empty($statusInput) && $statusPublish) {
                if ($statusPublish == 1) {
                    if ($data_nilai->belumPublish == 0) $query_data[$jadwalID] = $data_nilai;
                } else {
                    if ($data_nilai->belumPublish > 0) $query_data[$jadwalID] = $data_nilai;
                }
            } else {
                $query_data[$jadwalID] = $data_nilai;
            }
        }

        $jml = count($query_data);
        $data_query = array_slice($query_data, $offset, 10);

        return view('publish_nilai_uas.s_publish_nilai_uas_mengajar_dosen', [
            'query' => $data_query,
            'cekhari' => $cekhari,
            'dosenID' => $dosenID,
            'cekwaktu' => $cekwaktu,
            'cekruang' => $cekruang,
            'cektanggal' => $cektanggal,
            'jadwalGabungan' => $listJadwalGabungan,
            'statusPublish' => $statusPublish,
            'link' => load_pagination($jml, 10, $offset, 'search_publish_nilai_uas_mengajar_dosen', 'filter'),
            'total_row' => total_row($jml, 10, $offset),
            'offset' => $offset,
            'JadwalID' => $JadwalID_input
        ]);
    }

    public function detail_publish_nilai_uas_mhsw($JadwalID, $kelasID = '', $dosenID = '')
    {
        $jadwal = DB::table('jadwal')->where('ID', $JadwalID)->first();
        if (!$jadwal) abort(404);

        $pesertakrs = DB::table('rombel')->where('JadwalID', $JadwalID)->first();
        $mk = get_id($jadwal->DetailKurikulumID, 'detailkurikulum');
        $query = $this->service->getPesertaKRS([$JadwalID], null);

        // Prepare categories and weights
        $skstetori = $mk->SKSTatapMuka ?? 0;
        $skspraktik = ($mk->SKSPraktikum ?? 0) + ($mk->SKSPraktekLap ?? 0);
        $kategori_jenisbobot = $this->service->getKategoriJenisBobot($skstetori, $skspraktik);
        $kategoriIDs = $kategori_jenisbobot->pluck('ID')->toArray();

        $dataAllBobot = $this->service->getBobotNilai($JadwalID, $kategoriIDs);
        $jenisBobotIDs = $dataAllBobot->pluck('JenisBobotID')->toArray();

        // Prepare student grades and attendance
        $mhswIDs = $query->pluck('MhswID')->toArray();
        $arr_bobot_mahasiswa_all = $this->service->getBobotMahasiswaAll($mhswIDs, $jadwal->DetailKurikulumID, $jadwal->TahunID, $jenisBobotIDs);
        $presensiData = $this->service->getPresensiMahasiswaCount($mhswIDs, $JadwalID);

        return view('publish_nilai_uas.detailjadwalmhsw', [
            'jadwal' => $jadwal,
            'JadwalID' => $JadwalID,
            'dosenID' => $dosenID,
            'pesertakrs' => $pesertakrs,
            'ProdiID' => $jadwal->ProdiID,
            'TahunID' => $jadwal->TahunID,
            'DetailKurikulumID' => $jadwal->DetailKurikulumID,
            'mk' => $mk,
            'query' => $query,
            'kategori_jenisbobot' => $kategori_jenisbobot,
            'dataAllBobot' => $dataAllBobot,
            'arr_bobot_mahasiswa_all' => $arr_bobot_mahasiswa_all,
            'presensiData' => $presensiData
        ]);
    }

    public function publish_all_uas(Request $request)
    {
        $jadwalID = $request->input('jadwalID');
        $tahunID = $request->input('tahunID');
        $tipe = $request->input('tipe') ?: 'Publish';
        $valid = $request->input('valid');
        $selected = $request->input('selected');

        $jadwal = get_id($jadwalID, 'jadwal');
        $detailkurikulumID = $jadwal->DetailKurikulumID;

        $krsID = DB::table('rencanastudi')
            ->whereIn('MhswID', $selected)
            ->where('JadwalID', $jadwalID)
            ->pluck('ID')
            ->toArray();

        $upd = [];
        if ($tipe == 'Publish') {
            $upd['PublishKHS'] = $valid;
            $upd['PublishTranskrip'] = $valid;
        } else {
            $upd['ValidasiDosen'] = $valid;
        }

        $update = DB::table('nilai')->whereIn('rencanastudiID', $krsID)->update($upd);

        if ($update) {
            $upd_bobot_mhsw = [];
            if ($tipe == 'Publish') {
                $upd_bobot_mhsw['Publish'] = $valid;
            } else {
                $upd_bobot_mhsw['ValidasiDosen'] = $valid;
            }

            $query_bm = DB::table('bobot_mahasiswa')
                ->where('TahunID', $tahunID)
                ->where('DetailKurikulumID', $detailkurikulumID)
                ->whereIn('MhswID', $selected);

            if ($valid == 1) {
                $query_bm->update($upd_bobot_mhsw);
            } else if ($valid == 0) {
                $query_bm->whereNotIn('JenisBobotID', [3])->update($upd_bobot_mhsw);
            }

            return response()->json(['status' => '1', 'message' => 'Data Berhasil diubah']);
        }

        return response()->json(['status' => '0', 'message' => 'Data Gagal diubah']);
    }

    public function devmode()
    {
        Session::put('devmode', 1);
    }
}
