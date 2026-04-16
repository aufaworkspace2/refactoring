<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SetupDuedatePembayaranService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SetupDuedatePembayaranController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(SetupDuedatePembayaranService $service)
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

            $this->Create = cek_level($levelUser, 'c_setup_duedate_pembayaran', 'Create');
            $this->Update = cek_level($levelUser, 'c_setup_duedate_pembayaran', 'Update');
            $this->Delete = cek_level($levelUser, 'c_setup_duedate_pembayaran', 'Delete');

            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        $data['Create'] = $this->Create;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Duedate Pembayaran');
        }

        return view('setup_duedate_pembayaran.v_setup_duedate_pembayaran', $data);
    }

    public function search(Request $request, $offset = 0)
    {
        $keyword = $request->input('keyword', '');
        $ProgramID = $request->input('ProgramID', '');
        $ProdiID = $request->input('ProdiID', '');
        $TahunMasuk = $request->input('TahunMasuk', '');
        $TahunID = $request->input('TahunID', '');
        $JenisBiayaID = $request->input('JenisBiayaID', '');

        $limit = 10;

        $jml = $this->service->count_all($keyword, $ProgramID, $ProdiID, $TahunMasuk, $TahunID, $JenisBiayaID);

        $data['offset'] = $offset;
        $data['query'] = $this->service->get_data($limit, $offset, $keyword, $ProgramID, $ProdiID, $TahunMasuk, $TahunID, $JenisBiayaID);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('setup_duedate_pembayaran.s_setup_duedate_pembayaran', $data);
    }

    public function add()
    {
        $data['save'] = 1;

        return view('setup_duedate_pembayaran.f_setup_duedate_pembayaran', $data);
    }

    public function view($id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['save'] = 2;

        return view('setup_duedate_pembayaran.f_setup_duedate_pembayaran', $data);
    }

    public function save(Request $request, $save)
    {
        $result = $this->service->save($save, $request->all());

        if ($result === 'gagal') {
            return response('gagal', 200);
        }

        return response($result, 200);
    }

    public function delete(Request $request)
    {
        $checkid = $request->input('checkID');
        $removedIds = [];

        if ($checkid) {
            foreach ($checkid as $id) {
                if (function_exists('log_akses')) {
                    $tahun = get_field($id, 'setup_duedate_pembayaran', 'TahunID');
                    log_akses('Hapus', 'Menghapus Data setup_duedate_pembayaran Pada Tahun ' . $tahun);
                }
                $this->service->delete($id);
                $removedIds[] = $id;
            }
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'setup_duedate_pembayaran_'
        ]);
    }

    public function pdf(Request $request)
    {
        // PDF export
        return redirect()->route('setup_duedate_pembayaran.index');
    }

    public function excel(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $ProgramID = $request->input('ProgramID', '');
        $ProdiID = $request->input('ProdiID', '');
        $TahunMasuk = $request->input('TahunMasuk', '');
        $TahunID = $request->input('TahunID', '');
        $JenisBiayaID = $request->input('JenisBiayaID', '');

        $query_data = $this->service->get_data('', '', $keyword, $ProgramID, $ProdiID, $TahunMasuk, $TahunID, $JenisBiayaID);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Setup DueDate Pembayaran');

        $row_num = 1;

        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'H');
        }

        $slog_text = strtoupper(__('app.slog') ?? 'DATA SETUP DUEDATE PEMBAYARAN');
        $sheet->setCellValue('A' . $row_num, $slog_text);
        $sheet->mergeCells('A' . $row_num . ':H' . $row_num);
        $sheet->getStyle('A' . $row_num)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $row_num++;

        $start_table_row = $row_num;

        $sheet->setCellValue('A' . $row_num, 'No.');
        $sheet->setCellValue('B' . $row_num, 'Tahun Akademik');
        $sheet->setCellValue('C' . $row_num, 'Program Kuliah');
        $sheet->setCellValue('D' . $row_num, 'Program Studi');
        $sheet->setCellValue('E' . $row_num, 'Angkatan');
        $sheet->setCellValue('F' . $row_num, 'Komponen Biaya');
        $sheet->setCellValue('G' . $row_num, 'Tipe');
        $sheet->setCellValue('H' . $row_num, 'DueDate');

        $sheet->getStyle('A' . $row_num . ':H' . $row_num)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row_num . ':H' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row_num . ':H' . $row_num)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC000');
        $row_num++;

        $no = 1;
        if (!empty($query_data)) {
            foreach ($query_data as $row) {
                $sheet->setCellValue('A' . $row_num, $no++);
                $sheet->setCellValue('B' . $row_num, get_field($row->TahunID, 'tahun'));

                $prog_kuliah = ($row->ProgramID == '0') ? 'Semua Program Kuliah' : get_field($row->ProgramID, 'program');
                $sheet->setCellValue('C' . $row_num, $prog_kuliah);

                $prog_studi = ($row->ProdiID === '0') ? 'Semua Program Studi' : get_field($row->ProdiID, 'programstudi');
                $sheet->setCellValue('D' . $row_num, $prog_studi);

                $angkatan = ($row->TahunMasuk === '0') ? 'Semua Tahun Masuk' : $row->TahunMasuk;
                $sheet->setCellValueExplicit('E' . $row_num, $angkatan, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                $sheet->setCellValue('F' . $row_num, $row->NamaJenisBiaya ?? '');
                $sheet->setCellValue('G' . $row_num, ucwords($row->Tipe ?? ''));

                $duedate_val = '';
                if ($row->Tipe == 'Tanggal') {
                    $duedate_val = tgl($row->Tanggal, '02');
                } else if ($row->Tipe == 'Hari') {
                    $duedate_val = "+" . $row->Hari . " Hari";
                }
                $sheet->setCellValue('H' . $row_num, $duedate_val);

                $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('G' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $row_num++;
            }
        } else {
            $sheet->setCellValue('A' . $row_num, 'Tidak ada data setup duedate pembayaran');
            $sheet->mergeCells('A' . $row_num . ':H' . $row_num);
            $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $row_num++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $start_table_row . ':H' . ($row_num - 1))->applyFromArray($styleBorder);

        if (!function_exists('cetak_kop_phpspreadsheet')) {
            $sheet->getColumnDimension('A')->setAutoSize(true);
        }
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "data_setup_duedate_pembayaran_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $objWriter->save('php://output');
        exit;
    }
}
