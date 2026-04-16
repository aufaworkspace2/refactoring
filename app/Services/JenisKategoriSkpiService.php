<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class JenisKategoriSkpiService
{
    protected $table = 'jenis_kategori_kegiatan';
    protected $pk = 'ID';

    /**
     * Get data with pagination and search
     */
    public function get_data($limit, $offset, $keyword = '')
    {
        $query = DB::table($this->table);

        if ($keyword) {
            $query->where('Nama', 'LIKE', "%{$keyword}%");
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
    public function count_all($keyword = '')
    {
        $query = DB::table($this->table);

        if ($keyword) {
            $query->where('Nama', 'LIKE', "%{$keyword}%");
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
     * Check if name already exists (for validation)
     */
    public function checkDuplicate($nama, $excludeId = null)
    {
        $query = DB::table($this->table)
            ->where('Nama', $nama);

        if ($excludeId) {
            $query->where($this->pk, '!=', $excludeId);
        }

        $result = $query->first();
        return $result ? (array) $result : null;
    }

    /**
     * Check if record has relation with kategori_kegiatan
     */
    public function hasRelation($id)
    {
        $relation = DB::table('kategori_kegiatan')
            ->where('JenisKategoriID', $id)
            ->first();

        return !empty($relation);
    }
}
