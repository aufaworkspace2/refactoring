<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JenisBiayaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class JenisBiayaController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(JenisBiayaService $service)
    {
        $this->service = $service;

        $this->middleware(function ($request, $next) {
            // Language setup
            $lang = Session::get('language');
            if (!$lang && !$request->cookie('language')) {
                $lang = 'indonesia';
                Session::put('language', $lang);
            }

            // Map legacy language names to Laravel locales
            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);

            $levelUser = Session::get('LevelUser');

            $this->Create = cek_level($levelUser, 'c_jenisbiaya', 'Create');
            $this->Update = cek_level($levelUser, 'c_jenisbiaya', 'Update');
            $this->Delete = cek_level($levelUser, 'c_jenisbiaya', 'Delete');

            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        $data['Create'] = $this->Create;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Setup Jenis Biaya');
        }

        return view('jenisbiaya.v_jenisbiaya', $data);
    }

    public function search(Request $request, $offset = 0)
    {
        $keyword = $request->input('keyword', '');
        $Program = $request->input('Program', '');
        $Prodi = $request->input('Prodi', '');
        $TahunMasuk = $request->input('TahunMasuk', '');
        $limit = 10;

        $jml = $this->service->count_all($keyword, $Program, $Prodi, $TahunMasuk);

        $data['offset'] = $offset;
        $data['query'] = $this->service->get_data($limit, $offset, $keyword, $Program, $Prodi, $TahunMasuk);

        // Get detail for each jenisbiaya
        $get = [];
        foreach ($data['query'] as $row) {
            $get[$row->ID] = DB::table('jenisbiaya_detail')
                ->where('JenisBiayaID', $row->ID)
                ->orderBy('Urut', 'ASC')
                ->get();
        }
        $data['get'] = $get;

        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('jenisbiaya.s_jenisbiaya', $data);
    }

    public function add()
    {
        $data['save'] = 1;

        return view('jenisbiaya.f_jenisbiaya', $data);
    }

    public function load_list_komponen(Request $request)
    {
        $JenisBiayaID = $request->input('JenisBiayaID', '');

        $query = DB::table('jenisbiaya_detail')
            ->where('JenisBiayaID', $JenisBiayaID)
            ->orderBy('Urut', 'ASC')
            ->get();

        return response()->json($query);
    }

    public function lihat_detail($JenisBiayaID)
    {
        $data['query'] = DB::table('jenisbiaya_detail')
            ->where('JenisBiayaID', $JenisBiayaID)
            ->orderBy('Urut', 'ASC')
            ->get();

        return view('jenisbiaya.s_detail', $data);
    }

    public function jenisbiaya_detail_delete(Request $request)
    {
        $ID = $request->input('ID', []);
        $IDx = $request->input('IDx', '');

        DB::table('jenisbiaya_detail')
            ->whereIn('ID', (array) $ID)
            ->where('JenisBiayaID', $IDx)
            ->delete();

        return response()->json(['status' => 'success']);
    }

    public function view($id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['save'] = 2;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Data Setup Jenis Biaya Dengan ID ' . $id);
        }

        return view('jenisbiaya.f_jenisbiaya', $data);
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
                    log_akses('Hapus', 'Menghapus Data Setup Jenis Biaya Dengan Nama ' . get_field($id, 'jenisbiaya', 'Nama'));
                }
                $this->service->delete_sub($id);
                $this->service->delete($id);
                $removedIds[] = $id;
            }
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'jenisbiaya_'
        ]);
    }

    public function pdf(Request $request)
    {
        $keyword = $request->input('keyword', '');
        // Implement PDF if needed
        return redirect()->route('jenisbiaya.index');
    }

    public function excel(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $frekuensi = $request->input('frekuensi', '');

        $query_data = $this->service->get_data('', '', $keyword, '', '', '');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Jenis Biaya');

        $row_num = 1;

        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'B');
        }

        $slog_text = strtoupper(__('app.slog') ?? 'DATA JENIS BIAYA');
        $sheet->setCellValue('A' . $row_num, $slog_text);
        $sheet->mergeCells('A' . $row_num . ':B' . $row_num);
        $sheet->getStyle('A' . $row_num)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $row_num++;

        $start_table_row = $row_num;

        $sheet->setCellValue('A' . $row_num, 'No.');
        $sheet->setCellValue('B' . $row_num, __('app.Nama') ?? 'Nama');

        $sheet->getStyle('A' . $row_num . ':B' . $row_num)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row_num . ':B' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row_num . ':B' . $row_num)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC000');
        $row_num++;

        $no = 1;
        if (!empty($query_data)) {
            foreach ($query_data as $row) {
                $sheet->setCellValue('A' . $row_num, $no++);
                $sheet->setCellValue('B' . $row_num, $row->Nama ?? '');
                $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $row_num++;
            }
        } else {
            $sheet->setCellValue('A' . $row_num, 'Tidak ada data jenis biaya');
            $sheet->mergeCells('A' . $row_num . ':B' . $row_num);
            $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $row_num++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $start_table_row . ':B' . ($row_num - 1))->applyFromArray($styleBorder);

        if (!function_exists('cetak_kop_phpspreadsheet')) {
            $sheet->getColumnDimension('A')->setAutoSize(true);
        }
        $sheet->getColumnDimension('B')->setAutoSize(true);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "data_jenisbiaya_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $objWriter->save('php://output');
        exit;
    }
}
