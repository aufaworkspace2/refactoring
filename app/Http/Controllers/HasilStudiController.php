<?php

namespace App\Http\Controllers;

use App\Services\HasilStudiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use stdClass;

class HasilStudiController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(HasilStudiService $service)
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
            $this->Create = cek_level($levelUser, 'c_hasilstudi', 'Create');
            $this->Update = cek_level($levelUser, 'c_hasilstudi', 'Update');
            $this->Delete = cek_level($levelUser, 'c_hasilstudi', 'Delete');

            return $next($request);
        });
    }

    public function index($offset = 0)
    {
        return view('hasilstudi.v_hasilstudi');
    }

    public function search(Request $request, $offset = 0)
    {
        $ProgramID = $request->input('ProgramID');
        $ProdiID = $request->input('ProdiID');
        $TahunMasuk = $request->input('TahunMasuk');
        $SemesterMasuk = $request->input('SemesterMasuk');
        $TahunID = $request->input('TahunID');
        $keyword = $request->input('keyword');

        $limit = 10;
        $jml = $this->service->countHasilStudiList($ProgramID, $ProdiID, $TahunMasuk, $SemesterMasuk, $TahunID, $keyword);

        $data['offset'] = $offset;
        $data['query'] = $this->service->getHasilStudiList($limit, $offset, $ProgramID, $ProdiID, $TahunMasuk, $SemesterMasuk, $TahunID, $keyword);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);

        return view('hasilstudi.s_hasilstudi', $data);
    }

    public function simpanipk(Request $request)
    {
        $ProgramID = $request->input('ProgramID');
        $ProdiID = $request->input('ProdiID');
        $TahunID = $request->input('TahunID');
        $jenis = $request->input('jenis');
        $dari = $request->input('dari');
        $sampai = $request->input('sampai');

        $this->service->simpanIpk($ProgramID, $ProdiID, $TahunID, $jenis, $dari, $sampai);
        
        return response()->json(['status' => 1]);
    }

    public function filterALLPDF($TahunID, $ProdiID, $ProgramID, $Jenis = "KHS", $MhswID = '', $JmlCheck = 0)
    {
        $cari = ["%2C", "%27", "_"];
        $ganti = [",", "'", ","];
        
        $in = ($JmlCheck > 1) ? str_replace($cari, $ganti, $MhswID) : str_replace("_", "", $MhswID);
        $whereMhsw = (empty($MhswID)) ? "" : " AND ( mahasiswa.ID in (" . $in . ") )";
        
        $sql = "select mahasiswa.*, mahasiswa.PembimbingID as PEMBID
			from mahasiswa,hasilstudi,statusmahasiswa
			where hasilstudi.TahunID = '" . $TahunID . "'
			and hasilstudi.ProdiID = '" . $ProdiID . "'
			and hasilstudi.ProgramID = '" . $ProgramID . "' $whereMhsw
			and mahasiswa.ID = hasilstudi.MhswID
			and statusmahasiswa.ID = mahasiswa.StatusMhswID
			group by hasilstudi.ID
			order by mahasiswa.Nama
			";
            
        $students = DB::select($sql);
        
        $data_mahasiswa = [];
        $date = date('Y-m-d');

        foreach ($students as $row) {
            $mhswdata = [];
            $mhswdata['mahasiswa'] = $row;
            $mhswdata['NPM'] = $row->NPM;
            $mhswdata['Nama'] = $row->Nama;
            $mhswdata['MhswID'] = $row->ID;
            $mhswdata['ProdiID'] = get_field($row->ProdiID, 'programstudi');
            $mhswdata['JenjangID'] = $row->JenjangID;
            $mhswdata['IDProdiID'] = $row->ProdiID;
            $mhswdata['TahunID'] = $TahunID;
            $mhswdata['tgl_cetak'] = $date;

            $prodi = get_id($row->ProdiID, 'programstudi');
            $mhswdata['prodi'] = $prodi;

            $bobot_master = DB::table('setting_pemberlakuan_bobot')
                ->join('bobot_master', 'bobot_master.ID', '=', 'setting_pemberlakuan_bobot.BobotMasterID')
                ->whereRaw("FIND_IN_SET (?, ProdiID)", [$row->ProdiID])
                ->whereRaw("FIND_IN_SET (?, TahunMasuk)", [$row->TahunMasuk])
                ->whereDate('TanggalMulai', '<=', $date)
                ->whereDate('TanggalSelesai', '>=', $date)
                ->select('BobotMasterID')
                ->first();

            $mhswdata['grade_nilai'] = $bobot_master ? DB::table('bobot')->where('BobotMasterID', $bobot_master->BobotMasterID)->orderBy('Urut', 'asc')->get()->toArray() : [];
            
            $mhswdata['query'] = view_khs($row->ID, $TahunID);
            $mhswdata['ips'] = view_ips($row->ID, $TahunID);
            $mhswdata['ipk'] = view_ipk($row->ID, $TahunID);
            $mhswdata['semester_info'] = get_semester_khs($row->ID, $TahunID); // Renamed to avoid collision with loop variable
            
            $data_mahasiswa[] = $mhswdata;
        }

        $data['data_mahasiswa'] = $data_mahasiswa;
        $data['Tahun'] = get_id($TahunID, 'tahun');

        if ($Jenis == "KRS") {
            $pdf = Pdf::loadView('hasilstudi.KRS_NEW_batch', $data);
        } else {
            $pdf = Pdf::loadView('hasilstudi.KHS_NEW_batch', $data);
        }
        
        return $pdf->stream('Hasil_Studi_Batch.pdf');
    }

    public function filterPDF(Request $request)
    {
        $MhswID = $request->input('MhswID');
        $TahunID = $request->input('TahunID');
        $tgl_cetak = $request->input('tgl_cetak');

        $row = DB::table('mahasiswa')->where('ID', $MhswID)->first();
        $date = date('Y-m-d');
        $data['tgl_cetak'] = $tgl_cetak ? $tgl_cetak : $date;

        $bobot_master = DB::table('setting_pemberlakuan_bobot')
            ->join('bobot_master', 'bobot_master.ID', '=', 'setting_pemberlakuan_bobot.BobotMasterID')
            ->whereRaw("FIND_IN_SET (?, ProdiID)", [$row->ProdiID])
            ->whereRaw("FIND_IN_SET (?, TahunMasuk)", [$row->TahunMasuk])
            ->whereDate('TanggalMulai', '<=', $date)
            ->whereDate('TanggalSelesai', '>=', $date)
            ->select('BobotMasterID')
            ->first();

        if ($bobot_master) {
            $bobot = DB::table('bobot')->where('BobotMasterID', $bobot_master->BobotMasterID)->orderBy('Urut', 'asc')->get()->toArray();
        } else {
            $bobot = [];
        }
        
        $data['grade_nilai'] = $bobot;
        $data['MhswID'] = $MhswID;
        $data['TahunID'] = $TahunID;
        $data['Tahun'] = get_id($TahunID, 'tahun');
        $data['NPM'] = $row->NPM;
        $data['Nama'] = ucwords(strtolower($row->Nama));
        
        $prodi = get_id($row->ProdiID, 'programstudi');
        $data['ProdiID'] = $prodi->Nama ?? '';
        $fakultas = get_id($prodi->FakultasID ?? 0, 'fakultas');
        $data['NamaFakultas'] = $fakultas->Nama ?? '';
        $data['Dekan'] = $fakultas->Dekan ?? '';
        $data['IDProdiID'] = $row->ProdiID;
        $data['ProgramID'] = $row->ProgramID;
        $data['JenjangID'] = $row->JenjangID;
        $data['TempatLahir'] = ucwords(strtolower($row->TempatLahir ?? ''));
        $data['TanggalLahir'] = $row->TanggalLahir;
        $data['TahunMasuk'] = $row->TahunMasuk;
        $data['PEMBID'] = $row->PembimbingID;

        $data['query'] = view_khs($MhswID, $TahunID);
        $data['ips'] = view_ips($MhswID, $TahunID);
        $data['prodi'] = $prodi;
        $data['kaProdi'] = get_id($prodi->KaProdiID ?? 0, 'dosen');
        $data['ipk'] = view_ipk($MhswID, $TahunID);
        $data['semester'] = get_semester_khs($MhswID, $TahunID);

        $setup_khs = get_setup_app("setup_cetak_khs");
        $khs_custom = json_decode($setup_khs->metadata ?? '{}', true);

        $data['valueQR'] = $data['NPM'] . ($data['Tahun']->TahunID ?? '') . "EDUEDU";

        $ukuran = $khs_custom['size'] ?? 'A5';
        $orientation = $khs_custom['orientation'] ?? 'L';

        // Check for custom view
        $custom_view_path = resource_path('views/hasilstudi/p_khs_mahasiswa.blade.php');
        if (file_exists($custom_view_path)) {
            $pdf = Pdf::loadView('hasilstudi.p_khs_mahasiswa', $data);
        } else {
            $pdf = Pdf::loadView('hasilstudi.KHS_NEW', $data);
        }

        return $pdf->setPaper($ukuran, $orientation == 'L' ? 'landscape' : 'portrait')->stream('KHS_' . $data['NPM'] . '.pdf');
    }

    public function filterPDFKRS(Request $request)
    {
        $MhswID = $request->input('MhswID');
        $TahunID = $request->input('TahunID');
        $tgl_cetak = $request->input('tgl_cetak');

        $pudir2 = DB::table('karyawan')
            ->select('ID', 'NIP', 'Nama', 'Title', 'Gelar')
            ->where('Jabatan1', '5')
            ->first();

        $row = DB::table('mahasiswa')->where('ID', $MhswID)->first();
        $data['MhswID'] = $MhswID;
        $data['mahasiswa'] = $row;
        $data['TahunID'] = $TahunID;
        $data['Tahun'] = get_id($TahunID, 'tahun');

        $tahunPrev = DB::table('tahun')
            ->where('TahunID', '<', $data['Tahun']->TahunID ?? 0)
            ->orderBy('TahunID', 'DESC')
            ->first();
        $data['TahunPrev'] = $tahunPrev;

        $data['NPM'] = $row->NPM;
        $data['Kelas'] = get_field($row->KelasID, 'kelas', 'Nama');
        $data['Nama'] = ucwords(strtolower($row->Nama));
        $prodi = get_id($row->ProdiID, 'programstudi');
        $data['ProdiID'] = $prodi->Nama ?? '';
        $fakultas = get_id($prodi->FakultasID ?? 0, 'fakultas');
        $data['NamaFakultas'] = $fakultas->Nama ?? '';
        $data['IDProdiID'] = $row->ProdiID;
        $data['ProgramID'] = $row->ProgramID;
        $data['TempatLahir'] = ucwords(strtolower($row->TempatLahir ?? ''));
        $data['TanggalLahir'] = $row->TanggalLahir;
        $data['TahunMasuk'] = $row->TahunMasuk;
        $data['PEMBID'] = $row->PembimbingID;
        $data['ips'] = view_ips($MhswID, $TahunID);
        $data['query'] = view_krs($MhswID, $TahunID);
        $data['semester'] = get_semester_khs($MhswID, $TahunID);
        $data['identitas'] = get_id(1, 'identitas');
        $data['prodi'] = $prodi;
        $data['kaProdi'] = get_id($prodi->KaProdiID ?? 0, 'dosen');
        $data['pudir2'] = $pudir2;
        $data['tgl_cetak'] = $tgl_cetak;
        $data['ipkPrev'] = view_ipk($MhswID, $tahunPrev->ID ?? null);
        $data['ipsPrev'] = view_ips($MhswID, $tahunPrev->ID ?? null);

        $setup_krs = get_setup_app("setup_cetak_krs");
        $krs_custom = json_decode($setup_krs->metadata ?? '{}', true);

        $data['valueQR'] = $data['NPM'] . ($data['Tahun']->TahunID ?? '') . "EDUEDU";
        
        $custom_view_path = resource_path('views/hasilstudi/p_krs_mahasiswa.blade.php');
        if (file_exists($custom_view_path)) {
            $pdf = Pdf::loadView('hasilstudi.p_krs_mahasiswa', $data);
        } else {
            $pdf = Pdf::loadView('hasilstudi.KRS_NEW', $data);
        }

        $ukuran = $krs_custom['size'] ?? 'A4';
        $orientation = $krs_custom['orientation'] ?? 'P';

        return $pdf->setPaper($ukuran, $orientation == 'L' ? 'landscape' : 'portrait')->stream('KRS_' . $data['NPM'] . '.pdf');
    }

    public function add()
    {
        $data['save'] = 1;
        $data['row'] = null;
        return view('hasilstudi.f_hasilstudi', $data);
    }

    public function view($id)
    {
        $data['row'] = $this->service->getHasilStudiById($id);
        $data['save'] = 2;
        return view('hasilstudi.f_hasilstudi', $data);
    }

    public function save(Request $request, $save)
    {
        $input = $request->only([
            'TahunID', 'ProgramID', 'ProdiID', 'MhswID', 'StatusMhswID', 
            'Semester', 'IPS', 'SKSIPS', 'IPK', 'SKSIPK'
        ]);

        if ($save == 1) {
            $this->service->addHasilStudi($input);
        } else {
            $id = $request->input('ID');
            $this->service->editHasilStudi($id, $input);
        }

        return response()->json(['status' => 1]);
    }

    public function delete(Request $request)
    {
        $checkid = $request->input('checkID');
        if (is_array($checkid)) {
            foreach ($checkid as $id) {
                $this->service->deleteHasilStudi($id);
            }
        }
        return response()->json(['status' => 1]);
    }

    public function pdf(Request $request)
    {
        $keyword = $request->input('keyword');
        $data['query'] = $this->service->getHasilStudiList(1000, 0, '', '', '', '', '', $keyword);
        $pdf = Pdf::loadView('hasilstudi.p_hasilstudi', $data);
        return $pdf->stream('hasilstudi.pdf');
    }

    public function excel(Request $request)
    {
        $keyword = $request->input('keyword');
        $query = $this->service->getHasilStudiList(10000, 0, '', '', '', '', '', $keyword);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Hasil Studi');

        $headers = [
            'No.', 'Tahun ID', 'Program ID', 'Prodi ID', 'NPM', 
            'Status Mhsw ID', 'Semester', 'IPS', 'SKS IPS', 'IPK', 'SKS IPK'
        ];
        
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        $row_num = 2;
        foreach ($query as $index => $row) {
            $sheet->setCellValue('A' . $row_num, $index + 1);
            $sheet->setCellValue('B' . $row_num, $row->TahunID ?? '');
            $sheet->setCellValue('C' . $row_num, $row->ProgramID ?? '');
            $sheet->setCellValue('D' . $row_num, $row->ProdiID ?? '');
            $sheet->setCellValue('E' . $row_num, $row->NPM ?? '');
            $sheet->setCellValue('F' . $row_num, $row->StatusMhswID ?? '');
            $sheet->setCellValue('G' . $row_num, $row->Semester ?? '');
            $sheet->setCellValue('H' . $row_num, $row->IPS ?? '');
            $sheet->setCellValue('I' . $row_num, $row->SKSIPS ?? '');
            $sheet->setCellValue('J' . $row_num, $row->IPK ?? '');
            $sheet->setCellValue('K' . $row_num, $row->SKSIPK ?? '');
            $row_num++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'data_hasilstudi_' . date('d-m-Y') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    // Alias for CI3 compatibility
    public function LaporanIpsDanIpk()
    {
        return $this->laporanIpsIpk();
    }

    public function LaporanIpsDanIpkAngkatan()
    {
        return $this->laporanIpsIpkAngkatan();
    }

    public function laporanIpsIpk()
    {
        $data['tahunMasuk'] = $this->service->getTahunMasuk();
        return view('hasilstudi.v_laporanIPSIPK', $data);
    }

    public function laporanIpsIpkAngkatan()
    {
        $data['tahunMasuk'] = $this->service->getTahunMasuk();
        return view('hasilstudi.v_laporanIPSIPKAngkatan', $data);
    }

    public function filterIpk(Request $request)
    {
        $programID = $request->input('programID');
        $prodiID = $request->input('prodiID');
        $tahunID = $request->input('tahunID');
        $jenis = $request->input('jenis');
        $dari = $request->input('dari');
        $sampai = $request->input('sampai');
        $tahunMasuk = $request->input('tahunMasuk');
        $pilihanSort = $request->input('pilihanSort', 'ipk');
        $tipeSort = $request->input('tipeSort', 'DESC');

        $data['query'] = $this->service->filterIPKLogic($programID, $prodiID, $tahunID, $jenis, $dari, $sampai, $tahunMasuk, $pilihanSort, $tipeSort);
        $data['tahunID'] = $tahunID;

        return view('hasilstudi.s_laporanIPSIPK', $data);
    }

    public function cetak(Request $request)
    {
        $programID = $request->input('programID');
        $prodiID = $request->input('prodiID');
        $tahunID = $request->input('tahunID');
        $jenis = $request->input('jenis');
        $dari = $request->input('dari');
        $sampai = $request->input('sampai');
        $tahunMasuk = $request->input('tahunMasuk');
        $type = $request->input('type');

        $tempData = $this->service->filterIPKLogic($programID, $prodiID, $tahunID, $jenis, $dari, $sampai, $tahunMasuk);

        $data['query'] = $tempData;
        $data['programID'] = $programID;
        $data['prodiID'] = $prodiID;
        $data['tahunID'] = $tahunID;

        if ($type == "excel") {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Laporan IPS dan IPK');

            $row_num = 1;
            $sheet->setCellValue('A' . $row_num, 'Laporan Akademik Mahasiswa');
            $sheet->mergeCells('A' . $row_num . ':H' . $row_num);
            $sheet->getStyle('A' . $row_num)->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row_num++;

            $sheet->setCellValue('A' . $row_num, 'Kelas ' . get_field($programID, 'program') . ' || Program Studi ' . get_field($prodiID, 'programstudi'));
            $sheet->mergeCells('A' . $row_num . ':H' . $row_num);
            $row_num++;

            $sheet->setCellValue('A' . $row_num, 'Tahun Semester ' . get_field($tahunID, 'tahun'));
            $sheet->mergeCells('A' . $row_num . ':H' . $row_num);
            $row_num += 2;

            $headers = ['No', 'NPM', 'Nama', 'SEMESTER', 'SKS SEMESTER', 'IPS', 'TOTAL SKS', 'IPK'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row_num, $header);
                $col++;
            }
            $sheet->getStyle('A' . $row_num . ':H' . $row_num)->getFont()->setBold(true);
            $row_num++;

            foreach ($tempData as $index => $row) {
                $sheet->setCellValue('A' . $row_num, $index + 1);
                $sheet->setCellValue('B' . $row_num, $row['npm']);
                $sheet->setCellValue('C' . $row_num, $row['nama']);
                $sheet->setCellValue('D' . $row_num, $row['semesterMahasiswa']);
                $sheet->setCellValue('E' . $row_num, $row['sksSemester'] . ' SKS');
                $sheet->setCellValue('F' . $row_num, $row['ips']);
                $sheet->setCellValue('G' . $row_num, $row['sksKumulatif'] . ' SKS');
                $sheet->setCellValue('H' . $row_num, $row['ipk']);
                $row_num++;
            }

            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="laporan_ips_ipk.xlsx"');
            $writer->save('php://output');
            exit;
        } else {
            $pdf = Pdf::loadView('hasilstudi.p_laporanipsdanipk', $data);
            return $pdf->stream('laporan_ips_ipk.pdf');
        }
    }

    public function filterIpkAngkatan(Request $request)
    {
        $programID = $request->input('programID');
        $prodiID = $request->input('prodiID');
        $tahunID = $request->input('tahunID');
        $jenis = $request->input('jenis');
        $dari = $request->input('dari');
        $sampai = $request->input('sampai');
        $tahunMasuk = $request->input('tahunMasuk');
        $statusMhswID = $request->input('statusMhswID');

        $data['query'] = $this->service->filterIPKAngkatanLogic($programID, $prodiID, $tahunID, $jenis, $dari, $sampai, $tahunMasuk, $statusMhswID);
        $data['tahunID'] = $tahunID;

        return view('hasilstudi.s_laporanIPSIPKAngkatan', $data);
    }

    public function cetakPerAngkatan(Request $request)
    {
        $programID = $request->input('programID');
        $prodiID = $request->input('prodiID');
        $tahunID = $request->input('tahunID');
        $jenis = $request->input('jenis');
        $dari = $request->input('dari');
        $sampai = $request->input('sampai');
        $tahunMasuk = $request->input('tahunMasuk');
        $statusMhswID = $request->input('statusMhswID');
        $type = $request->input('type');

        $tempData = $this->service->filterIPKAngkatanLogic($programID, $prodiID, $tahunID, $jenis, $dari, $sampai, $tahunMasuk, $statusMhswID);

        $data['query'] = $tempData;
        $data['programID'] = $programID;
        $data['prodiID'] = $prodiID;
        $data['tahunID'] = $tahunID;

        if ($type == "excel") {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Laporan IPS dan IPK Angkatan');

            $row_num = 1;
            $sheet->setCellValue('A' . $row_num, 'Laporan Akademik Mahasiswa');
            $sheet->mergeCells('A' . $row_num . ':F' . $row_num);
            $sheet->getStyle('A' . $row_num)->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row_num++;

            $sheet->setCellValue('A' . $row_num, 'Kelas ' . get_field($programID, 'program') . ' || Program Studi ' . get_field($prodiID, 'programstudi'));
            $sheet->mergeCells('A' . $row_num . ':F' . $row_num);
            $row_num++;

            $headers = ['No', 'Tahun Masuk', 'Program', 'Program Studi', 'IPS', 'IPK'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row_num, $header);
                $col++;
            }
            $row_num++;

            foreach ($tempData as $index => $row) {
                $sheet->setCellValue('A' . $row_num, $index + 1);
                $sheet->setCellValue('B' . $row_num, $row['tahunMasuk']);
                $sheet->setCellValue('C' . $row_num, $row['namaProgram']);
                $sheet->setCellValue('D' . $row_num, $row['namaProdi']);
                $sheet->setCellValue('E' . $row_num, $row['ips']);
                $sheet->setCellValue('F' . $row_num, $row['ipk']);
                $row_num++;
            }

            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="laporan_ips_ipk_angkatan.xlsx"');
            $writer->save('php://output');
            exit;
        } else {
            $pdf = Pdf::loadView('hasilstudi.p_laporanipsdanipk_angkatan', $data);
            return $pdf->stream('laporan_ips_ipk_angkatan.pdf');
        }
    }

    public function devmode()
    {
        Session::put('devmode', 1);
        return response()->json(['status' => 1]);
    }
}
