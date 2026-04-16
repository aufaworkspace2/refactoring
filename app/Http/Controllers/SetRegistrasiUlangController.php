<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SetRegistrasiUlangService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SetRegistrasiUlangController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(SetRegistrasiUlangService $service)
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

            $levelUser = Session::get('LevelUser');
            $this->Create = cek_level($levelUser, 'c_setregistrasiulang', 'Create');
            $this->Update = cek_level($levelUser, 'c_setregistrasiulang', 'Update');
            $this->Delete = cek_level($levelUser, 'c_setregistrasiulang', 'Delete');

            return $next($request);
        });
    }

    /**
     * Display list of mahasiswa for registration
     */
    public function index(Request $request, $offset = 0, $bayar = 1)
    {
        $data['Create'] = $this->Create;
        $data['bayar'] = $bayar;

        // Load dropdown data directly from database
        $data['data_gelombang'] = DB::table('pmb_tbl_gelombang')
            ->orderBy('nama', 'ASC')
            ->get();

        $data['data_gelombang_detail'] = DB::table('pmb_tbl_gelombang_detail')
            ->leftJoin('pmb_tbl_gelombang', 'pmb_tbl_gelombang.id', '=', 'pmb_tbl_gelombang_detail.gelombang_id')
            ->select('pmb_tbl_gelombang_detail.id', 'pmb_tbl_gelombang.nama', 'pmb_tbl_gelombang_detail.gelombang_id')
            ->orderBy('pmb_tbl_gelombang.nama', 'ASC')
            ->get();

        $data['data_program'] = DB::table('program')
            ->orderBy('Nama', 'ASC')
            ->get();

        // Get filter values from request for auto-select
        $data['selected_gelombang'] = $request->input('gelombang', '');
        $data['selected_gelombang_detail'] = $request->input('gelombang_detail', '');
        $data['selected_program'] = $request->input('program', '');
        $data['selected_pilihan1'] = $request->input('pilihan1', '');
        $data['selected_bayar'] = $request->input('bayar', $bayar);
        $data['selected_orderby'] = $request->input('orderby', 'mahasiswa.Nama');
        $data['selected_descasc'] = $request->input('descasc', 'ASC');
        $data['selected_viewpage'] = $request->input('viewpage', '10');

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Menu Set Registrasi Ulang');
        }

        return view('setregistrasiulang.v_setregistrasiulang', $data);
    }

    /**
     * Search mahasiswa with filters
     */
    public function search(Request $request, $offset = 0)
    {
        $whr = '';
        $bayar = $request->input('bayar', '1');
        $linkurlpage = "?1";

        // Build WHERE clause
        if (!empty($request->input('gelombang'))) {
            $sgelombang = $request->input('gelombang');
            $whr .= " AND pmb_tbl_gelombang.ID = '$sgelombang'";
            $linkurlpage .= "&gelombang=$sgelombang";
        }

        if (!empty($request->input('gelombang_detail'))) {
            $sgelombang_detail = $request->input('gelombang_detail');
            $whr .= " AND mahasiswa.gelombang_detail_pmb = '$sgelombang_detail'";
            $linkurlpage .= "&gelombang_detail=$sgelombang_detail";
        }

        if (!empty($request->input('program'))) {
            $sprogram = $request->input('program');
            $whr .= " AND mahasiswa.ProgramID = '$sprogram'";
            $linkurlpage .= "&program=$sprogram";
        }

        if (!empty($request->input('pilihan1'))) {
            $spilihan1 = $request->input('pilihan1');
            $whr .= " AND mahasiswa.pilihan1 = '$spilihan1'";
            $linkurlpage .= "&pilihan1=$spilihan1";
        }

        if (!empty($request->input('pilihan2'))) {
            $spilihan2 = $request->input('pilihan2');
            $whr .= " AND mahasiswa.pilihan2 = '$spilihan2'";
            $linkurlpage .= "&pilihan2=$spilihan2";
        }

        if (!empty($request->input('keyword'))) {
            $skeyword = $request->input('keyword');
            $whr .= " AND (mahasiswa.noujian_pmb LIKE '%$skeyword%' OR mahasiswa.Nama LIKE '%$skeyword%')";
            $linkurlpage .= "&keyword=$skeyword";
        }

        // Ordering
        $ord_tbh = $request->input('orderby', 'mahasiswa.Nama');
        $ord_asc = $request->input('descasc', 'ASC');
        $linkurlpage .= "&orderby=$ord_tbh&descasc=$ord_asc";

        $limit = 10;
        if (!empty($request->input('viewpage'))) {
            $limit = (int) $request->input('viewpage');
        }

        $orderby_calon = "ORDER BY $ord_tbh $ord_asc, mahasiswa.ID DESC";
        $jml = $this->service->countVerifikasiPMBRegis($whr, $bayar, $orderby_calon);

        $data['offset'] = $offset;
        $data['linkurlpage'] = $linkurlpage;

        $query = $this->service->getMahasiswaPMBRegis($whr, $bayar, $orderby_calon, $limit, $offset);

        // Process query to add status and score
        $tempQuery = [];
        foreach ($query as $row) {
            $row = (array) $row;
            $id = $row['ID'];

            // Status lulus
            if ($row['statuslulus_pmb'] == "1") {
                $row['statuslulus_str'] = "<label class='badge badge-success' style='background-color:mediumseagreen;'>Lulus</label>";
                $row['textubah_statuslulus'] = "<label class='badge badge-secondary'>Batalkan Lulus</label>";
                $row['rev_statuslulus'] = "0";
            } else if ($row['statuslulus_pmb'] == "2") {
                $row['statuslulus_str'] = "<label class='badge badge-danger' style='background-color:red;'>Tidak Lulus</label>";
                $row['textubah_statuslulus'] = "<label class='badge badge-secondary'>Batalkan Tidak Lulus</label>";
                $row['rev_statuslulus'] = "0";
            } else {
                $row['statuslulus_str'] = "<label class='badge badge-secondary'>Belum</label>";
                $row['textubah_statuslulus'] = "";
                $row['rev_statuslulus'] = "1";
            }

            // Status registrasi
            if ($row['statusregistrasi_pmb'] == "1") {
                $row['statusregistrasi_str'] = "<label class='badge badge-success' style='background-color:mediumseagreen;'>Sudah</label>";
                $row['textubah_statusregistrasi'] = "<label class='badge badge-secondary'>Batalkan Registrasi</label>";
                $row['rev_statusregistrasi'] = "0";
            } else if ($row['statusregistrasi_pmb'] == "2") {
                $row['statusregistrasi_str'] = "<label class='badge badge-danger' style='background-color:red;'>Tidak Dapat Tagihan</label>";
                $row['textubah_statusregistrasi'] = "<label class='badge badge-secondary'>Batalkan Tidak Registrasi</label>";
                $row['rev_statusregistrasi'] = "0";
            } else {
                $row['statusregistrasi_str'] = "<label class='badge badge-secondary'>Belum</label>";
                $row['textubah_statusregistrasi'] = "";
                $row['rev_statusregistrasi'] = "1";
            }

            // Get score ujian
            $row['score'] = $this->service->getScoreUjian($id);
            $row['nilai'] = $row['nilai_pmb'] ?? 0;

            // Get Jumlah Tagihan
            $row['JumlahTagihan'] = $this->service->getJumlahTagihan($id);

            // Check cicilan registrasiulang
            $row['cek_cicilan_registrasiulang'] = $this->service->checkCicilanRegistrasiulang($id);

            $tempQuery[] = $row;
        }

        // Get all prodi and jenjang
        $all_prodi = [];
        $all_jenjang = [];
        $programstudi = DB::table('programstudi')->get();
        foreach ($programstudi as $row_prodi) {
            $row_prodi = (array) $row_prodi;
            if (!isset($all_jenjang[$row_prodi['JenjangID']])) {
                $jenjang = DB::table('jenjang')->where('ID', $row_prodi['JenjangID'])->first();
                if ($jenjang) {
                    $all_jenjang[$row_prodi['JenjangID']] = (array) $jenjang;
                }
            }
            $row_prodi['NamaJenjang'] = $all_jenjang[$row_prodi['JenjangID']]['Nama'] ?? '';
            $all_prodi[$row_prodi['ID']] = $row_prodi;
        }

        $data['all_prodi'] = $all_prodi;
        $data['all_jenjang'] = $all_jenjang;

        $data['jmlVerif'] = $this->service->countVerifikasiPMBRegis('', '0', '');
        $data['query'] = $tempQuery;
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;
        $data['bayar'] = $bayar;

        return view('setregistrasiulang.s_setregistrasiulang', $data);
    }

    /**
     * Set registration status for selected students
     */
    public function save(Request $request)
    {
        $post = $request->all();
        $ac = $post['action_do'] ?? '';

        if ($ac == 'registrasi') {
            $statusregistrasi = "1";
        } else if ($ac == 'tidakregistrasi') {
            $statusregistrasi = "2";
        } else if ($ac == 'batalregistrasi') {
            $statusregistrasi = "0";
        }

        $cek = $post["checkID"] ?? [];
        $jml = count($cek);

        $ada_tagihan = 1;
        $diskon_tidak_double = 1;

        for ($i = 0; $i < $jml; $i++) {
            $idact = $cek[$i];
            $UserID = session('UserID');

            $run_hapus = $this->service->updateStatusRegistrasiPMBMahasiswa($idact, $statusregistrasi, $UserID);

            if ($run_hapus['status'] == 1) {
                if ($statusregistrasi == 1) {
                    // Success - already registered
                } else {
                    // Success - other status
                }
            } else {
                if (isset($run_hapus['double_diskon']) && $run_hapus['double_diskon'] == 1 && $statusregistrasi == 1) {
                    $diskon_tidak_double = 0;
                    break;
                }
            }
        }

        if ($ada_tagihan == 0) {
            $statuspesan = 'tidak ada tagihan';
        } else if ($diskon_tidak_double == 0) {
            $statuspesan = 'diskon double';
        } else {
            $statuspesan = $run_hapus['status'] ?? 1;
        }

        $res['statuspesan'] = $statuspesan;
        $res['message'] = $run_hapus['message'] ?? '';

        return response()->json($res);
    }

    /**
     * Get all tahun for dropdown
     */
    public function get_tahun()
    {
        $data = DB::table('tahun')
            ->orderBy('TahunID', 'DESC')
            ->get()
            ->map(fn($row) => "<option value='{$row->ID}'>{$row->Nama}</option>")
            ->implode('');
        
        return response($data)->header('Content-Type', 'text/html');
    }

    /**
     * Get all program for dropdown
     */
    public function get_program()
    {
        $data = DB::table('program')
            ->orderBy('Nama', 'ASC')
            ->get()
            ->map(fn($row) => "<option value='{$row->ID}'>{$row->Nama}</option>")
            ->implode('');
        
        return response($data)->header('Content-Type', 'text/html');
    }

    /**
     * Get all prodi for dropdown
     */
    public function get_prodi()
    {
        $data = DB::table('programstudi')
            ->join('jenjang', 'jenjang.ID', '=', 'programstudi.JenjangID')
            ->orderBy('jenjang.Nama', 'ASC')
            ->orderBy('programstudi.Nama', 'ASC')
            ->select('programstudi.ID', DB::raw('CONCAT(jenjang.Nama, " || ", programstudi.Nama) as display'))
            ->get()
            ->map(fn($row) => "<option value='{$row->ID}'>{$row->display}</option>")
            ->implode('');
        
        return response($data)->header('Content-Type', 'text/html');
    }

    /**
     * Get all gelombang for dropdown
     */
    public function get_gelombang()
    {
        $data = DB::table('pmb_tbl_gelombang')
            ->orderBy('nama', 'ASC')
            ->get()
            ->map(fn($row) => "<option value='{$row->id}'>{$row->kode} || {$row->nama}</option>")
            ->implode('');
        
        return response($data)->header('Content-Type', 'text/html');
    }

    /**
     * Get gelombang detail by gelombang_id
     */
    public function get_gelombang_detail(Request $request)
    {
        $gelombang_id = $request->input('gelombang_id', 0);
        
        $data = DB::table('pmb_tbl_gelombang_detail')
            ->where('gelombang_id', $gelombang_id)
            ->orderBy('nama', 'ASC')
            ->get()
            ->map(fn($row) => "<option value='{$row->id}'>{$row->nama}</option>")
            ->implode('');
        
        return response($data)->header('Content-Type', 'text/html');
    }
}
