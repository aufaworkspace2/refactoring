<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use App\Models\HasilStudi;
use stdClass;

class HasilStudiService
{
    public function getHasilStudiList($limit, $offset, $ProgramID = '', $ProdiID = '', $TahunMasuk = '', $SemesterMasuk = '', $TahunID = '', $keyword = '')
    {
        $user = get_id(Session::get('UserID'), 'user');

        $arrProgramID = [];
        if (!empty($ProgramID)) {
            $arrProgramID = [$ProgramID];
        } else {
            if (!str_contains(Session::get('LevelKode'), 'SPR') && isset($user->ProgramID)) {
                $arrProgramID = explode(",", $user->ProgramID);
            }
        }

        $arrProdiID = [];
        if (!empty($ProdiID)) {
            $arrProdiID = [$ProdiID];
        } else {
            if (!str_contains(Session::get('LevelKode'), 'SPR') && isset($user->ProdiID)) {
                $arrProdiID = explode(",", $user->ProdiID);
            }
        }

        $query = DB::table('mahasiswa as b')
            ->select("a.*", "b.NPM as npmMahasiswa", "b.Nama as namaMahasiswa", "b.StatusMhswID", "b.ID as MhswID")
            ->join("rencanastudi as a", "a.MhswID", "=", "b.ID");

        if (count($arrProgramID) > 0) {
            $query->whereIn("b.ProgramID", $arrProgramID);
        }
        if (count($arrProdiID) > 0) {
            $query->whereIn("b.ProdiID", $arrProdiID);
        }
        if (!empty($TahunID)) {
            $query->where("a.TahunID", $TahunID);
        }
        if (!empty($TahunMasuk)) {
            $query->where("b.TahunMasuk", $TahunMasuk);
        }
        if (!empty($SemesterMasuk)) {
            $query->where("b.SemesterMasuk", $SemesterMasuk);
        }
        if ($keyword != '' && $keyword != null) {
            $query->where(function ($q) use ($keyword) {
                $q->orWhere("b.Nama", "like", "%$keyword%")
                    ->orWhere("b.NPM", "like", "%$keyword%");
            });
        }

        $result = $query->groupBy("b.ID")
            ->orderBy("b.Nama")
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(fn($item) => (object) $item)
            ->toArray();

        foreach ($result as $row) {
            $row->namaMahasiswa = stripslashes($row->namaMahasiswa);
        }

        return $result;
    }

    public function countHasilStudiList($ProgramID = '', $ProdiID = '', $TahunMasuk = '', $SemesterMasuk = '', $TahunID = '', $keyword = '')
    {
        $user = get_id(Session::get('UserID'), 'user');

        $arrProgramID = [];
        if (!empty($ProgramID)) {
            $arrProgramID = [$ProgramID];
        } else {
            if (!str_contains(Session::get('LevelKode'), 'SPR') && isset($user->ProgramID)) {
                $arrProgramID = explode(",", $user->ProgramID);
            }
        }

        $arrProdiID = [];
        if (!empty($ProdiID)) {
            $arrProdiID = [$ProdiID];
        } else {
            if (!str_contains(Session::get('LevelKode'), 'SPR') && isset($user->ProdiID)) {
                $arrProdiID = explode(",", $user->ProdiID);
            }
        }

        $query = DB::table('mahasiswa as b')
            ->join("rencanastudi as a", "a.MhswID", "=", "b.ID");

        if (count($arrProgramID) > 0) {
            $query->whereIn("b.ProgramID", $arrProgramID);
        }
        if (count($arrProdiID) > 0) {
            $query->whereIn("b.ProdiID", $arrProdiID);
        }
        if (!empty($TahunID)) {
            $query->where("a.TahunID", $TahunID);
        }
        if (!empty($TahunMasuk)) {
            $query->where("b.TahunMasuk", $TahunMasuk);
        }
        if (!empty($SemesterMasuk)) {
            $query->where("b.SemesterMasuk", $SemesterMasuk);
        }
        if ($keyword != '' && $keyword != null) {
            $query->where(function ($q) use ($keyword) {
                $q->orWhere("b.Nama", "like", "%$keyword%")
                    ->orWhere("b.NPM", "like", "%$keyword%");
            });
        }

        return $query->distinct("b.ID")->count("b.ID");
    }

