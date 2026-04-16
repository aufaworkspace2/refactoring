<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SetDraftRegistrasiUlangService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\JsonResponse;

class SetDraftRegistrasiUlangController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;
    protected $frontmedias;
    protected $frontdomain;
    protected $lokasimedias;

    public function __construct(SetDraftRegistrasiUlangService $service)
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
            $this->Create = cek_level($levelUser, 'c_set_draft_registrasiulang', 'Create');
            $this->Update = cek_level($levelUser, 'c_set_draft_registrasiulang', 'Update');
            $this->Delete = cek_level($levelUser, 'c_set_draft_registrasiulang', 'Delete');

            // Set media paths (as per CI3)
            $this->frontmedias = env('CLIENT_HOST', '') . "/pmb/medias/";
            $this->frontdomain = env('PMB_URL', '');
            $this->lokasimedias = env('CLIENT_PATH', '') . "/pmb/medias/";

            return $next($request);
        });
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

    /**
     * Display list of mahasiswa for draft registrasi
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
            log_akses('View', 'Melihat Menu Set Draft Registrasi Ulang');
        }

        return view('set_draft_registrasiulang.v_set_draft_registrasiulang', $data);
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
        $jml = $this->service->countVerifikasiPMB($whr, $bayar, $orderby_calon);
        
        $data['offset'] = $offset;
        $data['linkurlpage'] = $linkurlpage;

        $query = $this->service->getMahasiswaPMB($whr, $bayar, $orderby_calon, $limit, $offset);
        
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
                $row['statuslulus_str'] = "<label class='badge badge-secondary'>Belum Di Set Tagihan</label>";
                $row['textubah_statuslulus'] = "";
                $row['rev_statuslulus'] = "1";
            }

            // Status draft registrasi
            if ($row['statusdraftregistrasi_pmb'] == "1") {
                $row['statusdraftregistrasi_str'] = "<label class='badge badge-success' style='background-color:mediumseagreen;'>Sudah</label>";
                $row['textubah_statusdraftregistrasi'] = "<label class='badge badge-secondary'>Batalkan Registrasi</label>";
                $row['rev_statusdraftregistrasi'] = "0";
            } else if ($row['statusdraftregistrasi_pmb'] == "2") {
                $row['statusdraftregistrasi_str'] = "<label class='badge badge-danger' style='background-color:red;'>Tidak</label>";
                $row['textubah_statusdraftregistrasi'] = "<label class='badge badge-secondary'>Batalkan Tidak Registrasi</label>";
                $row['rev_statusdraftregistrasi'] = "0";
            } else {
                $row['statusdraftregistrasi_str'] = "<label class='badge badge-secondary'>Belum</label>";
                $row['textubah_statusdraftregistrasi'] = "";
                $row['rev_statusdraftregistrasi'] = "1";
            }

            // Get score ujian
            $row['score'] = $this->service->getScoreUjian($id);
            $row['nilai'] = $row['nilai_pmb'] ?? 0;

            $tempQuery[] = $row;
        }

        $data['jmlVerif'] = $this->service->countVerifikasiPMB('', '0', '');
        $data['query'] = $tempQuery;
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;
        $data['bayar'] = $bayar;

        // Load all prodi and jenjang (as per CI3)
        $all_prodi = $this->service->getAllProdi();
        $all_jenjang = [];
        foreach ($all_prodi as $prodi) {
            $jenjangId = $prodi['JenjangID'] ?? null;
            if ($jenjangId && !isset($all_jenjang[$jenjangId])) {
                $all_jenjang[$jenjangId] = DB::table('jenjang')->where('ID', $jenjangId)->first();
                if ($all_jenjang[$jenjangId]) {
                    $all_jenjang[$jenjangId] = (array) $all_jenjang[$jenjangId];
                }
            }
        }
        $data['all_prodi'] = $all_prodi;
        $data['all_jenjang'] = $all_jenjang;

        return view('set_draft_registrasiulang.s_set_draft_registrasiulang', $data);
    }

    /**
     * Set status draft registrasi for selected students
     */
    public function save(Request $request)
    {
        try {
            $request->validate([
                'checkID' => 'required|array|min:1',
                'checkID.*' => 'required',
                'action_do' => 'required|in:registrasi,tidakregistrasi,batalregistrasi'
            ]);

            $checkid = $request->input('checkID', []);
            $action = $request->input('action_do');

            \Log::info('SetDraftRegistrasiUlang::save - checkID: ' . json_encode($checkid) . ', action: ' . $action);

            $status_map = [
                'registrasi' => 1,
                'tidakregistrasi' => 2,
                'batalregistrasi' => 0
            ];

            $status = $status_map[$action];
            $updated = 0;

            foreach ($checkid as $id) {
                \Log::info('SetDraftRegistrasiUlang::save - Updating ID: ' . $id . ' to status: ' . $status);
                
                // Update directly in database (as per CI3 logic)
                $updated_status = $this->service->updateStatusDraftRegistrasi((int)$id, $status);

                \Log::info('SetDraftRegistrasiUlang::save - Update result for ID ' . $id . ': ' . ($updated_status ? 'true' : 'false'));

                if ($updated_status) {
                    $updated++;
                }
            }

            \Log::info('SetDraftRegistrasiUlang::save - Total updated: ' . $updated);

            if ($updated > 0) {
                $statuspesan = '1';
            } else {
                $statuspesan = '0';
            }

            $res['statuspesan'] = $statuspesan;
            $res['message'] = $updated > 0 ? "Berhasil update $updated mahasiswa" : 'Gagal update data';

            \Log::info('SetDraftRegistrasiUlang::save - Result: ' . json_encode($res));

            return response()->json($res);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Pilih minimal 1 data',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('SetDraftRegistrasiUlangController::save - Error: ' . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Terjadi kesalahan saat update status'
            ], 500);
        }
    }

    /**
     * Show detail draft tagihan for a student
     */
    public function detail_draft($ID)
    {
        $data['query'] = $this->service->getDetailDraft($ID);
        $data['jenisbiaya'] = $this->service->getAllJenisBiaya();
        $data['master_diskon'] = $this->service->getAllMasterDiskon();

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Detail Draft Tagihan Mahasiswa ID: ' . $ID);
        }

        return view('set_draft_registrasiulang.detail_draft', $data);
    }
}
