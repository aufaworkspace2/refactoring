<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\KategoriSoalPmbService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Barryvdh\DomPDF\Facade\Pdf;

class KategoriSoalPmbController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(KategoriSoalPmbService $service)
    {
        $this->service = $service;

        $this->middleware(function ($request, $next) {
            if (!Session::get('username')) { return redirect('/'); }
            $lang = Session::get('language');
            if (!$lang && !request()->cookie('language')) { $lang = 'indonesia'; Session::put('language', $lang); }
            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);
            $levelUser = Session::get('LevelUser');
            $this->Create = cek_level($levelUser, 'c_kategori_soal_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_kategori_soal_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_kategori_soal_pmb', 'Delete');
            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        $data['Create'] = $this->Create;
        if (function_exists('log_akses')) { log_akses('View', 'Melihat Daftar Data Setup Kategori Soal'); }
        return view('kategori_soal_pmb.v_kategori_soal_pmb', $data);
    }

    public function soal(Request $request, $offset = 0)
    {
        $data['Create'] = $this->Create;
        $idkategori = $request->input('idkategori', '');
        $data['idkategori'] = $idkategori;
        if (function_exists('log_akses')) { log_akses('View', 'Melihat Daftar Data Setup Soal'); }
        return view('kategori_soal_pmb.v_soal_pmb', $data);
    }

    public function subsoal(Request $request, $offset = 0)
    {
        $data['Create'] = $this->Create;
        $idkategori = $request->input('idkategori', '');
        $idsoal = $request->input('idsoal', '');
        $data['idkategori'] = $idkategori;
        $data['idsoal'] = $idsoal;
        if (function_exists('log_akses')) { log_akses('View', 'Melihat Daftar Data Setup Sub Soal'); }
        return view('kategori_soal_pmb.v_subsoal_pmb', $data);
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
        return view('kategori_soal_pmb.s_kategori_soal_pmb', $data);
    }

    public function search_soal(Request $request, $idkategori = 0)
    {
        $keyword = $request->input('keyword', '');
        $offset = 0;
        $limit = 50; // OPTIMIZED: Was 1000, reduced to 50 for better performance
        $jml = $this->service->count_all_soal($idkategori, $keyword);
        $data['offset'] = $offset;
        $data['query'] = $this->service->get_data_soal($limit, $offset, $idkategori, $keyword);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;
        return view('kategori_soal_pmb.s_soal_pmb', $data);
    }

    public function search_subsoal(Request $request, $idkategori = 0, $idsoal = 0)
    {
        $offset = 0;
        $limit = 50; // OPTIMIZED: Was 1000, reduced to 50 for better performance
        $jml = $this->service->count_all_subsoal($idkategori, $idsoal);
        $data['offset'] = $offset;
        $data['query'] = $this->service->get_data_subsoal($limit, $offset, $idkategori, $idsoal);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;
        $data['idkategori'] = $idkategori;
        return view('kategori_soal_pmb.s_subsoal_pmb', $data);
    }

    public function add() { $data['save'] = 1; return view('kategori_soal_pmb.f_kategori_soal_pmb', $data); }

    public function add_soal(Request $request)
    {
        $data['save'] = 1;
        $row = new \stdClass();
        $row->idkategori = $request->input('idkategori', '');
        $data['row'] = $row;
        return view('kategori_soal_pmb.f_soal_pmb', $data);
    }

    public function add_subsoal(Request $request)
    {
        $data['save'] = 1;
        $row = new \stdClass();
        $row->idsoal = $request->input('idsoal', '');
        $row->idkategori = $request->input('idkategori', '');
        $data['row'] = $row;
        return view('kategori_soal_pmb.f_subsoal_pmb', $data);
    }

    public function view($id) { $data['row'] = $this->service->get_id($id); $data['save'] = 2; return view('kategori_soal_pmb.f_kategori_soal_pmb', $data); }
    public function view_soal($id) { $data['row'] = $this->service->get_id_soal($id); $data['save'] = 2; return view('kategori_soal_pmb.f_soal_pmb', $data); }
    public function view_subsoal($id, Request $request) { $row = $this->service->get_id_subsoal($id); $row->idkategori = $request->input('idkategori', ''); $data['row'] = $row; $data['save'] = 2; return view('kategori_soal_pmb.f_subsoal_pmb', $data); }

    public function copy_soal($subaksi)
    {
        $data['data_kategori'] = $this->service->get_all_kategori();
        $data['row'] = $this->service->get_id($subaksi);
        $data['subaksi'] = $subaksi;
        $data['save'] = 1;
        return view('kategori_soal_pmb.f_copy_soal_pmb', $data);
    }

    public function save_copy_soal(Request $request, $subaksi)
    {
        $kategori = $request->input('kategori', '');
        $run_tambah = $this->service->copy_soal($subaksi, $kategori);
        echo $run_tambah ? '1' : '0';
    }

    public function save(Request $request, $save)
    {
        $id = $request->input('id', '');
        $nama = $request->input('nama', '');
        $input['nama'] = $nama;
        $cek = $this->service->checkDuplicateNama($nama, $id);
        if ($cek && isset($cek->id)) { echo "gagal"; exit; }
        if ($save == 1) {
            if (function_exists('logs')) { logs("Menambah data $nama pada tabel kategori_soal_pmb"); }
            echo $this->service->add($input);
        }
        if ($save == 2) {
            if (function_exists('logs')) { logs("Mengubah data $cek->nama menjadi $nama pada tabel kategori_soal_pmb"); }
            $this->service->edit($id, $input);
        }
    }

    public function save_soal(Request $request, $save)
    {
        $id = $request->input('id', '');
        $input['soal'] = $request->input('soal', '');
        $input['jawaban'] = $request->input('jawaban', '');
        $input['pilihana'] = $request->input('pilihana', '');
        $input['pilihanb'] = $request->input('pilihanb', '');
        $input['pilihanc'] = $request->input('pilihanc', '');
        $input['pilihand'] = $request->input('pilihand', '');
        $input['pilihane'] = $request->input('pilihane', '');
        $input['cerita'] = $request->input('cerita', '');
        if ($save == 1) {
            $idkategori = $request->input('idkategori', '');
            $input['idkategori'] = $idkategori;
            echo $this->service->add_soal($input);
        }
        if ($save == 2) {
            $this->service->edit_soal($id, $input);
        }
    }

    public function save_subsoal(Request $request, $save)
    {
        $id = $request->input('id', '');
        $input['soal'] = $request->input('soal', '');
        $input['jawaban'] = $request->input('jawaban', '');
        $input['pilihana'] = $request->input('pilihana', '');
        $input['pilihanb'] = $request->input('pilihanb', '');
        $input['pilihanc'] = $request->input('pilihanc', '');
        $input['pilihand'] = $request->input('pilihand', '');
        $input['pilihane'] = $request->input('pilihane', '');
        if ($save == 1) {
            $idsoal = $request->input('idsoal', '');
            $input['idsoal'] = $idsoal;
            $this->service->add_subsoal($input);
        }
        if ($save == 2) {
            $this->service->edit_subsoal($id, $input);
        }
    }

    public function delete(Request $request)
    {
        $checkid = $request->input('checkID', []);
        $removedIds = [];
        
        for ($x = 0; $x <= count($checkid) - 1; $x++) {
            if (function_exists('log_akses')) {
                log_akses('Hapus', 'Menghapus Data kategori_soal_pmb Dengan nama ' . get_field($checkid[$x], 'pmb_tbl_kategori_soal', 'nama'));
            }
            $this->service->delete($checkid[$x]);
            $removedIds[] = $checkid[$x];
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'kategori_soal_pmb_'
        ]);
    }

    public function delete_soal(Request $request)
    {
        $checkid = $request->input('checkID', []);
        $removedIds = [];
        
        for ($x = 0; $x <= count($checkid) - 1; $x++) {
            if (function_exists('log_akses')) {
                log_akses('Hapus', 'Menghapus Data soal_pmb Dengan id ' . $checkid[$x]);
            }
            $this->service->delete_soal($checkid[$x]);
            $removedIds[] = $checkid[$x];
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'kategori_soal_pmb_'
        ]);
    }

    public function delete_subsoal(Request $request)
    {
        $checkid = $request->input('checkID', []);
        $removedIds = [];
        
        for ($x = 0; $x <= count($checkid) - 1; $x++) {
            if (function_exists('log_akses')) {
                log_akses('Hapus', 'Menghapus Data sub_soal_pmb Dengan id ' . $checkid[$x]);
            }
            $this->service->delete_subsoal($checkid[$x]);
            $removedIds[] = $checkid[$x];
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'kategori_soal_pmb_'
        ]);
    }

    public function pdf(Request $request)
    {
        $gelombang = $request->input('gelombang', '');
        $jenis = $request->input('jenis', '');
        $data['query'] = $this->service->get_data(null, null, $gelombang, $jenis);

        $pdf = Pdf::loadView('kategori_soal_pmb.p_kategori_soal_pmb', $data);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Data_Kategori_Soal_PMB_' . date('Y-m-d') . '.pdf');
    }

    public function excel(Request $request)
    {
        $gelombang = $request->input('gelombang', '');
        $jenis = $request->input('jenis', '');
        $data = $this->service->get_data(null, null, $gelombang, $jenis);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Kategori Soal PMB');

        $row_num = 1;
        
        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'C');
        }

        $sheet->setCellValue('A'.$row_num, 'DATA KATEGORI SOAL PMB');
        $sheet->mergeCells('A'.$row_num.':C'.$row_num); 
        $sheet->getStyle('A'.$row_num)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row_num += 2; 
        $start_table_row = $row_num;
        
        $sheet->setCellValue('A'.$row_num, 'No.');
        $sheet->setCellValue('B'.$row_num, 'Nama Paket Soal');
        $sheet->setCellValue('C'.$row_num, 'Aksi');

        $sheet->getStyle('A'.$row_num.':C'.$row_num)->getFont()->setBold(true);
        $sheet->getStyle('A'.$row_num.':C'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A'.$row_num.':C'.$row_num)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFC000');
        $row_num++;

        $no = 1;
        if (!empty($data)) {
            foreach ($data as $row) {
                $row = (object) $row;
                
                $sheet->setCellValue('A'.$row_num, $no++);
                $sheet->setCellValue('B'.$row_num, $row->nama ?? '');
                $sheet->setCellValue('C'.$row_num, 'Kelola Soal');
                
                $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $row_num++;
            }
        } else {
            $sheet->setCellValue('A'.$row_num, 'Tidak ada data');
            $sheet->mergeCells('A'.$row_num.':C'.$row_num);
            $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row_num++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A'.$start_table_row.':C'.($row_num-1))->applyFromArray($styleBorder);

        foreach(range('A','C') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "data_kategori_soal_pmb_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function upload_file(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('pmb/soal'), $fileName);
            return response()->json(['location' => asset('pmb/soal/' . $fileName)]);
        }
        return response()->json(['error' => 'No file uploaded'], 500);
    }
}
