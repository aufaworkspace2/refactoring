<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LevelModulService;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LevelModulController extends Controller
{
    protected $service;

    public function __construct(LevelModulService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $level = $request->input('level', ''); // default empty string if not provided
        $data['level'] = $level;

        return view('levelmodul.v_levelmodul', $data);
    }

    public function search(Request $request)
    {
        $level = $request->input('level', '');
        $keyword = $request->input('keyword', '');

        // Setup pagination constants, similar to CI3 logic
        $limit = 10;
        $offset = $request->input('offset', 0);

        $data['my_level'] = $level;
        $data['query'] = $this->service->get_data($limit, $offset, $level, $keyword);
        $data['jml'] = $this->service->count_all($level, $keyword);
        $data['limit'] = $limit;
        $data['offset'] = $offset;

        return view('levelmodul.s_levelmodul', $data);
    }

    public function add()
    {
        $data['save'] = 1;
        return view('levelmodul.f_levelmodul', $data);
    }

    public function view($id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['save'] = 2;
        return view('levelmodul.f_levelmodul', $data);
    }

    public function save(Request $request, $save = '')
    {
        $LevelID = $request->input('LevelID');
        $input['LevelID'] = $input2['LevelID'] = $erp['LevelID'] = $LevelID;

        // Migrasi fungsi save() dari CI3 C_levelmodul
        $modulgrups = DB::table('modulgrup')->get();
        foreach ($modulgrups as $row) {
            $moduls = DB::table('modul')->where('MdlGrpID', $row->ID)->get();
            foreach ($moduls as $raw) {
                $Create = $request->input('input_modul_' . $raw->ID);
                $Read = $request->input('lihat_modul_' . $raw->ID);
                $Update = $request->input('update_modul_' . $raw->ID);
                $Delete = $request->input('delete_modul_' . $raw->ID);

                $input['ModulID'] = $raw->ID;
                $input['Create'] = ($Create) ? "YA" : "TIDAK";
                $input['Read'] = ($Read) ? "YA" : "TIDAK";
                $input['Update'] = ($Update) ? "YA" : "TIDAK";
                $input['Delete'] = ($Delete) ? "YA" : "TIDAK";
                $input['type'] = 'modul';

                DB::table('levelmodul')->updateOrInsert(
                ['LevelID' => $input['LevelID'], 'ModulID' => $raw->ID, 'type' => 'modul'],
                    $input
                );

                $submoduls = DB::table('submodul')->where('ModulID', $raw->ID)->get();
                foreach ($submoduls as $riw) {
                    $Create2 = $request->input('input_submodul_' . $riw->ID);
                    $Read2 = $request->input('lihat_submodul_' . $riw->ID);
                    $Update2 = $request->input('update_submodul_' . $riw->ID);
                    $Delete2 = $request->input('delete_submodul_' . $riw->ID);

                    $input2['ModulID'] = $riw->ID;
                    $input2['Create'] = ($Create2) ? "YA" : "TIDAK";
                    $input2['Read'] = ($Read2) ? "YA" : "TIDAK";
                    $input2['Update'] = ($Update2) ? "YA" : "TIDAK";
                    $input2['Delete'] = ($Delete2) ? "YA" : "TIDAK";
                    $input2['type'] = 'submodul';

                    DB::table('levelmodul')->updateOrInsert(
                    ['LevelID' => $input2['LevelID'], 'ModulID' => $riw->ID, 'type' => 'submodul'],
                        $input2
                    );
                }
            }
        }

        $sql = "SELECT ModulID FROM submodul WHERE ID IN (SELECT ModulID FROM levelmodul WHERE `Read` = 'YA' AND type = 'submodul' AND LevelID = ?)";
        $readYaSubmoduls = DB::select($sql, [$LevelID]);

        $ubah['Read'] = 'YA';
        foreach ($readYaSubmoduls as $row) {
            DB::table('levelmodul')
                ->where('ModulID', $row->ModulID)
                ->where('type', 'modul')
                ->where('LevelID', $LevelID)
                ->update($ubah);
        }

        // Handle Level Modul ERP
        $modulgrups_erp = DB::table('modulgrup')
            ->where('AksesID', 5)
            ->orderBy('Urut', 'ASC')
            ->get();

        foreach ($modulgrups_erp as $row) {
            $erp['MdlGrpID'] = $row->ID;
            $Active = $request->input('erp_modul_' . $row->ID);
            $erp['Active'] = ($Active) ? "YA" : "TIDAK";

            DB::table('levelmodulerp')->updateOrInsert(
            ['LevelID' => $LevelID, 'MdlGrpID' => $row->ID],
                $erp
            );
        }

        // Return JSON success or redirect, mimicking CI3 AJAX behavior
        return response()->json(['status' => 'success']);
    }

    public function delete(Request $request)
    {
        $checkid = $request->input('checkID');
        if ($checkid) {
            $this->service->delete($checkid);
        }

        // Return a response so jQuery AJAX success block triggers
        return response()->json(['status' => 'success', 'deleted' => $checkid]);
    }

    public function pdf(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $level = $request->input('level', '');

        // Fetching all without limit via null pass to get_data
        $data['query'] = $this->service->get_data(null, null, $level, $keyword);

        $pdf = Pdf::loadView('levelmodul.p_levelmodul', $data);
        return $pdf->stream('data_levelmodul.pdf');
    }

    public function excel(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $level = $request->input('level', '');

        $data = $this->service->get_data(null, null, $level, $keyword);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Level Modul');

        $row_num = 1;
        
        if (function_exists('cetak_kop_phpspreadsheet')) {
            $row_num = cetak_kop_phpspreadsheet($sheet, 'I');
        }

        $sheet->setCellValue('A'.$row_num, 'DATA LEVEL MODUL');
        $sheet->mergeCells('A'.$row_num.':I'.$row_num); 
        $sheet->getStyle('A'.$row_num)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row_num += 2; 
        $start_table_row = $row_num;
        
        $sheet->setCellValue('A'.$row_num, 'No.');
        $sheet->setCellValue('B'.$row_num, 'Level ID');
        $sheet->setCellValue('C'.$row_num, 'Modul ID');
        $sheet->setCellValue('D'.$row_num, 'Create');
        $sheet->setCellValue('E'.$row_num, 'Read');
        $sheet->setCellValue('F'.$row_num, 'Update');
        $sheet->setCellValue('G'.$row_num, 'Delete');
        $sheet->setCellValue('H'.$row_num, 'Shortcut');
        $sheet->setCellValue('I'.$row_num, 'Icon');

        $sheet->getStyle('A'.$row_num.':I'.$row_num)->getFont()->setBold(true);
        $sheet->getStyle('A'.$row_num.':I'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A'.$row_num.':I'.$row_num)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFC000');
        $row_num++;

        $no = 1;
        if (!empty($data)) {
            foreach ($data as $row) {
                $row = (object) $row;
                
                $sheet->setCellValue('A'.$row_num, $no++);
                $sheet->setCellValue('B'.$row_num, $row->LevelID ?? '');
                $sheet->setCellValue('C'.$row_num, $row->ModulID ?? '');
                $sheet->setCellValue('D'.$row_num, $row->Create ?? '');
                $sheet->setCellValue('E'.$row_num, $row->Read ?? '');
                $sheet->setCellValue('F'.$row_num, $row->Update ?? '');
                $sheet->setCellValue('G'.$row_num, $row->Delete ?? '');
                $sheet->setCellValue('H'.$row_num, $row->Shortcut ?? '');
                $sheet->setCellValue('I'.$row_num, $row->Icon ?? '');
                
                $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $row_num++;
            }
        } else {
            $sheet->setCellValue('A'.$row_num, 'Tidak ada data');
            $sheet->mergeCells('A'.$row_num.':I'.$row_num);
            $sheet->getStyle('A'.$row_num)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row_num++;
        }

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A'.$start_table_row.':I'.($row_num-1))->applyFromArray($styleBorder);

        foreach(range('A','I') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = "data_levelmodul_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
