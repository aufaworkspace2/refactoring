<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class JalurPendaftaranPmbService
{
    private $table = 'pmb_edu_jalur_pendaftaran';
    private $pk = 'id';

    /**
     * Get data with pagination and filters
     */
    public function get_data($limit, $offset, $keyword = '', $active = '')
    {
        $query = DB::table($this->table)
            ->select($this->table . '.*', DB::raw('COUNT(pmb_pilihan_pendaftaran.id) AS jumlah_pilihan_pendaftaran'));

        if ($keyword) {
            $query->whereRaw("(" . $this->table . ".nama like '%" . $keyword . "%' OR " . $this->table . ".kode like '%" . $keyword . "%')");
        }

        if ($active == '1') {
            $query->where($this->table . '.aktif', $active);
        }

        $query->leftJoin('pmb_pilihan_pendaftaran', $this->table . '.id', '=', 'pmb_pilihan_pendaftaran.jalur')
            ->orderBy($this->table . '.nama', 'ASC')
            ->groupBy($this->table . '.id');

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
    public function count_all($keyword = '', $active = '')
    {
        $query = DB::table($this->table);

        if ($keyword) {
            $query->whereRaw("(nama like '%" . $keyword . "%' OR kode like '%" . $keyword . "%')");
        }

        if ($active == '1') {
            $query->where($this->table . '.aktif', $active);
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
     * Check if kode already exists
     */
    public function checkDuplicateKode($kode, $id = '')
    {
        $query = DB::table('pmb_edu_jalur_pendaftaran')
            ->select('id', 'nama')
            ->where('kode', $kode);

        if ($id) {
            $query->where('id', '!=', $id);
        }

        return $query->first();
    }

    /**
     * Update aktif status
     */
    public function updateAktif($val, $buka)
    {
        $input['aktif'] = $buka;
        $input['user_update'] = session('UserID');

        return DB::table('pmb_edu_jalur_pendaftaran')
            ->where('ID', $val)
            ->update($input);
    }
}
