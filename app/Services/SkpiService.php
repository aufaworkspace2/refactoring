<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SkpiService
{
    /**
     * Get data mahasiswa untuk SKPI dengan filter dan pagination
     */
    public function get_data($limit, $offset, $filters = [])
    {
        $ProgramID = $filters['ProgramID'] ?? '';
        $ProdiID = $filters['ProdiID'] ?? '';
        $StatusMhswID = $filters['StatusMhswID'] ?? '';
        $TahunMasuk = $filters['TahunMasuk'] ?? '';
        $KelasID = $filters['KelasID'] ?? '';
        $keyword = $filters['keyword'] ?? '';

        $query = DB::table('mahasiswa')
            ->select(
                'skpi.MhswID',
                'skpi.IjinProdi',
                'skpi.Persyaratan',
                'skpi.Bahasa',
                'skpi.PendidikanLanjut',
                'skpi.StatusProfesi',
                'skpi.SistemPenilaian',
                'skpi.TanggalKelulusan',
                'skpi.NoIjazah as NoIjazah2',
                'skpi.Gelar',
                'skpi.LamaStudi',
                'skpi.IPK',
                'skpi.SKS',
                'mahasiswa.*'
            )
            ->leftJoin('statusmahasiswa', 'statusmahasiswa.ID', '=', 'mahasiswa.StatusMhswID')
            ->leftJoin('skpi', 'mahasiswa.ID', '=', 'skpi.MhswID')
            ->whereNotNull('mahasiswa.NPM');

        // Apply filters
        if ($ProgramID) {
            $query->where('mahasiswa.ProgramID', $ProgramID);
        }

        if ($StatusMhswID) {
            $query->where('mahasiswa.StatusMhswID', $StatusMhswID);
        } else {
            $query->whereIn('mahasiswa.StatusMhswID', ['1', '2', '3']);
        }

        if ($KelasID) {
            $query->where('mahasiswa.KelasID', $KelasID);
        }

        if ($ProdiID) {
            $prodiIds = explode(',', $ProdiID);
            $query->whereIn('mahasiswa.ProdiID', $prodiIds);
        }

        if ($TahunMasuk) {
            $query->where('mahasiswa.TahunMasuk', $TahunMasuk);
        }

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('mahasiswa.Nama', 'LIKE', "%{$keyword}%")
                  ->orWhere('mahasiswa.NPM', 'LIKE', "%{$keyword}%");
            });
        }

        $query->orderBy('mahasiswa.NPM', 'ASC');

        return $query->offset($offset)
            ->limit($limit)
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
    }

    /**
     * Count all mahasiswa data with filters
     */
    public function count_all($filters = [])
    {
        $ProgramID = $filters['ProgramID'] ?? '';
        $ProdiID = $filters['ProdiID'] ?? '';
        $StatusMhswID = $filters['StatusMhswID'] ?? '';
        $TahunMasuk = $filters['TahunMasuk'] ?? '';
        $KelasID = $filters['KelasID'] ?? '';
        $keyword = $filters['keyword'] ?? '';

        $query = DB::table('mahasiswa')
            ->select('skpi.MhswID')
            ->leftJoin('statusmahasiswa', 'statusmahasiswa.ID', '=', 'mahasiswa.StatusMhswID')
            ->leftJoin('skpi', 'mahasiswa.ID', '=', 'skpi.MhswID')
            ->whereNotNull('mahasiswa.NPM');

        if ($ProgramID) {
            $query->where('mahasiswa.ProgramID', $ProgramID);
        }

        if ($StatusMhswID) {
            $query->where('mahasiswa.StatusMhswID', $StatusMhswID);
        } else {
            $query->whereIn('mahasiswa.StatusMhswID', ['1', '2', '3']);
        }

        if ($KelasID) {
            $query->where('mahasiswa.KelasID', $KelasID);
        }

        if ($ProdiID) {
            $prodiIds = explode(',', $ProdiID);
            $query->whereIn('mahasiswa.ProdiID', $prodiIds);
        }

        if ($TahunMasuk) {
            $query->where('mahasiswa.TahunMasuk', $TahunMasuk);
        }

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('mahasiswa.Nama', 'LIKE', "%{$keyword}%")
                  ->orWhere('mahasiswa.NPM', 'LIKE', "%{$keyword}%");
            });
        }

        return $query->count();
    }

    /**
     * Get single mahasiswa data with SKPI info
     */
    public function get_id($id)
    {
        $query = DB::table('mahasiswa')
            ->select(
                'skpi.MhswID',
                'skpi.IjinProdi',
                'skpi.Persyaratan',
                'skpi.Bahasa',
                'skpi.PendidikanLanjut',
                'skpi.StatusProfesi',
                'skpi.SistemPenilaian',
                'skpi.TanggalKelulusan',
                'skpi.NoIjazah as NoIjazah2',
                'skpi.Gelar',
                'skpi.LamaStudi',
                'skpi.IPK',
                'skpi.SKS',
                'skpi.NomorSKPI',
                'mahasiswa.*'
            )
            ->where('mahasiswa.ID', $id)
            ->leftJoin('skpi', 'mahasiswa.ID', '=', 'skpi.MhswID')
            ->first();

        return $query ? (array) $query : null;
    }

    /**
     * Add new SKPI record
     */
    public function add($data)
    {
        return DB::table('skpi')->insert($data);
    }

    /**
     * Update SKPI record
     */
    public function edit($where, $data)
    {
        return DB::table('skpi')
            ->where($where)
            ->update($data);
    }

    /**
     * Delete SKPI record
     */
    public function delete($id, $table = 'skpi')
    {
        return DB::table($table)
            ->where('ID', $id)
            ->delete();
    }

    /**
     * Delete capaian and informasi related to SKPI
     */
    public function deleteCapaiInfo($skpiId)
    {
        DB::table('t_informasi')->where('SKPIID', $skpiId)->delete();
        DB::table('t_pencapaian')->where('SKPIID', $skpiId)->delete();

        return DB::getPdo()->affectedRows();
    }

    /**
     * Get tahun masuk list
     */
    public function get_tahun()
    {
        return DB::table('mahasiswa')
            ->select('TahunMasuk')
            ->distinct()
            ->orderBy('TahunMasuk', 'DESC')
            ->get();
    }

    /**
     * Get all program studi
     */
    public function get_all_program_studi()
    {
        return DB::table('programstudi')
            ->select('ID', 'ProdiID', 'Nama', 'JenjangID')
            ->orderBy('Nama', 'ASC')
            ->get();
    }

    /**
     * Get all program
     */
    public function get_all_program()
    {
        return DB::table('program')
            ->select('ID', 'ProgramID', 'Nama')
            ->orderBy('Nama', 'ASC')
            ->get();
    }

    /**
     * Get all status mahasiswa
     */
    public function get_all_status_mahasiswa()
    {
        return DB::table('statusmahasiswa')
            ->select('ID', 'Nama')
            ->whereIn('Nama', ['aktif', 'lulus', 'cuti'])
            ->orderBy('Nama', 'ASC')
            ->get();
    }

    /**
     * Get mahasiswa by ProdiID for dropdown
     */
    public function get_mahasiswa_by_prodi($prodiId)
    {
        return DB::table('mahasiswa')
            ->select('ID', 'NPM', 'Nama')
            ->where('ProdiID', $prodiId)
            ->whereNotNull('NPM')
            ->orderBy('Nama', 'ASC')
            ->get();
    }

    /**
     * Get wisudawan data by MhswID
     */
    public function get_wisudawan($mhswId)
    {
        return DB::table('wisudawan')
            ->where('MhswID', $mhswId)
            ->first();
    }

    /**
     * Get pencapaian for mahasiswa
     */
    public function get_pencapaian($mhswId, $prodiId)
    {
        $query = DB::table('m_pencapaian')
            ->select(
                'm_pencapaian.*',
                't_pencapaian.MhswID',
                't_pencapaian.CapaiID',
                't_pencapaian.IsiIndonesia',
                't_pencapaian.IsiInggris'
            )
            ->leftJoin('t_pencapaian', function($join) use ($mhswId) {
                $join->on('m_pencapaian.ID', '=', 't_pencapaian.CapaiID')
                     ->where('t_pencapaian.MhswID', '=', $mhswId);
            })
            ->leftJoin('tbl_kategori_pencapaian', 'tbl_kategori_pencapaian.ID', '=', 'm_pencapaian.KategoriPencapaianID')
            ->whereRaw("FIND_IN_SET(?, m_pencapaian.ProdiID)", [$prodiId])
            ->orderBy('tbl_kategori_pencapaian.Urut', 'ASC')
            ->orderByRaw('CAST(SUBSTRING_INDEX(m_pencapaian.Kode,".",1) AS UNSIGNED)')
            ->orderByRaw('CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(m_pencapaian.Kode,".",2),".",-1) AS UNSIGNED)')
            ->get();

        return $query->map(fn($item) => (array) $item)->toArray();
    }

    /**
     * Get informasi for mahasiswa
     */
    public function get_informasi($mhswId, $prodiId)
    {
        $query = DB::table('m_informasi')
            ->select('m_informasi.*', DB::raw('GROUP_CONCAT(t_informasi_baru.ID) as GID'))
            ->leftJoin('t_informasi_baru', function($join) use ($mhswId) {
                $join->on('m_informasi.ID', '=', 't_informasi_baru.InformasiID')
                     ->where('t_informasi_baru.MhswID', '=', $mhswId)
                     ->where('t_informasi_baru.approve', '1');
            })
            ->whereRaw("FIND_IN_SET(?, m_informasi.ProdiID)", [$prodiId])
            ->groupBy('m_informasi.ID')
            ->get();

        return $query->map(fn($item) => (array) $item)->toArray();
    }

    /**
     * Get kategori pencapaian
     */
    public function get_kategori_pencapaian()
    {
        return DB::table('tbl_kategori_pencapaian')
            ->orderBy('Urut', 'ASC')
            ->get();
    }

    /**
     * Get dosen by ID
     */
    public function get_dosen($id)
    {
        return DB::table('dosen')
            ->where('ID', $id)
            ->first();
    }

    /**
     * Get karyawan by ID
     */
    public function get_karyawan($id)
    {
        return DB::table('karyawan')
            ->where('ID', $id)
            ->first();
    }

    /**
     * Get data SKPI per prodi
     */
    public function get_data_skpi($prodiId)
    {
        return DB::table('data_skpi')
            ->where('ProdiID', $prodiId)
            ->first();
    }

    /**
     * Get keterangan status mahasiswa
     */
    public function get_keterangan_status($mhswId)
    {
        return DB::table('keteranganstatusmahasiswa')
            ->where('MhswID', $mhswId)
            ->where('StatusMahasiswaID', '1')
            ->orderBy('ID', 'DESC')
            ->first();
    }

    /**
     * Get catatan resmi SKPI
     */
    public function get_catatan_resmi()
    {
        return DB::table('catatan_resmi_skpi')
            ->get();
    }

    /**
     * Check if SKPI exists for MhswID
     */
    public function checkSkpiExists($mhswId)
    {
        return DB::table('skpi')
            ->where('MhswID', $mhswId)
            ->first();
    }

    /**
     * Save capaian (t_pencapaian)
     */
    public function saveCapaian($data)
    {
        return DB::table('t_pencapaian')
            ->updateOrInsert(
                ['MhswID' => $data['MhswID'], 'CapaiID' => $data['CapaiID']],
                $data
            );
    }

    /**
     * Save informasi (t_informasi_baru)
     */
    public function saveInformasi($data)
    {
        return DB::table('t_informasi_baru')
            ->updateOrInsert(
                ['MhswID' => $data['MhswID'], 'InformasiID' => $data['InformasiID']],
                $data
            );
    }
}
