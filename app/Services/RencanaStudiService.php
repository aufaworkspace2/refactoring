<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Mahasiswa;
use stdClass;

class RencanaStudiService
{
    /**
     * Get transcript search data
     */
    public function searchTranscript($params, $limit = 10, $offset = 0)
    {
        $mahasiswaService = app(MahasiswaService::class);
        
        $jml = $mahasiswaService->count_all(
            $params['ProgramID'] ?? '',
            $params['ProdiID'] ?? '',
            $params['KelasID'] ?? '',
            $params['StatusMhswID'] ?? '',
            $params['TahunMasuk'] ?? '',
            $params['JenjangID'] ?? '',
            $params['keyword'] ?? '',
            '', '',
            $params['SemesterMasuk'] ?? ''
        );

        $query = $mahasiswaService->get_data(
            $limit, $offset,
            $params['ProgramID'] ?? '',
            $params['ProdiID'] ?? '',
            $params['KelasID'] ?? '',
            $params['StatusMhswID'] ?? '',
            $params['TahunMasuk'] ?? '',
            $params['JenjangID'] ?? '',
            $params['keyword'] ?? '',
            '', '', '',
            $params['SemesterMasuk'] ?? ''
        );

        return [
            'query' => $query,
            'total' => $jml
        ];
    }

    /**
     * Get data for "Perkembangan Akademik" PDF
     */
    public function getPerkembanganData($mhswId)
    {
        $data = [];
        $data['TahunAktif'] = DB::table('tahun')->where('ProsesBuka', '1')->first();
        $row = DB::table('mahasiswa')->where('ID', $mhswId)->first();
        
        if (!$row) return null;

        $data['mhs'] = $row;
        $data['MhswID'] = $mhswId;
        $data['NPM'] = $row->NPM;
        $data['JenjangID'] = get_field($row->JenjangID, 'jenjang');
        $data['Nama'] = ucwords($row->Nama);
        $data['ProdiID'] = get_field($row->ProdiID, 'programstudi');
        $data['IDProdiID'] = $row->ProdiID;
        $data['ProgramID'] = $row->ProgramID;
        $data['TempatLahir'] = ucwords(strtolower($row->TempatLahir ?? ''));
        $data['TanggalLahir'] = $row->TanggalLahir;
        $data['TanggalLulus'] = $row->TanggalLulus;

        $jab = ($row->ProdiID == '3') ? '35' : '2';

        $sql_krs = "SELECT rencanastudi.ID, detailkurikulum.MKKode, detailkurikulum.Semester, detailkurikulum.Nama AS NamaMataKuliah, detailkurikulum.TotalSKS, nilai.NilaiAkhir, nilai.Bobot, nilai.NilaiBobot, nilai.NilaiHuruf, tahun.TahunID AS TahunID
                    FROM nilai 
                    LEFT JOIN rencanastudi ON nilai.rencanastudiID = rencanastudi.ID 
                    LEFT JOIN detailkurikulum ON detailkurikulum.ID = nilai.DetailKurikulumID 
                    LEFT JOIN tahun ON tahun.ID = rencanastudi.TahunID
                    WHERE nilai.MhswID = ?";
        
        $query_krs = DB::select($sql_krs, [$mhswId]);

        $query_all = [];
        foreach ($query_krs as $item) {
            $query_all[$item->TahunID][] = $item;
        }
        ksort($query_all);

        $data['query_all'] = $query_all;

        // Get KA and WKA
        $data['KA'] = "";
        $data['NIPKA'] = "";
        $ka = DB::table('karyawan')->where('Jabatan1', '1')->first();
        if ($ka) {
            $data['KA'] = ($ka->Title ? $ka->Title.' ' : '').$ka->Nama.($ka->Gelar ? ' '.$ka->Gelar : '');
            $data['NIPKA'] = $ka->NIP;
        }

        $data['WKA'] = "";
        $data['NIPWKA'] = "";
        $wka = DB::table('karyawan')->where('Jabatan1', $jab)->first();
        if ($wka) {
            $data['WKA'] = ($wka->Title ? $wka->Title.' ' : '').$wka->Nama.($wka->Gelar ? ' '.$wka->Gelar : '');
            $data['NIPWKA'] = $wka->NIP;
        }

        $identitas = get_id(1, 'identitas');
        $data['identitas'] = $identitas;
        $data['kota'] = get_wilayah($identitas->KotaPT ?? 0);

        return $data;
    }

    /**
     * Get transcript data (Asli or Sementara)
     */
    public function getTranscriptData($mhswId, $isAsli = true)
    {
        $mhs = DB::table('mahasiswa as a')
            ->join('programstudi as b', 'a.ProdiID', '=', 'b.ID')
            ->join('jenjang as c', 'c.ID', '=', 'b.JenjangID', 'inner')
            ->select('a.*', 'b.Nama as NamaProdi', 'c.Nama as NamaJenjang', 'b.KAProdiID')
            ->where('a.ID', $mhswId)
            ->first();

        if (!$mhs) return null;

        $data = [];
        $data['mhs'] = $mhs;

        // Check if table 'tugasakhir' exists before querying
        $hasTugasAkhir = false;
        try {
            $checkTable = DB::select("SHOW TABLES LIKE 'tugasakhir'");
            $hasTugasAkhir = !empty($checkTable);
        } catch (\Exception $e) {
            $hasTugasAkhir = false;
        }

        $data['ta'] = $hasTugasAkhir ? DB::table('tugasakhir')->where('MhswID', $mhswId)->first() : null;
        $data['query'] = $isAsli ? view_transkrip($mhswId) : view_transkrip_sementara($mhswId);
        $data['identitas'] = $identitas = get_id(1, 'identitas');
        $data['kota'] = get_wilayah($identitas->KotaPT ?? 0);

        return $data;
    }
}
