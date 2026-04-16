<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SettingProdiTambahanJurusanService
{
    private $table = 'setting_prodi_tambahan_jurusan';
    private $pk = 'id';

    public function get_data($limit, $offset, $jalurID = '', $prodiID = '', $keyword = '')
    {
        $query = DB::table($this->table . ' AS s')
            ->select('s.*');

        if ($jalurID) {
            $query->whereRaw("FIND_IN_SET(?, s.JalurID) != 0", [$jalurID]);
        }

        if ($prodiID) {
            $query->whereRaw("FIND_IN_SET(?, s.ProdiID) != 0", [$prodiID]);
        }

        $query->orderBy('s.id', 'DESC');

        if ($limit !== null) {
            $query->limit($limit);
        }
        if ($offset !== null) {
            $query->offset($offset);
        }

        return $query->get()->map(fn($item) => (array) $item)->toArray();
    }

    public function count_all($jalurID = '', $prodiID = '', $keyword = '')
    {
        $query = DB::table($this->table . ' AS s');

        if ($jalurID) {
            $query->whereRaw("FIND_IN_SET(?, s.JalurID) != 0", [$jalurID]);
        }

        if ($prodiID) {
            $query->whereRaw("FIND_IN_SET(?, s.ProdiID) != 0", [$prodiID]);
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
}
