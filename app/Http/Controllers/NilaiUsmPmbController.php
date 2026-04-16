<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NilaiUsmPmbService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Barryvdh\DomPDF\Facade\Pdf;

class NilaiUsmPmbController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(NilaiUsmPmbService $service)
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
            $this->Create = cek_level($levelUser, 'c_nilai_usm_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_nilai_usm_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_nilai_usm_pmb', 'Delete');

            return $next($request);
        });
    }

    /**
     * Display list of mahasiswa with USM scores
     */
    public function index(Request $request, $offset = 0, $bayar = 1)
    {
        $data['Create'] = $this->Create;
        $data['bayar'] = $bayar;

        // Load dropdown data directly from database
        $data['data_gelombang'] = DB::table('pmb_tbl_gelombang')
            ->orderBy('nama', 'ASC')
            ->get();

        $data['data_gelombang_detail'] = DB::table('pmb_tbl_gelombang_detail')
            ->leftJoin('pmb_tbl_gelombang', 'pmb_tbl_gelombang.id', '=', 'pmb_tbl_gelombang_detail.gelombang_id')
            ->select('pmb_tbl_gelombang_detail.id', 'pmb_tbl_gelombang.nama', 'pmb_tbl_gelombang_detail.gelombang_id')
            ->orderBy('pmb_tbl_gelombang.nama', 'ASC')
            ->get();

        $data['data_program'] = DB::table('program')
            ->orderBy('Nama', 'ASC')
            ->get();

        $data['data_prodi'] = DB::table('programstudi')
            ->orderBy('Nama', 'ASC')
            ->get();

        // Get filter values from request for auto-select
        $data['selected_gelombang'] = $request->input('gelombang', '');
        $data['selected_gelombang_detail'] = $request->input('gelombang_detail', '');
        $data['selected_program'] = $request->input('program', '');
        $data['selected_pilihan1'] = $request->input('pilihan1', '');
        $data['selected_statustest'] = $request->input('statustest', '');
        $data['selected_orderby'] = $request->input('orderby', 'mahasiswa.Nama');
        $data['selected_descasc'] = $request->input('descasc', 'ASC');
        $data['selected_viewpage'] = $request->input('viewpage', '10');

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Menu Nilai USM');
        }

        return view('nilai_usm_pmb.v_nilai_usm_pmb', $data);
    }

    /**
     * Search mahasiswa with filters
     */
    public function search(Request $request, $offset = 0)
    {
        $filters = [];
        $bayar = $request->input('bayar', '1');

        // Build filters array
        if (!empty($request->input('gelombang'))) {
            $filters['gelombang'] = $request->input('gelombang');
        }

        if (!empty($request->input('gelombang_detail'))) {
            $filters['gelombang_detail'] = $request->input('gelombang_detail');
        }

        if (!empty($request->input('program'))) {
            $filters['program'] = $request->input('program');
        }

        if (!empty($request->input('pilihan1'))) {
            $filters['pilihan1'] = $request->input('pilihan1');
        }

        if (!empty($request->input('pilihan2'))) {
            $filters['pilihan2'] = $request->input('pilihan2');
        }

        if (!empty($request->input('keyword'))) {
            $filters['keyword'] = $request->input('keyword');
        }

        // Build HAVING clause for statustest
        $having = '';
        if (!empty($request->input('statustest'))) {
            $statustest = $request->input('statustest');
            if ($statustest === 'selesai') {
                $having = "HAVING (jumlahSelesai >= jumlahUjian AND jumlahUjian != 0)";
            } else {
                $having = "HAVING (jumlahSelesai < jumlahUjian OR jumlahUjian = 0)";
            }
        }

        // Build ORDER BY clause
        $orderby = 'ORDER BY mahasiswa.Nama ASC';
        if (!empty($request->input('orderby'))) {
            $orderby_col = $request->input('orderby');
            $orderby_dir = $request->input('descasc', 'ASC');
            $orderby = "ORDER BY mahasiswa.ID desc, $orderby_col $orderby_dir";
        }
        $limit = 10;
        if (!empty($request->input('viewpage'))) {
            $limit = (int) $request->input('viewpage');
        }

        $jml = $this->service->countVerifikasiPMB($filters, $bayar, $having);
        $data['offset'] = $offset;
        $data['bayar'] = $bayar;

        $data['query'] = $this->service->getMahasiswaPMB($filters, $bayar, $having, $orderby, $limit, $offset);
        
        // Get data jenis USM
        $data['data_jenisusm'] = $this->service->getJenisUSM();
        
        // Get all prodi for lookup
        $allProdi = DB::table('programstudi')->get();
        $arrProdi = [];
        foreach ($allProdi as $prodi) {
            $jenjang = DB::table('jenjang')->where('ID', $prodi->JenjangID)->first();
            $prodi->NamaJenjang = $jenjang->Nama ?? '';
            $arrProdi[$prodi->ID] = $prodi;
        }
        $data['all_prodi'] = $arrProdi;
        
        // Get data hasil USM (offline & online)
        $data['data_hasil'] = [];
        $data['data_hasilonline'] = [];
        
        foreach ($data['data_jenisusm'] as $jenisusm) {
            if ($jenisusm['jenis'] == 'offline') {
                // Get offline hasil
                $hasilOffline = DB::table('pmb_tbl_hasil_usm_baru')
                    ->where('idjenisusm', $jenisusm['id'])
                    ->get();
                foreach ($hasilOffline as $h) {
                    $data['data_hasil'][$jenisusm['id']][$h->idpendaftar] = $h;
                }
            } else {
                // Get online hasil (from API or database)
                // For now, get from pmb_tbl_hasil_test
                $hasilOnline = DB::table('pmb_tbl_hasil_test')
                    ->select('idmember as idpendaftar', DB::raw('AVG(score) as nilai'))
                    ->groupBy('idmember')
                    ->get();
                foreach ($hasilOnline as $h) {
                    $data['data_hasilonline'][$jenisusm['id']][$h->idpendaftar] = $h;
                }
            }
        }

        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('nilai_usm_pmb.s_nilai_usm_pmb', $data);
    }

    /**
     * Show form to edit USM scores for a student
     */
    public function edit($id)
    {
        $data['row'] = $this->service->getDataForSKL($id);
        $data['detail_nilai'] = $this->service->getDetailNilai($id);
        $data['Update'] = $this->Update;

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Detail Nilai USM Mahasiswa ID: ' . $id);
        }

        return view('nilai_usm_pmb.f_nilai_usm_pmb', $data);
    }

    /**
     * Print SKL (Surat Keterangan Lulus) menggunakan DomPDF
     */
    public function print_skl($id)
    {
        // Get data mahasiswa untuk SKL
        $result = DB::table('mahasiswa')->where('ID', $id)->first();
        
        if (!$result) {
            abort(404, 'Data mahasiswa tidak ditemukan');
        }

        // Get identitas institusi
        $row_institusi = DB::table('identitas')->first();
        $row_info_pmb = DB::table('pmb_info')->where('id', 1)->first();
        
        // Get gelombang detail dan gelombang
        $gelombang_detail = DB::table('pmb_tbl_gelombang_detail')
            ->where('id', $result->gelombang_detail_pmb)
            ->first();
        $gelombang = DB::table('pmb_tbl_gelombang')
            ->where('id', $gelombang_detail->gelombang_id ?? null)
            ->first();

        // Setup data institusi
        $email_institusi = $row_info_pmb->email ?? $row_institusi->EmailMarketing ?? '';
        $telp_institusi = $row_info_pmb->telepon ?? $row_institusi->TeleponPMB ?? '';
        $hp_institusi = $row_info_pmb->telepon ?? $row_institusi->TeleponPMB ?? '';
        $faxinstitusi_institusi = $row_info_pmb->fax ?? $row_institusi->FaxPT ?? '';
        $logo_institusi = $row_info_pmb->logo ?? $row_institusi->Gambar ?? '';
        
        // Setup URL logo
        $urllogo_institusi = $logo_institusi 
            ? asset('pmb/logo/' . $logo_institusi)
            : asset('images/' . $logo_institusi);

        // Get template wording menggunakan helper function
        $params = ['pendaftar_id' => $id];
        $get_template_wording = get_template_wording('skl_pmb', $params);
        $skl_custom = $get_template_wording['skl_pmb'] ?? '';

        $data = [
            'identitas' => (array) $row_institusi,
            'data_skl' => $result,
            'row' => $result, // Add $row for view compatibility
            'gelombang_detail' => $gelombang_detail,
            'gelombang' => $gelombang,
            'email_institusi' => $email_institusi,
            'telp_institusi' => $telp_institusi,
            'hp_institusi' => $hp_institusi,
            'faxinstitusi_institusi' => $faxinstitusi_institusi,
            'logo_institusi' => $logo_institusi,
            'urllogo_institusi' => $urllogo_institusi,
            'skl_custom' => $skl_custom,
            'detail_nilai' => $this->service->getDetailNilai($id)
        ];
        if (function_exists('log_akses')) {
            log_akses('Print', 'Mencetak SKL Mahasiswa ID: ' . $id);
        }

        // Render view ke HTML
        $html = view('nilai_usm_pmb.p_skl_pmb', $data)->render();

        // Generate PDF menggunakan DomPDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4');
        
        // Stream PDF ke browser
        return $pdf->stream('SKL_' . ($result->NPM ?? $id) . '.pdf');
    }

    /**
     * Export Excel - Data Nilai USM
     */
    public function export_excel(Request $request)
    {
        $filters = [];
        $bayar = $request->input('bayar', '1');

        if (!empty($request->input('gelombang'))) {
            $filters['gelombang'] = $request->input('gelombang');
        }

        if (!empty($request->input('gelombang_detail'))) {
            $filters['gelombang_detail'] = $request->input('gelombang_detail');
        }

        if (!empty($request->input('program'))) {
            $filters['program'] = $request->input('program');
        }

        $data['query'] = $this->service->getMahasiswaPMB($filters, $bayar, '', 'ORDER BY mahasiswa.Nama ASC');

        return view('nilai_usm_pmb.ex_nilai_usm_pmb', $data);
    }

    /**
     * Save nilai USM atau set status lulus
     */
    public function save(Request $request)
    {
        try {
            $ac = $request->input('action_do');

            // Handle save nilai (simpannilai)
            if ($ac == 'simpannilai') {
                $cek = $request->input('idpend', []);
                $idjenisusmx = $request->input('idjenisusm', []);
                $jenisusmx = $request->input('jenisusm', []);
                $nilai = $request->input('nilai', []);
                $score = $request->input('score', []);
                $nilaiwawancara = $request->input('nilaiwawancara', []);

                for ($i = 0; $i < count($cek); $i++) {
                    $idact = $cek[$i];
                    $baginilai = 0;
                    $tmpnilai = 0;
                    $loopNilai = count($idjenisusmx[$idact] ?? []);

                    for ($x = 0; $x < $loopNilai; $x++) {
                        $idjenisusm = $idjenisusmx[$idact][$x];
                        $jenisusm = $jenisusmx[$idact][$x];
                        $nilai_input = $nilai[$idact][$x] ?? 0;

                        if ($nilai_input) {
                            if ($jenisusm == 'online') {
                                $tmpnilai += $nilai_input;
                            } else {
                                // Save offline nilai
                                $this->service->saveNilaiUSMOffline($idact, $idjenisusm, $nilai_input);
                                $tmpnilai += $nilai_input;
                            }
                            $baginilai++;
                        }
                    }

                    $nilaiakhir = ($baginilai > 0) ? ($tmpnilai / $baginilai) : 0;

                    if (!empty($nilaiakhir)) {
                        $this->service->updateNilaiAkhirPMB($idact, $nilaiakhir);
                    }
                }

                return response()->json(['status' => 1, 'message' => 'Nilai berhasil disimpan']);
            }

            // Handle set lulus/tidak lulus USM
            if (in_array($ac, ['lulus', 'tidaklulus', 'batallulus'])) {
                $tipe = 'usm';
                $statuslulus = ($ac == 'lulus') ? 1 : (($ac == 'tidaklulus') ? 2 : 0);
                $pilihan_prodi_lulus = $request->input('pilihan_prodi_lulus');

                return $this->processSetLulus($request, $tipe, $statuslulus, $pilihan_prodi_lulus);
            }

            // Handle set lulus/tidak lulus kesehatan
            if (in_array($ac, ['luluskesehatan', 'tidakluluskesehatan', 'batalluluskesehatan'])) {
                $tipe = 'kesehatan';
                $statuslulus = ($ac == 'luluskesehatan') ? 1 : (($ac == 'tidakluluskesehatan') ? 2 : 0);

                return $this->processSetLulus($request, $tipe, $statuslulus, null);
            }

            return response()->json(['status' => 0, 'message' => 'Aksi tidak dikenali']);

        } catch (\Exception $e) {
            \Log::error('NilaiUsmPmbController::save - Error: ' . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Process set lulus (USM or Kesehatan)
     */
    private function processSetLulus(Request $request, string $tipe, int $statuslulus, ?int $pilihan_prodi_lulus)
    {
        try {
            $cek = $request->input('checkID', []);
            $jml = count($cek);
            $success = 0;
            $failed = 0;

            for ($i = 0; $i < $jml; $i++) {
                $idact = $cek[$i];

                if ($tipe == 'usm') {
                    $result = $this->service->setStatusLulusUSM($idact, $statuslulus, $pilihan_prodi_lulus);
                } else {
                    $result = $this->service->setStatusLulusKesehatan($idact, $statuslulus);
                }

                if ($result['status']) {
                    $success++;

                    // Send email if lulus USM
                    if ($tipe == 'usm' && $statuslulus == 1 && ($result['email_sent'] ?? false)) {
                        // TODO: Implement email sending logic
                        // This would call the email API similar to CI3
                    }
                } else {
                    $failed++;
                }
            }

            if ($success > 0 && $failed == 0) {
                return response()->json(['status' => 1, 'message' => 'Berhasil mengubah status']);
            } elseif ($success > 0 && $failed > 0) {
                return response()->json([
                    'status' => 1,
                    'message' => "Berhasil: {$success} data. Gagal: {$failed} data."
                ]);
            } else {
                return response()->json(['status' => 0, 'message' => 'Gagal mengubah status']);
            }

        } catch (\Exception $e) {
            \Log::error('NilaiUsmPmbController::processSetLulus - Error: ' . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Export Excel - Generate XLSX file directly with PhpSpreadsheet
     */
    public function export(Request $request)
    {
        $get = $request->query();
        $post = $request->input();

        $whr = '';
        $offset = 0;
        $bayar = 1;
        $having = '';

        // Build WHERE clause from filters
        if (!empty($post['gelombang']) || !empty($get['gelombang'])) {
            $sgelombang = !empty($post['gelombang']) ? $post['gelombang'] : ($get['gelombang'] ?? '');
            $whr .= " and pmb_tbl_gelombang.ID='" . $sgelombang . "' ";
        }

        if (!empty($post['gelombang_detail']) || !empty($get['gelombang_detail'])) {
            $sgelombang_detail = !empty($post['gelombang_detail']) ? $post['gelombang_detail'] : ($get['gelombang_detail'] ?? '');
            $whr .= " and mahasiswa.gelombang_detail_pmb='" . $sgelombang_detail . "'";
        }

        if (!empty($post['program']) || !empty($get['program'])) {
            $sprogram = !empty($post['program']) ? $post['program'] : ($get['program'] ?? '');
            $whr .= " and mahasiswa.ProgramID='" . $sprogram . "'";
        }

        if (!empty($post['pilihan1']) || !empty($get['pilihan1'])) {
            $spilihan1 = !empty($post['pilihan1']) ? $post['pilihan1'] : ($get['pilihan1'] ?? '');
            $whr .= " and mahasiswa.pilihan1='" . $spilihan1 . "'";
        }

        if (!empty($post['pilihan2']) || !empty($get['pilihan2'])) {
            $spilihan2 = !empty($post['pilihan2']) ? $post['pilihan2'] : ($get['pilihan2'] ?? '');
            $whr .= " and mahasiswa.pilihan2='" . $spilihan2 . "'";
        }

        if (!empty($post['statustest']) || !empty($get['statustest'])) {
            $sstatustest = !empty($post['statustest']) ? $post['statustest'] : ($get['statustest'] ?? '');
            if ($sstatustest == 'selesai') {
                $having = "HAVING (jumlahSelesai >= jumlahUjian AND jumlahUjian != 0)";
            } else {
                $having = "HAVING (jumlahSelesai < jumlahUjian OR jumlahUjian = 0)";
            }
        }

        if (!empty($post['keyword']) || !empty($get['keyword'])) {
            $skeyword = !empty($post['keyword']) ? $post['keyword'] : ($get['keyword'] ?? '');
            $whr .= " and (mahasiswa.noujian_pmb like '%" . $skeyword . "%' or mahasiswa.Nama like '%" . $skeyword . "%')";
        }

        $limit = (!empty($post['viewpage'])) ? $post['viewpage'] : (!empty($get['viewpage']) ? $get['viewpage'] : 1000000);
        $ord_tbh = (!empty($post['orderby'])) ? $post['orderby'] : (!empty($get['orderby']) ? $get['orderby'] : 'mahasiswa.Nama');
        $ord_asc = (!empty($post['descasc'])) ? $post['descasc'] : (!empty($get['descasc']) ? $get['descasc'] : 'asc');

        $limitwhr = "limit $offset, $limit";
        $orderby_calon = $having . " order by $ord_tbh $ord_asc, mahasiswa.ID desc " . $limitwhr;

        $query = $this->service->getMahasiswaPMB([], $bayar, $having, "ORDER BY $ord_tbh $ord_asc, mahasiswa.ID desc", (int)$limit, $offset);

        // Get data jenis USM
        $data_hasil = [];
        $data_hasilonline = [];
        $data_jenisusm = [];

        $qjenisusm = DB::table('pmb_edu_jenisusm')->orderBy('jenis')->get();
        $jumjenisusm = $qjenisusm->count();

        foreach ($qjenisusm as $rowjenisusm) {
            $idjenis = $rowjenisusm->id ?? '';
            $jenisusm = $rowjenisusm->jenis ?? '';
            $namajenis = $rowjenisusm->nama ?? '';

            if ($jenisusm == 'offline') {
                $rhasil = DB::table('pmb_tbl_hasil_usm_baru')
                    ->where('idjenisusm', $idjenis)
                    ->get();
                foreach ($rhasil as $rowhasil) {
                    if (!empty($rowhasil->idpendaftar)) {
                        $data_hasil[$idjenis][$rowhasil->idpendaftar] = [
                            'id' => $rowhasil->idpendaftar,
                            'idpendaftar' => $rowhasil->idpendaftar,
                            'nilai' => $rowhasil->nilai ?? ''
                        ];
                    }
                }
            } elseif ($jenisusm == 'online') {
                $rhasilonline = DB::table('pmb_tbl_hasil_test')
                    ->select('idmember as idpendaftar', DB::raw('AVG(score) as nilai'))
                    ->groupBy('idmember')
                    ->get();
                foreach ($rhasilonline as $rowhasilonline) {
                    if (!empty($rowhasilonline->idpendaftar)) {
                        $data_hasilonline[$idjenis][$rowhasilonline->idpendaftar] = [
                            'id' => $rowhasilonline->idpendaftar,
                            'idpendaftar' => $rowhasilonline->idpendaftar,
                            'nilai' => $rowhasilonline->nilai ?? ''
                        ];
                    }
                }
            }

            $data_jenisusm[$idjenis] = [
                'id' => $idjenis,
                'jenis' => $jenisusm,
                'nama' => $namajenis
            ];
        }

        // Helper function to get column letter
        $getColLetter = function($n) {
            for($r = ""; $n >= 0; $n = intval($n / 26) - 1) {
                $r = chr($n % 26 + 0x41) . $r;
            }
            return $r;
        };

        $total_cols = 5 + $jumjenisusm + 1 + 1;
        $lastColLetter = $getColLetter($total_cols - 1);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Nilai USM');

        $row_num = 1;

        // Add header/kop if function exists
        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, $lastColLetter);
        }

        $sheet->setCellValue('A'.$row_num, 'DATA NILAI USM PMB');
        $sheet->mergeCells('A'.$row_num.':'.$lastColLetter.$row_num);
        $sheet->getStyle('A'.$row_num)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row_num += 2;

        $start_table_row = $row_num;

        $sheet->setCellValue('A'.$row_num, 'No.')->mergeCells('A'.$row_num.':A'.($row_num+1));
        $sheet->setCellValue('B'.$row_num, 'No Ujian')->mergeCells('B'.$row_num.':B'.($row_num+1));
        $sheet->setCellValue('C'.$row_num, 'Nama')->mergeCells('C'.$row_num.':C'.($row_num+1));
        $sheet->setCellValue('D'.$row_num, 'Pilihan')->mergeCells('D'.$row_num.':D'.($row_num+1));
        $sheet->setCellValue('E'.$row_num, 'Program')->mergeCells('E'.$row_num.':E'.($row_num+1));

        $col_idx = 5;
        $start_nilai_letter = $getColLetter($col_idx);
        $end_nilai_letter = $getColLetter($col_idx + $jumjenisusm - 1);

        if ($jumjenisusm > 0) {
            $sheet->setCellValue($start_nilai_letter.$row_num, 'Nilai')->mergeCells($start_nilai_letter.$row_num.':'.$end_nilai_letter.$row_num);
        } else {
            $sheet->setCellValue($start_nilai_letter.$row_num, 'Nilai');
        }

        foreach($data_jenisusm as $j) {
            $letter = $getColLetter($col_idx);
            $sheet->setCellValue($letter.($row_num+1), $j['nama']);
            $col_idx++;
        }

        $letter = $getColLetter($col_idx);
        $sheet->setCellValue($letter.($row_num+1), 'Akhir');
        $col_idx++;

        $letter = $getColLetter($col_idx);
        $sheet->setCellValue($letter.$row_num, 'Lulus USM')->mergeCells($letter.$row_num.':'.$letter.($row_num+1));

        $sheet->getStyle('A'.$row_num.':'.$lastColLetter.($row_num+1))->getFont()->setBold(true);
        $sheet->getStyle('A'.$row_num.':'.$lastColLetter.($row_num+1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A'.$row_num.':'.$lastColLetter.($row_num+1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A'.$row_num.':'.$lastColLetter.($row_num+1))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC000');

        $row_num += 2;

        $no = 1;
        if (!empty($query)) {
            foreach($query as $row) {
                $row = (array) $row;
                $sheet->setCellValue('A'.$row_num, $no++);
                $sheet->setCellValueExplicit('B'.$row_num, $row['noujian_pmb'] ?? '', DataType::TYPE_STRING);
                $sheet->setCellValue('C'.$row_num, $row['Nama'] ?? '');

                $pilihan1 = DB::table('programstudi')->where('ID', $row['pilihan1'] ?? '')->first();
                $pilihan2 = DB::table('programstudi')->where('ID', $row['pilihan2'] ?? '')->first();

                $pilihan_text = "1. " . ($pilihan1->Nama ?? '');
                if(!empty($row['pilihan2']) && $pilihan2) {
                    $pilihan_text .= "\n2. " . ($pilihan2->Nama ?? '');
                }
                $sheet->setCellValue('D'.$row_num, $pilihan_text);
                $sheet->getStyle('D'.$row_num)->getAlignment()->setWrapText(true);

                $sheet->setCellValue('E'.$row_num, $row['programNama'] ?? '-');

                $c_idx = 5;
                foreach($data_jenisusm as $j) {
                    $nilai = "";
                    if($j['jenis'] == 'online') {
                        if(!empty($data_hasilonline[$j['id']])) {
                            foreach ($data_hasilonline[$j['id']] as $h) {
                                if($h['idpendaftar'] == $row['ID']) {
                                    $nilai = $h['nilai'];
                                    break;
                                }
                            }
                        }
                    } else {
                        if(!empty($data_hasil[$j['id']])) {
                            foreach($data_hasil[$j['id']] as $h) {
                                if($h['idpendaftar'] == $row['ID']) {
                                    $nilai = $h['nilai'];
                                    break;
                                }
                            }
                        }
                    }
                    $letter = $getColLetter($c_idx);
                    $sheet->setCellValue($letter.$row_num, $nilai);
                    $sheet->getStyle($letter.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $c_idx++;
                }

                $letter = $getColLetter($c_idx);
                $sheet->setCellValue($letter.$row_num, $row['nilai_pmb'] ?? '');
                $sheet->getStyle($letter.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $c_idx++;

                $status_lulus = $row['statuslulus_pmb'] ?? '';
                if ($status_lulus == "1") {
                    $statuslulus_str = "Lulus";
                } else if ($status_lulus == "2") {
                    $statuslulus_str = "Tidak Lulus";
                } else {
                    $statuslulus_str = "Belum Lulus";
                }

                $letter = $getColLetter($c_idx);
                $sheet->setCellValue($letter.$row_num, $statuslulus_str);
                $sheet->getStyle($letter.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle('A'.$row_num.':'.$lastColLetter.$row_num)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

                $row_num++;
            }
        } else {
            $sheet->setCellValue('A'.$row_num, 'Tidak ada data');
            $sheet->mergeCells('A'.$row_num.':'.$lastColLetter.$row_num);
            $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row_num++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A'.$start_table_row.':'.$lastColLetter.($row_num-1))->applyFromArray($styleBorder);

        for ($i = 0; $i <= $total_cols - 1; $i++) {
            $sheet->getColumnDimension($getColLetter($i))->setAutoSize(true);
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "data_nilai_usm_pmb_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
