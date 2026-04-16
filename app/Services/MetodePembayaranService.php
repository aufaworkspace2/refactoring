<?php

namespace App\Services;

use App\Models\MetodePembayaran;
use Illuminate\Support\Facades\DB;

class MetodePembayaranService
{
    public function get_data($limit, $offset, $keyword = '')
    {
        $query = DB::table('metode_pembayaran');

        if ($keyword) {
            $query->where('Nama', 'like', "%{$keyword}%");
        }

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
        $query = DB::table('metode_pembayaran');

        if ($keyword) {
            $query->where('Nama', 'like', "%{$keyword}%");
        }

        return $query->count();
    }

    public function get_id($id)
    {
        return DB::table('metode_pembayaran')->where('ID', $id)->first();
    }

    public function add($data)
    {
        return DB::table('metode_pembayaran')->insertGetId($data);
    }

    public function edit($id, $data)
    {
        return DB::table('metode_pembayaran')->where('ID', $id)->update($data);
    }

    public function delete($id)
    {
        if (is_array($id)) {
            for ($x = 0; $x < count($id); $x++) {
                DB::table('metode_pembayaran')->where('ID', $id[$x])->delete();
            }
        } else {
            return DB::table('metode_pembayaran')->where('ID', $id)->delete();
        }
    }

    public function save($save, $inputData)
    {
        $ID = $inputData['ID'] ?? '';
        $Nama = $inputData['Nama'] ?? '';

        $input['Nama'] = $Nama;

        // Cek duplikasi nama (exclude current ID for update)
        $cek = DB::selectOne("SELECT ID FROM metode_pembayaran WHERE Nama = ? AND ID != ?", [$Nama, $ID]);

        if ($cek && $cek->ID) {
            return "gagal";
        }

        if ($save == 1) {
            $input['createdAt'] = date('Y-m-d H:i:s');
            $this->add($input);
            return DB::getPdo()->lastInsertId();
        }

        if ($save == 2) {
            $this->edit($ID, $input);
            return $ID;
        }
    }

    public function check_channel_exists($metodePembayaranId)
    {
        $channel = DB::table('channel_pembayaran')
            ->where('MetodePembayaranID', $metodePembayaranId)
            ->first();

        return $channel ? true : false;
    }

    public function get_default_ids()
    {
        return [1, 2, 3, 4];
    }
}
