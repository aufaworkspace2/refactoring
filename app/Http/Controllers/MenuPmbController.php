<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MenuPmbService;
use Illuminate\Support\Facades\Session;

class MenuPmbController extends Controller
{
    protected $service;
    public $Create; public $Update; public $Delete;

    public function __construct(MenuPmbService $service)
    {
        $this->service = $service;
        $this->middleware(function ($request, $next) {
            if (!Session::get('username')) { return redirect('/'); }
            $lang = Session::get('language');
            if (!$lang && !request()->cookie('language')) { $lang = 'indonesia'; Session::put('language', $lang); }
            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);
            $levelUser = Session::get('LevelUser');
            $this->Create = cek_level($levelUser, 'c_menu_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_menu_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_menu_pmb', 'Delete');
            return $next($request);
        });
    }

    public function index($offset = 0) { $data['Create'] = $this->Create; $data['Delete'] = $this->Delete; $data['save'] = 1; return view('menu_pmb.v_kanal', $data); }

    public function search(Request $request, $offset = 0)
    {
        $keyword = $request->input('keyword', '');
        $limit = 10; $jml = $this->service->count_all($keyword);
        $data['offset'] = $offset; $data['query'] = $this->service->get_data($limit, $offset, $keyword);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update; $data['Delete'] = $this->Delete;
        return view('menu_pmb.s_kanal', $data);
    }

    public function add() { $data['save'] = 1; $data['kanal_utama'] = $this->service->getKanalUtama(); return view('menu_pmb.f_kanal', $data); }
    public function view($id) { $data['save'] = 2; $data['kanal_utama'] = $this->service->getKanalUtama(); $data['row'] = $this->service->get_id($id); return view('menu_pmb.f_kanal', $data); }

    public function save(Request $request, $save)
    {
        $id = $request->input('id', ''); $namamenu = $request->input('namamenu', '');
        $link = $request->input('link', ''); $icon = $request->input('icon', '');
        $url = $request->input('url', ''); $status = $request->input('status', 0);
        $megamenu = $request->input('megamenu', 0);
        $input['namamenu'] = $namamenu; $input['link'] = $link; $input['icon'] = $icon;
        $input['url'] = $url; $input['status'] = $status; $input['megamenu'] = $megamenu;
        if ($save == 1) { echo $this->service->add($input); }
        if ($save == 2) { $this->service->edit($id, $input); }
    }

    public function delete(Request $request)
    {
        $checkid = $request->input('checkID', []);
        $removedIds = [];
        
        for ($x = 0; $x <= count($checkid) - 1; $x++) {
            if (function_exists('log_akses')) {
                log_akses('Hapus', 'Menghapus Data Kanal PMB Dengan Nama ' . $this->service->getNamamenuById($checkid[$x]));
            }
            $this->service->delete($checkid[$x]);
            $removedIds[] = $checkid[$x];
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'kanal_'
        ]);
    }

    public function getJudul(Request $request) {
        $checkid = $request->input('checkID', []);
        $hasil = '';
        $no = 1;
        foreach ($checkid as $id) {
            $hasil .= "<b>" . $no++ . ". " . $this->service->getNamamenuById($id) . "</b><br>";
        }
        echo $hasil;
    }

    public function sort() { $data['query'] = $this->service->get_data('', '', '', 1); $data['Update'] = $this->Update; return view('menu_pmb.sort_kanal', $data); }

    public function save_sort(Request $request) { $urut_menu = $request->input('data_urut', []); $this->service->saveSort($urut_menu); echo '1'; }
}
