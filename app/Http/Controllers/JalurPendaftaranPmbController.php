<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JalurPendaftaranPmbService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Barryvdh\DomPDF\Facade\Pdf;

class JalurPendaftaranPmbController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(JalurPendaftaranPmbService $service)
    {
        $this->service = $service;

        $this->middleware(function ($request, $next) {
            // Check authentication
            if (!Session::get('username')) {
                return redirect('/');
            }

            // Language setup
            $lang = Session::get('language');
            if (!$lang && !request()->cookie('language')) {
                $lang = 'indonesia';
                Session::put('language', $lang);
            }

            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);

            $levelUser = Session::get('LevelUser');

            $this->Create = cek_level($levelUser, 'c_jalur_pendaftaran_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_jalur_pendaftaran_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_jalur_pendaftaran_pmb', 'Delete');

            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        $data['Create'] = $this->Create;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Jalur Pendaftaran');
        }

        return view('jalur_pendaftaran_pmb.v_jalur_pendaftaran_pmb', $data);
    }

    public function search(Request $request, $offset = 0)
    {
        $keyword = $request->input('keyword', '');
        $active = $request->input('active', '');

        $limit = 10;
        $jml = $this->service->count_all($keyword, $active);
        $data['offset'] = $offset;

        $data['query'] = $this->service->get_data($limit, $offset, $keyword, $active);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('jalur_pendaftaran_pmb.s_jalur_pendaftaran_pmb', $data);
    }

    public function add()
    {
        $data['save'] = 1;

        return view('jalur_pendaftaran_pmb.f_jalur_pendaftaran_pmb', $data);
    }

    public function add_detail(Request $request)
    {
        $data['save'] = 1;
        $row = new \stdClass();
        $row->gelombang_id = $request->input('gelombang_id', '');
        $data['row'] = $row;

        return view('jalur_pendaftaran_pmb.f_gelombang_detail_pmb', $data);
    }

    public function view($id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['save'] = 2;

        return view('jalur_pendaftaran_pmb.f_jalur_pendaftaran_pmb', $data);
    }

    public function save(Request $request, $save)
    {
        $id = $request->input('id', '');
        $kode = $request->input('kode', '');
        $nama = $request->input('nama', '');

        $input['kode'] = $kode;
        $input['nama'] = $nama;

        $cek = $this->service->checkDuplicateKode($kode, $id);

        if ($cek && isset($cek->id)) {
            echo "gagal";
        } else {
            if ($save == 1) {
                if (function_exists('logs')) {
                    logs("Menambah data $nama pada tabel " . request()->segment(1));
                }
                $insertId = $this->service->add($input);
                echo $insertId;
            }
            if ($save == 2) {
                if (function_exists('logs')) {
                    logs("Mengubah data $cek->nama menjadi $nama pada tabel " . request()->segment(1));
                }
                $this->service->edit($id, $input);
            }
        }
    }

    public function delete(Request $request)
    {
        $checkid = $request->input('checkID', []);
        $removedIds = [];

        for ($x = 0; $x <= count($checkid) - 1; $x++) {
            if (function_exists('log_akses')) {
                log_akses('Hapus', 'Menghapus Data jalur_pendaftaran_pmb Dengan nama ' . get_field($checkid[$x], 'pmb_edu_jalur_pendaftaran', 'nama'));
            }
            $this->service->delete($checkid[$x]);
            $removedIds[] = $checkid[$x];
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'jalur_pendaftaran_pmb_'
        ]);
    }

    public function pdf(Request $request)
    {
        $keyword = $request->input('keyword', '');

        $data['query'] = $this->service->get_data(null, null, $keyword, '');

        $pdf = Pdf::loadView('jalur_pendaftaran_pmb.p_jalur_pendaftaran_pmb', $data);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Data_Jalur_Pendaftaran_PMB_' . date('Y-m-d') . '.pdf');
    }

    public function excel(Request $request)
    {
        $keyword = $request->input('keyword', '');

        $data = $this->service->get_data(null, null, $keyword, '');

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Jalur Pendaftaran PMB');

        $row_num = 1;
        
        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'D');
        }

        $sheet->setCellValue('A'.$row_num, 'DATA JALUR PENDAFTARAN PMB');
        $sheet->mergeCells('A'.$row_num.':D'.$row_num); 
        $sheet->getStyle('A'.$row_num)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row_num += 2; 
        $start_table_row = $row_num;
        
        $sheet->setCellValue('A'.$row_num, 'No.');
        $sheet->setCellValue('B'.$row_num, 'Kode');
        $sheet->setCellValue('C'.$row_num, 'Nama');
        $sheet->setCellValue('D'.$row_num, 'Aktif');

        $sheet->getStyle('A'.$row_num.':D'.$row_num)->getFont()->setBold(true);
        $sheet->getStyle('A'.$row_num.':D'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A'.$row_num.':D'.$row_num)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFC000');
        $row_num++;

        $no = 1;
        if (!empty($data)) {
            foreach ($data as $row) {
                $row = (object) $row;
                
                $sheet->setCellValue('A'.$row_num, $no++);
                $sheet->setCellValue('B'.$row_num, $row->kode ?? '');
                $sheet->setCellValue('C'.$row_num, $row->nama ?? '');
                
                $aktif = (isset($row->aktif) && $row->aktif == '1') ? 'Aktif' : 'Tidak Aktif';
                $sheet->setCellValue('D'.$row_num, $aktif);
                
                $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $row_num++;
            }
        } else {
            $sheet->setCellValue('A'.$row_num, 'Tidak ada data');
            $sheet->mergeCells('A'.$row_num.':D'.$row_num);
            $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row_num++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A'.$start_table_row.':D'.($row_num-1))->applyFromArray($styleBorder);

        foreach(range('A','D') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "data_jalur_pendaftaran_pmb_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function aktif(Request $request)
    {
        $val = $request->input('val', '');
        $buka = $request->input('buka', '');

        $this->service->updateAktif($val, $buka);
    }
}
