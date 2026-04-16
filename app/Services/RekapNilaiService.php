<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class RekapNilaiService
{
    /**
     * Search and process grade data
     */
    public function searchData($filters)
    {
        $TahunMasuk = $filters['TahunMasuk'] ?? '';
        $TahunID = $filters['TahunID'] ?? '';
        $ProdiID = $filters['ProdiID'] ?? '';
        $ProgramID = $filters['ProgramID'] ?? '';
        $KelasID = $filters['KelasID'] ?? '';
        $SemesterMasuk = $filters['SemesterMasuk'] ?? '';
        $Semester = $filters['Semester'] ?? '';
        $keyword = $filters['keyword'] ?? '';

        // Get karyawan ProdiID for access control
        $entityID = Session::get('EntityID');
        $kar = DB::table('karyawan')->where('ID', $entityID)->first();
        $arrProdi = $kar && $kar->ProdiID ? explode(',', $kar->ProdiID) : [];
        $levelKode = Session::get('LevelKode', '');

        $tahunSemester = '';
        if ($TahunID) {
            $tahunRow = DB::table('tahun')->where('ID', $TahunID)->first();
            $tahunSemester = $tahunRow->TahunID ?? '';
        }

        // Query nilai data
        $query = DB::table('nilai')
            ->select(
                'mahasiswa.ID AS MhswID',
                'mahasiswa.Nama AS NamaMahasiswa',
                'mahasiswa.NPM AS NPM',
                'detailkurikulum.ID as DetailKurikulumID',
                'detailkurikulum.MKKode',
                'detailkurikulum.Nama',
                'nilai.TotalSKS',
                'nilai.NilaiAkhir',
                'nilai.NilaiHuruf',
                'nilai.Bobot',
                'nilai.NamaMataKuliah',
                'nilai.ID as nilaiID',
                'tahun.ID AS TahunID'
            )
            ->leftJoin('rencanastudi', 'rencanastudi.ID', '=', 'nilai.rencanastudiID')
            ->join('mahasiswa', 'mahasiswa.ID', '=', 'nilai.MhswID')
            ->join('detailkurikulum', 'detailkurikulum.ID', '=', 'nilai.detailkurikulumID')
            ->leftJoin('tahun', 'tahun.ID', '=', 'nilai.TahunID')
            ->where('nilai.PublishTranskrip', '1')
            ->where('nilai.PublishKHS', '1')
            ->orderBy('mahasiswa.NPM', 'ASC');

        if ($TahunID) {
            $query->where('tahun.TahunID', '<=', $tahunSemester);
        }
        if ($ProdiID) {
            $query->where('mahasiswa.ProdiID', $ProdiID);
        } else {
            if (!in_array('SPR', explode(',', $levelKode))) {
                $query->whereIn('mahasiswa.ProdiID', $arrProdi);
            }
        }
        if ($ProgramID) {
            $query->where('mahasiswa.ProgramID', $ProgramID);
        }
        if ($TahunMasuk) {
            $query->where('mahasiswa.TahunMasuk', $TahunMasuk);
        }
        if ($SemesterMasuk) {
            $query->where('mahasiswa.SemesterMasuk', $SemesterMasuk);
        }
        if ($Semester) {
            $query->where('rencanastudi.Semester', $Semester);
        }
        if ($KelasID) {
            $query->where('rencanastudi.KelasID', $KelasID);
        }
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('mahasiswa.NPM', 'like', '%' . $keyword . '%')
                  ->orWhere('mahasiswa.Nama', 'like', '%' . $keyword . '%');
            });
        }

        $queryResult = $query->get()->toArray();

        // Group by MhswID
        $dataNilaiPerMahasiswa = [];
        foreach ($queryResult as $dataNilai) {
            if (!empty($dataNilai->nilaiID)) {
                $dataNilaiPerMahasiswa[$dataNilai->MhswID][] = $dataNilai;
            }
        }

        // Process to remove duplicates by MKKode
        $exceptNilai = $this->getExceptNilai();
        $dataMkKodeClear = [];

        foreach ($dataNilaiPerMahasiswa as $MhswID => $dataNilai) {
            $mkList = [];
            $mkBobot = [];
            $listData = [];

            foreach ($dataNilai as $valAwal) {
                if (!in_array($valAwal->NilaiAkhir, $exceptNilai)) {
                    if (!in_array($valAwal->MKKode, $mkList)) {
                        $listData[$valAwal->MKKode] = $valAwal;
                        $mkList[] = $valAwal->MKKode;
                        $mkBobot[$valAwal->MKKode] = $valAwal->Bobot;
                    } else if ($valAwal->Bobot > ($mkBobot[$valAwal->MKKode] ?? -1)) {
                        unset($listData[$valAwal->MKKode]);
                        $listData[$valAwal->MKKode] = $valAwal;
                        $mkBobot[$valAwal->MKKode] = $valAwal->Bobot;
                    }
                }
            }
            $dataMkKodeClear[$MhswID] = $listData;
        }

        // Process to remove duplicates by NamaMataKuliah
        $dataNamaMatkulClear = [];
        foreach ($dataMkKodeClear as $MhswID => $dataNilai2) {
            $mkList2 = [];
            $mkBobot2 = [];
            $listData2 = [];

            foreach ($dataNilai2 as $valAwal) {
                $mkName = strtolower($valAwal->NamaMataKuliah ?? '');
                if (!in_array($mkName, $mkList2)) {
                    $listData2[$mkName] = $valAwal;
                    $mkList2[] = $mkName;
                    $mkBobot2[$mkName] = $valAwal->Bobot;
                } else if ($valAwal->Bobot > ($mkBobot2[$mkName] ?? -1)) {
                    unset($listData2[$mkName]);
                    $listData2[$mkName] = $valAwal;
                    $mkBobot2[$mkName] = $valAwal->Bobot;
                }
            }
            $dataNamaMatkulClear[$MhswID] = $listData2;
        }

        // Flatten to final data
        $finalData = [];
        foreach ($dataNamaMatkulClear as $MhswID => $dataMK) {
            foreach ($dataMK as $dataAll) {
                $finalData[] = $dataAll;
            }
        }

        // Build result arrays
        $mahasiswa = [];
        $matakuliah = [];
        $nilaiMatkul = [];
        $bobotMatkul = [];

        foreach ($finalData as $getMatkul) {
            if ($TahunID == $getMatkul->TahunID || empty($TahunID)) {
                $mhswID = $getMatkul->MhswID;
                $mkID = $getMatkul->DetailKurikulumID;

                $mahasiswa[$mhswID]['ID'] = $mhswID;
                $mahasiswa[$mhswID]['Nama'] = $getMatkul->NamaMahasiswa;
                $mahasiswa[$mhswID]['NPM'] = $getMatkul->NPM;

                if ($getMatkul->NilaiHuruf != '' && $getMatkul->NilaiHuruf != 'T') {
                    $mahasiswa[$mhswID]['TotalSKS'] = ($mahasiswa[$mhswID]['TotalSKS'] ?? 0) + $getMatkul->TotalSKS;
                }

                $matakuliah[$mkID]['ID'] = $mkID;
                $matakuliah[$mkID]['MKKode'] = $getMatkul->MKKode;
                $matakuliah[$mkID]['Nama'] = $getMatkul->Nama;
                $matakuliah[$mkID]['TotalSKS'] = $getMatkul->TotalSKS;

                $nilaiMatkul[$mhswID][$mkID]['NilaiAkhir'] = $getMatkul->NilaiAkhir;
                $nilaiMatkul[$mhswID][$mkID]['NilaiHuruf'] = $getMatkul->NilaiHuruf;

                $bobotMatkul[$mhswID][$mkID]['Bobot'] = $getMatkul->Bobot;
            } else {
                if ($getMatkul->NilaiHuruf != '' && $getMatkul->NilaiHuruf != 'T') {
                    $mhswID = $getMatkul->MhswID;
                    $mahasiswa[$mhswID]['TotalSKSSebelumnya'] = ($mahasiswa[$mhswID]['TotalSKSSebelumnya'] ?? 0) + $getMatkul->TotalSKS;
                    $mahasiswa[$mhswID]['TotalBobotSebelumnya'] = ($mahasiswa[$mhswID]['TotalBobotSebelumnya'] ?? 0) + ($getMatkul->Bobot * $getMatkul->TotalSKS);
                }
            }
        }

        // Sort matakuliah by MKKode
        uasort($matakuliah, function ($a, $b) {
            return strcmp($a['MKKode'], $b['MKKode']);
        });

        return [
            'mahasiswa' => $mahasiswa,
            'matakuliah' => $matakuliah,
            'nilai_matkul' => $nilaiMatkul,
            'bobot_matkul' => $bobotMatkul,
        ];
    }

    /**
     * Get list of grades to exclude
     */
    private function getExceptNilai()
    {
        $exceptNilai = [];
        $rowHideNilaiHuruf = DB::table('setup_app')->where('nama_setup', 'setup_hide_nilai_huruf')->first();

        if ($rowHideNilaiHuruf && $rowHideNilaiHuruf->metadata) {
            $metadata = json_decode($rowHideNilaiHuruf->metadata, true);
            if (isset($metadata['hide_nilai_huruf'])) {
                $expHuruf = array_filter(explode(',', $metadata['hide_nilai_huruf']));
                if ($expHuruf) {
                    $exceptNilai = $expHuruf;
                }
            }
        }

        return $exceptNilai;
    }
}
