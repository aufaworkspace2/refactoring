<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class InfoPmbService
{
    private $table = 'pmb_info';
    private $pk = 'id';

    public function get_id($id) { $result = DB::table($this->table)->where($this->pk, $id)->first(); return $result ? (array) $result : null; }
    public function add($data) { return DB::table($this->table)->insertGetId($data); }
    public function edit($id, $data) { return DB::table($this->table)->where($this->pk, $id)->update($data); }
    
    public function uploadImage($file, $path = 'pmb/logo') { $fileName = time() . '_' . $file->getClientOriginalName(); $file->move(public_path($path), $fileName); return $fileName; }
    public function deleteImage($fileName, $path = 'pmb/logo') { $filePath = public_path($path . '/' . $fileName); if (file_exists($filePath)) { unlink($filePath); } }
}
