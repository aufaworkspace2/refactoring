<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DataLeadsPmbService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Barryvdh\DomPDF\Facade\Pdf;

class DataLeadsPmbController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(DataLeadsPmbService $service)
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

            $this->Create = cek_level($levelUser, 'c_data_leads_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_data_leads_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_data_leads_pmb', 'Delete');

            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Data Leads PMB');
        }

        return view('data_leads.v_data_leads_pmb');
    }

    public function search(Request $request, $offset = 0)
    {
        $StatusMendaftar = $request->input('StatusMendaftar', '');
        $keyword = $request->input('keyword', '');
        $tgl1 = $request->input('tgl1', '');
        $tgl2 = $request->input('tgl2', '');

        $limit = 10;
        $jml = $this->service->count_all($keyword, $StatusMendaftar, $tgl1, $tgl2);
        $data['offset'] = $offset;

        $data['query'] = $this->service->get_data($limit, $offset, $keyword, $StatusMendaftar, $tgl1, $tgl2);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('data_leads.s_data_leads_pmb', $data);
    }

    public function delete(Request $request)
    {
        $checkid = $request->input('checkID', []);
        $removedIds = [];

        for ($x = 0; $x <= count($checkid) - 1; $x++) {
            if (function_exists('log_akses')) {
                log_akses('Hapus', 'Menghapus Data Leads Dengan Nama ' . $this->service->getNamaById($checkid[$x]));
            }
            $this->service->delete($checkid[$x]);
            $removedIds[] = $checkid[$x];
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'data_'
        ]);
    }

    public function pdf(Request $request)
    {
        $StatusMendaftar = $request->input('StatusMendaftar', '');
        $keyword = $request->input('keyword', '');
        $tgl1 = $request->input('Tgl1', '');
        $tgl2 = $request->input('Tgl2', '');

        $offset = 0;
        $limit = 100000000000;
        $data['query'] = $this->service->get_data($limit, $offset, $keyword, $StatusMendaftar, $tgl1, $tgl2);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('data_leads.p_data_leads_pmb', $data);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Data_Leads_PMB_' . date('Y-m-d') . '.pdf');
    }

    public function excel(Request $request)
    {
        $StatusMendaftar = $request->input('StatusMendaftar', '');
        $keyword = $request->input('keyword', '');
        $tgl1 = $request->input('Tgl1', '');
        $tgl2 = $request->input('Tgl2', '');

        $offset = 0;
        $limit = 100000000000;
        $data = $this->service->get_data($limit, $offset, $keyword, $StatusMendaftar, $tgl1, $tgl2);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Leads PMB');

        $row_num = 1;
        
        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'H');
        }

        $sheet->setCellValue('A'.$row_num, 'REKAPITULASI DATA LEADS PMB');
        $sheet->mergeCells('A'.$row_num.':H'.$row_num); 
        $sheet->getStyle('A'.$row_num)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row_num += 2; 
        $start_table_row = $row_num;
        
        $sheet->setCellValue('A'.$row_num, 'No.');
        $sheet->setCellValue('B'.$row_num, 'Nama');
        $sheet->setCellValue('C'.$row_num, 'Email');
        $sheet->setCellValue('D'.$row_num, 'Telepon');
        $sheet->setCellValue('E'.$row_num, 'Asal Sekolah');
        $sheet->setCellValue('F'.$row_num, 'Tanggal Lahir');
        $sheet->setCellValue('G'.$row_num, 'Jenis Kelamin');
        $sheet->setCellValue('H'.$row_num, 'Status Daftar');

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
                
                $sheet->setCellValue('B'.$row_num, $row->nama ?? '');
                $sheet->setCellValue('C'.$row_num, $row->email ?? '');
                
                $sheet->setCellValueExplicit('D'.$row_num, $row->telepon ?? '', DataType::TYPE_STRING);
                
                $sheet->setCellValue('E'.$row_num, $row->asal_sekolah ?? '');
                
                $tanggal_format = '';
                if (isset($row->tanggal_lahir) && $row->tanggal_lahir != '') {
                    $tanggal_format = date('d/m/Y', strtotime($row->tanggal_lahir));
                }
                $sheet->setCellValue('F'.$row_num, $tanggal_format);
                
                $jenis_kelamin = '';
                if ($row->jenis_kelamin == 'L') {
                    $jenis_kelamin = 'Laki-laki';
                } elseif ($row->jenis_kelamin == 'P') {
                    $jenis_kelamin = 'Perempuan';
                } else {
                    $jenis_kelamin = '-';
                }
                $sheet->setCellValue('G'.$row_num, $jenis_kelamin);

                $status_daftar = ($row->MhswID) ? "Sudah" : "Belum";
                $sheet->setCellValue('H'.$row_num, $status_daftar);
                
                $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('G'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
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

        $filename = "Data_Leads_PMB_" . $tgl1 . "_" . $tgl2 . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
