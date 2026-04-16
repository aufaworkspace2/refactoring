<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class OpsiMahasiswaService
{
    public function getData($tahunID, $programID, $prodiID, $statusMhsw, $tahunMasuk, $SemesterMasuk, $statusBayar, $statusInput, $keyword, $limit, $offset)
    {
        $query = $this->buildBaseQuery($tahunID, $programID, $prodiID, $statusMhsw, $tahunMasuk, $SemesterMasuk, $statusBayar, $statusInput, $keyword);

        // Apply limit and offset
        if (!empty($limit)) {
            $query->limit($limit)->offset($offset);
        }

        return $query->get();
    }

    public function countData($tahunID, $programID, $prodiID, $statusMhsw, $tahunMasuk, $SemesterMasuk, $statusBayar, $statusInput, $keyword)
    {
        $query = $this->buildBaseQuery($tahunID, $programID, $prodiID, $statusMhsw, $tahunMasuk, $SemesterMasuk, $statusBayar, $statusInput, $keyword);
        
        return $query->count();
    }

    private function buildBaseQuery($tahunID, $programID, $prodiID, $statusMhsw, $tahunMasuk, $SemesterMasuk, $statusBayar, $statusInput, $keyword)
    {
        $query = DB::table('mahasiswa')
            ->select(
                'mahasiswa.ID',
                'mahasiswa.NPM',
                'mahasiswa.Nama',
                'mahasiswa.TahunMasuk',
                'mahasiswa.Kelamin',
                'mahasiswa.Foto',
                'program.Nama as Program',
                'programstudi.Nama as Prodi',
                'statusmahasiswa.Nama as StatusMahasiswa',
                'opsi_mahasiswa.KRS',
                'opsi_mahasiswa.UTS',
                'opsi_mahasiswa.UAS',
                'opsi_mahasiswa.KHS',
                'opsi_mahasiswa.TRANSKRIP',
                DB::raw('SUM(tagihan_mahasiswa.Jumlah) AS TotalTagihan'),
                DB::raw('SUM(cicilan_tagihan_mahasiswa.Jumlah) AS TotalCicilan'),
                DB::raw('IF((SUM(cicilan_tagihan_mahasiswa.Jumlah) >= SUM(tagihan_mahasiswa.Jumlah)),1,0) AS StatusBayar')
            )
            ->leftJoin('program', 'program.ID', '=', 'mahasiswa.ProgramID')
            ->leftJoin('programstudi', 'programstudi.ID', '=', 'mahasiswa.ProdiID')
            ->leftJoin('statusmahasiswa', 'statusmahasiswa.ID', '=', 'mahasiswa.StatusMhswID')
            ->leftJoin('opsi_mahasiswa', function($join) use ($tahunID) {
                $join->on('mahasiswa.ID', '=', 'opsi_mahasiswa.MhswID')
                     ->where('opsi_mahasiswa.TahunID', '=', $tahunID);
            })
            ->leftJoin('tagihan_mahasiswa', function($join) use ($tahunID) {
                $join->on('tagihan_mahasiswa.MhswID', '=', 'mahasiswa.ID')
                     ->where('tagihan_mahasiswa.Periode', '=', $tahunID);
            })
            ->leftJoin(DB::raw('(SELECT SUM(Jumlah) as Jumlah, TagihanMahasiswaID FROM cicilan_tagihan_mahasiswa GROUP BY TagihanMahasiswaID) as cicilan_tagihan_mahasiswa'),
                'cicilan_tagihan_mahasiswa.TagihanMahasiswaID', '=', 'tagihan_mahasiswa.ID')
            ->where('mahasiswa.jenis_mhsw', 'mhsw')
            ->groupBy('mahasiswa.ID')
            ->orderBy('mahasiswa.NPM', 'ASC');

        // Apply filters
        if (!empty($programID)) {
            $query->where('mahasiswa.ProgramID', $programID);
        }
        if (!empty($prodiID)) {
            $query->where('mahasiswa.ProdiID', $prodiID);
        }
        if (!empty($statusMhsw)) {
            $query->where('mahasiswa.StatusMhswID', $statusMhsw);
        }
        if (!empty($tahunMasuk)) {
            $query->where('mahasiswa.TahunMasuk', $tahunMasuk);
        }
        if (!empty($SemesterMasuk)) {
            $query->where('mahasiswa.SemesterMasuk', $SemesterMasuk);
        }
        if (!empty($keyword)) {
            $query->where(function($q) use ($keyword) {
                $q->where('mahasiswa.NPM', 'like', "%{$keyword}%")
                  ->orWhere('mahasiswa.Nama', 'like', "%{$keyword}%");
            });
        }

        // Status Bayar filter
        if ($statusBayar !== null && $statusBayar !== '') {
            if ($statusBayar == 1) {
                $query->havingRaw('SUM(cicilan_tagihan_mahasiswa.Jumlah) >= SUM(tagihan_mahasiswa.Jumlah)');
            } elseif ($statusBayar == 0) {
                $query->havingRaw('(IFNULL(SUM(cicilan_tagihan_mahasiswa.Jumlah),0) < IFNULL(SUM(tagihan_mahasiswa.Jumlah),0)) OR SUM(tagihan_mahasiswa.Jumlah) = 0 OR SUM(tagihan_mahasiswa.Jumlah) IS NULL');
            }
        }

        // Status Input filter
        if ($statusInput !== null && $statusInput !== '') {
            if ($statusInput == 1) {
                $query->havingRaw('COUNT(tagihan_mahasiswa.ID) > 0');
            } elseif ($statusInput == 0) {
                $query->havingRaw('COUNT(tagihan_mahasiswa.ID) = 0');
            }
        }

        return $query;
    }

    public function getAngkatan()
    {
        return DB::table('mahasiswa')
            ->select('TahunMasuk')
            ->where('TahunMasuk', '!=', '')
            ->groupBy('TahunMasuk')
            ->orderBy('TahunMasuk', 'DESC')
            ->get();
    }

    public function getDataBy($select, $where, $table, $type)
    {
        $query = DB::table($table)->select($select);

        if (!empty($where)) {
            foreach ($where as $key => $value) {
                $query->where($key, $value);
            }
        }

        if ($type == 1) {
            return $query->first();
        } elseif ($type == 2) {
            return $query->get();
        } elseif ($type == 3) {
            return $query->get()->toArray();
        } elseif ($type == 4) {
            return $query->count();
        }

        return null;
    }

    public function insertData($table, $data)
    {
        return DB::table($table)->insert($data) ? 1 : 0;
    }

    public function updateData($where, $table, $data)
    {
        if (!empty($where)) {
            $query = DB::table($table);
            foreach ($where as $key => $value) {
                $query->where($key, $value);
            }
            return $query->update($data);
        }
        return 0;
    }

    public function deleteData($where, $table)
    {
        if (!empty($where)) {
            $query = DB::table($table);
            foreach ($where as $key => $value) {
                $query->where($key, $value);
            }
            return $query->delete();
        }
        return 0;
    }

    public function checkOpsiNilai()
    {
        $opsi_check = get_setup_app("opsi_mahasiswa_check");
        
        if (!$opsi_check) {
            return 0;
        }

        $arr_opsi_check = json_decode($opsi_check->metadata, true);

        if (array_key_exists('opsi_nilai', $arr_opsi_check)) {
            return ($arr_opsi_check['opsi_nilai'] == 1) ? 1 : 0;
        }

        return 0;
    }
}
