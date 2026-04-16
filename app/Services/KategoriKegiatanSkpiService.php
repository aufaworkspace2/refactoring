<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class KategoriKegiatanSkpiService
{
    protected $table = 'kategori_kegiatan';
    protected $pk = 'ID';

    /**
     * Get data with pagination and search
     */
    public function get_data($limit, $offset, $JenisKategoriID = '', $keyword = '')
    {
        $query = DB::table($this->table);

        if ($keyword) {
            $query->where('Nama', 'LIKE', "%{$keyword}%");
        }

        if ($JenisKategoriID) {
            $query->where('JenisKategoriID', $JenisKategoriID);
        }

        return $query->orderBy('Nama', 'ASC')
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
    }

    /**
     * Count all records with optional search
     */
    public function count_all($JenisKategoriID, $keyword = '')
    {
        $query = DB::table($this->table);

        if ($keyword) {
            $query->where('Nama', 'LIKE', "%{$keyword}%");
        }

        if ($JenisKategoriID) {
            $query->where('JenisKategoriID', $JenisKategoriID);
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
     * Check if record has relation with nilai_kegiatan
     */
    public function hasRelation($id)
    {
        $relation = DB::table('nilai_kegiatan')
            ->where('KategoriKegiatanID', $id)
            ->first();

        return !empty($relation);
    }

    /**
     * Get all jenis kategori kegiatan for dropdown
     */
    public function getJenisKategori()
    {
        return DB::table('jenis_kategori_kegiatan')
            ->orderBy('Nama', 'ASC')
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
    }
}
