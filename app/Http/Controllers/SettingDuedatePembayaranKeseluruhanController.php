<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SettingDuedatePembayaranKeseluruhanController extends Controller
{
    protected $arr_opsi = ['BATAS_MAKSIMAL_SETELAH_PENGAJUAN'];

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
        return view('setting_duedate_pembayaran_keseluruhan.v_setting_duedate_pembayaran_keseluruhan');
    }

    public function content_opsi(Request $request)
    {
        $arr_opsi = $this->arr_opsi;
        $opsi = [];

        foreach ($arr_opsi as $val_opsi) {
            $rand_color = $this->rand_color();

            $queryopsi = DB::table('setting_duedate_pembayaran_keseluruhan')
                ->select('*')
                ->where('Nama', $val_opsi)
                ->first();

            $opsi[$val_opsi]['Nama'] = $val_opsi;
            $opsi[$val_opsi]['Jumlah'] = $queryopsi->Jumlah ?? '';
            $opsi[$val_opsi]['Color'] = $rand_color;
        }

        $data['opsi'] = $opsi;

        return view('setting_duedate_pembayaran_keseluruhan.s_content_data', $data);
    }

    public function set_opsi(Request $request)
    {
        $jumlah = $request->input('jumlah', []);

        $jumInsert = 0;
        $jumUpdate = 0;

        $input['LastUpdateUserID'] = Session::get('UserID');

        foreach ($this->arr_opsi as $kode) {
            $Jml = $jumlah[$kode] ?? '';

            if ($Jml) {
                $cekopsi = DB::table('setting_duedate_pembayaran_keseluruhan')
                    ->select('ID', 'Jumlah')
                    ->where('Nama', $kode)
                    ->first();

                if (!$cekopsi || !$cekopsi->ID) {
                    $input['Nama'] = $kode;
                    $input['jumlah'] = $Jml;
                    $input['createdAt'] = date('Y-m-d H:i:s');
                    $input['UserID'] = Session::get('UserID');

                    DB::table('setting_duedate_pembayaran_keseluruhan')->insert($input);
                    $jumInsert += 1;
                } else {
                    $upd['jumlah'] = $Jml;

                    DB::table('setting_duedate_pembayaran_keseluruhan')
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
