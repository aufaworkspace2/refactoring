<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MahasiswaDiskonTelatService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class MahasiswaDiskonTelatController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(MahasiswaDiskonTelatService $service)
    {
        $this->service = $service;

        $this->middleware(function ($request, $next) {
            $lang = Session::get('language');
            if (!$lang && !$request->cookie('language')) {
                $lang = 'indonesia';
                Session::put('language', $lang);
            }

            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);

            $levelUser = Session::get('LevelUser');

            $this->Create = cek_level($levelUser, 'c_mahasiswa_diskon_telat', 'Create');
            $this->Update = cek_level($levelUser, 'c_mahasiswa_diskon_telat', 'Update');
            $this->Delete = cek_level($levelUser, 'c_mahasiswa_diskon_telat', 'Delete');

            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        $data['Create'] = $this->Create;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Mahasiswa Diskon Telat');
        }

        return view('mahasiswa_diskon_telat.v', $data);
    }

    public function search(Request $request, $offset = 0)
    {
        $keyword = $request->input('keyword', '');
        $TahunID = $request->input('TahunID', '');
        $ProgramID = $request->input('ProgramID', '');
        $ProdiID = $request->input('ProdiID', '');
        $StatusAktif = $request->input('StatusAktif', '');

        $limit = 10;

        $jml = $this->service->count_all($keyword, $TahunID, $ProgramID, $ProdiID, $StatusAktif);

        $data['offset'] = $offset;
        $data['query'] = $this->service->get_data($limit, $offset, $keyword, $TahunID, $ProgramID, $ProdiID, $StatusAktif);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('mahasiswa_diskon_telat.s', $data);
    }

    public function changenominal(Request $request)
    {
        $PemberiDiskonID = $request->input('PemberiDiskonID', '');

        $row = DB::table('discount')->where('PemberiDiskonID', $PemberiDiskonID)->first();

        return response()->json([
            'nom' => $row->Nominal ?? 0,
            'DiscountID' => $row->DiscountID ?? ''
        ]);
    }

    public function add()
    {
        $data['save'] = 1;
        $data['master_diskon'] = DB::table('master_diskon')->get();

        return view('mahasiswa_diskon_telat.f', $data);
    }

    public function view($id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['row_data'] = DB::table('mahasiswa')->where('ID', $data['row']->MhswID)->first();
        $data['master_diskon'] = DB::table('master_diskon')->get();
        $data['save'] = 2;

        return view('mahasiswa_diskon_telat.f_edit', $data);
    }

    public function filtermhs(Request $request)
    {
        $result = $this->service->filter_students($request->all());
        
        return view('mahasiswa_diskon_telat.filtermhs', $result);
    }

    public function save(Request $request, $save)
    {
        $result = $this->service->save($save, $request->all());

        return response()->json(['status' => 'success', 'message' => 'Data saved']);
    }

    public function set_diskon($MhswDiskonTelatID)
    {
        $this->service->set_diskon($MhswDiskonTelatID);
        return response()->json(['status' => 'success']);
    }

    public function unset_diskon($MhswDiskonTelatID)
    {
        $this->service->unset_diskon($MhswDiskonTelatID);
        return response()->json(['status' => 'success']);
    }

    public function delete(Request $request)
    {
        $checkid = $request->input('checkID', []);

        $removed_ids = [];
        foreach ($checkid as $id) {
            if (!empty($id) && $id != '00') {
                $this->service->soft_delete($id);
                $this->service->unset_diskon($id);
                $removed_ids[] = $id;
            }
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removed_ids,
            'class_prefix' => 'diskon_'
        ]);
    }

    public function edit($id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['row_data'] = DB::table('mahasiswa')->where('ID', $data['row']->MhswID)->first();
        $data['master_diskon'] = DB::table('master_diskon')->get();
        $data['save'] = 2;

        return view('mahasiswa_diskon_telat.f_edit', $data);
    }

    public function update(Request $request)
    {
        $id = $request->input('ID');
        $result = $this->service->update($id, $request->all());

        return response()->json(['status' => 'success', 'message' => 'Data updated']);
    }

    public function lihat_detail($MhswDiskonTelatID)
    {
        $data['query'] = DB::table('mahasiswa_diskon_telat_detail')
            ->where('MhswDiskonTelatID', $MhswDiskonTelatID)
            ->get();

        $query_diskon = DB::table('master_diskon')->get();
        $diskon = [];
        foreach ($query_diskon as $row_diskon) {
            $diskon[$row_diskon->ID] = $row_diskon;
        }

        $data['diskon'] = $diskon;

        return view('mahasiswa_diskon_telat.lihat_detail', $data);
    }

    public function aktifkan($id)
    {
        $this->service->aktifkan($id);
        return response()->json(['status' => 'success']);
    }
}
