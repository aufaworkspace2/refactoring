<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LevelService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LevelController extends Controller
{
    protected $service;

    public function __construct(LevelService $service)
    {
        $this->service = $service;
    }

    public function index($offset = 0)
    {
        return view('level.v_level');
    }

    public function search(Request $request, $offset = 0)
    {
        $keyword = $request->input('keyword');
        $limit = 10;

        $jml = $this->service->count_all($keyword);
        $data['offset'] = $offset;
        $data['query'] = $this->service->get_data($limit, $offset, $keyword);

        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);

        return view('level.s_level', $data);
    }

    public function add()
    {
        $data['save'] = 1;
        $data['row'] = null;
        return view('level.f_level', $data);
    }

    public function view($id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['save'] = 2;
        return view('level.f_level', $data);
    }

    public function save(Request $request, $save)
    {
        try {
            $result = $this->service->save($save, $request->all());

            session()->flash('success', 'Data berhasil disimpan!');

            return response()->json(['status' => 'success', 'data' => $result]);

        }
        catch (\Exception $e) {

            session()->flash('error', 'Gagal menyimpan data!');

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function delete(Request $request)
    {
        $checkid = $request->input('checkID');

        $this->service->delete($checkid);

        return response()->json(['status' => 'success']);
    }

    public function pdf(Request $request)
    {
        $keyword = $request->input('keyword');
        $data['query'] = $this->service->get_data(null, null, $keyword);

        // Mengubah library bawaan CI3 "chtml2pdf" yang sudah tidak relevan
        // Memakai standard Laravel "barryvdh/laravel-dompdf"
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('level.p_level', $data);

        // Langsung tampilkan atau force-download (gunakan ->download('filename.pdf') jika ingin lgsg download)
        return $pdf->stream('data_level.pdf');
    }

    public function excel(Request $request)
    {
        $keyword = $request->input('keyword');
        $data = $this->service->get_data(null, null, $keyword);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Level');

        $row_num = 1;
        
        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'B');
        }

        $sheet->setCellValue('A'.$row_num, 'DATA LEVEL');
        $sheet->mergeCells('A'.$row_num.':B'.$row_num); 
        $sheet->getStyle('A'.$row_num)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row_num += 2; 
        $start_table_row = $row_num;
        
        $sheet->setCellValue('A'.$row_num, 'No.');
        $sheet->setCellValue('B'.$row_num, 'Nama');

        $sheet->getStyle('A'.$row_num.':B'.$row_num)->getFont()->setBold(true);
        $sheet->getStyle('A'.$row_num.':B'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A'.$row_num.':B'.$row_num)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFC000');
        $row_num++;

        $no = 1;
        if (!empty($data)) {
            foreach ($data as $row) {
                $row = (object) $row;
                
                $sheet->setCellValue('A'.$row_num, $no++);
                $sheet->setCellValue('B'.$row_num, $row->Nama ?? '');
                
                $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $row_num++;
            }
        } else {
            $sheet->setCellValue('A'.$row_num, 'Tidak ada data');
            $sheet->mergeCells('A'.$row_num.':B'.$row_num);
            $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row_num++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A'.$start_table_row.':B'.($row_num-1))->applyFromArray($styleBorder);

        foreach(range('A','B') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "data_level_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}