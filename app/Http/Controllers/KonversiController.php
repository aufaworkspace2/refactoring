<?php

namespace App\Http\Controllers;

use App\Services\KonversiService;
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

class KonversiController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(KonversiService $service)
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

            $levelUser = Session::get('LevelUser');

            $this->Create = cek_level($levelUser, 'c_konversi', 'Create');
            $this->Update = cek_level($levelUser, 'c_konversi', 'Update');
            $this->Delete = cek_level($levelUser, 'c_konversi', 'Delete');

            return $next($request);
        });
    }

    /**
     * Main index page
     * CI3: C_konversi->index()
     */
    public function index(Request $request)
    {
        $programID = $request->input('programID', '');
        $prodiID = $request->input('prodiID', '');
        $tahunMasuk = $request->input('tahunMasuk', '');
        $keyword = $request->input('keyword', '');
        $semesterMasuk = $request->input('SemesterMasuk', '');

        $limit = 10;
        $offset = 0;

        $data['query'] = $this->service->get_data($limit, $offset, $programID, $prodiID, $tahunMasuk, $semesterMasuk, $keyword);
        $jml = $this->service->count_all($programID, $prodiID, $tahunMasuk, $semesterMasuk, $keyword);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['offset'] = $offset;

        $data['Create'] = $this->Create;
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Keahlian');
        }

        return view('konversi.v_konversi', $data);
    }

    /**
     * Search with pagination
     * CI3: C_konversi->search()
     */
    public function search(Request $request, $offset = 0)
    {
        $programID = $request->input('programID', '');
        $prodiID = $request->input('prodiID', '');
        $tahunMasuk = $request->input('tahunMasuk', '');
        $keyword = $request->input('keyword', '');
        $semesterMasuk = $request->input('SemesterMasuk', '');

        $limit = 10;

        $data['offset'] = $offset;
        $data['query'] = $this->service->get_data($limit, $offset, $programID, $prodiID, $tahunMasuk, $semesterMasuk, $keyword);
        $jml = $this->service->count_all($programID, $prodiID, $tahunMasuk, $semesterMasuk, $keyword);

        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('konversi.s_konversi', $data);
    }

    /**
     * Add new konversi form
     * CI3: C_konversi->add()
     */
    public function add()
    {
        $data['save'] = 1;

        return view('konversi.f_konversi', $data);
    }

    /**
     * Add internal konversi form
     * CI3: C_konversi->add_internal()
     */
    public function add_internal($type = '')
    {
        $data['save'] = 1;
        $data['type'] = $type;

        return view('konversi.f_konversi_internal', $data);
    }

    /**
     * View/edit konversi form
     * CI3: C_konversi->view()
     */
    public function view($id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['save'] = 2;

        return view('konversi.f_konversi', $data);
    }

    /**
     * Save/Update konversi
     * CI3: C_konversi->save()
     */
    public function save(Request $request, $save)
    {
        $post = $request->all();
        $mhsw = DB::table('mahasiswa')->where('NPM', $post['NPM'])->first();

        $konvID = $post['ID'];
        $input['KodeKonversi'] = $post['KodeKonversi'];
        $input['MhswID'] = $mhsw->ID;
        $input['SemesterMulai'] = 1;
        $input['user'] = Session::get('username');
        $input['Alasan'] = $post['Alasan'];
        $input['create_at'] = date('Y-m-d H:i:s');

        // Check for duplicate KodeKonversi
        $cekData = DB::table('konversi')
            ->where('KodeKonversi', $post['KodeKonversi'])
            ->where('ID !=', $konvID)
            ->count();

        $dataBobot = $this->service->get_bobot_values($post['NPM'], true);

        if (count($post['MKKodeAsal']) > 0) {
            $kondisi = false;
            $totalMKAsal = 0;
            $totalSKSAsal = 0;
            $totalMKTujuan = 0;
            $totalSKSTujuan = 0;
            $dataFail = [];
            $tempMatkulID = [];
            $isClear = [];

            // Check for duplicate mata kuliah
            foreach ($post['MKKodeAsal'] as $index => $val) {
                $tempMatkulID[$post['DetailkurikulumID'][$index]] = ($tempMatkulID[$post['DetailkurikulumID'][$index]] ?? 0) + 1;
            }

            foreach ($tempMatkulID as $key => $value) {
                if ($value > 1) {
                    $isClear[] = 0;
                    $dataFail[] = get_field($key, 'detailkurikulum', 'MKKode');
                } else {
                    $isClear[] = 1;
                }
            }

            if (!in_array(0, $isClear)) {
                if ($cekData == 0 || $save == 2) {
                    if ($save == 1) {
                        $kondisi = DB::table('konversi')->insert($input);
                        $id = DB::getPdo()->lastInsertId();
                        if ($kondisi) {
                            $insLog = [
                                'KonversiID' => $id,
                                'MhswID' => $mhsw->ID,
                                'NPM' => $mhsw->NPM,
                                'Jenis' => 'tambah',
                                'Internal' => 'Tidak',
                                'createdAt' => date('Y-m-d H:i:s'),
                                'UserID' => Session::get('UserID'),
                            ];
                            DB::table('log_konversi')->insert($insLog);
                        }
                    } else {
                        $id = $konvID;
                        DB::table('konversi')->where('ID', $konvID)->update($input);
                        $kondisi = true;
                        if ($kondisi) {
                            $insLog = [
                                'KonversiID' => $id,
                                'MhswID' => $mhsw->ID,
                                'NPM' => $mhsw->NPM,
                                'Jenis' => 'edit',
                                'Internal' => 'Tidak',
                                'createdAt' => date('Y-m-d H:i:s'),
                                'UserID' => Session::get('UserID'),
                            ];
                            DB::table('log_konversi')->insert($insLog);
                        }
                    }

                    if ($kondisi) {
                        foreach ($post['MKKodeAsal'] as $index => $val) {
                            $inputDetail['KonversiID'] = $id;
                            $inputDetail['MhswID'] = $mhsw->ID;
                            $inputDetail['MKKodeAsal'] = $post['MKKodeAsal'][$index];
                            $inputDetail['NamaMKAsal'] = $post['NamaMKAsal'][$index];
                            $inputDetail['SKSAsal'] = $post['SKSAsal'][$index];
                            $inputDetail['NilaiAsal'] = strtoupper($post['NilaiAsal'][$index]);
                            $inputDetail['DetailkurikulumID'] = (empty($post['DetailkurikulumID'][$index])) ? null : $post['DetailkurikulumID'][$index];
                            $inputDetail['NilaiKonversi'] = (empty($post['NilaiKonversi'][$index])) ? null : $post['NilaiKonversi'][$index];
                            $inputDetail['NilaiAngkaKonversi'] = $dataBobot[$post['NilaiKonversi'][$index]] ?? null;

                            $inputDetail['Semester'] = (empty($post['Semester'][$index])) ? null : $post['Semester'][$index];
                            if (empty($inputDetail['Semester'])) {
                                $inputDetail['Semester'] = get_field($inputDetail['DetailkurikulumID'], 'detailkurikulum', 'Semester');
                            }

                            $inputDetail['create_at'] = date('Y-m-d H:i:s');

                            // Check if already exists
                            $cekDataDetail = DB::table('konversi_detail')
                                ->select('ID', 'DetailkurikulumID')
                                ->where('DetailkurikulumID', $post['DetailkurikulumID'][$index])
                                ->where('NilaiKonversi', $post['NilaiKonversi'][$index])
                                ->where('KonversiID', $id)
                                ->first();

                            $totalMKAsal += (!empty($post['MKKodeAsal'][$index]) ? 1 : 0);
                            $totalSKSAsal += (!empty($post['SKSAsal'][$index]) ? $post['SKSAsal'][$index] : 0);
                            $totalMKTujuan += (!empty($post['DetailkurikulumID'][$index]) ? 1 : 0);
                            $totalSKSTujuan += (!empty(get_field($post['DetailkurikulumID'][$index], 'detailkurikulum', 'TotalSKS')) ? get_field($post['DetailkurikulumID'][$index], 'detailkurikulum', 'TotalSKS') : 0);

                            $dataID = $post['IDDetail'][$index];
                            if (empty($cekDataDetail->ID) && empty($dataID)) {
                                DB::table('konversi_detail')->insert($inputDetail);
                            } else {
                                if (!empty($dataID)) {
                                    DB::table('konversi_detail')->where('ID', $dataID)->update($inputDetail);
                                }
                            }
                        }

                        // Update konversi totals
                        $updateKonversi['TotalMKAsal'] = $totalMKAsal;
                        $updateKonversi['TotalSKSAsal'] = $totalSKSAsal;
                        $updateKonversi['TotalMKTujuan'] = $totalMKTujuan;
                        $updateKonversi['TotalSKSTujuan'] = $totalSKSTujuan;

                        DB::table('konversi')->where('ID', $id)->update($updateKonversi);

                        $uk = DB::table('konversi')->where('ID', $id)->update($updateKonversi);

                        if ($uk) {
                            $insLog = [
                                'KonversiID' => $id,
                                'MhswID' => $mhsw->ID,
                                'NPM' => $mhsw->NPM,
                                'Jenis' => 'edit',
                                'Internal' => 'Tidak',
                                'createdAt' => date('Y-m-d H:i:s'),
                                'UserID' => Session::get('UserID'),
                            ];
                            DB::table('log_konversi')->insert($insLog);
                        }

                        $result['data'] = [];
                        $result['status'] = 1;

                        if ($save == 2) {
                            $result['url'] = 'konversi/view/' . $id;
                        } else {
                            $result['url'] = '#konversi';
                        }
                    } else {
                        $result['status'] = 0;
                        $result['message'] = 'Maaf data gagal disimpan !.';
                    }
                } else {
                    $result['status'] = 0;
                    $result['message'] = 'Maaf data konversi nilai dengan kode "' . $post['KodeKonversi'] . '" sudah tercatat disistem !.';
                }
            } else {
                $result['status'] = 0;
                $result['message'] = 'Maaf anda tidak boleh menginput mata kuliah yang sama. Berikut ini Mata Kuliah yang sama : ';
                $result['matkul'] = array_unique($dataFail);
            }
        } else {
            $result['status'] = 0;
            $result['message'] = 'Maaf silahkan masukan Mata Kuliah konversi !.';
        }

        return response()->json($result);
    }

    /**
     * Delete konversi
     * CI3: C_konversi->delete()
     */
    public function delete(Request $request)
    {
        $checkid = $request->input('checkID');
        $removedIds = [];

        if ($checkid) {
            // Get mata kuliah data before deletion
            $getDataMatkul = DB::table('mahasiswa')
                ->select(
                    'mahasiswa.NPM as npm',
                    'mahasiswa.ID as MhswID',
                    'mahasiswa.Nama as nama',
                    'detailkurikulum.ID as MatkulID',
                    'detailkurikulum.MKKode as mkkode',
                    'detailkurikulum.Nama as namaMatkul',
                    'detailkurikulum.Semester as semester'
                )
                ->join('konversi', 'mahasiswa.ID', '=', 'konversi.MhswID')
                ->join('konversi_detail', 'konversi.ID', '=', 'konversi_detail.KonversiID')
                ->join('detailkurikulum', 'konversi_detail.DetailkurikulumID', '=', 'detailkurikulum.ID')
                ->whereIn('konversi.ID', $checkid)
                ->orderBy('mahasiswa.NPM', 'ASC')
                ->orderBy('detailkurikulum.MKKode', 'ASC')
                ->get();

            if (count($getDataMatkul) > 0) {
                $getData = DB::table('konversi')->whereIn('ID', $checkid)->get();

                // Delete konversi and details
                DB::table('konversi')->whereIn('ID', $checkid)->delete();
                DB::table('konversi_detail')->whereIn('KonversiID', $checkid)->delete();

                foreach ($getData as $kon) {
                    $mhsw = DB::table('mahasiswa')->select('ID', 'NPM', 'Nama')->where('ID', $kon->MhswID)->first();

                    $insLog = [
                        'KonversiID' => $kon->ID,
                        'MhswID' => $mhsw->ID,
                        'NPM' => $mhsw->NPM,
                        'Jenis' => 'hapus',
                        'Internal' => $kon->internal,
                        'createdAt' => date('Y-m-d H:i:s'),
                        'UserID' => Session::get('UserID'),
                    ];
                    DB::table('log_konversi')->insert($insLog);
                }

                // Delete related nilai
                foreach ($getDataMatkul as $value) {
                    $cekNilai = DB::table('nilai')
                        ->select('ID')
                        ->where('Konversi', '1')
                        ->where('MhswID', $value->MhswID)
                        ->where('DetailKurikulumID', $value->MatkulID)
                        ->first();

                    if (!empty($cekNilai->ID)) {
                        DB::table('nilai')->where('ID', $cekNilai->ID)->delete();
                    }
                }

                foreach ($checkid as $id) {
                    $removedIds[] = $id;
                }

                return response()->json([
                    'status' => '1',
                    'message' => 'Data berhasil dihapus !.',
                    'removed_ids' => $removedIds,
                    'class_prefix' => 'konversi_'
                ]);
            } else {
                return response()->json([
                    'status' => '0',
                    'message' => 'Data gagal dihapus !.'
                ]);
            }
        }

        return response()->json([
            'status' => '0',
            'message' => 'No data selected.'
        ]);
    }

    /**
     * Delete konversi detail
     * CI3: C_konversi->delete_detail()
     */
    public function delete_detail(Request $request)
    {
        $checkid = $request->input('checkID');
        $this->service->delete_detail($checkid);

        return response()->json(['status' => '1']);
    }

    /**
     * Get konversi detail JSON
     * CI3: C_konversi->json_konversi()
     */
    public function json_konversi(Request $request)
    {
        $konversiID = $request->input('KonversiID');
        $konvDetail = $this->service->get_konversi_detail($konversiID);

        return response()->json($konvDetail);
    }

    /**
     * Get nilai JSON for mahasiswa
     * CI3: C_konversi->json_nilai()
     */
    public function json_nilai(Request $request)
    {
        $mhswID = $request->input('MhswID');
        $data = $this->service->get_nilai_for_mahasiswa($mhswID);

        return response()->json($data);
    }

    /**
     * Search mata kuliah JSON
     * CI3: C_konversi->json_mk()
     */
    public function json_mk(Request $request)
    {
        $npm = $request->input('NPM', $request->input('q', ''));
        $search = $request->input('q', '');
        $page = $request->input('page', 0);

        $programID = $request->input('ProgramID', '');
        $prodiID = $request->input('ProdiID', '');
        $kurikulumID = $request->input('KurikulumID', '');

        // If NPM is provided, get mahasiswa data
        if (!empty($npm) && empty($programID)) {
            $mhs = DB::table('mahasiswa')->where('NPM', $npm)->first();
            if ($mhs) {
                $programID = $mhs->ProgramID;
                $prodiID = $mhs->ProdiID;
                $kurikulumID = $mhs->KurikulumID;
            }
        }

        if (empty($mhs ?? null)) {
            return response()->json([
                'total_count' => 0,
                'items' => [['id' => '', 'text' => 'Harap Pilih Mahasiswa Terlebih Dahulu ']]
            ]);
        }

        $result = $this->service->search_mata_kuliah($programID, $prodiID, $kurikulumID, $search, $page);

        return response()->json($result);
    }

    /**
     * Check NPM existence
     * CI3: C_konversi->cek_npm()
     */
    public function cek_npm(Request $request)
    {
        $npm = $request->input('npm');
        $exists = $this->service->check_npm_exists($npm);

        return response($exists ? '0' : '1');
    }

    /**
     * Get bobot values
     * CI3: C_konversi->change_bobot()
     */
    public function change_bobot(Request $request)
    {
        $npm = $request->input('mhswID', '');
        $asArray = false;

        $result = $this->service->get_bobot_values($npm, $asArray);

        return response()->json($result);
    }

    /**
     * Get single bobot value
     * CI3: C_konversi->nilai_bobot()
     */
    public function nilai_bobot(Request $request)
    {
        $npm = $request->input('mhswID', '');
        $bobotNilai = $request->input('BobotNilai', '');

        $bobot = $this->service->get_nilai_bobot($npm, $bobotNilai);

        return response($bobot);
    }

    /**
     * Get mahasiswa parameters
     * CI3: C_konversi->get_param_mahasiswa()
     */
    public function get_param_mahasiswa(Request $request)
    {
        $npm = $request->input('NPM');
        $data = $this->service->get_mahasiswa_param($npm);

        return response()->json($data);
    }

    /**
     * Change semester
     * CI3: C_konversi->changeSemester()
     */
    public function changeSemester(Request $request)
    {
        $detailKurikulumID = $request->input('detailKurikulumID');
        $semester = $this->service->change_semester($detailKurikulumID);

        return response($semester ?? '');
    }

    /**
     * PDF single konversi nilai
     * CI3: C_konversi->cetakNilaiKonversi()
     */
    public function cetakNilaiKonversi($id)
    {
        $data = $this->service->get_data_for_pdf($id);

        $pdf = Pdf::loadView('konversi.cetak_nilai_konversi_single', $data);
        $pdf->setPaper('F4', 'P');
        return $pdf->stream('konversi_' . date('Y-m-d') . '.pdf');
    }

    /**
     * PDF report list
     * CI3: C_konversi->pdf()
     */
    public function pdf(Request $request)
    {
        $programID = $request->input('programID', '');
        $prodiID = $request->input('prodiID', '');
        $tahunMasuk = $request->input('tahunMasuk', '');
        $keyword = $request->input('keyword', '');
        $semesterMasuk = $request->input('SemesterMasuk', '');

        $data['query'] = $this->service->get_data(null, null, $programID, $prodiID, $tahunMasuk, $semesterMasuk, $keyword);

        $pdf = Pdf::loadView('konversi.p_konversi', $data);
        $pdf->setPaper('A4', 'P');
        return $pdf->stream('Data_Konversi_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Excel export
     * CI3: C_konversi->excel()
     */
    public function excel(Request $request)
    {
        $programID = $request->input('programID', '');
        $prodiID = $request->input('prodiID', '');
        $tahunMasuk = $request->input('tahunMasuk', '');
        $keyword = $request->input('keyword', '');
        $semesterMasuk = $request->input('SemesterMasuk', '');

        $query = $this->service->get_data(null, null, $programID, $prodiID, $tahunMasuk, $semesterMasuk, $keyword);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Konversi');

        $row_num = 1;

        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'E');
        }

        $sheet->setCellValue('A' . $row_num, 'Data Mahasiswa Konversi');
        $sheet->mergeCells('A' . $row_num . ':E' . $row_num);
        $sheet->getStyle('A' . $row_num)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row_num += 2;

        $start_table_row = $row_num;

        $sheet->setCellValue('A' . $row_num, 'No');
        $sheet->setCellValue('B' . $row_num, 'NPM');
        $sheet->setCellValue('C' . $row_num, 'Nama');
        $sheet->setCellValue('D' . $row_num, 'Program');
        $sheet->setCellValue('E' . $row_num, 'Program Studi');

        $sheet->getStyle('A' . $row_num . ':E' . $row_num)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row_num . ':E' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row_num . ':E' . $row_num)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A' . $row_num . ':E' . $row_num)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFC000');
        $row_num++;

        $nomor = 1;
        if (!empty($query)) {
            foreach ($query as $value) {
                $value = (object) $value;

                $sheet->setCellValue('A' . $row_num, $nomor++);
                $sheet->setCellValueExplicit('B' . $row_num, $value->NPM ?? '', DataType::TYPE_STRING);
                $sheet->setCellValue('C' . $row_num, $value->Nama ?? '');
                $sheet->setCellValue('D' . $row_num, get_field($value->ProgramID ?? '', 'program'));
                $sheet->setCellValue('E' . $row_num, get_field($value->ProdiID ?? '', 'programstudi'));

                $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('D' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $row_num++;
            }
        } else {
            $sheet->setCellValue('A' . $row_num, 'Tidak ada data konversi');
            $sheet->mergeCells('A' . $row_num . ':E' . $row_num);
            $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row_num++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $start_table_row . ':E' . ($row_num - 1))->applyFromArray($styleBorder);

        if (!function_exists('cetak_kop_phpspreadsheet')) {
            $sheet->getColumnDimension('A')->setAutoSize(true);
        }
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "data_konversi_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $objWriter = new Xlsx($spreadsheet);
        $objWriter->save('php://output');
        exit;
    }

    /**
     * Generate konversi
     * CI3: C_konversi->genKonversi()
     */
    public function genKonversi(Request $request)
    {
        $konversiID = $request->input('KonversiID');
        $result = $this->service->gen_konversi($konversiID, Session::get('UserID'));

        return response()->json($result);
    }

    /**
     * Generate all konversi
     * CI3: C_konversi->konversi_all()
     */
    public function konversi_all(Request $request)
    {
        $programID = $request->input('ProgramID', '');
        $prodiID = $request->input('ProdiID', '');
        $tahunMasuk = $request->input('TahunMasuk', '');

        $result = $this->service->konversi_all($programID, $prodiID, $tahunMasuk);

        return response()->json($result);
    }

    /**
     * Cancel/batalkan konversi
     * CI3: C_konversi->batalKonversi()
     */
    public function batalKonversi(Request $request)
    {
        $konversiID = $request->input('KonversiID');
        $result = $this->service->batal_konversi($konversiID, Session::get('UserID'));

        return response()->json($result);
    }

    /**
     * Get last NPM for internal conversion
     * CI3: C_konversi->get_last_npm()
     */
    public function get_last_npm(Request $request)
    {
        $prodiID = $request->input('ProdiID', '');
        $programID = $request->input('ProgramID', '');

        $npm = $this->service->get_last_npm($prodiID, $programID);

        return response($npm ?? '');
    }

    /**
     * Tools generate konversi
     * CI3: C_konversi->toolsGenKonversi()
     */
    public function toolsGenKonversi()
    {
        $query = DB::table('konversi')->where('statuskonversi', 0)->get();
        $count = 0;

        foreach ($query as $row) {
            $konversiID = $row->ID;

            $totalSKSAsal = 0;
            $totalSKSTujuan = 0;

            $allDetail = DB::table('konversi_detail')->where('KonversiID', $konversiID)->get();
            foreach ($allDetail as $detail) {
                $sksMk = get_field($detail->DetailkurikulumID, 'detailkurikulum', 'TotalSKS');

                $totalSKSAsal += $detail->SKSAsal;
                $totalSKSTujuan += $sksMk;
            }

            DB::table('konversi')->where('ID', $konversiID)->update([
                'TotalSKSAsal' => $totalSKSAsal,
                'TotalSKSTujuan' => $totalSKSTujuan,
            ]);

            $this->service->gen_konversi($konversiID, Session::get('UserID'), true);
            $count++;
        }

        return response("selesai " . $count);
    }

    /**
     * Save internal konversi
     * CI3: C_konversi->save_internal()
     */
    public function save_internal(Request $request, $save = '1')
    {
        $post = $request->all();
        $result = $this->service->save_internal($post, $save, Session::get('UserID'));

        return response()->json($result);
    }

    /**
     * Excel template for nilai transfer
     * CI3: C_konversi->excelNilai()
     */
    public function excelNilai(Request $request)
    {
        $programID = $request->input('ProgramID', '');
        $prodiID = $request->input('ProdiID', '');
        $kurikulumID = $request->input('KurikulumID', '');

        // Template rules
        $aturan = [
            ['1.', 'Nilai Huruf harus Huruf Kapital'],
            ['2.', 'ID mata kuliah di akui, di pastikan sesuai degan ID mata kuliah yang terdaftar di sheet kurikulum'],
            ['3.', 'Kolom yang di tandai merah tidak boleh di kosongkan'],
        ];

        // Get mata kuliah list
        $dataMk = DB::table('detailkurikulum')
            ->select('ID', 'MKKode', 'Nama')
            ->where('ProdiID', $prodiID)
            ->where('KurikulumID', $kurikulumID)
            ->when($programID, function($q) use ($programID) {
                $q->where('ProgramID', $programID);
            })
            ->get();

        $spreadsheet = new Spreadsheet();

        // Sheet 1: Mahasiswa (empty template)
        $sheet1 = $spreadsheet->getSheet(0);
        $sheet1->setTitle('Mahasiswa');

        // Sheet 2: Kurikulum (list mata kuliah)
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Kurikulum');

        $rowNum = 2;
        $sheet2->setCellValue('A1', 'ID');
        $sheet2->setCellValue('B1', 'MKKode');
        $sheet2->setCellValue('C1', 'Nama');
        foreach ($dataMk as $mk) {
            $sheet2->setCellValue('A' . $rowNum, $mk->ID);
            $sheet2->setCellValue('B' . $rowNum, $mk->MKKode);
            $sheet2->setCellValue('C' . $rowNum, $mk->Nama);
            $rowNum++;
        }

        // Sheet 3: Aturan Penggunaan
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Aturan Penggunaan');
        $rowNum = 1;
        foreach ($aturan as $aturanRow) {
            $sheet3->setCellValue('A' . $rowNum, $aturanRow[0]);
            $sheet3->setCellValue('B' . $rowNum, $aturanRow[1]);
            $rowNum++;
        }

        $filename = time() . '-NilaiTransfer.xlsx';

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = new Xlsx($spreadsheet);
        $objWriter->save('php://output');
        exit;
    }

    /**
     * Upload Excel for batch konversi
     * CI3: C_konversi->uploadExcel()
     */
    public function uploadExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:csv,xls,xlsx|max:10000',
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
            $result = $this->service->process_upload_excel($filePath, Session::get('UserID'));

            // Clean up uploaded file
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            return response()->json([
                'status' => 0,
                'message' => 'Mohon maaf data gagal diupload !. ' . $e->getMessage(),
            ]);
        }
    }
}
