<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeneralAjaxController extends Controller
{
    /**
     * AJAX: Get Kurikulum based on Prodi and Program
     * Corresponds to: C_kurikulum->onchange()
     */
    public function changekurikulum(Request $request)
    {
        $prodiID = $request->input('ProdiID');
        $programID = $request->input('ProgramID');

        $query = DB::table('kurikulum')
            ->where('Prodi2', $prodiID)
            ->where('Program', $programID)
            ->orderBy('Nama', 'DESC')
            ->get();

        $hasil = '<option value=""> -- Semua Kurikulum -- </option>';
        foreach ($query as $row) {
            $hasil .= '<option value="' . $row->ID . '">' . $row->Nama . '</option>';
        }

        return response($hasil);
    }

    /**
     * AJAX: Get Konsentrasi based on Prodi
     * Corresponds to: C_detailkurikulum->changekonsentrasi()
     */
    public function changekonsentrasi(Request $request)
    {
        $prodiID = $request->input('ProdiID');

        $query = DB::table('konsentrasi')
            ->where('ProdiID', $prodiID)
            ->orderBy('Nama', 'ASC')
            ->get();

        $hasil = '<option value=""> -- Tidak Ada Konsentrasi -- </option>';
        foreach ($query as $row) {
            $hasil .= '<option value="' . $row->ID . '">' . $row->Nama . '</option>';
        }

        return response($hasil);
    }

    /**
     * AJAX: Get Kelas based on Prodi
     * Corresponds to: C_kelas->changekelas()
     */
    public function changekelas(Request $request)
    {
        $prodiID = $request->input('ProdiID');

        $query = DB::table('kelas')
            ->where('ProdiID', $prodiID)
            ->orderBy('Nama', 'ASC')
            ->get();

        $hasil = '<option value=""> -- Semua Kelas -- </option>';
        foreach ($query as $row) {
            $hasil .= '<option value="' . $row->ID . '">' . $row->Nama . '</option>';
        }

        return response($hasil);
    }

    /**
     * AJAX: Get Semester based on filter
     * Corresponds to: C_jadwal->changesemester()
     */
    public function changesemester(Request $request)
    {
        $prodiID = $request->input('prodiID');
        $programID = $request->input('programID');
        $kurikulumID = $request->input('kurikulumID');

        $query = DB::table('detailkurikulum')
            ->select('Semester')
            ->where('ProdiID', $prodiID)
            ->where('ProgramID', $programID);
        
        if ($kurikulumID) {
            $query->where('KurikulumID', $kurikulumID);
        }

        $semesters = $query->distinct()->orderBy('Semester', 'ASC')->pluck('Semester');

        $hasil = '<option value=""> -- Semua Semester -- </option>';
        foreach ($semesters as $sem) {
            $hasil .= '<option value="' . $sem . '">Semester ' . $sem . '</option>';
        }

        return response($hasil);
    }
}
