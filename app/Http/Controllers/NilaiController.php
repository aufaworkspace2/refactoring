<?php

namespace App\Http\Controllers;

use App\Services\NilaiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class NilaiController extends Controller
{
    protected $service;

    public function __construct(NilaiService $service)
    {
        $this->service = $service;
    }

    /**
     * Main index page
     */
    public function index(Request $request)
    {
        // Permission check could be added here via Middleware
        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Jadwal untuk Input Nilai');
        }

        $data['programs'] = DB::table('program')->orderBy('Nama', 'ASC')->get();
        $data['tahuns'] = DB::table('tahun')->orderBy('TahunID', 'DESC')->get();
        $data['dosens'] = DB::table('dosen')
            ->whereNotNull('Nama')
            ->where('Nama', '!=', '')
            ->where('Nama', '!=', '-')
            ->orderBy('Nama', 'ASC')
            ->get();

        return view('nilai.v_jadwal', $data);
    }

    /**
     * Search schedules for Grade management
     */
    public function search(Request $request)
    {
        $programID = $request->input('programID');
        $tahunID = $request->input('tahunID');
        $prodiID = $request->input('prodiID');
        $kurikulumID = $request->input('kurikulumID');
        $konsentrasiID = $request->input('konsentrasiID');
        $kelasID = $request->input('kelasID');
        $semester = $request->input('semester');
        $keyword = $request->input('keyword');
        $dosenIDArray = $request->input('dosenID', []);
        $dosenID = !empty($dosenIDArray) ? implode(',', $dosenIDArray) : '';

        $query = $this->service->getDataJadwal($programID, $tahunID, $prodiID, $kurikulumID, $konsentrasiID, $kelasID, $semester, $keyword, $dosenID);
        
        $jadwalGabungan = $this->service->getListJadwalGabungan($tahunID);
        $listJadwalGabungan = [];
        foreach ($jadwalGabungan as $value) {
            $listJadwalGabungan[$value->jadwalID] = $value;
            foreach (explode(',', $value->jadwalGabungan) as $subID) {
                $listJadwalGabungan[$subID] = $value;
            }
        }

        $rowSpan = ['mk' => [], 'jadwal' => []];
        $jadwalAll = [];
        $tempPersentase = [];
        $tempValidasiKHS = [];
        $tempValidasiTranskrip = [];
        $tempValidasiDosen = [];
        $tempPersentaseBobot = [];
        $bobotnilai = [];

        foreach ($query as $row) {
            if ($row->jadwalID) {
                // Rowspan logic
                $rowSpan['mk'][$row->matkulID] = ($rowSpan['mk'][$row->matkulID] ?? 0) + 1;
                $rowSpan['jadwal'][$row->matkulID][$row->jadwalID] = ($rowSpan['jadwal'][$row->matkulID][$row->jadwalID] ?? 0) + 1;
                
                $jadwalAll[$row->semester][] = $row;

                $whereJadwal = [];
                if ($row->gabungan == 'YA' && isset($listJadwalGabungan[$row->jadwalID])) {
                    $whereJadwal = explode(',', $listJadwalGabungan[$row->jadwalID]->listJadwal);
                } else {
                    $whereJadwal[] = $row->jadwalID;
                }

                // Stats calculation (simplified from legacy for performance)
                $stats = DB::table('nilai')
                    ->join('rencanastudi', 'rencanastudi.ID', '=', 'nilai.rencanastudiID')
                    ->selectRaw('count(distinct nilai.rencanastudiID) as total, sum(PublishKHS) as khs, sum(PublishTranskrip) as transkrip, sum(ValidasiDosen) as dosen')
                    ->whereIn('rencanastudi.JadwalID', $whereJadwal)
                    ->where('nilai.TahunID', $tahunID)
                    ->first();

                $tempPersentase[$row->jadwalID] = $stats->total ?? 0;
                $tempValidasiKHS[$row->jadwalID] = $stats->khs ?? 0;
                $tempValidasiTranskrip[$row->jadwalID] = $stats->transkrip ?? 0;
                $tempValidasiDosen[$row->jadwalID] = $stats->dosen ?? 0;

                // Bobot Nilai per Jadwal
                $bobotnilai[$row->jadwalID] = $this->service->getBobotNilai($row->matkulID, $row->ProdiID, $tahunID, $row->jadwalID);
                
                // If no specific schedule weights, get prodi defaults
                if ($bobotnilai[$row->jadwalID]->isEmpty()) {
                    $bobotnilai[$row->jadwalID] = $this->service->getBobotNilai($row->matkulID, $row->ProdiID, $tahunID);
                }
            }
        }

        $data = [
            'rowSpan' => $rowSpan,
            'jadwal' => $jadwalAll,
            'TahunID' => $tahunID,
            'ProgramID' => $programID,
            'ProdiID' => $prodiID,
            'Semester' => $semester,
            'persentase' => $tempPersentase,
            'validasiKHS' => $tempValidasiKHS,
            'validasiTranskrip' => $tempValidasiTranskrip,
            'validasiDosen' => $tempValidasiDosen,
            'jadwalGabungan' => $listJadwalGabungan,
            'bobotnilai' => $bobotnilai
        ];

        return view('nilai.s_jadwal', $data);
    }

    /**
     * Filter participants for grade input
     */
    public function filter_peserta(Request $request)
    {
        $jadwalID = $request->input('JadwalID');
        $detailKuri = $request->input('DetailKuri');
        $tahunID = $request->input('TahunID');
        
        $jadwal = DB::table('jadwal')->where('ID', $jadwalID)->first();
        $whereJadwal = [];
        
        if ($jadwal && $jadwal->gabungan == 'YA') {
            $gabungan = DB::table('jadwal_gabungan')->where('jadwalID', $jadwalID)->first();
            $whereJadwal = explode(',', $gabungan->listJadwal ?? '');
        } elseif ($jadwal) {
            $whereJadwal[] = $jadwal->ID;
        }

        $query = $this->service->getPesertaKRS($whereJadwal, $detailKuri, $tahunID);
        
        $tempPresensi = [];
        $arr_cek_grade = [];
        
        foreach ($query as $ps) {
            $tempPresensi[$ps->MhswID] = $this->service->getPersentasePresensi($ps->MhswID, $ps->jadwalID);
            $arr_cek_grade[$ps->MhswID] = get_bobot_angka($ps->MhswID);
        }

        $data = [
            'query' => $query,
            'persentasePresensi' => $tempPresensi,
            'arr_cek_grade' => $arr_cek_grade,
            'jadwalID' => $jadwalID,
            'DetailKurikulumID' => $detailKuri,
            'TahunID' => $tahunID,
            'ProdiID' => $request->input('ProdiID'),
            'KelasID' => $request->input('KelasID'),
            'mk' => DB::table('detailkurikulum')->where('ID', $detailKuri)->first()
        ];

        return view('nilai.input_nilai', $data);
    }

    /**
     * Add grade form
     */
    public function add(Request $request, $jadwalID = 0)
    {
        if ($jadwalID != 0) {
            $jadwal = DB::table('jadwal')->where('ID', $jadwalID)->first();
            $detailkurikulum = DB::table('detailkurikulum')->where('ID', $jadwal->DetailKurikulumID)->first();
            
            $data = [
                'DetailKurikulumID' => $jadwal->DetailKurikulumID,
                'TahunID' => $jadwal->TahunID,
                'mk' => $detailkurikulum,
                'jadwal' => $jadwal,
                'cek_valid' => 0
            ];
        } else {
            $detailkurikulumID = $request->input('detailkurikulumID');
            $tahunID = $request->input('tahunID');
            $detailkurikulum = DB::table('detailkurikulum')->where('ID', $detailkurikulumID)->first();
            
            $data = [
                'DetailKurikulumID' => $detailkurikulumID,
                'TahunID' => $tahunID,
                'mk' => $detailkurikulum,
                'jadwal' => (object)['ID' => 0, 'MKKode' => $detailkurikulum->MKKode, 'TahunID' => $tahunID],
                'cek_valid' => 0
            ];
        }

        return view('nilai.f_nilai', $data);
    }

    /**
     * Save weightings
     */
    public function saveBobot(Request $request)
    {
        $post = $request->all();
        $totalAffected = $this->service->saveBobot($post);

        if ($totalAffected > 0) {
            return response()->json(['status' => '1', 'message' => $totalAffected . ' Data bobot berhasil disimpan !.']);
        }
        return response()->json(['status' => '0', 'message' => 'Mohon maaf data bobot gagal disimpan !.']);
    }

    /**
     * Save student grades
     */
    public function saveNilai(Request $request)
    {
        $post = $request->all();
        $totalAffected = $this->service->saveNilai($post);

        if ($totalAffected > 0) {
            return response()->json(['status' => true, 'message' => $totalAffected . ' data Berhasil disimpan ke dalam sistem ! ']);
        }
        return response()->json(['status' => false, 'message' => 'Mohon maaf tidak ada data nilai yang ditambahkan atau diperbaharui !.']);
    }
}
