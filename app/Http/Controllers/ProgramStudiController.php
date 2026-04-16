<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ProgramStudiController extends Controller
{
    /**
     * AJAX: Get Program Studi based on Program and Tahun
     * Corresponds to: C_programstudi->changeprodi()
     */
    public function changeprodi(Request $request, $ket = '')
    {
        $programID = $request->input('ProgramID');
        $tahunID = $request->input('TahunID');
        $selectedProdiID = $request->input('ProdiID');

        $query = DB::table('programstudi');

        // Permission filter (Simplified version of legacy logic)
        if (Session::get('cek_superadmin') < 1) {
            $entityID = Session::get('EntityID');
            $prodiList = DB::table('karyawan')->where('ID', $entityID)->value('ProdiID');
            if ($prodiList) {
                $query->whereIn('ID', explode(',', $prodiList));
            }
        }

        $prodis = $query->orderBy('Nama', 'ASC')->get();

        $hasil = ($ket != '') ? "<option value=''>-- Pilih Semua Prodi --</option>" : '';
        
        foreach ($prodis as $pro) {
            $sl = ($selectedProdiID == $pro->ID) ? "selected" : "";
            $jenjang = get_field($pro->JenjangID, 'jenjang');
            $hasil .= '<option ' . $sl . ' value="' . $pro->ID . '">' . $pro->ProdiID . ' || ' . $jenjang . ' || ' . $pro->Nama . '</option>';
        }

        if (empty($hasil)) {
            $hasil = '<option value="">Maaf Program Studi Tidak Ditemukan</option>';
        }

        return response($hasil);
    }
}
