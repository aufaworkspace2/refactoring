<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class MahasiswaDiskonService
{
    public function get_data($limit, $offset, $keyword, $TahunID, $ProgramID, $ProdiID, $StatusAktif)
    {
        $query = DB::table('mahasiswa_diskon')
            ->join('mahasiswa', 'mahasiswa_diskon.MhswID', '=', 'mahasiswa.ID')
            ->select(
                'mahasiswa_diskon.*',
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
            $query->where('mahasiswa_diskon.TahunID', $TahunID);
        }

        if ($ProgramID) {
            $query->where('mahasiswa.ProgramID', $ProgramID);
        }

        if ($ProdiID) {
            $query->where('mahasiswa.ProdiID', $ProdiID);
        }

        if ($StatusAktif !== null && $StatusAktif !== '') {
            $query->where('mahasiswa_diskon.StatusAktif', $StatusAktif);
        }

        // IMPORTANT: Filter jenis_mhsw = 'mhsw' sesuai model CI
        $query->where('mahasiswa_diskon.jenis_mhsw', 'mhsw');

        $query->orderBy('mahasiswa_diskon.ID', 'DESC');

        if ($limit) {
            return $query->skip($offset)->take($limit)->get();
        }

        return $query->get();
    }

    public function count_all($keyword, $TahunID, $ProgramID, $ProdiID, $StatusAktif)
    {
        $query = DB::table('mahasiswa_diskon')
            ->join('mahasiswa', 'mahasiswa_diskon.MhswID', '=', 'mahasiswa.ID');

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('mahasiswa.Nama', 'like', "%{$keyword}%")
                  ->orWhere('mahasiswa.NPM', 'like', "%{$keyword}%");
            });
        }

        if ($TahunID) {
            $query->where('mahasiswa_diskon.TahunID', $TahunID);
        }

        if ($ProgramID) {
            $query->where('mahasiswa.ProgramID', $ProgramID);
        }

        if ($ProdiID) {
            $query->where('mahasiswa.ProdiID', $ProdiID);
        }

        if ($StatusAktif !== null && $StatusAktif !== '') {
            $query->where('mahasiswa_diskon.StatusAktif', $StatusAktif);
        }

        // IMPORTANT: Filter jenis_mhsw = 'mhsw' sesuai model CI
        $query->where('mahasiswa_diskon.jenis_mhsw', 'mhsw');

        return $query->count();
    }

    public function get_id($id)
    {
        return DB::table('mahasiswa_diskon')->where('ID', $id)->first();
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
            ->selectRaw('mahasiswa.*, sum(draft_tagihan_mahasiswa.TotalTagihan) as JumlahTagihan, sum(draft_tagihan_mahasiswa.JumlahDiskon) as JumlahDiskon')
            ->join('draft_tagihan_mahasiswa', function($join) use ($TahunID) {
                $join->on('draft_tagihan_mahasiswa.MhswID', '=', 'mahasiswa.ID')
                     ->where('draft_tagihan_mahasiswa.Periode', $TahunID)
                     ->where('draft_tagihan_mahasiswa.StatusPosting', 0);
            });

        if ($ProdiID) $query->where('mahasiswa.ProdiID', $ProdiID);
        if ($ProgramID) $query->where('mahasiswa.ProgramID', $ProgramID);
        if ($KelasID) $query->where('mahasiswa.KelasID', $KelasID);
        if (!empty($TahunMasuk)) $query->whereIn('mahasiswa.TahunMasuk', $TahunMasuk);
        if ($keyword) $query->whereRaw("(mahasiswa.NPM LIKE ? OR mahasiswa.Nama LIKE ?)", ["%{$keyword}%", "%{$keyword}%"]);

        $query->where('mahasiswa.jenis_mhsw', 'mhsw');
        $query->groupBy('mahasiswa.ID');

        $get_mhs = $query->get();

        $MhswID_arr = $get_mhs->pluck('ID')->toArray();
        $query_jenisbiaya = [];

        if (count($MhswID_arr) > 0) {
            $query_jenisbiaya = DB::table('jenisbiaya')
                ->join('draft_tagihan_mahasiswa', 'draft_tagihan_mahasiswa.JenisBiayaID', '=', 'jenisbiaya.ID')
                ->where('draft_tagihan_mahasiswa.Periode', $TahunID)
                ->whereIn('draft_tagihan_mahasiswa.MhswID', $MhswID_arr)
                ->groupBy('jenisbiaya.ID')
                ->select('jenisbiaya.*')
                ->get();
        }

        $diskon = DB::table('master_diskon')
            ->whereRaw("(ProdiID='" . $ProdiID . "' OR ProdiID='' OR ProdiID is null OR ProdiID=0)")
            ->orderByRaw("FIELD(JenisDiskon, 'potong_dari_total', 'potong_dari_sisa')")
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
            $arr_jb_imp = [];

            foreach ($JenisBiayaID as $jb) {
                $diskon_jb = $DiscountID[$jb] ?? [];

                if (count($diskon_jb) > 0) {
                    foreach ($diskon_jb as $disc) {
                        $Discount_arr[$disc] = $disc;
                    }

                    $cek_draft_tagihan = DB::table('draft_tagihan_mahasiswa')
                        ->where('MhswID', $get_mhs_id->ID)
                        ->where('JenisBiayaID', $jb)
                        ->where('Periode', $TahunID)
                        ->first();

                    if ($cek_draft_tagihan) {
                        if ($cek_draft_tagihan->MasterDiskonID) {
                            $exp_d = explode(",", $cek_draft_tagihan->MasterDiskonID);
                            foreach ($exp_d as $row_exp_d) {
                                if ($row_exp_d) {
                                    $diskon_jb[] = $row_exp_d;
                                }
                            }
                        }
                        $diskon_jb = array_unique($diskon_jb);

                        $tagihan = $cek_draft_tagihan->TotalTagihan;
                        $jumlah_sisa = $cek_draft_tagihan->Jumlah;
                        $diskon = 0;
                        $jumlah_sisaTemp = $jumlah_sisa;
                        $tagihanTemp = $tagihan;

                        foreach ($diskon_jb as $djb) {
                            if (isset($arr_master_diskon[$djb])) {
                                $jumlah_diskon = $arr_master_diskon[$djb]->Jumlah;
                                $tipe_diskon = $arr_master_diskon[$djb]->Tipe;
                                $jenis_diskon = $arr_master_diskon[$djb]->JenisDiskon;

                                if ($tipe_diskon == 'persen') {
                                    if ($jenis_diskon == 'potong_dari_sisa') {
                                        $tempPersen = $jumlah_sisaTemp * $jumlah_diskon / 100;
                                    } else {
                                        $tempPersen = $tagihan * $jumlah_diskon / 100;
                                    }
                                    $tempPersen = intval($tempPersen);
                                    $tagihanTemp -= $tempPersen;
                                    $jumlah_sisaTemp -= $tempPersen;
                                    $diskon += $tempPersen;
                                } else if ($tipe_diskon == 'nominal') {
                                    $tagihanTemp -= $jumlah_diskon;
                                    $jumlah_sisaTemp -= $jumlah_diskon;
                                    $diskon += $jumlah_diskon;
                                    if ($diskon > $jumlah_sisa) {
                                        $diskon = $jumlah_sisa;
                                    }
                                }
                            }
                        }

                        $insert_detail = [
                            'DraftTagihanMahasiswaID' => $cek_draft_tagihan->ID,
                            'JenisBiayaID' => $cek_draft_tagihan->JenisBiayaID,
                            'NamaJenisBiaya' => $arr_jenisbiaya[$cek_draft_tagihan->JenisBiayaID]->Nama ?? '',
                            'MasterDiskonID_list' => implode(",", $diskon_jb),
                            'JumlahTagihan' => $jumlah_sisa,
                            'JumlahDiskon' => $diskon,
                            'Jumlah' => $jumlah_sisa - $diskon,
                            'createdAt' => date('Y-m-d H:i:s'),
                            'UserID' => $UserID
                        ];
                        $insert_details[] = $insert_detail;
                        $arr_jb_imp[] = $cek_draft_tagihan->JenisBiayaID;
                    }
                }
            }

            $Discount_arr = array_unique($Discount_arr);
            $DiscountID_imp = implode(",", $Discount_arr);
            $jb_imp = implode(",", array_unique($arr_jb_imp));

            $insert = [
                'TahunID' => $TahunID,
                'MhswID' => $get_mhs_id->ID,
                'NPM' => $get_mhs_id->NPM,
                'Nama' => $get_mhs_id->Nama,
                'ListMasterDiskonID' => $DiscountID_imp,
                'ListJenisBiayaID' => $jb_imp,
                'createdAt' => date('Y-m-d H:i:s'),
                'UserID' => $UserID,
                'LastUpdateUserID' => $UserID
            ];

            $run = DB::table('mahasiswa_diskon')->insert($insert);

            if ($run) {
                $MhswDiskonID = DB::getPdo()->lastInsertId();
                foreach ($insert_details as $row_insert_detail) {
                    $row_insert_detail['MhswDiskonID'] = $MhswDiskonID;
                    DB::table('mahasiswa_diskon_detail')->insert($row_insert_detail);
                }
                $this->set_diskon($MhswDiskonID);
            }
        }
    }

    public function set_diskon($MhswDiskonID)
    {
        $mahasiswa_diskon = DB::table('mahasiswa_diskon')->where('ID', $MhswDiskonID)->first();
        if (!$mahasiswa_diskon) return;

        $MhswID = $mahasiswa_diskon->MhswID;
        $detail_diskon = DB::table('mahasiswa_diskon_detail')->where('MhswDiskonID', $MhswDiskonID)->get();

        $arr_master_diskon = [];
        foreach (DB::table('master_diskon')->get() as $row) {
            $arr_master_diskon[$row->ID] = $row;
        }

        $list_id_semester = [];
        $diskon_per_id_semester = [];

        foreach ($detail_diskon as $row_detail_diskon) {
            $row_draft = DB::table('draft_tagihan_mahasiswa')->where('ID', $row_detail_diskon->DraftTagihanMahasiswaID)->first();
            if (!$row_draft) continue;

            $tagihan = $row_detail_diskon->JumlahTagihan;
            $diskon_jb = explode(",", $row_detail_diskon->MasterDiskonID_list);
            
            if ($row_draft->MasterDiskonID) {
                $diskon_lama = explode(",", $row_draft->MasterDiskonID);
                $diskon_jb = array_merge($diskon_lama, $diskon_jb);
            }
            $diskon_jb = array_unique($diskon_jb);

            $jumlah_sisa = $row_draft->Jumlah;
            $diskon = 0;
            $jumlah_sisaTemp = $jumlah_sisa;
            $tagihanTemp = $tagihan;

            foreach ($diskon_jb as $djb) {
                if (isset($arr_master_diskon[$djb])) {
                    $jumlah_diskon = $arr_master_diskon[$djb]->Jumlah;
                    $tipe_diskon = $arr_master_diskon[$djb]->Tipe;
                    $jenis_diskon = $arr_master_diskon[$djb]->JenisDiskon;

                    if ($tipe_diskon == 'persen') {
                        if ($jenis_diskon == 'potong_dari_sisa') {
                            $tempPersen = $jumlah_sisaTemp * $jumlah_diskon / 100;
                        } else {
                            $tempPersen = $tagihan * $jumlah_diskon / 100;
                        }
                        $tempPersen = intval($tempPersen);
                        $tagihanTemp -= $tempPersen;
                        $jumlah_sisaTemp -= $tempPersen;
                        $diskon += $tempPersen;
                        if ($diskon > $tagihan) $diskon = $tagihan;
                    } else if ($tipe_diskon == 'nominal') {
                        $tagihanTemp -= $jumlah_diskon;
                        $jumlah_sisaTemp -= $jumlah_diskon;
                        $diskon += $jumlah_diskon;
                        if ($diskon > $tagihan) $diskon = $tagihan;
                    }
                }
            }

            DB::table('draft_tagihan_mahasiswa')
                ->where('ID', $row_draft->ID)
                ->update([
                    'MasterDiskonID' => implode(",", $diskon_jb),
                    'TotalTagihan' => $tagihan,
                    'JumlahDiskon' => $diskon,
                    'Jumlah' => $tagihan - $diskon
                ]);

            $id_semester = $row_draft->DraftTagihanMahasiswaSemesterID;
            $list_id_semester[] = $id_semester;
            $diskon_per_id_semester[$id_semester] = ($diskon_per_id_semester[$id_semester] ?? 0) + $diskon;

            // Update detail draft tagihan
            $query_draft_detail = DB::table('draft_tagihan_mahasiswa_detail')
                ->where('DraftTagihanMahasiswaID', $row_draft->ID)->get();
            
            foreach ($query_draft_detail as $row_draft_detail) {
                $tagihan_detail = $row_draft_detail->TotalTagihan;
                $jumlah_sisa_detail = $row_draft_detail->Jumlah;
                $diskon_detail = 0;
                $jumlah_sisaTemp_d = $jumlah_sisa_detail;
                $tagihanTemp_d = $tagihan_detail;

                foreach ($diskon_jb as $djb) {
                    if (isset($arr_master_diskon[$djb])) {
                        $jumlah_diskon = $arr_master_diskon[$djb]->Jumlah;
                        $tipe_diskon = $arr_master_diskon[$djb]->Tipe;
                        $jenis_diskon = $arr_master_diskon[$djb]->JenisDiskon;

                        if ($tipe_diskon == 'persen') {
                            if ($jenis_diskon == 'potong_dari_sisa') {
                                $tempPersen = $jumlah_sisaTemp_d * $jumlah_diskon / 100;
                            } else {
                                $tempPersen = $tagihan_detail * $jumlah_diskon / 100;
                            }
                            $tempPersen = intval($tempPersen);
                            $tagihanTemp_d -= $tempPersen;
                            $jumlah_sisaTemp_d -= $tempPersen;
                            $diskon_detail += $tempPersen;
                            if ($diskon_detail > $tagihan_detail) $diskon_detail = $tagihan_detail;
                        } else if ($tipe_diskon == 'nominal') {
                            $tagihanTemp_d -= $jumlah_diskon;
                            $jumlah_sisaTemp_d -= $jumlah_diskon;
                            $diskon_detail += $jumlah_diskon;
                            if ($diskon_detail > $tagihan_detail) $diskon_detail = $tagihan_detail;
                        }
                    }
                }

                DB::table('draft_tagihan_mahasiswa_detail')
                    ->where('ID', $row_draft_detail->ID)
                    ->update([
                        'TotalTagihan' => $tagihan_detail,
                        'JumlahDiskon' => $diskon_detail,
                        'Jumlah' => $tagihan_detail - $diskon_detail
                    ]);
            }

            // Update termin draft tagihan
            $query_draft_termin = DB::table('draft_tagihan_mahasiswa_termin')
                ->where('DraftTagihanMahasiswaID', $row_draft->ID)->get();
            
            foreach ($query_draft_termin as $row_draft_termin) {
                $tagihan_termin = $row_draft_termin->TotalTagihan;
                $jumlah_sisa_termin = $row_draft_termin->Jumlah;
                $diskon_termin = 0;
                $jumlah_sisaTemp_t = $jumlah_sisa_termin;
                $tagihanTemp_t = $tagihan_termin;

                foreach ($diskon_jb as $djb) {
                    if (isset($arr_master_diskon[$djb])) {
                        $jumlah_diskon = $arr_master_diskon[$djb]->Jumlah;
                        $tipe_diskon = $arr_master_diskon[$djb]->Tipe;
                        $jenis_diskon = $arr_master_diskon[$djb]->JenisDiskon;

                        if ($tipe_diskon == 'persen') {
                            if ($jenis_diskon == 'potong_dari_sisa') {
                                $tempPersen = $jumlah_sisaTemp_t * $jumlah_diskon / 100;
                            } else {
                                $tempPersen = $tagihan_termin * $jumlah_diskon / 100;
                            }
                            $tempPersen = intval($tempPersen);
                            $tagihanTemp_t -= $tempPersen;
                            $jumlah_sisaTemp_t -= $tempPersen;
                            $diskon_termin += $tempPersen;
                            if ($diskon_termin > $tagihan_termin) $diskon_termin = $tagihan_termin;
                        } else if ($tipe_diskon == 'nominal') {
                            $tagihanTemp_t -= $jumlah_diskon;
                            $jumlah_sisaTemp_t -= $jumlah_diskon;
                            $diskon_termin += $jumlah_diskon;
                            if ($diskon_termin > $tagihan_termin) $diskon_termin = $tagihan_termin;
                        }
                    }
                }

                DB::table('draft_tagihan_mahasiswa_termin')
                    ->where('ID', $row_draft_termin->ID)
                    ->update([
                        'TotalTagihan' => $tagihan_termin,
                        'JumlahDiskon' => $diskon_termin,
                        'Jumlah' => $tagihan_termin - $diskon_termin
                    ]);
            }
        }

        // Update semester totals
        if (count($list_id_semester) > 0) {
            foreach ($list_id_semester as $row_id_semester) {
                $tagihan_semester = DB::table('draft_tagihan_mahasiswa_semester')->where('ID', $row_id_semester)->first();
                if ($tagihan_semester) {
                    $diskon = $diskon_per_id_semester[$row_id_semester] ?? 0;
                    DB::table('draft_tagihan_mahasiswa_semester')
                        ->where('ID', $row_id_semester)
                        ->update([
                            'TotalTagihan' => $tagihan_semester->TotalTagihan,
                            'JumlahDiskon' => $diskon,
                            'Jumlah' => $tagihan_semester->TotalTagihan - $diskon
                        ]);
                }
            }
        }
    }

    public function unset_diskon($MhswDiskonID)
    {
        if (!$MhswDiskonID) return;

        $mahasiswa_diskon = DB::table('mahasiswa_diskon')->where('ID', $MhswDiskonID)->first();
        if (!$mahasiswa_diskon) return;

        $MhswID = $mahasiswa_diskon->MhswID;
        $detail_diskon = DB::table('mahasiswa_diskon_detail')->where('MhswDiskonID', $MhswDiskonID)->get();

        $arr_master_diskon = [];
        foreach (DB::table('master_diskon')->get() as $row) {
            $arr_master_diskon[$row->ID] = $row;
        }

        $list_id_semester = [];
        $list_id_termin = [];
        $diskon_per_id_semester = [];
        $diskon_per_id_termin = [];

        foreach ($detail_diskon as $row_detail_diskon) {
            $tagihan = $row_detail_diskon->JumlahTagihan;
            $tagihanTemp = $tagihan;
            $diskon = 0;

            $row_draft = DB::table('draft_tagihan_mahasiswa')->where('ID', $row_detail_diskon->DraftTagihanMahasiswaID)->first();
            if (!$row_draft) continue;

            $jumlah_sisa = $row_draft->Jumlah;
            $jumlah_sisaTemp = $jumlah_sisa;

            $diskon_jb = explode(",", $row_draft->MasterDiskonID);
            $diskon_jb_detail = explode(",", $row_detail_diskon->MasterDiskonID_list);

            $diskon_jb_temp = $diskon_jb;
            foreach ($diskon_jb_temp as $djb_temp) {
                if (in_array($djb_temp, $diskon_jb_detail)) {
                    if (($key1 = array_search($djb_temp, $diskon_jb)) !== false) {
                        unset($diskon_jb[$key1]);
                    }
                }
            }
            if (count($diskon_jb) > 0) {
                $diskon_jb = array_unique($diskon_jb);
            }

            foreach ($diskon_jb as $djb) {
                if (isset($arr_master_diskon[$djb])) {
                    $jumlah_diskon = $arr_master_diskon[$djb]->Jumlah;
                    $tipe_diskon = $arr_master_diskon[$djb]->Tipe;
                    $jenis_diskon = $arr_master_diskon[$djb]->JenisDiskon;

                    if ($tipe_diskon == 'persen') {
                        if ($jenis_diskon == 'potong_dari_sisa') {
                            $tempPersen = $jumlah_sisaTemp * $jumlah_diskon / 100;
                        } else {
                            $tempPersen = $tagihan * $jumlah_diskon / 100;
                        }
                        $tempPersen = intval($tempPersen);
                        $tagihanTemp -= $tempPersen;
                        $jumlah_sisaTemp -= $tempPersen;
                        $diskon += $tempPersen;
                        if ($diskon > $tagihan) $diskon = $tagihan;
                    } else if ($tipe_diskon == 'nominal') {
                        $tagihanTemp -= $jumlah_diskon;
                        $jumlah_sisaTemp -= $jumlah_diskon;
                        $diskon += $jumlah_diskon;
                        if ($diskon > $tagihan) $diskon = $tagihan;
                    }
                }
            }

            DB::table('draft_tagihan_mahasiswa')
                ->where('ID', $row_draft->ID)
                ->update([
                    'MasterDiskonID' => implode(",", $diskon_jb),
                    'TotalTagihan' => $tagihan,
                    'JumlahDiskon' => $diskon,
                    'Jumlah' => $tagihan - $diskon
                ]);

            $id_semester = $row_draft->DraftTagihanMahasiswaSemesterID;
            $list_id_semester[] = $id_semester;
            $diskon_per_id_semester[$id_semester] = ($diskon_per_id_semester[$id_semester] ?? 0) + $diskon;

            // Update detail
            $query_draft_detail = DB::table('draft_tagihan_mahasiswa_detail')
                ->where('DraftTagihanMahasiswaID', $row_draft->ID)->get();

            foreach ($query_draft_detail as $row_draft_detail) {
                $tagihan_detail = $row_draft_detail->TotalTagihan;
                $jumlah_sisa_detail = $row_draft_detail->Jumlah;
                $diskon_detail = 0;
                $jumlah_sisaTemp_d = $jumlah_sisa_detail;
                $tagihanTemp_d = $tagihan_detail;

                foreach ($diskon_jb as $djb) {
                    if (isset($arr_master_diskon[$djb])) {
                        $jumlah_diskon = $arr_master_diskon[$djb]->Jumlah;
                        $tipe_diskon = $arr_master_diskon[$djb]->Tipe;
                        $jenis_diskon = $arr_master_diskon[$djb]->JenisDiskon;

                        if ($tipe_diskon == 'persen') {
                            if ($jenis_diskon == 'potong_dari_sisa') {
                                $tempPersen = $jumlah_sisaTemp_d * $jumlah_diskon / 100;
                            } else {
                                $tempPersen = $tagihan_detail * $jumlah_diskon / 100;
                            }
                            $tempPersen = intval($tempPersen);
                            $tagihanTemp_d -= $tempPersen;
                            $jumlah_sisaTemp_d -= $tempPersen;
                            $diskon_detail += $tempPersen;
                            if ($diskon_detail > $tagihan_detail) $diskon_detail = $tagihan_detail;
                        } else if ($tipe_diskon == 'nominal') {
                            $tagihanTemp_d -= $jumlah_diskon;
                            $jumlah_sisaTemp_d -= $jumlah_diskon;
                            $diskon_detail += $jumlah_diskon;
                            if ($diskon_detail > $tagihan_detail) $diskon_detail = $tagihan_detail;
                        }
                    }
                }

                DB::table('draft_tagihan_mahasiswa_detail')
                    ->where('ID', $row_draft_detail->ID)
                    ->update([
                        'TotalTagihan' => $tagihan_detail,
                        'JumlahDiskon' => $diskon_detail,
                        'Jumlah' => $tagihan_detail - $diskon_detail
                    ]);
            }

            // Update termin
            $query_draft_termin = DB::table('draft_tagihan_mahasiswa_termin')
                ->where('DraftTagihanMahasiswaID', $row_draft->ID)->get();

            foreach ($query_draft_termin as $row_draft_termin) {
                $tagihan_termin = $row_draft_termin->TotalTagihan;
                $jumlah_sisa_termin = $row_draft_termin->Jumlah;
                $diskon_termin = 0;
                $jumlah_sisaTemp_t = $jumlah_sisa_termin;
                $tagihanTemp_t = $tagihan_termin;

                foreach ($diskon_jb as $djb) {
                    if (isset($arr_master_diskon[$djb])) {
                        $jumlah_diskon = $arr_master_diskon[$djb]->Jumlah;
                        $tipe_diskon = $arr_master_diskon[$djb]->Tipe;
                        $jenis_diskon = $arr_master_diskon[$djb]->JenisDiskon;

                        if ($tipe_diskon == 'persen') {
                            if ($jenis_diskon == 'potong_dari_sisa') {
                                $tempPersen = $jumlah_sisaTemp_t * $jumlah_diskon / 100;
                            } else {
                                $tempPersen = $tagihan_termin * $jumlah_diskon / 100;
                            }
                            $tempPersen = intval($tempPersen);
                            $tagihanTemp_t -= $tempPersen;
                            $jumlah_sisaTemp_t -= $tempPersen;
                            $diskon_termin += $tempPersen;
                            if ($diskon_termin > $tagihan_termin) $diskon_termin = $tagihan_termin;
                        } else if ($tipe_diskon == 'nominal') {
                            $tagihanTemp_t -= $jumlah_diskon;
                            $jumlah_sisaTemp_t -= $jumlah_diskon;
                            $diskon_termin += $jumlah_diskon;
                            if ($diskon_termin > $tagihan_termin) $diskon_termin = $tagihan_termin;
                        }
                    }
                }

                DB::table('draft_tagihan_mahasiswa_termin')
                    ->where('ID', $row_draft_termin->ID)
                    ->update([
                        'TotalTagihan' => $tagihan_termin,
                        'JumlahDiskon' => $diskon_termin,
                        'Jumlah' => $tagihan_termin - $diskon_termin
                    ]);

                $list_id_termin[] = $row_draft_termin->ID;
                $diskon_per_id_termin[$row_draft_termin->ID] = ($diskon_per_id_termin[$row_draft_termin->ID] ?? 0) + $diskon_termin;
            }
        }

        // Update semester totals
        if (count($list_id_semester) > 0) {
            foreach ($list_id_semester as $row_id_semester) {
                $tagihan_semester = DB::table('draft_tagihan_mahasiswa_semester')->where('ID', $row_id_semester)->first();
                if ($tagihan_semester) {
                    $diskon = $diskon_per_id_semester[$row_id_semester] ?? 0;
                    DB::table('draft_tagihan_mahasiswa_semester')
                        ->where('ID', $tagihan_semester->ID)
                        ->update([
                            'TotalTagihan' => $tagihan_semester->TotalTagihan,
                            'JumlahDiskon' => $diskon,
                            'Jumlah' => $tagihan_semester->TotalTagihan - $diskon
                        ]);
                }
            }
        }
    }

    public function soft_delete($id)
    {
        DB::table('mahasiswa_diskon')
            ->where('ID', $id)
            ->update(['StatusAktif' => '0']);
    }

    public function update($id, $data)
    {
        $TahunID = $data['TahunID'] ?? '';
        $JenisBiayaID = $data['JenisBiayaID'] ?? [];
        $MasterDiskonID = $data['MasterDiskonID'] ?? [];
        $UserID = Session::get('UserID');

        $arr_jenisbiaya = [];
        foreach (DB::table('jenisbiaya')->get() as $row) {
            $arr_jenisbiaya[$row->ID] = $row;
        }

        $arr_master_diskon = [];
        foreach (DB::table('master_diskon')->get() as $row) {
            $arr_master_diskon[$row->ID] = $row;
        }

        $mahasiswa_diskon = DB::table('mahasiswa_diskon')->where('ID', $id)->first();
        if (!$mahasiswa_diskon) return;

        $get_mhs_id = DB::table('mahasiswa')->where('ID', $mahasiswa_diskon->MhswID)->first();

        $Discount_arr = [];
        $insert_details = [];
        $arr_jb_imp = [];

        // Delete old details
        DB::table('mahasiswa_diskon_detail')->where('MhswDiskonID', $id)->delete();

        foreach ($JenisBiayaID as $jb) {
            $diskon_jb = $MasterDiskonID ?? [];

            if (count($diskon_jb) > 0) {
                foreach ($diskon_jb as $disc) {
                    $Discount_arr[$disc] = $disc;
                }

                $cek_draft_tagihan = DB::table('draft_tagihan_mahasiswa')
                    ->where('MhswID', $get_mhs_id->ID)
                    ->where('JenisBiayaID', $jb)
                    ->where('Periode', $TahunID)
                    ->first();

                if ($cek_draft_tagihan) {
                    $diskon_jb = array_unique($diskon_jb);

                    $tagihan = $cek_draft_tagihan->TotalTagihan;
                    $jumlah_sisa = $cek_draft_tagihan->Jumlah;
                    $diskon = 0;
                    $jumlah_sisaTemp = $jumlah_sisa;
                    $tagihanTemp = $tagihan;

                    foreach ($diskon_jb as $djb) {
                        if (isset($arr_master_diskon[$djb])) {
                            $jumlah_diskon = $arr_master_diskon[$djb]->Jumlah;
                            $tipe_diskon = $arr_master_diskon[$djb]->Tipe;
                            $jenis_diskon = $arr_master_diskon[$djb]->JenisDiskon;

                            if ($tipe_diskon == 'persen') {
                                if ($jenis_diskon == 'potong_dari_sisa') {
                                    $tempPersen = $jumlah_sisaTemp * $jumlah_diskon / 100;
                                } else {
                                    $tempPersen = $tagihan * $jumlah_diskon / 100;
                                }
                                $tempPersen = intval($tempPersen);
                                $tagihanTemp -= $tempPersen;
                                $jumlah_sisaTemp -= $tempPersen;
                                $diskon += $tempPersen;
                            } else if ($tipe_diskon == 'nominal') {
                                $tagihanTemp -= $jumlah_diskon;
                                $jumlah_sisaTemp -= $jumlah_diskon;
                                $diskon += $jumlah_diskon;
                                if ($diskon > $jumlah_sisa) {
                                    $diskon = $jumlah_sisa;
                                }
                            }
                        }
                    }

                    $insert_detail = [
                        'DraftTagihanMahasiswaID' => $cek_draft_tagihan->ID,
                        'JenisBiayaID' => $cek_draft_tagihan->JenisBiayaID,
                        'NamaJenisBiaya' => $arr_jenisbiaya[$cek_draft_tagihan->JenisBiayaID]->Nama ?? '',
                        'MasterDiskonID_list' => implode(",", $diskon_jb),
                        'JumlahTagihan' => $jumlah_sisa,
                        'JumlahDiskon' => $diskon,
                        'Jumlah' => $jumlah_sisa - $diskon,
                        'createdAt' => date('Y-m-d H:i:s'),
                        'UserID' => $UserID,
                        'MhswDiskonID' => $id
                    ];
                    $insert_details[] = $insert_detail;
                    $arr_jb_imp[] = $cek_draft_tagihan->JenisBiayaID;
                }
            }
        }

        $Discount_arr = array_unique($Discount_arr);
        $DiscountID_imp = implode(",", $Discount_arr);
        $jb_imp = implode(",", array_unique($arr_jb_imp));

        $update = [
            'TahunID' => $TahunID,
            'ListMasterDiskonID' => $DiscountID_imp,
            'ListJenisBiayaID' => $jb_imp,
            'LastUpdateUserID' => $UserID
        ];

        DB::table('mahasiswa_diskon')
            ->where('ID', $id)
            ->update($update);

        foreach ($insert_details as $row_insert_detail) {
            DB::table('mahasiswa_diskon_detail')->insert($row_insert_detail);
        }

        $this->set_diskon($id);
    }

    public function aktivkan($id)
    {
        DB::table('mahasiswa_diskon')
            ->where('ID', $id)
            ->update(['StatusAktif' => '1']);
    }
}
