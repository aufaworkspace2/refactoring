<?php

namespace App\Http\Controllers;

use App\Services\LaporanStatusInputNilaiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanStatusInputNilaiController extends Controller
{
    protected $service;

    public function __construct(LaporanStatusInputNilaiService $service)
    {
        $this->service = $service;

        $this->middleware(function ($request, $next) {
            if (!Session::get('username')) {
                return redirect('/');
            }
            return $next($request);
        });
    }

    /**
     * Display main view
     */
    public function index()
    {
        log_akses('View', 'Melihat Laporan Status Input Nilai');
        return view('laporan_status_input_nilai.v_laporan_status_input_nilai');
    }

    /**
     * CI3 compatibility alias
     */
    public function LaporanStatusInputNilai()
    {
        return $this->index();
    }

    /**
     * AJAX search for status input nilai
     */
    public function search(Request $request, $offset = 0)
    {
        try {
            $filters = $request->only([
                'ProgramID',
                'ProdiID',
                'TahunID',
                'DosenID',
                'Status'
            ]);

            $result = $this->service->searchData($filters);

            return view('laporan_status_input_nilai.s_laporan_status_input_nilai', [
                'query' => $result['query'],
                'dosenID' => $filters['DosenID'] ?? '',
                'jadwalGabungan' => $result['jadwalGabungan'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add form view
     */
    public function add()
    {
        return view('laporan_status_input_nilai.f_laporan_status_input_nilai', ['save' => 1]);
    }

    /**
     * View/Edit form
     */
    public function view($id)
    {
        $row = $this->service->getById($id);
        return view('laporan_status_input_nilai.f_laporan_status_input_nilai', [
            'row' => $row,
            'save' => 2
        ]);
    }

    /**
     * Save data (add/edit)
     */
    public function save(Request $request, $save)
    {
        try {
            $validated = $request->validate([
                'ID' => 'nullable|integer',
                'Nama' => 'required|string',
                'singkatan' => 'required|string',
                'KodeDikti' => 'required|string',
                'Urut' => 'nullable|integer',
                'TunjanganFungsionalDosKar' => 'nullable|string',
                'TunjanganFungsionalDosSaja' => 'nullable|string',
            ]);

            $check = $this->service->checkDuplicate(
                $validated['KodeDikti'],
                $validated['singkatan'],
                $validated['ID'] ?? null
            );

            if ($check) {
                return response('gagal', 200);
            }

            if ($save == 1) {
                logs('Menambah data ' . $validated['Nama'] . ' pada tabel ' . request()->segment(1));
                $id = $this->service->create($validated);
                return response((string) $id, 200);
            } elseif ($save == 2) {
                logs('Mengubah data ' . ($check->Nama ?? '') . ' menjadi ' . $validated['Nama'] . ' pada tabel ' . request()->segment(1));
                $this->service->update($validated['ID'], $validated);
                return response('sukses', 200);
            }

            return response('invalid', 400);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete records
     */
    public function delete(Request $request)
    {
        try {
            $checkId = $request->input('checkID', []);
            $scripts = '';

            foreach ($checkId as $id) {
                log_akses('Hapus', 'Menghapus Data Jabatan Dengan Nama ' . get_field($id, 'jabatan', 'Nama'));
                $this->service->delete($id);
                $scripts .= '$(".jabatan_' . $id . '").remove();';
            }

            return response('<script>' . $scripts . '</script>', 200)
                ->header('Content-Type', 'text/html');

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export PDF
     */
    public function pdf(Request $request)
    {
        $keyword = $request->query('keyword', '');
        $query = $this->service->getData('', '', $keyword);

        $data['query'] = $query;

        $pdf = Pdf::loadView('laporan_status_input_nilai.p_laporan_status_input_nilai', $data);
        return $pdf->stream('laporan_status_input_nilai.pdf');
    }

    /**
     * Export Excel
     */
    public function excel(Request $request)
    {
        $filters = $request->only([
            'ProgramID',
            'ProdiID',
            'TahunID',
            'DosenID',
            'Status'
        ]);

        $result = $this->service->searchData($filters);
        $query = $result['query'];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Status Input Nilai');

        $rowNum = 1;

        if (function_exists('cetak_kop_phpspreadsheet')) {
            $rowNum = cetak_kop_phpspreadsheet($sheet, 'G');
        }

        $sheet->setCellValue('A' . $rowNum, 'Laporan Status Input Nilai');
        $sheet->mergeCells('A' . $rowNum . ':G' . $rowNum);
        $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $rowNum += 2;

        $startTableRow = $rowNum;

        $sheet->setCellValue('A' . $rowNum, 'No.');
        $sheet->setCellValue('B' . $rowNum, 'Dosen');
        $sheet->setCellValue('C' . $rowNum, 'Kode MK');
        $sheet->setCellValue('D' . $rowNum, 'Mata Kuliah');
        $sheet->setCellValue('E' . $rowNum, 'Kelas');
        $sheet->setCellValue('F' . $rowNum, 'Status Input');
        $sheet->setCellValue('G' . $rowNum, 'Persentase (%)');

        $sheet->getStyle('A' . $rowNum . ':G' . $rowNum)->getFont()->setBold(true);
        $sheet->getStyle('A' . $rowNum . ':G' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $rowNum . ':G' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A' . $rowNum . ':G' . $rowNum)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC000');
        $rowNum++;

        $no = 0;

        if (!empty($query)) {
            foreach ($query as $row) {
                $sheet->setCellValue('A' . $rowNum, ++$no);

                $dosen = get_id($row->dosenID, 'dosen');
                $titleDosen = (!empty($dosen->Title) ? $dosen->Title . ', ' : '');
                $gelarDosen = (!empty($dosen->Gelar) ? ', ' . $dosen->Gelar : '');
                $namaDosen = $titleDosen . ucwords($dosen->Nama ?? '') . $gelarDosen;

                $dosenAnggotaExp = explode(',', $row->dosenAnggota ?? '');
                $countDosen = empty($row->dosenAnggota) ? 0 : count($dosenAnggotaExp);

                $dosenText = "";
                if (!empty($namaDosen)) {
                    $dosenText .= ($dosen->NIP ?? '') . "\n" . $namaDosen . "  [K]";
                }

                if ($countDosen > 0 && !empty($row->dosenAnggota)) {
                    $dosenText .= "\n\nDosen Anggota :\n";
                    for ($i = 0; $i < $countDosen; $i++) {
                        $dosenAng = get_id($dosenAnggotaExp[$i], 'dosen');
                        if ($dosenAng) {
                            $dosenText .= ($dosenAng->NIP ?? '') . "\n" . ($dosenAng->Title ?? '') . ' ' . ($dosenAng->Nama ?? '') . ' ' . ($dosenAng->Gelar ?? '') . "\n";
                        }
                    }
                }
                $sheet->setCellValue('B' . $rowNum, trim($dosenText));
                $sheet->getStyle('B' . $rowNum)->getAlignment()->setWrapText(true);

                $sheet->setCellValueExplicit('C' . $rowNum, $row->mkkode ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                $mkText = html_entity_decode($row->namaMatkul ?? '');
                if ($row->gabungan == 'YA') {
                    $mkText .= "\n(Jadwal Gabungan)";
                }
                $sheet->setCellValue('D' . $rowNum, $mkText);
                $sheet->getStyle('D' . $rowNum)->getAlignment()->setWrapText(true);

                $kelasText = get_field($row->kelasID, 'kelas');
                $sheet->setCellValue('E' . $rowNum, $kelasText);

                $statusText = $row->persentaseNilai > 0 ? 'Sudah' : 'Belum';
                $sheet->setCellValue('F' . $rowNum, $statusText);

                $sheet->setCellValue('G' . $rowNum, $row->persentaseNilai);

                $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A' . $rowNum . ':G' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyle('C' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('G' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $rowNum++;
            }
        } else {
            $sheet->setCellValue('A' . $rowNum, 'Maaf jadwal yang anda cari tidak ditemukan');
            $sheet->mergeCells('A' . $rowNum . ':G' . $rowNum);
            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $rowNum++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $startTableRow . ':G' . ($rowNum - 1))->applyFromArray($styleBorder);

        if (!function_exists('cetak_kop_phpspreadsheet')) {
            $sheet->getColumnDimension('A')->setAutoSize(true);
        }
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = time() . "_LaporanStatusInputNilai.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $objWriter->save('php://output');
        exit;
    }
}
