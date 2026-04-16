<?php

namespace App\Http\Controllers;

use App\Services\KategoriKegiatanSkpiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class KategoriKegiatanSkpiController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(KategoriKegiatanSkpiService $service)
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
            $this->Create = cek_level($levelUser, 'c_kategori_kegiatan_skpi', 'Create');
            $this->Update = cek_level($levelUser, 'c_kategori_kegiatan_skpi', 'Update');
            $this->Delete = cek_level($levelUser, 'c_kategori_kegiatan_skpi', 'Delete');

            return $next($request);
        });
    }

    /**
     * Display main view
     */
    public function index(Request $request, $offset = 0)
    {
        $data['Create'] = $this->Create;

        // Get jenis kategori for dropdown
        $data['data_jenis_kategori'] = $this->service->getJenisKategori();

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Kategori Kegiatan SKPI');
        }

        return view('kategori_kegiatan_skpi.v_kategori_kegiatan_skpi', $data);
    }

    /**
     * Search with pagination
     */
    public function search(Request $request, $offset = 0)
    {
        $JenisKategoriID = $request->input('JenisKategoriID', '');
        $keyword = $request->input('keyword', '');

        $limit = 10;
        $jml = $this->service->count_all($JenisKategoriID, $keyword);
        $data['offset'] = $offset;

        $data['query'] = $this->service->get_data($limit, $offset, $JenisKategoriID, $keyword);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);

        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        // Get jenis kategori for display
        $data['data_jenis_kategori'] = $this->service->getJenisKategori();

        return view('kategori_kegiatan_skpi.s_kategori_kegiatan_skpi', $data);
    }

    /**
     * Display add form
     */
    public function add(Request $request)
    {
        $data['save'] = 1;
        $data['row'] = null;
        $data['data_jenis_kategori'] = $this->service->getJenisKategori();

        return view('kategori_kegiatan_skpi.f_kategori_kegiatan_skpi', $data);
    }

    /**
     * Display view/edit form
     */
    public function view(Request $request, $id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['save'] = 2;
        $data['data_jenis_kategori'] = $this->service->getJenisKategori();

        return view('kategori_kegiatan_skpi.f_kategori_kegiatan_skpi', $data);
    }

    /**
     * Save data (add or update)
     */
    public function save(Request $request, $save)
    {
        $Nama = $request->input('Nama');
        $ID = $request->input('ID');
        $JenisKategoriID = $request->input('JenisKategoriID');

        $input['Nama'] = $Nama;
        $input['JenisKategoriID'] = $JenisKategoriID;
        $input['userID'] = Session::get('UserID');

        if ($save == 1) {
            $input['createAt'] = date('Y-m-d H:i:s');

            if (function_exists('logs')) {
                logs("Menambah data $Nama pada tabel " . request()->segment(1));
            }

            $this->service->add($input);
            return response()->json(['status' => 'success', 'id' => DB::getPdo()->lastInsertId()]);
        }

        if ($save == 2) {
            $input['updateAt'] = date('Y-m-d H:i:s');

            if (function_exists('logs')) {
                logs("Mengubah data $Nama pada tabel " . request()->segment(1));
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
                $nama = DB::table('kategori_kegiatan')->where('ID', $id)->value('Nama');
                log_akses('Hapus', "Menghapus Data Kategori Kegiatan Dengan Nama {$nama}");
            }

            $this->service->delete($id);
            $removedIds[] = $id;
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'kategori_kegiatan_'
        ]);
    }

    /**
     * Export to PDF
     */
    public function pdf(Request $request)
    {
        $JenisKategoriID = $request->input('JenisKategoriID', '');
        $keyword = $request->input('keyword', '');

        $data['query'] = $this->service->get_data('', '', $JenisKategoriID, $keyword);
        $data['title'] = 'Data Kategori Kegiatan SKPI';

        $pdf = Pdf::loadView('kategori_kegiatan_skpi.p_kategori_kegiatan_skpi', $data);
        $pdf->setPaper('A4', 'P');
        return $pdf->stream('data_kategori_kegiatan_skpi_' . date('d-m-Y') . '.pdf');
    }

    /**
     * Export to Excel
     */
    public function excel(Request $request)
    {
        $JenisKategoriID = $request->input('JenisKategoriID', '');
        $keyword = $request->input('keyword', '');

        $offset = 0;
        $limit = 100000000000;
        $data = $this->service->get_data($limit, $offset, $JenisKategoriID, $keyword);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Kategori Kegiatan SKPI');

        $row_num = 1;

        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'B');
        }

        $sheet->setCellValue('A'.$row_num, 'DATA KATEGORI KEGIATAN SKPI');
        $sheet->mergeCells('A'.$row_num.':C'.$row_num);
        $sheet->getStyle('A'.$row_num)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row_num += 2;
        $start_table_row = $row_num;

        $sheet->setCellValue('A'.$row_num, 'No.');
        $sheet->setCellValue('B'.$row_num, 'Nama');
        $sheet->setCellValue('C'.$row_num, 'Jenis Kategori');

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
                $jenisKategori = DB::table('jenis_kategori_kegiatan')
                    ->where('ID', $row->JenisKategoriID ?? 0)
                    ->value('Nama');

                $sheet->setCellValue('A'.$row_num, $no++);
                $sheet->setCellValue('B'.$row_num, $row->Nama ?? '');
                $sheet->setCellValue('C'.$row_num, $jenisKategori ?? '');

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

        $filename = "Data_Kategori_Kegiatan_SKPI_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
