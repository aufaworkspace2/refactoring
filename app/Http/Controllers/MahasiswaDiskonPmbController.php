<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MahasiswaDiskonPmbService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Barryvdh\DomPDF\Facade\Pdf;

class MahasiswaDiskonPmbController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(MahasiswaDiskonPmbService $service)
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
            $this->Create = cek_level($levelUser, 'c_mahasiswa_diskon_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_mahasiswa_diskon_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_mahasiswa_diskon_pmb', 'Delete');

            return $next($request);
        });
    }

    /**
     * Display list of mahasiswa for discount
     */
    public function index(Request $request, $offset = 0)
    {
        $data['Create'] = $this->Create;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Mahasiswa Diskon PMB');
        }

        return view('mahasiswa_diskon_pmb.v', $data);
    }

    /**
     * Search mahasiswa with filters
     */
    public function search(Request $request, $offset = 0)
    {
        $filters = [];
        
        if (!empty($request->input('keyword'))) {
            $filters['keyword'] = $request->input('keyword');
        }

        if (!empty($request->input('TahunID'))) {
            $filters['TahunID'] = $request->input('TahunID');
        }

        if (!empty($request->input('ProgramID'))) {
            $filters['ProgramID'] = $request->input('ProgramID');
        }

        if (!empty($request->input('ProdiID'))) {
            $filters['ProdiID'] = $request->input('ProdiID');
        }

        if (!empty($request->input('StatusAktif'))) {
            $filters['StatusAktif'] = $request->input('StatusAktif');
        }

        $limit = 10;
        $jml = $this->service->count_all($filters);
        $data['offset'] = $offset;

        $data['query'] = $this->service->get_data($filters, $limit, $offset);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('mahasiswa_diskon_pmb.s', $data);
    }

    /**
     * Get nominal for discount type
     */
    public function changenominal(Request $request)
    {
        $PemberiDiskonID = $request->input('PemberiDiskonID', 0);
        
        $data = $this->service->changenominal($PemberiDiskonID);
        
        return response()->json($data);
    }

    /**
     * Show form to add discount
     */
    public function add()
    {
        $data['save'] = 1;
        $data['master_diskon'] = $this->service->getAllMasterDiskon();

        if (function_exists('log_akses')) {
            log_akses('View', 'Form Tambah Diskon Mahasiswa');
        }

        return view('mahasiswa_diskon_pmb.f', $data);
    }

    /**
     * View/edit discount for a student
     */
    public function view($id)
    {
        $data['row'] = $this->service->get_id($id);
        
        if ($data['row']) {
            $data['row_data'] = DB::table('mahasiswa')
                ->where('ID', $data['row']['MhswID'])
                ->first();
        }
        
        $data['master_diskon'] = $this->service->getAllMasterDiskon();
        $data['save'] = 2;

        if (function_exists('log_akses')) {
            log_akses('View', 'View/Edit Diskon Mahasiswa ID: ' . $id);
        }

        return view('mahasiswa_diskon_pmb.f_edit', $data);
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
     * Filter mahasiswa eligible for discount
     */
    public function filtermhs(Request $request)
    {
        $filters = [];
        
        if (!empty($request->input('TahunID'))) {
            $filters['TahunID'] = $request->input('TahunID');
        }

        if (!empty($request->input('ProdiID'))) {
            $filters['ProdiID'] = $request->input('ProdiID');
        }

        if (!empty($request->input('ProgramID'))) {
            $filters['ProgramID'] = $request->input('ProgramID');
        }

        if (!empty($request->input('KelasID'))) {
            $filters['KelasID'] = $request->input('KelasID');
        }

        if ($request->has('TahunMasuk')) {
            $filters['TahunMasuk'] = $request->input('TahunMasuk');
        }

        if (!empty($request->input('gelombang'))) {
            $filters['gelombang'] = $request->input('gelombang');
        }

        if (!empty($request->input('gelombang_detail'))) {
            $filters['gelombang_detail'] = $request->input('gelombang_detail');
        }

        if (!empty($request->input('keyword'))) {
            $filters['keyword'] = $request->input('keyword');
        }

        if (!empty($request->input('Tgl1')) && !empty($request->input('Tgl2'))) {
            $filters['Tgl1'] = $request->input('Tgl1');
            $filters['Tgl2'] = $request->input('Tgl2');
        }

        $result = $this->service->filtermhs($filters);
        
        $data['get_mhs'] = $result['get_mhs'] ?? [];
        $data['query_jenisbiaya'] = $result['query_jenisbiaya'] ?? [];
        $data['diskon'] = $result['diskon'] ?? [];
        $data['MhswID_arr'] = $result['MhswID_arr'] ?? [];
        $data['tahun'] = DB::table('tahun')->where('ID', $filters['TahunID'] ?? 0)->first();

        return view('mahasiswa_diskon_pmb.filtermhs', $data);
    }

    /**
     * Save discount for selected students
     */
    public function save(Request $request)
    {
        try {
            $request->validate([
                'checkID' => 'required|array|min:1',
                'checkID.*' => 'required|integer|exists:mahasiswa,ID',
                'JenisBiayaID' => 'required|array',
                'TahunID' => 'required'
            ]);

            $data = $request->all();
            $data['UserID'] = Session::get('UserID');

            $result = $this->service->save($data);

            if ($result['success'] > 0) {
                return response()->json([
                    'status' => 1,
                    'message' => "{$result['success']} data berhasil disimpan. {$result['failed']} data gagal."
                ]);
            } else {
                return response()->json([
                    'status' => 0,
                    'message' => 'Tidak ada data yang disimpan'
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('MahasiswaDiskonPmbController::save - Error: ' . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Terjadi kesalahan saat menyimpan data'
            ], 500);
        }
    }

    /**
     * Set discount for a student
     */
    public function set_diskon($MhswDiskonID)
    {
        try {
            if ($this->service->aktifkan($MhswDiskonID)) {
                return response()->json([
                    'status' => 1,
                    'message' => 'Diskon berhasil diaktifkan'
                ]);
            } else {
                return response()->json([
                    'status' => 0,
                    'message' => 'Gagal mengaktifkan diskon'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('MahasiswaDiskonPmbController::set_diskon - Error: ' . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
    }

    /**
     * Unset discount for a student
     */
    public function unset_diskon($MhswDiskonID)
    {
        try {
            if ($this->service->delete($MhswDiskonID)) {
                return response()->json([
                    'status' => 1,
                    'message' => 'Diskon berhasil dihapus'
                ]);
            } else {
                return response()->json([
                    'status' => 0,
                    'message' => 'Gagal menghapus diskon'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('MahasiswaDiskonPmbController::unset_diskon - Error: ' . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
    }

    /**
     * View discount detail
     */
    public function lihat_detail($MhswDiskonID)
    {
        $data['row'] = $this->service->get_id($MhswDiskonID);
        
        if (function_exists('log_akses')) {
            log_akses('View', 'Lihat Detail Diskon MhswDiskonID: ' . $MhswDiskonID);
        }

        return view('mahasiswa_diskon_pmb.lihat_detail', $data);
    }

    /**
     * Delete discount
     */
    public function delete(Request $request)
    {
        try {
            $request->validate([
                'checkID' => 'required|array|min:1',
                'checkID.*' => 'required|integer|exists:mahasiswa_diskon,MhswDiskonID'
            ]);

            $checkid = $request->input('checkID', []);
            $removedIds = [];

            foreach ($checkid as $id) {
                if ($this->service->delete($id)) {
                    $removedIds[] = $id;
                }
            }

            return response()->json([
                'status' => 'success',
                'removed_ids' => $removedIds,
                'class_prefix' => 'mahasiswa_diskon_',
                'message' => count($removedIds) . " data berhasil dihapus."
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pilih minimal 1 data untuk dihapus',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('MahasiswaDiskonPmbController::delete - Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus data'
            ], 500);
        }
    }

    /**
     * Activate discount
     */
    public function aktifkan(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer|exists:mahasiswa_diskon,MhswDiskonID'
            ]);

            $id = $request->input('id');

            if ($this->service->aktifkan($id)) {
                return response()->json([
                    'status' => 1,
                    'message' => 'Diskon berhasil diaktifkan'
                ]);
            } else {
                return response()->json([
                    'status' => 0,
                    'message' => 'Gagal mengaktifkan diskon'
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('MahasiswaDiskonPmbController::aktifkan - Error: ' . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
    }

    /**
     * Export to Excel
     */
    public function excel(Request $request)
    {
        $filters = [];

        if (!empty($request->input('keyword'))) {
            $filters['keyword'] = $request->input('keyword');
        }

        if (!empty($request->input('TahunID'))) {
            $filters['TahunID'] = $request->input('TahunID');
        }

        if (!empty($request->input('ProgramID'))) {
            $filters['ProgramID'] = $request->input('ProgramID');
        }

        if (!empty($request->input('ProdiID'))) {
            $filters['ProdiID'] = $request->input('ProdiID');
        }

        $data = $this->service->get_data($filters);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Mahasiswa Diskon PMB');

        $row_num = 1;
        
        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'H');
        }

        $sheet->setCellValue('A'.$row_num, 'DATA MAHASISWA DISKON PMB');
        $sheet->mergeCells('A'.$row_num.':H'.$row_num); 
        $sheet->getStyle('A'.$row_num)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row_num += 2; 
        $start_table_row = $row_num;
        
        $sheet->setCellValue('A'.$row_num, 'No.');
        $sheet->setCellValue('B'.$row_num, 'No. Ujian');
        $sheet->setCellValue('C'.$row_num, 'Nama');
        $sheet->setCellValue('D'.$row_num, 'Program');
        $sheet->setCellValue('E'.$row_num, 'Prodi');
        $sheet->setCellValue('F'.$row_num, 'Total Tagihan');
        $sheet->setCellValue('G'.$row_num, 'Total Diskon');
        $sheet->setCellValue('H'.$row_num, 'Status');

        $sheet->getStyle('A'.$row_num.':H'.$row_num)->getFont()->setBold(true);
        $sheet->getStyle('A'.$row_num.':H'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A'.$row_num.':H'.$row_num)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFC000');
        $row_num++;

        $no = 1;
        if (!empty($data)) {
            foreach ($data as $row) {
                $row = (object) $row;
                
                $sheet->setCellValue('A'.$row_num, $no++);
                $sheet->setCellValueExplicit('B'.$row_num, $row->noujian_pmb ?? '', DataType::TYPE_STRING);
                $sheet->setCellValue('C'.$row_num, $row->Nama ?? '');
                $sheet->setCellValue('D'.$row_num, $row->programNama ?? '-');
                $sheet->setCellValue('E'.$row_num, $row->prodiNama ?? '-');
                $sheet->setCellValue('F'.$row_num, number_format($row->JumlahTagihan ?? 0, 0, ',', '.'));
                $sheet->setCellValue('G'.$row_num, number_format($row->JumlahDiskon ?? 0, 0, ',', '.'));
                
                $status = ($row->StatusAktif == 1) ? 'Aktif' : 'Tidak Aktif';
                $sheet->setCellValue('H'.$row_num, $status);
                
                $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('G'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('H'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $row_num++;
            }
        } else {
            $sheet->setCellValue('A'.$row_num, 'Tidak ada data');
            $sheet->mergeCells('A'.$row_num.':H'.$row_num);
            $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row_num++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A'.$start_table_row.':H'.($row_num-1))->applyFromArray($styleBorder);

        foreach(range('A','H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "data_mahasiswa_diskon_pmb_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
