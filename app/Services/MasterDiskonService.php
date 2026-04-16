<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class MasterDiskonService
{
    public function get_data($limit, $offset, $keyword = '', $Tipe = '', $BiayaAwalID = '', $ProdiID = '')
    {
        $query = DB::table('master_diskon')
            ->select('master_diskon.*');

        if ($Tipe) {
            $query->where('Tipe', $Tipe);
        }

        if ($keyword) {
            $query->whereRaw("(Nama LIKE ?)", ["%{$keyword}%"]);
        }

        if ($BiayaAwalID) {
            $query->where('BiayaAwalID', $BiayaAwalID);
        }

        if ($ProdiID) {
            $query->where('ProdiID', $ProdiID);
        }

        if ($limit !== null && $limit !== '') {
            $query->limit($limit);
        }

        if ($offset !== null && $offset !== '') {
            $query->offset($offset);
        }

        return $query->get();
    }

    public function count_all($keyword = '', $Tipe = '', $BiayaAwalID = '', $ProdiID = '')
    {
        $query = DB::table('master_diskon')
            ->select('master_diskon.*');

        if ($Tipe) {
            $query->where('Tipe', $Tipe);
        }

        if ($keyword) {
            $query->whereRaw("(Nama LIKE ?)", ["%{$keyword}%"]);
        }

        if ($BiayaAwalID) {
            $query->where('BiayaAwalID', $BiayaAwalID);
        }

        if ($ProdiID) {
            $query->where('ProdiID', $ProdiID);
        }

        return $query->count();
    }

    public function get_id($id)
    {
        return DB::table('master_diskon')->where('ID', $id)->first();
    }

    public function add($data)
    {
        return DB::table('master_diskon')->insertGetId($data);
    }

    public function edit($id, $data)
    {
        return DB::table('master_diskon')->where('ID', $id)->update($data);
    }

    public function delete($id)
    {
        if (is_array($id)) {
            foreach ($id as $id_item) {
                DB::table('master_diskon')->where('ID', $id_item)->delete();
            }
        } else {
            return DB::table('master_diskon')->where('ID', $id)->delete();
        }
    }

    public function check_duplicate($nama, $prodiId, $excludeId = null)
    {
        $query = DB::table('master_diskon')
            ->where('Nama', $nama)
            ->where('ProdiID', $prodiId);

        if ($excludeId) {
            $query->where('ID', '!=', $excludeId);
        }

        return $query->first();
    }

    public function save($save, $inputData)
    {
        $ID = $inputData['ID'] ?? '';
        $Nama = $inputData['Nama'] ?? '';
        $Tipe = $inputData['Tipe'] ?? 'nominal';
        $Jumlah = $inputData['Jumlah'] ?? '';
        $BiayaAwalID = $inputData['BiayaAwalID'] ?? '';
        $ProdiID = $inputData['ProdiID'] ?? '';
        $RangeAwalNilaiUSM = $inputData['RangeAwalNilaiUSM'] ?? '';
        $RangeAkhirNilaiUSM = $inputData['RangeAkhirNilaiUSM'] ?? '';
        $JenisDiskon = $inputData['JenisDiskon'] ?? 'potong_dari_total';
        $UserID = Session::get('UserID');

        // Clean Jumlah value (remove thousand separators for nominal)
        if ($Tipe == 'nominal') {
            $Jumlah = str_replace(['.', ','], '', $Jumlah);
        }

        $input['Nama'] = $Nama;
        $input['Tipe'] = $Tipe;
        $input['Jumlah'] = $Jumlah;
        $input['BiayaAwalID'] = $BiayaAwalID ?: null;
        $input['ProdiID'] = $ProdiID ?: null;
        $input['RangeAwalNilaiUSM'] = $RangeAwalNilaiUSM ?: null;
        $input['RangeAkhirNilaiUSM'] = $RangeAkhirNilaiUSM ?: null;
        $input['LastUpdateUserID'] = $UserID;
        $input['JenisDiskon'] = $JenisDiskon;

        // Check duplicate
        $cek = $this->check_duplicate($Nama, $ProdiID, $ID);

        if ($cek && $cek->ID) {
            return "gagal";
        }

        if ($save == 1) {
            $input['createdAt'] = date('Y-m-d H:i:s');
            $input['UserID'] = $UserID;

            $newID = $this->add($input);
            return $newID;
        }

        if ($save == 2) {
            $this->edit($ID, $input);
            return $ID;
        }

        return "gagal";
    }
}
