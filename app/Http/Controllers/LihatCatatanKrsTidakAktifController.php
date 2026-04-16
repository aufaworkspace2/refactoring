<?php

namespace App\Http\Controllers;

use App\Services\LihatCatatanKrsTidakAktifService;
use Illuminate\Http\Request;

class LihatCatatanKrsTidakAktifController extends BaseController
{
    protected $service;

    public function __construct(LihatCatatanKrsTidakAktifService $service)
    {
        $this->service = $service;
    }

    /**
     * Display main view
     */
    public function index()
    {
        $data['dataTahun'] = $this->service->getDataTahunSemester();
        $data['angkatan'] = $this->service->getDataTahunAngkatan();

        return view('lihat_catatan_krs_tidak_aktif.v_lihat_catatan_krs_tidak_aktif', $data);
    }

    /**
     * Search data
     */
    public function search(Request $request, $offset = 0)
    {
        try {
            $filters = $request->only([
                'TahunMasuk',
                'TahunID',
                'ProgramID',
                'ProdiID',
                'Status',
                'Tgl1',
                'Tgl2',
                'keyword',
                'SetKRSYa'
            ]);

            // Clean empty date values
            if (isset($filters['Tgl1']) && $filters['Tgl1'] == '____-__-__') {
                $filters['Tgl1'] = null;
            }
            if (isset($filters['Tgl2']) && $filters['Tgl2'] == '____-__-__') {
                $filters['Tgl2'] = null;
            }

            $limit = 10;
            $result = $this->service->searchData($limit, $offset, $filters);

            return view('lihat_catatan_krs_tidak_aktif.s_lihat_catatan_krs_tidak_aktif', [
                'offset' => $offset,
                'query' => $result['data'],
                'link' => load_pagination($result['total'], $limit, $offset, 'search', 'filter'),
            ]);

        } catch (\Exception $e) {
            $this->logError('search', $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve/Reject records
     */
    public function approve(Request $request, $status)
    {
        try {
            $validated = $request->validate([
                'checkID' => 'required|array',
                'CatatanTambahan' => 'nullable|string'
            ]);

            $result = $this->service->approveRecords(
                $validated['checkID'],
                $status,
                $validated['CatatanTambahan'] ?? ''
            );

            return response()->json($result);

        } catch (\Exception $e) {
            $this->logError('approve', $e->getMessage(), $request->all());
            return response()->json([
                'status' => 0,
                'message' => 'Data gagal diproses'
            ], 500);
        }
    }

    /**
     * Export Excel
     */
    public function excel(Request $request)
    {
        $filters = $request->only([
            'TahunMasuk',
            'TahunID',
            'ProgramID',
            'ProdiID',
            'Status',
            'Tgl1',
            'Tgl2',
            'keyword',
            'SetKRSYa'
        ]);

        // Clean empty date values
        if (isset($filters['Tgl1']) && $filters['Tgl1'] == '____-__-__') {
            $filters['Tgl1'] = null;
        }
        if (isset($filters['Tgl2']) && $filters['Tgl2'] == '____-__-__') {
            $filters['Tgl2'] = null;
        }

        $result = $this->service->searchData(100000000, 0, $filters);
        $queryData = $result['data'];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pengajuan Perjanjian');

        $rowNum = 1;

        if (function_exists('cetak_kop_phpspreadsheet')) {
            $rowNum = cetak_kop_phpspreadsheet($sheet, 'I');
        }

        $slogText = strtoupper('DATA PENGAJUAN PERJANJIAN');
        $sheet->setCellValue('A' . $rowNum, $slogText);
        $sheet->mergeCells('A' . $rowNum . ':I' . $rowNum);
        $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $rowNum++;

        $startTableRow = $rowNum;

        $sheet->setCellValue('A' . $rowNum, 'No.');
        $sheet->setCellValue('B' . $rowNum, 'Mahasiswa');
        $sheet->setCellValue('C' . $rowNum, 'Catatan');
        $sheet->setCellValue('D' . $rowNum, 'Tanggal Buat');
        $sheet->setCellValue('E' . $rowNum, 'Tanggal Reminder');
        $sheet->setCellValue('F' . $rowNum, 'Tanggal Akan Bayar');
        $sheet->setCellValue('G' . $rowNum, 'Sisa Tagihan');
        $sheet->setCellValue('H' . $rowNum, "Catatan\nAdmin");
        $sheet->setCellValue('I' . $rowNum, 'Status');

        $sheet->getStyle('A' . $rowNum . ':I' . $rowNum)->getFont()->setBold(true);
        $sheet->getStyle('A' . $rowNum . ':I' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $rowNum . ':I' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A' . $rowNum . ':I' . $rowNum)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC000');

        $sheet->getStyle('H' . $rowNum)->getAlignment()->setWrapText(true);

        $rowNum++;

        $no = 1;
        if (!empty($queryData)) {
            foreach ($queryData as $row) {
                $sheet->setCellValue('A' . $rowNum, $no++);

                $objRichText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
                $npmBold = $objRichText->createTextRun(trim($row->NPM ?? '') . "\n");
                $npmBold->getFont()->setBold(true);
                $objRichText->createText(trim($row->Nama ?? ''));
                $sheet->setCellValue('B' . $rowNum, $objRichText);

                $sheet->setCellValue('C' . $rowNum, $row->Catatan ?? '');

                $sheet->setCellValue('D' . $rowNum, tgl($row->TanggalBuat, '02'));
                $sheet->setCellValue('E' . $rowNum, tgl($row->TanggalReminder, '02'));
                $sheet->setCellValue('F' . $rowNum, tgl($row->TanggalAkanBayar, '02'));

                $sisa = intval(($row->TotalJumlah ?? 0) - ($row->TotalBayar ?? 0));
                $sheet->setCellValue('G' . $rowNum, $sisa);
                $sheet->getStyle('G' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0');

                $sheet->setCellValue('H' . $rowNum, $row->CatatanAdmin ?? '');

                $statusText = ($row->SetKRSYa == 1) ? 'Sudah Bisa KRS' : 'Tidak Bisa KRS';
                $sheet->setCellValue('I' . $rowNum, $statusText);

                $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D' . $rowNum . ':F' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('G' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('I' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle('A' . $rowNum . ':I' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $sheet->getStyle('B' . $rowNum)->getAlignment()->setWrapText(true);
                $sheet->getStyle('C' . $rowNum)->getAlignment()->setWrapText(true);
                $sheet->getStyle('H' . $rowNum)->getAlignment()->setWrapText(true);

                $rowNum++;
            }
        } else {
            $sheet->setCellValue('A' . $rowNum, 'Tidak ada data pengajuan perjanjian');
            $sheet->mergeCells('A' . $rowNum . ':I' . $rowNum);
            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $rowNum++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $startTableRow . ':I' . ($rowNum - 1))->applyFromArray($styleBorder);

        if (!function_exists('cetak_kop_phpspreadsheet')) {
            $sheet->getColumnDimension('A')->setAutoSize(true);
        }

        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('H')->setWidth(30);

        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "data_lihat_catatan_krs_tidak_aktif_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $objWriter->save('php://output');
        exit;
    }
}
