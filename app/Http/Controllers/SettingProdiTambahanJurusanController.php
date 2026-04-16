<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SettingProdiTambahanJurusanService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SettingProdiTambahanJurusanController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(SettingProdiTambahanJurusanService $service)
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

            $this->Create = cek_level($levelUser, 'c_setting_prodi_tambahan_jurusan', 'Create');
            $this->Update = cek_level($levelUser, 'c_setting_prodi_tambahan_jurusan', 'Update');
            $this->Delete = cek_level($levelUser, 'c_setting_prodi_tambahan_jurusan', 'Delete');

            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        $data['Create'] = $this->Create;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Setting Pilihan Prodi Tambahan Per Jalur Pendaftaran');
        }

        return view('setting_prodi_tambahan_jurusan.v_setting_prodi_tambahan_jurusan', $data);
    }

    public function search(Request $request, $offset = 0)
    {
        $JalurID = $request->input('JalurID', '');
        $ProdiID = $request->input('ProdiID', '');
        $keyword = $request->input('keyword', '');

        $limit = 10;
        $jml = $this->service->count_all($JalurID, $ProdiID, $keyword);
        $data['offset'] = $offset;

        $data['query'] = $this->service->get_data($limit, $offset, $JalurID, $ProdiID, $keyword);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('setting_prodi_tambahan_jurusan.s_setting_prodi_tambahan_jurusan', $data);
    }

    public function add()
    {
        $data['save'] = 1;

        return view('setting_prodi_tambahan_jurusan.f_setting_prodi_tambahan_jurusan', $data);
    }

    public function view($id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['save'] = 2;

        return view('setting_prodi_tambahan_jurusan.f_setting_prodi_tambahan_jurusan', $data);
    }

    public function save(Request $request, $save)
    {
        $id = $request->input('id', '');
        $JumlahProdiTambahan = $request->input('JumlahProdiTambahan', '');
        $ProdiID = implode(",", $request->input('ProdiID', []));
        $ProdiID2 = implode(",", $request->input('ProdiID2', []));
        $ProdiID3 = implode(",", $request->input('ProdiID3', []));
        $JalurID = implode(",", $request->input('JalurID', []));

        $input['JalurID'] = $JalurID;
        $input['JumlahProdiTambahan'] = $JumlahProdiTambahan;
        $input['ProdiID'] = $ProdiID;
        $input['ListProdi2'] = $ProdiID2;
        $input['ListProdi3'] = $ProdiID3;

        if ($save == 1) {
            $input['createdAt'] = date('Y-m-d H:i:s');

            if (function_exists('logs')) {
                logs("Menambah data $JalurID $ProdiID $JumlahProdiTambahan $ProdiID $ProdiID2 $ProdiID3 pada tabel " . request()->segment(1));
            }
            $insertId = $this->service->add($input);
            echo $insertId;
        }
        if ($save == 2) {
            if (function_exists('logs')) {
                logs("Mengubah data $JalurID $ProdiID $JumlahProdiTambahan $ProdiID $ProdiID2 $ProdiID3 pada tabel " . request()->segment(1));
            }
            $this->service->edit($id, $input);
        }
    }

    public function delete(Request $request)
    {
        $checkid = $request->input('checkID', []);
        $removedIds = [];

        for ($x = 0; $x <= count($checkid) - 1; $x++) {
            if (function_exists('log_akses')) {
                log_akses('Hapus', 'Menghapus Data Setup Prodi Tambahan Jurusan');
            }
            $this->service->delete($checkid[$x]);
            $removedIds[] = $checkid[$x];
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'setting_pilihan_jurusan_'
        ]);
    }
}
