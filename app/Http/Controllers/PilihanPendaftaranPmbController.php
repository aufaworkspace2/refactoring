<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PilihanPendaftaranPmbService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Barryvdh\DomPDF\Facade\Pdf;

class PilihanPendaftaranPmbController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(PilihanPendaftaranPmbService $service)
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

            $this->Create = cek_level($levelUser, 'c_pilihan_pendaftaran_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_pilihan_pendaftaran_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_pilihan_pendaftaran_pmb', 'Delete');

            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        $data['Create'] = $this->Create;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Pilihan Pendaftaran');
        }

        return view('pilihan_pendaftaran_pmb.v_pilihan_pendaftaran_pmb', $data);
    }

    public function search(Request $request, $offset = 0)
    {
        $keyword = $request->input('keyword', '');
        $tahun_id = $request->input('tahun_id', '');

        $limit = 10;
        $jml = $this->service->count_all($keyword, $tahun_id);
        $data['offset'] = $offset;

        $data['query'] = $this->service->get_data($limit, $offset, $keyword, $tahun_id);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('pilihan_pendaftaran_pmb.s_pilihan_pendaftaran_pmb', $data);
    }

    public function add()
    {
        $data['save'] = 1;

        return view('pilihan_pendaftaran_pmb.f_pilihan_pendaftaran_pmb', $data);
    }

    public function add_detail(Request $request)
    {
        $data['save'] = 1;
        $row = new \stdClass();
        $row->gelombang_id = $request->input('gelombang_id', '');
        $data['row'] = $row;

        return view('pilihan_pendaftaran_pmb.f_gelombang_detail_pmb', $data);
    }

    public function view($id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['save'] = 2;

        return view('pilihan_pendaftaran_pmb.f_pilihan_pendaftaran_pmb', $data);
    }

    public function save(Request $request, $save)
    {
        $id = $request->input('id', '');
        $tahun_id = $request->input('tahun_id', '');
        $program_id = $request->input('program_id', '');
        $nama = $request->input('nama', '');
        $jenis_pendaftaran = $request->input('jenis_pendaftaran', []);
        $master_diskon_id_list = $request->input('master_diskon_id_list', []);
        $jalur = $request->input('jalur', '');

        $user = Session::get('UserID');

        $input['nama'] = $nama;
        $input['tahun_id'] = $tahun_id;
        $input['program_id'] = $program_id;
        $input['jenis_pendaftaran'] = implode(",", $jenis_pendaftaran);
        $input['master_diskon_id_list'] = implode(",", $master_diskon_id_list);
        $input['jalur'] = $jalur;
        $input['user_update'] = $user;

        $cek = $this->service->checkDuplicate($nama, $tahun_id, $id);

        if ($cek && isset($cek->id)) {
            echo "gagal";
        } else {
            if ($save == 1) {
                $input['created_at'] = date('Y-m-d H:i:s');
                $input['user_create'] = $user;

                if (function_exists('logs')) {
                    logs("Menambah data $nama pada tabel " . request()->segment(1));
                }
                $this->service->add($input);
                echo DB::getPdo()->lastInsertId();
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
                log_akses('Hapus', 'Menghapus Data pilihan_pendaftaran_pmb Dengan nama ' . get_field($checkid[$x], 'pmb_pilihan_pendaftaran', 'nama'));
            }
            $this->service->delete($checkid[$x]);
            $removedIds[] = $checkid[$x];
        }

        return response()->json([
            'status' => 'success',
            'removed_ids' => $removedIds,
            'class_prefix' => 'pilihan_pendaftaran_pmb_'
        ]);
    }

    public function pdf(Request $request)
    {
        $keyword = $request->input('keyword', '');

        $data['query'] = $this->service->get_data(null, null, $keyword, '');

        $pdf = Pdf::loadView('pilihan_pendaftaran_pmb.p_pilihan_pendaftaran_pmb', $data);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Data_Pilihan_Pendaftaran_PMB_' . date('Y-m-d') . '.pdf');
    }

    public function excel(Request $request)
    {
        $keyword = $request->input('keyword', '');

        $data = $this->service->get_data(null, null, $keyword, '');

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Pilihan Pendaftaran');

        $row_num = 1;
        
        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'H');
        }

        $sheet->setCellValue('A'.$row_num, 'DATA PILIHAN PENDAFTARAN');
        $sheet->mergeCells('A'.$row_num.':H'.$row_num); 
        $sheet->getStyle('A'.$row_num)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row_num += 2; 
        $start_table_row = $row_num;
        
        $sheet->setCellValue('A'.$row_num, 'No.');
        $sheet->setCellValue('B'.$row_num, 'Nama');
        $sheet->setCellValue('C'.$row_num, 'Tahun');
        $sheet->setCellValue('D'.$row_num, 'Program');
        $sheet->setCellValue('E'.$row_num, 'Jalur');
        $sheet->setCellValue('F'.$row_num, 'Jenis Pendaftaran');
        $sheet->setCellValue('G'.$row_num, 'Beasiswa/Diskon');
        $sheet->setCellValue('H'.$row_num, 'Aktif');

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
                $sheet->setCellValue('B'.$row_num, $row->nama ?? '');
                
                // Tahun
                $tahun_nama = '';
                if (isset($row->tahun_id) && function_exists('get_field')) {
                    $tahun_nama = get_field($row->tahun_id, 'tahun');
                }
                $sheet->setCellValue('C'.$row_num, $tahun_nama);
                
                // Program
                $program_nama = '';
                if (isset($row->program_id) && $row->program_id) {
                    $programs = explode(",", $row->program_id);
                    $prog_names = [];
                    foreach ($programs as $p) {
                        if (function_exists('get_field')) {
                            $prog_names[] = get_field($p, 'program');
                        }
                    }
                    $program_nama = implode(", ", $prog_names);
                }
                $sheet->setCellValue('D'.$row_num, $program_nama);
                
                // Jalur
                $jalur_nama = '';
                if (isset($row->jalur) && $row->jalur) {
                    $jalurs = explode(",", $row->jalur);
                    $jalur_names = [];
                    foreach ($jalurs as $j) {
                        if (function_exists('get_field')) {
                            $jalur_names[] = get_field($j, 'pmb_edu_jalur_pendaftaran');
                        }
                    }
                    $jalur_nama = implode(", ", $jalur_names);
                }
                $sheet->setCellValue('E'.$row_num, $jalur_nama);
                
                // Jenis Pendaftaran
                $jp_nama = '';
                if (isset($row->jenis_pendaftaran) && $row->jenis_pendaftaran) {
                    $jps = explode(",", $row->jenis_pendaftaran);
                    $jp_names = [];
                    foreach ($jps as $jp) {
                        if (function_exists('get_field')) {
                            $jp_names[] = get_field($jp, 'jenis_pendaftaran');
                        }
                    }
                    $jp_nama = implode(", ", $jp_names);
                }
                $sheet->setCellValue('F'.$row_num, $jp_nama);
                
                // Beasiswa/Diskon
                $diskon_nama = 'Tidak Ada Beasiswa/Diskon';
                if (isset($row->master_diskon_id_list) && $row->master_diskon_id_list) {
                    $diskons = explode(",", $row->master_diskon_id_list);
                    $diskon_names = [];
                    foreach ($diskons as $d) {
                        if (function_exists('get_field')) {
                            $diskon_names[] = get_field($d, 'master_diskon');
                        }
                    }
                    $diskon_nama = implode(", ", $diskon_names);
                }
                $sheet->setCellValue('G'.$row_num, $diskon_nama);
                
                // Aktif
                $aktif = (isset($row->aktif) && $row->aktif == '1') ? 'Aktif' : 'Tidak Aktif';
                $sheet->setCellValue('H'.$row_num, $aktif);
                
                $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
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

        $filename = "data_pilihan_pendaftaran_pmb_" . date('d-m-Y') . ".xlsx";

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

    public function changediskon(Request $request)
    {
        $tahun_id = $request->input('tahun_id', '');
        $program_id = $request->input('program_id', '');
        $jenis_pendaftaran = $request->input('jenis_pendaftaran', []);
        $jalur = $request->input('jalur', '');
        $select_master_diskon = $request->input('select_master_diskon', '');

        $query = $this->service->getDiskonList($tahun_id, $program_id, $jenis_pendaftaran, $jalur, $select_master_diskon);

        echo "<option value=''>-- Pilih Diskon --</option>";

        $select_master_diskon_arr = explode(",", $select_master_diskon);

        foreach ($query as $raw) {
            $raw = (array) $raw;
            if ($raw['Tipe'] == 'nominal') {
                if (function_exists('rupiah')) {
                    $hrg = rupiah($raw['Jumlah']);
                } else {
                    $hrg = 'Rp ' . number_format($raw['Jumlah'], 0, ',', '.');
                }
            } else {
                $hrg = $raw['Jumlah'] . ' %';
            }
            $s = (in_array($raw['ID'], $select_master_diskon_arr)) ? 'selected' : '';
            echo "<option value='$raw[ID]' $s > $raw[prodi] -- $raw[Nama] $hrg</option>";
        }
    }
}
