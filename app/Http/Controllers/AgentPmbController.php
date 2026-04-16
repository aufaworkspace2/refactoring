<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AgentPmbService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Barryvdh\DomPDF\Facade\Pdf;

class AgentPmbController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(AgentPmbService $service)
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

            $this->Create = cek_level($levelUser, 'c_agent_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_agent_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_agent_pmb', 'Delete');

            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        $data['Create'] = $this->Create;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data agent PMB');
        }

        return view('agent_pmb.v_agent_pmb', $data);
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

        return view('agent_pmb.s_agent_pmb', $data);
    }

    public function add()
    {
        $data['save'] = 1;

        return view('agent_pmb.f_agent_pmb', $data);
    }

    public function add_detail(Request $request)
    {
        $data['save'] = 1;
        $row = new \stdClass();
        $row->gelombang_id = $request->input('gelombang_id', '');
        $data['row'] = $row;

        return view('agent_pmb.f_gelombang_detail_pmb', $data);
    }

    public function view($id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['save'] = 2;

        return view('agent_pmb.f_agent_pmb', $data);
    }

    public function save(Request $request, $save)
    {
        $id = $request->input('id', '');
        $nama = $request->input('nama', '');
        $institusi = $request->input('institusi', '');
        $no_telepon = $request->input('no_telepon', '');
        $email = $request->input('email', '');

        $input['nama'] = $nama;
        $input['institusi'] = $institusi;
        $input['no_telepon'] = $no_telepon;
        $input['email'] = $email;

        $cek = $this->service->checkDuplicateNama($nama, $id);

        if ($cek && isset($cek->id)) {
            echo "gagal";
            exit;
        }

        if ($save == 1) {
            $input['kode_referal'] = $this->service->generateKodeReferal();
            $input['created_at'] = date('Y-m-d H:i:s');

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
                log_akses('Hapus', 'Menghapus Data agent_pmb Dengan nama ' . get_field($checkid[$x], 'pmb_tbl_agent', 'nama'));
            }
            $this->service->delete($checkid[$x]);
            $removedIds[] = $checkid[$x];
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'agent_pmb_'
        ]);
    }

    public function pdf(Request $request)
    {
        $keyword = $request->input('keyword', '');

        $data['query'] = $this->service->get_data(null, null, $keyword, '');

        $pdf = Pdf::loadView('agent_pmb.p_agent_pmb', $data);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Data_Agent_PMB_' . date('Y-m-d') . '.pdf');
    }

    public function excel(Request $request)
    {
        $keyword = $request->input('keyword', '');

        $data = $this->service->get_data(null, null, $keyword, '');

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Agent PMB');

        $row_num = 1;
        
        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'G');
        }

        $sheet->setCellValue('A'.$row_num, 'DATA AGENT PMB');
        $sheet->mergeCells('A'.$row_num.':G'.$row_num); 
        $sheet->getStyle('A'.$row_num)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row_num += 2; 
        $start_table_row = $row_num;
        
        $sheet->setCellValue('A'.$row_num, 'No.');
        $sheet->setCellValue('B'.$row_num, 'Nama');
        $sheet->setCellValue('C'.$row_num, 'Institusi');
        $sheet->setCellValue('D'.$row_num, 'No Telepon');
        $sheet->setCellValue('E'.$row_num, 'Email');
        $sheet->setCellValue('F'.$row_num, 'Kode Referal');
        $sheet->setCellValue('G'.$row_num, 'Link Daftar');

        $sheet->getStyle('A'.$row_num.':G'.$row_num)->getFont()->setBold(true);
        $sheet->getStyle('A'.$row_num.':G'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A'.$row_num.':G'.$row_num)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFC000');
        $row_num++;

        $no = 1;
        if (!empty($data)) {
            foreach ($data as $row) {
                $row = (object) $row;
                
                $sheet->setCellValue('A'.$row_num, $no++);
                $sheet->setCellValue('B'.$row_num, $row->nama ?? '');
                $sheet->setCellValue('C'.$row_num, $row->institusi ?? '');
                $sheet->setCellValueExplicit('D'.$row_num, $row->no_telepon ?? '', DataType::TYPE_STRING);
                $sheet->setCellValue('E'.$row_num, $row->email ?? '');
                $sheet->setCellValue('F'.$row_num, $row->kode_referal ?? '');
                $sheet->setCellValue('G'.$row_num, config('app.pmb_url', getenv('PMB_URL')) . '/registrasi?rf=' . ($row->kode_referal ?? ''));
                
                $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $row_num++;
            }
        } else {
            $sheet->setCellValue('A'.$row_num, 'Tidak ada data');
            $sheet->mergeCells('A'.$row_num.':G'.$row_num);
            $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row_num++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A'.$start_table_row.':G'.($row_num-1))->applyFromArray($styleBorder);

        foreach(range('A','G') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "data_agent_pmb_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
