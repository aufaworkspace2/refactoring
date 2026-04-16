<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SetupHargaBiayaVariableService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SetupHargaBiayaVariableController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(SetupHargaBiayaVariableService $service)
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

            $this->Create = cek_level($levelUser, 'c_setup_harga_biaya_variable', 'Create');
            $this->Update = cek_level($levelUser, 'c_setup_harga_biaya_variable', 'Update');
            $this->Delete = cek_level($levelUser, 'c_setup_harga_biaya_variable', 'Delete');

            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        $data['Create'] = $this->Create;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Setup Harga Biaya Variable');
        }

        return view('setup_harga_biaya_variable.v_setup_harga_biaya_variable', $data);
    }

    public function search(Request $request, $offset = 0)
    {
        $keyword = $request->input('keyword', '');
        $ProgramID = $request->input('ProgramID', '');
        $ProdiID = $request->input('ProdiID', '');
        $TahunMasuk = $request->input('TahunMasuk', '');
        $Jenis = $request->input('Jenis', '');
        $JenisPendaftaran = $request->input('JenisPendaftaran', '');

        $limit = 10;

        $jml = $this->service->count_all($keyword, $ProgramID, $ProdiID, $TahunMasuk, $Jenis, $JenisPendaftaran);

        $data['offset'] = $offset;
        $data['query'] = $this->service->get_data($limit, $offset, $keyword, $ProgramID, $ProdiID, $TahunMasuk, $Jenis, $JenisPendaftaran);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('setup_harga_biaya_variable.s_setup_harga_biaya_variable', $data);
    }

    public function add()
    {
        $data['save'] = 1;

        return view('setup_harga_biaya_variable.f_setup_harga_biaya_variable', $data);
    }

    public function view($id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['save'] = 2;

        return view('setup_harga_biaya_variable.f_setup_harga_biaya_variable', $data);
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
                    log_akses('Hapus', 'Menghapus Data setup_harga_biaya_variable Dengan Nominal ' . get_field($id, 'setup_harga_biaya_variable', 'Nominal'));
                }
                $this->service->delete($id);
                $removedIds[] = $id;
            }
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'setup_harga_biaya_variable_'
        ]);
    }

    public function pdf(Request $request)
    {
        return redirect()->route('setup_harga_biaya_variable.index');
    }

    public function excel(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $ProgramID = $request->input('ProgramID', '');
        $ProdiID = $request->input('ProdiID', '');
        $TahunMasuk = $request->input('TahunMasuk', '');
        $Jenis = $request->input('Jenis', '');

        $query_data = $this->service->get_data('', '', $keyword, $ProgramID, $ProdiID, $TahunMasuk, $Jenis, '');

        $query_jp = DB::table('jenis_pendaftaran')->get();
        $arr_jenis_pendaftaran = [];
        foreach ($query_jp as $jp) {
            $arr_jenis_pendaftaran[$jp->Kode] = $jp->Nama;
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Setup Harga Biaya Variable');

        $row_num = 1;

        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'G');
        }

        $slog_text = strtoupper(__('app.slog') ?? 'DATA SETUP HARGA BIAYA VARIABLE');
        $sheet->setCellValue('A' . $row_num, $slog_text);
        $sheet->mergeCells('A' . $row_num . ':G' . $row_num);
        $sheet->getStyle('A' . $row_num)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $row_num++;

        $start_table_row = $row_num;

        $sheet->setCellValue('A' . $row_num, 'No.');
        $sheet->setCellValue('B' . $row_num, 'Program Kuliah');
        $sheet->setCellValue('C' . $row_num, 'Program Studi');
        $sheet->setCellValue('D' . $row_num, 'Angkatan');
        $sheet->setCellValue('E' . $row_num, 'Jenis Pendaftaran');
        $sheet->setCellValue('F' . $row_num, 'Jenis');
        $sheet->setCellValue('G' . $row_num, 'Nominal');

        $sheet->getStyle('A' . $row_num . ':G' . $row_num)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row_num . ':G' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row_num . ':G' . $row_num)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC000');
        $row_num++;

        $no = 1;
        if (!empty($query_data)) {
            foreach ($query_data as $row) {
                $sheet->setCellValue('A' . $row_num, $no++);

                $prog_kuliah = ($row->ProgramID == '0') ? 'Semua Program Kuliah' : get_field($row->ProgramID, 'program');
                $sheet->setCellValue('B' . $row_num, $prog_kuliah);

                $prog_studi = ($row->ProdiID === '0') ? 'Semua Program Studi' : get_field($row->ProdiID, 'programstudi');
                $sheet->setCellValue('C' . $row_num, $prog_studi);

                $angkatan = ($row->TahunMasuk === '0') ? 'Semua Tahun Masuk' : $row->TahunMasuk;
                $sheet->setCellValueExplicit('D' . $row_num, $angkatan, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                $jenis_pend = '';
                if ($row->JenisPendaftaran != null || $row->JenisPendaftaran === '0') {
                    if ($row->JenisPendaftaran === '0') {
                        $jenis_pend = "Semua Jenis Pendaftaran";
                    } else {
                        $jenis_pend = $arr_jenis_pendaftaran[$row->JenisPendaftaran] ?? '';
                    }
                }
                $sheet->setCellValue('E' . $row_num, $jenis_pend);

                $jenis_text = $row->Jenis;
                if ($row->Jenis == 'Cuti') {
                    $jenis_text .= "\n( " . tgl($row->TanggalMulai, '02') . " s/d " . tgl($row->TanggalSelesai, '02') . " )";
                }
                $sheet->setCellValue('F' . $row_num, $jenis_text);

                $nom_arr = [];
                if ($row->Jenis == 'SKS') {
                    if ($row->HitungPraktek == 1) {
                        $nom_arr[] = "Per SKS Teori : " . rupiah($row->Nominal);
                        $nom_arr[] = "Per SKS Praktek : " . rupiah($row->NominalPraktek);
                    } else {
                        $nom_arr[] = "Per SKS : " . rupiah($row->Nominal);
                    }
                    $nom_arr[] = "Paket : " . rupiah($row->NominalPaket);
                    $nom_arr[] = "Skripsi : " . rupiah($row->NominalSkripsi);
                } else {
                    $nom_arr[] = rupiah($row->Nominal);
                }
                $sheet->setCellValue('G' . $row_num, implode("\n", $nom_arr));

                $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('G' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle('A' . $row_num . ':G' . $row_num)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
                $sheet->getStyle('F' . $row_num)->getAlignment()->setWrapText(true);
                $sheet->getStyle('G' . $row_num)->getAlignment()->setWrapText(true);

                $row_num++;
            }
        } else {
            $sheet->setCellValue('A' . $row_num, 'Tidak ada data setup harga biaya variable');
            $sheet->mergeCells('A' . $row_num . ':G' . $row_num);
            $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $row_num++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $start_table_row . ':G' . ($row_num - 1))->applyFromArray($styleBorder);

        if (!function_exists('cetak_kop_phpspreadsheet')) {
            $sheet->getColumnDimension('A')->setAutoSize(true);
        }
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setWidth(30);
        $sheet->getColumnDimension('G')->setWidth(35);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "data_setup_harga_biaya_variable_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $objWriter->save('php://output');
        exit;
    }
}
