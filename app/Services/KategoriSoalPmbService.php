<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class KategoriSoalPmbService
{
    private $table = 'pmb_tbl_kategori_soal';
    private $table2 = 'pmb_tbl_soal';
    private $table3 = 'pmb_tbl_subsoal';
    private $pk = 'id';

    // Kategori Soal
    public function get_data($limit, $offset, $keyword = '')
    {
        $query = DB::table($this->table);
        if ($keyword) {
            $query->where('nama', 'LIKE', "%{$keyword}%");
        }
        if ($limit !== null) { $query->limit($limit); }
        if ($offset !== null) { $query->offset($offset); }
        return $query->get()->map(fn($item) => (array) $item)->toArray();
    }

    public function count_all($keyword = '')
    {
        $query = DB::table($this->table);
        if ($keyword) { $query->where('nama', 'LIKE', "%{$keyword}%"); }
        return $query->count();
    }

    public function get_id($id) { $result = DB::table($this->table)->where($this->pk, $id)->first(); return $result ? (array) $result : null; }
    public function add($data) { return DB::table($this->table)->insertGetId($data); }
    public function edit($id, $data) { return DB::table($this->table)->where($this->pk, $id)->update($data); }
    public function delete($id) { return DB::table($this->table)->where($this->pk, $id)->delete(); }
    public function checkDuplicateNama($nama, $id = '') { $query = DB::table($this->table)->where('nama', $nama); if ($id) { $query->where('id', '!=', $id); } return $query->first(); }

    // Soal
    public function get_data_soal($limit, $offset, $idkategori = '', $keyword = '')
    {
        $query = DB::table($this->table2);
        if ($idkategori) { $query->where('idkategori', $idkategori); }
        if ($keyword) { $query->where('soal', 'LIKE', "%{$keyword}%"); }
        $query->orderBy('id', 'ASC');
        if ($limit !== null) { $query->limit($limit); }
        if ($offset !== null) { $query->offset($offset); }
        return $query->get()->map(fn($item) => (array) $item)->toArray();
    }

    public function count_all_soal($idkategori = '', $keyword = '')
    {
        $query = DB::table($this->table2);
        if ($idkategori) { $query->where('idkategori', $idkategori); }
        if ($keyword) { $query->where('soal', 'LIKE', "%{$keyword}%"); }
        return $query->count();
    }

    public function get_id_soal($id) { $result = DB::table($this->table2)->where($this->pk, $id)->first(); return $result ? (array) $result : null; }
    public function add_soal($data) { return DB::table($this->table2)->insertGetId($data); }
    public function edit_soal($id, $data) { return DB::table($this->table2)->where($this->pk, $id)->update($data); }
    public function delete_soal($id) { return DB::table($this->table2)->where($this->pk, $id)->delete(); }
    public function getSoalById($id) { return DB::table($this->table2)->where($this->pk, $id)->value('soal'); }

    // Sub Soal
    public function get_data_subsoal($limit, $offset, $idkategori = '', $idsoal = '')
    {
        $query = DB::table($this->table3);
        if ($idsoal) { $query->where('idsoal', $idsoal); }
        $query->orderBy('id', 'ASC');
        if ($limit !== null) { $query->limit($limit); }
        if ($offset !== null) { $query->offset($offset); }
        return $query->get()->map(fn($item) => (array) $item)->toArray();
    }

    public function count_all_subsoal($idkategori = '', $idsoal = '')
    {
        $query = DB::table($this->table3);
        if ($idsoal) { $query->where('idsoal', $idsoal); }
        return $query->count();
    }

    public function get_id_subsoal($id) { $result = DB::table($this->table3)->where($this->pk, $id)->first(); return $result ? (array) $result : null; }
    public function add_subsoal($data) { return DB::table($this->table3)->insertGetId($data); }
    public function edit_subsoal($id, $data) { return DB::table($this->table3)->where($this->pk, $id)->update($data); }
    public function delete_subsoal($id) { return DB::table($this->table3)->where($this->pk, $id)->delete(); }

    // Copy Soal
    public function copy_soal($from_kategori_id, $to_kategori_id)
    {
        $soals = DB::table($this->table2)->where('idkategori', $from_kategori_id)->get();
        foreach ($soals as $soal) {
            $insert = [
                'soal' => $soal->soal,
                'jawaban' => $soal->jawaban,
                'idkategori' => $to_kategori_id,
                'pilihana' => $soal->pilihana,
                'pilihanb' => $soal->pilihanb,
                'pilihanc' => $soal->pilihanc,
                'pilihand' => $soal->pilihand,
                'pilihane' => $soal->pilihane,
                'cerita' => $soal->cerita,
            ];
            DB::table($this->table2)->insert($insert);
        }
        return true;
    }

    public function count_soal_by_kategori($idkategori)
    {
        return DB::table($this->table2)->where('idkategori', $idkategori)->count();
    }

    public function get_all_kategori()
    {
        return DB::table($this->table)->get();
    }
}
