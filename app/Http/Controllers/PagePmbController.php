<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PagePmbService;
use Illuminate\Support\Facades\Session;

class PagePmbController extends Controller
{
    protected $service;
    public $Create; public $Update; public $Delete;

    public function __construct(PagePmbService $service)
    {
        $this->service = $service;
        $this->middleware(function ($request, $next) {
            if (!Session::get('username')) { return redirect('/'); }
            $lang = Session::get('language');
            if (!$lang && !request()->cookie('language')) { $lang = 'indonesia'; Session::put('language', $lang); }
            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);
            $levelUser = Session::get('LevelUser');
            $this->Create = cek_level($levelUser, 'c_page_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_page_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_page_pmb', 'Delete');
            return $next($request);
        });
    }

    public function index($offset = 0) { $data['Create'] = $this->Create; $data['Delete'] = $this->Delete; $data['save'] = 1; return view('page_pmb.v_page', $data); }

    public function search(Request $request, $offset = 0)
    {
        $keyword = $request->input('keyword', '');
        $limit = 10; $jml = $this->service->count_all($keyword);
        $data['offset'] = $offset; $data['query'] = $this->service->get_data($limit, $offset, $keyword);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update; $data['Delete'] = $this->Delete;
        return view('page_pmb.s_page', $data);
    }

    public function add() { $data['save'] = 1; return view('page_pmb.f_page', $data); }
    public function view($id) { $data['save'] = 2; $data['row'] = $this->service->get_id($id); return view('page_pmb.f_page', $data); }

    public function save(Request $request, $save)
    {
        $id = $request->input('id', ''); $namamenu = $request->input('namamenu', ''); $isi = $request->input('isi', '');
        $link = $request->input('link', ''); $status = $request->input('status', 0);
        $input['namamenu'] = $namamenu; $input['isi'] = $isi; $input['link'] = $link; $input['status'] = $status;
        $file_old = $request->input('file_old', '');
        if ($request->hasFile('files')) { $file = $request->file('files'); $fileName = $this->service->uploadFile($file, 'pmb/page'); if ($file_old) { $this->service->deleteFile($file_old, 'pmb/page'); } $input['files'] = $fileName; } else { $input['files'] = $file_old; }
        if ($save == 1) { echo $this->service->add($input); }
        if ($save == 2) { $this->service->edit($id, $input); }
    }

    public function delete(Request $request)
    {
        $checkid = $request->input('checkID', []);
        $removedIds = [];
        
        for ($x = 0; $x <= count($checkid) - 1; $x++) {
            $get_id = $this->service->get_id($checkid[$x]);
            if ($get_id && $get_id['files']) {
                $this->service->deleteFile($get_id['files'], 'pmb/page');
            }
            if (function_exists('log_akses')) {
                log_akses('Hapus', 'Menghapus Data Page PMB Dengan Nama ' . $this->service->getNamamenuById($checkid[$x]));
            }
            $this->service->delete($checkid[$x]);
            $removedIds[] = $checkid[$x];
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'page_'
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

    public function upload_file(Request $request)
    {
        if ($request->hasFile('file')) { $file = $request->file('file'); $fileName = $this->service->uploadFile($file, 'pmb/page/detail'); return response()->json(['location' => asset('pmb/page/detail/' . $fileName)]); }
        return response()->json(['error' => 'No file uploaded'], 500);
    }
}
