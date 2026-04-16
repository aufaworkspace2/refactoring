<?php

namespace App\Http\Controllers;

use App\Services\NilaiKegiatanSkpiService;
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

class NilaiKegiatanSkpiController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(NilaiKegiatanSkpiService $service)
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
            $this->Create = cek_level($levelUser, 'c_nilai_kegiatan_skpi', 'Create');
            $this->Update = cek_level($levelUser, 'c_nilai_kegiatan_skpi', 'Update');
            $this->Delete = cek_level($levelUser, 'c_nilai_kegiatan_skpi', 'Delete');

            return $next($request);
        });
    }

    /**
     * Display main view
     */
    public function index(Request $request, $offset = 0)
    {
        $data['Create'] = $this->Create;

        // Get jenis kategori kegiatan for dropdown
        $data['data_jenis_kategori'] = $this->service->getAllJenisKategoriKegiatan();

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Nilai Kegiatan SKPI');
        }

        return view('nilai_kegiatan_skpi.v_nilai_kegiatan_skpi', $data);
    }

    /**
     * Search with pagination
     */
    public function search(Request $request, $offset = 0)
    {
        $JenisKategoriID = $request->input('JenisKategoriID', '');

        $limit = 10;
        $jml = $this->service->count_all($JenisKategoriID);
        $data['offset'] = $offset;

        $query = $this->service->get_data($limit, $offset, $JenisKategoriID);
        // Add namaKegiatan to each row using get_field helper (same as CI3)
        foreach ($query as &$row) {
            $row['namaKegiatan'] = get_field($row['KegiatanID'], 'master_kegiatan','Nama');
        }
        
        $data['query'] = $query;
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);

        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('nilai_kegiatan_skpi.s_nilai_kegiatan_skpi', $data);
    }

    /**
     * Display add form
     */
    public function add(Request $request)
    {
        $data['save'] = 1;
        $data['row'] = null;

        // Get dropdown data
        $data['data_kegiatan'] = $this->service->getAllKegiatan();
        $data['data_kategori'] = $this->service->getAllKategoriKegiatan();

        return view('nilai_kegiatan_skpi.f_nilai_kegiatan_skpi', $data);
    }

    /**
     * Display view/edit form
     */
    public function view(Request $request, $id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['save'] = 2;

        // Get dropdown data
        $data['data_kegiatan'] = $this->service->getAllKegiatan();
        $data['data_kategori'] = $this->service->getAllKategoriKegiatan();

        return view('nilai_kegiatan_skpi.f_nilai_kegiatan_skpi', $data);
    }

    /**
     * Save data (add or update)
     */
    public function save(Request $request, $save)
    {
        $KategoriKegiatanID = $request->input('KategoriKegiatanID');
        $KegiatanID = $request->input('KegiatanID');
        $Point = $request->input('Point');
        $ID = $request->input('ID');

        $input['KategoriKegiatanID'] = $KategoriKegiatanID;
        $input['KegiatanID'] = $KegiatanID;
        $input['Point'] = $Point;
        $input['userID'] = Session::get('UserID');

        if ($save == 1) {
            $input['createAt'] = date('Y-m-d H:i:s');

            if (function_exists('logs')) {
                logs("Menambah data pada tabel " . request()->segment(1));
            }

            $this->service->add($input);
            return response()->json(['status' => 'success', 'id' => DB::getPdo()->lastInsertId()]);
        }

        if ($save == 2) {
            $input['updateAt'] = date('Y-m-d H:i:s');

            if (function_exists('logs')) {
                logs("Mengubah data pada tabel " . request()->segment(1));
            }

            $this->service->edit($ID, $input);
            return response()->json(['status' => 'success']);
        }
    }

    /**
     * Delete records
     */
    public function delete(Request $request)
    {
        $checkid = $request->input('checkID', []);
        $removedIds = [];

        foreach ($checkid as $id) {
            if (function_exists('log_akses')) {
                $nama = $this->service->getKegiatanName(
                    DB::table('nilai_kegiatan')->where('ID', $id)->value('KegiatanID')
                );
                log_akses('Hapus', "Menghapus Data Nilai Kegiatan SKPI Dengan Kegiatan {$nama}");
            }

            $this->service->delete($id);
            $removedIds[] = $id;
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'nilai_kegiatan_'
        ]);
    }

    /**
     * Export to PDF
     */
    public function pdf(Request $request)
    {
        $JenisKategoriID = $request->input('JenisKategoriID', '');

        $query = $this->service->get_all_data($JenisKategoriID);

        // Add namaKegiatan to each row using get_field helper (same as CI3)
        foreach ($query as &$row) {
            $row['namaKegiatan'] = get_field($row['KegiatanID'] ?? '', 'master_kegiatan');
        }

        $data['query'] = $query;
        $data['title'] = 'Daftar Data Nilai Kegiatan SKPI';

        $pdf = Pdf::loadView('nilai_kegiatan_skpi.p_nilai_kegiatan_skpi', $data);
        $pdf->setPaper('A4', 'P');
        return $pdf->stream('data_nilai_kegiatan_skpi_' . date('d-m-Y') . '.pdf');
    }

    /**
     * Export to Excel
     */
    public function excel(Request $request)
    {
        $JenisKategoriID = $request->input('JenisKategoriID', '');

        $data = $this->service->get_all_data($JenisKategoriID);

        // Add namaKegiatan to each row using get_field helper (same as CI3)
        foreach ($data as &$row) {
            $row['namaKegiatan'] = get_field($row['KegiatanID'] ?? '', 'master_kegiatan');
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Nilai Kegiatan SKPI');

        $row_num = 1;

        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'E');
        }

        $sheet->setCellValue('A'.$row_num, strtoupper('Daftar Data Nilai Kegiatan SKPI'));
        $sheet->mergeCells('A'.$row_num.':E'.$row_num);
        $sheet->getStyle('A'.$row_num)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row_num += 2;

        $start_table_row = $row_num;

        $sheet->setCellValue('A'.$row_num, 'No.');
        $sheet->setCellValue('B'.$row_num, 'Kegiatan');
        $sheet->setCellValue('C'.$row_num, 'Tingkat/Sebagai Kegiatan');
        $sheet->setCellValue('D'.$row_num, 'Jenis');
        $sheet->setCellValue('E'.$row_num, 'Point');

        $sheet->getStyle('A'.$row_num.':E'.$row_num)->getFont()->setBold(true);
        $sheet->getStyle('A'.$row_num.':E'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A'.$row_num.':E'.$row_num)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A'.$row_num.':E'.$row_num)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFC000');
        $row_num++;

        $no = 0;
        if (!empty($data)) {
            foreach ($data as $row) {
                $sheet->setCellValue('A'.$row_num, ++$no);

                $sheet->setCellValueExplicit('B'.$row_num, $row['namaKegiatan'] ?? '', DataType::TYPE_STRING);
                $sheet->setCellValue('C'.$row_num, $row['namaKategori'] ?? '');
                $sheet->setCellValue('D'.$row_num, $row['namaJenis'] ?? '');
                $sheet->setCellValue('E'.$row_num, $row['Point'] ?? 0);

                $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('C'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

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

        if (!function_exists('cetak_kop_phpspreadsheet')) {
            $sheet->getColumnDimension('A')->setAutoSize(true);
        }
        $sheet->getColumnDimension('B')->setWidth(55);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "data_nilai_kegiatan_skpi_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
