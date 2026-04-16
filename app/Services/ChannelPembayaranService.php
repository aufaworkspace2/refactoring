<?php

namespace App\Services;

use App\Models\ChannelPembayaran;
use App\Models\PanduanPembayaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ChannelPembayaranService
{
    public function get_data($limit, $offset, $keyword = '', $MetodePembayaranID = '')
    {
        $query = DB::table('channel_pembayaran');

        if ($keyword) {
            $query->where('Nama', 'like', "%{$keyword}%");
        }

        if ($MetodePembayaranID) {
            $query->where('MetodePembayaranID', $MetodePembayaranID);
        }

        if ($limit !== null) {
            $query->take($limit);
        }

        if ($offset !== null) {
            $query->skip($offset);
        }

        return $query->get();
    }

    public function count_all($keyword = '', $MetodePembayaranID = '')
    {
        $query = DB::table('channel_pembayaran');

        if ($keyword) {
            $query->where('Nama', 'like', "%{$keyword}%");
        }

        if ($MetodePembayaranID) {
            $query->where('MetodePembayaranID', $MetodePembayaranID);
        }

        return $query->count();
    }

    public function get_id($id)
    {
        return DB::table('channel_pembayaran')->where('ID', $id)->first();
    }

    public function add($data)
    {
        return DB::table('channel_pembayaran')->insertGetId($data);
    }

    public function edit($id, $data)
    {
        return DB::table('channel_pembayaran')->where('ID', $id)->update($data);
    }

    public function delete($id)
    {
        if (is_array($id)) {
            for ($x = 0; $x < count($id); $x++) {
                DB::table('channel_pembayaran')->where('ID', $id[$x])->delete();
            }
        } else {
            return DB::table('channel_pembayaran')->where('ID', $id)->delete();
        }
    }

    public function get_list_panduan($channelIds)
    {
        if (empty($channelIds)) {
            return [];
        }

        $query = DB::table('panduan_pembayaran')
            ->whereIn('ChannelPembayaranID', $channelIds)
            ->orderBy('Urut', 'ASC')
            ->get();

        $list_panduan = [];
        foreach ($query as $row_panduan) {
            $list_panduan[$row_panduan->ChannelPembayaranID][] = $row_panduan;
        }

        return $list_panduan;
    }

    public function get_jenis_biaya()
    {
        $query = DB::table('jenisbiaya')->get();
        $jenisbiaya = [];
        foreach ($query as $row) {
            $jenisbiaya[$row->ID] = $row;
        }
        return $jenisbiaya;
    }

    public function get_metode_pembayaran_list()
    {
        return DB::table('metode_pembayaran')->orderBy('Nama', 'ASC')->get();
    }

    public function save($save, $inputData, $files = null)
    {
        $ID = $inputData['ID'] ?? '';
        $Nama = $inputData['Nama'] ?? '';
        $MetodePembayaranID = $inputData['MetodePembayaranID'] ?? '';
        $JenisBiayaID_list = isset($inputData['JenisBiayaID_list']) ? implode(',', $inputData['JenisBiayaID_list']) : '';
        $BiayaAdmin = $inputData['BiayaAdmin'] ?? '';

        $input['Nama'] = $Nama;
        $input['MetodePembayaranID'] = $MetodePembayaranID;
        $input['JenisBiayaID_list'] = $JenisBiayaID_list;
        $input['BiayaAdmin'] = str_replace('.', '', $BiayaAdmin); // Remove thousand separator

        // Handle file upload
        if ($files && isset($files['Icon']) && $files['Icon']->isValid()) {
            $file = $files['Icon'];
            $extension = $file->getClientOriginalExtension();
            $allowedTypes = ['jpeg', 'jpg', 'png', 'gif'];

            if (in_array(strtolower($extension), $allowedTypes)) {
                $maxSize = 2000 * 1024; // 2000KB in bytes
                if ($file->getSize() <= $maxSize) {
                    $fileName = time() . '_' . uniqid() . '.' . $extension;
                    $uploadPath = public_path('metodebayar/channelbayar');

                    // Create directory if not exists
                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0777, true);
                    }

                    $file->move($uploadPath, $fileName);
                    $input['Icon'] = $fileName;
                }
            }
        }

        // Check duplicate
        $cek = DB::selectOne("SELECT ID FROM channel_pembayaran WHERE Nama = ? AND MetodePembayaranID = ? AND ID != ?", [$Nama, $MetodePembayaranID, $ID]);

        if ($cek && $cek->ID) {
            return "gagal";
        }

        if ($save == 1) {
            $input['createdAt'] = date('Y-m-d H:i:s');
            $this->add($input);
            $ID = DB::getPdo()->lastInsertId();
        }

        if ($save == 2) {
            $this->edit($ID, $input);
        }

        return $ID;
    }

    public function save_panduan_pembayaran($channelId, $namaPanduanList, $textCaraBayarList)
    {
        $list_id = [];
        $urut = 1;

        foreach ($namaPanduanList as $key => $namaPanduan) {
            $input_syarat = [
                'ChannelPembayaranID' => $channelId,
                'NamaPanduan' => $namaPanduan,
                'TextCaraBayar' => $textCaraBayarList[$key] ?? '',
                'Urut' => $urut
            ];

            // Check if exists
            $cek_dok = DB::selectOne("SELECT ID FROM panduan_pembayaran WHERE ChannelPembayaranID = ? AND Urut = ?", [$channelId, $urut]);

            if ($cek_dok) {
                // Update
                DB::table('panduan_pembayaran')
                    ->where('ID', $cek_dok->ID)
                    ->update($input_syarat);
                $list_id[] = $cek_dok->ID;
            } else {
                // Insert
                $input_syarat['createdAt'] = date('Y-m-d H:i:s');
                $input_syarat['UserID'] = Session::get('UserID');

                DB::table('panduan_pembayaran')->insert($input_syarat);
                $list_id[] = DB::getPdo()->lastInsertId();
            }

            $urut++;
        }

        // Delete old panduan that not in list_id
        if (count($list_id) > 0) {
            DB::table('panduan_pembayaran')
                ->where('ChannelPembayaranID', $channelId)
                ->whereNotIn('ID', $list_id)
                ->delete();
        } else {
            DB::table('panduan_pembayaran')
                ->where('ChannelPembayaranID', $channelId)
                ->delete();
        }
    }

    public function set_aktif($val, $status)
    {
        return DB::table('channel_pembayaran')
            ->where('ID', $val)
            ->update(['Status' => $status]);
    }

    public function get_panduan_by_channel($channelId)
    {
        return DB::table('panduan_pembayaran')
            ->where('ChannelPembayaranID', $channelId)
            ->orderBy('Urut', 'ASC')
            ->get();
    }
}
