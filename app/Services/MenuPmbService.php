<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class MenuPmbService
{
    private $table = 'pmb_tbl_kanal';
    private $pk = 'id';

    public function get_data($limit, $offset, $keyword = '', $status = '')
    {
        $query = DB::table($this->table);
        if ($keyword) { $query->where('namamenu', 'LIKE', "%{$keyword}%"); }
        if ($status) { $query->where('status', $status); }
        if ($limit !== null) { $query->limit($limit); }
        if ($offset !== null) { $query->offset($offset); }
        return $query->orderBy('urutan', 'ASC')->get()->map(fn($item) => (array) $item)->toArray();
    }

    public function count_all($keyword = '')
    {
        $query = DB::table($this->table);
        if ($keyword) { $query->where('namamenu', 'LIKE', "%{$keyword}%"); }
        return $query->count();
    }

    public function get_id($id) { $result = DB::table($this->table)->where($this->pk, $id)->first(); return $result ? (array) $result : null; }
    public function add($data) { return DB::table($this->table)->insertGetId($data); }
    public function edit($id, $data) { return DB::table($this->table)->where($this->pk, $id)->update($data); }
    public function delete($id) { return DB::table($this->table)->where($this->pk, $id)->delete(); }
    public function getKanalUtama() { return DB::table('pmb_tbl_kanal_utama')->get(); }
    public function saveSort($urut_menu) { for ($i = 1; $i < count($urut_menu); $i++) { DB::table($this->table)->where('id', $urut_menu[$i])->update(['urutan' => $i]); } }
    public function getNamamenuById($id) { return DB::table($this->table)->where('id', $id)->value('namamenu'); }
}
