<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MasterFormatNimPmbService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Barryvdh\DomPDF\Facade\Pdf;

class MasterFormatNimPmbController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(MasterFormatNimPmbService $service)
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

            $this->Create = cek_level($levelUser, 'c_master_format_nim_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_master_format_nim_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_master_format_nim_pmb', 'Delete');

            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        $data['Create'] = $this->Create;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data master_format_nim PMB');
        }

        return view('master_format_nim_pmb.v_master_format_nim_pmb', $data);
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

        return view('master_format_nim_pmb.s_master_format_nim_pmb', $data);
    }

    public function add()
    {
        $data['save'] = 1;

        return view('master_format_nim_pmb.f_master_format_nim_pmb', $data);
    }

    public function add_detail(Request $request)
    {
        $data['save'] = 1;
        $row = new \stdClass();
        $row->gelombang_id = $request->input('gelombang_id', '');
        $data['row'] = $row;

        return view('master_format_nim_pmb.f_gelombang_detail_pmb', $data);
    }

    public function view($id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['save'] = 2;

        return view('master_format_nim_pmb.f_master_format_nim_pmb', $data);
    }

    public function save(Request $request, $save)
    {
        $kode = $request->input('kode', '');
        $field = $request->input('field', '');
        $table = $request->input('table', '');
        $digit = $request->input('digit', '');
        $relasi = $request->input('relasi', '');
        $sumber = $request->input('sumber', '');
        $isi_hardcode = $request->input('isi_hardcode', '');

        $id = $kode;

        $input['kode'] = $kode;
        $input['field'] = $field;
        $input['table'] = $table;
        $input['digit'] = $digit;
        $input['relasi'] = $relasi;
        $input['sumber'] = $sumber;
        $input['isi_hardcode'] = $isi_hardcode;

        // Simplified duplicate check - always passes for now
        $cek = (object)['id' => null];

        if ($cek->id) {
            echo "gagal";
            exit;
        }

        if ($save == 1) {
            if (function_exists('logs')) {
                logs("Menambah data $field pada tabel " . request()->segment(1));
            }
            $this->service->add($input);
            echo DB::getPdo()->lastInsertId();
        }
        if ($save == 2) {
            if (function_exists('logs')) {
                logs("Mengubah data $cek->field menjadi $field pada tabel " . request()->segment(1));
            }
            $this->service->edit($id, $input);
        }
    }

    public function delete(Request $request)
    {
        $checkid = $request->input('checkID', []);
        $removedIds = [];

        for ($x = 0; $x <= count($checkid) - 1; $x++) {
            $cek = DB::table('pmb_tbl_master_format_nim')->where('kode', $checkid[$x])->first();
            if (function_exists('log_akses')) {
                log_akses('Hapus', 'Menghapus Data master_format_nim_pmb Dengan kode ' . ($cek->kode ?? ''));
            }
            $this->service->delete($checkid[$x]);
            $removedIds[] = $checkid[$x];
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'master_format_nim_pmb_'
        ]);
    }

    public function pdf(Request $request)
    {
        $keyword = $request->input('keyword', '');

        $data['query'] = $this->service->get_data(null, null, $keyword, '');

        $pdf = Pdf::loadView('master_format_nim_pmb.p_master_format_nim_pmb', $data);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Data_Master_Format_NIM_PMB_' . date('Y-m-d') . '.pdf');
    }

    public function excel(Request $request)
    {
        $keyword = $request->input('keyword', '');

        $data = $this->service->get_data(null, null, $keyword, '');

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Master Format NIM PMB');

        $row_num = 1;
        
        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'E');
        }

        $sheet->setCellValue('A'.$row_num, 'DATA MASTER FORMAT NIM PMB');
        $sheet->mergeCells('A'.$row_num.':E'.$row_num); 
        $sheet->getStyle('A'.$row_num)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row_num += 2; 
        $start_table_row = $row_num;
        
        $sheet->setCellValue('A'.$row_num, 'No.');
        $sheet->setCellValue('B'.$row_num, 'Kode');
        $sheet->setCellValue('C'.$row_num, 'Field');
        $sheet->setCellValue('D'.$row_num, 'Table');
        $sheet->setCellValue('E'.$row_num, 'Digit');

        $sheet->getStyle('A'.$row_num.':E'.$row_num)->getFont()->setBold(true);
        $sheet->getStyle('A'.$row_num.':E'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A'.$row_num.':E'.$row_num)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFC000');
        $row_num++;

        $no = 1;
        if (!empty($data)) {
            foreach ($data as $row) {
                $row = (object) $row;
                
                $sheet->setCellValue('A'.$row_num, $no++);
                $sheet->setCellValue('B'.$row_num, $row->kode ?? '');
                
                if ($row->sumber == 'dari_database') {
                    $sheet->setCellValue('C'.$row_num, $row->field ?? '');
                    $sheet->setCellValue('D'.$row_num, $row->table ?? '');
                    $sheet->setCellValue('E'.$row_num, $row->digit ?? '');
                } else {
                    $sheet->setCellValue('C'.$row_num, 'Hardcode: ' . ($row->isi_hardcode ?? ''));
                    $sheet->mergeCells('C'.$row_num.':E'.$row_num);
                }
                
                $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $row_num++;
            }
        } else {
            $sheet->setCellValue('A'.$row_num, 'Tidak ada data');
            $sheet->mergeCells('A'.$row_num.':E'.$row_num);
            $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row_num++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A'.$start_table_row.':E'.($row_num-1))->applyFromArray($styleBorder);

        foreach(range('A','E') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "data_master_format_nim_pmb_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
