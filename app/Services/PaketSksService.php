<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PaketSksService
{
    public function getPaketSksByProdi($prodiId)
    {
        return DB::table('paket_sks')
            ->where('ProdiID', $prodiId)
            ->first();
    }

    public function savePaketSks($prodiId, $semesterPaketArray)
    {
        // Delete existing data
        DB::table('paket_sks')->where('ProdiID', $prodiId)->delete();

        // Insert new data
        if (!empty($semesterPaketArray)) {
            return DB::table('paket_sks')->insert([
                'ProdiID' => $prodiId,
                'SemesterPaket' => implode(',', $semesterPaketArray),
                'createdAt' => date('Y-m-d H:i:s'),
                'UserID' => Session::get('UserID')
            ]);
        }

        return true;
    }

    public function getAllProdi($levelKode, $userProdiId)
    {
        $query = DB::table('programstudi')
            ->select('ID', 'Nama', 'JenjangID');

        if (!in_array('SPR', explode(',', $levelKode ?? ''))) {
            $query->whereIn('ID', explode(',', $userProdiId ?? '0'));
        }

        return $query->get();
    }
}
