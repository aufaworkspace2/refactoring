<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RekapitulasiPendaftaranPmbService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Barryvdh\DomPDF\Facade\Pdf;

class RekapitulasiPendaftaranPmbController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(RekapitulasiPendaftaranPmbService $service)
    {
        $this->service = $service;

        $this->middleware(function ($request, $next) {
            if (!Session::has('username')) {
                return redirect('/');
            }

            // Language setup
            $lang = Session::get('language');
            if (!$lang && !request()->cookie('language')) {
                $lang = 'indonesia';
                Session::put('language', $lang);
            }

            // Map legacy language names to Laravel locales
            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);

            $levelUser = Session::get('LevelUser');

            $this->Create = cek_level($levelUser, 'c_rekapitulasi_pendaftaran_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_rekapitulasi_pendaftaran_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_rekapitulasi_pendaftaran_pmb', 'Delete');

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Rekapitulasi Pendaftaran');
        }

        $data['Create'] = $this->Create;

        return view('rekapitulasi_pendaftaran.v_rekapitulasi_pendaftaran', $data);
    }

    public function search(Request $request)
    {
        $gelombang = $request->input('gelombang', '');
        $gelombang_detail = $request->input('gelombang_detail', '');
        $ujian = $request->input('ujian', '');
        $program = $request->input('program', '');
        $ikut_ujian = $request->input('ikut_ujian', '');
        $tgl1 = $request->input('tgl1', '');
        $tgl2 = $request->input('tgl2', '');
        $status = $request->input('status', '');

        $prodi = [];
        $rowprodi = [];
        $arrProdi = [];

        // Get data with all filters applied
        $query = $this->service->get_data($gelombang, $gelombang_detail, $ujian, $program, $ikut_ujian, $tgl1, $tgl2, $status);

        if (!empty($query)) {
            foreach ($query as $row) {
                $prodi[] = $row;
                if (!isset($rowprodi[$row['pilihan1']])) {
                    $rowprodi[$row['pilihan1']] = 0;
                }
                $rowprodi[$row['pilihan1']] += 1;
            }
        }

        $data['rowprodi'] = $rowprodi;
        $data['query'] = $prodi;
        $data['arrProdi'] = $arrProdi;
        $data['offset'] = 0;
        $data['total_row'] = count($prodi);

        return view('rekapitulasi_pendaftaran.s_rekapitulasi_pendaftaran', $data);
    }

    public function pdf(Request $request)
    {
        $gelombang = $request->input('gelombang', '');
        $gelombang_detail = $request->input('gelombang_detail', '');
        $ujian = $request->input('ujian', '');
        $program = $request->input('program', '');
        $ikut_ujian = $request->input('ikut_ujian', '');
        $tgl1 = $request->input('tgl1', '');
        $tgl2 = $request->input('tgl2', '');
        $status = $request->input('status', '');

        $prodi = [];
        $rowprodi = [];
        $arrProdi = [];

        // Get data with all filters applied
        $query = $this->service->get_data($gelombang, $gelombang_detail, $ujian, $program, $ikut_ujian, $tgl1, $tgl2, $status);

        if (!empty($query)) {
            foreach ($query as $row) {
                $prodi[] = $row;
                if (!isset($rowprodi[$row['pilihan1']])) {
                    $rowprodi[$row['pilihan1']] = 0;
                }
                $rowprodi[$row['pilihan1']] += 1;
            }
        }

        $data['rowprodi'] = $rowprodi;
        $data['query'] = $prodi;
        $data['arrProdi'] = $arrProdi;
        $data['offset'] = 0;

        $pdf = Pdf::loadView('rekapitulasi_pendaftaran.p_rekapitulasi_pendaftaran', $data);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Rekapitulasi_Pendaftaran_' . date('Y-m-d') . '.pdf');
    }

    public function excel(Request $request)
    {
        $gelombang = $request->input('gelombang', '');
        $gelombang_detail = $request->input('gelombang_detail', '');
        $ujian = $request->input('ujian', '');
        $program = $request->input('program', '');
        $ikut_ujian = $request->input('ikut_ujian', '');
        $tgl1 = $request->input('tgl1', '');
        $tgl2 = $request->input('tgl2', '');
        $status = $request->input('status', '');

        $prodi = [];
        $rowprodi = [];

        // Get data with all filters applied
        $query = $this->service->get_data($gelombang, $gelombang_detail, $ujian, $program, $ikut_ujian, $tgl1, $tgl2, $status);

        if (!empty($query)) {
            foreach ($query as $row) {
                $prodi[] = $row;
                if (!isset($rowprodi[$row['pilihan1']])) {
                    $rowprodi[$row['pilihan1']] = 0;
                }
                $rowprodi[$row['pilihan1']] += 1;
            }
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekapitulasi Pendaftaran');

        $row_num = 1;

        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'D');
        }

        $sheet->setCellValue('A' . $row_num, 'REKAPITULASI PENDAFTARAN');
        $sheet->mergeCells('A' . $row_num . ':E' . $row_num);
        $sheet->getStyle('A' . $row_num)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row_num += 2;

        $start_table_row = $row_num;

        $sheet->setCellValue('A' . $row_num, 'No.');
        $sheet->setCellValue('B' . $row_num, 'Program Studi');
        $sheet->setCellValue('C' . $row_num, 'Program');
        $sheet->setCellValue('D' . $row_num, 'Jalur Pendaftaran');
        $sheet->setCellValue('E' . $row_num, 'Jumlah Peserta');

        $sheet->getStyle('A' . $row_num . ':E' . $row_num)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row_num . ':E' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row_num . ':E' . $row_num)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC000');
        $row_num++;

        $arr_prodi = [];
        $arrProdiNama = [];
        $no = 0;
        $total_peserta = 0;

        if (!empty($prodi)) {
            foreach ($prodi as $row) {
                $total_peserta += $row['jumlah'] ?? 0;

                $sheet->setCellValue('C' . $row_num, get_field($row['ProgramID'] ?? '', 'program'));
                $sheet->setCellValue('D' . $row_num, get_field($row['jalur_pmb'] ?? '', 'pmb_edu_jalur_pendaftaran', 'nama'));
                $sheet->setCellValue('E' . $row_num, $row['jumlah'] ?? 0);

                if (!in_array($row['pilihan1'], $arr_prodi)) {
                    $no++;
                    $jml_span = $rowprodi[$row['pilihan1']];

                    $nama_prodi_lengkap = '';
                    if ($row['pilihan1'] ?? '') {
                        $prodi_ids = explode(",", $row['pilihan1']);
                        $temp_names = [];
                        foreach ($prodi_ids as $val) {
                            $getprodi = get_id($val, 'programstudi');
                            if ($getprodi) {
                                $temp_names[] = get_field($getprodi->JenjangID ?? '', "jenjang") . " " . ($getprodi->Nama ?? '');
                            }
                        }
                        $nama_prodi_lengkap = implode("\n", $temp_names);
                    }

                    $sheet->setCellValue('A' . $row_num, $no);
                    $sheet->setCellValue('B' . $row_num, $nama_prodi_lengkap);

                    $sheet->getStyle('B' . $row_num)->getAlignment()->setWrapText(true);
                    $sheet->getStyle('A' . $row_num)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                    $sheet->getStyle('B' . $row_num)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

                    if ($jml_span > 1) {
                        $endRow = $row_num + $jml_span - 1;
                        $sheet->mergeCells("A{$row_num}:A{$endRow}");
                        $sheet->mergeCells("B{$row_num}:B{$endRow}");
                    }

                    $arr_prodi[] = $row['pilihan1'];
                }

                $row_num++;
            }

            $sheet->setCellValue('A' . $row_num, 'Total');
            $sheet->mergeCells("A{$row_num}:D{$row_num}");
            $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("A{$row_num}:E{$row_num}")->getFont()->setBold(true);
            $sheet->setCellValue('E' . $row_num, $total_peserta);
            $sheet->getStyle('E' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row_num++;

        } else {
            $sheet->setCellValue('A' . $row_num, 'Tidak ada data');
            $sheet->mergeCells('A' . $row_num . ':E' . $row_num);
            $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row_num++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $start_table_row . ':E' . ($row_num - 1))->applyFromArray($styleBorder);

        foreach (range('A', 'E') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "data_rekapitulasi_pendaftaran_pmb_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = new Xlsx($spreadsheet);
        $objWriter->save('php://output');
        exit;
    }
}
