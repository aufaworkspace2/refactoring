<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class InformasiService
{
    protected $table = 'm_informasi';
    protected $pk = 'ID';

    /**
     * Get data with pagination, search and filter by ProdiID
     */
    public function get_data($limit, $offset, $ProdiID = '', $keyword = '')
    {
        $query = DB::table($this->table);

        if ($ProdiID) {
            $query->whereRaw("FIND_IN_SET(?, {$this->table}.ProdiID)", [$ProdiID]);
        }

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('Kode', 'LIKE', "%{$keyword}%")
                  ->orWhere('Indonesia', 'LIKE', "%{$keyword}%")
                  ->orWhere('Inggris', 'LIKE', "%{$keyword}%");
            });
        }

        return $query->orderBy('Kode', 'ASC')
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
        $query = DB::table($this->table);

        if ($ProdiID) {
            $query->whereRaw("FIND_IN_SET(?, ProdiID)", [$ProdiID]);
        }

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('Kode', 'LIKE', "%{$keyword}%")
                  ->orWhere('Indonesia', 'LIKE', "%{$keyword}%")
                  ->orWhere('Inggris', 'LIKE', "%{$keyword}%");
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
}
