<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GelombangPmbService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Barryvdh\DomPDF\Facade\Pdf;

class GelombangPmbController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(GelombangPmbService $service)
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

            // Map legacy language names to Laravel locales
            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);

            $levelUser = Session::get('LevelUser');

            $this->Create = cek_level($levelUser, 'c_gelombang_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_gelombang_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_gelombang_pmb', 'Delete');

            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        $data['Create'] = $this->Create;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Gelombang Pendaftaran');
        }

        return view('gelombang_pmb.v_gelombang_pmb', $data);
    }

    public function detail(Request $request, $offset = 0)
    {
        $data['Create'] = $this->Create;
        $gelombang_id = $request->input('gelombang_id', '');
        $data['gelombang_id'] = $gelombang_id;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Gelombang Pendaftaran');
        }

        return view('gelombang_pmb.v_gelombang_detail_pmb', $data);
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

        return view('gelombang_pmb.s_gelombang_pmb', $data);
    }

    public function search_detail(Request $request)
    {
        $gelombang_id = $request->input('gelombang_id', 0);
        $keyword = $request->input('keyword', '');

        $offset = 0;
        $limit = 1000;
        $jml = $this->service->count_all_detail($gelombang_id, $keyword);
        $data['offset'] = $offset;
        $data['query'] = $this->service->get_data_detail($limit, $offset, $gelombang_id, $keyword);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;
        $data['gelombang_id'] = $gelombang_id;

        return view('gelombang_pmb.s_gelombang_detail_pmb', $data);
    }

    public function edit_tanggal_batch(Request $request)
    {
        $gelombang_id = $request->input('gelombang_id', '');
        $tgl1 = $request->input('tgl1', '');
        $tgl2 = $request->input('tgl2', '');

        $result = $this->service->updateTanggalBatch($gelombang_id, $tgl1, $tgl2);

        $totalBerhasil = $result['totalBerhasil'];
        $totalData = $result['totalData'];
        $totalGagal = $result['totalGagal'];

        $status = ($totalBerhasil == $totalData) ? '1' : '0';
        $message = "Data berhasil diubah";
        if ($totalBerhasil != $totalData) {
            $message = "$totalGagal data gagal diubah";
        }

        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    public function add()
    {
        $data['save'] = 1;

        return view('gelombang_pmb.f_gelombang_pmb', $data);
    }

    public function add_detail(Request $request)
    {
        $data['save'] = 1;
        $row = new \stdClass();
        $row->gelombang_id = $request->input('gelombang_id', '');
        $data['row'] = $row;

        return view('gelombang_pmb.f_gelombang_detail_pmb', $data);
    }

    public function view($id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['save'] = 2;

        return view('gelombang_pmb.f_gelombang_pmb', $data);
    }

    public function view_detail($id)
    {
        $data['row'] = $this->service->get_id_detail($id);
        $data['save'] = 2;

        // Get custom wording
        $setuwording = DB::table('setup')->where('app_id', 'custom_wording_pmb')->first();
        if ($setuwording) {
            $arr_wording = json_decode($setuwording->metadata, true);
            foreach ($arr_wording as $key => $wording) {
                $data['list_' . $key] = $wording;
            }
        }

        return view('gelombang_pmb.f_gelombang_detail_pmb', $data);
    }

    public function save(Request $request, $save)
    {
        $id = $request->input('id', '');
        $kode = $request->input('kode', '');
        $nama = $request->input('nama', '');
        $tahun_id = $request->input('tahun_id', '');
        $tahunmasuk = $request->input('tahunmasuk', '');
        $GelombangKe = $request->input('GelombangKe', '');
        $date_start = $request->input('date_start', '');
        $date_end = $request->input('date_end', '');

        $input['kode'] = $kode;
        $input['nama'] = $nama;
        $input['tahun_id'] = $tahun_id;
        $input['tahunmasuk'] = $tahunmasuk;
        $input['GelombangKe'] = $GelombangKe;
        $input['date_start'] = $date_start;
        $input['date_end'] = $date_end;

        // Check if biaya semester exists
        $hasBiaya = $this->service->checkBiayaSemester($tahun_id);
        if (!$hasBiaya) {
            echo "gagal_belum_ada_biaya";
            exit;
        }

        // Check duplicate kode
        $cek = $this->service->checkDuplicateKode($kode, $id);
        if ($cek && isset($cek->id)) {
            echo "gagal";
            exit;
        }

        // Check duplicate nama in same tahun
        $cek2 = $this->service->checkDuplicateNama($nama, $tahun_id, $id);
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

    public function save_detail(Request $request, $save)
    {
        $id = $request->input('id', '');
        $pilihan_pendaftaran_id = $request->input('pilihan_pendaftaran_id', '');
        $biaya_semester_satu_id = $request->input('biaya_semester_satu_id', '');
        $prodi_id = $request->input('prodi_id', '');
        $biaya = $request->input('biaya', '');
        $date_end = $request->input('date_end', '');
        $date_start = $request->input('date_start', '');
        $date_herregistrasi_end = $request->input('date_herregistrasi_end', '');

        // Get pilihan pendaftaran data
        $pilihan_pendaftaran = DB::table('pmb_pilihan_pendaftaran')->where('id', $pilihan_pendaftaran_id)->first();

        $program_id = $pilihan_pendaftaran->program_id ?? '';
        $jenis_pendaftaran = $pilihan_pendaftaran->jenis_pendaftaran ?? '';
        $jalur = $pilihan_pendaftaran->jalur ?? '';

        $input['pilihan_pendaftaran_id'] = $pilihan_pendaftaran_id;
        $input['biaya_semester_satu_id'] = $biaya_semester_satu_id;
        $input['program_id'] = $program_id;
        $input['prodi_id'] = $prodi_id;
        $input['jenis_pendaftaran'] = $jenis_pendaftaran;
        $input['jalur'] = $jalur;
        $input['biaya'] = $biaya;
        $input['date_end'] = $date_end;
        $input['date_start'] = $date_start;

        if ($save == 1) {
            $gelombang_id = $request->input('gelombang_id', '');
            $input['gelombang_id'] = $gelombang_id;
            $input['created_at'] = date('Y-m-d H:i:s');

            $insertId = $this->service->add_detail($input);
            echo $insertId;
        }
        if ($save == 2) {
            $this->service->edit_detail($id, $input);
        }
    }

    public function change_tahunmasuk(Request $request)
    {
        $tahun_id = $request->input('tahun_id', '');

        $tahunmasuk = $this->service->getTahunMasuk($tahun_id);

        echo $tahunmasuk;
    }

    public function delete(Request $request)
    {
        $checkid = $request->input('checkID', []);
        $removedIds = [];

        for ($x = 0; $x <= count($checkid) - 1; $x++) {
            if (function_exists('log_akses')) {
                log_akses('Hapus', 'Menghapus Data gelombang_pmb Dengan nama ' . get_field($checkid[$x], 'pmb_tbl_gelombang', 'nama'));
            }
            $this->service->delete($checkid[$x]);
            $removedIds[] = $checkid[$x];
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'gelombang_pmb_'
        ]);
    }

    public function delete_detail(Request $request)
    {
        $checkid = $request->input('checkID', []);
        $removedIds = [];

        for ($x = 0; $x <= count($checkid) - 1; $x++) {
            if (function_exists('log_akses')) {
                log_akses('Hapus', 'Menghapus Data gelombang_detail_pmb Dengan id ' . $checkid[$x]);
            }
            $this->service->delete_detail($checkid[$x]);
            $removedIds[] = $checkid[$x];
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'gelombang_detail_'
        ]);
    }

    public function pdf(Request $request)
    {
        $keyword = $request->input('keyword', '');

        $data['query'] = $this->service->get_data(null, null, $keyword, '');

        $pdf = Pdf::loadView('gelombang_pmb.p_gelombang_pmb', $data);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Data_Gelombang_PMB_' . date('Y-m-d') . '.pdf');
    }

    public function excel(Request $request)
    {
        $keyword = $request->input('keyword', '');

        $data = $this->service->get_data(null, null, $keyword, '');

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Gelombang PMB');

        $row_num = 1;
        
        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'H');
        }

        $sheet->setCellValue('A'.$row_num, 'DATA GELOMBANG PMB');
        $sheet->mergeCells('A'.$row_num.':H'.$row_num); 
        $sheet->getStyle('A'.$row_num)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row_num += 2; 
        $start_table_row = $row_num;
        
        $sheet->setCellValue('A'.$row_num, 'No.');
        $sheet->setCellValue('B'.$row_num, 'Kode');
        $sheet->setCellValue('C'.$row_num, 'Nama');
        $sheet->setCellValue('D'.$row_num, 'Tahun Akademik');
        $sheet->setCellValue('E'.$row_num, 'Tahun Masuk Mahasiswa');
        $sheet->setCellValue('F'.$row_num, 'Gelombang Ke');
        $sheet->setCellValue('G'.$row_num, 'Status Pendaftaran');
        $sheet->setCellValue('H'.$row_num, 'Aksi');

        $sheet->getStyle('A'.$row_num.':H'.$row_num)->getFont()->setBold(true);
        $sheet->getStyle('A'.$row_num.':H'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A'.$row_num.':H'.$row_num)->getFill()
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
                
                // Tahun Akademik
                $tahun_nama = '';
                if (isset($row->tahun_id) && function_exists('get_field')) {
                    $tahun_nama = get_field($row->tahun_id, 'tahun');
                }
                $sheet->setCellValue('D'.$row_num, $tahun_nama);
                
                $sheet->setCellValue('E'.$row_num, $row->tahunmasuk ?? '');
                $sheet->setCellValue('F'.$row_num, $row->GelombangKe ?? '');
                
                // Status Pendaftaran
                $status = 'Tidak ada pendaftaran terbuka';
                if (isset($row->PendaftaranTerbuka) && $row->PendaftaranTerbuka > 0) {
                    $status = $row->PendaftaranTerbuka . ' Pendaftaran terbuka';
                }
                $sheet->setCellValue('G'.$row_num, $status);
                $sheet->setCellValue('H'.$row_num, 'Lihat Detail');
                
                $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $row_num++;
            }
        } else {
            $sheet->setCellValue('A'.$row_num, 'Tidak ada data');
            $sheet->mergeCells('A'.$row_num.':H'.$row_num);
            $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row_num++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A'.$start_table_row.':H'.($row_num-1))->applyFromArray($styleBorder);

        foreach(range('A','H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "data_gelombang_pmb_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function change_gelombang_detail_pmb(Request $request)
    {
        $ProdiID = $request->input('ProdiID', '');
        $ProgramID = $request->input('ProgramID', '');
        $gelombang = $request->input('gelombang', '');

        $where = '';
        if ($ProdiID) {
            $where .= " AND FIND_IN_SET('$ProdiID',pmb_tbl_gelombang_detail.prodi_id) != 0";
        }
        if ($ProgramID) {
            $where .= " AND FIND_IN_SET('$ProgramID',pmb_tbl_gelombang_detail.program_id) != 0";
        }

        $sql_gelombang_detail = "SELECT pmb_tbl_gelombang_detail.*,pmb_tbl_gelombang.tahun_id,pmb_tbl_gelombang.nama,pmb_tbl_gelombang.kode,tahun.Nama as nama_tahun FROM `pmb_tbl_gelombang_detail` join pmb_tbl_gelombang on pmb_tbl_gelombang.id=pmb_tbl_gelombang_detail.gelombang_id join tahun on tahun.ID=pmb_tbl_gelombang.tahun_id WHERE 1
        AND pmb_tbl_gelombang.id = '$gelombang' $where order by tahun.TahunID DESC,pmb_tbl_gelombang.kode ASC";

        $run_gelombang_detail = DB::select($sql_gelombang_detail);

        $jml_gelombang_detail = count($run_gelombang_detail);

        if ($jml_gelombang_detail > 0) {
            echo '<option value="">-- Pilih Gelombang Detail --</option>';
            $query = $run_gelombang_detail;
            $nama_prodi = [];
            $nama_program = [];
            $nama_jalur = [];

            foreach ($query as $row_gelombang_detail) {
                $row = (array) $row_gelombang_detail;

                if (!isset($nama_prodi[$row['prodi_id']])) {
                    $get_prodi = DB::table('programstudi')->where('ID', $row['prodi_id'])->first();
                    if ($get_prodi) {
                        $nama_prodi[$row['prodi_id']] = get_field($get_prodi->JenjangID, "jenjang") . " | " . $get_prodi->Nama;
                    }
                }
                if (!isset($nama_program[$row['program_id']])) {
                    $nama_program[$row['program_id']] = get_field($row['program_id'], "program");
                }
                if (!isset($nama_jalur[$row['jalur']])) {
                    $nama_jalur[$row['jalur']] = get_field($row['jalur'], "pmb_edu_jalur_pendaftaran");
                }

                $id = $row["id"];
                $nama = $nama_program[$row['program_id']] . " | " . $nama_prodi[$row['prodi_id']] . ' | ' . $nama_jalur[$row['jalur']] . ' | ' . rupiah($row['biaya']);

                echo '<option value="' . $id . '" >' . $nama . '</option>';
            }
        } else {
            echo '<option value="">-- Pilih Gelombang Detail --</option>';
        }
    }

    public function change_penawaran(Request $request)
    {
        $pilihan_pendaftaran_id = $request->input('pilihan_pendaftaran_id', '');
        $prodi_id = $request->input('prodi_id', '');
        $biaya_semester_satu_id = $request->input('biaya_semester_satu_id', '');
        $gelombang_id = $request->input('gelombang_id', '');

        $result = $this->service->get_penawaran($pilihan_pendaftaran_id, $prodi_id, $biaya_semester_satu_id, $gelombang_id);

        if (count($result) > 0) {
            echo '<option value="">-- Pilih Penawaran Biaya Pendaftaran --</option>';
            foreach ($result as $row_penawaran) {
                $row = (array) $row_penawaran;
                $id = $row["ID"];
                $prodi_id = $row["ProdiID"];
                $prodi = DB::table('programstudi')->where('ID', $prodi_id)->first();
                $nama_jenjang = get_field($prodi->JenjangID ?? '', 'jenjang');
                $nama = "Prodi " . $nama_jenjang . "-" . ($prodi->Nama ?? '') . ' | Biaya Formulir : ' . rupiah($row['formulir']);

                $formulir = $row["formulir"];
                $selected = ($biaya_semester_satu_id == $id) ? 'selected' : '';

                echo '<option value="' . $id . '" ' . $selected . ' formulir="' . $formulir . '">' . $nama . '</option>';
            }
        } else {
            echo '<option value="">-- Tidak Penawaran Biaya Pendaftaran Tersedia Sesuai Pilihan --</option>';
        }
    }

    public function get_detail_pilihan_pendaftaran(Request $request)
    {
        $pilihan_pendaftaran_id = $request->input('pilihan_pendaftaran_id', '');

        $data_return = $this->service->get_detail_pilihan_pendaftaran($pilihan_pendaftaran_id);

        echo json_encode($data_return);
    }

    public function generate_gelombang(Request $request)
    {
        $data['save'] = 1;
        $data['gelombang_id'] = $request->input('gelombang_id', '');
        $data['Create'] = $this->Create;

        return view('gelombang_pmb.v_generate_gelombang_pmb', $data);
    }

    public function search_generate_gelombang(Request $request, $gelombang_id = 0)
    {
        $offset = 0;
        $limit = 10000000000000000;

        $program = $request->input('program', '');
        $prodi = $request->input('prodi', '');
        $jalur = $request->input('jalur', '');
        $status = $request->input('status', '');
        $keyword = $request->input('keyword', '');

        $jml = $this->service->count_data_generate($gelombang_id, $program, $prodi, $jalur, $status);
        $data['offset'] = $offset;

        $data['query'] = $this->service->get_data_generate($gelombang_id, $program, $prodi, $jalur, $status);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);

        // Get data program
        $arrProgram = array();
        foreach (get_all('program') as $row) {
            $arrProgram[$row->ID] = $row->Nama;
        }

        // Get data prodi
        $arrProdi = array();
        foreach (get_all('programstudi') as $row) {
            $jenjang = get_field($row->JenjangID, 'jenjang');
            $arrProdi[$row->ID] = $jenjang . " " . $row->Nama;
        }

        // Get data jalur
        $arrJalur = array();
        foreach (get_all('pmb_edu_jalur_pendaftaran') as $row) {
            $arrJalur[$row->id] = $row->nama;
        }

        // Get data jenis pendaftaran
        $arrJenisPendaftaran = array();
        foreach (get_all('jenis_pendaftaran') as $row) {
            $arrJenisPendaftaran[$row->ID] = $row->Nama;
        }

        // Get biaya semester
        $arrBiayaSemester = array();
        foreach (DB::table('biaya_semester')->get() as $row) {
            $arrBiayaSemester[$row->ID] = $row->Jumlah;
        }

        // Get biaya
        $arrBiaya = array();
        foreach (DB::table('biaya')->get() as $row) {
            $arrBiaya[$row->ID] = $row->Jumlah;
        }

        $data['biaya'] = $arrBiaya;
        $data['biayaSemester'] = $arrBiayaSemester;
        $data['jenisPendaftaran'] = $arrJenisPendaftaran;
        $data['jalur'] = $arrJalur;
        $data['prodi'] = $arrProdi;
        $data['program'] = $arrProgram;
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;
        $data['gelombang_id'] = $gelombang_id;

        return view('gelombang_pmb.s_generate_gelombang_pmb', $data);
    }

    public function proses_generate_gelombang(Request $request)
    {
        $gelombang_id = $request->input('gelombang_id', '');
        $checkid = $request->input('checkID', []);

        $total = 0;
        $sukses = 0;
        $gagal = 0;

        for ($x = 0; $x <= count($checkid) - 1; $x++) {
            $total++;
            $dataGelombangDetail = explode(";", $checkid[$x]);
            $programid = $dataGelombangDetail[0];
            $prodiid = $dataGelombangDetail[1];
            $jalur = $dataGelombangDetail[2];
            $jenisPendaftaran = $dataGelombangDetail[3];
            $biayaSemesterSatu = $dataGelombangDetail[4];
            $biayaPendaftaran = $dataGelombangDetail[5];

            // Get data gelombang master
            $gelombangMaster = DB::table('pmb_tbl_gelombang')->where('id', $gelombang_id)->first();

            // Get data biaya
            $dataBiaya = DB::table('biaya')->where('ID', $biayaPendaftaran)->first();

            // Check pilihan pendaftaran
            $dataPilihanPendaftaran = DB::table('pmb_pilihan_pendaftaran')
                ->where('program_id', $programid)
                ->whereRaw('FIND_IN_SET("'.$jenisPendaftaran.'",jenis_pendaftaran) <> 0')
                ->where('jalur', $jalur)
                ->where('aktif', '1')
                ->where('tahun_id', $gelombangMaster->tahun_id)
                ->first();

            if ($dataPilihanPendaftaran) {
                $inputData = array();
                $inputData['pilihan_pendaftaran_id'] = $dataPilihanPendaftaran->id;
                $inputData['gelombang_id'] = $gelombang_id;
                $inputData['jalur'] = $jalur;
                $inputData['jenis_pendaftaran'] = $jenisPendaftaran;
                $inputData['program_id'] = $programid;
                $inputData['prodi_id'] = $prodiid;
                $inputData['biaya_semester_satu_id'] = $biayaSemesterSatu;
                $inputData['date_start'] = $gelombangMaster->date_start;
                $inputData['date_end'] = $gelombangMaster->date_end;
                $inputData['biaya'] = $dataBiaya->Jumlah;
                $inputData['created_at'] = date('Y-m-d H:i:s');

                $status = $this->service->add_detail($inputData);
                if ($status) {
                    $sukses++;
                } else {
                    $gagal++;
                }
            }
        }

        if ($sukses == $total) {
            $result['status'] = 1;
            $result['message'] = "Proses generate $sukses data berhasil";
        } else if ($sukses > 0 && $gagal > 0) {
            $result['status'] = 1;
            $result['message'] = "Proses generate $sukses data berhasil namun $gagal data gagal";
        } else if ($gagal == $total) {
            $result['status'] = 0;
            $result['message'] = "Proses generate $gagal data gagal";
        }

        echo json_encode($result);
    }
}
