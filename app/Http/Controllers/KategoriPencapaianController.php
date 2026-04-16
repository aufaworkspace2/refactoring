<?php

namespace App\Http\Controllers;

use App\Services\KategoriPencapaianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class KategoriPencapaianController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(KategoriPencapaianService $service)
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
            $this->Create = cek_level($levelUser, 'c_skpi/kategoriPencapaian', 'Create');
            $this->Update = cek_level($levelUser, 'c_skpi/kategoriPencapaian', 'Update');
            $this->Delete = cek_level($levelUser, 'c_skpi/kategoriPencapaian', 'Delete');

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

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Kategori Capaian');
        }

        return view('skpi.kategori_pencapaian.v_kategori_pencapaian', $data);
    }

    /**
     * Search with pagination
     */
    public function searchKategoriCapaian(Request $request, $offset = 0)
    {
        $keyword = $request->input('keyword', '');

        $limit = 10;
        $jml = $this->service->count_all($keyword);
        $data['offset'] = $offset;

        $data['query'] = $this->service->get_data($limit, $offset, $keyword);
        $data['link'] = load_pagination($jml, $limit, $offset, 'searchKategoriCapaian', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);

        $data['Create'] = $this->Create;
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('skpi.kategori_pencapaian.s_kategori_pencapaian', $data);
    }

    /**
     * Display add form
     */
    public function addKategoriPencapaian(Request $request)
    {
        $data['save'] = 1;
        $data['btn'] = 'Tambah';
        $data['row'] = null;

        return view('skpi.kategori_pencapaian.f_kategori_pencapaian', $data);
    }

    /**
     * Display view/edit form
     */
    public function viewKategoriPencapaian(Request $request, $id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['ID'] = $id;
        $data['save'] = 2;
        $data['btn'] = 'Ubah';

        return view('skpi.kategori_pencapaian.f_kategori_pencapaian', $data);
    }

    /**
     * Save data (add or update)
     */
    public function saveKategoriPencapaian(Request $request, $save)
    {
        $ID = $request->input('ID');
        $Nama = $request->input('Nama');
        $NamaInggris = $request->input('NamaInggris');
        $Urut = $request->input('Urut');

        $input['ID'] = $ID;
        $input['Nama'] = $Nama;
        $input['NamaInggris'] = $NamaInggris;
        $input['Urut'] = $Urut;

        $cek = DB::table('tbl_kategori_pencapaian')
            ->where('ID', $ID)
            ->first();

        if (!isset($cek->ID) && $save == 1) {
            if (function_exists('logs')) {
                logs("Menambah data $Nama pada tabel tbl_kategori_pencapaian");
            }

            $this->service->add($input);
            return response()->json(['status' => 'success', 'id' => DB::getPdo()->lastInsertId()]);
        }

        if ($save == 2) {
            if (function_exists('logs')) {
                logs("Mengubah data $cek->Nama menjadi $Nama pada tabel tbl_kategori_pencapaian");
            }

            $this->service->edit($ID, $input);
            return response()->json(['status' => 'success']);
        }
    }

    /**
     * Delete records
     */
    public function deleteKategoriPencapaian(Request $request)
    {
        $checkid = $request->input('checkID', []);
        $removedIds = [];

        foreach ($checkid as $id) {
            if (function_exists('log_akses')) {
                $nama = DB::table('tbl_kategori_pencapaian')->where('ID', $id)->value('Nama');
                log_akses('Hapus', "Menghapus Data Kategori Pencapaian Dengan ID {$id}");
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
}
