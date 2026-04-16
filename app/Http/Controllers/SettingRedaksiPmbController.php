<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SettingRedaksiPmbController extends Controller
{
    protected $Update;
    private $setupDb = 'edufectacampus_dev.setup_app';

    private $tipe_alertkelulusanpmb = ["alertkelulusanpmb_belumkeluar","alertkelulusanpmb_lulus","alertkelulusanpmb_tidaklulus"];
    private $tipe_infokelulusanpmb = ["infokelulusanpmb_belumkeluar","infokelulusanpmb_lulus","infokelulusanpmb_tidaklulus","skl_pmb"];
    private $tipe_infopembayaranformulir = ["infopembayaranformulir_bayar","infopembayaranformulir_gratis"];
    private $tipe_templateemail = ["templateemail_pendaftaran","templateemail_usm_lulus","templateemail_usm_tidak_lulus","templateemail_generate_npm"];

    public function __construct()
    {
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
            $this->Update = cek_level($levelUser, 'c_setting_redaksi_pmb', 'Update');

            return $next($request);
        });
    }

    public function index()
    {
        return view('block_cs');
    }

    public function get_default_setting_redaksi_pmb(Request $request)
    {
        $tipe = $request->input('tipe', '');

        $get_default = DB::table('ref_setting_redaksi')
            ->where('tipe', $tipe)
            ->first();

        echo $get_default->redaksi ?? '';
    }

    public function get_setting_redaksi_pmb($tipe)
    {
        if (!is_array($tipe)) $tipe = [$tipe];

        $return = [];

        $get_setup_app = DB::table($this->setupDb)
            ->whereIn('tipe_setup', $tipe)
            ->get();

        foreach ($get_setup_app as $row_setup_app) {
            $obj_setup_app = new \stdClass();
            $obj_setup_app->tipe_setup = $row_setup_app->tipe_setup;
            $obj_setup_app->metadata = $row_setup_app->metadata;

            $return[$row_setup_app->tipe_setup] = $obj_setup_app;
        }

        // Check setting redaksi is exist, if not exist set setting redaksi to default
        foreach ($tipe as $row_tipe) {
            if (empty($return[$row_tipe]->metadata)) {
                $get_redaksi_default = DB::table('ref_setting_redaksi')
                    ->where('tipe', $row_tipe)
                    ->value('redaksi');

                $obj_redaksi_default = new \stdClass();
                $obj_redaksi_default->tipe_setup = $row_tipe;
                $obj_redaksi_default->metadata = $get_redaksi_default;
                $return[$row_tipe] = $obj_redaksi_default;
            }
        }

        return $return;
    }

    public function alert_kelulusan()
    {
        $data['Update'] = $this->Update;

        $get_setup_app = $this->get_setting_redaksi_pmb($this->tipe_alertkelulusanpmb);
        foreach ($get_setup_app as $row_setup) {
            $data[$row_setup->tipe_setup] = $row_setup->metadata;
        }

        return view('setting_redaksi_pmb.alert_kelulusan', $data);
    }

    public function save_alert_kelulusan(Request $request)
    {
        foreach ($this->tipe_alertkelulusanpmb as $rowtipe) {
            $arr_data = [];
            $arr_data['metadata'] = $request->input($rowtipe, '');

            $check_setup = DB::table($this->setupDb)
                ->where('tipe_setup', $rowtipe)
                ->first();

            if (empty($check_setup)) {
                $arr_data['tipe_setup'] = $rowtipe;
                DB::table($this->setupDb)->insert($arr_data);
            } else {
                DB::table($this->setupDb)
                    ->where('tipe_setup', $rowtipe)
                    ->update($arr_data);
            }
        }

        echo '1';
    }

    public function info_kelulusan()
    {
        $data['Update'] = $this->Update;

        $get_setup_app = $this->get_setting_redaksi_pmb($this->tipe_infokelulusanpmb);
        foreach ($get_setup_app as $row_setup) {
            $data[$row_setup->tipe_setup] = $row_setup->metadata;
        }

        return view('setting_redaksi_pmb.info_kelulusan', $data);
    }

    public function save_info_kelulusan(Request $request)
    {
        foreach ($this->tipe_infokelulusanpmb as $rowtipe) {
            $arr_data = [];
            $arr_data['metadata'] = $request->input($rowtipe, '');

            $check_setup = DB::table($this->setupDb)
                ->where('tipe_setup', $rowtipe)
                ->first();

            if (empty($check_setup)) {
                $arr_data['tipe_setup'] = $rowtipe;
                DB::table($this->setupDb)->insert($arr_data);
            } else {
                DB::table($this->setupDb)
                    ->where('tipe_setup', $rowtipe)
                    ->update($arr_data);
            }
        }

        echo '1';
    }

    public function info_pembayaran_formulir()
    {
        $data['Update'] = $this->Update;

        $get_setup_app = $this->get_setting_redaksi_pmb($this->tipe_infopembayaranformulir);
        foreach ($get_setup_app as $row_setup) {
            $data[$row_setup->tipe_setup] = $row_setup->metadata;
        }

        return view('setting_redaksi_pmb.info_pembayaran_formulir', $data);
    }

    public function save_info_pembayaran_formulir(Request $request)
    {
        foreach ($this->tipe_infopembayaranformulir as $rowtipe) {
            $arr_data = [];
            $arr_data['metadata'] = $request->input($rowtipe, '');

            $check_setup = DB::table($this->setupDb)
                ->where('tipe_setup', $rowtipe)
                ->first();

            if (empty($check_setup)) {
                $arr_data['tipe_setup'] = $rowtipe;
                DB::table($this->setupDb)->insert($arr_data);
            } else {
                DB::table($this->setupDb)
                    ->where('tipe_setup', $rowtipe)
                    ->update($arr_data);
            }
        }

        echo '1';
    }

    public function email()
    {
        $data['Update'] = $this->Update;

        $get_setup_app = $this->get_setting_redaksi_pmb($this->tipe_templateemail);
        foreach ($get_setup_app as $row_setup) {
            $data[$row_setup->tipe_setup] = $row_setup->metadata;
        }

        return view('setting_redaksi_pmb.format_email', $data);
    }

    public function save_format_email(Request $request)
    {
        foreach ($this->tipe_templateemail as $rowtipe) {
            $arr_data = [];
            $arr_data['metadata'] = $request->input($rowtipe, '');

            $check_setup = DB::table($this->setupDb)
                ->where('tipe_setup', $rowtipe)
                ->first();

            if (empty($check_setup)) {
                $arr_data['tipe_setup'] = $rowtipe;
                DB::table($this->setupDb)->insert($arr_data);
            } else {
                DB::table($this->setupDb)
                    ->where('tipe_setup', $rowtipe)
                    ->update($arr_data);
            }
        }

        echo '1';
    }

    public function upload_file(Request $request)
    {
        $config['upload_path'] = public_path('pmb/setting_redaksi');
        $config['allowed_types'] = 'jpeg|jpg|png|gif';

        // Create directory if not exists
        if (!file_exists($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move($config['upload_path'], $fileName);

            return response()->json([
                'location' => asset('pmb/setting_redaksi/' . $fileName)
            ]);
        }

        return response()->json(['error' => 'No file uploaded'], 500);
    }
}
