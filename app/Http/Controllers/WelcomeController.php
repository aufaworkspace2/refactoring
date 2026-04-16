<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WelcomeController extends Controller
{
    protected $service;

    public function __construct(\App\Services\WelcomeService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        // HARDCODE UNTUK TESTING DARI USER
        session(['UserID' => 1]);
        // session(['modulgrup' => 0]); // Opsional, uncomment jika ingin hardcode modulgrup juga

        if (session()->has('UserID')) {
            return redirect('dashboard');
        }
        return view('welcome');
    }

    public function cek_logout(Request $request)
    {
        $post = $request->all();
        $level = session('LevelUser');

        $status = $this->service->cek_logout($post, $level);

        return response()->json($status);
    }

    public function get_modul_grup($url)
    {
        $status = $this->service->get_modul_grup($url);

        return $status;
    }

    public function menu($id)
    {
        // $id is the MdlGrpID
        $userId = session('UserID');

        // Cek login (Jika belum, kembalikan response kosong/error handling)
        if(!$userId) {
            return '';
        }

        // Simpan Modul Grup ID ke session, agar dashboard membedakan state
        session(['modulgrup' => $id]);

        // Ambil data menu dari Service
        $menus = $this->service->getSidebarMenu($userId, $id);

        // Render blade view dengan data $menus
        return view('layouts.menu1', compact('menus', 'id'));
    }

    public function test(Request $request)
    {
        $table = $request->query('table');
        $field = $request->query('field');
        $checkid = $request->input('checkID', []);

        $names = [];
        foreach ($checkid as $id) {
            $name = DB::table($table)->where('ID', $id)->value($field);
            if ($name) {
                $names[] = $name;
            }
        }

        return response(implode(', ', $names));
    }
}
