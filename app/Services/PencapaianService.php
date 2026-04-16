<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PencapaianService
{
    protected $table = 'm_pencapaian';
    protected $pk = 'ID';

    /**
     * Get data with pagination, search and filter by ProdiID
     */
    public function get_data($limit, $offset, $ProdiID = '', $keyword = '')
    {
        $query = DB::table($this->table)
            ->select('m_pencapaian.*', 'tbl_kategori_pencapaian.Nama as namaKategori', 'tbl_kategori_pencapaian.NamaInggris as namaKategoriInggris')
            ->join('tbl_kategori_pencapaian', 'tbl_kategori_pencapaian.ID', '=', 'm_pencapaian.KategoriPencapaianID');

        if ($ProdiID) {
            $query->whereRaw("FIND_IN_SET(?, m_pencapaian.ProdiID)", [$ProdiID]);
        }

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('m_pencapaian.Kode', 'LIKE', "%{$keyword}%")
                  ->orWhere('m_pencapaian.Indonesia', 'LIKE', "%{$keyword}%")
                  ->orWhere('m_pencapaian.Inggris', 'LIKE', "%{$keyword}%");
            });
        }

        return $query->orderBy('m_pencapaian.Kode', 'ASC')
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
    }

    /**
     * Count all records with optional filters
     */
    public function count_all($ProdiID = '', $keyword = '')
    {
        $query = DB::table($this->table)
            ->join('tbl_kategori_pencapaian', 'tbl_kategori_pencapaian.ID', '=', 'm_pencapaian.KategoriPencapaianID');

        if ($ProdiID) {
            $query->whereRaw("FIND_IN_SET(?, m_pencapaian.ProdiID)", [$ProdiID]);
        }

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('m_pencapaian.Kode', 'LIKE', "%{$keyword}%")
                  ->orWhere('m_pencapaian.Indonesia', 'LIKE', "%{$keyword}%")
                  ->orWhere('m_pencapaian.Inggris', 'LIKE', "%{$keyword}%");
            });
        }

        return $query->count();
    }

    /**
     * Get single record by ID
     */
    public function get_id($id)
    {
        $result = DB::table($this->table)
            ->where($this->pk, $id)
            ->first();

        return $result ? (array) $result : null;
    }

    /**
     * Add new record
     */
    public function add($data)
    {
        return DB::table($this->table)->insert($data);
    }

    /**
     * Update record
     */
    public function edit($id, $data)
    {
        return DB::table($this->table)
            ->where($this->pk, $id)
            ->update($data);
    }

    /**
     * Delete record
     */
    public function delete($id)
    {
        return DB::table($this->table)
            ->where($this->pk, $id)
            ->delete();
    }

    /**
     * Get all kategori pencapaian for dropdown
     */
    public function getAllKategoriPencapaian()
    {
        return DB::table('tbl_kategori_pencapaian')
            ->orderBy('Urut', 'ASC')
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
            ->join('jenjang', 'jenjang.ID', '=', 'programstudi.JenjangID')
            ->orderBy('programstudi.Nama', 'ASC')
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
    }

    /**
     * Get mahasiswa who have this pencapaian
     */
    public function getMahasiswaByCapaiID($capaiID)
    {
        return DB::table('t_pencapaian')
            ->select('t_pencapaian.*', 'mahasiswa.NPM', 'mahasiswa.Nama as namaMahasiswa')
            ->join('mahasiswa', 'mahasiswa.ID', '=', 't_pencapaian.MhswID')
            ->where('t_pencapaian.CapaiID', $capaiID)
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
    }
}
