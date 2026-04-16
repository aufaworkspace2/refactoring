<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SetupDendaService
{
    public function get_data($limit, $offset, $keyword = '', $ProgramID = '', $ProdiID = '', $TahunMasuk = '', $TahunID = '', $JenisBiayaID = '')
    {
        $user = DB::table('user')->where('ID', Session::get('UserID'))->first();
        $arrProdi = $user->ProdiID ? explode(",", $user->ProdiID) : [];
        $LevelKode = Session::get('LevelKode');

        $query = DB::table('setup_denda')
            ->select('setup_denda.*', 'jenisbiaya.Nama as NamaJenisBiaya')
            ->join('jenisbiaya', 'jenisbiaya.ID', '=', 'setup_denda.JenisBiayaID');

        if ($keyword) {
            $query->whereRaw("(jenisbiaya.Nama LIKE ?)", ["%{$keyword}%"]);
        }

        if ($ProgramID !== null && $ProgramID !== "") {
            $query->where("setup_denda.ProgramID", $ProgramID);
        }

        if ($ProdiID !== null && $ProdiID !== "") {
            $ProdiID_arr = explode(',', $ProdiID);
            $query->whereIn("setup_denda.ProdiID", $ProdiID_arr);
        } 

        if ($TahunMasuk !== null && $TahunMasuk !== "") {
            $query->where("setup_denda.TahunMasuk", $TahunMasuk);
        }

        if ($TahunID) {
            $query->where('setup_denda.TahunID', $TahunID);
        }

        if ($JenisBiayaID) {
            $query->where('setup_denda.JenisBiayaID', $JenisBiayaID);
        }

        $query->orderBy('setup_denda.ID', 'ASC');

        if ($limit !== null && $limit !== '') {
            $query->limit($limit);
        }

        if ($offset !== null && $offset !== '') {
            $query->offset($offset);
        }

        return $query->get();
    }

    public function count_all($keyword = '', $ProgramID = '', $ProdiID = '', $TahunMasuk = '', $TahunID = '', $JenisBiayaID = '')
    {
        $user = DB::table('user')->where('ID', Session::get('UserID'))->first();
        $arrProdi = $user->ProdiID ? explode(",", $user->ProdiID) : [];
        $LevelKode = Session::get('LevelKode');

        $query = DB::table('setup_denda')
            ->select('setup_denda.ID')
            ->join('jenisbiaya', 'jenisbiaya.ID', '=', 'setup_denda.JenisBiayaID');

        if ($keyword) {
            $query->whereRaw("(jenisbiaya.Nama LIKE ?)", ["%{$keyword}%"]);
        }

        if ($ProgramID !== null && $ProgramID !== "") {
            $query->where("setup_denda.ProgramID", $ProgramID);
        }

        if ($ProdiID !== null && $ProdiID !== "") {
            $ProdiID_arr = explode(',', $ProdiID);
            $query->whereIn("setup_denda.ProdiID", $ProdiID_arr);
        } else {
            if (!in_array('SPR', explode(',', $LevelKode ?? ''))) {
                $query->whereIn("setup_denda.ProdiID", $arrProdi ?: [0]);
            }
        }

        if ($TahunMasuk !== null && $TahunMasuk !== "") {
            $query->where("setup_denda.TahunMasuk", $TahunMasuk);
        }

        if ($TahunID) {
            $query->where('setup_denda.TahunID', $TahunID);
        }

        if ($JenisBiayaID) {
            $query->where('setup_denda.JenisBiayaID', $JenisBiayaID);
        }

        $query->orderBy('setup_denda.ID', 'ASC');

        return $query->count();
    }

    public function get_id($id)
    {
        return DB::table('setup_denda')->where('ID', $id)->first();
    }

    public function add($data)
    {
        return DB::table('setup_denda')->insertGetId($data);
    }

    public function edit($id, $data)
    {
        return DB::table('setup_denda')->where('ID', $id)->update($data);
    }

    public function delete($id)
    {
        if (is_array($id)) {
            foreach ($id as $id_item) {
                DB::table('setup_denda')->where('ID', $id_item)->delete();
            }
        } else {
            return DB::table('setup_denda')->where('ID', $id)->delete();
        }
    }

    public function check_duplicate($tahunId, $programId, $prodiId, $tahunMasuk, $jenisBiayaId, $hari, $excludeId = null)
    {
        $query = DB::table('setup_denda')
            ->where('TahunID', $tahunId)
            ->where('ProgramID', $programId)
            ->where('ProdiID', $prodiId)
            ->where('TahunMasuk', $tahunMasuk)
            ->where('JenisBiayaID', $jenisBiayaId)
            ->where('Hari', $hari);

        if ($excludeId) {
            $query->where('ID', '!=', $excludeId);
        }

        return $query->first();
    }

    public function save($save, $inputData)
    {
        $ID = $inputData['ID'] ?? '';
        $TahunID = $inputData['TahunID'] ?? '';
        $JenisBiayaID = $inputData['JenisBiayaID'] ?? '';
        $ProgramID = $inputData['ProgramID'] ?? '0';
        $ProdiID = $inputData['ProdiID'] ?? '0';
        $TahunMasuk = $inputData['TahunMasuk'] ?? '0';
        $Tipe = $inputData['Tipe'] ?? '';
        $Jumlah = $inputData['Jumlah'] ?? '';
        $Hari = $inputData['Hari'] ?? '';

        // Clean currency value
        $Jumlah = str_replace(['.', ','], '', $Jumlah);

        $input['TahunID'] = $TahunID;
        $input['JenisBiayaID'] = $JenisBiayaID;
        $input['ProgramID'] = $ProgramID;
        $input['ProdiID'] = $ProdiID;
        $input['TahunMasuk'] = $TahunMasuk;
        $input['Tipe'] = $Tipe;
        $input['Jumlah'] = $Jumlah;
        $input['Hari'] = $Hari;

        // Check duplicate
        $excludeId = ($save == 2 && !empty($ID)) ? $ID : null;
        $cek = $this->check_duplicate($TahunID, $ProgramID, $ProdiID, $TahunMasuk, $JenisBiayaID, $Hari, $excludeId);

        if ($cek && $cek->ID) {
            return "gagal";
        }

        if ($save == 1) {
            $input['createdAt'] = date('Y-m-d H:i:s');

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
