<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SetupPersentaseBayarService
{
    public function get_data($limit, $offset, $keyword = '', $ProgramID = '', $ProdiID = '', $TahunMasuk = '', $SemesterMasuk = '')
    {
        $user = DB::table('user')->where('ID', Session::get('UserID'))->first();
        $arrProdi = $user->ProdiID ? explode(",", $user->ProdiID) : null;
        $LevelKode = Session::get('LevelKode');

        $query = DB::table('setup_persentase_bayar');

        if ($keyword) {
            $query->where('Nama', 'like', "%{$keyword}%");
        }

        if ($ProgramID !== null && $ProgramID !== "") {
            $query->where("ProgramID", $ProgramID);
        }

        if (!empty($ProdiID)) {
            $ProdiID_arr = explode(',', $ProdiID);
            $query->where(function($q) use ($ProdiID_arr) {
                $q->whereIn('ProdiID', $ProdiID_arr)
                  ->orWhere('ProdiID', 0);
            });
        } else {
            if (!in_array('SPR', explode(',', $LevelKode ?? ''))) {
                $query->where(function($q) use ($arrProdi) {
                    $q->whereIn('ProdiID', $arrProdi ?? [0])
                      ->orWhere('ProdiID', 0);
                });
            }
        }

        if ($TahunMasuk !== null && $TahunMasuk !== "") {
            $query->where("TahunMasuk", $TahunMasuk);
        }

        if ($SemesterMasuk !== null && $SemesterMasuk !== "") {
            $query->where("SemesterMasuk", $SemesterMasuk);
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

    public function count_all($keyword = '', $ProgramID = '', $ProdiID = '', $TahunMasuk = '', $SemesterMasuk = '')
    {
        $user = DB::table('user')->where('ID', Session::get('UserID'))->first();
        $arrProdi = $user->ProdiID ? explode(",", $user->ProdiID) : null;
        $LevelKode = Session::get('LevelKode');

        $query = DB::table('setup_persentase_bayar');

        if ($keyword) {
            $query->where('Nama', 'like', "%{$keyword}%");
        }

        if ($ProgramID !== null && $ProgramID !== "") {
            $query->where("ProgramID", $ProgramID);
        }

        if (!empty($ProdiID)) {
            $ProdiID_arr = explode(',', $ProdiID);
            $query->where(function($q) use ($ProdiID_arr) {
                $q->whereIn('ProdiID', $ProdiID_arr)
                  ->orWhere('ProdiID', 0);
            });
        } else {
            if (!in_array('SPR', explode(',', $LevelKode ?? ''))) {
                $query->where(function($q) use ($arrProdi) {
                    $q->whereIn('ProdiID', $arrProdi ?? [0])
                      ->orWhere('ProdiID', 0);
                });
            }
        }

        if ($TahunMasuk !== null && $TahunMasuk !== "") {
            $query->where("TahunMasuk", $TahunMasuk);
        }

        if ($SemesterMasuk !== null && $SemesterMasuk !== "") {
            $query->where("SemesterMasuk", $SemesterMasuk);
        }

        return $query->count();
    }

    public function get_id($id)
    {
        return DB::table('setup_persentase_bayar')->where('ID', $id)->first();
    }

    public function add($data)
    {
        return DB::table('setup_persentase_bayar')->insertGetId($data);
    }

    public function edit($id, $data)
    {
        return DB::table('setup_persentase_bayar')->where('ID', $id)->update($data);
    }

    public function delete($id)
    {
        if (is_array($id)) {
            foreach ($id as $id_item) {
                DB::table('setup_persentase_bayar')->where('ID', $id_item)->delete();
            }
        } else {
            return DB::table('setup_persentase_bayar')->where('ID', $id)->delete();
        }
    }

    public function check_duplicate($nama, $programId, $prodiId, $tahunMasuk, $semesterMasuk, $excludeId = null)
    {
        $query = DB::table('setup_persentase_bayar')
            ->where('Nama', $nama)
            ->where('ProgramID', $programId)
            ->where('ProdiID', $prodiId)
            ->where('TahunMasuk', $tahunMasuk)
            ->where('SemesterMasuk', $semesterMasuk);

        if ($excludeId && $excludeId != '') {
            $query->where('ID', '!=', $excludeId);
        }

        return $query->first();
    }

    public function get_tahun_angkatan()
    {
        $tahun_masuk_list = DB::table('mahasiswa')
            ->select('TahunMasuk')
            ->where('TahunMasuk', '!=', '')
            ->groupBy('TahunMasuk')
            ->get();

        $arr_tahun_angkatan = [];
        foreach ($tahun_masuk_list as $arr) {
            $arr_tahun_angkatan[$arr->TahunMasuk] = $arr->TahunMasuk;
        }

        if (empty($arr_tahun_angkatan)) {
            $arr_tahun_angkatan[date('Y')] = date('Y');
        }

        $angkatan_terakhir = max($arr_tahun_angkatan);
        $angkatan_terakhir_plus = $angkatan_terakhir + 2;

        for ($i = $angkatan_terakhir; $i <= $angkatan_terakhir_plus; $i++) {
            $arr_tahun_angkatan[$i] = $i;
        }

        rsort($arr_tahun_angkatan);

        return $arr_tahun_angkatan;
    }

    public function save($save, $inputData)
    {
        $ID = $inputData['ID'] ?? '';
        $Nama = $inputData['Nama'] ?? '';
        $Persen = $inputData['Persen'] ?? '';
        $ProgramID = $inputData['ProgramID'] ?? '0';
        $ProdiID = $inputData['ProdiID'] ?? '0';
        $TahunMasuk = $inputData['TahunMasuk'] ?? '0';
        $SemesterMasuk = $inputData['SemesterMasuk'] ?? '0';
        $Tipe = $inputData['Tipe'] ?? 'persen';
        $JenisBiayaID_list = isset($inputData['JenisBiayaID_list']) && is_array($inputData['JenisBiayaID_list']) ? implode(",", $inputData['JenisBiayaID_list']) : '';

        $input['Nama'] = $Nama;
        $input['Persen'] = $Persen;
        $input['ProgramID'] = $ProgramID;
        $input['ProdiID'] = $ProdiID;
        $input['TahunMasuk'] = $TahunMasuk;
        $input['SemesterMasuk'] = $SemesterMasuk;
        $input['Tipe'] = $Tipe;
        $input['JenisBiayaID_list'] = $JenisBiayaID_list;

        // Check duplicate - only exclude current ID when editing
        $excludeId = ($save == 2 && !empty($ID)) ? $ID : null;
        $cek = $this->check_duplicate($Nama, $ProgramID, $ProdiID, $TahunMasuk, $SemesterMasuk, $excludeId);

        if ($cek && $cek->ID) {
            \Log::warning('Duplicate check failed for setup_persentase_bayar', [
                'nama' => $Nama,
                'programId' => $ProgramID,
                'prodiId' => $ProdiID,
                'tahunMasuk' => $TahunMasuk,
                'semesterMasuk' => $SemesterMasuk,
                'excludeId' => $excludeId,
                'found_id' => $cek->ID
            ]);
            return "gagal";
        }

        if ($save == 1) {
            $input['createdAt'] = date('Y-m-d H:i:s');
            $input['UserID'] = Session::get('UserID');

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
