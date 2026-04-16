<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ListTidakKrsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ListTidakKrsController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;
    protected $jb = 59;

    public function __construct(ListTidakKrsService $service)
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

            $this->Create = cek_level($levelUser, 'c_list_tidak_krs', 'Create');
            $this->Update = cek_level($levelUser, 'c_list_tidak_krs', 'Update');
            $this->Delete = cek_level($levelUser, 'c_list_tidak_krs', 'Delete');

            return $next($request);
        });
    }

    public function index()
    {
        $row = DB::table('cek_running_tidak_krs')->where('ID', '1')->first();

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Mahasiswa Tidak KRS');
        }

        if ($row && $row->StatusRunning == 0) {
            $data['Update'] = $this->Update;
            $data['Create'] = $this->Create;
            return view('list_tidak_krs.v', $data);
        } else {
            return view('list_tidak_krs.loading');
        }
    }

    public function search(Request $request, $offset = 0)
    {
        $programID = $request->input('ProgramID', '');
        $prodiID = $request->input('ProdiID', '');
        $tahunID = $request->input('TahunID', '');
        $tahunMasuk = $request->input('TahunMasuk', '');
        $keyword = $request->input('keyword', '');
        $statusMhswID = $request->input('statusMhswID', '');
        $TidakKrs = $request->input('TidakKRS', '');

        $limit = 10;

        $query = $this->service->getDataMahasiswaTidakKRS(
            $programID, $tahunMasuk, $prodiID, $tahunID, $statusMhswID, $keyword, $TidakKrs, $offset, $limit
        );

        $count = $this->service->countDataMahasiswaTidakKRS(
            $programID, $tahunMasuk, $prodiID, $tahunID, $statusMhswID, $keyword, $TidakKrs
        );

        $data['query'] = $query;
        $data['link'] = load_pagination($count, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($count, $limit, $offset);
        $data['offset'] = $offset;
        $data['TahunID'] = $tahunID;
        $data['Update'] = $this->Update;

        return view('list_tidak_krs.s', $data);
    }

    public function update_data(Request $request)
    {
        $UserID = Session::get('UserID');
        
        // Execute background cron job
        $cronPath = base_path('cron/mahasiswatidakkrs.php');
        $clientSubdomain = config('app.client_subdomain', 'default');
        
        if (file_exists($cronPath)) {
            $cmd = "nohup php {$cronPath} {$clientSubdomain} {$UserID} > /dev/null 2>&1 &";
            exec($cmd, $output);
        }

        // Update running status
        DB::table('cek_running_tidak_krs')
            ->where('ID', '1')
            ->update([
                'StatusRunning' => '1',
                'LastUserID' => $UserID,
                'LastRunning' => date('Y-m-d H:i:s')
            ]);

        // Insert log
        $tahun_now = DB::table('tahun')->where('ProsesBuka', '1')->first();
        if ($tahun_now) {
            DB::table('log_running_tidak_krs')->insert([
                'TahunID' => $tahun_now->ID,
                'createdAt' => date('Y-m-d H:i:s'),
                'UserID' => $UserID
            ]);
        }

        return response()->json($output ?? []);
    }

    public function excel(Request $request)
    {
        $programID = $request->input('ProgramID', '');
        $prodiID = $request->input('ProdiID', '');
        $tahunID = $request->input('TahunID', '');
        $tahunMasuk = $request->input('TahunMasuk', '');
        $keyword = $request->input('keyword', '');
        $StatusMhswID = $request->input('StatusMhswID', '');
        $TidakKrs = $request->input('TidakKRS', '');

        $query_data = $this->service->getDataMahasiswaTidakKRS(
            $programID, $tahunMasuk, $prodiID, $tahunID, $StatusMhswID, $keyword, $TidakKrs, '', ''
        );

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Mhsw Tidak KRS');

        $row_num = 1;

        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'G');
        }

        $slog_text = strtoupper('DATA MAHASISWA TIDAK KRS');
        $sheet->setCellValue('A' . $row_num, $slog_text);
        $sheet->mergeCells('A' . $row_num . ':G' . $row_num);
        $sheet->getStyle('A' . $row_num)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $row_num++;

        $start_table_row = $row_num;

        $sheet->setCellValue('A' . $row_num, 'No.');
        $sheet->setCellValue('B' . $row_num, 'Nama');
        $sheet->setCellValue('C' . $row_num, 'NPM');
        $sheet->setCellValue('D' . $row_num, 'Program Kuliah');
        $sheet->setCellValue('E' . $row_num, 'Program Studi');
        $sheet->setCellValue('F' . $row_num, 'Tahun Masuk');
        $sheet->setCellValue('G' . $row_num, 'Jumlah Semester Tidak KRS');

        $sheet->getStyle('A' . $row_num . ':G' . $row_num)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row_num . ':G' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row_num . ':G' . $row_num)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A' . $row_num . ':G' . $row_num)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC000');
        $row_num++;

        $no = 1;
        if (!empty($query_data)) {
            foreach ($query_data as $mhs) {
                $sheet->setCellValue('A' . $row_num, $no++);
                $sheet->setCellValue('B' . $row_num, $mhs->Nama ?? '');
                $sheet->setCellValueExplicit('C' . $row_num, $mhs->NPM ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('D' . $row_num, get_field($mhs->ProgramID, "program"));
                $sheet->setCellValue('E' . $row_num, get_field($mhs->ProdiID, 'programstudi'));
                $sheet->setCellValueExplicit('F' . $row_num, $mhs->TahunMasuk ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('G' . $row_num, $mhs->jumlah ?? 0);

                $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('G' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A' . $row_num . ':G' . $row_num)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

                $row_num++;
            }
        } else {
            $sheet->setCellValue('A' . $row_num, 'Tidak ada data mahasiswa tidak KRS');
            $sheet->mergeCells('A' . $row_num . ':G' . $row_num);
            $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $row_num++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $start_table_row . ':G' . ($row_num - 1))->applyFromArray($styleBorder);

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setWidth(15);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "data_Mahasiswa_Tidak_KRS_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $objWriter->save('php://output');
        exit;
    }

    public function pdf(Request $request)
    {
        $programID = $request->input('ProgramID', '');
        $prodiID = $request->input('ProdiID', '');
        $tahunID = $request->input('TahunID', '');
        $tahunMasuk = $request->input('TahunMasuk', '');
        $keyword = $request->input('keyword', '');
        $statusMhswID = $request->input('statusMhswID', '');
        $TidakKrs = $request->input('TidakKRS', '');

        $data['query'] = $this->service->getDataMahasiswaTidakKRS(
            $programID, $tahunMasuk, $prodiID, $tahunID, $statusMhswID, $keyword, $TidakKrs, '', ''
        );

        return view('list_tidak_krs.p', $data);
    }

    public function set_status(Request $request)
    {
        $mhswID = $request->input('mhswID', '');
        $status = $request->input('status', '');
        $tahunID = $request->input('TahunID', '');

        $result = $this->service->setStudentStatus($mhswID, $status, $tahunID, $this->jb);

        return response()->json($result);
    }

    public function set_statusall(Request $request, $status = '')
    {
        $tahunID = $request->input('TahunID', '');
        $checkid = $request->input('checkID', []);

        // If status not in URL, check if it's in request
        if (!$status) {
            $status = $request->input('status', '');
        }

        $removed_ids = [];
        foreach ($checkid as $mhswID) {
            $result = $this->service->setStudentStatus($mhswID, $status, $tahunID, $this->jb);
            if ($result === 'success') {
                $removed_ids[] = $mhswID;
            } elseif ($result === 2) {
                return response('2');
            }
        }

        return response()->json($removed_ids);
    }
}
