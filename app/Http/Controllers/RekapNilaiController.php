<?php

namespace App\Http\Controllers;

use App\Services\RekapNilaiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RekapNilaiController extends Controller
{
    protected $service;

    public function __construct(RekapNilaiService $service)
    {
        $this->service = $service;

        $this->middleware(function ($request, $next) {
            if (!Session::get('username')) {
                return redirect('/');
            }
            return $next($request);
        });
    }

    /**
     * Display main view
     */
    public function index()
    {
        log_akses('View', 'Melihat Daftar Data Nilai');
        return view('rekap_nilai.v_rekap_nilai');
    }

    /**
     * CI3 compatibility alias
     */
    public function RekapNilai()
    {
        return $this->index();
    }

    /**
     * Search data
     */
    public function search(Request $request)
    {
        try {
            $filters = $request->only([
                'TahunMasuk',
                'TahunID',
                'ProdiID',
                'ProgramID',
                'KelasID',
                'SemesterMasuk',
                'Semester',
                'keyword',
            ]);

            $result = $this->service->searchData($filters);

            return view('rekap_nilai.s_rekap_nilai', $result);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage()
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
            'ProdiID',
            'ProgramID',
            'KelasID',
            'SemesterMasuk',
            'Semester',
            'keyword',
        ]);

        $result = $this->service->searchData($filters);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Nilai');

        $rowNum = 1;

        if (function_exists('cetak_kop_phpspreadsheet')) {
            $rowNum = cetak_kop_phpspreadsheet($sheet, 'Z');
        }

        $sheet->setCellValue('A' . $rowNum, 'REKAP NILAI MAHASISWA');
        $sheet->mergeCells('A' . $rowNum . ':Z' . $rowNum);
        $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $rowNum++;
        $rowNum++;

        $mahasiswa = $result['mahasiswa'] ?? [];
        $matakuliah = $result['matakuliah'] ?? [];
        $nilaiMatkul = $result['nilai_matkul'] ?? [];
        $bobotMatkul = $result['bobot_matkul'] ?? [];

        if (empty($mahasiswa) || empty($matakuliah)) {
            $sheet->setCellValue('A' . $rowNum, 'Tidak ada data');
            $sheet->mergeCells('A' . $rowNum . ':Z' . $rowNum);
            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        } else {
            // Header: No, NPM, Nama, [Mata Kuliah columns...]
            $col = 'A';
            $sheet->setCellValue($col . $rowNum, 'No.');
            $sheet->getColumnDimension($col)->setWidth(5);
            $col++;

            $sheet->setCellValue($col . $rowNum, 'NPM');
            $sheet->getColumnDimension($col)->setWidth(15);
            $col++;

            $sheet->setCellValue($col . $rowNum, 'Nama');
            $sheet->getColumnDimension($col)->setWidth(30);
            $col++;

            // Mata kuliah columns
            foreach ($matakuliah as $mk) {
                $sheet->setCellValue($col . $rowNum, $mk['MKKode'] . "\n" . $mk['Nama']);
                $sheet->getColumnDimension($col)->setWidth(20);
                $sheet->getStyle($col . $rowNum)->getAlignment()->setWrapText(true);
                $col++;
            }

            // Total SKS column
            $sheet->setCellValue($col . $rowNum, 'Total SKS');
            $sheet->getColumnDimension($col)->setWidth(12);

            $headerStartRow = $rowNum;
            $rowNum++;

            // Data rows
            $no = 1;
            foreach ($mahasiswa as $mhswID => $mhs) {
                $col = 'A';
                $sheet->setCellValue($col . $rowNum, $no++);
                $col++;

                $sheet->setCellValue($col . $rowNum, $mhs['NPM'] ?? '');
                $col++;

                $sheet->setCellValue($col . $rowNum, $mhs['Nama'] ?? '');
                $col++;

                // Nilai per mata kuliah
                foreach ($matakuliah as $mkID => $mk) {
                    $nilai = $nilaiMatkul[$mhswID][$mkID]['NilaiHuruf'] ?? '';
                    $sheet->setCellValue($col . $rowNum, $nilai);
                    $col++;
                }

                // Total SKS
                $sheet->setCellValue($col . $rowNum, $mhs['TotalSKS'] ?? 0);
                $col++;

                $rowNum++;
            }

            // Apply border style
            $lastCol = $col;
            $sheet->getStyle('A' . $headerStartRow . ':' . $lastCol . $rowNum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            // Header styling
            $sheet->getStyle('A' . $headerStartRow . ':' . $lastCol . $headerStartRow)->getFont()->setBold(true);
            $sheet->getStyle('A' . $headerStartRow . ':' . $lastCol . $headerStartRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A' . $headerStartRow . ':' . $lastCol . $headerStartRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A' . $headerStartRow . ':' . $lastCol . $headerStartRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC000');
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "RekapNilai_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
