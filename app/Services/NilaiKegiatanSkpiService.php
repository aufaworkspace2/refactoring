<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class NilaiKegiatanSkpiService
{
    protected $table = 'nilai_kegiatan';
    protected $pk = 'ID';

    /**
     * Get data with pagination and filter by JenisKategoriID
     */
    public function get_data($limit, $offset, $JenisKategoriID = '')
    {
        $query = DB::table($this->table)
            ->select('nilai_kegiatan.*',
                     'jenis_kategori_kegiatan.Nama as namaJenis',
                     'kategori_kegiatan.Nama as namaKategori')
            ->join('kategori_kegiatan', 'kategori_kegiatan.ID', '=', 'nilai_kegiatan.KategoriKegiatanID')
            ->join('jenis_kategori_kegiatan', 'jenis_kategori_kegiatan.ID', '=', 'kategori_kegiatan.JenisKategoriID');

        if ($JenisKategoriID) {
            $query->where('jenis_kategori_kegiatan.ID', $JenisKategoriID);
        }

        return $query->orderBy('nilai_kegiatan.ID', 'DESC')
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
    }

    /**
     * Count all records with optional filter
     */
    public function count_all($JenisKategoriID = '')
    {
        $query = DB::table($this->table)
            ->join('kategori_kegiatan', 'kategori_kegiatan.ID', '=', 'nilai_kegiatan.KategoriKegiatanID')
            ->join('jenis_kategori_kegiatan', 'jenis_kategori_kegiatan.ID', '=', 'kategori_kegiatan.JenisKategoriID');

        if ($JenisKategoriID) {
            $query->where('jenis_kategori_kegiatan.ID', $JenisKategoriID);
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
     * Get all data for export (no pagination)
     */
    public function get_all_data($JenisKategoriID = '')
    {
        $query = DB::table($this->table)
            ->select('nilai_kegiatan.*',
                     'jenis_kategori_kegiatan.Nama as namaJenis',
                     'kategori_kegiatan.Nama as namaKategori')
            ->join('kategori_kegiatan', 'kategori_kegiatan.ID', '=', 'nilai_kegiatan.KategoriKegiatanID')
            ->join('jenis_kategori_kegiatan', 'jenis_kategori_kegiatan.ID', '=', 'kategori_kegiatan.JenisKategoriID');

        if ($JenisKategoriID) {
            $query->where('jenis_kategori_kegiatan.ID', $JenisKategoriID);
        }

        return $query->orderBy('nilai_kegiatan.ID', 'DESC')
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
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
     * Get all kegiatan for dropdown
     */
    public function getAllKegiatan()
    {
        return DB::table('master_kegiatan')
            ->orderBy('Nama', 'ASC')
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
    }

    /**
     * Get all kategori kegiatan for dropdown
     */
    public function getAllKategoriKegiatan()
    {
        return DB::table('kategori_kegiatan')
            ->select('kategori_kegiatan.*', 'jenis_kategori_kegiatan.Nama as namaJenis')
            ->join('jenis_kategori_kegiatan', 'jenis_kategori_kegiatan.ID', '=', 'kategori_kegiatan.JenisKategoriID')
            ->orderBy('kategori_kegiatan.Nama', 'ASC')
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
    }

    /**
     * Get all jenis kategori kegiatan for dropdown
     */
    public function getAllJenisKategoriKegiatan()
    {
        return DB::table('jenis_kategori_kegiatan')
            ->orderBy('Nama', 'ASC')
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
    }
}