    public function simpanIpk($ProgramID, $ProdiID, $TahunID, $jenis = '', $dari = '', $sampai = '')
    {
        // $cek = "SELECT *,SUM(TotalSKS) as tot FROM (SELECT b.*,c.TotalSKS,a.TahunID FROM rencanastudi a,mahasiswa b,detailkurikulum c WHERE a.DetailKurikulumID=c.ID AND a.MhswID=b.ID AND  b.StatusMhswID='3' AND a.ProgramID='$ProgramID' AND a.ProdiID='$ProdiID' AND a.TahunID='$TahunID' ORDER BY b.NPM) abc GROUP BY abc.ID";
        $q = DB::table(DB::raw("(SELECT b.*, c.TotalSKS, a.TahunID FROM rencanastudi a, mahasiswa b, detailkurikulum c WHERE a.DetailKurikulumID = c.ID AND a.MhswID = b.ID AND b.StatusMhswID = '3' AND a.ProgramID = '$ProgramID' AND a.ProdiID = '$ProdiID' AND a.TahunID = '$TahunID' ORDER BY b.NPM) as abc"))
            ->select(DB::raw("*, SUM(TotalSKS) as tot"))
            ->groupBy("ID")
            ->get();

        foreach ($q as $row) {
            $all = DB::table('rencanastudi as a')
                ->join('detailkurikulum as b', 'a.DetailKurikulumID', '=', 'b.ID')
                ->where('MhswID', $row->ID)
                ->select(DB::raw("SUM(TotalSKS) as tot"))
                ->first();

            $ips = view_ips($row->ID, $row->TahunID)->IPS ?? 0;
            $ipk = view_ipk($row->ID)->IPK ?? 0;

            $s = "";
            if ($dari != '' && $sampai != "") {
                if ($jenis == 'IPK') {
                    if (!($ipk >= $dari && $ipk <= $sampai)) {
                        $s = "style='display:none;'";
                    }
                } elseif ($jenis == 'IPS') {
                    if (!($ips >= $dari && $ips <= $sampai)) {
                        $s = "style='display:none;'";
                    }
                }
            }

            $data = [
                'TahunID' => $row->TahunID,
                'ProgramID' => $row->ProgramID,
                'ProdiID' => $row->ProdiID,
                'MhswID' => $row->ID,
                'NPM' => $row->NPM,
                'IPS' => $ips,
                'SKSIPS' => $row->tot,
                'IPK' => $ipk,
                'SKSIPK' => $all->tot ?? 0,
            ];

            if ($s == "") {
                $c = DB::table('hasilstudi')->where('MhswID', $row->ID)->where('TahunID', $row->TahunID)->first();
                if ($c) {
                    DB::table('hasilstudi')->where("ID", $c->ID)->update($data);
                } else {
                    $Q = DB::table('rencanastudi')->where('MhswID', $row->ID)->groupBy('TahunID')->count();
                    $Qc = DB::table('rencanastudi')->where('MhswID', $row->ID)->where('TahunID', $row->TahunID)->groupBy('TahunID')->count();
                    
                    if ($Qc > 0) {
                        $sem = $Q;
                    } else {
                        $sem = $Q + 1;
                    }

                    $data['Semester'] = $sem;
                    DB::table('hasilstudi')->insert($data);
                }
            }
        }
    }

    public function getHasilStudiById($id)
    {
        return HasilStudi::find($id);
    }

    public function addHasilStudi($data)
    {
        return HasilStudi::create($data);
    }

    public function editHasilStudi($id, $data)
    {
        return HasilStudi::where('ID', $id)->update($data);
    }

