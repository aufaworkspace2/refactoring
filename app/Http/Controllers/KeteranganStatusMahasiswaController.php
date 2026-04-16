<?php

namespace App\Http\Controllers;

use App\Services\KeteranganStatusMahasiswaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class KeteranganStatusMahasiswaController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(KeteranganStatusMahasiswaService $service)
    {
        $this->service = $service;

        $this->middleware(function ($request, $next) {
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

            $this->Create = cek_level($levelUser, 'c_keterangan_status_mahasiswa', 'Create');
            $this->Update = cek_level($levelUser, 'c_keterangan_status_mahasiswa', 'Update');
            $this->Delete = cek_level($levelUser, 'c_keterangan_status_mahasiswa', 'Delete');

            return $next($request);
        });
    }

    /**
     * Main index page
     * Legacy: c_keterangan_status_mahasiswa->index()
     */
    public function index(Request $request)
    {
        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Setup Keterangan Status Mahasiswa');
        }

        $data['Create'] = $this->Create;
        $data['programs'] = DB::table('program')->get();
        $data['tahuns'] = DB::table('tahun')->orderBy('ID', 'DESC')->get();
        $data['prodis'] = DB::table('programstudi')->get();
        $data['statuses'] = DB::table('statusmahasiswa')->whereNotIn('KodeDikti', ['A'])->get();
        
        $tahunMasukQuery = DB::table('mahasiswa')
            ->select('TahunMasuk')
            ->whereNotNull('TahunMasuk')
            ->groupBy('TahunMasuk')
            ->orderBy('TahunMasuk', 'ASC')
            ->get();
        $data['tahunMasuk'] = $tahunMasukQuery;

        return view('keterangan_status_mahasiswa.index', $data);
    }

    /**
     * AJAX search results
     * Legacy: c_keterangan_status_mahasiswa->search()
     */
    public function search(Request $request, $offset = 0)
    {
        $ProdiID = $request->input('ProdiID', '');
        $TahunID = $request->input('TahunID', '');
        $StatusMhswID = $request->input('StatusMhswID', '');
        $keyword = $request->input('keyword', '');
        $TahunMasuk = $request->input('TahunMasuk', '');
        $ProgramID = $request->input('ProgramID', '');

        $limit = 10;
        $jml = $this->service->count_all($ProdiID, $TahunID, $StatusMhswID, $keyword, $TahunMasuk, $ProgramID);
        
        $data['query'] = $this->service->get_data($limit, $offset, $ProdiID, $TahunID, $StatusMhswID, $keyword, $TahunMasuk, $ProgramID);
        $data['offset'] = $offset;
        $data['StatusMhswID'] = $StatusMhswID;
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);

        return view('keterangan_status_mahasiswa.search', $data);
    }

    /**
     * AJAX: Get student options
     * Legacy: c_keterangan_status_mahasiswa->changemhsw()
     */
    public function changemhsw(Request $request)
    {
        $ProgramID = $request->input('ProgramID');
        $ProdiID = $request->input('ProdiID');
        $ID = $request->input('ID');

        $options = $this->service->get_mahasiswa_options($ProgramID, $ProdiID, $ID);

        $html = '<option value="" selected>Pilih data</option>';
        foreach ($options as $row) {
            $s = $row['Selected'] ? 'selected' : '';
            $html .= "<option value='{$row['ID']}' $s>{$row['NPM']} || {$row['Nama']} || {$row['Status']}</option>";
        }
        return response($html);
    }

    /**
     * AJAX: Get status options based on student
     * Legacy: c_keterangan_status_mahasiswa->changestatus()
     */
    public function changestatus(Request $request)
    {
        $MhswID = $request->input('MhswID');
        $stat = DB::table('mahasiswa')->where('ID', $MhswID)->value('StatusMhswID');
        
        $get_status_mhsw = DB::table('statusmahasiswa')->get();
        $html = '';
        foreach ($get_status_mhsw as $row) {
            $selected = ($row->ID == $stat) ? "selected" : "";
            $html .= "<option value='$row->ID' $selected> $row->Nama</option>";
        }
        return response($html);
    }

    /**
     * Add form
     * Legacy: c_keterangan_status_mahasiswa->add()
     */
    public function add()
    {
        $data['save'] = 1;
        $data['programs'] = DB::table('program')->get();
        return view('keterangan_status_mahasiswa.form', $data);
    }

    /**
     * View/Edit form
     * Legacy: c_keterangan_status_mahasiswa->view()
     */
    public function view($id)
    {
        $row = DB::table('keterangan_status_mahasiswa')->where('ID', $id)->first();
        if (!$row) return redirect()->back()->with('error', 'Data tidak ditemukan');

        $row->Nama = get_field($row->MhswID, 'mahasiswa');
        $row->ProdiID = get_field($row->MhswID, 'mahasiswa', 'ProdiID');
        $row->ProgramID = get_field($row->MhswID, 'mahasiswa', 'ProgramID');

        $data['row'] = $row;
        $data['save'] = 2;
        $data['programs'] = DB::table('program')->get();
        return view('keterangan_status_mahasiswa.form', $data);
    }

    /**
     * Save/Update record
     * Legacy: c_keterangan_status_mahasiswa->save()
     */
    public function save(Request $request, $save_type)
    {
        $data = $request->all();
        $result = $this->service->save($data, $save_type);
        return response($result);
    }

    /**
     * Delete records
     * Legacy: c_keterangan_status_mahasiswa->delete()
     */
    public function delete(Request $request)
    {
        $checkid = $request->input('checkID');
        $this->service->delete($checkid);
        return response('Success');
    }

    /**
     * Export PDF
     * Legacy: c_keterangan_status_mahasiswa->pdf()
     */
    public function pdf(Request $request, $offset = 0)
    {
        $ProdiID = $request->input('ProdiID', '');
        $TahunID = $request->input('TahunID', '');
        $StatusMhswID = $request->input('StatusMhswID', '');
        $keyword = $request->input('keyword', '');
        $TahunMasuk = $request->input('TahunMasuk', '');
        $ProgramID = $request->input('ProgramID', '');

        $limit = 1000;
        $data['StatusMhswID'] = $StatusMhswID;
        $data['offset'] = $offset;
        $data['query'] = $this->service->get_data($limit, $offset, $ProdiID, $TahunID, $StatusMhswID, $keyword, $TahunMasuk, $ProgramID);
        
        $pdf = Pdf::loadView('keterangan_status_mahasiswa.pdf', $data);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('keterangan_status_mahasiswa.pdf');
    }

    /**
     * Export Excel using PhpSpreadsheet
     * Legacy: c_keterangan_status_mahasiswa->excel()
     */
    public function excel(Request $request)
    {
        $offset = $request->input('offset', 0);
        $ProdiID = $request->input('ProdiID', '');
        $TahunID = $request->input('TahunID', '');
        $StatusMhswID = $request->input('StatusMhswID', '');
        $keyword = $request->input('keyword', '');
        $TahunMasuk = $request->input('TahunMasuk', '');
        $ProgramID = $request->input('ProgramID', '');
        $limit = 1000;

        $query = $this->service->get_data($limit, $offset, $ProdiID, $TahunID, $StatusMhswID, $keyword, $TahunMasuk, $ProgramID);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Status Mahasiswa');

        $row_num = 1;
        // KOP can be added here if helper exists

        $sheet->setCellValue('A' . $row_num, 'DAFTAR STATUS MAHASISWA');
        $max_col = ($StatusMhswID == 2 ? 'I' : 'G');
        $sheet->mergeCells("A$row_num:{$max_col}$row_num");
        $sheet->getStyle('A' . $row_num)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row_num += 2;

        $start_table_row = $row_num;
        $headers = ['No.', 'Mahasiswa', 'Status', 'Nomor Surat'];
        if ($StatusMhswID == 2) {
            $headers[] = 'Mulai Semester';
            $headers[] = 'Akhir Semester';
        }
        $headers[] = 'Alasan';
        $headers[] = 'Tahun Semester';
        $headers[] = 'Tanggal';

        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . $row_num, $h);
            $col++;
        }

        $last_col = chr(ord('A') + count($headers) - 1);
        $sheet->getStyle("A$row_num:{$last_col}$row_num")->getFont()->setBold(true);
        $sheet->getStyle("A$row_num:{$last_col}$row_num")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A$row_num:{$last_col}$row_num")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC000');
        $row_num++;

        $no = $offset + 1;
        foreach ($query as $row) {
            $row = (object) $row;
            $nama_mahasiswa = get_field($row->MhswID, 'mahasiswa');
            $tahun_smt = get_field($row->TahunID, 'tahun');
            $tgl_val = !empty($row->Tgl) ? tgl($row->Tgl, '02') : '';

            $sheet->setCellValue('A' . $row_num, $no++);
            $sheet->setCellValue('B' . $row_num, $nama_mahasiswa);
            $sheet->setCellValue('C' . $row_num, $row->Status ?? '');
            $sheet->setCellValueExplicit('D' . $row_num, (string)($row->Nomor_Surat ?? ''), DataType::TYPE_STRING);

            $current_col = 'E';
            if ($StatusMhswID == 2) {
                $sheet->setCellValueExplicit('E' . $row_num, (string)($row->Mulai_Semester ?? ''), DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('F' . $row_num, (string)($row->Akhir_Semester ?? ''), DataType::TYPE_STRING);
                $current_col = 'G';
            }

            $sheet->setCellValue($current_col++ . $row_num, $row->Alasan ?? '');
            $sheet->setCellValue($current_col++ . $row_num, $tahun_smt);
            $sheet->setCellValue($current_col . $row_num, $tgl_val);
            $row_num++;
        }

        $styleBorder = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
        $sheet->getStyle("A$start_table_row:{$last_col}" . ($row_num - 1))->applyFromArray($styleBorder);

        foreach (range('A', $last_col) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="data_keterangan_status_mahasiswa.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * AJAX: Reactive student status
     * Legacy: c_keterangan_status_mahasiswa->reactive()
     */
    public function reactive(Request $request)
    {
        $checkid = $request->input('tableID');
        $this->service->reactive($checkid);
        return response('Success');
    }

    /**
     * Download Excel template
     * Legacy: c_keterangan_status_mahasiswa->downloadFormat()
     */
    public function downloadFormat()
    {
        $spreadsheet = new Spreadsheet();
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Format Upload');
        
        $sheet1->setCellValueExplicit('A1', 'NIM', DataType::TYPE_STRING);
        $sheet1->setCellValueExplicit('B1', 'Kode Tahun', DataType::TYPE_STRING);
        $sheet1->setCellValueExplicit('C1', 'Tanggal', DataType::TYPE_STRING);
        $sheet1->setCellValueExplicit('D1', 'Status Mahasiswa', DataType::TYPE_STRING);
        $sheet1->setCellValueExplicit('E1', 'Alasan', DataType::TYPE_STRING);
        $sheet1->setCellValueExplicit('F1', 'Nomor Surat', DataType::TYPE_STRING);

        $sheet1->fromArray([['12345678', '20201', '2020-09-01', 'Lulus', '', 'SK-001']], null, 'A2');
        $sheet1->getStyle("A1:F1")->getFont()->setBold(true);

        $sheet2 = $spreadsheet->createSheet(1);
        $sheet2->setTitle('List Status Mahasiwa');
        $sheet2->setCellValueExplicit('A1', 'Nama Status Mahasiswa', DataType::TYPE_STRING);
        $sheet2->getStyle("A1")->getFont()->setBold(true);
        
        $queryStatus = DB::table('statusmahasiswa')->select('Nama')->get();
        $sheetstatus = $queryStatus->map(fn($s) => [$s->Nama])->toArray();
        $sheet2->fromArray($sheetstatus, null, 'A2');

        foreach (range('A', 'F') as $col) { $sheet1->getColumnDimension($col)->setAutoSize(true); }
        $sheet2->getColumnDimension('A')->setAutoSize(true);

        header('Content-Disposition: attachment;filename="FormatUpload.xlsx"');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Import from Excel
     * Legacy: c_keterangan_status_mahasiswa->import()
     */
    public function import(Request $request)
    {
        $file = $request->file('file');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray(null, true, true, true);
        
        // Remove header
        unset($data[1]);

        $arrFailed = $this->service->process_import($data);

        $html = '';
        if (count($arrFailed) > 0) {
            $html .= "<ul>";
            foreach ($arrFailed as $row_fail) {
                $html .= "<li>" . $row_fail . "</li>";
            }
            $html .= "</ul>";
        } else {
            $html .= "Semua Data Berhasil di Insert.";
        }

        return response($html);
    }
}
