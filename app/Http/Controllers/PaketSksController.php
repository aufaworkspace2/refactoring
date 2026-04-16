<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaketSksService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PaketSksController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(PaketSksService $service)
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

            $this->Create = cek_level($levelUser, 'c_paketsks', 'Create');
            $this->Update = cek_level($levelUser, 'c_paketsks', 'Update');
            $this->Delete = cek_level($levelUser, 'c_paketsks', 'Delete');

            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        $data['Create'] = $this->Create;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Paket SKS');
        }

        return view('paketsks.v_paketsks', $data);
    }

    public function search(Request $request, $offset = 0)
    {
        $user = Session::get('UserID');
        $LevelKode = Session::get('LevelKode');
        $ProdiID = get_field($user, 'user', 'ProdiID');

        if (empty($ProdiID)) {
            $ProdiID = 0;
        }

        if (in_array('SPR', explode(',', $LevelKode ?? '')) || 1 == 1) {
            $data['prodi'] = DB::table('programstudi')
                ->select('ID', 'Nama', 'JenjangID')
                ->get();
        } else {
            $data['prodi'] = DB::table('programstudi')
                ->select('ID', 'Nama', 'JenjangID')
                ->whereIn('ID', explode(',', $ProdiID))
                ->get();
        }

        $data['Update'] = $this->Update;
        $data['offset'] = $offset;

        return view('paketsks.s_paketsks', $data);
    }

    public function add()
    {
        $data['save'] = 1;

        return view('paketsks.f_paketsks', $data);
    }

    public function view($ProdiID)
    {
        $data['query'] = DB::table('paket_sks')
            ->where('ProdiID', $ProdiID)
            ->first();

        $data['row'] = DB::table('programstudi')->where('ID', $ProdiID)->first();
        $data['save'] = 2;
        $data['ProdiID'] = $ProdiID;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Data Paket SKS Prodi ' . $ProdiID);
        }

        return view('paketsks.f_paketsks', $data);
    }

    public function save(Request $request, $save)
    {
        $ID = $request->input('ID', '');
        $SemesterPaket = $request->input('SemesterPaket', []);

        // Delete existing data
        DB::table('paket_sks')->where('ProdiID', $ID)->delete();

        // Insert new data
        if (!empty($SemesterPaket)) {
            DB::table('paket_sks')->insert([
                'ProdiID' => $ID,
                'SemesterPaket' => implode(',', $SemesterPaket),
            ]);
        }

        return response('success', 200);
    }
}