    public function deleteHasilStudi($id)
    {
        return HasilStudi::where('ID', $id)->delete();
    }

    public function getTahunMasuk()
    {
        return DB::table('mahasiswa')
            ->select('TahunMasuk')
            ->distinct()
            ->orderBy('TahunMasuk', 'DESC')
            ->get()
            ->toArray();
    }

    public function getListNilai($programID, $prodiID, $statusMhswID, $tahunMasuk, $tahunID, $keyword)
    {
        $tahunRow = get_id($tahunID, 'tahun');

        $query = DB::table('nilai')
            ->select(DB::raw('mahasiswa.ID AS mhswID,
							rencanastudi.ID AS rencanastudiID,
							mahasiswa.NPM AS npm,
							mahasiswa.Nama AS nama,
							mahasiswa.TahunMasuk AS tahunMasuk,
							program.Nama AS namaProgram,
							programstudi.Nama AS namaProdi,
							rencanastudi.Semester AS semester,
							nilai.NilaiAkhir AS nilaiakhir,
							nilai.NilaiHuruf AS nilaiHuruf,
							nilai.Bobot AS bobot,
							rencanastudi.TahunID as tahunID,
							rencanastudi.MKKode,
							nilai.NamaMataKuliah,
							rencanastudi.TotalSKS as totalSKS'))
            ->join('rencanastudi', 'nilai.rencanastudiID', '=', 'rencanastudi.ID')
            ->join('mahasiswa', 'rencanastudi.MhswID', '=', 'mahasiswa.ID')
            ->join('tahun', 'tahun.ID', '=', 'rencanastudi.TahunID')
            ->join('program', 'mahasiswa.ProgramID', '=', 'program.ID')
            ->join('programstudi', 'mahasiswa.ProdiID', '=', 'programstudi.ID');

        if (!empty($programID)) {
            $query->where('mahasiswa.ProgramID', $programID);
        }
        if (!empty($prodiID)) {
            $query->where('mahasiswa.ProdiID', $prodiID);
        }
        if (!empty($statusMhswID)) {
            $query->where('mahasiswa.StatusMhswID', $statusMhswID);
        }
        if (!empty($tahunMasuk)) {
            $query->where('mahasiswa.TahunMasuk', $tahunMasuk);
        } else {
            $tahunNow = (int) substr($tahunRow->TahunID, 0, 4);
            $tahun7Ago = $tahunNow - 6;
            $rangeTahun = range($tahun7Ago, $tahunNow);
            $query->whereIn('mahasiswa.TahunMasuk', $rangeTahun);
        }
        if (!empty($tahunID)) {
            $query->where('tahun.TahunID', '<=', $tahunRow->TahunID);
        }
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->orWhere("mahasiswa.NPM", "like", "%$keyword%")
                    ->orWhere("mahasiswa.Nama", "like", "%$keyword%");
            });
        }

        $query->where('mahasiswa.jenis_mhsw', 'mhsw');

        $result = $query->groupBy('rencanastudi.ID')
            ->orderBy('mahasiswa.NPM', 'ASC')
            ->orderBy('mahasiswa.ID', 'ASC')
            ->orderBy('rencanastudi.TahunID', 'ASC')
            ->get()
            ->map(fn($item) => (object) $item)
            ->toArray();

