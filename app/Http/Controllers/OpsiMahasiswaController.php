<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OpsiMahasiswaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class OpsiMahasiswaController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(OpsiMahasiswaService $service)
    {
        $this->service = $service;

        $this->middleware(function ($request, $next) {
            // Language setup
            $lang = Session::get('language');
            if (!$lang && !$request->cookie('language')) {
                $lang = 'indonesia';
                Session::put('language', $lang);
            }

            // Map legacy language names to Laravel locales
            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);

            $levelUser = Session::get('LevelUser');

            $this->Create = cek_level($levelUser, 'c_opsi_mahasiswa', 'Create');
            $this->Update = cek_level($levelUser, 'c_opsi_mahasiswa', 'Update');
            $this->Delete = cek_level($levelUser, 'c_opsi_mahasiswa', 'Delete');

            return $next($request);
        });
    }

    public function index()
    {
        $data['Create'] = $this->Create;
        $data['tahunMasuk'] = $this->service->getAngkatan();

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Mahasiswa');
        }

        // Check opsi mahasiswa setup
        $buka_opsi_nilai = $this->service->checkOpsiNilai();
        $data['buka_opsi_nilai'] = $buka_opsi_nilai;

        return view('opsi_mahasiswa.v_opsi_mahasiswa', $data);
    }

    public function search(Request $request, $offset = 0)
    {
        $tahunID = $request->input('tahunID', '');
        $programID = $request->input('programID', '');
        $prodiID = $request->input('prodiID', '');
        $statusMhsw = $request->input('statusMhswID', '');
        $SemesterMasuk = $request->input('SemesterMasuk', '');
        $tahunMasuk = $request->input('tahunMasuk', '');
        $statusBayar = $request->input('statusBayar', '');
        $statusInput = $request->input('statusInput', '');
        $keyword = $request->input('keyword', '');
        $pilihan = $request->input('type', '');

        $limit = 50;

        $jml = $this->service->countData(
            $tahunID, $programID, $prodiID, $statusMhsw, $tahunMasuk,
            $SemesterMasuk, $statusBayar, $statusInput, $keyword
        );

        $data['tahunID'] = $tahunID;
        $data['programID'] = $programID;
        $data['prodiID'] = $prodiID;
        $data['statusMhsw'] = $statusMhsw;
        $data['tahunMasuk'] = $tahunMasuk;
        $data['statusBayar'] = $statusBayar;
        $data['statusInput'] = $statusInput;
        $data['keyword'] = $keyword;
        $data['pilihan'] = $pilihan;
        $data['offset'] = $offset;

        $data['query'] = $this->service->getData(
            $tahunID, $programID, $prodiID, $statusMhsw, $tahunMasuk,
            $SemesterMasuk, $statusBayar, $statusInput, $keyword, $limit, $offset
        );
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);

        // Check opsi mahasiswa setup
        $buka_opsi_nilai = $this->service->checkOpsiNilai();
        $data['buka_opsi_nilai'] = $buka_opsi_nilai;

        return view('opsi_mahasiswa.s_mahasiswa_opsi', $data);
    }

    public function save(Request $request)
    {
        $idMahasiswa = $request->input('checkID', []);
        $tahunID = $request->input('tahunID', '');
        $programID = $request->input('programID', '');
        $prodiID = $request->input('prodiID', '');
        $statusMhsw = $request->input('statusMhswID', '');
        $tahunMasuk = $request->input('tahunMasuk', '');
        $krsOption = $request->input('krsOption', '');
        $utsOption = $request->input('utsOption', '');
        $uasOption = $request->input('uasOption', '');
        $khsOption = $request->input('khsOption', '');
        $transkripOption = $request->input('transkripOption', '');
        $type = $request->input('pilihan', 'off');
        $keyword = $request->input('keyword', '');
        $SemesterMasuk = $request->input('SemesterMasuk', '');
        $statusBayar = $request->input('statusBayar', '');
        $statusInput = $request->input('statusInput', '');

        $input['KRS'] = ($krsOption == 'on' ? '1' : '0');
        $input['UTS'] = ($utsOption == 'on' ? '1' : '0');
        $input['UAS'] = ($uasOption == 'on' ? '1' : '0');
        $input['KHS'] = ($khsOption == 'on' ? '1' : '0');
        $input['TRANSKRIP'] = ($transkripOption == 'on' ? '1' : '0');

        $KodeTahun = get_field($tahunID, 'tahun', 'TahunID');

        $affectedRow = 0;
        $tempResponse = [];

        if ($type == 'off') {
            // Process only selected students
            if (count($idMahasiswa) > 0) {
                foreach ($idMahasiswa as $value) {
                    $cekData = $this->service->getDataBy('ID', ['MhswID' => $value, 'TahunID' => $tahunID], 'opsi_mahasiswa', 1);

                    $input['MhswID'] = $value;
                    $input['TahunID'] = $tahunID;

                    if (!empty($cekData->ID)) {
                        $affectedRow += $this->service->updateData(['ID' => $cekData->ID], 'opsi_mahasiswa', $input);
                        if (function_exists('log_akses')) {
                            log_akses('Update', 'Mengubah data ' . json_encode($input));
                        }
                    } else {
                        $affectedRow += $this->service->insertData('opsi_mahasiswa', $input);
                        if (function_exists('log_akses')) {
                            log_akses('Insert', 'Menginputkan data ' . json_encode($input));
                        }
                    }

                    // Call elearning API if available
                    if (function_exists('insert_akses_uts_uas')) {
                        insert_akses_uts_uas(get_field($value, 'mahasiswa', 'NPM'), $KodeTahun, $input['UTS'], $input['UAS']);
                    }
                }

                if ($affectedRow > 0) {
                    $tempResponse['status'] = 1;
                    $tempResponse['message'] = 'Data Berhasil Disimpan !';
                } else {
                    $tempResponse['status'] = 0;
                    $tempResponse['message'] = 'Maaf, Tidak Ada Perubahan Data !';
                }
            } else {
                $tempResponse['status'] = 0;
                $tempResponse['message'] = 'Data Gagal Disimpan !';
            }
        } else {
            // Process all students based on filter
            $listQuery = $this->service->getData(
                $tahunID, $programID, $prodiID, $statusMhsw, $tahunMasuk,
                $SemesterMasuk, $statusBayar, $statusInput, $keyword, 1000000, 0
            );

            if (count($listQuery) > 0) {
                foreach ($listQuery as $value) {
                    $cekData = $this->service->getDataBy('ID', ['MhswID' => $value->ID, 'TahunID' => $tahunID], 'opsi_mahasiswa', 1);

                    $input['MhswID'] = $value->ID;
                    $input['TahunID'] = $tahunID;

                    if (!empty($cekData->ID)) {
                        $affectedRow += $this->service->updateData(['ID' => $cekData->ID], 'opsi_mahasiswa', $input);
                        if (function_exists('log_akses')) {
                            log_akses('Update', 'Mengubah data ' . json_encode($input));
                        }
                    } else {
                        $affectedRow += $this->service->insertData('opsi_mahasiswa', $input);
                        if (function_exists('log_akses')) {
                            log_akses('Insert', 'Menginputkan data ' . json_encode($input));
                        }
                    }

                    // Call elearning API if available
                    if (function_exists('insert_akses_uts_uas')) {
                        insert_akses_uts_uas(get_field($value->ID, 'mahasiswa', 'NPM'), $KodeTahun, $input['UTS'], $input['UAS']);
                    }
                }

                if ($affectedRow > 0) {
                    $tempResponse['status'] = 1;
                    $tempResponse['message'] = 'Data Berhasil Disimpan !';
                } else {
                    $tempResponse['status'] = 0;
                    $tempResponse['message'] = 'Maaf, Tidak Ada Perubahan Data !';
                }
            } else {
                $tempResponse['status'] = 0;
                $tempResponse['message'] = 'Data Gagal Disimpan !';
            }
        }

        return response()->json($tempResponse);
    }

    public function excel(Request $request)
    {
        $tahunID = $request->input('tahunID', '');
        $programID = $request->input('programID', '');
        $prodiID = $request->input('prodiID', '');
        $statusMhsw = $request->input('statusMhswID', '');
        $tahunMasuk = $request->input('tahunMasuk', '');
        $statusBayar = $request->input('statusBayar', '');
        $statusInput = $request->input('statusInput', '');
        $keyword = $request->input('keyword', '');
        $SemesterMasuk = $request->input('SemesterMasuk', '');

        $offset = 0;
        $limit = 1000000;

        $query_data = $this->service->getData(
            $tahunID, $programID, $prodiID, $statusMhsw, $tahunMasuk,
            $SemesterMasuk, $statusBayar, $statusInput, $keyword, $limit, $offset
        );

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Aktivasi Mahasiswa');

        $row_num = 1;

        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'K');
        }

        $nama_tahun = get_field($tahunID, 'tahun');
        $slog_text = strtoupper("Data Aktivasi KRS/UTS/UAS Mahasiswa " . $nama_tahun);

        $sheet->setCellValue('A' . $row_num, $slog_text);
        $sheet->mergeCells('A' . $row_num . ':K' . $row_num);
        $sheet->getStyle('A' . $row_num)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $row_num += 2;

        $start_table_row = $row_num;

        $sheet->setCellValue('A' . $row_num, 'No.');
        $sheet->setCellValue('B' . $row_num, __('app.NPM') ?? 'NPM');
        $sheet->setCellValue('C' . $row_num, __('app.Nama') ?? 'Nama');
        $sheet->setCellValue('D' . $row_num, 'Program');
        $sheet->setCellValue('E' . $row_num, 'Program Studi');
        $sheet->setCellValue('F' . $row_num, 'Jumlah Tagihan');
        $sheet->setCellValue('G' . $row_num, 'Jumlah Bayar');
        $sheet->setCellValue('H' . $row_num, 'Status');
        $sheet->setCellValue('I' . $row_num, 'KRS');
        $sheet->setCellValue('J' . $row_num, 'UTS');
        $sheet->setCellValue('K' . $row_num, 'UAS');

        $sheet->getStyle('A' . $row_num . ':K' . $row_num)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row_num . ':K' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row_num . ':K' . $row_num)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A' . $row_num . ':K' . $row_num)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC000');
        $row_num++;

        $no = 1;
        if (!empty($query_data)) {
            foreach ($query_data as $row) {
                $sheet->setCellValue('A' . $row_num, $no++);
                $sheet->setCellValueExplicit('B' . $row_num, $row->NPM ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('C' . $row_num, $row->Nama ?? '');
                $sheet->setCellValue('D' . $row_num, $row->Program ?? '');
                $sheet->setCellValue('E' . $row_num, $row->Prodi ?? '');

                $tagihan = isset($row->TotalTagihan) ? $row->TotalTagihan : 0;
                $cicilan = isset($row->TotalCicilan) ? $row->TotalCicilan : 0;

                $sheet->setCellValue('F' . $row_num, $tagihan);
                $sheet->getStyle('F' . $row_num)->getNumberFormat()->setFormatCode('"Rp "#,##0');

                $sheet->setCellValue('G' . $row_num, $cicilan);
                $sheet->getStyle('G' . $row_num)->getNumberFormat()->setFormatCode('"Rp "#,##0');

                $status_text = '';
                if ($tagihan > 0) {
                    if (isset($row->StatusBayar) && $row->StatusBayar == 1) {
                        $status_text = 'Sudah Lunas';
                    } else {
                        $status_text = 'Belum Lunas';
                    }
                } else {
                    $status_text = 'Belum Ada Tagihan';
                }
                $sheet->setCellValue('H' . $row_num, $status_text);

                $krs_val = (isset($row->KRS) && $row->KRS == 1) ? '√' : 'X';
                $uts_val = (isset($row->UTS) && $row->UTS == 1) ? '√' : 'X';
                $uas_val = (isset($row->UAS) && $row->UAS == 1) ? '√' : 'X';

                $sheet->setCellValue('I' . $row_num, $krs_val);
                $sheet->setCellValue('J' . $row_num, $uts_val);
                $sheet->setCellValue('K' . $row_num, $uas_val);

                $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('H' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('I' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('J' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('K' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $row_num++;
            }
        } else {
            $sheet->setCellValue('A' . $row_num, 'Tidak ada data aktivasi mahasiswa');
            $sheet->mergeCells('A' . $row_num . ':K' . $row_num);
            $sheet->getStyle('A' . $row_num)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $row_num++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $start_table_row . ':K' . ($row_num - 1))->applyFromArray($styleBorder);

        if (!function_exists('cetak_kop_phpspreadsheet')) {
            $sheet->getColumnDimension('A')->setAutoSize(true);
        }

        for ($col = 'B'; $col !== 'L'; $col++) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "data_aktivasi_krs_uts_uas_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $objWriter->save('php://output');
        exit;
    }
}
