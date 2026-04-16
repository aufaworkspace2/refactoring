<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ApproveInformasiService
{
    /**
     * Get data for approve informasi with filters
     */
    public function get_data_approve_informasi($limit, $offset, $filters = [])
    {
        $ProgramID = $filters['ProgramID'] ?? '';
        $ProdiID = $filters['ProdiID'] ?? '';
        $StatusMhswID = $filters['StatusMhswID'] ?? '';
        $TahunMasuk = $filters['TahunMasuk'] ?? '';
        $KelasID = $filters['KelasID'] ?? '';
        $keyword = $filters['keyword'] ?? '';
        $orderby = $filters['orderby'] ?? 'mahasiswa.Nama';
        $descasc = $filters['descasc'] ?? 'DESC';

        $query = DB::table('mahasiswa')
            ->select('mahasiswa.*', 'skpi.MhswID', 'skpi.NoIjazah as NoIjazah2')
            ->leftJoin('skpi', 'mahasiswa.ID', '=', 'skpi.MhswID')
            ->leftJoin('statusmahasiswa', 'statusmahasiswa.ID', '=', 'mahasiswa.StatusMhswID')
            ->join('t_informasi_baru', 't_informasi_baru.MhswID', '=', 'mahasiswa.ID');

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
            $query->where('mahasiswa.ProdiID', $ProdiID);
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

        return $query->orderBy($orderby, $descasc)
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
    }

    /**
     * Count all for approve informasi
     */
    public function count_all_approve_informasi($filters = [])
    {
        $ProgramID = $filters['ProgramID'] ?? '';
        $ProdiID = $filters['ProdiID'] ?? '';
        $StatusMhswID = $filters['StatusMhswID'] ?? '';
        $TahunMasuk = $filters['TahunMasuk'] ?? '';
        $KelasID = $filters['KelasID'] ?? '';
        $keyword = $filters['keyword'] ?? '';

        $query = DB::table('mahasiswa')
            ->leftJoin('skpi', 'mahasiswa.ID', '=', 'skpi.MhswID')
            ->leftJoin('statusmahasiswa', 'statusmahasiswa.ID', '=', 'mahasiswa.StatusMhswID')
            ->join('t_informasi_baru', 't_informasi_baru.MhswID', '=', 'mahasiswa.ID');

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
            $query->where('mahasiswa.ProdiID', $ProdiID);
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
     * Get all program for dropdown
     */
    public function getAllProgram()
    {
        return DB::table('program')
            ->orderBy('ProgramID', 'ASC')
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
    }

    /**
     * Get all program studi for dropdown
     */
    public function getAllProgramStudi()
    {
        return DB::table('programstudi')
            ->select('programstudi.*', 'jenjang.Nama as jenjangNama')
            ->leftJoin('jenjang', 'jenjang.ID', '=', 'programstudi.JenjangID')
            ->orderBy('programstudi.ProdiID', 'ASC')
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
    }

    /**
     * Get all status mahasiswa for dropdown
     */
    public function getAllStatusMahasiswa()
    {
        return DB::table('statusmahasiswa')
            ->whereIn('Nama', ['aktif', 'lulus', 'cuti'])
            ->orderBy('Nama', 'ASC')
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
    }

    /**
     * Get all tahun masuk for dropdown
     */
    public function getAllTahunMasuk()
    {
        return DB::table('mahasiswa')
            ->select('TahunMasuk')
            ->distinct()
            ->orderBy('TahunMasuk', 'DESC')
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
    }

    /**
     * Get all kelas for dropdown
     */
    public function getAllKelas()
    {
        return DB::table('kelas')
            ->orderBy('Nama', 'ASC')
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
    }

    /**
     * Get informasi tambahan by mahasiswa ID
     */
    public function getInformasiByMhswID($mhswID)
    {
        return DB::table('t_informasi_baru')
            ->select('t_informasi_baru.*', 'm_informasi.Indonesia', 'm_informasi.Inggris', 'm_informasi.Kode')
            ->join('m_informasi', 'm_informasi.ID', '=', 't_informasi_baru.InformasiID')
            ->where('t_informasi_baru.MhswID', $mhswID)
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
    }

    /**
     * Approve informasi
     */
    public function approveInformasi($id)
    {
        return DB::table('t_informasi_baru')
            ->where('ID', $id)
            ->update(['approve' => '1']);
    }

    /**
     * Reject informasi
     */
    public function rejectInformasi($id)
    {
        return DB::table('t_informasi_baru')
            ->where('ID', $id)
            ->delete();
    }
}
