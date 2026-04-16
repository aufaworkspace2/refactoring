<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ArtikelPmbService;
use Illuminate\Support\Facades\Session;

class ArtikelPmbController extends Controller
{
    protected $service;
    public $Create; public $Update; public $Delete;

    public function __construct(ArtikelPmbService $service)
    {
        $this->service = $service;
        $this->middleware(function ($request, $next) {
            if (!Session::get('username')) { return redirect('/'); }
            $lang = Session::get('language');
            if (!$lang && !request()->cookie('language')) { $lang = 'indonesia'; Session::put('language', $lang); }
            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);
            $levelUser = Session::get('LevelUser');
            $this->Create = cek_level($levelUser, 'c_artikel_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_artikel_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_artikel_pmb', 'Delete');
            return $next($request);
        });
    }

    public function index($offset = 0) { $data['Create'] = $this->Create; $data['Delete'] = $this->Delete; $data['save'] = 1; return view('artikel.v_artikel', $data); }

    public function search(Request $request, $offset = 0)
    {
        $keyword = $request->input('keyword', '');
        $limit = 10; $jml = $this->service->count_all($keyword);
        $data['offset'] = $offset; $data['query'] = $this->service->get_data($limit, $offset, $keyword);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update; $data['Delete'] = $this->Delete;
        return view('artikel.s_artikel', $data);
    }

    public function add() { $data['save'] = 1; return view('artikel.f_artikel', $data); }
    public function view($id) { $data['save'] = 2; $data['row'] = $this->service->get_id($id); return view('artikel.f_artikel', $data); }

    public function save(Request $request, $save)
    {
        $id = $request->input('id', ''); $judul = $request->input('judul', ''); $isi = $request->input('isi', '');
        $event_date = $request->input('event_date', ''); $status = $request->input('status', 0); $publish = $request->input('publish', 0);
        $metatitle = $request->input('metatitle', ''); $metakeywords = $request->input('metakeywords', ''); $metadescription = $request->input('metadescription', '');
        if ($event_date) { $tgl = explode('/', $event_date); $event_date = date('Y-m-d', mktime(0, 0, 0, $tgl[1], $tgl[0], $tgl[2])); }
        $input['judul'] = $judul; $input['alias'] = $this->service->generateAlias($judul); $input['isi'] = $isi;
        $input['event_date'] = $event_date; $input['status'] = $status; $input['publish'] = $publish;
        $input['metatitle'] = $metatitle; $input['metakeywords'] = $metakeywords; $input['metadescription'] = $metadescription;
        $foto = $request->input('foto', '');
        if ($request->hasFile('gambar')) { $file = $request->file('gambar'); $fileName = $this->service->uploadImage($file, 'pmb/artikel'); if ($foto) { $this->service->deleteImage($foto, 'pmb/artikel'); } $input['gambar'] = $fileName; } else { $input['gambar'] = $foto; }
        if ($save == 1) { if (function_exists('logs')) { logs("Menambah data $judul pada tabel artikel_pmb"); } echo $this->service->add($input); }
        if ($save == 2) { if (function_exists('logs')) { logs("Mengubah data $judul pada tabel artikel_pmb"); } $this->service->edit($id, $input); }
    }

    public function delete(Request $request)
    {
        $checkid = $request->input('checkID', []);
        $removedIds = [];
        
        for ($x = 0; $x <= count($checkid) - 1; $x++) {
            $get_id = $this->service->get_id($checkid[$x]);
            if ($get_id && $get_id['gambar']) {
                $this->service->deleteImage($get_id['gambar'], 'pmb/artikel');
            }
            if (function_exists('log_akses')) {
                log_akses('Hapus', 'Menghapus Data Artikel PMB Dengan Nama ' . $this->service->getJudulById($checkid[$x]));
            }
            $this->service->delete($checkid[$x]);
            $removedIds[] = $checkid[$x];
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'artikel_'
        ]);
    }

    public function getJudul(Request $request) { $checkid = $request->input('checkID', []); $hasil = ''; $no = 1; foreach ($checkid as $id) { $hasil .= "<b>" . $no++ . ". " . $this->service->getJudulById($id) . "</b><br>"; } echo $hasil; }

    public function upload_file(Request $request)
    {
        if ($request->hasFile('file')) { $file = $request->file('file'); $fileName = $this->service->uploadImage($file, 'pmb/artikel'); return response()->json(['location' => asset('pmb/artikel/' . $fileName)]); }
        return response()->json(['error' => 'No file uploaded'], 500);
    }
}
