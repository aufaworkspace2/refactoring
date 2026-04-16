<?php

namespace App\Http\Controllers;

use App\Services\PencapaianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PencapaianController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(PencapaianService $service)
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
            $this->Create = cek_level($levelUser, 'c_skpi/pencapaian', 'Create');
            $this->Update = cek_level($levelUser, 'c_skpi/pencapaian', 'Update');
            $this->Delete = cek_level($levelUser, 'c_skpi/pencapaian', 'Delete');

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
            log_akses('View', 'Melihat Daftar Data Capaian Pembelajaran');
        }

        return view('skpi.pencapaian.v_pencapaian', $data);
    }

    /**
     * Search with pagination
     */
    public function searchCapaian(Request $request, $offset = 0)
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

        // Get kategori data for display
        $allKategori = $this->service->getAllKategoriPencapaian();
        $kategori = [];
        foreach ($allKategori as $k) {
            $kategori[$k['ID']] = $k;
        }
        $data['kategori'] = $kategori;

        $data['query'] = $query;
        $data['link'] = load_pagination($jml, $limit, $offset, 'searchCapaian', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);

        $data['Create'] = $this->Create;
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('skpi.pencapaian.s_pencapaian', $data);
    }

    /**
     * Display add form
     */
    public function addPencapaian(Request $request)
    {
        $data['save'] = 1;
        $data['btn'] = 'Tambah';
        $data['row'] = null;

        // Get dropdown data
        $data['data_prodi'] = $this->service->getAllProgramStudi();
        $data['data_kategori'] = $this->service->getAllKategoriPencapaian();

        return view('skpi.pencapaian.f_pencapaian', $data);
    }

    /**
     * Display view/edit form
     */
    public function viewPencapaian(Request $request, $id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['ID'] = $id;
        $data['save'] = 2;
        $data['btn'] = 'Ubah';

        // Get dropdown data
        $data['data_prodi'] = $this->service->getAllProgramStudi();
        $data['data_kategori'] = $this->service->getAllKategoriPencapaian();

        return view('skpi.pencapaian.f_pencapaian', $data);
    }

    /**
     * Save data (add or update)
     */
    public function savePencapaian(Request $request, $save)
    {
        $ID = $request->input('ID');
        $ProdiID = $request->input('ProdiID', []);
        $Kode = $request->input('Kode');
        $Indonesia = $request->input('Indonesia');
        $Inggris = $request->input('Inggris');
        $KategoriPencapaianID = $request->input('KategoriPencapaianID');

        $input['ProdiID'] = implode(',', $ProdiID);
        $input['Kode'] = $Kode;
        $input['Indonesia'] = $Indonesia;
        $input['Inggris'] = $Inggris;
        $input['KategoriPencapaianID'] = $KategoriPencapaianID;

        $cek = DB::table('m_pencapaian')
            ->where('ID', $ID)
            ->first();

        if (!isset($cek->ID) && $save == 1) {
            if (function_exists('logs')) {
                logs("Menambah data $Kode pada tabel m_pencapaian");
            }

            $this->service->add($input);
            return response()->json(['status' => 'success', 'id' => DB::getPdo()->lastInsertId()]);
        }

        if ($save == 2) {
            if (function_exists('logs')) {
                logs("Mengubah data $cek->Kode menjadi $Kode pada tabel m_pencapaian");
            }

            $this->service->edit($ID, $input);
            return response()->json(['status' => 'success']);
        }
    }

    /**
     * Delete records
     */
    public function deletePencapaian(Request $request)
    {
        $checkid = $request->input('checkID', []);
        $removedIds = [];

        foreach ($checkid as $id) {
            // Delete from t_pencapaian first
            DB::table('t_pencapaian')
                ->where('CapaiID', $id)
                ->delete();

            if (function_exists('log_akses')) {
                $kode = DB::table('m_pencapaian')->where('ID', $id)->value('Kode');
                log_akses('Hapus', "Menghapus Data Pencapaian Dengan Kode {$id}");
            }

            $this->service->delete($id);
            $removedIds[] = $id;
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'capaian_'
        ]);
    }

    /**
     * Get mahasiswa by capaian ID (for modal)
     */
    public function searchMahasiswa(Request $request)
    {
        $CapaianID = $request->input('CapaianID', '');

        $mahasiswa = $this->service->getMahasiswaByCapaiID($CapaianID);

        $html = '<table class="table table-bordered">';
        $html .= '<thead><tr><th>No.</th><th>NPM</th><th>Nama</th></tr></thead><tbody>';
        $no = 1;
        foreach ($mahasiswa as $m) {
            $html .= '<tr>';
            $html .= '<td>' . $no++ . '</td>';
            $html .= '<td>' . ($m['NPM'] ?? '') . '</td>';
            $html .= '<td>' . ($m['namaMahasiswa'] ?? '') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        return response($html)->header('Content-Type', 'text/html');
    }
}
