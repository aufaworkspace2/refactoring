<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SetupDuedatePembayaranService
{
    public function get_data($limit, $offset, $keyword = '', $ProgramID = '', $ProdiID = '', $TahunMasuk = '', $TahunID = '', $JenisBiayaID = '')
    {
        $user = DB::table('user')->where('ID', Session::get('UserID'))->first();
        $arrProdi = $user->ProdiID ? explode(",", $user->ProdiID) : [];
        $LevelKode = Session::get('LevelKode');

        $query = DB::table('setup_duedate_pembayaran')
            ->select('setup_duedate_pembayaran.*', 'jenisbiaya.Nama as NamaJenisBiaya')
            ->join('jenisbiaya', 'jenisbiaya.ID', '=', 'setup_duedate_pembayaran.JenisBiayaID');

        if ($keyword) {
            $query->whereRaw("(jenisbiaya.Nama LIKE ?)", ["%{$keyword}%"]);
        }

        if ($ProgramID !== null && $ProgramID !== "") {
            $query->where("setup_duedate_pembayaran.ProgramID", $ProgramID);
        }

        if ($ProdiID !== null && $ProdiID !== "") {
            $ProdiID_arr = explode(',', $ProdiID);
            $query->whereIn("setup_duedate_pembayaran.ProdiID", $ProdiID_arr);
        } else {
            if (!in_array('SPR', explode(',', $LevelKode ?? ''))) {
                $query->whereIn("setup_duedate_pembayaran.ProdiID", $arrProdi ?: [0]);
            }
        }

        if ($TahunMasuk !== null && $TahunMasuk !== "") {
            $query->where("setup_duedate_pembayaran.TahunMasuk", $TahunMasuk);
        }

        if ($TahunID) {
            $query->where('setup_duedate_pembayaran.TahunID', $TahunID);
        }

        if ($JenisBiayaID) {
            $query->where('setup_duedate_pembayaran.JenisBiayaID', $JenisBiayaID);
        }

        $query->orderBy('setup_duedate_pembayaran.ID', 'ASC');

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

        $query = DB::table('setup_duedate_pembayaran')
            ->select('setup_duedate_pembayaran.ID')
            ->join('jenisbiaya', 'jenisbiaya.ID', '=', 'setup_duedate_pembayaran.JenisBiayaID');

        if ($keyword) {
            $query->whereRaw("(jenisbiaya.Nama LIKE ?)", ["%{$keyword}%"]);
        }

        if ($ProgramID !== null && $ProgramID !== "") {
            $query->where("setup_duedate_pembayaran.ProgramID", $ProgramID);
        }

        if ($ProdiID !== null && $ProdiID !== "") {
            $ProdiID_arr = explode(',', $ProdiID);
            $query->whereIn("setup_duedate_pembayaran.ProdiID", $ProdiID_arr);
        } else {
            if (!in_array('SPR', explode(',', $LevelKode ?? ''))) {
                $query->whereIn("setup_duedate_pembayaran.ProdiID", $arrProdi ?: [0]);
            }
        }

        if ($TahunMasuk !== null && $TahunMasuk !== "") {
            $query->where("setup_duedate_pembayaran.TahunMasuk", $TahunMasuk);
        }

        if ($TahunID) {
            $query->where('setup_duedate_pembayaran.TahunID', $TahunID);
        }

        if ($JenisBiayaID) {
            $query->where('setup_duedate_pembayaran.JenisBiayaID', $JenisBiayaID);
        }

        $query->orderBy('setup_duedate_pembayaran.ID', 'ASC');

        return $query->count();
    }

    public function get_id($id)
    {
        return DB::table('setup_duedate_pembayaran')->where('ID', $id)->first();
    }

    public function add($data)
    {
        return DB::table('setup_duedate_pembayaran')->insertGetId($data);
    }

    public function edit($id, $data)
    {
        return DB::table('setup_duedate_pembayaran')->where('ID', $id)->update($data);
    }

    public function delete($id)
    {
        if (is_array($id)) {
            foreach ($id as $id_item) {
                DB::table('setup_duedate_pembayaran')->where('ID', $id_item)->delete();
            }
        } else {
            return DB::table('setup_duedate_pembayaran')->where('ID', $id)->delete();
        }
    }

    public function check_duplicate($tahunId, $programId, $prodiId, $tahunMasuk, $jenisBiayaId, $excludeId = null)
    {
        $query = DB::table('setup_duedate_pembayaran')
            ->where('TahunID', $tahunId)
            ->where('ProgramID', $programId)
            ->where('ProdiID', $prodiId)
            ->where('TahunMasuk', $tahunMasuk)
            ->where('JenisBiayaID', $jenisBiayaId);

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
        $Tanggal = $inputData['Tanggal'] ?? '';
        $Hari = $inputData['Hari'] ?? '';

        $input['TahunID'] = $TahunID;
        $input['JenisBiayaID'] = $JenisBiayaID;
        $input['ProgramID'] = $ProgramID;
        $input['ProdiID'] = $ProdiID;
        $input['TahunMasuk'] = $TahunMasuk;
        $input['Tipe'] = $Tipe;
        $input['Tanggal'] = $Tanggal;
        $input['Hari'] = $Hari;

        // Check duplicate
        $cek = $this->check_duplicate($TahunID, $ProgramID, $ProdiID, $TahunMasuk, $JenisBiayaID, ($save == 2 && !empty($ID)) ? $ID : null);

        if ($cek && $cek->ID) {
            \Log::warning('Duplicate check failed for setup_duedate_pembayaran', [
                'tahunId' => $TahunID,
                'programId' => $ProgramID,
                'prodiId' => $ProdiID,
                'tahunMasuk' => $TahunMasuk,
                'jenisBiayaId' => $JenisBiayaID,
                'found_id' => $cek->ID
            ]);
            return "gagal";
        }

        if ($save == 1) {
            $input['createdAt'] = date('Y-m-d H:i:s');

            try {
                $newID = $this->add($input);
                return $newID;
            } catch (\Exception $e) {
                \Log::error('SetupDuedatePembayaran save error: ' . $e->getMessage(), ['input' => $input]);
                return "gagal";
            }
        }

        if ($save == 2) {
            try {
                $this->edit($ID, $input);
                return $ID;
            } catch (\Exception $e) {
                \Log::error('SetupDuedatePembayaran edit error: ' . $e->getMessage(), ['id' => $ID, 'input' => $input]);
                return "gagal";
            }
        }

        return "gagal";
    }
}
