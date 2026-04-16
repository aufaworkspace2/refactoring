<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SettingTerminPembayaranMahasiswaController extends Controller
{
    protected $arr_opsi = ['SETTING_TERMIN_PEMBAYARAN_MAHASISWA'];
    protected $komp_opsi = [
        'SETTING_TERMIN_PEMBAYARAN_MAHASISWA' => [
            1 => 'per_termin_total',
            2 => 'per_termin_semester',
            3 => 'per_termin',
            4 => 'per_jenisbiaya'
        ],
    ];

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

            return $next($request);
        });
    }

    public function index()
    {
        return view('setting_termin_pembayaran_mahasiswa.v_setting_termin_pembayaran_mahasiswa');
    }

    public function content_opsi(Request $request)
    {
        $arr_opsi = $this->arr_opsi;
        $komp_opsi = $this->komp_opsi;
        $opsi = [];

        foreach ($arr_opsi as $val_opsi) {
            $rand_color = $this->rand_color();

            $queryopsi = DB::table('setting_termin_pembayaran_mahasiswa')
                ->select('*')
                ->where('Nama', $val_opsi)
                ->first();

            $opsi[$val_opsi]['Nama'] = $val_opsi;
            $opsi[$val_opsi]['Jenis'] = $queryopsi->Jenis ?? '';
            $opsi[$val_opsi]['Color'] = $rand_color;
        }

        $data['opsi'] = $opsi;
        $data['komp_opsi'] = $komp_opsi;

        return view('setting_termin_pembayaran_mahasiswa.s_content_data', $data);
    }

    public function set_opsi(Request $request)
    {
        $jumlah = $request->input('jumlah', []);
        $opsi = $request->input('opsi', []);

        $jumInsert = 0;
        $jumUpdate = 0;

        $input['LastUpdateUserID'] = Session::get('UserID');

        foreach ($opsi as $kode) {
            $Jenis = $jumlah[$kode] ?? '';

            if ($Jenis) {
                $cekopsi = DB::table('setting_termin_pembayaran_mahasiswa')
                    ->select('ID', 'Jenis')
                    ->where('Nama', $kode)
                    ->first();

                if (!$cekopsi || !$cekopsi->ID) {
                    $input['Nama'] = $kode;
                    $input['Jenis'] = $Jenis;
                    $input['createdAt'] = date('Y-m-d H:i:s');
                    $input['UserID'] = Session::get('UserID');

                    DB::table('setting_termin_pembayaran_mahasiswa')->insert($input);
                    $jumInsert += 1;
                } else {
                    $upd['Jenis'] = $Jenis;

                    DB::table('setting_termin_pembayaran_mahasiswa')
                        ->where('Nama', $kode)
                        ->update($upd);

                    $jumUpdate += 1;
                }
            }
        }

        $tampung['status'] = '1';
        $tampung['message'] = '';
        
        if ($jumInsert > 0) {
            $tampung['message'] .= $jumInsert . ' data Setting opsi berhasil ditambahkan.<br>';
        }
        if ($jumUpdate > 0) {
            $tampung['message'] .= $jumUpdate . ' data Setting opsi berhasil diubah.';
        }

        return response()->json($tampung);
    }

    private function rand_color()
    {
        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }
}
