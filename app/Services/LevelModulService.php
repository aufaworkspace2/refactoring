<?php

namespace App\Services;

use App\Models\LevelModul;
use Illuminate\Support\Facades\DB;

class LevelModulService
{
    public function get_data($limit, $offset, $level_id = '', $keyword = '')
    {
        $query = LevelModul::query();
        
        if ($level_id) {
            $query->where('LevelID', $level_id);
        }
        
        if ($keyword) {
            $query->where('Nama', 'like', "%{$keyword}%");
        }
        
        // Mencegah error bind jika dipanggil oleh report PDF/Excel tanpa paginasi
        if ($limit !== null) {
            $query->take($limit);
        }
        
        if ($offset !== null) {
            $query->skip($offset);
        }
        
        return $query->get();
    }

    public function count_all($level_id = '', $keyword = '')
    {
        $query = LevelModul::query();
        
        if ($level_id) {
            $query->where('LevelID', $level_id);
        }
        
        if ($keyword) {
            $query->where('Nama', 'like', "%{$keyword}%");
        }
        
        return $query->count();
    }

    public function get_id($id)
    {
        return LevelModul::where('ID', $id)->first();
    }

    public function delete($checkid)
    {
        if (is_array($checkid)) {
            foreach ($checkid as $id) {
                LevelModul::where('ID', $id)->delete();
            }
        }
    }
}
