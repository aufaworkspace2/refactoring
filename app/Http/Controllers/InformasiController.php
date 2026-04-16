<?php

namespace App\Http\Controllers;

use App\Services\InformasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class InformasiController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(InformasiService $service)
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
            $this->Create = cek_level($levelUser, 'c_skpi/informasi', 'Create');
            $this->Update = cek_level($levelUser, 'c_skpi/informasi', 'Update');
            $this->Delete = cek_level($levelUser, 'c_skpi/informasi', 'Delete');

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

        // Get all program studi for dropdown
        $data['data_prodi'] = $this->service->getAllProgramStudi();

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Aktivitas Prestasi Dan Penghargaan');
        }

        return view('skpi.informasi.v_informasi', $data);
    }

    /**
     * Search with pagination
     */
    public function searchInformasi(Request $request, $offset = 0)
    {
        $ProdiID = $request->input('ProdiID', '');
        $keyword = $request->input('keyword', '');

        $limit = 10;
        $jml = $this->service->count_all($ProdiID, $keyword);
        $data['offset'] = $offset;

        $query = $this->service->get_data($limit, $offset, $ProdiID, $keyword);

        // Get prodi data for display
        $allProdi = $this->service->getAllProgramStudi();
        $prodi = [];
        foreach ($allProdi as $p) {
            $prodi[$p['ID']] = $p;
        }
        $data['prodi'] = $prodi;

        $data['query'] = $query;
        $data['link'] = load_pagination($jml, $limit, $offset, 'searchInformasi', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);

        $data['Create'] = $this->Create;
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('skpi.informasi.s_informasi', $data);
    }

    /**
     * Display add form
     */
    public function addInformasi(Request $request)
    {
        $data['save'] = 1;
        $data['btn'] = 'Tambah';
        $data['row'] = null;

        // Get dropdown data
        $data['data_prodi'] = $this->service->getAllProgramStudi();

        return view('skpi.informasi.f_informasi', $data);
    }

    /**
     * Display view/edit form
     */
    public function viewInformasi(Request $request, $id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['ID'] = $id;
        $data['save'] = 2;
        $data['btn'] = 'Ubah';

        // Get dropdown data
        $data['data_prodi'] = $this->service->getAllProgramStudi();

        return view('skpi.informasi.f_informasi', $data);
    }

    /**
     * Save data (add or update)
     */
    public function saveInformasi(Request $request, $save)
    {
        $ID = $request->input('ID');
        $Kode = $request->input('Kode');
        $ProdiID = $request->input('ProdiID', []);
        $Indonesia = $request->input('Indonesia');
        $Inggris = $request->input('Inggris');

        $input['Kode'] = $Kode;
        $input['ProdiID'] = implode(',', $ProdiID);
        $input['Indonesia'] = $Indonesia;
        $input['Inggris'] = $Inggris;

        $cek = DB::table('m_informasi')
            ->where('ID', $ID)
            ->first();

        if (!isset($cek->ID) && $save == 1) {
            if (function_exists('logs')) {
                logs("Menambah data $Kode pada tabel m_informasi");
            }

            $this->service->add($input);
            return response()->json(['status' => 'success', 'id' => DB::getPdo()->lastInsertId()]);
        }

        if ($save == 2) {
            if (function_exists('logs')) {
                logs("Mengubah data $cek->Kode menjadi $Kode pada tabel m_informasi");
            }

            $this->service->edit($ID, $input);
            return response()->json(['status' => 'success']);
        }
    }

    /**
     * Delete records
     */
    public function deleteInformasi(Request $request)
    {
        $checkid = $request->input('checkID', []);
        $removedIds = [];

        foreach ($checkid as $id) {
            if (function_exists('log_akses')) {
                $kode = DB::table('m_informasi')->where('ID', $id)->value('Kode');
                log_akses('Hapus', "Menghapus Data Informasi Dengan Kode {$id}");
            }

            $this->service->delete($id);
            $removedIds[] = $id;
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'informasi_'
        ]);
    }
}
