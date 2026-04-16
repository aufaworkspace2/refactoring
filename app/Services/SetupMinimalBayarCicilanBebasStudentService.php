<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SetupMinimalBayarCicilanBebasStudentService
{
    public function get_data($limit, $offset, $keyword, $ProgramID, $ProdiID, $TahunMasuk)
    {
        $query = DB::table('setup_minimal_bayar_cicilan_bebas_student');

        if ($keyword) {
            $query->where('Nama', 'like', "%{$keyword}%");
        }

        if ($ProgramID !== null && $ProgramID !== "") {
            $query->where('ProgramID', $ProgramID);
        }

        if ($ProdiID !== null && $ProdiID !== "") {
            $ProdiID_arr = explode(',', $ProdiID);
            $query->whereIn('ProdiID', $ProdiID_arr);
        }

        if ($TahunMasuk !== null && $TahunMasuk !== "") {
            $query->where('TahunMasuk', $TahunMasuk);
        }

        $query->orderBy('ID', 'ASC');

        if ($limit) {
            return $query->skip($offset)->take($limit)->get();
        }

        return $query->get();
    }

    public function count_all($keyword, $ProgramID, $ProdiID, $TahunMasuk)
    {
        $query = DB::table('setup_minimal_bayar_cicilan_bebas_student');

        if ($keyword) {
            $query->where('Nama', 'like', "%{$keyword}%");
        }

        if ($ProgramID !== null && $ProgramID !== "") {
            $query->where('ProgramID', $ProgramID);
        }

        if ($ProdiID !== null && $ProdiID !== "") {
            $ProdiID_arr = explode(',', $ProdiID);
            $query->whereIn('ProdiID', $ProdiID_arr);
        }

        if ($TahunMasuk !== null && $TahunMasuk !== "") {
            $query->where('TahunMasuk', $TahunMasuk);
        }

        return $query->count();
    }

    public function get_id($id)
    {
        return DB::table('setup_minimal_bayar_cicilan_bebas_student')
            ->where('ID', $id)
            ->first();
    }
}
