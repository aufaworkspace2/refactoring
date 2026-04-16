<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class JenisBiayaService
{
    public function get_data($limit, $offset, $keyword = '', $frekuensi = '', $Program = '', $Prodi = '', $TahunMasuk = '')
    {
        $query = DB::table('jenisbiaya');

        if ($keyword) {
            $query->whereRaw("(Nama LIKE ?)", ["%{$keyword}%"]);
        }

        if ($frekuensi) {
            $query->where('frekuensi', $frekuensi);
        }

        if ($Program) {
            $query->whereRaw("FIND_IN_SET(?, Program) > 0", [$Program]);
        }

        if ($Prodi) {
            $query->whereRaw("(FIND_IN_SET(?, Prodi) > 0 OR Prodi = '0')", [$Prodi]);
        }

        if ($TahunMasuk) {
            $query->whereRaw("(FIND_IN_SET(?, TahunMasuk) > 0 OR TahunMasuk = '0')", [$TahunMasuk]);
        }

        $query->orderBy('Urut', 'ASC');

        if ($limit !== null && $limit !== '') {
            $query->limit($limit);
        }

        if ($offset !== null && $offset !== '') {
            $query->offset($offset);
        }

        return $query->get();
    }

    public function count_all($keyword = '', $frekuensi = '', $Program = '', $Prodi = '', $TahunMasuk = '')
    {
        $query = DB::table('jenisbiaya');

        if ($keyword) {
            $query->whereRaw("(Nama LIKE ?)", ["%{$keyword}%"]);
        }

        if ($frekuensi) {
            $query->where('frekuensi', $frekuensi);
        }

        if ($Program) {
            $query->whereRaw("FIND_IN_SET(?, Program) > 0", [$Program]);
        }

        if ($Prodi) {
            $query->whereRaw("(FIND_IN_SET(?, Prodi) > 0 OR Prodi = '0')", [$Prodi]);
        }

        if ($TahunMasuk) {
            $query->whereRaw("(FIND_IN_SET(?, TahunMasuk) > 0 OR TahunMasuk = '0')", [$TahunMasuk]);
        }

        return $query->count();
    }

    public function get_id($id)
    {
        return DB::table('jenisbiaya')->where('ID', $id)->first();
    }

    public function add($data)
    {
        return DB::table('jenisbiaya')->insertGetId($data);
    }

    public function edit($id, $data)
    {
        return DB::table('jenisbiaya')->where('ID', $id)->update($data);
    }

    public function delete($id)
    {
        if (is_array($id)) {
            foreach ($id as $id_item) {
                DB::table('jenisbiaya')->where('ID', $id_item)->delete();
            }
        } else {
            return DB::table('jenisbiaya')->where('ID', $id)->delete();
        }
    }

    public function delete_sub($parentid)
    {
        return DB::table('jenisbiaya_detail')->where('JenisBiayaID', $parentid)->delete();
    }

    public function check_duplicate($nama, $excludeId = null)
    {
        $query = DB::table('jenisbiaya')
            ->where('Nama', $nama);

        if ($excludeId) {
            $query->where('ID', '!=', $excludeId);
        }

        return $query->first();
    }

    public function save($save, $inputData)
    {
        $ID = $inputData['ID'] ?? '';
        $Nama = $inputData['Nama'] ?? '';
        $Kode = $inputData['Kode'] ?? '';
        $frekuensi = $inputData['frekuensi'] ?? '';
        $Urut = $inputData['Urut'] ?? '';
        $TipeMhsw = 'mhsw';
        $PilihProgram = $inputData['PilihProgram'] ?? '0';
        $PilihProdi = $inputData['PilihProdi'] ?? '0';
        $PilihTahunMasuk = $inputData['PilihTahunMasuk'] ?? '0';
        $StatusHide = $inputData['StatusHide'] ?? '0';

        // Handle Program
        $Program = isset($inputData['Program']) && is_array($inputData['Program'])
            ? implode(',', $inputData['Program'])
            : '0';

        // Handle Prodi
        $Prodi = isset($inputData['Prodi']) && is_array($inputData['Prodi'])
            ? implode(',', $inputData['Prodi'])
            : '0';

        // Handle TahunMasuk
        $TahunMasuk = isset($inputData['TahunMasuk']) && is_array($inputData['TahunMasuk'])
            ? implode(',', $inputData['TahunMasuk'])
            : '0';

        // If radio buttons are set to "Semua" (0), override with 0
        if ($PilihProgram == '0') {
            $Program = '0';
        }
        if ($PilihProdi == '0') {
            $Prodi = '0';
        }
        if ($PilihTahunMasuk == '0') {
            $TahunMasuk = '0';
        }

        $input['Nama'] = $Nama;
        $input['Kode'] = $Kode;
        $input['frekuensi'] = $frekuensi;
        $input['Urut'] = $Urut;
        $input['TipeMhsw'] = $TipeMhsw;
        $input['Program'] = $Program;
        $input['Prodi'] = $Prodi;
        $input['TahunMasuk'] = $TahunMasuk;
        $input['StatusHide'] = $StatusHide;

        // Check duplicate
        $cek = $this->check_duplicate($Nama, $ID);

        if ($cek && $cek->ID) {
            return "gagal";
        }

        // Handle Sub Biaya (jenisbiaya_detail)
        $NamaSubBiaya = $inputData['NamaSubBiaya'] ?? [];
        $SubJenisBiayaID = $inputData['SubJenisBiayaID'] ?? [];

        if ($save == 1) {
            // Add new jenisbiaya
            $input['createdAt'] = date('Y-m-d H:i:s');
            $newID = $this->add($input);

            // Insert detail
            $ite = 0;
            foreach ($NamaSubBiaya as $i => $namaSub) {
                if (!empty($namaSub)) {
                    DB::table('jenisbiaya_detail')->insert([
                        'JenisBiayaID' => $newID,
                        'Nama' => $namaSub,
                        'Urut' => ++$ite,
                        'createdAt' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            if (function_exists('log_akses')) {
                log_akses('Tambah', 'Menambah Data Setup Jenis Biaya Dengan Nama ' . $Nama, $newID, 'jenisbiaya');
            }

            return $newID;
        }

        if ($save == 2) {
            // Update jenisbiaya
            $this->edit($ID, $input);

            // Update/Insert detail using replace logic
            $data_insert = [];
            foreach ($NamaSubBiaya as $i => $namaSub) {
                if (!empty($namaSub)) {
                    $data_insert[] = [
                        'ID' => $SubJenisBiayaID[$i] ?? null,
                        'JenisBiayaID' => $ID,
                        'Nama' => $namaSub,
                    ];
                }
            }

            // Delete existing details and insert new ones
            if (count($data_insert) > 0) {
                DB::table('jenisbiaya_detail')->where('JenisBiayaID', $ID)->delete();
                foreach ($data_insert as $item) {
                    unset($item['ID']); // Remove ID for new insert
                    $item['createdAt'] = date('Y-m-d H:i:s');
                    DB::table('jenisbiaya_detail')->insert($item);
                }
            }

            if (function_exists('log_akses')) {
                log_akses('Ubah', 'Ubah Data Setup Jenis Biaya Dengan Nama ' . $Nama, $ID, 'jenisbiaya');
            }

            return $ID;
        }

        return "gagal";
    }
}
