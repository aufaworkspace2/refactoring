<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SetupMinimalBayarGenerateNimService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SetupMinimalBayarGenerateNimController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(SetupMinimalBayarGenerateNimService $service)
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

            $this->Create = cek_level($levelUser, 'c_setup_minimal_bayar_generate_nim', 'Create');
            $this->Update = cek_level($levelUser, 'c_setup_minimal_bayar_generate_nim', 'Update');
            $this->Delete = cek_level($levelUser, 'c_setup_minimal_bayar_generate_nim', 'Delete');

            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        $data['Create'] = $this->Create;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Setup Minimal Bayar Generate NIM');
        }

        return view('setup_minimal_bayar_generate_nim.v_setup_minimal_bayar_generate_nim', $data);
    }

    public function search(Request $request, $offset = 0)
    {
        $keyword = $request->input('keyword', '');
        $ProgramID = $request->input('ProgramID', '');
        $ProdiID = $request->input('ProdiID', '');

        $limit = 10;

        $jml = $this->service->count_all($keyword, $ProgramID, $ProdiID);

        $data['offset'] = $offset;
        $data['query'] = $this->service->get_data($limit, $offset, $keyword, $ProgramID, $ProdiID);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('setup_minimal_bayar_generate_nim.s_setup_minimal_bayar_generate_nim', $data);
    }

    public function add()
    {
        $data['save'] = 1;

        return view('setup_minimal_bayar_generate_nim.f_setup_minimal_bayar_generate_nim', $data);
    }

    public function view($id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['save'] = 2;

        return view('setup_minimal_bayar_generate_nim.f_setup_minimal_bayar_generate_nim', $data);
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
                    log_akses('Hapus', 'Menghapus Data setup_minimal_bayar_generate_nim Dengan Jenis ' . get_field($id, 'setup_minimal_bayar', 'Jenis'));
                }
                $this->service->delete($id);
                $removedIds[] = $id;
            }
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'setup_minimal_bayar_generate_nim_'
        ]);
    }

    public function pdf(Request $request)
    {
        return redirect()->route('setup_minimal_bayar_generate_nim.index');
    }

    public function excel(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $ProgramID = $request->input('ProgramID', '');
        $ProdiID = $request->input('ProdiID', '');

        $query_data = $this->service->get_data('', '', $keyword, $ProgramID, $ProdiID);

        $query_jenisbiaya = DB::table('jenisbiaya')->get();
        $jenisbiaya = [];
        foreach ($query_jenisbiaya as $row_jb) {
            $jenisbiaya[$row_jb->ID] = $row_jb;
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Setup Min. Bayar Gen NIM');

        $row_num = 1;

        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'E');
        }

        $slog_text = strtoupper(__('app.slog') ?? 'DATA SETUP MINIMAL BAYAR GENERATE NIM');
        $sheet->setCellValue('A' . $row_num, $slog_text);
        $sheet->mergeCells('A' . $row_num . ':E' . $row_num);
        $sheet->getStyle('A' . $row_num)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $row_num++;

        $start_table_row = $row_num;

        $sheet->setCellValue('A' . $row_num, 'No.');
        $sheet->setCellValue('B' . $row_num, 'Program Kuliah');
        $sheet->setCellValue('C' . $row_num, 'Program Studi');
        $sheet->setCellValue('D' . $row_num, 'Nominal');
        $sheet->setCellValue('E' . $row_num, 'Komponen Biaya');

        $sheet->getStyle('A' . $row_num . ':E' . $row_num)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row_num . ':E' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row_num . ':E' . $row_num)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC000');
        $row_num++;

        $no = 1;
        if (!empty($query_data)) {
            foreach ($query_data as $row) {
                $sheet->setCellValue('A' . $row_num, $no++);

                $prog_kuliah = ($row->ProgramID == '0') ? 'Semua Program Kuliah' : get_field($row->ProgramID, 'program');
                $sheet->setCellValue('B' . $row_num, $prog_kuliah);

                $prog_studi = ($row->ProdiID === '0') ? 'Semua Program Studi' : get_field($row->ProdiID, 'programstudi');
                $sheet->setCellValue('C' . $row_num, $prog_studi);

                $sheet->setCellValue('D' . $row_num, $row->Nominal ?? 0);
                $sheet->getStyle('D' . $row_num)->getNumberFormat()->setFormatCode('"Rp "#,##0');

                $komponen_str = "Tidak Ada Komponen Biaya";
                if (!empty($row->JenisBiayaID_list)) {
                    $jenisbiaya_id_list = explode(",", $row->JenisBiayaID_list);
                    $arr_kb = [];
                    foreach ($jenisbiaya_id_list as $m) {
                        if (isset($jenisbiaya[$m])) {
                            $arr_kb[] = "- " . $jenisbiaya[$m]->Nama;
                        }
                    }
                    if (count($arr_kb) > 0) {
                        $komponen_str = implode("\n", $arr_kb);
                    }
                }
                $sheet->setCellValue('E' . $row_num, $komponen_str);

                $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A' . $row_num . ':E' . $row_num)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
                $sheet->getStyle('E' . $row_num)->getAlignment()->setWrapText(true);

                $row_num++;
            }
        } else {
            $sheet->setCellValue('A' . $row_num, 'Tidak ada data setup minimal bayar');
            $sheet->mergeCells('A' . $row_num . ':E' . $row_num);
            $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $row_num++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $start_table_row . ':E' . ($row_num - 1))->applyFromArray($styleBorder);

        if (!function_exists('cetak_kop_phpspreadsheet')) {
            $sheet->getColumnDimension('A')->setAutoSize(true);
        }
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setWidth(40);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "data_setup_minimal_bayar_generate_nim_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $objWriter->save('php://output');
        exit;
    }
}
