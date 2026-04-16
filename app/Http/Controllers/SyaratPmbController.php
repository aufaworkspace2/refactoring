<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SyaratPmbService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Barryvdh\DomPDF\Facade\Pdf;

class SyaratPmbController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(SyaratPmbService $service)
    {
        $this->service = $service;

        $this->middleware(function ($request, $next) {
            if (!Session::get('username')) {
                return redirect('/');
            }

            $lang = Session::get('language');
            if (!$lang && !request()->cookie('language')) {
                $lang = 'indonesia';
                Session::put('language', $lang);
            }

            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);

            $levelUser = Session::get('LevelUser');

            $this->Create = cek_level($levelUser, 'c_syarat_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_syarat_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_syarat_pmb', 'Delete');

            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        $data['Create'] = $this->Create;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Syarat PMB');
        }

        return view('syarat_pmb.v_syarat_pmb', $data);
    }

    public function search(Request $request, $offset = 0)
    {
        $keyword = $request->input('keyword', '');
        $tipe = $request->input('tipe', '');

        $limit = 10;
        $jml = $this->service->count_all($keyword, $tipe);
        $data['offset'] = $offset;

        $data['query'] = $this->service->get_data($limit, $offset, $keyword, $tipe);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('syarat_pmb.s_syarat_pmb', $data);
    }

    public function add()
    {
        $data['save'] = 1;

        return view('syarat_pmb.f_syarat_pmb', $data);
    }

    public function add_detail(Request $request)
    {
        $data['save'] = 1;
        $row = new \stdClass();
        $row->gelombang_id = $request->input('gelombang_id', '');
        $data['row'] = $row;

        return view('syarat_pmb.f_gelombang_detail_pmb', $data);
    }

    public function view($id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['save'] = 2;

        return view('syarat_pmb.f_syarat_pmb', $data);
    }

    public function save(Request $request, $save)
    {
        $id = $request->input('id', '');
        $kode = $request->input('kode', '');
        $nama = $request->input('nama', '');
        $jalur_pendaftaran = implode(",", $request->input('jalur_pendaftaran', []));
        $tipe = $request->input('tipe', '');
        $master_diskon_id_list = implode(",", $request->input('master_diskon_id_list', []));
        $old_file = $request->input('old_file', '');

        $input['kode'] = $kode;
        $input['nama'] = $nama;
        $input['jalur_pendaftaran'] = $jalur_pendaftaran;
        $input['tipe'] = $tipe;
        $input['master_diskon_id_list'] = $master_diskon_id_list;

        // File upload handling - simplified for Laravel
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('pmb/file_referensi_syarat'), $fileName);
            $input['file'] = $fileName;
        } else {
            $input['file'] = $old_file;
        }

        $input['keterangan'] = $request->input('keterangan', '');

        $cek = $this->service->checkDuplicateKode($kode, $id);

        if ($cek && isset($cek->id)) {
            echo "gagal";
            exit;
        }

        $cek2 = $this->service->checkDuplicateNama($nama, $id);

        if ($cek2 && isset($cek2->id)) {
            echo "gagal";
            exit;
        }

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

    public function delete(Request $request)
    {
        $checkid = $request->input('checkID', []);
        $removedIds = [];

        for ($x = 0; $x <= count($checkid) - 1; $x++) {
            if (function_exists('log_akses')) {
                log_akses('Hapus', 'Menghapus Data syarat_pmb Dengan nama ' . get_field($checkid[$x], 'pmb_edu_syarat', 'nama'));
            }
            $this->service->delete($checkid[$x]);
            $removedIds[] = $checkid[$x];
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'syarat_pmb_'
        ]);
    }

    public function pdf(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $tipe = $request->input('tipe', '');

        $data['query'] = $this->service->get_data(null, null, $keyword, $tipe);

        $pdf = Pdf::loadView('syarat_pmb.p_syarat_pmb', $data);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Data_Syarat_PMB_' . date('Y-m-d') . '.pdf');
    }

    public function excel(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $tipe = $request->input('tipe', '');

        $data = $this->service->get_data(null, null, $keyword, $tipe);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Syarat PMB');

        $row_num = 1;
        
        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'E');
        }

        $sheet->setCellValue('A'.$row_num, 'DATA SYARAT PMB');
        $sheet->mergeCells('A'.$row_num.':E'.$row_num); 
        $sheet->getStyle('A'.$row_num)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row_num += 2; 
        $start_table_row = $row_num;
        
        $sheet->setCellValue('A'.$row_num, 'No.');
        $sheet->setCellValue('B'.$row_num, 'Kode');
        $sheet->setCellValue('C'.$row_num, 'Nama');
        $sheet->setCellValue('D'.$row_num, 'Jalur Pendaftaran');
        $sheet->setCellValue('E'.$row_num, 'Tipe');

        $sheet->getStyle('A'.$row_num.':E'.$row_num)->getFont()->setBold(true);
        $sheet->getStyle('A'.$row_num.':E'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A'.$row_num.':E'.$row_num)->getFill()
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
                
                // Jalur Pendaftaran
                $jalur_nama = '';
                if (isset($row->jalur_pendaftaran) && $row->jalur_pendaftaran) {
                    $jalurs = explode(",", $row->jalur_pendaftaran);
                    $jalur_names = [];
                    foreach ($jalurs as $j) {
                        if (function_exists('get_field')) {
                            $jalur_names[] = get_field($j, 'pmb_edu_jalur_pendaftaran');
                        }
                    }
                    $jalur_nama = implode(", ", $jalur_names);
                }
                $sheet->setCellValue('D'.$row_num, $jalur_nama);
                $sheet->setCellValue('E'.$row_num, $row->tipe ?? '');
                
                $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $row_num++;
            }
        } else {
            $sheet->setCellValue('A'.$row_num, 'Tidak ada data');
            $sheet->mergeCells('A'.$row_num.':E'.$row_num);
            $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row_num++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A'.$start_table_row.':E'.($row_num-1))->applyFromArray($styleBorder);

        foreach(range('A','E') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "data_syarat_pmb_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
