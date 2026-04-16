<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SetupMahasiswaDiskonSampaiLulusService
{
    public function get_data($limit, $offset, $keyword = '', $TahunID = '', $ProgramID = '', $ProdiID = '', $StatusAktif = '')
    {
        $query = DB::table('setup_mahasiswa_diskon_sampai_lulus')
            ->select(
                'setup_mahasiswa_diskon_sampai_lulus.*',
                'mahasiswa.NPM',
                'mahasiswa.Nama',
                'mahasiswa.ProdiID',
                'mahasiswa.ProgramID'
            )
            ->join('mahasiswa', 'setup_mahasiswa_diskon_sampai_lulus.MhswID', '=', 'mahasiswa.ID');

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('mahasiswa.Nama', 'like', "%{$keyword}%")
                  ->orWhere('mahasiswa.NPM', 'like', "%{$keyword}%");
            });
        }

        if ($TahunID) {
            $query->where(function($q) use ($TahunID) {
                $q->whereRaw("FIND_IN_SET(?, setup_mahasiswa_diskon_sampai_lulus.ListTahunID) != 0", [$TahunID])
                  ->orWhere('setup_mahasiswa_diskon_sampai_lulus.PerTahunID', '0');
            });
        }

        if ($ProgramID) {
            $query->where('mahasiswa.ProgramID', $ProgramID);
        }

        if ($ProdiID) {
            $query->where('mahasiswa.ProdiID', $ProdiID);
        }

        if ($StatusAktif !== null && $StatusAktif !== '') {
            $query->where('setup_mahasiswa_diskon_sampai_lulus.StatusAktif', $StatusAktif);
        }

        $query->orderBy('setup_mahasiswa_diskon_sampai_lulus.ID', 'ASC')
              ->orderBy('mahasiswa.ID', 'ASC');

        if ($limit !== null && $limit !== '') {
            $query->limit($limit);
        }

        if ($offset !== null && $offset !== '') {
            $query->offset($offset);
        }

        return $query->get();
    }

    public function count_all($keyword = '', $TahunID = '', $ProgramID = '', $ProdiID = '', $StatusAktif = '')
    {
        $query = DB::table('setup_mahasiswa_diskon_sampai_lulus')
            ->select('setup_mahasiswa_diskon_sampai_lulus.ID')
            ->join('mahasiswa', 'setup_mahasiswa_diskon_sampai_lulus.MhswID', '=', 'mahasiswa.ID');

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('mahasiswa.Nama', 'like', "%{$keyword}%")
                  ->orWhere('mahasiswa.NPM', 'like', "%{$keyword}%");
            });
        }

        if ($TahunID) {
            $query->where(function($q) use ($TahunID) {
                $q->whereRaw("FIND_IN_SET(?, setup_mahasiswa_diskon_sampai_lulus.ListTahunID) != 0", [$TahunID])
                  ->orWhere('setup_mahasiswa_diskon_sampai_lulus.PerTahunID', '0');
            });
        }

        if ($ProgramID) {
            $query->where('mahasiswa.ProgramID', $ProgramID);
        }

        if ($ProdiID) {
            $query->where('mahasiswa.ProdiID', $ProdiID);
        }

        if ($StatusAktif !== null && $StatusAktif !== '') {
            $query->where('setup_mahasiswa_diskon_sampai_lulus.StatusAktif', $StatusAktif);
        }

        $query->orderBy('setup_mahasiswa_diskon_sampai_lulus.ID', 'ASC')
              ->orderBy('mahasiswa.ID', 'ASC');

        return $query->count();
    }

    public function get_id($ID)
    {
        return DB::table('setup_mahasiswa_diskon_sampai_lulus')
            ->where('ID', $ID)
            ->first();
    }

    public function save($save, $inputData)
    {
        $arr_ex = [];
        foreach (($inputData['JenisBiayaID'] ?? []) as $key_jb => $jb) {
            $arr_ex_dt = [];
            $arr_ex_dt['JenisBiayaID'] = $jb;
            $arr_ex_dt['ListMasterDiskonID'] = $inputData['ListMasterDiskonID'][$key_jb] ?? [];
            $arr_ex[] = $arr_ex_dt;
        }

        $ListTahunID = $inputData['ListTahunID'] ?? [];
        $TahunID_imp = implode(',', $ListTahunID);
        $ListDiskon = json_encode($arr_ex);

        if (($inputData['PerTahunID'] ?? '') == '1' && empty($TahunID_imp)) {
            return "tahun_kosong";
        }

        $isPerMhs = $inputData['isPerMhs'] ?? '0';

        if ($isPerMhs == '1') {
            // Single student by NPM
            $get_mhs_id = DB::table('mahasiswa')->where('NPM', $inputData['npm'])->first();

            if (!$get_mhs_id) {
                return "gagal";
            }

            $cek = DB::table('setup_mahasiswa_diskon_sampai_lulus')
                ->where('MhswID', $get_mhs_id->ID)
                ->where('StatusAktif', 1)
                ->count();

            if ($cek == 0) {
                $insert = [
                    'PerTahunID' => $inputData['PerTahunID'] ?? '0',
                    'MhswID' => $get_mhs_id->ID,
                    'NPM' => $get_mhs_id->NPM,
                    'Nama' => $get_mhs_id->Nama,
                    'ListDiskon' => $ListDiskon,
                    'ListTahunID' => $TahunID_imp,
                    'StatusAktif' => '1',
                    'createdAt' => date('Y-m-d H:i:s')
                ];

                DB::table('setup_mahasiswa_diskon_sampai_lulus')->insert($insert);
                return "success";
            } else {
                return "gagal";
            }
        } else if ($isPerMhs == '0') {
            // Multiple students by checkID
            $checkIDs = $inputData['checkID'] ?? [];

            if (empty($checkIDs)) {
                return "gagal";
            }

            foreach ($checkIDs as $MhswID) {
                $get_mhs_id = DB::table('mahasiswa')
                    ->select('ID', 'NPM', 'Nama')
                    ->where('ID', $MhswID)
                    ->first();

                if (!$get_mhs_id) {
                    continue;
                }

                $cek = DB::table('setup_mahasiswa_diskon_sampai_lulus')
                    ->where('MhswID', $get_mhs_id->ID)
                    ->where('StatusAktif', 1)
                    ->count();

                if ($cek == 0) {
                    $insert2 = [
                        'PerTahunID' => $inputData['PerTahunID'] ?? '0',
                        'MhswID' => $get_mhs_id->ID,
                        'NPM' => $get_mhs_id->NPM,
                        'Nama' => $get_mhs_id->Nama,
                        'ListDiskon' => $ListDiskon,
                        'ListTahunID' => $TahunID_imp,
                        'StatusAktif' => '1',
                        'createdAt' => date('Y-m-d H:i:s')
                    ];

                    DB::table('setup_mahasiswa_diskon_sampai_lulus')->insert($insert2);
                }
            }

            return "success";
        }

        return "gagal";
    }

    public function save_alone($save, $inputData)
    {
        $ID = $inputData['ID'] ?? '';

        $arr_ex = [];
        foreach (($inputData['JenisBiayaID'] ?? []) as $key_jb => $jb) {
            $arr_ex_dt = [];
            $arr_ex_dt['JenisBiayaID'] = $jb;
            $arr_ex_dt['ListMasterDiskonID'] = $inputData['ListMasterDiskonID'][$key_jb] ?? [];
            $arr_ex[] = $arr_ex_dt;
        }

        $ListTahunID = $inputData['ListTahunID'] ?? [];
        $TahunID_imp = implode(',', $ListTahunID);
        $ListDiskon = json_encode($arr_ex);

        $get_mhs_id = DB::table('mahasiswa')->where('NPM', $inputData['npm'])->first();

        if (!$get_mhs_id) {
            return "gagal";
        }

        $cek = DB::table('setup_mahasiswa_diskon_sampai_lulus')
            ->where('MhswID', $get_mhs_id->ID)
            ->count();

        if ($cek > 0) {
            $update = [
                'PerTahunID' => $inputData['PerTahunID'] ?? '0',
                'ListDiskon' => $ListDiskon,
                'ListTahunID' => $TahunID_imp,
            ];

            DB::table('setup_mahasiswa_diskon_sampai_lulus')
                ->where('ID', $ID)
                ->update($update);

            return "success";
        }

        return "gagal";
    }
}
