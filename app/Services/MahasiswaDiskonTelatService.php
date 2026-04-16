<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class MahasiswaDiskonTelatService
{
    public function get_data($limit, $offset, $keyword, $TahunID, $ProgramID, $ProdiID, $StatusAktif)
    {
        $query = DB::table('mahasiswa_diskon_telat')
            ->join('mahasiswa', 'mahasiswa_diskon_telat.MhswID', '=', 'mahasiswa.ID')
            ->select(
                'mahasiswa_diskon_telat.*',
                'mahasiswa.NPM',
                'mahasiswa.Nama',
                'mahasiswa.ProgramID',
                'mahasiswa.ProdiID'
            );

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('mahasiswa.Nama', 'like', "%{$keyword}%")
                  ->orWhere('mahasiswa.NPM', 'like', "%{$keyword}%");
            });
        }

        if ($TahunID) {
            $query->where('mahasiswa_diskon_telat.TahunID', $TahunID);
        }

        if ($ProgramID) {
            $query->where('mahasiswa.ProgramID', $ProgramID);
        }

        if ($ProdiID) {
            $query->where('mahasiswa.ProdiID', $ProdiID);
        }

        if ($StatusAktif !== null && $StatusAktif !== '') {
            $query->where('mahasiswa_diskon_telat.StatusAktif', $StatusAktif);
        }

        $query->where('mahasiswa_diskon_telat.jenis_mhsw', 'mhsw');
        $query->orderBy('mahasiswa_diskon_telat.ID', 'DESC');

        if ($limit) {
            return $query->skip($offset)->take($limit)->get();
        }

        return $query->get();
    }

    public function count_all($keyword, $TahunID, $ProgramID, $ProdiID, $StatusAktif)
    {
        $query = DB::table('mahasiswa_diskon_telat')
            ->join('mahasiswa', 'mahasiswa_diskon_telat.MhswID', '=', 'mahasiswa.ID');

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('mahasiswa.Nama', 'like', "%{$keyword}%")
                  ->orWhere('mahasiswa.NPM', 'like', "%{$keyword}%");
            });
        }

        if ($TahunID) {
            $query->where('mahasiswa_diskon_telat.TahunID', $TahunID);
        }

        if ($ProgramID) {
            $query->where('mahasiswa.ProgramID', $ProgramID);
        }

        if ($ProdiID) {
            $query->where('mahasiswa.ProdiID', $ProdiID);
        }

        if ($StatusAktif !== null && $StatusAktif !== '') {
            $query->where('mahasiswa_diskon_telat.StatusAktif', $StatusAktif);
        }

        $query->where('mahasiswa_diskon_telat.jenis_mhsw', 'mhsw');

        return $query->count();
    }

    public function get_id($id)
    {
        return DB::table('mahasiswa_diskon_telat')->where('ID', $id)->first();
    }

    public function filter_students($data)
    {
        $TahunID = $data['TahunID'] ?? '';
        $ProdiID = $data['ProdiID'] ?? '';
        $ProgramID = $data['ProgramID'] ?? '';
        $KelasID = $data['KelasID'] ?? '';
        $TahunMasuk = $data['TahunMasuk'] ?? [];
        $keyword = $data['keyword'] ?? '';

        $TahunMasuk_imp = implode(',', $TahunMasuk);

        $query = DB::table('mahasiswa')
            ->selectRaw('mahasiswa.ID, mahasiswa.Nama, mahasiswa.NPM, mahasiswa.ProdiID, mahasiswa.TahunMasuk,
                mahasiswa.ProgramID, group_concat(tagihan_mahasiswa.ID) as listTagihan,
                sum(tagihan_mahasiswa.TotalTagihan) as JumlahTagihan,
                sum(tagihan_mahasiswa.JumlahDiskon) as JumlahDiskon,
                sum(tagihan_mahasiswa.TotalCicilan) as JumlahBayar')
            ->join('tagihan_mahasiswa', function($join) use ($TahunID) {
                $join->on('tagihan_mahasiswa.MhswID', '=', 'mahasiswa.ID')
                     ->where('tagihan_mahasiswa.Periode', $TahunID);
            })
            ->where('tagihan_mahasiswa.JenisBiayaID', '!=', 32);

        if ($ProdiID) $query->where('mahasiswa.ProdiID', $ProdiID);
        if ($ProgramID) $query->where('mahasiswa.ProgramID', $ProgramID);
        if ($KelasID) $query->where('mahasiswa.KelasID', $KelasID);
        if (!empty($TahunMasuk)) $query->whereIn('mahasiswa.TahunMasuk', $TahunMasuk);
        if ($keyword) $query->whereRaw("(mahasiswa.NPM LIKE ? OR mahasiswa.Nama LIKE ?)", ["%{$keyword}%", "%{$keyword}%"]);

        $query->where('mahasiswa.jenis_mhsw', 'mhsw');
        $query->groupBy('mahasiswa.ID');

        $get_mhs = $query->get();

        // Get cicilan info for each student
        foreach ($get_mhs as $row_mhs) {
            $exp_tagihan = array_filter(explode(",", $row_mhs->listTagihan));
            $listCicilan = [];
            
            if (!empty($exp_tagihan)) {
                $cek_cicilan = DB::table('cicilan_tagihan_mahasiswa')
                    ->whereIn('TagihanMahasiswaID', $exp_tagihan)
                    ->get();
                
                foreach ($cek_cicilan as $row_cicilan) {
                    if (!isset($listCicilan[$row_cicilan->JenisBiayaID])) {
                        $listCicilan[$row_cicilan->JenisBiayaID] = 0;
                    }
                    $listCicilan[$row_cicilan->JenisBiayaID] += $row_cicilan->Jumlah;
                }
            }
            $row_mhs->listCicilan = $listCicilan;
        }

        $MhswID_arr = $get_mhs->pluck('ID')->toArray();
        $query_jenisbiaya = [];

        if (count($MhswID_arr) > 0) {
            $query_get_jenisbiaya = DB::table('jenisbiaya')
                ->join('tagihan_mahasiswa', 'tagihan_mahasiswa.JenisBiayaID', '=', 'jenisbiaya.ID')
                ->where('tagihan_mahasiswa.Periode', $TahunID)
                ->where('jenisbiaya.ID', '!=', 32)
                ->whereIn('tagihan_mahasiswa.MhswID', $MhswID_arr)
                ->groupBy('jenisbiaya.ID')
                ->select('jenisbiaya.*')
                ->get();
            
            foreach ($query_get_jenisbiaya as $row_jb) {
                $query_jenisbiaya[$row_jb->ID] = $row_jb;
            }
        }

        $diskon = DB::table('master_diskon')
            ->whereRaw("(ProdiID='" . $ProdiID . "' OR ProdiID='' OR ProdiID is null OR ProdiID=0)")
            ->get();

        return [
            'get_mhs' => $get_mhs,
            'query_jenisbiaya' => $query_jenisbiaya,
            'diskon' => $diskon,
            'tahun' => DB::table('tahun')->where('ID', $TahunID)->first()
        ];
    }

    public function save($save, $data)
    {
        $TahunID = $data['TahunID'] ?? '';
        $checkID = $data['checkID'] ?? [];
        
        // Convert JenisBiayaID dari string "1,2,3" ke array [1, 2, 3]
        $JenisBiayaID_raw = $data['JenisBiayaID'] ?? '';
        $JenisBiayaID = is_array($JenisBiayaID_raw) ? $JenisBiayaID_raw : array_filter(array_map('trim', explode(',', $JenisBiayaID_raw)));
        
        $DiscountID = $data['DiscountID'] ?? [];
        $UserID = Session::get('UserID');

        $arr_jenisbiaya = [];
        foreach (DB::table('jenisbiaya')->get() as $row) {
            $arr_jenisbiaya[$row->ID] = $row;
        }

        $arr_master_diskon = [];
        foreach (DB::table('master_diskon')->get() as $row) {
            $arr_master_diskon[$row->ID] = $row;
        }

        foreach ($checkID as $MhswID) {
            $get_mhs_id = DB::table('mahasiswa')->where('ID', $MhswID)->first();

            $Discount_arr = [];
            $insert_details = [];

            foreach ($JenisBiayaID as $jb) {
                $diskon_jb = $DiscountID[$jb] ?? [];

                if (count($diskon_jb) > 0) {
                    foreach ($diskon_jb as $disc) {
                        $Discount_arr[$disc] = $disc;
                    }

                    $cek_tagihan = DB::table('tagihan_mahasiswa')
                        ->where('MhswID', $get_mhs_id->ID)
                        ->where('JenisBiayaID', $jb)
                        ->where('Periode', $TahunID)
                        ->first();

                    if ($cek_tagihan) {
                        if ($cek_tagihan->MasterDiskonID) {
                            $exp_d = explode(",", $cek_tagihan->MasterDiskonID);
                            foreach ($exp_d as $row_exp_d) {
                                if ($row_exp_d) {
                                    $diskon_jb[] = $row_exp_d;
                                }
                            }
                        }
                        $diskon_jb = array_unique($diskon_jb);

                        $tagihan = $cek_tagihan->TotalTagihan;
                        $jumlah_sisa = $cek_tagihan->Sisa;
                        $diskon = 0;
                        $jumlah_sisaTemp = $jumlah_sisa;

                        foreach ($diskon_jb as $djb) {
                            if (isset($arr_master_diskon[$djb])) {
                                $jumlah_diskon = $arr_master_diskon[$djb]->Jumlah;
                                $tipe_diskon = $arr_master_diskon[$djb]->Tipe;
                                $jenis_diskon = $arr_master_diskon[$djb]->JenisDiskon;

                                if ($tipe_diskon == 'persen') {
                                    if ($jenis_diskon == 'potong_dari_sisa') {
                                        $tempPersen = $jumlah_sisa * $jumlah_diskon / 100;
                                    } else {
                                        $tempPersen = $tagihan * $jumlah_diskon / 100;
                                    }
                                    $tempPersen = intval($tempPersen);
                                    $jumlah_sisaTemp -= $tempPersen;
                                    $diskon += $tempPersen;

                                    if ($diskon > $tagihan) {
                                        $diskon = $tagihan;
                                    }
                                } else if ($tipe_diskon == 'nominal') {
                                    $diskon += $jumlah_diskon;

                                    if ($diskon > $cek_tagihan->TotalTagihan) {
                                        $diskon = $cek_tagihan->TotalTagihan;
                                    }
                                }
                            }
                        }

                        $insert_detail = [
                            'TagihanMahasiswaID' => $cek_tagihan->ID,
                            'JenisBiayaID' => $cek_tagihan->JenisBiayaID,
                            'NamaJenisBiaya' => $arr_jenisbiaya[$cek_tagihan->JenisBiayaID]->Nama ?? '',
                            'MasterDiskonID_list' => implode(",", $diskon_jb),
                            'JumlahTagihan' => $cek_tagihan->TotalTagihan,
                            'JumlahDiskon' => $diskon,
                            'Jumlah' => $cek_tagihan->TotalTagihan - $diskon,
                            'createdAt' => date('Y-m-d H:i:s'),
                            'UserID' => $UserID
                        ];
                        $insert_details[] = $insert_detail;
                    }
                }
            }

            $Discount_arr = array_unique($Discount_arr);
            $DiscountID_imp = implode(",", $Discount_arr);
            $jb_imp = implode(",", $JenisBiayaID);

            $insert = [
                'TahunID' => $TahunID,
                'MhswID' => $get_mhs_id->ID,
                'NPM' => $get_mhs_id->NPM,
                'Nama' => $get_mhs_id->Nama,
                'ListMasterDiskonID' => $DiscountID_imp,
                'ListJenisBiayaID' => $jb_imp,
                'jenis_mhsw' => 'mhsw',
                'createdAt' => date('Y-m-d H:i:s'),
                'UserID' => $UserID,
                'LastUpdateUserID' => $UserID
            ];

            $run = DB::table('mahasiswa_diskon_telat')->insert($insert);

            if ($run) {
                $MhswDiskonTelatID = DB::getPdo()->lastInsertId();
                foreach ($insert_details as $row_insert_detail) {
                    $row_insert_detail['MhswDiskonTelatID'] = $MhswDiskonTelatID;
                    DB::table('mahasiswa_diskon_telat_detail')->insert($row_insert_detail);
                }
                $this->set_diskon($MhswDiskonTelatID);
            }
        }
    }

    public function set_diskon($MhswDiskonTelatID)
    {
        // Call helper function diskon_mahasiswa_telat
        if (function_exists('diskon_mahasiswa_telat')) {
            $mahasiswa_diskon_telat = DB::table('mahasiswa_diskon_telat')->where('ID', $MhswDiskonTelatID)->first();
            if (!$mahasiswa_diskon_telat) return;

            $detail_diskon = DB::table('mahasiswa_diskon_telat_detail')
                ->where('MhswDiskonTelatID', $MhswDiskonTelatID)
                ->get();

            diskon_mahasiswa_telat($detail_diskon, $mahasiswa_diskon_telat->MhswID, 'insert');
        }
    }

    public function unset_diskon($MhswDiskonTelatID)
    {
        // Call helper function diskon_mahasiswa_telat
        if (function_exists('diskon_mahasiswa_telat')) {
            $mahasiswa_diskon_telat = DB::table('mahasiswa_diskon_telat')->where('ID', $MhswDiskonTelatID)->first();
            if (!$mahasiswa_diskon_telat) return;

            $detail_diskon = DB::table('mahasiswa_diskon_telat_detail')
                ->where('MhswDiskonTelatID', $MhswDiskonTelatID)
                ->get();

            diskon_mahasiswa_telat($detail_diskon, $mahasiswa_diskon_telat->MhswID, 'hapus');
        }
    }

    public function soft_delete($id)
    {
        DB::table('mahasiswa_diskon_telat')
            ->where('ID', $id)
            ->update(['StatusAktif' => '0']);
    }

    public function update($id, $data)
    {
        // Similar to save but updates existing record
        $mahasiswa_diskon_telat = DB::table('mahasiswa_diskon_telat')->where('ID', $id)->first();
        if (!$mahasiswa_diskon_telat) return;

        // Delete old details
        DB::table('mahasiswa_diskon_telat_detail')->where('MhswDiskonTelatID', $id)->delete();

        // Re-save with new data
        $this->save(2, $data);
    }

    public function aktifkan($id)
    {
        DB::table('mahasiswa_diskon_telat')
            ->where('ID', $id)
            ->update(['StatusAktif' => '1']);
    }
}
