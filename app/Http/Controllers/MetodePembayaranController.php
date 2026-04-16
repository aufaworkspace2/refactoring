<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MetodePembayaranService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class MetodePembayaranController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(MetodePembayaranService $service)
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

            $this->Create = cek_level($levelUser, 'c_metode_pembayaran', 'Create');
            $this->Update = cek_level($levelUser, 'c_metode_pembayaran', 'Update');
            $this->Delete = cek_level($levelUser, 'c_metode_pembayaran', 'Delete');

            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        $data['Create'] = $this->Create;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data metode_pembayaran');
        }

        return view('metode_pembayaran.v_metode_pembayaran', $data);
    }

    public function search(Request $request, $offset = 0)
    {
        $keyword = $request->input('keyword', '');
        $limit = 10;

        $jml = $this->service->count_all($keyword);
        $data['offset'] = $offset;

        $data['query'] = $this->service->get_data($limit, $offset, $keyword);

        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);

        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('metode_pembayaran.s_metode_pembayaran', $data);
    }

    public function add()
    {
        $data['save'] = 1;

        return view('metode_pembayaran.f_metode_pembayaran', $data);
    }

    public function view($id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['save'] = 2;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Data metode_pembayaran Dengan ID ' . $id);
        }

        return view('metode_pembayaran.f_metode_pembayaran', $data);
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
                    log_akses('Hapus', 'Menghapus Data metode_pembayaran Dengan ID ' . $id);
                }
                $this->service->delete($id);
                $removedIds[] = $id;
            }
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'metode_pembayaran_'
        ]);
    }
}
