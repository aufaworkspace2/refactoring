<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JenisUsmPmbService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Barryvdh\DomPDF\Facade\Pdf;

class JenisUsmPmbController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(JenisUsmPmbService $service)
    {
        $this->service = $service;

        $this->middleware(function ($request, $next) {
            if (!Session::get('username')) { return redirect('/'); }
            $lang = Session::get('language');
            if (!$lang && !request()->cookie('language')) { $lang = 'indonesia'; Session::put('language', $lang); }
            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);
            $levelUser = Session::get('LevelUser');
            $this->Create = cek_level($levelUser, 'c_jenis_usm_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_jenis_usm_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_jenis_usm_pmb', 'Delete');
            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        $data['Create'] = $this->Create;
        if (function_exists('log_akses')) { log_akses('View', 'Melihat Daftar Data Keahlian'); }
        return view('jenis_usm_pmb.v_jenis_usm_pmb', $data);
    }

    public function search(Request $request, $offset = 0)
    {
        $keyword = $request->input('keyword', '');
        $active = $request->input('active','');
        $limit = 10;
        $jml = $this->service->count_all($keyword,$active);
        $data['offset'] = $offset;
        $data['query'] = $this->service->get_data($limit, $offset, $keyword,$active);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;
        return view('jenis_usm_pmb.s_jenis_usm_pmb', $data);
    }

    public function add() { $data['save'] = 1; return view('jenis_usm_pmb.f_jenis_usm_pmb', $data); }
    public function view($id) { $data['row'] = $this->service->get_id($id); $data['save'] = 2; return view('jenis_usm_pmb.f_jenis_usm_pmb', $data); }

    public function save(Request $request, $save)
    {
        try {
            $validated = $request->validate([
                'id' => 'nullable|integer',
                'kode' => 'required|string|max:50',
                'nama' => 'required|string|max:255',
                'jenis' => 'required|string|max:100'
            ], [
                'kode.required' => 'Kode wajib diisi',
                'nama.required' => 'Nama wajib diisi',
                'jenis.required' => 'Jenis wajib diisi'
            ]);

            $id = $validated['id'] ?? null;
            $input = [
                'kode' => $validated['kode'],
                'nama' => $validated['nama'],
                'jenis' => $validated['jenis']
            ];
            
            // Check duplicate
            $cek = $this->service->checkDuplicateNama($input['nama'], $id);
            if ($cek && isset($cek->id)) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Nama jenis USM sudah digunakan'
                ], 422);
            }
            
            if ($save == 1) {
                if (function_exists('logs')) { 
                    logs("Menambah data {$input['kode']} {$input['nama']} {$input['jenis']} pada tabel jenis_usm_pmb"); 
                }
                $insertId = $this->service->add($input);
                return response()->json([
                    'status' => 1,
                    'message' => 'Data berhasil ditambahkan',
                    'data' => ['id' => $insertId]
                ]);
            }
            
            if ($save == 2) {
                if (function_exists('logs')) { 
                    logs("Mengubah data {$cek->nama} menjadi {$input['nama']} pada tabel jenis_usm_pmb"); 
                }
                $this->service->edit($id, $input);
                return response()->json([
                    'status' => 1,
                    'message' => 'Data berhasil diubah'
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Terjadi kesalahan saat menyimpan data'
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $request->validate([
                'checkID' => 'required|array|min:1',
                'checkID.*' => 'required|integer|exists:pmb_edu_jenisusm,id'
            ]);

            $checkid = $request->input('checkID', []);
            $removedIds = [];

            foreach ($checkid as $id) {
                // Check if can delete
                $this->service->delete($id);
                $removedIds[] = $id;
            }

            return response()->json([
                'status' => 'success',
                'removed_ids' => $removedIds,
                'class_prefix' => 'jenis_usm_pmb_'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function pdf(Request $request)
    {
        try {
            $keyword = $request->input('keyword', '');
            $data['query'] = $this->service->get_data(null, null, $keyword, '');

            $pdf = Pdf::loadView('jenis_usm_pmb.p_jenis_usm_pmb', $data);
            $pdf->setPaper('A4', 'portrait');
            return $pdf->stream('Data_Jenis_USM_PMB_' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e->getMessage());
            return response("Error generating PDF", 500);
        }
    }

    public function excel(Request $request)
    {
        try {
            $keyword = $request->input('keyword', '');
            $data = $this->service->get_data(null, null, $keyword, '');

            $spreadsheet = new Spreadsheet();
            $spreadsheet->setActiveSheetIndex(0);
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Data Jenis USM PMB');

            $row_num = 1;
            
            if (function_exists('cetak_kop_phpspreadsheet')) {
                $row_num = cetak_kop_phpspreadsheet($sheet, 'D');
            }

            $sheet->setCellValue('A'.$row_num, 'DATA JENIS USM PMB');
            $sheet->mergeCells('A'.$row_num.':D'.$row_num); 
            $sheet->getStyle('A'.$row_num)->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            $row_num += 2; 
            $start_table_row = $row_num;
            
            $sheet->setCellValue('A'.$row_num, 'No.');
            $sheet->setCellValue('B'.$row_num, 'Kode');
            $sheet->setCellValue('C'.$row_num, 'Nama');
            $sheet->setCellValue('D'.$row_num, 'Jenis');

            $sheet->getStyle('A'.$row_num.':D'.$row_num)->getFont()->setBold(true);
            $sheet->getStyle('A'.$row_num.':D'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A'.$row_num.':D'.$row_num)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFC000');
            $row_num++;

            $no = 1;
            if (!empty($data)) {
                foreach ($data as $row) {
                    $row = (object) $row;
                    
                    $sheet->setCellValue('A'.$row_num, $no++);
                    $sheet->setCellValue('B'.$row_num, $row->kode ?? '');
                    $sheet->setCellValue('C'.$row_num, $row->nama ?? '');
                    $sheet->setCellValue('D'.$row_num, $row->jenis ?? '');
                    
                    $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    $row_num++;
                }
            } else {
                $sheet->setCellValue('A'.$row_num, 'Tidak ada data');
                $sheet->mergeCells('A'.$row_num.':D'.$row_num);
                $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row_num++;
            }

            $styleBorder = [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ];
            $sheet->getStyle('A'.$start_table_row.':D'.($row_num-1))->applyFromArray($styleBorder);

            foreach(range('A','D') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $filename = "data_jenis_usm_pmb_" . date('d-m-Y') . ".xlsx";

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');
            header('Pragma: public');
            
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e->getMessage());
            return response("Error generating Excel", 500);
        }
    }
}
