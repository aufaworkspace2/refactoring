<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AgendaPmbService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AgendaPmbController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(AgendaPmbService $service)
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

            $this->Create = cek_level($levelUser, 'c_agenda_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_agenda_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_agenda_pmb', 'Delete');

            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        $data['Create'] = $this->Create;
        $data['Delete'] = $this->Delete;
        $data['save'] = 1;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Agenda PMB');
        }

        return view('agenda.v_agenda', $data);
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

        return view('agenda.s_agenda', $data);
    }

    public function add()
    {
        $data['save'] = 1;

        return view('agenda.f_agenda', $data);
    }

    public function view($id)
    {
        $data['save'] = 2;
        $data['row'] = $this->service->get_id($id);

        return view('agenda.f_agenda', $data);
    }

    public function save(Request $request, $save)
    {
        $id = $request->input('id', '');
        $judul = $request->input('judul', '');
        $isi = $request->input('isi', '');
        $tanggal = $request->input('tanggal', '');
        $waktu = $request->input('waktu', '');
        $tempat = $request->input('tempat', '');
        $publish = $request->input('publish', 0);
        $metatitle = $request->input('metatitle', '');
        $metakeywords = $request->input('metakeywords', '');
        $metadescription = $request->input('metadescription', '');

        // Convert tanggal from DD/MM/YYYY to YYYY-MM-DD
        if ($tanggal) {
            $tgl = explode('/', $tanggal);
            $tanggal = date('Y-m-d', mktime(0, 0, 0, $tgl[1], $tgl[0], $tgl[2]));
        }

        $input['judul'] = $judul;
        $input['alias'] = $this->service->generateAlias($judul);
        $input['isi'] = $isi;
        $input['tanggal'] = $tanggal;
        $input['waktu'] = $waktu;
        $input['tempat'] = $tempat;
        $input['publish'] = $publish;
        $input['metatitle'] = $metatitle;
        $input['metakeywords'] = $metakeywords;
        $input['metadescription'] = $metadescription;

        // Handle image upload
        $foto = $request->input('foto', '');
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $fileName = $this->service->uploadImage($file, 'pmb/agenda');
            
            // Delete old image
            if ($foto) {
                $this->service->deleteImage($foto, 'pmb/agenda');
            }
            $input['gambar'] = $fileName;
        } else {
            $input['gambar'] = $foto;
        }

        if ($save == 1) {
            if (function_exists('logs')) {
                logs("Menambah data $judul pada tabel agenda_pmb");
            }
            $insertId = $this->service->add($input);
            echo $insertId;
        }
        if ($save == 2) {
            if (function_exists('logs')) {
                logs("Mengubah data $judul pada tabel agenda_pmb");
            }
            $this->service->edit($id, $input);
        }
    }

    public function delete(Request $request)
    {
        $checkid = $request->input('checkID', []);
        $removedIds = [];

        for ($x = 0; $x <= count($checkid) - 1; $x++) {
            $get_id = $this->service->get_id($checkid[$x]);
            if ($get_id && $get_id['gambar']) {
                $this->service->deleteImage($get_id['gambar'], 'pmb/agenda');
            }

            if (function_exists('log_akses')) {
                log_akses('Hapus', 'Menghapus Data Agenda PMB Dengan Nama ' . $this->service->getJudulById($checkid[$x]));
            }
            $this->service->delete($checkid[$x]);
            $removedIds[] = $checkid[$x];
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'agenda_'
        ]);
    }

    public function getJudul(Request $request)
    {
        $checkid = $request->input('checkID', []);
        $hasil = '';
        $no = 1;

        foreach ($checkid as $id) {
            $hasil .= "<b>" . $no++ . ". " . $this->service->getJudulById($id) . "</b><br>";
        }
        echo $hasil;
    }

    public function upload_file(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $this->service->uploadImage($file, 'pmb/agenda');

            return response()->json([
                'location' => asset('pmb/agenda/' . $fileName)
            ]);
        }

        return response()->json(['error' => 'No file uploaded'], 500);
    }
}
