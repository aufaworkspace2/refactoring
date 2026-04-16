<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SetupMahasiswaDiskonSampaiLulusService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SetupMahasiswaDiskonSampaiLulusController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(SetupMahasiswaDiskonSampaiLulusService $service)
    {
        $this->service = $service;

        $this->middleware(function ($request, $next) {
            $lang = Session::get('language');
            if (!$lang && !$request->cookie('language')) {
                $lang = 'indonesia';
                Session::put('language', $lang);
            }

            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);

            $levelUser = Session::get('LevelUser');

            $this->Create = cek_level($levelUser, 'c_setup_mahasiswa_diskon_sampai_lulus', 'Create');
            $this->Update = cek_level($levelUser, 'c_setup_mahasiswa_diskon_sampai_lulus', 'Update');
            $this->Delete = cek_level($levelUser, 'c_setup_mahasiswa_diskon_sampai_lulus', 'Delete');

            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        $data['Create'] = $this->Create;

        return view('setup_mahasiswa_diskon_sampai_lulus.v', $data);
    }

    public function search(Request $request, $offset = 0)
    {
        $keyword = $request->input('keyword', '');
        $TahunID = $request->input('TahunID', '');
        $ProgramID = $request->input('ProgramID', '');
        $ProdiID = $request->input('ProdiID', '');
        $StatusAktif = $request->input('StatusAktif', '');

        $limit = 10;

        $jml = $this->service->count_all($keyword, $TahunID, $ProgramID, $ProdiID, $StatusAktif);

        $data['offset'] = $offset;
        $data['query'] = $this->service->get_data($limit, $offset, $keyword, $TahunID, $ProgramID, $ProdiID, $StatusAktif);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('setup_mahasiswa_diskon_sampai_lulus.s', $data);
    }

    public function changenominal(Request $request)
    {
        $PemberiDiskonID = $request->input('PemberiDiskonID', '');

        $row = DB::table('discount')->where('PemberiDiskonID', $PemberiDiskonID)->first();

        return response()->json([
            'nom' => $row->Nominal ?? 0,
            'DiscountID' => $row->DiscountID ?? ''
        ]);
    }

    public function add()
    {
        $data['save'] = 1;

        // Get master_diskon with prodi info
        $data['master_diskon'] = DB::table('master_diskon')
            ->select('master_diskon.*', DB::raw('if(master_diskon.ProdiID = 0, CONCAT("Semua Programstudi"), CONCAT(jenjang.Nama," || ",programstudi.Nama)) as prodi'))
            ->leftJoin('programstudi', 'programstudi.ID', '=', 'master_diskon.ProdiID')
            ->leftJoin('jenjang', 'jenjang.ID', '=', 'programstudi.JenjangID')
            ->get();

        return view('setup_mahasiswa_diskon_sampai_lulus.f', $data);
    }

    public function view($id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['row_data'] = DB::table('mahasiswa')->where('ID', $data['row']->MhswID)->first();

        // Get master_diskon with prodi info
        $data['master_diskon'] = DB::table('master_diskon')
            ->select('master_diskon.*', DB::raw('if(master_diskon.ProdiID = 0, CONCAT("Semua Programstudi"), CONCAT(jenjang.Nama," || ",programstudi.Nama)) as prodi'))
            ->leftJoin('programstudi', 'programstudi.ID', '=', 'master_diskon.ProdiID')
            ->leftJoin('jenjang', 'jenjang.ID', '=', 'programstudi.JenjangID')
            ->get();

        $data['save'] = 2;

        return view('setup_mahasiswa_diskon_sampai_lulus.f_edit', $data);
    }

    public function filtermhs(Request $request)
    {
        $TahunMasuk = $request->input('TahunMasuk', []);
        $ProdiID = $request->input('ProdiID', '');
        $ProgramID = $request->input('ProgramID', '');
        $KelasID = $request->input('KelasID', '');

        $query = DB::table('mahasiswa')
            ->where('jenis_mhsw', 'mhsw');

        if ($ProdiID) {
            $query->where('ProdiID', $ProdiID);
        }
        if ($ProgramID) {
            $query->where('ProgramID', $ProgramID);
        }
        if ($KelasID) {
            $query->where('KelasID', $KelasID);
        }
        if (!empty($TahunMasuk)) {
            $query->whereIn('TahunMasuk', $TahunMasuk);
        }

        $data['get_mhs'] = $query->get();

        return view('setup_mahasiswa_diskon_sampai_lulus.filtermhs', $data);
    }

    public function filtermhscalon(Request $request)
    {
        $TahunMasuk = $request->input('TahunMasuk', []);
        $ProdiID = $request->input('ProdiID', '');
        $ProgramID = $request->input('ProgramID', '');
        $KelasID = $request->input('KelasID', '');

        $query = DB::table('mahasiswa')
            ->where(function($q) {
                $q->where('jenis_mhsw', 'calon')
                  ->orWhere('statuslulus_pmb', '1');
            });

        if ($ProdiID) {
            $query->where('ProdiID', $ProdiID);
        }
        if ($ProgramID) {
            $query->where('ProgramID', $ProgramID);
        }
        if ($KelasID) {
            $query->where('KelasID', $KelasID);
        }
        if (!empty($TahunMasuk)) {
            $query->whereIn('TahunMasuk', $TahunMasuk);
        }

        $data['get_mhs'] = $query->get();

        return view('setup_mahasiswa_diskon_sampai_lulus.filtermhs', $data);
    }

    public function save(Request $request, $save)
    {
        $result = $this->service->save($save, $request->all());

        return response($result, 200);
    }

    public function save_alone(Request $request, $save)
    {
        $result = $this->service->save_alone($save, $request->all());

        return response($result, 200);
    }

    public function delete(Request $request)
    {
        $checkid = $request->input('checkID');

        if ($checkid) {
            foreach ($checkid as $id) {
                DB::table('setup_mahasiswa_diskon_sampai_lulus')
                    ->where('ID', $id ?: '00')
                    ->update(['StatusAktif' => '0']);
            }
        }

        return response()->json(['status' => 'success']);
    }

    public function aktifkan($id)
    {
        DB::table('setup_mahasiswa_diskon_sampai_lulus')
            ->where('ID', $id ?: '00')
            ->update(['StatusAktif' => '1']);

        return response()->json(['status' => 'success']);
    }

    public function excel(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $TahunID = $request->input('TahunID', '');
        $ProgramID = $request->input('ProgramID', '');
        $ProdiID = $request->input('ProdiID', '');
        $StatusAktif = $request->input('StatusAktif', '');

        $query_data = $this->service->get_data(0, 1000000000, $keyword, $TahunID, $ProgramID, $ProdiID, $StatusAktif);

        $query_master_diskon = DB::table('master_diskon')->get();
        $list_master_diskon = [];
        foreach ($query_master_diskon as $row_master_diskon) {
            $list_master_diskon[$row_master_diskon->ID] = $row_master_diskon;
        }

        $query_jenisbiaya = DB::table('jenisbiaya')->get();
        $list_jenisbiaya = [];
        foreach ($query_jenisbiaya as $row_jenisbiaya) {
            $list_jenisbiaya[$row_jenisbiaya->ID] = $row_jenisbiaya;
        }

        $query_jenjang = DB::table('jenjang')->select('ID', 'Nama')->get();
        $jenjang = [];
        foreach ($query_jenjang as $row_jenjang) {
            $jenjang[$row_jenjang->ID] = $row_jenjang->Nama;
        }

        $query_programstudi = DB::table('programstudi')->select('ID', 'Nama', 'JenjangID')->get();
        $arr_programstudi = [];
        foreach ($query_programstudi as $row_programstudi) {
            $arr_programstudi[$row_programstudi->ID] = $row_programstudi;
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Setup Diskon Mahasiswa');

        $row_num = 1;

        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'G');
        }

        $slog_text = strtoupper('DATA SETUP MAHASISWA DISKON');
        $sheet->setCellValue('A' . $row_num, $slog_text);
        $sheet->mergeCells('A' . $row_num . ':G' . $row_num);
        $sheet->getStyle('A' . $row_num)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $row_num++;

        $start_table_row = $row_num;

        $sheet->setCellValue('A' . $row_num, 'No.');
        $sheet->setCellValue('B' . $row_num, 'NPM');
        $sheet->setCellValue('C' . $row_num, 'NAMA');
        $sheet->setCellValue('D' . $row_num, 'JURUSAN');
        $sheet->setCellValue('E' . $row_num, 'JENIS BIAYA');
        $sheet->setCellValue('F' . $row_num, 'AKTIF');
        $sheet->setCellValue('G' . $row_num, 'NAMA DISKON');

        $sheet->getStyle('A' . $row_num . ':G' . $row_num)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row_num . ':G' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row_num . ':G' . $row_num)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC000');
        $row_num++;

        $no = 1;
        if (!empty($query_data)) {
            foreach ($query_data as $row) {
                $sheet->setCellValue('A' . $row_num, $no++);
                $sheet->setCellValueExplicit('B' . $row_num, $row->NPM ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('C' . $row_num, $row->Nama ?? '');

                $row_prodi = $arr_programstudi[$row->ProdiID] ?? null;
                $nama_jurusan = '';
                if ($row_prodi) {
                    $nama_jenjang = $jenjang[$row_prodi->JenjangID] ?? '';
                    $nama_jurusan = trim($nama_jenjang . ' ' . $row_prodi->Nama);
                }
                $sheet->setCellValue('D' . $row_num, $nama_jurusan);

                $ListDiskon = json_decode($row->ListDiskon, true);
                $str_jenisbiaya = [];
                $str_namadiskon = [];

                if (is_array($ListDiskon)) {
                    foreach ($ListDiskon as $row_diskon) {
                        $jenis_nama = $list_jenisbiaya[$row_diskon['JenisBiayaID']]->Nama ?? '';
                        $str_jenisbiaya[] = "- " . $jenis_nama;

                        $expl_discount = $row_diskon['ListMasterDiskonID'];
                        if (is_array($expl_discount)) {
                            foreach ($expl_discount as $val_disc) {
                                if (isset($list_master_diskon[$val_disc])) {
                                    $master_diskon = $list_master_diskon[$val_disc];
                                    if ($master_diskon->Tipe == 'nominal') {
                                        $nom = "Rp " . number_format($master_diskon->Jumlah, 0, ',', '.');
                                    } else {
                                        $nom = $master_diskon->Jumlah . ' %';
                                    }
                                    $str_namadiskon[] = "• " . $master_diskon->Nama . ' - ' . $nom;
                                }
                            }
                        }
                    }
                }

                $sheet->setCellValue('E' . $row_num, implode("\n", $str_jenisbiaya));
                $sheet->setCellValue('G' . $row_num, implode("\n", $str_namadiskon));

                $status_aktif = (isset($row->StatusAktif) && $row->StatusAktif == 1) ? 'Y' : 'N';
                $sheet->setCellValue('F' . $row_num, $status_aktif);

                $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle('A' . $row_num . ':G' . $row_num)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
                $sheet->getStyle('E' . $row_num)->getAlignment()->setWrapText(true);
                $sheet->getStyle('G' . $row_num)->getAlignment()->setWrapText(true);

                $row_num++;
            }
        } else {
            $sheet->setCellValue('A' . $row_num, 'Tidak ada data setup diskon');
            $sheet->mergeCells('A' . $row_num . ':G' . $row_num);
            $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $row_num++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $start_table_row . ':G' . ($row_num - 1))->applyFromArray($styleBorder);

        if (!function_exists('cetak_kop_phpspreadsheet')) {
            $sheet->getColumnDimension('A')->setAutoSize(true);
        }
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setWidth(40);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "setup_diskon_sampai_lulus_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $objWriter->save('php://output');
        exit;
    }
}
