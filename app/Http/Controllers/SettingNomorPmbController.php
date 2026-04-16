<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SettingNomorPmbService;
use Illuminate\Support\Facades\Session;

class SettingNomorPmbController extends Controller
{
    protected $service;

    public function __construct(SettingNomorPmbService $service)
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

            return $next($request);
        });
    }

    public function index()
    {
        $data = $this->service->get_pmb();

        if ($data && $data['row']) {
            $data['save'] = 2;
        } else {
            $data['save'] = 1;
            $data['row'] = null;
        }

        return view('setting_nomor_pmb.f_setting_nomor_pmb', $data);
    }

    public function save(Request $request, $save)
    {
        $ID = $request->input('ID', '');
        $format = $request->input('format', []);

        $result = $this->service->save_pmb($save, $ID, $format);

        echo $result ? '1' : '0';
    }

    public function setting_nomor_invoice()
    {
        $data = $this->service->get_invoice();

        if ($data && $data['row']) {
            $data['save'] = 2;
        } else {
            $data['save'] = 1;
            $data['row'] = null;
        }

        return view('setting_nomor_pmb.f_setting_nomor_invoice', $data);
    }

    public function save_invoice(Request $request, $save)
    {
        $ID = $request->input('ID', '');
        $format = $request->input('format', []);

        $result = $this->service->save_invoice($save, $ID, $format);

        echo $result ? '1' : '0';
    }

    public function setting_nomor_nim()
    {
        $data = $this->service->get_nim();

        if ($data && $data['row']) {
            $data['save'] = 2;
        } else {
            $data['save'] = 1;
            $data['row'] = null;
        }

        return view('setting_nomor_pmb.f_setting_nomor_nim', $data);
    }

    public function save_nim(Request $request, $save)
    {
        $ID = $request->input('ID', '');
        $format = $request->input('format', []);

        $result = $this->service->save_nim($save, $ID, $format);

        echo $result ? '1' : '0';
    }

    public function getMaster()
    {
        $data_master = $this->service->getMaster();

        foreach ($data_master as $row_master) {
            echo '<option value="' . $row_master->kode . '">' . $row_master->kode . ' (' . $row_master->digit . ' Digit)</option>';
        }
    }
}
