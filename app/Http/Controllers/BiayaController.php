<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BiayaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class BiayaController extends Controller
{
    protected $service;
    protected $id_jb_formulir = 32;

    public function __construct(BiayaService $service)
    {
        $this->service = $service;
        $this->id_jb_formulir = 32;

        $this->middleware(function ($request, $next) {
            $lang = Session::get('language');
            if (!$lang && !$request->cookie('language')) {
                $lang = 'indonesia';
                Session::put('language', $lang);
            }

            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $data['save'] = 1;
        $data['identitas'] = DB::table('identitas')->first();

        $data['TahunMasuk'] = $request->input('TahunMasuk', '');
        $data['ProgramID'] = $request->input('ProgramID', '');
        $data['ProdiID'] = $request->input('ProdiID', '');
        $data['JalurPendaftaran'] = $request->input('JalurPendaftaran', '');
        $data['JenisPendaftaran'] = $request->input('JenisPendaftaran', '');
        $data['SemesterMasuk'] = $request->input('SemesterMasuk', '');
        $data['GelombangKe'] = $request->input('GelombangKe', '');

        return view('biaya.v_biaya', $data);
    }

    public function search(Request $request)
    {
        $TahunMasuk = $request->input('TahunMasuk', '');
        $ProgramID = $request->input('ProgramID', '');
        $ProdiID = $request->input('ProdiID', '');
        $JalurPendaftaran = $request->input('JalurPendaftaran', '');
        $JenisPendaftaran = $request->input('JenisPendaftaran', '');
        $SemesterMasuk = $request->input('SemesterMasuk', '');
        $GelombangKe = $request->input('GelombangKe', '');

        if (empty($TahunMasuk) || empty($ProdiID) || empty($ProgramID) ||
            empty($JalurPendaftaran) || empty($JenisPendaftaran) ||
            empty($SemesterMasuk) || empty($GelombangKe)) {
            return view('biaya.s_biaya', [
                'TahunMasuk' => $TahunMasuk,
                'ProgramID' => $ProgramID,
                'ProdiID' => $ProdiID,
                'JalurPendaftaran' => $JalurPendaftaran,
                'JenisPendaftaran' => $JenisPendaftaran,
                'SemesterMasuk' => $SemesterMasuk,
                'GelombangKe' => $GelombangKe,
                'data_biaya' => [],
                'data_biaya_jb' => [],
                'data_biaya_jb_detail' => [],
                'data_biaya_jb_termin' => [],
                'count_Semester_biaya' => 0,
                'i_loop' => 1,
                'jenisbiaya' => [],
                'jenisbiaya_detail' => [],
                'nama_jenisbiaya_detail' => [],
                'master_diskon' => [],
                'id_jb_formulir' => $this->id_jb_formulir,
                'id_sudah_set_tahap_per_semester' => [],
                'id_sudah_set_tahap_total' => [],
                'id_sudah_set_ke_mahasiswa' => []
            ]);
        }

        $data = $this->service->getBiayaData(
            $TahunMasuk, $ProgramID, $ProdiID,
            $JalurPendaftaran, $JenisPendaftaran,
            $SemesterMasuk, $GelombangKe,
            $this->id_jb_formulir
        );

        $data['TahunMasuk'] = $TahunMasuk;
        $data['ProgramID'] = $ProgramID;
        $data['ProdiID'] = $ProdiID;
        $data['JalurPendaftaran'] = $JalurPendaftaran;
        $data['JenisPendaftaran'] = $JenisPendaftaran;
        $data['SemesterMasuk'] = $SemesterMasuk;
        $data['GelombangKe'] = $GelombangKe;
        $data['id_jb_formulir'] = $this->id_jb_formulir;

        // Calculate total per semester for "Set Tahap Pembayaran Keseluruhan" tab
        $total_semester = [];
        foreach ($data['data_biaya_jb'] ?? [] as $semester => $biaya_list) {
            $total = 0;
            foreach ($biaya_list as $biaya) {
                $total += $biaya->JumlahTagihan ?? 0;
            }
            $total_semester[$semester] = $total;
        }
        $data['total_semester'] = $total_semester;

        return view('biaya.s_biaya', $data);
    }

    public function reset(Request $request)
    {
        $TahunMasuk = $request->input('TahunMasuk', '');
        $ProgramID = $request->input('ProgramID', '');
        $ProdiID = $request->input('ProdiID', '');
        $JalurPendaftaran = $request->input('JalurPendaftaran', '');
        $JenisPendaftaran = $request->input('JenisPendaftaran', '');
        $SemesterMasuk = $request->input('SemesterMasuk', '');
        $GelombangKe = $request->input('GelombangKe', '');

        // Delete all related biaya data
        DB::table('biaya_termin')
            ->where('TahunMasuk', $TahunMasuk)
            ->where('ProgramID', $ProgramID)
            ->where('ProdiID', $ProdiID)
            ->where('JalurPendaftaran', $JalurPendaftaran)
            ->where('JenisPendaftaran', $JenisPendaftaran)
            ->where('SemesterMasuk', $SemesterMasuk)
            ->where('GelombangKe', $GelombangKe)
            ->delete();

        DB::table('biaya_detail')
            ->where('TahunMasuk', $TahunMasuk)
            ->where('ProgramID', $ProgramID)
            ->where('ProdiID', $ProdiID)
            ->where('JalurPendaftaran', $JalurPendaftaran)
            ->where('JenisPendaftaran', $JenisPendaftaran)
            ->where('SemesterMasuk', $SemesterMasuk)
            ->where('GelombangKe', $GelombangKe)
            ->delete();

        DB::table('biaya')
            ->where('TahunMasuk', $TahunMasuk)
            ->where('ProgramID', $ProgramID)
            ->where('ProdiID', $ProdiID)
            ->where('JalurPendaftaran', $JalurPendaftaran)
            ->where('JenisPendaftaran', $JenisPendaftaran)
            ->where('SemesterMasuk', $SemesterMasuk)
            ->where('GelombangKe', $GelombangKe)
            ->delete();

        DB::table('biaya_semester')
            ->where('TahunMasuk', $TahunMasuk)
            ->where('ProgramID', $ProgramID)
            ->where('ProdiID', $ProdiID)
            ->where('JalurPendaftaran', $JalurPendaftaran)
            ->where('JenisPendaftaran', $JenisPendaftaran)
            ->where('SemesterMasuk', $SemesterMasuk)
            ->where('GelombangKe', $GelombangKe)
            ->delete();

        return response()->json(['status' => 'success']);
    }

    public function get_semester_biaya(Request $request)
    {
        $TahunMasuk = $request->input('TahunMasuk', '');
        $ProgramID = $request->input('ProgramID', '');
        $ProdiID = $request->input('ProdiID', '');
        $JalurPendaftaran = $request->input('JalurPendaftaran', '');
        $JenisPendaftaran = $request->input('JenisPendaftaran', '');
        $SemesterMasuk = $request->input('SemesterMasuk', '');
        $GelombangKe = $request->input('GelombangKe', '');

        $semesters = DB::table('biaya')
            ->select('Semester')
            ->where('TahunMasuk', $TahunMasuk)
            ->where('ProgramID', $ProgramID)
            ->where('ProdiID', $ProdiID)
            ->where('JalurPendaftaran', $JalurPendaftaran)
            ->where('JenisPendaftaran', $JenisPendaftaran)
            ->where('SemesterMasuk', $SemesterMasuk)
            ->where('GelombangKe', $GelombangKe)
            ->groupBy('Semester')
            ->orderBy('Semester', 'ASC')
            ->get();

        $hasil = '<option value="">-- Copy Semua Semester --</option>';
        $semestersArr = [];

        foreach ($semesters as $row) {
            $hasil .= '<option value="' . $row->Semester . '">Semester ' . $row->Semester . '</option>';
            $semestersArr[] = (int)$row->Semester;
        }

        $max_semester = !empty($semestersArr) ? max($semestersArr) : null;

        return response()->json([
            'semester_option' => $hasil,
            'max_semester' => ['Semester' => $max_semester]
        ]);
    }

    public function save(Request $request, $save)
    {
        $result = $this->service->saveBiaya($save, $request->all());

        return response()->json($result);
    }

    public function copy_biaya(Request $request)
    {
        $result = $this->service->copyBiaya($request->all());

        return response()->json($result);
    }

    public function excel(Request $request)
    {
        $TahunMasuk = $request->input('TahunMasuk', '');
        $ProgramID = $request->input('ProgramID', '');
        $ProdiID = $request->input('ProdiID', '');
        $JalurPendaftaran = $request->input('JalurPendaftaran', '');
        $JenisPendaftaran = $request->input('JenisPendaftaran', '');
        $SemesterMasuk = $request->input('SemesterMasuk', '');
        $GelombangKe = $request->input('GelombangKe', '');

        // Implement Excel export if needed
        return redirect()->route('biaya.index');
    }
}
