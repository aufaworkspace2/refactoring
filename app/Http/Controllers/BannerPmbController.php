<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BannerPmbService;
use Illuminate\Support\Facades\Session;

class BannerPmbController extends Controller
{
    protected $service;
    public $Create; public $Update; public $Delete;

    public function __construct(BannerPmbService $service)
    {
        $this->service = $service;
        $this->middleware(function ($request, $next) {
            if (!Session::get('username')) { return redirect('/'); }
            $lang = Session::get('language');
            if (!$lang && !request()->cookie('language')) { $lang = 'indonesia'; Session::put('language', $lang); }
            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);
            $levelUser = Session::get('LevelUser');
            $this->Create = cek_level($levelUser, 'c_banner_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_banner_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_banner_pmb', 'Delete');
            return $next($request);
        });
    }

    public function index($offset = 0) { $data['Create'] = $this->Create; $data['Delete'] = $this->Delete; $data['save'] = 1; return view('banner.v_banner', $data); }

    public function search(Request $request, $offset = 0)
    {
        $keyword = $request->input('keyword', '');
        $limit = 10; $jml = $this->service->count_all($keyword);
        $data['offset'] = $offset; $data['query'] = $this->service->get_data($limit, $offset, $keyword);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update; $data['Delete'] = $this->Delete;
        return view('banner.s_banner', $data);
    }

    public function add() { $data['save'] = 1; return view('banner.f_banner', $data); }
    public function view($id) { $data['save'] = 2; $data['row'] = $this->service->get_id($id); return view('banner.f_banner', $data); }

    public function save(Request $request, $save)
    {
        $id = $request->input('id', ''); $judul = $request->input('judul', ''); $deskripsi = $request->input('deskripsi', '');
        $link = $request->input('link', ''); $status = $request->input('status', 0);
        $input['judul'] = $judul; $input['deskripsi'] = $deskripsi; $input['link'] = $link; $input['status'] = $status;
        $foto = $request->input('foto', '');
        if ($request->hasFile('gambar')) { $file = $request->file('gambar'); $fileName = $this->service->uploadImage($file, 'pmb/banner'); if ($foto) { $this->service->deleteImage($foto, 'pmb/banner'); } $input['image'] = $fileName; } else { $input['image'] = $foto; }
        if ($save == 1) { if (function_exists('logs')) { logs("Menambah data $judul pada tabel banner_pmb"); } echo $this->service->add($input); }
        if ($save == 2) { if (function_exists('logs')) { logs("Mengubah data $judul pada tabel banner_pmb"); } $this->service->edit($id, $input); }
    }

    public function delete(Request $request)
    {
        $checkid = $request->input('checkID', []);
        $removedIds = [];
        
        for ($x = 0; $x <= count($checkid) - 1; $x++) {
            $get_id = $this->service->get_id($checkid[$x]);
            if ($get_id && $get_id['image']) {
                $this->service->deleteImage($get_id['image'], 'pmb/banner');
            }
            if (function_exists('log_akses')) {
                log_akses('Hapus', 'Menghapus Data Banner PMB Dengan Nama ' . $this->service->getJudulById($checkid[$x]));
            }
            $this->service->delete($checkid[$x]);
            $removedIds[] = $checkid[$x];
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'banner_'
        ]);
    }

    public function getJudul(Request $request) { $checkid = $request->input('checkID', []); $hasil = ''; $no = 1; foreach ($checkid as $id) { $hasil .= "<b>" . $no++ . ". " . $this->service->getJudulById($id) . "</b><br>"; } echo $hasil; }
}
