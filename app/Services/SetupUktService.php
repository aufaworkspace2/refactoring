<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SetupUktService
{
    public function get_data($limit, $offset, $ProgramID = '', $ProdiID = '', $TahunMasuk = '')
    {
        $user = DB::table('user')->where('ID', Session::get('UserID'))->first();
        $arrProdi = $user->ProdiID ? explode(",", $user->ProdiID) : [];
        $LevelKode = Session::get('LevelKode');

        $query = DB::table('setup_ukt');

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

        if ($TahunMasuk !== null && $TahunMasuk !== "") {
            $query->where("TahunMasuk", $TahunMasuk);
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

    public function count_all($ProgramID = '', $ProdiID = '', $TahunMasuk = '')
    {
        $user = DB::table('user')->where('ID', Session::get('UserID'))->first();
        $arrProdi = $user->ProdiID ? explode(",", $user->ProdiID) : [];
        $LevelKode = Session::get('LevelKode');

        $query = DB::table('setup_ukt');

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

        if ($TahunMasuk !== null && $TahunMasuk !== "") {
            $query->where("TahunMasuk", $TahunMasuk);
        }

        return $query->count();
    }

    public function get_id($id)
    {
        return DB::table('setup_ukt')->where('ID', $id)->first();
    }

    public function add($data)
    {
        return DB::table('setup_ukt')->insertGetId($data);
    }

    public function edit($id, $data)
    {
        return DB::table('setup_ukt')->where('ID', $id)->update($data);
    }

    public function delete($id)
    {
        if (is_array($id)) {
            foreach ($id as $id_item) {
                DB::table('setup_ukt')->where('ID', $id_item)->delete();
            }
        } else {
            return DB::table('setup_ukt')->where('ID', $id)->delete();
        }
    }

    public function check_duplicate($programId, $prodiId, $tahunMasuk, $excludeId = null)
    {
        $query = DB::table('setup_ukt')
            ->where('ProgramID', $programId)
            ->where('ProdiID', $prodiId)
            ->where('TahunMasuk', $tahunMasuk);

        if ($excludeId) {
            $query->where('ID', '!=', $excludeId);
        }

        return $query->first();
    }

    public function save($save, $inputData)
    {
        $ID = $inputData['ID'] ?? '';
        $ProgramID = $inputData['ProgramID'] ?? '0';
        $ProdiID = $inputData['ProdiID'] ?? '0';
        $TahunMasuk = $inputData['TahunMasuk'] ?? '0';

        $input['ProgramID'] = $ProgramID;
        $input['ProdiID'] = $ProdiID;
        $input['TahunMasuk'] = $TahunMasuk;

        // Check duplicate
        $excludeId = ($save == 2 && !empty($ID)) ? $ID : null;
        $cek = $this->check_duplicate($ProgramID, $ProdiID, $TahunMasuk, $excludeId);

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
