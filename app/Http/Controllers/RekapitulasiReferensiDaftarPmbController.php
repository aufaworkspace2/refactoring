<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RekapitulasiReferensiDaftarPmbService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Barryvdh\DomPDF\Facade\Pdf;

class RekapitulasiReferensiDaftarPmbController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(RekapitulasiReferensiDaftarPmbService $service)
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

            $this->Create = cek_level($levelUser, 'c_rekapitulasi_referensi_daftar_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_rekapitulasi_referensi_daftar_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_rekapitulasi_referensi_daftar_pmb', 'Delete');

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

        return view('rekapitulasi_referensi_daftar_pmb.v_rekapitulasi_referensi_daftar_pmb', $data);
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

        // Get data with all filters applied
        $query = $this->service->get_data($gelombang, $gelombang_detail, $programID, $pilihan1, $tahunMasuk, $statusbayar_registrasi_pmb, $sekolahID);

        // Get all referensi
        $list_referensi = $this->service->get_all_referensi();

        $jum_ref = [];
        $TotalJumlahPendaftar = 0;

        foreach ($query as $row) {
            if (!isset($jum_ref[$row['ref_daftar']])) {
                $jum_ref[$row['ref_daftar']] = 0;
            }
            $jum_ref[$row['ref_daftar']] += $row['JumlahPendaftar'];
            $TotalJumlahPendaftar += $row['JumlahPendaftar'];
        }

        $result_query = [];
        $arr_no = [];
        $no = 0;

        foreach ($list_referensi as $lr) {
            $arr_no[$lr['id_ref_daftar']] = ++$no;
            $lr['JumlahPendaftar'] = isset($jum_ref[$lr['id_ref_daftar']]) ? (int) $jum_ref[$lr['id_ref_daftar']] : 0;
            $result_query[] = $lr;
        }

        $jumlah_tidak_ada_referensi = isset($jum_ref[""]) ? (int) $jum_ref[""] : 0;

        $data['query'] = $result_query;
        $data['TotalJumlahPendaftar'] = $TotalJumlahPendaftar;
        $data['arr_no'] = $arr_no;
        $data['jumlah_tidak_ada_referensi'] = $jumlah_tidak_ada_referensi;
        $data['total_row'] = count($result_query);

        return view('rekapitulasi_referensi_daftar_pmb.s_rekapitulasi_referensi_daftar_pmb', $data);
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

        // Get data with all filters applied
        $query = $this->service->get_data($gelombang, $gelombang_detail, $programID, $pilihan1, $tahunMasuk, $statusbayar_registrasi_pmb, $sekolahID);

        // Get all referensi
        $list_referensi = $this->service->get_all_referensi();

        $jum_ref = [];
        $TotalJumlahPendaftar = 0;

        foreach ($query as $row) {
            if (!isset($jum_ref[$row['ref_daftar']])) {
                $jum_ref[$row['ref_daftar']] = 0;
            }
            $jum_ref[$row['ref_daftar']] += $row['JumlahPendaftar'];
            $TotalJumlahPendaftar += $row['JumlahPendaftar'];
        }

        $result_query = [];
        $arr_no = [];
        $no = 0;

        foreach ($list_referensi as $lr) {
            $arr_no[$lr['id_ref_daftar']] = ++$no;
            $lr['JumlahPendaftar'] = isset($jum_ref[$lr['id_ref_daftar']]) ? (int) $jum_ref[$lr['id_ref_daftar']] : 0;
            $result_query[] = $lr;
        }

        $jumlah_tidak_ada_referensi = isset($jum_ref[""]) ? (int) $jum_ref[""] : 0;

        $data['query'] = $result_query;
        $data['TotalJumlahPendaftar'] = $TotalJumlahPendaftar;
        $data['arr_no'] = $arr_no;
        $data['jumlah_tidak_ada_referensi'] = $jumlah_tidak_ada_referensi;

        $pdf = Pdf::loadView('rekapitulasi_referensi_daftar_pmb.p_rekapitulasi_referensi_daftar_pmb', $data);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Daftar_Referensi_Daftar_' . date('Y-m-d') . '.pdf');
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

        // Get data with all filters applied
        $query = $this->service->get_data($gelombang, $gelombang_detail, $programID, $pilihan1, $tahunMasuk, $statusbayar_registrasi_pmb, $sekolahID);

        // Get all referensi
        $list_referensi = $this->service->get_all_referensi();

        $jum_ref = [];
        $TotalJumlahPendaftar = 0;

        foreach ($query as $row) {
            if (!isset($jum_ref[$row['ref_daftar']])) {
                $jum_ref[$row['ref_daftar']] = 0;
            }
            $jum_ref[$row['ref_daftar']] += $row['JumlahPendaftar'];
            $TotalJumlahPendaftar += $row['JumlahPendaftar'];
        }

        $result_query = [];
        $arr_no = [];
        $no = 0;

        foreach ($list_referensi as $lr) {
            $arr_no[$lr['id_ref_daftar']] = ++$no;
            $lr['JumlahPendaftar'] = isset($jum_ref[$lr['id_ref_daftar']]) ? (int) $jum_ref[$lr['id_ref_daftar']] : 0;
            $result_query[] = $lr;
        }

        $jumlah_tidak_ada_referensi = isset($jum_ref[""]) ? (int) $jum_ref[""] : 0;

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Referensi Daftar');

        $row_num = 1;

        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'C');
        }

        $sheet->setCellValue('A' . $row_num, 'REKAPITULASI REFERENSI DAFTAR');
        $sheet->mergeCells('A' . $row_num . ':C' . $row_num);
        $sheet->getStyle('A' . $row_num)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row_num += 2;

        $start_table_row = $row_num;

        $sheet->setCellValue('A' . $row_num, 'No.');
        $sheet->setCellValue('B' . $row_num, 'Referensi');
        $sheet->setCellValue('C' . $row_num, 'Jumlah Mahasiswa');

        $sheet->getStyle('A' . $row_num . ':C' . $row_num)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row_num . ':C' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row_num . ':C' . $row_num)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC000');
        $row_num++;

        if (!empty($result_query)) {
            foreach ($result_query as $row) {
                $sheet->setCellValue('A' . $row_num, $arr_no[$row['id_ref_daftar']] . '.');
                $sheet->setCellValue('B' . $row_num, $row['nama_ref'] ?? '');
                $sheet->setCellValue('C' . $row_num, $row['JumlahPendaftar'] ?? 0);

                $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $row_num++;
            }

            if ($jumlah_tidak_ada_referensi > 0) {
                $sheet->setCellValue('A' . $row_num, ($no + 1) . '.');
                $sheet->setCellValue('B' . $row_num, 'Tidak Diisi');
                $sheet->setCellValue('C' . $row_num, $jumlah_tidak_ada_referensi);

                $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row_num++;
            }

            $sheet->setCellValue('A' . $row_num, 'Total');
            $sheet->mergeCells("A{$row_num}:B{$row_num}");
            $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("A{$row_num}:C{$row_num}")->getFont()->setBold(true);
            $sheet->setCellValue('C' . $row_num, $TotalJumlahPendaftar);
            $sheet->getStyle('C' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row_num++;

        } else {
            $sheet->setCellValue('A' . $row_num, 'Tidak ada data');
            $sheet->mergeCells('A' . $row_num . ':C' . $row_num);
            $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row_num++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $start_table_row . ':C' . ($row_num - 1))->applyFromArray($styleBorder);

        foreach (range('A', 'C') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "rekapitulasi_ref_daftar_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = new Xlsx($spreadsheet);
        $objWriter->save('php://output');
        exit;
    }
}
