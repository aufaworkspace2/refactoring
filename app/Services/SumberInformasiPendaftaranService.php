<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SumberInformasiPendaftaranService
{
    private $table = 'pmb_tbl_referensi_daftar';
    private $pk = 'id_ref_daftar';

    /**
     * Get data with pagination and filters
     */
    public function get_data($limit, $offset, $keyword = '')
    {
        $query = DB::table($this->table)
            ->select('*');

        if ($keyword) {
            $query->whereRaw("(" . $this->table . ".nama_ref like '%" . $keyword . "%')");
        }

        $query->orderBy($this->table . '.nama_ref', 'ASC')
            ->groupBy($this->table . '.id_ref_daftar');

        if ($limit !== null) {
            $query->limit($limit);
        }
        if ($offset !== null) {
            $query->offset($offset);
        }

        return $query->get()->map(fn($item) => (array) $item)->toArray();
    }

    /**
     * Count all total row data
     */
    public function count_all($keyword = '')
    {
        $query = DB::table($this->table);

        if ($keyword) {
            $query->whereRaw("(nama_ref like '%" . $keyword . "%')");
        }

        return $query->count();
    }

    /**
     * Get data with id
     */
    public function get_id($id)
    {
        $result = DB::table($this->table)
            ->where($this->pk, $id)
            ->first();

        return $result ? (array) $result : null;
    }

    /**
     * Add data
     */
    public function add($data)
    {
        return DB::table($this->table)->insertGetId($data);
    }

    /**
     * Edit data
     */
    public function edit($id, $data)
    {
        return DB::table($this->table)
            ->where($this->pk, $id)
            ->update($data);
    }

    /**
     * Delete data
     */
    public function delete($id)
    {
        return DB::table($this->table)
            ->where($this->pk, $id)
            ->delete();
    }

    /**
     * Check if nama_ref already exists
     */
    public function checkDuplicate($nama, $id = '')
    {
        $query = DB::table('pmb_tbl_referensi_daftar')
            ->select('id_ref_daftar', 'nama_ref')
            ->where('nama_ref', $nama);

        if ($id) {
            $query->where('id_ref_daftar', '!=', $id);
        }

        return $query->first();
    }
}