        return $result;
    }

    public function filterIPKLogic($programID, $prodiID, $tahunID, $jenis, $dari, $sampai, $tahunMasuk, $pilihanSort = 'ipk', $tipeSort = 'DESC')
    {
        $query = $this->getListNilai($programID, $prodiID, null, $tahunMasuk, $tahunID, null);

        $setup_transkrip = get_setup_app("setup_cetak_transkrip_sementara");
        $transkrip_custom = json_decode($setup_transkrip->metadata ?? '{}', true);

        $tempData = [];
        $tempNilai = [];
        $tempNilaiSemester = [];
        $tempSKS = [];
        $tempSKSSemester = [];
        $tempTahun = [];

        $mkList = [];
        $mkBobot = [];
        $listData = [];

        $mkList2 = [];
        $listData2 = [];

        foreach ($query as $valAwal) {
            if (!isset($mkList[$valAwal->mhswID])) {
                $mkList[$valAwal->mhswID] = [];
            }
            if (!isset($mkList2[$valAwal->mhswID])) {
                $mkList2[$valAwal->mhswID] = [];
            }
            if (!isset($mkBobot[$valAwal->mhswID])) {
                $mkBobot[$valAwal->mhswID] = [];
            }
        }

        foreach ($query as $valAwal) {
            if (!in_array($valAwal->MKKode, $mkList[$valAwal->mhswID])) {
                $listData[$valAwal->mhswID][$valAwal->MKKode] = $valAwal;
                $mkList[$valAwal->mhswID][] = $valAwal->MKKode;
                $mkBobot[$valAwal->mhswID][$valAwal->MKKode] = $valAwal->bobot;
            } else if ($valAwal->bobot > ($mkBobot[$valAwal->mhswID][$valAwal->MKKode] ?? -1)) {
                unset($listData[$valAwal->mhswID][$valAwal->MKKode]);
                $listData[$valAwal->mhswID][$valAwal->MKKode] = $valAwal;
                $mkBobot[$valAwal->mhswID][$valAwal->MKKode] = $valAwal->bobot;
            }
        }

        foreach ($listData as $mhswID => $list_per_mahasiswa) {
            foreach ($list_per_mahasiswa as $valAwal) {
                $mkName = strtolower($valAwal->NamaMataKuliah);
                if (!in_array($mkName, $mkList2[$mhswID])) {
                    $listData2[$mhswID][$mkName] = $valAwal;
                    $mkList2[$mhswID][] = $mkName;
                    $mkBobot[$mhswID][$mkName] = $valAwal->bobot;
                } else if ($valAwal->bobot > ($mkBobot[$mhswID][$mkName] ?? -1)) {
                    unset($listData2[$mhswID][$mkName]);
                    $listData2[$mhswID][$mkName] = $valAwal;
                    $mkBobot[$mhswID][$mkName] = $valAwal->bobot;
                }
            }
        }

        if ($transkrip_custom['transkrip_only_cek_MKKode'] ?? false) {
            $listData2 = $listData;
        }

        foreach ($listData2 as $mhswID => $val) {
            foreach ($val as $value) {
                if ($value->nilaiHuruf != '' && $value->nilaiHuruf != 'T') {
                    $tempSKS[$value->mhswID] = ($tempSKS[$value->mhswID] ?? 0) + $value->totalSKS;
                    $tempNilai[$value->mhswID] = ($tempNilai[$value->mhswID] ?? 0) + ($value->totalSKS * $value->bobot);
                    $tempTahun[$value->mhswID][$value->tahunID] = 1;
                }

                if ($value->tahunID == $tahunID) {
                    if ($value->nilaiHuruf != '' && $value->nilaiHuruf != 'T') {
                        $tempNilaiSemester[$value->mhswID] = ($tempNilaiSemester[$value->mhswID] ?? 0) + ($value->totalSKS * $value->bobot);
                        $tempSKSSemester[$value->mhswID] = ($tempSKSSemester[$value->mhswID] ?? 0) + $value->totalSKS;
                    }
                }
            }
        }

        $tahunRow = get_id($tahunID, 'tahun');

        foreach ($listData2 as $mhswID => $val) {
            foreach ($val as $value) {
                $param = [];
                $sksSem = $tempSKSSemester[$value->mhswID] ?? 0;
                $nilSem = $tempNilaiSemester[$value->mhswID] ?? 0;
                $ips = $sksSem > 0 ? number_format($nilSem / $sksSem, 2) : "0.00";

                $sksKum = $tempSKS[$value->mhswID] ?? 0;
                $nilKum = $tempNilai[$value->mhswID] ?? 0;
                $ipk = $sksKum > 0 ? number_format($nilKum / $sksKum, 2) : "0.00";

                $showData = true;
                if ($jenis == 'IPK') {
                    if ($dari != null && $sampai != null) {
                        if (!($ipk >= $dari && $ipk <= $sampai)) {
                            $showData = false;
                        }
                    }
                } else {
                    if ($dari != null && $sampai != null) {
                        if (!($ips >= $dari && $ips <= $sampai)) {
                            $showData = false;
                        }
                    }
                }

                if ($showData) {
                    $param['mhswID'] = $value->mhswID;
                    $param['rencanastudiID'] = $value->rencanastudiID;
                    $param['npm'] = $value->npm;
                    $param['nama'] = stripslashes($value->nama);
                    $param['tahunMasuk'] = $value->tahunMasuk;
                    $param['namaProgram'] = $value->namaProgram;
                    $param['namaProdi'] = $value->namaProdi;
                    $param['semester'] = $value->semester;
                    $param['nilaiHuruf'] = $value->nilaiHuruf;
                    $param['bobot'] = $value->bobot;
                    $param['tahunID'] = $value->tahunID;
                    $param['totalSKS'] = $value->totalSKS;
                    $param['sksSemester'] = $sksSem;
                    $param['ips'] = $ips;
                    $param['sksKumulatif'] = $sksKum;
                    $param['ipk'] = $ipk;
                    $param['semesterMahasiswa'] = ($tahunRow->Semester != 3) ? (get_semester($value->mhswID, $tahunID)->Semester ?? '') : 'SP';

                    $tempData[$value->mhswID] = $param;
                }
            }
        }

        if (!empty($tempData)) {
            $sortColumn = array_column($tempData, $pilihanSort);
            $sortOrder = $tipeSort == 'ASC' ? SORT_ASC : SORT_DESC;
            array_multisort($sortColumn, $sortOrder, $tempData);
        }

        return $tempData;
    }

    public function filterIPKAngkatanLogic($programID, $prodiID, $tahunID, $jenis, $dari, $sampai, $tahunMasuk, $statusMhswID)
    {
        $query = $this->getListNilai($programID, $prodiID, $statusMhswID, $tahunMasuk, $tahunID, null);

        $tempData = [];
        $tempNilai = [];
        $tempNilaiSemester = [];
        $tempSKS = [];
        $tempSKSSemester = [];
        $tempTahun = [];

        $mkList = [];
        $mkBobot = [];
        $listData = [];

        $mkList2 = [];
        $listData2 = [];

        foreach ($query as $valAwal) {
            if (!isset($mkList[$valAwal->mhswID])) {
                $mkList[$valAwal->mhswID] = [];
            }
            if (!isset($mkList2[$valAwal->mhswID])) {
                $mkList2[$valAwal->mhswID] = [];
            }
            if (!isset($mkBobot[$valAwal->mhswID])) {
                $mkBobot[$valAwal->mhswID] = [];
            }
        }

        foreach ($query as $valAwal) {
            if (!in_array($valAwal->MKKode, $mkList[$valAwal->mhswID])) {
                $listData[$valAwal->mhswID][$valAwal->MKKode] = $valAwal;
                $mkList[$valAwal->mhswID][] = $valAwal->MKKode;
                $mkBobot[$valAwal->mhswID][$valAwal->MKKode] = $valAwal->bobot;
            } else if ($valAwal->bobot > ($mkBobot[$valAwal->mhswID][$valAwal->MKKode] ?? -1)) {
                unset($listData[$valAwal->mhswID][$valAwal->MKKode]);
                $listData[$valAwal->mhswID][$valAwal->MKKode] = $valAwal;
                $mkBobot[$valAwal->mhswID][$valAwal->MKKode] = $valAwal->bobot;
            }
        }

        foreach ($listData as $mhswID => $list_per_mahasiswa) {
            foreach ($list_per_mahasiswa as $valAwal) {
                $mkName = strtolower($valAwal->NamaMataKuliah);
                if (!in_array($mkName, $mkList2[$mhswID])) {
                    $listData2[$mhswID][$mkName] = $valAwal;
                    $mkList2[$mhswID][] = $mkName;
                    $mkBobot[$mhswID][$mkName] = $valAwal->bobot;
                } else if ($valAwal->bobot > ($mkBobot[$mhswID][$mkName] ?? -1)) {
                    unset($listData2[$mhswID][$mkName]);
                    $listData2[$mhswID][$mkName] = $valAwal;
                    $mkBobot[$mhswID][$mkName] = $valAwal->bobot;
                }
            }
        }

        foreach ($listData2 as $mhswID => $val) {
            foreach ($val as $value) {
                if ($value->nilaiHuruf != '' && $value->nilaiHuruf != 'T') {
                    $tempSKS[$value->tahunMasuk] = ($tempSKS[$value->tahunMasuk] ?? 0) + $value->totalSKS;
                    $tempNilai[$value->tahunMasuk] = ($tempNilai[$value->tahunMasuk] ?? 0) + ($value->totalSKS * $value->bobot);
                    $tempTahun[$value->tahunMasuk][$value->tahunID] = 1;
                }

                if ($value->tahunID == $tahunID) {
                    if ($value->nilaiHuruf != '' && $value->nilaiHuruf != 'T') {
                        $tempNilaiSemester[$value->tahunMasuk] = ($tempNilaiSemester[$value->tahunMasuk] ?? 0) + ($value->totalSKS * $value->bobot);
                        $tempSKSSemester[$value->tahunMasuk] = ($tempSKSSemester[$value->tahunMasuk] ?? 0) + $value->totalSKS;
                    }
                }
            }
        }

        foreach ($listData2 as $mhswID => $val) {
            foreach ($val as $value) {
                $param = [];
                $sksSem = $tempSKSSemester[$value->tahunMasuk] ?? 0;
                $nilSem = $tempNilaiSemester[$value->tahunMasuk] ?? 0;
                $ips = $sksSem > 0 ? number_format($nilSem / $sksSem, 2) : "0.00";

                $sksKum = $tempSKS[$value->tahunMasuk] ?? 0;
                $nilKum = $tempNilai[$value->tahunMasuk] ?? 0;
                $ipk = $sksKum > 0 ? number_format($nilKum / $sksKum, 2) : "0.00";

                $showData = true;
                if ($jenis == 'IPK') {
                    if ($dari != null && $sampai != null) {
                        if (!($ipk >= $dari && $ipk <= $sampai)) {
                            $showData = false;
                        }
                    }
                } else {
                    if ($dari != null && $sampai != null) {
                        if (!($ips >= $dari && $ips <= $sampai)) {
                            $showData = false;
                        }
                    }
                }

                if ($showData) {
                    $param['tahunMasuk'] = $value->tahunMasuk;
                    $param['namaProgram'] = $value->namaProgram;
                    $param['namaProdi'] = $value->namaProdi;
                    $param['semester'] = $value->semester;
                    $param['nilaiHuruf'] = $value->nilaiHuruf;
                    $param['bobot'] = $value->bobot;
                    $param['tahunID'] = $value->tahunID;
                    $param['totalSKS'] = $value->totalSKS;
                    $param['sksSemester'] = $sksSem;
                    $param['ips'] = $ips;
                    $param['sksKumulatif'] = $sksKum;
                    $param['ipk'] = $ipk;

                    $tempData[$value->tahunMasuk] = $param;
                }
            }
        }

        if (!empty($tempData)) {
            $pilihanSort = 'tahunMasuk';
            $tipeSort = 'DESC';
            $sortColumn = array_column($tempData, $pilihanSort);
            $sortOrder = $tipeSort == 'ASC' ? SORT_ASC : SORT_DESC;
            array_multisort($sortColumn, $sortOrder, $tempData);
        }

        return $tempData;
    }
}
