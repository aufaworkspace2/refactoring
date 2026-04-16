<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AgendaPmbService
{
    private $table = 'pmb_tbl_agenda';
    private $pk = 'id';

    public function get_data($limit, $offset, $keyword = '')
    {
        $query = DB::table($this->table);

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('judul', 'LIKE', "%{$keyword}%")
                  ->orWhere('isi', 'LIKE', "%{$keyword}%");
            });
        }

        if ($limit !== null) {
            $query->limit($limit);
        }
        if ($offset !== null) {
            $query->offset($offset);
        }

        return $query->orderBy('id', 'DESC')->get()->map(fn($item) => (array) $item)->toArray();
    }

    public function count_all($keyword = '')
    {
        $query = DB::table($this->table);

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('judul', 'LIKE', "%{$keyword}%")
                  ->orWhere('isi', 'LIKE', "%{$keyword}%");
            });
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

    public function checkDuplicateJudul($judul, $id = '')
    {
        $query = DB::table($this->table)
            ->select('id', 'judul')
            ->where('judul', $judul);

        if ($id) {
            $query->where('id', '!=', $id);
        }

        return $query->first();
    }

    public function generateAlias($judul)
    {
        $alias = strtolower($judul);
        $alias = preg_replace('/[^a-z0-9-]/', '-', $alias);
        $alias = preg_replace('/-+/', '-', $alias);
        return trim($alias, '-');
    }

    public function uploadImage($file, $path = 'pmb/agenda')
    {
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path($path), $fileName);
        return $fileName;
    }

    public function deleteImage($fileName, $path = 'pmb/agenda')
    {
        $filePath = public_path($path . '/' . $fileName);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function getJudulById($id)
    {
        return DB::table($this->table)->where('id', $id)->value('judul');
    }
}
