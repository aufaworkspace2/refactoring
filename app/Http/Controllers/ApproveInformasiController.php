<?php

namespace App\Http\Controllers;

use App\Services\ApproveInformasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ApproveInformasiController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(ApproveInformasiService $service)
    {
        $this->service = $service;

        $this->middleware(function ($request, $next) {
            if (!Session::get('username')) {
                return redirect('/');
            }

            $lang = Session::get('language');
            if (!$lang && !request()->cookie('language')) {
                $lang = 'indonesia';
                Session::put('language', $lang);
            }

            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);

            $levelUser = Session::get('LevelUser');
            $this->Create = cek_level($levelUser, 'c_skpi/approveInformasi', 'Create');
            $this->Update = cek_level($levelUser, 'c_skpi/approveInformasi', 'Update');
            $this->Delete = cek_level($levelUser, 'c_skpi/approveInformasi', 'Delete');

            return $next($request);
        });
    }

    /**
     * Display main view
     */
    public function index(Request $request, $offset = 0)
    {
        $data['Create'] = $this->Create;
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        // Get dropdown data
        $data['data_program'] = $this->service->getAllProgram();
        $data['data_prodi'] = $this->service->getAllProgramStudi();
        $data['data_status'] = $this->service->getAllStatusMahasiswa();
        $data['data_tahun'] = $this->service->getAllTahunMasuk();
        $data['data_kelas'] = $this->service->getAllKelas();

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Approve Aktivitas Prestasi Dan Penghargaan');
        }

        return view('skpi.approve_informasi.v_approve_informasi', $data);
    }

    /**
     * Search with pagination
     */
    public function searchApproveInformasi(Request $request, $offset = 0)
    {
        $filters = [
            'ProgramID' => $request->input('ProgramID', ''),
            'ProdiID' => $request->input('ProdiID', ''),
            'StatusMhswID' => $request->input('StatusMhswID', ''),
            'TahunMasuk' => $request->input('TahunMasuk', ''),
            'KelasID' => $request->input('KelasID', ''),
            'keyword' => $request->input('keyword', ''),
            'orderby' => $request->input('orderby', 'mahasiswa.Nama'),
            'descasc' => $request->input('descasc', 'DESC'),
        ];

        $limit = 10;
        $jml = $this->service->count_all_approve_informasi($filters);
        $data['offset'] = $offset;

        $data['query'] = $this->service->get_data_approve_informasi($limit, $offset, $filters);
        $data['link'] = load_pagination($jml, $limit, $offset, 'searchApproveInformasi', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);

        $data['Create'] = $this->Create;
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('skpi.approve_informasi.s_approve_informasi', $data);
    }

    /**
     * Get informasi by mahasiswa ID (for modal)
     */
    public function lihatInformasi(Request $request)
    {
        $mhswID = $request->input('mhswID', '');

        $mahasiswa = DB::table('mahasiswa')
            ->where('ID', $mhswID)
            ->first();

        $informasiList = $this->service->getInformasiByMhswID($mhswID);

        $html = view('skpi.approve_informasi.modal_informasi', [
            'mahasiswa' => $mahasiswa,
            'informasiList' => $informasiList
        ])->render();

        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * Approve informasi
     */
    public function approveInformasi(Request $request)
    {
        $id = $request->input('id', '');

        $this->service->approveInformasi($id);

        return response()->json(['status' => 'success']);
    }

    /**
     * Reject informasi
     */
    public function rejectInformasi(Request $request)
    {
        $id = $request->input('id', '');

        $this->service->rejectInformasi($id);

        return response()->json(['status' => 'success']);
    }
}
