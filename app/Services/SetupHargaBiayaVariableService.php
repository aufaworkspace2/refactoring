<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SetupHargaBiayaVariableService
{
    public function get_data($limit, $offset, $keyword = '', $ProgramID = '', $ProdiID = '', $TahunMasuk = '', $Jenis = '', $JenisPendaftaran = '')
    {
        $user = DB::table('user')->where('ID', Session::get('UserID'))->first();
        $arrProdi = $user->ProdiID ? explode(",", $user->ProdiID) : [];
        $LevelKode = Session::get('LevelKode');

        $query = DB::table('setup_harga_biaya_variable')
            ->select('setup_harga_biaya_variable.*')
            ->leftJoin('programstudi', 'programstudi.ID', '=', 'setup_harga_biaya_variable.ProdiID')
            ->leftJoin('program', 'program.ID', '=', 'setup_harga_biaya_variable.ProgramID');

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('programstudi.Nama', 'like', "%{$keyword}%")
                  ->orWhere('program.Nama', 'like', "%{$keyword}%")
                  ->orWhere('setup_harga_biaya_variable.TahunMasuk', 'like', "%{$keyword}%");
            });
        }

        if ($ProgramID !== null && $ProgramID !== "") {
            $query->where("setup_harga_biaya_variable.ProgramID", $ProgramID);
        }

        if ($ProdiID !== null && $ProdiID !== "") {
            $ProdiID_arr = explode(',', $ProdiID);
            $query->whereIn("setup_harga_biaya_variable.ProdiID", $ProdiID_arr);
        } else {
            if (!in_array('SPR', explode(',', $LevelKode ?? ''))) {
                $query->whereIn("setup_harga_biaya_variable.ProdiID", $arrProdi ?: [0]);
            }
        }

        if ($TahunMasuk !== null && $TahunMasuk !== "") {
            $query->where("setup_harga_biaya_variable.TahunMasuk", $TahunMasuk);
        }

        if ($Jenis) {
            $query->where("setup_harga_biaya_variable.Jenis", $Jenis);
        }

        if ($JenisPendaftaran !== null && $JenisPendaftaran !== "") {
            $query->where("setup_harga_biaya_variable.JenisPendaftaran", $JenisPendaftaran);
        }

        $query->groupBy("setup_harga_biaya_variable.ID")
              ->orderBy("setup_harga_biaya_variable.ProgramID", "ASC")
              ->orderBy("setup_harga_biaya_variable.ProdiID", "ASC")
              ->orderBy("setup_harga_biaya_variable.TahunMasuk", "ASC");

        if ($limit !== null && $limit !== '') {
            $query->limit($limit);
        }

        if ($offset !== null && $offset !== '') {
            $query->offset($offset);
        }

        return $query->get();
    }

    public function count_all($keyword = '', $ProgramID = '', $ProdiID = '', $TahunMasuk = '', $Jenis = '', $JenisPendaftaran = '')
    {
        $user = DB::table('user')->where('ID', Session::get('UserID'))->first();
        $arrProdi = $user->ProdiID ? explode(",", $user->ProdiID) : [];
        $LevelKode = Session::get('LevelKode');

        $query = DB::table('setup_harga_biaya_variable')
            ->select('setup_harga_biaya_variable.ID')
            ->leftJoin('programstudi', 'programstudi.ID', '=', 'setup_harga_biaya_variable.ProdiID')
            ->leftJoin('program', 'program.ID', '=', 'setup_harga_biaya_variable.ProgramID');

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('programstudi.Nama', 'like', "%{$keyword}%")
                  ->orWhere('program.Nama', 'like', "%{$keyword}%")
                  ->orWhere('setup_harga_biaya_variable.TahunMasuk', 'like', "%{$keyword}%");
            });
        }

        if ($ProgramID !== null && $ProgramID !== "") {
            $query->where("setup_harga_biaya_variable.ProgramID", $ProgramID);
        }

        if ($ProdiID !== null && $ProdiID !== "") {
            $ProdiID_arr = explode(',', $ProdiID);
            $query->whereIn("setup_harga_biaya_variable.ProdiID", $ProdiID_arr);
        } else {
            if (!in_array('SPR', explode(',', $LevelKode ?? ''))) {
                $query->whereIn("setup_harga_biaya_variable.ProdiID", $arrProdi ?: [0]);
            }
        }

        if ($TahunMasuk !== null && $TahunMasuk !== "") {
            $query->where("setup_harga_biaya_variable.TahunMasuk", $TahunMasuk);
        }

        if ($Jenis) {
            $query->where("setup_harga_biaya_variable.Jenis", $Jenis);
        }

        if ($JenisPendaftaran !== null && $JenisPendaftaran !== "") {
            $query->where("setup_harga_biaya_variable.JenisPendaftaran", $JenisPendaftaran);
        }

        $query->groupBy("setup_harga_biaya_variable.ID");

        return $query->count();
    }

    public function get_id($id)
    {
        return DB::table('setup_harga_biaya_variable')->where('ID', $id)->first();
    }

    public function add($data)
    {
        return DB::table('setup_harga_biaya_variable')->insertGetId($data);
    }

    public function edit($id, $data)
    {
        return DB::table('setup_harga_biaya_variable')->where('ID', $id)->update($data);
    }

    public function delete($id)
    {
        if (is_array($id)) {
            foreach ($id as $id_item) {
                DB::table('setup_harga_biaya_variable')->where('ID', $id_item)->delete();
            }
        } else {
            return DB::table('setup_harga_biaya_variable')->where('ID', $id)->delete();
        }
    }

    public function check_duplicate($jenis, $jenisPendaftaran, $programId, $prodiId, $tahunMasuk, $tanggalMulai, $tanggalSelesai, $excludeId = null)
    {
        $query = DB::table('setup_harga_biaya_variable')
            ->where('Jenis', $jenis)
            ->where('JenisPendaftaran', $jenisPendaftaran)
            ->where('ProgramID', $programId)
            ->where('ProdiID', $prodiId)
            ->where('TahunMasuk', $tahunMasuk);

        if ($jenis == 'Cuti') {
            $query->where(function($q) use ($tanggalMulai, $tanggalSelesai) {
                $q->whereRaw("('$tanggalMulai' between TanggalMulai and TanggalSelesai)")
                  ->orWhereRaw("('$tanggalSelesai' between TanggalMulai and TanggalSelesai)");
            });
        }

        if ($excludeId) {
            $query->where('ID', '!=', $excludeId);
        }

        return $query->first();
    }

    public function save($save, $inputData)
    {
        $ID = $inputData['ID'] ?? '';
        $Nominal = $inputData['Nominal'] ?? '';
        $NominalPaket = $inputData['NominalPaket'] ?? '';
        $NominalSkripsi = $inputData['NominalSkripsi'] ?? '';
        $HitungPraktek = $inputData['HitungPraktek'] ?? '0';
        $NominalPraktek = $inputData['NominalPraktek'] ?? '';
        $ProgramID = $inputData['ProgramID'] ?? '0';
        $ProdiID = $inputData['ProdiID'] ?? '0';
        $Jenis = $inputData['Jenis'] ?? '';
        $JenisPendaftaran = $inputData['JenisPendaftaran'] ?? '0';
        $TahunMasuk = $inputData['TahunMasuk'] ?? '0';
        $TanggalMulai = $inputData['TanggalMulai'] ?? '';
        $TanggalSelesai = $inputData['TanggalSelesai'] ?? '';

        // Clean currency values
        $Nominal = str_replace(['.', ','], '', $Nominal);
        $NominalPaket = str_replace(['.', ','], '', $NominalPaket);
        $NominalSkripsi = str_replace(['.', ','], '', $NominalSkripsi);
        $NominalPraktek = str_replace(['.', ','], '', $NominalPraktek);

        $input['Nominal'] = $Nominal;
        $input['NominalPaket'] = $NominalPaket ?: null;
        $input['NominalSkripsi'] = $NominalSkripsi ?: null;
        $input['ProgramID'] = $ProgramID;
        $input['ProdiID'] = $ProdiID;
        $input['TahunMasuk'] = $TahunMasuk;
        $input['Jenis'] = $Jenis;
        $input['JenisPendaftaran'] = $JenisPendaftaran;
        $input['TanggalMulai'] = $TanggalMulai;
        $input['TanggalSelesai'] = $TanggalSelesai;

        if ($Jenis == 'SKS') {
            $input['HitungPraktek'] = $HitungPraktek;
            $input['NominalPraktek'] = $NominalPraktek ?: 0;
        } else {
            $input['HitungPraktek'] = 0;
            $input['NominalPraktek'] = 0;
        }

        // Check duplicate (only for new records)
        $excludeId = ($save == 2 && !empty($ID)) ? $ID : null;
        $cek = $this->check_duplicate($Jenis, $JenisPendaftaran, $ProgramID, $ProdiID, $TahunMasuk, $TanggalMulai, $TanggalSelesai, $excludeId);

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
