<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChannelPembayaranService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ChannelPembayaranController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(ChannelPembayaranService $service)
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

            $this->Create = cek_level($levelUser, 'c_channel_pembayaran', 'Create');
            $this->Update = cek_level($levelUser, 'c_channel_pembayaran', 'Update');
            $this->Delete = cek_level($levelUser, 'c_channel_pembayaran', 'Delete');

            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        $data['Create'] = $this->Create;
        $data['MetodePembayaranList'] = $this->service->get_metode_pembayaran_list();

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data channel_pembayaran');
        }

        return view('channel_pembayaran.v_channel_pembayaran', $data);
    }

    public function search(Request $request, $offset = 0)
    {
        $keyword = $request->input('keyword', '');
        $MetodePembayaranID = $request->input('MetodePembayaranID', '');
        $limit = 10;

        $jml = $this->service->count_all($keyword, $MetodePembayaranID);
        $data['offset'] = $offset;

        $query = $this->service->get_data($limit, $offset, $keyword, $MetodePembayaranID);

        // Get list panduan
        $id = [];
        foreach ($query as $row) {
            $id[] = $row->ID;
        }

        $list_panduan = [];
        if (count($id) > 0) {
            $list_panduan = $this->service->get_list_panduan($id);
        }

        $data['query'] = $query;
        $data['list_panduan'] = $list_panduan;

        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);

        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;
        $data['jenisbiaya'] = $this->service->get_jenis_biaya();

        return view('channel_pembayaran.s_channel_pembayaran', $data);
    }

    public function add()
    {
        $data['save'] = 1;
        $data['MetodePembayaranList'] = $this->service->get_metode_pembayaran_list();
        $data['JenisBiayaList'] = DB::table('jenisbiaya')->get();

        return view('channel_pembayaran.f_channel_pembayaran', $data);
    }

    public function view($id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['save'] = 2;
        $data['MetodePembayaranList'] = $this->service->get_metode_pembayaran_list();
        $data['JenisBiayaList'] = DB::table('jenisbiaya')->get();
        $data['PanduanPembayaranList'] = $this->service->get_panduan_by_channel($id);

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Data channel_pembayaran Dengan ID ' . $id);
        }

        return view('channel_pembayaran.f_channel_pembayaran', $data);
    }

    public function save(Request $request, $save)
    {
        $result = $this->service->save($save, $request->all(), $request->allFiles());

        if ($result === 'gagal') {
            return response('gagal', 200);
        }

        $ID = $result;

        // Save panduan pembayaran
        $namaPanduan = $request->input('NamaPanduan', []);
        $textCaraBayar = $request->input('TextCaraBayar', []);

        if (count($namaPanduan) > 0) {
            $this->service->save_panduan_pembayaran($ID, $namaPanduan, $textCaraBayar);
        }

        return response($ID, 200);
    }

    public function delete(Request $request)
    {
        $checkid = $request->input('checkID');
        $removedIds = [];

        if ($checkid) {
            foreach ($checkid as $id) {
                if (function_exists('log_akses')) {
                    log_akses('Hapus', 'Menghapus Data channel_pembayaran Dengan ID ' . $id);
                }
                $this->service->delete($id);
                $removedIds[] = $id;
            }
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'channel_pembayaran_'
        ]);
    }

    public function set_aktif(Request $request)
    {
        $val = $request->input('val');
        $buka = $request->input('buka');
        $tutup = $request->input('tutup');

        $status = $buka == 1 ? 1 : 0;

        $this->service->set_aktif($val, $status);

        return response()->json(['status' => 'success']);
    }
}
