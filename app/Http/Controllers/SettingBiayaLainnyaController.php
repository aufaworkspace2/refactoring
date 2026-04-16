<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SettingBiayaLainnyaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class SettingBiayaLainnyaController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(SettingBiayaLainnyaService $service)
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

            $this->Create = cek_level($levelUser, 'c_setting_biaya_lainnya', 'Create');
            $this->Update = cek_level($levelUser, 'c_setting_biaya_lainnya', 'Update');
            $this->Delete = cek_level($levelUser, 'c_setting_biaya_lainnya', 'Delete');

            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        $data['Create'] = $this->Create;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Setting Biaya Lainnya');
        }

        return view('setting_biaya_lainnya.v', $data);
    }

    public function search(Request $request, $offset = 0)
    {
        $keyword = $request->input('keyword', '');
        $limit = 10;

        $jml = $this->service->count_all($keyword);

        $data['offset'] = $offset;
        $data['query'] = $this->service->get_data($limit, $offset, $keyword);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('setting_biaya_lainnya.s', $data);
    }

    public function add()
    {
        $data['save'] = 1;
        $data['row'] = (object) [
            'ID' => '',
            'JenisBiayaID' => '',
            'Gambar' => '',
            'GambarKecil' => '',
            'Harga' => '',
            'Deskripsi' => '',
            'TanggalMulai' => '',
            'TanggalSelesai' => '',
            'NoRekening' => '',
            'AtasNamaRekening' => ''
        ];

        return view('setting_biaya_lainnya.f', $data);
    }

    public function view($id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['save'] = 2;

        return view('setting_biaya_lainnya.f', $data);
    }

    public function save(Request $request, $save)
    {
        $ID = $request->input('ID', '');
        $JenisBiayaID = $request->input('JenisBiayaID', '');
        $Harga = $request->input('Harga', '');
        $Deskripsi = $request->input('Deskripsi', '');
        $TanggalMulai = $request->input('TanggalMulai', '');
        $TanggalSelesai = $request->input('TanggalSelesai', '');
        $NamaGambar = '';

        $UserID = Session::get('UserID');

        // Handle image upload
        if ($request->hasFile('Gambar')) {
            $file = $request->file('Gambar');
            $extension = $file->getClientOriginalExtension();
            $data_u = time();
            $fileName = 'Gambar' . $data_u . '.' . $extension;

            // Create directory if not exists
            $uploadPath = public_path('client/biaya_lainnya/gambar');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Save and resize image using ImageManager (Intervention v4)
            $manager = new ImageManager(new Driver());
            $img = $manager->read($file->getRealPath());
            $img->resize(286, 180);
            $img->save($uploadPath . '/' . $fileName);

            $NamaGambar = $fileName;
        } else {
            // Keep old image if editing
            $NamaGambar = $request->input('NamaGambar', '');
        }

        $input = [
            'JenisBiayaID' => $JenisBiayaID,
            'Gambar' => $NamaGambar,
            'Harga' => $Harga,
            'Deskripsi' => $Deskripsi,
            'TanggalMulai' => $TanggalMulai,
            'TanggalSelesai' => $TanggalSelesai,
            'LastUpdateUserID' => $UserID
        ];

        if ($save == 1) {
            if (function_exists('logs')) {
                logs("Menambah data setting biaya lainnya " . get_field($JenisBiayaID, 'jenisbiaya', 'Nama') . " pada tabel setting_biaya_lainnya");
            }

            $input['createdAt'] = date('Y-m-d H:i:s');
            $input['UserID'] = $UserID;

            DB::table('setting_biaya_lainnya')->insert($input);
            return response(DB::getPdo()->lastInsertId());
        }

        if ($save == 2) {
            if (function_exists('logs')) {
                $oldJenisBiayaID = DB::table('setting_biaya_lainnya')->where('ID', $ID)->value('JenisBiayaID');
                logs("Mengubah data setting biaya lainnya " . get_field($oldJenisBiayaID, 'jenisbiaya', 'Nama') . " menjadi " . get_field($JenisBiayaID, 'jenisbiaya', 'Nama') . " pada tabel setting_biaya_lainnya");
            }

            DB::table('setting_biaya_lainnya')
                ->where('ID', $ID)
                ->update($input);

            return response('success');
        }
    }

    public function delete(Request $request)
    {
        $checkid = $request->input('checkID', []);

        $removed_ids = [];
        foreach ($checkid as $id) {
            if (!empty($id)) {
                $JenisBiayaID = DB::table('setting_biaya_lainnya')->where('ID', $id)->value('JenisBiayaID');
                $NamaJenisBiaya = get_field($JenisBiayaID, 'jenisbiaya', 'Nama');

                if (function_exists('log_akses')) {
                    log_akses('Hapus', 'Menghapus Data setting biaya lainnya Dengan Nama ' . $NamaJenisBiaya);
                }

                // Delete image file if exists
                $gambar = DB::table('setting_biaya_lainnya')->where('ID', $id)->value('Gambar');
                if ($gambar) {
                    $imagePath = public_path('client/biaya_lainnya/gambar/' . $gambar);
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }

                DB::table('setting_biaya_lainnya')->where('ID', $id)->delete();
                $removed_ids[] = $id;
            }
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removed_ids,
            'class_prefix' => 'setting_biaya_lainnya_'
        ]);
    }

    public function pdf(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $data['query'] = $this->service->get_data('', '', $keyword);

        return view('setting_biaya_lainnya.p', $data);
    }

    public function excel(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $data['query'] = $this->service->get_data('', '', $keyword);

        return view('setting_biaya_lainnya.ex', $data);
    }
}
