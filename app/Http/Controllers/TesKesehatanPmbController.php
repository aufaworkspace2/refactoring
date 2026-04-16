<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TesKesehatanPmbService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class TesKesehatanPmbController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(TesKesehatanPmbService $service)
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
            $this->Create = cek_level($levelUser, 'c_tes_kesehatan_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_tes_kesehatan_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_tes_kesehatan_pmb', 'Delete');

            return $next($request);
        });
    }

    /**
     * Display list of mahasiswa for health test
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

        $data['data_prodi'] = DB::table('programstudi')
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
            log_akses('View', 'Melihat Menu Tes Kesehatan');
        }

        return view('tes_kesehatan_pmb.v_tes_kesehatan_pmb', $data);
    }

    /**
     * Search mahasiswa with filters
     */
    public function search(Request $request, $offset = 0)
    {
        $filters = [];
        $bayar = $request->input('bayar', '1');

        // Build filters array
        if (!empty($request->input('gelombang'))) {
            $filters['gelombang'] = $request->input('gelombang');
        }

        if (!empty($request->input('gelombang_detail'))) {
            $filters['gelombang_detail'] = $request->input('gelombang_detail');
        }

        if (!empty($request->input('program'))) {
            $filters['program'] = $request->input('program');
        }

        if (!empty($request->input('pilihan1'))) {
            $filters['pilihan1'] = $request->input('pilihan1');
        }

        if (!empty($request->input('pilihan2'))) {
            $filters['pilihan2'] = $request->input('pilihan2');
        }

        if (!empty($request->input('keyword'))) {
            $filters['keyword'] = $request->input('keyword');
        }

        // Build ORDER BY clause
        $orderby = 'ORDER BY mahasiswa.Nama ASC';
        if (!empty($request->input('orderby'))) {
            $orderby_col = $request->input('orderby');
            $orderby_dir = $request->input('descasc', 'ASC');
            $orderby = "ORDER BY $orderby_col $orderby_dir";
        }

        $limit = 10;
        if (!empty($request->input('viewpage'))) {
            $limit = (int) $request->input('viewpage');
        }

        $jml = $this->service->countVerifikasiPMB($filters, $bayar, $orderby);
        $data['offset'] = $offset;
        $data['bayar'] = $bayar;

        $data['query'] = $this->service->getMahasiswaPMB($filters, $bayar, $orderby, $limit, $offset);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('tes_kesehatan_pmb.s_tes_kesehatan_pmb', $data);
    }

    /**
     * Set lulus tes kesehatan for selected students
     */
    public function set_lulus(Request $request)
    {
        try {
            $request->validate([
                'checkID' => 'required|array|min:1',
                'checkID.*' => 'required|integer|exists:mahasiswa,ID'
            ]);

            $checkid = $request->input('checkID', []);
            $updated = 0;

            foreach ($checkid as $id) {
                if ($this->service->updateStatusKesehatan($id, 1)) {
                    $updated++;
                }
            }

            return response()->json([
                'status' => $updated > 0 ? 1 : 0,
                'message' => "$updated mahasiswa berhasil ditandai lulus tes kesehatan."
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Pilih minimal 1 data',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('TesKesehatanPmbController::set_lulus - Error: ' . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Terjadi kesalahan saat update status'
            ], 500);
        }
    }

    /**
     * Export Excel - Data Tes Kesehatan
     */
    public function export_excel(Request $request)
    {
        $filters = [];
        $bayar = $request->input('bayar', '1');

        if (!empty($request->input('gelombang'))) {
            $filters['gelombang'] = $request->input('gelombang');
        }

        if (!empty($request->input('gelombang_detail'))) {
            $filters['gelombang_detail'] = $request->input('gelombang_detail');
        }

        if (!empty($request->input('program'))) {
            $filters['program'] = $request->input('program');
        }

        $data['query'] = $this->service->getExportData($filters, $bayar);

        return view('tes_kesehatan_pmb.ex_tes_kesehatan_pmb', $data);
    }

    /**
     * Download template Excel untuk upload nilai
     */
    public function download_template()
    {
        // Create simple Excel template
        $data = [
            ['No. Ujian', 'Nama', 'Nilai Tes Kesehatan'],
            ['', '', ''],
            ['', '', ''],
            ['', '', ''],
        ];

        // Return as CSV for simplicity
        $csv = implode("\n", array_map(fn($row) => implode(',', $row), $data));
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="template_nilai_tes_kesehatan.csv"');
    }

    /**
     * Upload Excel dengan hasil tes kesehatan
     */
    public function upload_excel(Request $request)
    {
        try {
            $request->validate([
                'fileUpload' => 'required|file|mimes:csv,xlsx,xls|max:5120'
            ]);

            $file = $request->file('fileUpload');
            $path = $file->getRealPath();

            // Parse CSV/Excel file
            $data = array_map('str_getcsv', file($path));
            array_shift($data); // Remove header

            // Format data for service
            $formatted_data = [];
            foreach ($data as $row) {
                if (count($row) >= 3) {
                    // Get mahasiswa by No. Ujian
                    $mahasiswa = DB::table('mahasiswa')
                        ->where('noujian_pmb', trim($row[0]))
                        ->first();

                    if ($mahasiswa) {
                        $formatted_data[] = [
                            'mahasiswa_id' => $mahasiswa->ID,
                            'nilai' => trim($row[2])
                        ];
                    }
                }
            }

            // Save hasil tes
            $result = $this->service->saveHasilTes($formatted_data);

            return response()->json([
                'status' => 1,
                'message' => "{$result['success']} data berhasil disimpan. {$result['failed']} data gagal."
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 0,
                'message' => 'File tidak valid',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('TesKesehatanPmbController::upload_excel - Error: ' . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Upload gagal'
            ], 500);
        }
    }

    /**
     * Set lulus/tidak lulus untuk selected students
     */
    public function set_status_lulus(Request $request)
    {
        try {
            $request->validate([
                'checkID' => 'required|array|min:1',
                'checkID.*' => 'required|integer|exists:mahasiswa,ID',
                'action' => 'required|in:lulus,tidaklulus,batalkan'
            ]);

            $checkid = $request->input('checkID', []);
            $action = $request->input('action');

            $status_map = [
                'lulus' => 1,
                'tidaklulus' => 2,
                'batalkan' => 0
            ];

            $status = $status_map[$action];
            $updated = 0;

            foreach ($checkid as $id) {
                if ($this->service->updateStatusLulusKesehatan($id, $status)) {
                    $updated++;
                }
            }

            return response()->json([
                'status' => $updated > 0 ? 1 : 0,
                'message' => "$updated mahasiswa berhasil diupdate statusnya."
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Pilih minimal 1 data',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('TesKesehatanPmbController::set_status_lulus - Error: ' . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Terjadi kesalahan saat update status'
            ], 500);
        }
    }
}
