<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BankService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class BankController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(BankService $service)
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

            $this->Create = cek_level($levelUser, 'c_bank', 'Create');
            $this->Update = cek_level($levelUser, 'c_bank', 'Update');
            $this->Delete = cek_level($levelUser, 'c_bank', 'Delete');

            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        $data['Create'] = $this->Create;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Setup Bank');
        }

        return view('bank.v_bank', $data);
    }

    public function search(Request $request, $offset = 0)
    {
        $keyword = $request->input('keyword', '');
        $limit = 10;

        $jml = $this->service->count_all($keyword);
        $data['offset'] = $offset;

        $data['query'] = $this->service->get_data($limit, $offset, $keyword);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);

        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        // Get all metode pembayaran
        $all_metode_bayar = [];
        foreach (DB::table('metode_pembayaran')->get() as $r) {
            $all_metode_bayar[$r->ID] = $r->Nama;
        }
        $data['all_metode_bayar'] = $all_metode_bayar;

        // Get channel pembayaran
        $channel_bayar = [];
        foreach (DB::table('channel_pembayaran')->get() as $r) {
            $channel_bayar[$r->ID] = $r;
        }
        $data['channel_bayar'] = $channel_bayar;

        return view('bank.s_bank', $data);
    }

    public function add()
    {
        $data['save'] = 1;
        $data['ChannelPembayaranList'] = $this->service->get_channel_pembayaran_list();

        return view('bank.f_bank', $data);
    }

    public function view($id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['save'] = 2;
        $data['ChannelPembayaranList'] = $this->service->get_channel_pembayaran_list();

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Data Setup Bank Dengan ID ' . $id);
        }

        return view('bank.f_bank', $data);
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
                    log_akses('Hapus', 'Menghapus Data Setup Bank Dengan Nama ' . get_field($id, 'bank', 'NamaBank'));
                }
                $this->service->delete($id);
                $removedIds[] = $id;
            }
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'bank_'
        ]);
    }

    public function pdf(Request $request)
    {
        // Legacy PDF export functionality
        $keyword = $request->input('keyword', '');
        
        // You can implement PDF export here if needed
        // For now, redirect to index or implement with a PDF library
        return redirect()->route('bank.index');
    }

    public function excel(Request $request)
    {
        $keyword = $request->input('keyword', '');

        $query_data = $this->service->get_data('', '', $keyword);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Bank');

        $row_num = 1;

        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'D');
        }

        $slog_text = strtoupper(__('app.slog'));
        $sheet->setCellValue('A' . $row_num, $slog_text);
        $sheet->mergeCells('A' . $row_num . ':D' . $row_num);
        $sheet->getStyle('A' . $row_num)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $row_num++;

        $start_table_row = $row_num;

        $sheet->setCellValue('A' . $row_num, 'No.');
        $sheet->setCellValue('B' . $row_num, __('app.NamaBank') ?? 'Nama Bank');
        $sheet->setCellValue('C' . $row_num, __('app.NoRekening') ?? 'No Rekening');
        $sheet->setCellValue('D' . $row_num, __('app.NamaPemilik') ?? 'Nama Pemilik');

        $sheet->getStyle('A' . $row_num . ':D' . $row_num)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row_num . ':D' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row_num . ':D' . $row_num)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC000');
        $row_num++;

        $no = 1;
        if (!empty($query_data)) {
            foreach ($query_data as $row) {
                $sheet->setCellValue('A' . $row_num, $no++);
                $sheet->setCellValue('B' . $row_num, $row->NamaBank ?? '');

                $sheet->setCellValueExplicit('C' . $row_num, $row->NoRekening ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                $sheet->setCellValue('D' . $row_num, $row->NamaPemilik ?? '');
                $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $row_num++;
            }
        } else {
            $sheet->setCellValue('A' . $row_num, 'Tidak ada data bank');
            $sheet->mergeCells('A' . $row_num . ':D' . $row_num);
            $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $row_num++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $start_table_row . ':D' . ($row_num - 1))->applyFromArray($styleBorder);

        if (!function_exists('cetak_kop_phpspreadsheet')) {
            $sheet->getColumnDimension('A')->setAutoSize(true);
        }
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "data_bank_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $objWriter->save('php://output');
        exit;
    }
}
