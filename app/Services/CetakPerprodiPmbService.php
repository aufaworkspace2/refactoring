<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CetakPerprodiPmbService
{
    /**
     * Get data with pagination
     */
    public function get_data($limit, $offset, $gelombang = '', $keyword = '')
    {
        $query = DB::table('pmb_tbl_gelombang')
            ->select('pmb_tbl_gelombang.*', 
                DB::raw('COUNT(mahasiswa.ID) as jumlah')
            )
            ->leftJoin('pmb_tbl_gelombang_detail', 'pmb_tbl_gelombang_detail.gelombang_id', '=', 'pmb_tbl_gelombang.id')
            ->leftJoin('mahasiswa', 'mahasiswa.gelombang_detail_pmb', '=', 'pmb_tbl_gelombang_detail.id');

        if ($gelombang) {
            $query->where('pmb_tbl_gelombang.id', $gelombang);
        }

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('pmb_tbl_gelombang.nama', 'LIKE', "%{$keyword}%")
                  ->orWhere('pmb_tbl_gelombang.kode', 'LIKE', "%{$keyword}%");
            });
        }

        $query->groupBy('pmb_tbl_gelombang.id');

        if ($limit !== null) {
            $query->limit($limit);
        }
        if ($offset !== null) {
            $query->offset($offset);
        }

        return $query->get()->map(fn($item) => (array) $item)->toArray();
    }

    /**
     * Count total data
     */
    public function count_all($keyword = '')
    {
        $query = DB::table('pmb_tbl_gelombang');

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('nama', 'LIKE', "%{$keyword}%")
                  ->orWhere('kode', 'LIKE', "%{$keyword}%");
            });
        }

        return $query->count();
    }

    /**
     * Get data for printing
     */
    public function get_data_cetak($akhir, $awal, $prodi, $gelombang)
    {
        $query = DB::table('mahasiswa')
            ->select('mahasiswa.id', 'mahasiswa.Nama as nama_lengkap', 'mahasiswa.noujian_pmb as noujian', 'mahasiswa.Foto as foto')
            ->join('pmb_tbl_gelombang_detail', 'pmb_tbl_gelombang_detail.id', '=', 'mahasiswa.gelombang_detail_pmb');

        if ($gelombang) {
            $query->where('pmb_tbl_gelombang_detail.gelombang_id', $gelombang);
        }

        if ($prodi) {
            $query->where('mahasiswa.pilihan1', $prodi);
        }

        if ($awal && $akhir) {
            $query->whereBetween('mahasiswa.id', [$awal, $akhir]);
        }

        $query->orderBy('mahasiswa.id', 'ASC');

        return $query->get()->map(fn($item) => (array) $item)->toArray();
    }

    /**
     * Get all gelombang for dropdown
     */
    public function getAllGelombang()
    {
        return DB::table('pmb_tbl_gelombang')
            ->orderBy('nama', 'ASC')
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
    }
}
