<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MasterDiskonService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class MasterDiskonController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(MasterDiskonService $service)
    {
        $this->service = $service;

        $this->middleware(function ($request, $next) {
            // Language setup
            $lang = Session::get('language');
            if (!$lang && !$request->cookie('language')) {
                $lang = 'indonesia';
                Session::put('language', $lang);
            }

            // Map legacy language names to Laravel locales
            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);

            $levelUser = Session::get('LevelUser');

            $this->Create = cek_level($levelUser, 'c_master_diskon', 'Create');
            $this->Update = cek_level($levelUser, 'c_master_diskon', 'Update');
            $this->Delete = cek_level($levelUser, 'c_master_diskon', 'Delete');

            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        $data['Create'] = $this->Create;

        return view('master_diskon.v_master_diskon', $data);
    }

    public function search(Request $request, $offset = 0)
    {
        $keyword = $request->input('keyword', '');
        $Tipe = $request->input('Tipe', '');
        $BiayaAwalID = $request->input('BiayaAwalID', '');
        $ProdiID = $request->input('ProdiID', '');
        $limit = 10;

        $jml = $this->service->count_all($keyword, $Tipe, $BiayaAwalID, $ProdiID);

        $data['offset'] = $offset;
        $data['query'] = $this->service->get_data($limit, $offset, $keyword, $Tipe, $BiayaAwalID, $ProdiID);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('master_diskon.s_master_diskon', $data);
    }

    public function add()
    {
        $data['save'] = 1;
        $data['param'] = 0;
        $data['persen'] = 0;

        return view('master_diskon.f_master_diskon', $data);
    }

    public function view($id)
    {
        $data['row'] = $row = $this->service->get_id($id);

        $data['param'] = $row->isPemberiDiskonID ?? 0;
        $data['persen'] = $row->isPersen ?? 0;
        $data['save'] = 2;

        return view('master_diskon.f_master_diskon', $data);
    }

    public function save(Request $request, $save)
    {
        $result = $this->service->save($save, $request->all());

        if ($result === 'gagal') {
            return response('gagal', 200);
        }

        return response($result, 200);
    }

    public function delete(Request $request)
    {
        $checkid = $request->input('checkID');
        $removedIds = [];

        if ($checkid) {
            foreach ($checkid as $id) {
                if (function_exists('log_akses')) {
                    log_akses('Hapus', 'Menghapus Data Master Diskon Dengan Nama ' . get_field($id, 'master_diskon', 'Nama'));
                }
                $this->service->delete($id);
                $removedIds[] = $id;
            }
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'master_diskon_'
        ]);
    }
}
