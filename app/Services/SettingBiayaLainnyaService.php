<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SettingBiayaLainnyaService
{
    public function get_data($limit, $offset, $keyword)
    {
        $query = DB::table('setting_biaya_lainnya')
            ->leftJoin('jenisbiaya', 'jenisbiaya.ID', '=', 'setting_biaya_lainnya.JenisBiayaID')
            ->select('setting_biaya_lainnya.*', 'jenisbiaya.Nama as NamaJB');

        if ($keyword) {
            $query->where('jenisbiaya.Nama', 'like', "%{$keyword}%");
        }

        $query->orderBy('setting_biaya_lainnya.ID', 'DESC');

        if ($limit) {
            return $query->skip($offset)->take($limit)->get();
        }

        return $query->get();
    }

    public function count_all($keyword)
    {
        $query = DB::table('setting_biaya_lainnya')
            ->leftJoin('jenisbiaya', 'jenisbiaya.ID', '=', 'setting_biaya_lainnya.JenisBiayaID')
            ->select('setting_biaya_lainnya.*', 'jenisbiaya.Nama as NamaJB');

        if ($keyword) {
            $query->where('jenisbiaya.Nama', 'like', "%{$keyword}%");
        }

        return $query->count();
    }

    public function get_id($id)
    {
        return DB::table('setting_biaya_lainnya')
            ->where('ID', $id)
            ->first();
    }
}
