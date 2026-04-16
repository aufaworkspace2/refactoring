<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JadwalUsmPmbService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Barryvdh\DomPDF\Facade\Pdf;

class JadwalUsmPmbController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(JadwalUsmPmbService $service)
    {
        $this->service = $service;

        $this->middleware(function ($request, $next) {
            if (!Session::get('username')) { return redirect('/'); }
            $lang = Session::get('language');
            if (!$lang && !request()->cookie('language')) { $lang = 'indonesia'; Session::put('language', $lang); }
            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);
            $levelUser = Session::get('LevelUser');
            $this->Create = cek_level($levelUser, 'c_jadwal_usm_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_jadwal_usm_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_jadwal_usm_pmb', 'Delete');
            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        $data['Create'] = $this->Create;
        if (function_exists('log_akses')) { log_akses('View', 'Melihat Daftar Data Jadwal USM PMB'); }
        return view('jadwal_usm_pmb.v_jadwal_usm_pmb', $data);
    }

    public function detail(Request $request, $offset = 0)
    {
        $data['Create'] = $this->Create;
        $jadwalusm_id = $request->input('jadwalusm_id', '');
        $data['jadwalusm_id'] = $jadwalusm_id;
        if (function_exists('log_akses')) { log_akses('View', 'Melihat Daftar Data Jadwal Detail USM PMB'); }
        return view('jadwal_usm_pmb.v_jadwal_detail_usm_pmb', $data);
    }

    public function search(Request $request, $offset = 0)
    {
        $keyword = $request->input('keyword', '');
        $gelombang = $request->input('gelombang', '');
        $jenis = $request->input('jenis', []);
        
        $limit = 10;
        $jml = $this->service->count_all($keyword, $gelombang, $jenis);
        $data['offset'] = $offset;
        
        $query = $this->service->get_data($limit, $offset, $keyword, $gelombang, $jenis);
        
        // Process query to add related data
        $tempQuery = [];
        foreach ($query as $row) {
            $row = (object) $row;
            $gelombang_id = $row->gelombang ?? '';
            
            // Get gelombang data
            $gelombang_data = DB::table('pmb_tbl_gelombang')->where('id', $gelombang_id)->first();
            $nama_tahun = $gelombang_data ? get_field($gelombang_data->tahun_id, 'tahun') : '';
            $row->namagelombang = $nama_tahun . " - " . ($gelombang_data->kode ?? '') . " - " . ($gelombang_data->nama ?? '');
            
            // Get ruangan names
            $ruang_ids = !empty($row->ruang) ? explode(',', $row->ruang) : [];
            $ruangan_names = [];
            if ($ruang_ids) {
                $ruangan_data = DB::table('ruang')->whereIn('ID', $ruang_ids)->get();
                foreach ($ruangan_data as $r) {
                    $ruangan_names[] = $r->Nama;
                }
            }
            $row->ruangan = $ruangan_names ? '<ol style="text-align:left;">' . implode('', array_map(fn($n) => '<li>'.$n.'</li>', $ruangan_names)) . '</ol>' : '';
            
            // Get jenis ujin names
            $jenis_ujin_ids = !empty($row->jenis_ujin) ? explode(',', $row->jenis_ujin) : [];
            $jenis_ujin_names = [];
            if ($jenis_ujin_ids) {
                $jenis_ujin_data = DB::table('pmb_edu_jenisusm')->whereIn('id', $jenis_ujin_ids)->get();
                foreach ($jenis_ujin_data as $j) {
                    $jenis_ujin_names[] = $j->nama;
                }
            }
            $row->jenis_ujin_text = $jenis_ujin_names ? '<ol style="text-align:left;">' . implode('', array_map(fn($n) => '<li>'.$n.'</li>', $jenis_ujin_names)) . '</ol>' : '';
            
            $tempQuery[] = $row;
        }
        
        $data['query'] = $tempQuery;
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;
        
        return view('jadwal_usm_pmb.s_jadwal_usm_pmb', $data);
    }

    public function search_detail(Request $request, $jadwalusm_id = 0)
    {
        if (empty($jadwalusm_id)) { die('No ACCESS'); }
        
        $offset = 0;
        $limit = 1000;
        $jml = $this->service->count_all_detail($jadwalusm_id);
        $data['offset'] = $offset;
        
        $data['query'] = $this->service->get_data_detail($limit, $offset, $jadwalusm_id);
        $data['jadwalusm'] = get_id($jadwalusm_id, 'pmb_edu_jadwalusm');
        $data['jadwalusm_id'] = $jadwalusm_id;
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;
        
        return view('jadwal_usm_pmb.s_jadwal_detail_usm_pmb', $data);
    }

    public function add()
    {
        $data['save'] = 1;
        
        // Load dropdown data directly from database (like CI3)
        $data['data_gelombang'] = DB::table('pmb_tbl_gelombang')
            ->orderBy('nama', 'ASC')
            ->get();
        
        $data['data_ruangan'] = DB::table('ruang')
            ->where('RuangPMB', '1')
            ->orderBy('Nama', 'ASC')
            ->get();
        
        $data['data_jenis_usm'] = DB::table('pmb_edu_jenisusm')
            ->orderBy('nama', 'ASC')
            ->get();
        
        return view('jadwal_usm_pmb.f_jadwal_usm_pmb', $data);
    }
    public function add_detail(Request $request) { $data['save'] = 1; $row = new \stdClass(); $row->jadwalusm_id = $request->input('jadwalusm_id', ''); $data['row'] = $row; return view('jadwal_usm_pmb.f_jadwal_detail_usm_pmb', $data); }
    public function view($id) { $data['row'] = $this->service->get_id($id); $data['save'] = 2; return view('jadwal_usm_pmb.f_jadwal_usm_pmb', $data); }
    public function view_detail($id) { $data['row'] = $this->service->get_id_detail($id); $data['save'] = 2; return view('jadwal_usm_pmb.f_jadwal_detail_usm_pmb', $data); }

    public function save(Request $request, $save)
    {
        $id = $request->input('id', '');
        $kode = $request->input('kode', '');
        
        $cek = $this->service->checkDuplicateKode($kode, $id);
        if ($cek && isset($cek->id)) { echo "gagal"; exit; }
        
        if ($save == 1) {
            $input['gelombang'] = $request->input('gelombang', '');
            $input['ruang'] = implode(',', $request->input('ruang', []));
            $input['jenis_ujin'] = implode(',', $request->input('jenis_ujin', []));
            $input['kode'] = $kode;
            $input['tgl_ujian'] = $request->input('tgl_ujian', '');
            $input['jam_mulai'] = $request->input('jam_mulai', '');
            $input['jam_selesai'] = $request->input('jam_selesai', '');
            $input['ada_ujian_online'] = $request->input('ada_ujian_online', 0);
            $input['kategori_soal_id'] = $request->input('kategori_soal_id', '');
            $input['tanggal_mulai_ujian_online'] = $request->input('tanggal_mulai_ujian_online', '');
            $input['tanggal_selesai_ujian_online'] = $request->input('tanggal_selesai_ujian_online', '');
            $input['jam_mulai_ujian_online'] = $request->input('jam_mulai_ujian_online', '');
            $input['jam_selesai_ujian_online'] = $request->input('jam_selesai_ujian_online', '');
            $input['durasi_ujian_online'] = $request->input('durasi_ujian_online', '');
            $input['jumlah_soal_ujian_online'] = $request->input('jumlah_soal_ujian_online', '');
            $input['jenis_ujian_online'] = $request->input('jenis_ujian_online', '');
            
            if (function_exists('logs')) { logs("Menambah data $kode pada tabel jadwal_usm_pmb"); }
            $insert_id = $this->service->add($input);
            
            // Insert detail
            $i = 0;
            foreach ($request->input('ruang', []) as $r) {
                DB::table('pmb_edu_jadwalusm_detail')->insert([
                    'jadwalusm_id' => $insert_id,
                    'ruang_id' => $r,
                    'urut' => ++$i,
                    'created_at' => date('Y-m-d')
                ]);
            }
        }
        if ($save == 2) {
            if (function_exists('logs')) { logs("Mengubah data $cek->kode menjadi $kode pada tabel jadwal_usm_pmb"); }
            $this->service->edit($id, ['kode' => $kode, 'tgl_ujian' => $request->input('tgl_ujian', ''), 'jam_mulai' => $request->input('jam_mulai', ''), 'jam_selesai' => $request->input('jam_selesai', '')]);
        }
    }

    public function delete(Request $request)
    {
        $checkid = $request->input('checkID', []);
        $berhasil = 0;
        $gagal = 0;
        $removedIds = [];

        foreach ($checkid as $id) {
            $jadwalusm_detail_jml = $this->service->count_jadwalusm_detail($id);
            if ($jadwalusm_detail_jml == 0) {
                ++$berhasil;
                if (function_exists('log_akses')) {
                    log_akses('Hapus', 'Menghapus Data pmb_edu_jadwalusm Dengan kode ' . get_field($id, 'pmb_edu_jadwalusm', 'kode'));
                }
                $this->service->delete($id);
                $removedIds[] = $id;
            } else {
                ++$gagal;
            }
        }

        return response()->json([
            'status' => $berhasil > 0 ? 'success' : 'error',
            'message' => "$berhasil Data Berhasil Dihapus. $gagal Data Gagal Dihapus.",
            'removed_ids' => $removedIds,
            'class_prefix' => 'jadwal_usm_pmb_'
        ]);
    }

    public function delete_detail(Request $request)
    {
        $checkid = $request->input('checkID', []);
        $removedIds = [];

        foreach ($checkid as $id) {
            $row_s = DB::table('pmb_edu_jadwalusm_detail')->where('id', $id)->first();
            if ($row_s) {
                DB::table('pmb_edu_jadwalusm_detail')->where('id', $id)->delete();
                DB::table('pmb_edu_map_peserta')->where('idjadwalusm_detail', $id)->delete();

                // Update ruang in jadwalusm
                $jadwalusm = DB::table('pmb_edu_jadwalusm')->where('id', $row_s->jadwalusm_id)->first();
                if ($jadwalusm) {
                    $ruangan = $jadwalusm->ruang ? explode(',', $jadwalusm->ruang) : [];
                    $ruangan = array_diff($ruangan, [$row_s->ruang_id]);
                    $ruangan_text = implode(',', $ruangan);
                    DB::table('pmb_edu_jadwalusm')->where('id', $row_s->jadwalusm_id)->update(['ruang' => $ruangan_text]);
                }
            }
            if (function_exists('log_akses')) { log_akses('Hapus', 'Menghapus Data jadwalusm_detail_pmb Dengan id ' . $id); }
            echo '<script>$(".jadwal_usm_pmb_'.$id.'").remove();</script>';
        }
    }

    public function pdf(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $data['query'] = $this->service->get_data(null, null, $keyword, '');

        $pdf = Pdf::loadView('jadwal_usm_pmb.p_jadwal_usm_pmb', $data);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Data_Jadwal_USM_PMB_' . date('Y-m-d') . '.pdf');
    }
    
    public function excel(Request $request)
    {
        $keyword = $request->input('keyword', '');

        $data = $this->service->get_data(null, null, $keyword, '');

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Jadwal Ujian PMB');

        $row_num = 1;
        
        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'H');
        }

        $sheet->setCellValue('A'.$row_num, 'DATA JADWAL UJIAN PMB');
        $sheet->mergeCells('A'.$row_num.':H'.$row_num); 
        $sheet->getStyle('A'.$row_num)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row_num += 2; 
        $start_table_row = $row_num;
        
        $sheet->setCellValue('A'.$row_num, 'No.');
        $sheet->setCellValue('B'.$row_num, 'Gelombang');
        $sheet->setCellValue('C'.$row_num, 'Kode Jadwal');
        $sheet->setCellValue('D'.$row_num, 'Tanggal Ujian');
        $sheet->setCellValue('E'.$row_num, 'Jam Ujian');
        $sheet->setCellValue('F'.$row_num, 'Ruangan');
        $sheet->setCellValue('G'.$row_num, 'Jenis Ujian');
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
                $sheet->setCellValue('B'.$row_num, $row->namagelombang ?? '');
                $sheet->setCellValue('C'.$row_num, $row->kode ?? '');
                
                // Tanggal Ujian
                $tanggal = '';
                if (isset($row->tgl_ujian) && $row->tgl_ujian) {
                    $tanggal = date('d/m/Y', strtotime($row->tgl_ujian));
                }
                $sheet->setCellValue('D'.$row_num, $tanggal);
                
                // Jam Ujian
                $jam = '';
                if (isset($row->jam_mulai) && isset($row->jam_selesai)) {
                    $jam = $row->jam_mulai . ' - ' . $row->jam_selesai;
                }
                $sheet->setCellValue('E'.$row_num, $jam);
                
                $sheet->setCellValue('F'.$row_num, strip_tags($row->ruangan ?? ''));
                $sheet->setCellValue('G'.$row_num, strip_tags($row->jenis_ujin_text ?? ''));
                $sheet->setCellValue('H'.$row_num, 'Detail');
                
                $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('H'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
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

        $filename = "data_jadwal_usm_pmb_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
