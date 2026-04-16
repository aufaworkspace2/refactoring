<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class MasterFormatNimPmbService
{
    private $table = 'pmb_tbl_master_format_nim';
    private $pk = 'kode';

    public function get_data($limit, $offset, $keyword = '')
    {
        $query = DB::table($this->table);

        if ($keyword) {
            $query->whereRaw("(field like '%" . $keyword . "%' OR kode like '%" . $keyword . "%')");
        }

        $query->orderBy('field', 'ASC');

        if ($limit !== null) {
            $query->limit($limit);
        }
        if ($offset !== null) {
            $query->offset($offset);
        }

        return $query->get()->map(fn($item) => (array) $item)->toArray();
    }

    public function count_all($keyword = '')
    {
        $query = DB::table($this->table);

        if ($keyword) {
            $query->whereRaw("(field like '%" . $keyword . "%' OR kode like '%" . $keyword . "%')");
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
        return DB::table($this->table)->insert($data);
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
