<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PagePmbService
{
    private $table = 'pmb_tbl_page';
    private $pk = 'id';

    public function get_data($limit, $offset, $keyword = '')
    {
        $query = DB::table($this->table);
        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('namamenu', 'LIKE', "%{$keyword}%")->orWhere('isi', 'LIKE', "%{$keyword}%");
            });
        }
        if ($limit !== null) { $query->limit($limit); }
        if ($offset !== null) { $query->offset($offset); }
        return $query->get()->map(fn($item) => (array) $item)->toArray();
    }

    public function count_all($keyword = '')
    {
        $query = DB::table($this->table);
        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('namamenu', 'LIKE', "%{$keyword}%")->orWhere('isi', 'LIKE', "%{$keyword}%");
            });
        }
        return $query->count();
    }

    public function get_id($id) { $result = DB::table($this->table)->where($this->pk, $id)->first(); return $result ? (array) $result : null; }
    public function add($data) { return DB::table($this->table)->insertGetId($data); }
    public function edit($id, $data) { return DB::table($this->table)->where($this->pk, $id)->update($data); }
    public function delete($id) { return DB::table($this->table)->where($this->pk, $id)->delete(); }
    
    public function uploadFile($file, $path = 'pmb/page') { $fileName = time() . '_' . $file->getClientOriginalName(); $file->move(public_path($path), $fileName); return $fileName; }
    public function deleteFile($fileName, $path = 'pmb/page') { $filePath = public_path($path . '/' . $fileName); if (file_exists($filePath)) { unlink($filePath); } }
    public function getNamamenuById($id) { return DB::table($this->table)->where('id', $id)->value('namamenu'); }
}
