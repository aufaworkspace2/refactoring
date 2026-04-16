<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SettingPilihanTambahanPmbService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SettingPilihanTambahanPmbController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(SettingPilihanTambahanPmbService $service)
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

            $this->Create = cek_level($levelUser, 'c_setting_pilihan_tambahan_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_setting_pilihan_tambahan_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_setting_pilihan_tambahan_pmb', 'Delete');

            return $next($request);
        });
    }

    public function index()
    {
        $data['save'] = 1;
        $data['title'] = "Setting Pilihan Tambahan PMB";

        $pilihan_aktif = array(
            2 => 'Pilihan Kedua',
            3 => 'Pilihan Ketiga'
        );
        $data['pilihan_aktif'] = $pilihan_aktif;

        $data['metadata_muncul_pmb'] = $this->service->getSettingMunculPmb() ?? [];
        $data['metadata_tambahan_nominal'] = $this->service->getSettingTambahanNominal() ?? [];

        return view('setting_pilihan_tambahan_pmb.f_setting_pilihan_tambahan_pmb', $data);
    }

    public function set_publish_all(Request $request)
    {
        $muncul_pmb = $request->input('muncul_pmb', []);
        $tambahan_nominal = $request->input('tambahan_nominal', []);

        $UserID = Session::get('UserID');

        $muncul_pmb['LastUpdateUserID'] = $UserID;
        $tambahan_nominal['LastUpdateUserID'] = $UserID;

        $this->service->saveSettingMunculPmb($muncul_pmb);
        $this->service->saveSettingTambahanNominal($tambahan_nominal);

        echo '1';
    }
}
