<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Mahasiswa;
use stdClass;

class PerkembanganAkademikService
{
    /**
     * Get data for transcript printing
     */
    public function getTranscriptData($mhswId)
    {
        $data = new stdClass();
        $data->mahasiswa = Mahasiswa::find($mhswId);
        $data->tahunAktif = DB::table('tahun')->where('ProsesBuka', '1')->first();
        $data->tugasAkhir = DB::table('tugasakhir')->where('MhswID', $mhswId)->first();
        
        // Get KAA (Kepala Administrasi Akademik)
        $data->ka = DB::table('karyawan')
            ->where('Jabatan2', '1')
            ->select(DB::raw("CONCAT(Title, '. ', Nama, ', ', Gelar) as NamaGelar"), 'NIP')
            ->first();

        // Get WKA (Wakil Ketua Akademik)
        $data->wka = DB::table('karyawan')
            ->where('Jabatan2', '4')
            ->select(DB::raw("CONCAT(Title, '. ', Nama, ', ', Gelar) as NamaGelar"), 'NIP')
            ->first();

        return $data;
    }

    /**
     * Get data for class development report
     */
    public function getClassDevelopmentData($params)
    {
        $query = DB::table('mahasiswa as a')
            ->join('rencanastudi as b', 'a.ID', '=', 'b.MhswID')
            ->join('detailkurikulum as c', 'b.DetailkurikulumID', '=', 'c.ID')
            ->join('tahun as d', 'b.TahunID', '=', 'd.ID')
            ->select('a.ID as MhswID', 'a.Nama', 'a.NPM', 'b.DetailKurikulumID', 'd.TahunID as KodeTahun', 'c.Nama as NamaMK', 'c.MKKode');

        if (!empty($params['ProgramID'])) $query->where('a.ProgramID', $params['ProgramID']);
        if (!empty($params['ProdiID'])) $query->where('a.ProdiID', $params['ProdiID']);
        if (!empty($params['TahunMasuk'])) $query->where('a.TahunMasuk', $params['TahunMasuk']);

        $results = $query->get();
        
        $data = [
            'mahasiswa' => [],
            'mk' => []
        ];

        foreach ($results as $row) {
            $data['mahasiswa']['NPM'][$row->MhswID] = $row->NPM;
            $data['mahasiswa']['Nama'][$row->MhswID] = $row->Nama;
            $data['mk'][$row->MhswID][$row->DetailKurikulumID] = $row;
        }

        return $data;
    }
}
