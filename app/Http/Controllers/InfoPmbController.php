<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\InfoPmbService;
use Illuminate\Support\Facades\Session;

class InfoPmbController extends Controller
{
    protected $service;
    public $Create; public $Update; public $Delete;

    public function __construct(InfoPmbService $service)
    {
        $this->service = $service;
        $this->middleware(function ($request, $next) {
            if (!Session::get('username')) { return redirect('/'); }
            $lang = Session::get('language');
            if (!$lang && !request()->cookie('language')) { $lang = 'indonesia'; Session::put('language', $lang); }
            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);
            $levelUser = Session::get('LevelUser');
            $this->Create = cek_level($levelUser, 'c_info_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_info_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_info_pmb', 'Delete');
            return $next($request);
        });
    }

    public function index()
    {
        $data['Create'] = $this->Create; $data['Delete'] = $this->Delete;
        $data['row'] = $this->service->get_id(1);
        return view('info_pmb.f_info', $data);
    }

    public function save(Request $request)
    {
        $email = $request->input('email', ''); $telepon = $request->input('telepon', '');
        $fax = $request->input('fax', ''); $youtube = $request->input('youtube', '');
        $twitter = $request->input('twitter', ''); $facebook = $request->input('facebook', '');
        $instagram = $request->input('instagram', ''); $password = $request->input('password', '');
        $whatsapp = $request->input('whatsapp', '');
        if (substr($whatsapp, 0, 1) == '0') { $whatsapp = '62' . substr($whatsapp, 1); }
        $input['email'] = $email; $input['telepon'] = $telepon; $input['fax'] = $fax;
        $input['youtube'] = $youtube; $input['twitter'] = $twitter; $input['facebook'] = $facebook;
        $input['instagram'] = $instagram; $input['password'] = $password; $input['whatsapp'] = $whatsapp;
        $foto = $request->input('foto', '');
        if ($request->hasFile('logo')) { $file = $request->file('logo'); $fileName = $this->service->uploadImage($file, 'pmb/logo'); if ($foto) { $this->service->deleteImage($foto, 'pmb/logo'); } $input['logo'] = $fileName; } else { $input['logo'] = $foto; }
        $row = $this->service->get_id(1);
        if ($row && $row['id']) { $this->service->edit(1, $input); } else { $input['id'] = 1; $this->service->add($input); }
        echo '1';
    }
}
