<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class BankService
{
    public function get_data($limit, $offset, $keyword = '')
    {
        $query = DB::table('bank')->where('StatusHide', 0);

        if ($keyword) {
            $query->where('NamaBank', 'like', "%{$keyword}%");
        }

        $query->orderBy('NamaBank', 'ASC');

        if ($limit !== null && $limit !== '') {
            $query->take($limit);
        }

        if ($offset !== null && $offset !== '') {
            $query->skip($offset);
        }

        return $query->get();
    }

    public function count_all($keyword = '')
    {
        $query = DB::table('bank')->where('StatusHide', 0);

        if ($keyword) {
            $query->where('NamaBank', 'like', "%{$keyword}%");
        }

        return $query->count();
    }

    public function get_id($id)
    {
        return DB::table('bank')->where('ID', $id)->first();
    }

    public function add($data)
    {
        return DB::table('bank')->insertGetId($data);
    }

    public function edit($id, $data)
    {
        return DB::table('bank')->where('ID', $id)->update($data);
    }

    public function delete($id)
    {
        if (is_array($id)) {
            foreach ($id as $id_item) {
                DB::table('bank')->where('ID', $id_item)->delete();
            }
        } else {
            return DB::table('bank')->where('ID', $id)->delete();
        }
    }

    public function get_channel_pembayaran_list()
    {
        return DB::table('channel_pembayaran')
            ->whereIn('MetodePembayaranID', [2, 3])
            ->get();
    }

    public function check_duplicate($namaBank, $excludeId = null)
    {
        $query = DB::table('bank')
            ->where('NamaBank', $namaBank);

        if ($excludeId) {
            $query->where('ID', '!=', $excludeId);
        }

        return $query->first();
    }

    public function save($save, $inputData)
    {
        $ID = $inputData['ID'] ?? '';
        $NamaBank = $inputData['NamaBank'] ?? '';
        $NoRekening = $inputData['NoRekening'] ?? '';
        $NamaPemilik = $inputData['NamaPemilik'] ?? '';
        $ChannelPembayaranID_list = isset($inputData['ChannelPembayaranID_list']) 
            ? implode(',', $inputData['ChannelPembayaranID_list']) 
            : '';

        $input['NamaBank'] = $NamaBank;
        $input['NoRekening'] = $NoRekening;
        $input['NamaPemilik'] = $NamaPemilik;
        $input['ChannelPembayaranID_list'] = $ChannelPembayaranID_list;

        // Check duplicate
        $cek = $this->check_duplicate($NamaBank, $ID);

        if ($cek && $cek->ID) {
            return "gagal";
        }

        if ($save == 1) {
            $this->add($input);
            $ID = DB::getPdo()->lastInsertId();
        }

        if ($save == 2) {
            $this->edit($ID, $input);
        }

        return $ID;
    }
}
