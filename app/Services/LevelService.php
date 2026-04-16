<?php

namespace App\Services;

use App\Models\Level;
use Illuminate\Support\Facades\DB;

class LevelService
{
    public function get_data($limit, $offset, $keyword = '')
    {
        $query = Level::query();
        
        if ($keyword) {
            $query->where('Nama', 'like', "%{$keyword}%");
        }
        
        $query->orderBy('Urut', 'ASC');

        // Mencegah error syntax SQL "OFFSET 0" jika Limit bernilai null/kosong
        // Berguna terutama saat mencetak PDF & Excel yang butuh menarik semua data
        if ($limit !== null) {
            $query->take($limit);
        }
        
        if ($offset !== null) {
            $query->skip($offset);
        }

        return $query->get();
    }

    public function count_all($keyword = '')
    {
        $query = Level::query();
        
        if ($keyword) {
            $query->where('Nama', 'like', "%{$keyword}%");
        }
        
        return $query->count();
    }

    public function get_id($id)
    {
        return Level::where('ID', $id)->first();
    }

    public function save($save, $inputData)
    {
        // Tetap menggunakan variabel yang sama persis
        $Nama = $inputData['Nama'] ?? '';
        $Urut = $inputData['Urut'] ?? '';
        $ID = $inputData['ID'] ?? null;
        
        $input['Nama'] = $Nama;
        $input['Urut'] = $Urut;

        if ($save == 1) {
            // Logika asli: Cek duplikasi menggunakan raw binding
            $cek = DB::selectOne("SELECT ID FROM level WHERE Nama = ?", [$Nama]);
            
            if ($cek && $cek->ID) {
                return "gagal";
            } else {
                Level::create($input);
                return $Nama;
            }
        }

        if ($save == 2) {
            Level::where('ID', $ID)->update($input);
            return $Nama;
        }
    }

    public function delete($checkid)
    {
        // Logika asli: looping array checkID dan delete satu per satu
        if (is_array($checkid)) {
            for ($x = 0; $x < count($checkid); $x++) {
                Level::where('ID', $checkid[$x])->delete();
            }
        }
    }
}