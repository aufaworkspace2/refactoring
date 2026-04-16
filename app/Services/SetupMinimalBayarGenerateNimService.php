<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SetupMinimalBayarGenerateNimService
{
    public function get_data($limit, $offset, $keyword = '', $ProgramID = '', $ProdiID = '')
    {
        $user = DB::table('user')->where('ID', Session::get('UserID'))->first();
        $arrProdi = $user->ProdiID ? explode(",", $user->ProdiID) : [];
        $LevelKode = Session::get('LevelKode');

        $query = DB::table('setup_minimal_bayar')
            ->where('Jenis', 'Generate NIM');

        if ($keyword) {
            $query->where('Jenis', 'like', "%{$keyword}%");
        }

        if ($ProgramID !== null && $ProgramID !== "") {
            $query->where("ProgramID", $ProgramID);
        }

        if ($ProdiID !== null && $ProdiID !== "") {
            $ProdiID_arr = explode(',', $ProdiID);
            $query->whereIn("ProdiID", $ProdiID_arr);
        } else {
            if (!in_array('SPR', explode(',', $LevelKode ?? ''))) {
                $query->whereIn("ProdiID", $arrProdi ?: [0]);
            }
        }

        $query->orderBy('ID', 'ASC');

        if ($limit !== null && $limit !== '') {
            $query->limit($limit);
        }

        if ($offset !== null && $offset !== '') {
            $query->offset($offset);
        }

        return $query->get();
    }

    public function count_all($keyword = '', $ProgramID = '', $ProdiID = '')
    {
        $user = DB::table('user')->where('ID', Session::get('UserID'))->first();
        $arrProdi = $user->ProdiID ? explode(",", $user->ProdiID) : [];
        $LevelKode = Session::get('LevelKode');

        $query = DB::table('setup_minimal_bayar')
            ->where('Jenis', 'Generate NIM');

        if ($keyword) {
            $query->where('Jenis', 'like', "%{$keyword}%");
        }

        if ($ProgramID !== null && $ProgramID !== "") {
            $query->where("ProgramID", $ProgramID);
        }

        if ($ProdiID !== null && $ProdiID !== "") {
            $ProdiID_arr = explode(',', $ProdiID);
            $query->whereIn("ProdiID", $ProdiID_arr);
        } else {
            if (!in_array('SPR', explode(',', $LevelKode ?? ''))) {
                $query->whereIn("ProdiID", $arrProdi ?: [0]);
            }
        }

        return $query->count();
    }

    public function get_id($id)
    {
        return DB::table('setup_minimal_bayar')->where('ID', $id)->first();
    }

    public function add($data)
    {
        return DB::table('setup_minimal_bayar')->insertGetId($data);
    }

    public function edit($id, $data)
    {
        return DB::table('setup_minimal_bayar')->where('ID', $id)->update($data);
    }

    public function delete($id)
    {
        if (is_array($id)) {
            foreach ($id as $id_item) {
                DB::table('setup_minimal_bayar')->where('ID', $id_item)->delete();
            }
        } else {
            return DB::table('setup_minimal_bayar')->where('ID', $id)->delete();
        }
    }

    public function check_duplicate($jenis, $programId, $prodiId, $excludeId = null)
    {
        $query = DB::table('setup_minimal_bayar')
            ->where('Jenis', $jenis)
            ->where('ProgramID', $programId)
            ->where('ProdiID', $prodiId);

        if ($excludeId) {
            $query->where('ID', '!=', $excludeId);
        }

        return $query->first();
    }

    public function save($save, $inputData)
    {
        $ID = $inputData['ID'] ?? '';
        $Jenis = $inputData['Jenis'] ?? 'Generate NIM';
        $ProgramID = $inputData['ProgramID'] ?? '0';
        $ProdiID = $inputData['ProdiID'] ?? '0';
        $Nominal = $inputData['Jumlah'] ?? $inputData['Nominal'] ?? '';
        $JenisBiayaID_list = isset($inputData['JenisBiayaID_list']) && is_array($inputData['JenisBiayaID_list']) ? implode(",", $inputData['JenisBiayaID_list']) : '';

        // Clean currency value
        $Nominal = str_replace(['.', ','], '', $Nominal);

        $input['Jenis'] = $Jenis;
        $input['ProgramID'] = $ProgramID;
        $input['ProdiID'] = $ProdiID;
        $input['Nominal'] = $Nominal;
        $input['JenisBiayaID_list'] = $JenisBiayaID_list;

        // Check duplicate
        $excludeId = ($save == 2 && !empty($ID)) ? $ID : null;
        $cek = $this->check_duplicate($Jenis, $ProgramID, $ProdiID, $excludeId);

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
