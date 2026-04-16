<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class JadwalUsmPmbService
{
    private $table = 'pmb_edu_jadwalusm';
    private $table2 = 'pmb_edu_jadwalusm_detail';
    private $pk = 'id';

    public function get_data($limit, $offset, $keyword = '', $gelombang = '', $jenis = [])
    {
        $query = DB::table($this->table);
        
        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('gelombang', 'LIKE', "%{$keyword}%")
                  ->orWhere('kode', 'LIKE', "%{$keyword}%");
            });
        }
        
        if ($gelombang) {
            $query->where('gelombang', $gelombang);
        }
        
        if ($jenis) {
            $query->where(function($q) use ($jenis) {
                foreach ($jenis as $needle) {
                    $q->orWhereRaw('FIND_IN_SET(?, jenis_ujin) != 0', [$needle]);
                }
            });
        }
        
        if ($limit !== null) { $query->limit($limit); }
        if ($offset !== null) { $query->offset($offset); }
        
        return $query->get()->map(fn($item) => (array) $item)->toArray();
    }

    public function count_all($keyword = '', $gelombang = '', $jenis = [])
    {
        $query = DB::table($this->table);
        
        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('gelombang', 'LIKE', "%{$keyword}%")
                  ->orWhere('kode', 'LIKE', "%{$keyword}%");
            });
        }
        
        if ($gelombang) {
            $query->where('gelombang', $gelombang);
        }
        
        if ($jenis) {
            $query->where(function($q) use ($jenis) {
                foreach ($jenis as $needle) {
                    $q->orWhereRaw('FIND_IN_SET(?, jenis_ujin) != 0', [$needle]);
                }
            });
        }
        
        return $query->count();
    }

    public function get_id($id) { $result = DB::table($this->table)->where($this->pk, $id)->first(); return $result ? (array) $result : null; }
    public function add($data) { return DB::table($this->table)->insertGetId($data); }
    public function edit($id, $data) { return DB::table($this->table)->where($this->pk, $id)->update($data); }
    public function delete($id) { return DB::table($this->table)->where($this->pk, $id)->delete(); }

    // Detail functions
    public function get_data_detail($limit, $offset, $jadwalusm_id = '')
    {
        $query = DB::table($this->table2);
        if ($jadwalusm_id) { $query->where('jadwalusm_id', $jadwalusm_id); }
        $query->orderBy('id', 'ASC');
        if ($limit !== null) { $query->limit($limit); }
        if ($offset !== null) { $query->offset($offset); }
        return $query->get()->map(fn($item) => (array) $item)->toArray();
    }

    public function count_all_detail($jadwalusm_id = '')
    {
        $query = DB::table($this->table2);
        if ($jadwalusm_id) { $query->where('jadwalusm_id', $jadwalusm_id); }
        return $query->count();
    }

    public function get_id_detail($id) { $result = DB::table($this->table2)->where($this->pk, $id)->first(); return $result ? (array) $result : null; }
    public function add_detail($data) { return DB::table($this->table2)->insertGetId($data); }
    public function edit_detail($id, $data) { return DB::table($this->table2)->where($this->pk, $id)->update($data); }
    public function delete_detail($id) { return DB::table($this->table2)->where($this->pk, $id)->delete(); }

    // Helper functions
    public function checkDuplicateKode($kode, $id = '') { $query = DB::table($this->table)->where('kode', $kode); if ($id) { $query->where('id', '!=', $id); } return $query->first(); }
    
    public function count_jadwalusm_detail($jadwalusm_id) { return DB::table($this->table2)->where('jadwalusm_id', $jadwalusm_id)->count(); }
    
    public function get_jadwalusm_by_gelombang($gelombang) { return DB::table($this->table)->where('gelombang', $gelombang)->get(); }
}
