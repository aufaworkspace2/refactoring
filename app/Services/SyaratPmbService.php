<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SyaratPmbService
{
    private $table = 'pmb_edu_syarat';
    private $pk = 'id';

    public function get_data($limit, $offset, $keyword = '', $tipe = '')
    {
        $query = DB::table($this->table);

        if ($keyword) {
            $query->whereRaw("(nama like '%" . $keyword . "%' OR kode like '%" . $keyword . "%')");
        }

        if ($tipe) {
            $query->where('tipe', $tipe);
        }

        $query->orderBy('Nama', 'ASC');

        if ($limit !== null) {
            $query->limit($limit);
        }
        if ($offset !== null) {
            $query->offset($offset);
        }

        return $query->get()->map(fn($item) => (array) $item)->toArray();
    }

    public function count_all($keyword = '', $tipe = '')
    {
        $query = DB::table($this->table);

        if ($keyword) {
            $query->whereRaw("(nama like '%" . $keyword . "%' OR kode like '%" . $keyword . "%')");
        }

        if ($tipe) {
            $query->where('tipe', $tipe);
        }

        return $query->count();
    }

    public function get_id($id)
    {
        $result = DB::table($this->table)->where($this->pk, $id)->first();
        return $result ? (array) $result : null;
    }

    public function add($data)
    {
        return DB::table($this->table)->insertGetId($data);
    }

    public function edit($id, $data)
    {
        return DB::table($this->table)->where($this->pk, $id)->update($data);
    }

    public function delete($id)
    {
        return DB::table($this->table)->where($this->pk, $id)->delete();
    }

    public function checkDuplicateKode($kode, $id = '')
    {
        $query = DB::table('pmb_edu_syarat')
            ->select('id', 'nama')
            ->where('kode', $kode);

        if ($id) {
            $query->where('id', '!=', $id);
        }

        return $query->first();
    }

    public function checkDuplicateNama($nama, $id = '')
    {
        $query = DB::table('pmb_edu_syarat')
            ->select('id', 'nama')
            ->where('nama', $nama);

        if ($id) {
            $query->where('id', '!=', $id);
        }

        return $query->first();
    }
}
