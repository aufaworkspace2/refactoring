<?php

namespace App\Http\Controllers;

use App\Services\TranskripMahasiswaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TranskripMahasiswaController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(TranskripMahasiswaService $service)
    {
        $this->service = $service;

        $this->middleware(function ($request, $next) {
            // Language setup
            $lang = Session::get('language');
            if (!$lang && !request()->cookie('language')) {
                $lang = 'indonesia';
                Session::put('language', $lang);
            }

            // Map legacy language names to Laravel locales
            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);

            return $next($request);
        });
    }

    /**
     * Main index page
     * CI3: C_transkripmahasiswa->index()
     */
    public function index(Request $request)
    {
        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Transkrip Mahasiswa');
        }

        $data['programs'] = DB::table('program')->orderBy('Nama', 'ASC')->get();
        $data['prodis'] = DB::table('programstudi')->orderBy('Nama', 'ASC')->get();
        $data['statuses'] = DB::table('statusmahasiswa')->orderBy('Nama', 'ASC')->get();
        $data['tahun_masuk'] = DB::table('mahasiswa')
            ->select('TahunMasuk')
            ->whereNotNull('TahunMasuk')
            ->groupBy('TahunMasuk')
            ->orderBy('TahunMasuk', 'DESC')
            ->get();
        $data['tahuns'] = DB::table('tahun')->orderBy('TahunID', 'DESC')->get();

        return view('transkripmahasiswa.v_transkripmahasiswa', $data);
    }

    /**
     * Search mahasiswa for transkrip
     * CI3: C_transkripmahasiswa->search()
     */
    public function search(Request $request, $offset = 0)
    {
        $programID = $request->input('ProgramID', '');
        $prodiID = $request->input('ProdiID', '');
        $statusMhswID = $request->input('StatusMhswID', '');
        $tahunMasuk = $request->input('TahunMasuk', '');
        $semesterMasuk = $request->input('SemesterMasuk', '');
        $keyword = $request->input('keyword', '');

        $limit = 10;

        $data['offset'] = $offset;
        $data['query'] = $this->service->getMahasiswaList($limit, $offset, $programID, $prodiID, $statusMhswID, $tahunMasuk, $semesterMasuk, $keyword);
        $jml = $this->service->countMahasiswaList($programID, $prodiID, $statusMhswID, $tahunMasuk, $semesterMasuk, $keyword);

        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);

        return view('transkripmahasiswa.s_transkripmahasiswa', $data);
    }

    /**
     * Load info mahasiswa
     * CI3: C_transkripmahasiswa->loadinfo()
     */
    public function loadinfo($id)
    {
        $data['ID'] = $id;
        $data['ProgramID'] = get_field($id, 'mahasiswa', 'ProgramID');

        return view('transkripmahasiswa.v_loadinfoasli', $data);
    }

    /**
     * Edit transkrip view (Add manual)
     * CI3: C_transkripmahasiswa->edit()
     */
    public function edit(Request $request)
    {
        $mhswID = $request->input('MhswID');
        $data['MhswID'] = $mhswID;
        $data['d_mhs'] = DB::table('mahasiswa')->where('ID', $mhswID)->first();

        if (!$data['d_mhs']) {
            return response('Mahasiswa tidak ditemukan');
        }

        // Get Mata Kuliah not in transkrip
        $sql = "SELECT mk.* FROM (
                    SELECT ID, MKKode, Nama
                    FROM detailkurikulum
                    GROUP BY MKKode, ID, Nama
                ) mk 
                LEFT JOIN transkrip ON transkrip.MKKode = mk.MKKode AND transkrip.NPM = ?
                WHERE transkrip.ID IS NULL";
        
        $data['detail'] = DB::select($sql, [$data['d_mhs']->NPM]);

        // Get Nilai Huruf
        $data['bobots'] = DB::table('bobot')
            ->select('Nilai')
            ->groupBy('Nilai')
            ->orderBy('Nilai', 'ASC')
            ->get();

        return view('transkripmahasiswa.v_inputtranskrip', $data);
    }

    /**
     * Edit transkrip data
     * CI3: C_transkripmahasiswa->edit_transkrip()
     */
    public function edit_transkrip($mhswID)
    {
        $data = $this->service->get_transkrip_for_edit($mhswID);

        if (!$data) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }

        $data['save'] = 2;

        if (function_exists('log_akses')) {
            log_akses('Edit', 'Mengedit Transkrip Mahasiswa ' . ($data['d_mhs']['NPM'] ?? ''));
        }

        return view('transkripmahasiswa.s_transkripmahasiswaedit', $data);
    }

    /**
     * Edit KHS data
     * CI3: C_transkripmahasiswa->edit_khs()
     */
    public function edit_khs($mhswID)
    {
        $data = $this->service->get_khs_for_edit($mhswID);

        if (!$data) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }

        $data['save'] = 2;

        // Get academic years for this student
        $data['tahuns'] = DB::table('tahun')
            ->select('tahun.ID', 'tahun.Nama', 'tahun.TahunID', 'tahun.ProsesBuka')
            ->join('rencanastudi', 'tahun.ID', '=', 'rencanastudi.TahunID')
            ->where('rencanastudi.MhswID', $mhswID)
            ->orWhere('tahun.ProsesBuka', 1)
            ->groupBy('tahun.ID', 'tahun.Nama', 'tahun.TahunID', 'tahun.ProsesBuka')
            ->orderBy('tahun.TahunID', 'DESC')
            ->get();

        return view('transkripmahasiswa.s_khsedit', $data);
    }

    /**
     * Search KHS data for edit
     * CI3: C_transkripmahasiswa->search_edit_khs()
     */
    public function search_edit_khs(Request $request)
    {
        $mhswID = $request->input('MhswID');
        $tahunID = $request->input('TahunID');

        $query = $this->service->search_edit_khs($mhswID, $tahunID);

        return response()->json($query);
    }

    /**
     * Add KHS form
     * CI3: C_transkripmahasiswa->add_khs()
     */
    public function add_khs(Request $request)
    {
        $mhswID = $request->input('MhswID');
        $data['MhswID'] = $mhswID;
        $data['d_mhs'] = DB::table('mahasiswa')->where('ID', $mhswID)->first();

        if (!$data['d_mhs']) {
            return response('Mahasiswa tidak ditemukan');
        }

        // Get academic years
        $data['tahuns'] = DB::table('tahun')
            ->select('ID', 'Nama', 'TahunID', 'ProsesBuka')
            ->orderBy('TahunID', 'DESC')
            ->get();

        // Get Mata Kuliah not in KHS
        $sql = "SELECT mk.* FROM (
                    SELECT ID, MKKode, Nama
                    FROM detailkurikulum
                    GROUP BY MKKode, ID, Nama
                ) mk 
                LEFT JOIN khs ON khs.MKKode = mk.MKKode AND khs.NPM = ?
                WHERE khs.ID IS NULL
                GROUP BY mk.MKKode, mk.ID, mk.Nama";
        
        $data['detail'] = DB::select($sql, [$data['d_mhs']->NPM]);

        // Get Nilai Huruf
        $data['bobots'] = DB::table('bobot')->orderBy('Nilai', 'ASC')->get();

        return view('transkripmahasiswa.f_khs', $data);
    }

    /**
     * Get transkrip (generate from nilai)
     * CI3: C_transkripmahasiswa->getTranskrip()
     */
    public function getTranskrip(Request $request)
    {
        $mhswID = $request->input('MhswID');
        $type = $request->input('type', 1);

        $result = $this->service->generate_transkrip($mhswID, $type, Session::get('UserID'));

        return response()->json($result);
    }

    /**
     * Generate KHS (from nilai)
     * CI3: C_transkripmahasiswa->gen_khs()
     */
    public function gen_khs(Request $request)
    {
        $mhswID = $request->input('MhswID');
        $tahunID = $request->input('TahunID');
        $type = $request->input('type', 1);

        $result = $this->service->generate_khs($mhswID, $tahunID, $type, Session::get('UserID'));

        return response($result['status']);
    }

    /**
     * Save transkrip single entry
     * CI3: C_transkripmahasiswa->save()
     */
    public function save(Request $request)
    {
        $mhswID = $request->input('MhswID');
        $detailkurikulumid = $request->input('detailkurikulumid');
        $semester = $request->input('Semester');
        $totalSKS = $request->input('TotalSKS');
        $nilaiHuruf = $request->input('NilaiHuruf');

        $result = $this->service->save_transkrip($mhswID, $detailkurikulumid, $semester, $totalSKS, $nilaiHuruf, Session::get('UserID'));

        return response()->json(['success' => $result ? 1 : 0]);
    }

    /**
     * Save KHS single entry
     * CI3: C_transkripmahasiswa->save_khs()
     */
    public function save_khs(Request $request)
    {
        $mhswID = $request->input('MhswID');
        $detailkurikulumid = $request->input('detailkurikulumid');
        $tahunID = $request->input('TahunID');
        $nilaiHuruf = $request->input('NilaiHuruf');

        $result = $this->service->save_khs($mhswID, $detailkurikulumid, $tahunID, $nilaiHuruf, Session::get('UserID'));

        return response()->json(['success' => $result ? 1 : 0]);
    }

    /**
     * Update transkrip inline (single field)
     * CI3: C_transkripmahasiswa->update()
     */
    public function update(Request $request)
    {
        $id = $request->input('ID');
        $param = $request->input('param');
        $val = $request->input('val');

        $result = $this->service->update_transkrip_field($id, $param, $val);

        return response()->json(['success' => $result ? 1 : 0]);
    }

    /**
     * Batch update transkrip (revision)
     * CI3: C_transkripmahasiswa->saverevisinilai()
     */
    public function saverevisinilai(Request $request)
    {
        $ID = $request->input('ID', []);
        $Semester = $request->input('Semester', []);
        $MKKode = $request->input('MKKode', []);
        $NamaMataKuliah = $request->input('NamaMataKuliah', []);
        $TotalSKS = $request->input('TotalSKS', []);
        $Nilai = $request->input('Nilai', []);

        $data = [];
        foreach ($ID as $index => $vId) {
            $data[] = [
                'ID' => $vId,
                'Semester' => $Semester[$index] ?? null,
                'MKKode' => $MKKode[$index] ?? null,
                'NamaMataKuliah' => $NamaMataKuliah[$index] ?? null,
                'TotalSKS' => $TotalSKS[$index] ?? null,
                'NilaiHuruf' => $Nilai[$index] ?? null,
            ];
        }

        $success = $this->service->batch_update_transkrip($data);

        return response()->json(['success' => $success]);
    }

    /**
     * Batch update KHS (revision)
     * CI3: C_transkripmahasiswa->saverevisinilaikhs()
     */
    public function saverevisinilaikhs(Request $request)
    {
        $ID = $request->input('ID', []);
        $Semester = $request->input('Semester', []);
        $MKKode = $request->input('MKKode', []);
        $NamaMataKuliah = $request->input('NamaMataKuliah', []);
        $TotalSKS = $request->input('TotalSKS', []);
        $Nilai = $request->input('Nilai', []);
        $Bobot = $request->input('Bobot', []);

        $data = [];
        foreach ($ID as $index => $vId) {
            $data[] = [
                'ID' => $vId,
                'Semester' => $Semester[$index] ?? null,
                'MKKode' => $MKKode[$index] ?? null,
                'NamaMataKuliah' => $NamaMataKuliah[$index] ?? null,
                'TotalSKS' => $TotalSKS[$index] ?? null,
                'NilaiHuruf' => $Nilai[$index] ?? null,
                'Bobot' => $Bobot[$index] ?? null,
            ];
        }

        $success = $this->service->batch_update_khs($data);

        return response()->json(['success' => $success]);
    }

    /**
     * Delete transkrip
     * CI3: C_transkripmahasiswa->delete()
     */
    public function delete(Request $request)
    {
        $checkid = $request->input('checkID');
        $removedIds = [];

        if ($checkid && count($checkid) > 0) {
            foreach ($checkid as $id) {
                $this->service->delete_transkrip($id);
                $removedIds[] = $id;
            }

            return response()->json([
                'status' => '1',
                'message' => 'Data Transkrip yang anda pilih berhasil dihapus !',
                'removed_ids' => $removedIds,
                'class_prefix' => 'transkrip_'
            ]);
        }

        return response()->json([
            'status' => '0',
            'message' => 'Data Transkrip yang anda pilih gagal dihapus !'
        ]);
    }

    /**
     * Delete KHS data
     * CI3: C_transkripmahasiswa->deleteDataKHS()
     */
    public function deleteDataKHS(Request $request)
    {
        $checkID = $request->input('checkID');
        $totalRow = 0;

        if ($checkID && count($checkID) > 0) {
            $totalRow = $this->service->delete_khs($checkID);

            if ($totalRow > 0) {
                return response()->json([
                    'status' => true,
                    'message' => 'Data Berhasil Dihapus !'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Maaf Data Gagal Dihapus !'
                ]);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Maaf, Silahan Pilih Data Yang Akan Dihapus Terlebih Dahulu !.'
        ]);
    }

    /**
     * Print transkrip PDF
     * CI3: C_transkripmahasiswa->cetak()
     */
    public function cetak(Request $request, $mhswID, $jenis = 'ASLI', $bahasa = 1)
    {
        $dataInput = [
            'nomor' => $request->get('nomor', ''),
            'nomorSeriIjazah' => $request->get('nomorSeriIjazah', ''),
            'transkrip' => $request->get('transkrip', ''),
            'TanggalLulus' => $request->get('tgl', ''),
            'JudulSkripsi' => $request->get('JudulSkripsi', true),
            'JudulSkripsiEn' => $request->get('JudulSkripsiEn', true),
            'tgl_cetak' => $request->get('tgl_cetak', ''),
        ];

        $data = $this->service->get_transkrip_data_for_print($mhswID, $dataInput);

        if (!$data) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }

        $data['jenis'] = $jenis;

        // Get custom print settings
        $setupTranskrip = get_setup_app("setup_cetak_transkrip");
        $transkripCustom = json_decode($setupTranskrip->metadata ?? '{}', true);

        $ukuran = 'Legal';
        $orientasi = 'P';
        $file = 'p_transkripmahasiswa.php';

        if (!empty($transkripCustom)) {
            $ukuran = $transkripCustom['size'] ?? $ukuran;
            $orientasi = $transkripCustom['orientation'] ?? $orientasi;

            if (isset($transkripCustom['custom_prodi'][$data['IDProdiID']])) {
                $ukuran = $transkripCustom['custom_prodi'][$data['IDProdiID']]['size'];
                $orientasi = $transkripCustom['custom_prodi'][$data['IDProdiID']]['orientation'];
                $file = $transkripCustom['custom_prodi'][$data['IDProdiID']]['file'];
            }
        }

        // Render view
        $viewPath = 'transkripmahasiswa.' . str_replace('.php', '', $file);

        if (defined('CLIENT_PATH') && file_exists(CLIENT_PATH . '/cetak/' . $file)) {
            // Custom view from client path
            $content = view('custom.cetak.' . str_replace('.php', '', $file), $data)->render();
        } else {
            $content = view($viewPath, $data)->render();
        }

        $pdf = Pdf::loadHTML($content);
        $pdf->setPaper($ukuran, $orientasi);

        return $pdf->stream('Transkrip_' . $data['NPM'] . '_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export transkrip to Excel
     * CI3: C_transkripmahasiswa->excel()
     */
    public function excel(Request $request, $mhswID, $jenis = 'ASLI', $bahasa = 1)
    {
        $dataInput = [
            'nomor' => $request->get('nomor', ''),
            'nomorSeriIjazah' => $request->get('nomorSeriIjazah', ''),
            'transkrip' => $request->get('transkrip', ''),
            'TanggalLulus' => $request->get('tgl', ''),
            'JudulSkripsi' => $request->get('JudulSkripsi', true),
            'JudulSkripsiEn' => $request->get('JudulSkripsiEn', true),
        ];

        $data = $this->service->get_transkrip_data_for_print($mhswID, $dataInput);

        if (!$data) {
            return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan.');
        }

        $data['jenis'] = $jenis;

        // Create Excel spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Transkrip');

        // Header
        $sheet->setCellValue('A1', 'TRANSKRIP NILAI MAHASISWA');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rowNum = 3;
        $sheet->setCellValue('A' . $rowNum, 'NPM');
        $sheet->setCellValue('B' . $rowNum, ':');
        $sheet->setCellValue('C' . $rowNum, $data['NPM']);
        $rowNum++;
        $sheet->setCellValue('A' . $rowNum, 'Nama');
        $sheet->setCellValue('B' . $rowNum, ':');
        $sheet->setCellValue('C' . $rowNum, $data['Nama']);
        $rowNum++;
        $sheet->setCellValue('A' . $rowNum, 'Program Studi');
        $sheet->setCellValue('B' . $rowNum, ':');
        $sheet->setCellValue('C' . $rowNum, $data['ProdiID']);
        $rowNum += 2;

        // Table header
        $sheet->setCellValue('A' . $rowNum, 'No');
        $sheet->setCellValue('B' . $rowNum, 'Kode MK');
        $sheet->setCellValue('C' . $rowNum, 'Mata Kuliah');
        $sheet->setCellValue('D' . $rowNum, 'SKS');
        $sheet->setCellValue('E' . $rowNum, 'Nilai');

        $sheet->getStyle('A' . $rowNum . ':E' . $rowNum)->getFont()->setBold(true);
        $sheet->getStyle('A' . $rowNum . ':E' . $rowNum)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFC000');
        $rowNum++;

        // Data
        $no = 1;
        foreach ($data['query'] ?? [] as $row) {
            $row = (object) $row;
            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValue('B' . $rowNum, $row->MKKode ?? '');
            $sheet->setCellValue('C' . $rowNum, $row->NamaMataKuliah ?? '');
            $sheet->setCellValue('D' . $rowNum, $row->TotalSKS ?? '');
            $sheet->setCellValue('E' . $rowNum, $row->NilaiHuruf ?? '');
            $rowNum++;
        }

        // Auto-size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "data_transkrip_" . $data['NPM'] . "_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Batch print KHS PDF
     * CI3: C_transkripmahasiswa->cetak_all()
     */
    public function cetak_all(Request $request)
    {
        $programID = $request->get('ProgramID');
        $prodiID = $request->get('ProdiID');
        $tahunMasuk = $request->get('TahunMasuk');
        $tahunID = $request->get('TahunID');

        $data = $this->service->get_khs_batch_data($programID, $prodiID, $tahunMasuk, $tahunID);

        // Get custom KHS settings
        $setupKhs = get_setup_app("setup_cetak_khs");
        $khsCustom = json_decode($setupKhs->metadata ?? '{}', true);

        $ukuran = $khsCustom['size'] ?? 'A5';
        $orientation = $khsCustom['orientation'] ?? 'L';

        // Render view
        if (defined('CLIENT_PATH') && file_exists(CLIENT_PATH . '/cetak/p_khs_mahasiswa_batch.php')) {
            $content = view('custom.cetak.p_khs_mahasiswa_batch', $data)->render();
        } else {
            $content = view('hasilstudi.KHS_NEW_batch', $data)->render();
        }

        $pdf = Pdf::loadHTML($content);
        $pdf->setPaper($ukuran, $orientation);

        return $pdf->stream('KHS_Batch_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Show upload form for nomor transkrip
     * CI3: C_transkripmahasiswa->add_upload_nomor()
     */
    public function add_upload_nomor()
    {
        $data['save'] = 1;

        return view('transkripmahasiswa.f_upload_ex_nomor_transkrip', $data);
    }

    /**
     * Generate template Excel for nomor transkrip upload
     * CI3: C_transkripmahasiswa->template_upload_nomor()
     */
    public function template_upload_nomor()
    {
        $spreadsheet = new Spreadsheet();

        // Sheet 1: Format (empty template)
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Format');

        $sheet1->setCellValue('A1', 'NPM');
        $sheet1->setCellValue('B1', 'Nomor Ijazah Nasional (PIN)');
        $sheet1->setCellValue('C1', 'Nomor Seri Ijazah');
        $sheet1->setCellValue('D1', 'Nomor Seri Transkrip');
        $sheet1->setCellValue('E1', 'Judul Skripsi');
        $sheet1->setCellValue('F1', 'Judul Skripsi Inggris');
        $sheet1->setCellValue('G1', 'Tanggal Yudisium');
        $sheet1->setCellValue('H1', 'Tanggal Cetak');

        $sheet1->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet1->getStyle('A1:H1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFC000');

        // Sheet 2: Tata Cara Isi
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Tata Cara Isi Template');

        $sheet2->setCellValue('A1', 'No');
        $sheet2->setCellValue('B1', 'Keterangan');
        $sheet2->getStyle('A1:B1')->getFont()->setBold(true);

        $sheet2->setCellValue('A2', '1');
        $sheet2->setCellValue('B2', 'Isian NPM sesuai dengan yang ada di data master mahasiswa');

        $sheet2->getColumnDimension('A')->setWidth(5);
        $sheet2->getColumnDimension('B')->setWidth(60);

        // Return to first sheet
        $spreadsheet->setActiveSheetIndex(0);

        $filename = "template_upload_nomor_transkrip.xlsx";

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Upload Excel for nomor transkrip
     * CI3: C_transkripmahasiswa->upload_excel_nomor()
     */
    public function upload_excel_nomor(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx|max:20000',
        ]);

        $file = $request->file('file_excel');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $uploadPath = public_path('excel_up/upload');

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $file->move($uploadPath, $fileName);
        $filePath = $uploadPath . '/' . $fileName;

        try {
            $result = $this->service->process_upload_nomor_transkrip($filePath);

            return response()->json($result);
        } catch (\Exception $e) {
            // Clean up file on error
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            return response()->json([
                'status' => false,
                'title' => "Data Gagal Diproses",
                'message' => 'Mohon Maaf, Terjadi kesalahan: ' . $e->getMessage(),
                'type' => 'error',
                'Persen' => 0
            ]);
        }
    }

    /**
     * Legacy: Get transkrip (old version - deprecated)
     * CI3: C_transkripmahasiswa->getTranskrip_11122018()
     * Kept for backward compatibility
     */
    public function getTranskrip_legacy(Request $request)
    {
        $mhswID = $request->input('MhswID');
        $dMhs = DB::table('mahasiswa')->where('ID', $mhswID)->first();

        if (!$dMhs) {
            return response('0');
        }

        // Check if transkrip exists
        $count = DB::table('transkrip')->where('NPM', $dMhs->NPM)->count();

        return response($count > 0 ? '1' : '0');
    }

    /**
     * Legacy: Generate KHS (old version - deprecated)
     * CI3: C_transkripmahasiswa->gen_khs_11122018()
     * Kept for backward compatibility
     */
    public function gen_khs_legacy(Request $request)
    {
        $mhswID = $request->input('MhswID');
        $tahunID = $request->input('TahunID');
        $type = $request->input('type', 1);

        // Use stored procedure or helper function if exists
        if (function_exists('get_gen_khs')) {
            $insert = get_gen_khs($mhswID, $tahunID, $type);
            $count = count($insert);

            if ($count > 0) {
                DB::table('nilai')->insert($insert);
            }

            return response(DB::getPdo()->rowCount());
        }

        return response('0');
    }
}
