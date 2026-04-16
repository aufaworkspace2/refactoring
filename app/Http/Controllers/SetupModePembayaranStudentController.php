<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SetupModePembayaranStudentController extends Controller
{
    public $Create;
    public $Update;
    public $Delete;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $lang = Session::get('language');
            if (!$lang && !$request->cookie('language')) {
                $lang = 'indonesia';
                Session::put('language', $lang);
            }

            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);

            $levelUser = Session::get('LevelUser');

            $this->Create = cek_level($levelUser, 'c_setup_mode_pembayaran_student', 'Create');
            $this->Update = cek_level($levelUser, 'c_setup_mode_pembayaran_student', 'Update');
            $this->Delete = cek_level($levelUser, 'c_setup_mode_pembayaran_student', 'Delete');

            return $next($request);
        });
    }

    public function index()
    {
        $data['all_data'] = DB::table('setup_mode_pembayaran_student')->get();
        $data['all_prodi'] = DB::table('programstudi')->get();
        $data['save'] = 1;
        $data['title'] = "Setup Mode Pengajuan Pembayaran Mahasiswa";

        $arr_nama_jenjang = [];
        foreach (get_all('jenjang') as $jenjang) {
            $arr_nama_jenjang[$jenjang->ID] = $jenjang->Nama;
        }
        $data['arr_nama_jenjang'] = $arr_nama_jenjang;

        return view('setup_mode_pembayaran_student.f_setup_mode_pembayaran_student', $data);
    }

    public function set_publish_all(Request $request)
    {
        $ID_list = $request->input('ID_list', []);
        $ProdiID_list = $request->input('ProdiID_list', []);
        $CicilTermin = $request->input('CicilTermin', []);
        $CicilBebas = $request->input('CicilBebas', []);
        $UserID = Session::get('UserID');

        $arr_ID = [];

        foreach ($ID_list as $key => $ID) {
            $cek = null;

            if ($ID) {
                $cek = DB::table('setup_mode_pembayaran_student')->where('ID', $ID)->first();
            }

            $input = [
                'ProdiID_list' => isset($ProdiID_list[$key]) ? implode(",", $ProdiID_list[$key]) : '',
                'CicilTermin' => $CicilTermin[$key] ?? 0,
                'CicilBebas' => $CicilBebas[$key] ?? 0,
                'LastUpdateUserID' => $UserID,
            ];

            if (empty($cek)) {
                $input['UserID'] = $UserID;
                $input['createdAt'] = date('Y-m-d H:i:s');

                $last_id = DB::table('setup_mode_pembayaran_student')->insertGetId($input);
                $arr_ID[] = $last_id;
            } else {
                DB::table('setup_mode_pembayaran_student')
                    ->where('ID', $ID)
                    ->update($input);
                $arr_ID[] = $ID;
            }
        }

        // Delete records not in the submitted list
        if (count($arr_ID) > 0) {
            DB::table('setup_mode_pembayaran_student')
                ->whereNotIn('ID', $arr_ID)
                ->delete();
        }

        return redirect()->route('setup_mode_pembayaran_student.index');
    }
}
