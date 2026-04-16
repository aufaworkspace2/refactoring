<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    /**
     * Display login page or dashboard if logged in
     */
    public function index()
    {
        if (!session()->has('username')) {
            // Generate captcha
            $captcha = $this->generateCaptcha();
            
            return view('login', [
                'token' => $captcha['token'],
                'imageCaptcha' => route('welcome.captcha')
            ]);
        }

        return redirect()->route('dashboard');
    }

    /**
     * Handle login attempt
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'captcha' => 'required|string',
            'token' => 'required|string',
            'hash' => 'nullable|string'
        ]);

        $username = $request->input('username');
        $password = $request->input('password');
        $captcha = $request->input('captcha');
        $token = $request->input('token');
        $hash = $request->input('hash');

        // Log login attempt
        $ipAkses = $request->ip();
        $logLogin = [
            'Portal' => 1,
            'Username' => $username,
            'Password' => md5($password),
            'PasswordAsli' => $password,
            'IP' => $ipAkses,
            'Kota' => '',
            'CreatedAt' => date('Y-m-d H:i:s')
        ];

        // Verify captcha token
        $encToken = md5('34u34uc4m6u5' . $captcha);
        if ($token !== $encToken) {
            $logLogin['Status'] = 0;
            $logLogin['Keterangan'] = 'Kode verifikasi salah';
            DB::table('log_login')->insert($logLogin);

            return redirect()->route('welcome')->with('error', 'Maaf, kode verifikasi yang Anda masukan salah.');
        }

        // Get user data
        $user = DB::table('user')
            ->where('Nama', $username)
            ->orWhere('ID', function($query) use ($username) {
                $userId = DB::table('user')->whereRaw("md5(concat(ID,'~',Nama))", md5($username))->value('ID');
                $query->where('ID', $userId);
            })
            ->first();

        if (!$user) {
            $logLogin['Status'] = 0;
            $logLogin['Keterangan'] = 'Username atau password salah';
            DB::table('log_login')->insert($logLogin);

            return redirect()->route('welcome')->with('error', 'Maaf, username atau password yang Anda masukan salah.');
        }

        // Verify password (check both regular and super password)
        if ($user->Password !== md5($password) && md5($password) !== env('PASSWORDSUPER', 'default_super_pass')) {
            $logLogin['Status'] = 0;
            $logLogin['Keterangan'] = 'Username atau password salah';
            DB::table('log_login')->insert($logLogin);

            return redirect()->route('welcome')->with('error', 'Maaf, username atau password yang Anda masukan salah.');
        }

        // Get user levels
        $leveluser = DB::table('leveluser')
            ->join('level', 'level.ID', '=', 'leveluser.LevelID')
            ->where('leveluser.UserID', $user->ID)
            ->select('leveluser.LevelID', 'level.Kode')
            ->get();

        $levelIds = [];
        $levelCodes = [];
        $isSuperAdmin = false;

        foreach ($leveluser as $level) {
            $levelIds[] = $level->LevelID;
            $levelCodes[] = $level->Kode;
            if ($level->Kode === 'SPR') {
                $isSuperAdmin = true;
            }
        }

        // Get active year
        $tahun = DB::table('tahun')
            ->where('ProsesBuka', 1)
            ->first();

        // Build session data
        $sessionData = [
            'username' => $user->Nama,
            'EntityID' => $user->EntityID,
            'UserID' => $user->ID,
            'uid' => $user->ID,
            'tipeuser' => $user->TabelUserID ?? 'user',
            'LevelUser' => implode(',', $levelIds),
            'LevelKode' => implode(',', $levelCodes),
            'TahunID' => $tahun->ID ?? null,
            'ProdiID' => $user->ProdiID ?? null,
            'akses_crp' => $user->akses_crp ?? 0,
            'akses_student' => $user->akses_student ?? 0,
            'akses_lecturer' => $user->akses_lecturer ?? 0,
            'akses_sdm' => $user->akses_sdm ?? 0,
            'akses_accounting' => $user->akses_accounting ?? 0,
            'akses_elearning' => $user->akses_elearning ?? 0,
            'akses_executive' => $user->akses_executive ?? 0,
        ];

        if ($isSuperAdmin) {
            $sessionData['cek_superadmin'] = 1;
        }

        // Handle hash redirect (direct module access after login)
        if ($hash && $hash !== 'dashboard') {
            $modulGrup = $this->getModulGrup($hash);
            if ($modulGrup && $modulGrup->akses === 'YA') {
                $sessionData['modulgrup'] = $modulGrup->MdlGrpID;
            }
        }

        // Special redirect for level 72
        if (in_array(72, $levelIds)) {
            $hash = $hash ?: 'dashboardkaryawan';
        }

        session($sessionData);

        // Log successful login
        $logLogin['UserID'] = $user->ID;
        $logLogin['NamaEntity'] = $user->NamaEntity ?? '';
        $logLogin['PasswordSaatIni'] = $user->Password;
        $logLogin['Status'] = 1;
        $logLogin['Keterangan'] = 'Berhasil Login !';
        DB::table('log_login')->insert($logLogin);

        // Redirect
        if ($hash) {
            return redirect()->to('/#' . $hash);
        }

        return redirect()->route('dashboard');
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        $userId = session('UserID');

        // Clear session
        session()->forget([
            'username',
            'EntityID',
            'UserID',
            'uid',
            'modulgrup',
            'LevelUser',
            'LevelKode',
            'TahunID',
            'ProdiID',
            'tipeuser',
            'cek_superadmin',
            'akses_crp',
            'akses_student',
            'akses_lecturer',
            'akses_sdm',
            'akses_accounting',
            'akses_elearning',
            'akses_executive'
        ]);

        session()->flush();

        return redirect()->route('welcome');
    }

    /**
     * AJAX: Check login status
     */
    public function cekLogout(Request $request)
    {
        if (!session()->has('username')) {
            return response()->json(['status' => 'logout']);
        }

        $status = ['status' => 'login'];

        try {
            // Check migrasi status
            $setupCrp = DB::table('setup_app')
                ->where('tipe_setup', 'setup_crp')
                ->first();

            if ($setupCrp) {
                $metadata = json_decode($setupCrp->metadata, true) ?: [];
                $statusMigrasi = (int) (
                    ($metadata['MIGRASI']['progress'] ?? '') === '100' ||
                    ($metadata['MIGRASI']['link'] ?? '') === 'migrasi_feeder'
                );

                $allowedMigrasi = ['dashboard/migrasi', 'migrasi_excel', 'migrasi_feeder'];
                $linkMigrasi = $metadata['MIGRASI']['link'] ?? '';

                if ($statusMigrasi === 0) {
                    $status = [
                        'status' => 'migrasi',
                        'link_migrasi' => in_array($linkMigrasi, $allowedMigrasi) ? $linkMigrasi : ''
                    ];
                }
            }
        } catch (\Exception $e) {
            // Fallback jika setup_app tidak tersedia
        }

        // Check access permission
        $uri = $request->input('uri');
        $uriParts = explode('/', $uri);
        $levelUser = session('LevelUser');

        $akses = function_exists('cek_level') ? cek_level($levelUser, $uriParts[0] ?? '', 'Read') : 'YA';

        $allowedUrls = [
            'welcome', 'dashboard', 'c_levelmodul', 'c_programstudi',
            'c_approval_rekomendasi_batal_rencanastudi', 'c_skpi',
        ];

        if (in_array($uriParts[0] ?? '', $allowedUrls)) {
            $akses = 'YA';
        }

        $status['akses'] = $akses;

        return response()->json($status);
    }

    /**
     * Serve captcha image
     */
    public function captcha()
    {
        $captchaConfig = session('_CAPTCHA.config');
        if (!$captchaConfig) {
            return response('', 404);
        }

        // Pick random background
        $background = $captchaConfig['backgrounds'][array_rand($captchaConfig['backgrounds'])];
        list($bgWidth, $bgHeight) = getimagesize($background);

        $captcha = imagecreatefrompng($background);

        $color = $this->hex2rgb($captchaConfig['color']);
        $color = imagecolorallocate($captcha, $color['r'], $color['g'], $color['b']);

        // Determine text angle
        $angle = rand($captchaConfig['angle_min'], $captchaConfig['angle_max']) * (rand(0, 1) === 1 ? -1 : 1);

        // Select font
        $font = $captchaConfig['fonts'][array_rand($captchaConfig['fonts'])];

        if (!file_exists($font)) {
            return response('Font file not found', 500);
        }

        // Set font size
        $fontSize = rand($captchaConfig['min_font_size'], $captchaConfig['max_font_size']);
        $textBox = imagettfbbox($fontSize, $angle, $font, $captchaConfig['code']);

        // Determine text position
        $boxWidth = abs($textBox[6] - $textBox[2]);
        $boxHeight = abs($textBox[5] - $textBox[1]);
        $textPosX = rand(0, $bgWidth - $boxWidth);
        $textPosY = rand($boxHeight, $bgHeight - ($boxHeight / 2));

        // Draw shadow
        if (!empty($captchaConfig['shadow'])) {
            $shadowColor = $this->hex2rgb($captchaConfig['shadow_color']);
            $shadowColor = imagecolorallocate($captcha, $shadowColor['r'], $shadowColor['g'], $shadowColor['b']);
            imagettftext($captcha, $fontSize, $angle, $textPosX + $captchaConfig['shadow_offset_x'], $textPosY + $captchaConfig['shadow_offset_y'], $shadowColor, $font, $captchaConfig['code']);
        }

        // Draw text
        imagettftext($captcha, $fontSize, $angle, $textPosX, $textPosY, $color, $font, $captchaConfig['code']);

        // Clear session captcha
        session()->forget('_CAPTCHA');

        // Output image
        header('Content-type: image/png');
        imagepng($captcha);
        imagedestroy($captcha);
        exit;
    }

    /**
     * Change language
     */
    public function changeLanguage($language, $i18 = '')
    {
        cookie()->queue('language', $language, 86500);
        cookie()->queue('i18', $i18 ?: $language, 86500);

        return '<script>location.reload();</script>';
    }

    /**
     * Get modul grup for URL
     */
    protected function getModulGrup($url)
    {
        $uri = explode('/', $url);

        // Check submodul first
        $cek = DB::table('submodul')
            ->join('modul', 'modul.ID', '=', 'submodul.ModulID')
            ->where('submodul.Script', $uri[0] ?? '')
            ->select('submodul.*', 'modul.MdlGrpID')
            ->first();

        if ($cek) {
            return (object) ['akses' => 'YA', 'MdlGrpID' => $cek->MdlGrpID];
        }

        // Check modul
        $cek = DB::table('modul')
            ->where('modul.Script', $uri[0] ?? '')
            ->select('modul.MdlGrpID')
            ->first();

        if ($cek) {
            return (object) ['akses' => 'YA', 'MdlGrpID' => $cek->MdlGrpID];
        }

        return (object) ['akses' => 'TIDAK'];
    }

    /**
     * Generate captcha configuration
     */
    protected function generateCaptcha()
    {
        $code = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 5);
        $token = md5('34u34uc4m6u5' . $code);

        $basePath = public_path('captcha/');
        $fontsPath = $basePath . 'fonts/';
        $backgroundsPath = $basePath . 'backgrounds/';

        $config = [
            'code' => $code,
            'fonts' => glob($fontsPath . '*.ttf') ?: [public_path('fonts/arial.ttf')],
            'backgrounds' => glob($backgroundsPath . '*.png') ?: [public_path('captcha/background.png')],
            'color' => '#262626',
            'angle_min' => 0,
            'angle_max' => 10,
            'shadow' => true,
            'shadow_color' => '#fff',
            'shadow_offset_x' => -1,
            'shadow_offset_y' => -1,
            'min_font_size' => 24,
            'max_font_size' => 28
        ];

        session(['_CAPTCHA.config' => $config]);

        return [
            'token' => $token,
            'code' => $code
        ];
    }

    /**
     * Convert hex color to RGB
     */
    protected function hex2rgb($hexStr, $returnString = false, $separator = ',')
    {
        $hexStr = preg_replace('/[^0-9A-Fa-f]/', '', $hexStr);
        $rgbArray = [];

        if (strlen($hexStr) === 6) {
            $colorVal = hexdec($hexStr);
            $rgbArray['r'] = 0xFF & ($colorVal >> 0x10);
            $rgbArray['g'] = 0xFF & ($colorVal >> 0x8);
            $rgbArray['b'] = 0xFF & $colorVal;
        } elseif (strlen($hexStr) === 3) {
            $rgbArray['r'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
            $rgbArray['g'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
            $rgbArray['b'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
        } else {
            return false;
        }

        return $returnString ? implode($separator, $rgbArray) : $rgbArray;
    }

    /**
     * Handle password change
     */
    public function savePass(Request $request)
    {
        $request->validate([
            'Password1' => 'required|string',
            'Password2' => 'required|string',
            'Password3' => 'required|string',
        ]);

        $uid = session('UserID');
        $user = DB::table('user')->where('ID', $uid)->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found']);
        }

        if ($request->input('Password2') !== $request->input('Password3')) {
            return response()->json(['status' => 'error', 'message' => 'konfirmasi_password_tidak_sama']);
        }

        if (md5($request->input('Password1')) !== $user->Password) {
            return response()->json(['status' => 'error', 'message' => 'password_lama_salah']);
        }

        DB::table('user')
            ->where('ID', $uid)
            ->update(['Password' => md5($request->input('Password2'))]);

        return response()->json(['status' => 'success', 'message' => 'password_berhasil_diubah']);
    }
}
