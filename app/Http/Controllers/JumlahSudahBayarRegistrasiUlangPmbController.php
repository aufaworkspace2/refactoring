<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JumlahSudahBayarRegistrasiUlangPmbService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class JumlahSudahBayarRegistrasiUlangPmbController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(JumlahSudahBayarRegistrasiUlangPmbService $service)
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

            $this->Create = cek_level($levelUser, 'c_jumlah_sudah_bayar_registrasi_ulang_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_jumlah_sudah_bayar_registrasi_ulang_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_jumlah_sudah_bayar_registrasi_ulang_pmb', 'Delete');

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Modul Jumlah Sudah Bayar Registrasi Ulang');
        }

        $data['TahunMasuk'] = $this->service->get_tahun_masuk();
        $data['Create'] = $this->Create;

        return view('jumlah_sudah_bayar_registrasi_ulang_pmb.v_jumlah_sudah_bayar_registrasi_ulang_pmb', $data);
    }

    public function search(Request $request)
    {
        $gelombang = $request->input('gelombang', '');
        $gelombang_detail = $request->input('gelombang_detail', '');
        $programID = $request->input('ProgramID', '');
        $pilihan1 = $request->input('pilihan1', '');
        $tahunMasuk = $request->input('TahunMasuk', '');
        $statusbayar_registrasi_pmb = $request->input('statusbayar_registrasi_pmb', '');
        $sekolahID = $request->input('SekolahID', '');
        $keyword = $request->input('keyword', '');

        $query = $this->service->get_data($gelombang, $gelombang_detail, $programID, $pilihan1, $tahunMasuk, $statusbayar_registrasi_pmb, $sekolahID);

        $rowprodi = [];
        $TotalJumlahSudahBayar = 0;
        $arr_no = [];
        $arr_prodi = [];

        $no = 0;
        foreach ($query as $row) {
            if (empty($rowprodi[$row['prodiID']])) {
                $arr_no[$row['prodiID']] = ++$no;
            }
            if (!isset($rowprodi[$row['prodiID']])) {
                $rowprodi[$row['prodiID']] = 0;
            }
            $rowprodi[$row['prodiID']] += 1;
            $TotalJumlahSudahBayar += $row['JumlahSudahBayar'];
        }

        $data['rowprodi'] = $rowprodi;
        $data['query'] = $query;
        $data['TotalJumlahSudahBayar'] = $TotalJumlahSudahBayar;
        $data['arr_no'] = $arr_no;
        $data['arr_prodi'] = $arr_prodi;
        $data['total_row'] = count($query);

        return view('jumlah_sudah_bayar_registrasi_ulang_pmb.s_jumlah_sudah_bayar_registrasi_ulang_pmb', $data);
    }

    public function pdf(Request $request)
    {
        $gelombang = $request->input('gelombang', '');
        $gelombang_detail = $request->input('gelombang_detail', '');
        $programID = $request->input('ProgramID', '');
        $pilihan1 = $request->input('pilihan1', '');
        $tahunMasuk = $request->input('TahunMasuk', '');
        $statusbayar_registrasi_pmb = $request->input('statusbayar_registrasi_pmb', '');
        $sekolahID = $request->input('SekolahID', '');
        $keyword = $request->input('keyword', '');

        $query = $this->service->get_data($gelombang, $gelombang_detail, $programID, $pilihan1, $tahunMasuk, $statusbayar_registrasi_pmb, $sekolahID);

        $rowprodi = [];
        $TotalJumlahSudahBayar = 0;
        $arr_no = [];

        $no = 0;
        foreach ($query as $row) {
            if (empty($rowprodi[$row['prodiID']])) {
                $arr_no[$row['prodiID']] = ++$no;
            }
            if (!isset($rowprodi[$row['prodiID']])) {
                $rowprodi[$row['prodiID']] = 0;
            }
            $rowprodi[$row['prodiID']] += 1;
            $TotalJumlahSudahBayar += $row['JumlahSudahBayar'];
        }

        $data['rowprodi'] = $rowprodi;
        $data['query'] = $query;
        $data['TotalJumlahSudahBayar'] = $TotalJumlahSudahBayar;
        $data['arr_no'] = $arr_no;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('jumlah_sudah_bayar_registrasi_ulang_pmb.p_jumlah_sudah_bayar_registrasi_ulang_pmb', $data);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Daftar_Sudah_Bayar_Registrasi_' . date('Y-m-d') . '.pdf');
    }

    public function excel(Request $request)
    {
        $gelombang = $request->input('gelombang', '');
        $gelombang_detail = $request->input('gelombang_detail', '');
        $programID = $request->input('ProgramID', '');
        $pilihan1 = $request->input('pilihan1', '');
        $tahunMasuk = $request->input('TahunMasuk', '');
        $statusbayar_registrasi_pmb = $request->input('statusbayar_registrasi_pmb', '');
        $sekolahID = $request->input('SekolahID', '');
        $keyword = $request->input('keyword', '');

        $query = $this->service->get_data($gelombang, $gelombang_detail, $programID, $pilihan1, $tahunMasuk, $statusbayar_registrasi_pmb, $sekolahID);

        $rowprodi = [];
        $TotalJumlahSudahBayar = 0;
        $arr_no = [];

        $no = 0;
        foreach ($query as $row) {
            if (empty($rowprodi[$row['prodiID']])) {
                $arr_no[$row['prodiID']] = ++$no;
            }
            if (!isset($rowprodi[$row['prodiID']])) {
                $rowprodi[$row['prodiID']] = 0;
            }
            $rowprodi[$row['prodiID']] += 1;
            $TotalJumlahSudahBayar += $row['JumlahSudahBayar'];
        }

        $data['rowprodi'] = $rowprodi;
        $data['query'] = $query;
        $data['TotalJumlahSudahBayar'] = $TotalJumlahSudahBayar;
        $data['arr_no'] = $arr_no;

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Registrasi Ulang');

        $row_num = 1;

        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'D');
        }

        $sheet->setCellValue('A' . $row_num, 'REKAPITULASI JUMLAH SUDAH BAYAR REGISTRASI ULANG');
        $sheet->mergeCells('A' . $row_num . ':D' . $row_num);
        $sheet->getStyle('A' . $row_num)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row_num += 2;

        $start_table_row = $row_num;

        $sheet->setCellValue('A' . $row_num, 'No.');
        $sheet->setCellValue('B' . $row_num, 'Program Studi Pilihan 1');
        $sheet->setCellValue('C' . $row_num, 'Program');
        $sheet->setCellValue('D' . $row_num, 'Jumlah Sudah Bayar Registrasi Ulang');

        $sheet->getStyle('A' . $row_num . ':D' . $row_num)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row_num . ':D' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row_num . ':D' . $row_num)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC000');
        $row_num++;

        $arr_prodi = [];
        if (!empty($query)) {
            foreach ($query as $row) {
                if (!in_array($row['prodiID'], $arr_prodi)) {
                    $span = $rowprodi[$row['prodiID']];
                    if ($span > 1) {
                        $endRow = $row_num + $span - 1;
                        $sheet->mergeCells("A{$row_num}:A{$endRow}");
                        $sheet->mergeCells("B{$row_num}:B{$endRow}");
                    }
                    $sheet->setCellValue('A' . $row_num, $arr_no[$row['prodiID']] . '.');
                    $sheet->setCellValue('B' . $row_num, $row['prodiNama']);

                    $sheet->getStyle("A{$row_num}:B" . ($row_num + $span - 1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("A{$row_num}:A" . ($row_num + $span - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $arr_prodi[] = $row['prodiID'];
                }

                $sheet->setCellValue('C' . $row_num, $row['programNama']);
                $sheet->setCellValue('D' . $row_num, $row['JumlahSudahBayar']);
                $sheet->getStyle('D' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $row_num++;
            }

            $sheet->setCellValue('A' . $row_num, 'Total');
            $sheet->mergeCells("A{$row_num}:C{$row_num}");
            $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("A{$row_num}:D{$row_num}")->getFont()->setBold(true);
            $sheet->setCellValue('D' . $row_num, $TotalJumlahSudahBayar);
            $sheet->getStyle('D' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row_num++;

        } else {
            $sheet->setCellValue('A' . $row_num, 'Tidak ada data');
            $sheet->mergeCells('A' . $row_num . ':D' . $row_num);
            $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row_num++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $start_table_row . ':D' . ($row_num - 1))->applyFromArray($styleBorder);

        foreach (range('A', 'D') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "data_jumlah_sudah_bayar_registrasi_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = new Xlsx($spreadsheet);
        $objWriter->save('php://output');
        exit;
    }
}
