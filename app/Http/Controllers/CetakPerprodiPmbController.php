<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CetakPerprodiPmbService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CetakPerprodiPmbController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(CetakPerprodiPmbService $service)
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

            // Note: Original code uses c_jadwal_usm_pmb permissions
            $levelUser = Session::get('LevelUser');
            $this->Create = cek_level($levelUser, 'c_jadwal_usm_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_jadwal_usm_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_jadwal_usm_pmb', 'Delete');

            return $next($request);
        });
    }

    /**
     * Display main view
     */
    public function index(Request $request, $offset = 0)
    {
        $data['Create'] = $this->Create;
        
        // Load gelombang data directly from database
        $data['data_gelombang'] = DB::table('pmb_tbl_gelombang')
            ->orderBy('nama', 'ASC')
            ->get();

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Cetak Per Prodi PMB');
        }

        return view('cetakperprodi_pmb.v_cetakperprodi_pmb', $data);
    }

    /**
     * Search with pagination
     */
    public function search(Request $request, $offset = 0)
    {
        $gelombang = $request->input('gelombang', '');
        $keyword = $request->input('keyword', '');

        $limit = 10;
        $no = 1;
        $bagiPer = 40;

        $jml = $this->service->count_all($keyword);
        $data['offset'] = $offset;

        $query = $this->service->get_data($limit, $offset, $gelombang, $keyword);

        // Process data for pagination per mahasiswa
        foreach ($query as $row_data) {
            $id = $row_data['id'];

            $row_data["no"] = $no;

            if ($row_data["jumlah"] > 0) {
                $loop = ceil($row_data["jumlah"] / $bagiPer);
                $x = 1;
                for ($i = 0; $i < $loop; $i++) {
                    if ($row_data["jumlah"] >= ($bagiPer * $x)) {
                        if ($x == 1) {
                            $row_data["cetak"][$x]["textawal"] = 1;
                            $row_data["cetak"][$x]["textakhir"] = ($bagiPer * $x);
                            $row_data["cetak"][$x]["awal"] = 0;
                            $row_data["cetak"][$x]["akhir"] = ($bagiPer * $x);
                        } else {
                            $row_data["cetak"][$x]["textawal"] = ($bagiPer * $i) + 1;
                            $row_data["cetak"][$x]["textakhir"] = ($bagiPer * $x);
                            $row_data["cetak"][$x]["awal"] = ($bagiPer * $i);
                            $row_data["cetak"][$x]["akhir"] = ($bagiPer * $x);
                        }
                    } else {
                        if ($x == 1) {
                            $row_data["cetak"][$x]["textawal"] = 1;
                            $row_data["cetak"][$x]["textakhir"] = $row_data["jumlah"];
                            $row_data["cetak"][$x]["awal"] = 0;
                            $row_data["cetak"][$x]["akhir"] = $row_data["jumlah"];
                        } else {
                            $row_data["cetak"][$x]["textawal"] = ($bagiPer * $i) + 1;
                            $row_data["cetak"][$x]["textakhir"] = $row_data["jumlah"];
                            $row_data["cetak"][$x]["awal"] = ($bagiPer * $i);
                            $row_data["cetak"][$x]["akhir"] = $row_data["jumlah"];
                        }
                    }
                    $x++;
                }
            }

            $datalist[$id] = $row_data;
            $no++;
        }

        $data['gelombang'] = $gelombang;
        $data['datalist'] = $datalist;
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);

        return view('cetakperprodi_pmb.s_cetakperprodi_pmb', $data);
    }

    /**
     * Print per prodi
     */
    public function cetak(Request $request)
    {
        $akhir = $request->input('akhir', '');
        $awal = $request->input('awal', '');
        $prodi = $request->input('prodi', '');
        $gelombang = $request->input('gelombang', '');

        $query = $this->service->get_data_cetak($akhir, $awal, $prodi, $gelombang);

        $no = 1;
        $datalist = [];
        foreach ($query as $row) {
            $row = (array) $row;
            $id = $row['id'] ?? 0;
            $nama = $row['nama_lengkap'] ?? '';
            $noujian = $row['noujian'] ?? '';
            $foto = $row['foto'] ?? '';

            $datalist[$id] = [
                "id" => $id,
                "no" => $no,
                "nama" => $nama,
                "noujian" => $noujian,
                "foto" => $foto
            ];

            $no++;
        }

        $data['identitas'] = DB::table('identitas')->where('ID', 1)->first();
        $data['datalist'] = $datalist;

        $pdf = \PDF::loadView('cetakperprodi_pmb.p_cetakperprodi_pmb', $data);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Cetak_Per_Prodi.pdf');
    }
}
