<?php

namespace App\Services;

use App\Models\Transkrip;
use App\Models\Khs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use DateTime;

class TranskripMahasiswaService
{
    /**
     * Get mahasiswa list for search
     * CI3: m_mahasiswa->get_data_list()
     */
    public function getMahasiswaList($limit, $offset, $programID, $prodiID, $statusMhswID, $tahunMasuk, $semesterMasuk, $keyword)
    {
        $query = DB::table('mahasiswa')
            ->select('mahasiswa.*', 'program.Nama as NamaProgram', 'programstudi.Nama as NamaProdi')
            ->join('program', 'mahasiswa.ProgramID', '=', 'program.ID')
            ->join('programstudi', 'mahasiswa.ProdiID', '=', 'programstudi.ID');

        if ($programID) {
            $query->where('mahasiswa.ProgramID', $programID);
        }
        if ($prodiID) {
            $query->where('mahasiswa.ProdiID', $prodiID);
        }
        if ($statusMhswID) {
            $query->where('mahasiswa.StatusMhswID', $statusMhswID);
        }
        if ($tahunMasuk) {
            $query->where('mahasiswa.TahunMasuk', $tahunMasuk);
        }
        if ($semesterMasuk) {
            $query->where('mahasiswa.SemesterMasuk', $semesterMasuk);
        }
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('mahasiswa.NPM', 'LIKE', "%{$keyword}%")
                    ->orWhere('mahasiswa.Nama', 'LIKE', "%{$keyword}%");
            });
        }

        return $query->orderBy('mahasiswa.Nama', 'ASC')
            ->take($limit)
            ->skip($offset)
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
    }

    /**
     * Count mahasiswa list for pagination
     * CI3: m_mahasiswa->count_data_list()
     */
    public function countMahasiswaList($programID, $prodiID, $statusMhswID, $tahunMasuk, $semesterMasuk, $keyword)
    {
        $query = DB::table('mahasiswa');

        if ($programID) {
            $query->where('ProgramID', $programID);
        }
        if ($prodiID) {
            $query->where('ProdiID', $prodiID);
        }
        if ($statusMhswID) {
            $query->where('StatusMhswID', $statusMhswID);
        }
        if ($tahunMasuk) {
            $query->where('TahunMasuk', $tahunMasuk);
        }
        if ($semesterMasuk) {
            $query->where('SemesterMasuk', $semesterMasuk);
        }
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('NPM', 'LIKE', "%{$keyword}%")
                    ->orWhere('Nama', 'LIKE', "%{$keyword}%");
            });
        }

        return $query->count();
    }

    /**
     * Get transkrip data for edit view
     * Corresponds to: C_transkripmahasiswa->edit_transkrip()
     */
    public function get_transkrip_for_edit($mhswID)
    {
        $mhs = DB::table('mahasiswa')->where('ID', $mhswID)->first();

        if (!$mhs) {
            return null;
        }

        // Get mahasiswa info with program and prodi
        $dMhs = DB::table('mahasiswa as a')
            ->select('a.ID', 'a.Nama', 'a.NPM', 'b.Nama as NamaProgram', 'c.Nama as NamaProdi')
            ->join('program as b', 'a.ProgramID', '=', 'b.ID')
            ->join('programstudi as c', 'a.ProdiID', '=', 'c.ID')
            ->where('a.ID', $mhswID)
            ->first();

        // Convert to array
        $dMhsArray = $dMhs ? (array) $dMhs : null;

        // Get grades list for dropdown
        $nilaiBobot = DB::table('bobot')
            ->orderBy('Bobot', 'DESC')
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();

        // Get nilai data
        $sql = "SELECT * FROM nilai WHERE MhswID = ? and PublishTranskrip='1' ORDER BY Semester, MKKode ASC";
        $query = DB::select($sql, [$mhs->ID]);

        $mkList = [];
        $mkList2 = [];
        $mkBobot = [];
        $listData = [];
        $listData2 = [];

        $exceptNilai = [];

        $rowHideNilaiHuruf = get_setup_app("setup_hide_nilai_huruf");
        $metadataHideNilaiHuruf = json_decode($rowHideNilaiHuruf->metadata ?? '{}', true);

        $setupTranskrip = get_setup_app("setup_cetak_transkrip");
        $transkripCustom = json_decode($setupTranskrip->metadata ?? '{}', true);

        if ($metadataHideNilaiHuruf && isset($metadataHideNilaiHuruf['hide_nilai_huruf'])) {
            $expHuruf = array_filter(explode(",", $metadataHideNilaiHuruf['hide_nilai_huruf']));
            if ($expHuruf) {
                $exceptNilai = $expHuruf;
            }
        }

        foreach ($query as $valAwal) {
            if (!in_array($valAwal->NilaiHuruf, $exceptNilai)) {
                if (!in_array($valAwal->MKKode, $mkList)) {
                    $listData[$valAwal->MKKode] = (array) $valAwal;
                    $mkList[] = $valAwal->MKKode;
                    $mkBobot[$valAwal->MKKode] = $valAwal->Bobot;
                } else if ($valAwal->Bobot > $mkBobot[$valAwal->MKKode]) {
                    unset($listData[$valAwal->MKKode]);
                    $listData[$valAwal->MKKode] = (array) $valAwal;
                    $mkBobot[$valAwal->MKKode] = $valAwal->Bobot;
                }
            }
        }

        foreach ($listData as $valAwal) {
            $valAwal = (object) $valAwal; // temporarily object for processing
            if (!in_array(strtolower($valAwal->NamaMataKuliah), $mkList2)) {
                $listData2[strtolower($valAwal->NamaMataKuliah)] = (array) $valAwal;
                $mkList2[] = strtolower($valAwal->NamaMataKuliah);
                $mkBobot[strtolower($valAwal->NamaMataKuliah)] = $valAwal->Bobot;
            } else if ($valAwal->Bobot > $mkBobot[strtolower($valAwal->NamaMataKuliah)]) {
                unset($listData2[strtolower($valAwal->NamaMataKuliah)]);
                $listData2[strtolower($valAwal->NamaMataKuliah)] = (array) $valAwal;
                $mkBobot[strtolower($valAwal->NamaMataKuliah)] = $valAwal->Bobot;
            }
        }

        // Jika pengecekan duplikat/MK mengulang hanya melihat dari MKKode tidak dengan Nama Mata Kuliah
        if (isset($transkripCustom['transkrip_only_cek_MKKode']) && $transkripCustom['transkrip_only_cek_MKKode']) {
            $listData2 = $listData;
        }

        // Calculate totals
        $sksTotal = 0;
        $bobotTotal = 0;
        $notCount = ['T', '', null];

        foreach ($listData2 as $row) {
            $row = (object) $row;
            if (!in_array($row->NilaiHuruf, $notCount)) {
                $sksTotal += $row->TotalSKS;
                $bobotTotal += ($row->Bobot * $row->TotalSKS);
            }
        }

        $ipk = ($sksTotal > 0) ? ($bobotTotal / $sksTotal) : 0;

        return [
            'd_mhs' => $dMhsArray,
            'query' => $listData2,
            'nilai_bobot' => $nilaiBobot,
            'MhswID' => $mhswID,
            'sks_total' => $sksTotal,
            'bobot_total' => $bobotTotal,
            'ipk' => $ipk,
        ];
    }

    /**
     * Get KHS data for edit view
     * Corresponds to: C_transkripmahasiswa->edit_khs()
     */
    public function get_khs_for_edit($mhswID)
    {
        $now = date('Y-m-d');

        // Get mahasiswa info
        $dMhs = DB::table('mahasiswa as a')
            ->select('a.ID', 'a.Nama', 'a.NPM', 'b.Nama as NamaProgram', 'c.Nama as NamaProdi', 'a.BobotMasterID', 'a.TahunMasuk', 'a.ProdiID', 'a.StatusPindahan')
            ->join('program as b', 'a.ProgramID', '=', 'b.ID')
            ->join('programstudi as c', 'a.ProdiID', '=', 'c.ID')
            ->where('a.ID', $mhswID)
            ->first();

        if (!$dMhs) {
            return null;
        }

        // Get nilai bobot
        $nilaiBobot = DB::select("SELECT bobot.* FROM bobot
            INNER JOIN bobot_master ON bobot_master.ID = bobot.BobotMasterID
            INNER JOIN setting_pemberlakuan_bobot ON setting_pemberlakuan_bobot.BobotMasterID = bobot.BobotMasterID
            WHERE 1=1 AND ('$now' BETWEEN bobot_master.TanggalMulai AND bobot_master.TanggalSelesai)
            AND FIND_IN_SET('$dMhs->TahunMasuk', setting_pemberlakuan_bobot.TahunMasuk) != 0
            AND FIND_IN_SET('$dMhs->ProdiID', setting_pemberlakuan_bobot.ProdiID) != 0");

        return [
            'd_mhs' => $dMhs,
            'nilai_bobot' => $nilaiBobot,
            'MhswID' => $mhswID,
        ];
    }

    /**
     * Search KHS data for edit
     * Corresponds to: C_transkripmahasiswa->search_edit_khs()
     */
    public function search_edit_khs($mhswID, $tahunID)
    {
        // Use helper function view_khs
        if (function_exists('view_khs')) {
            return view_khs($mhswID, $tahunID);
        }
        return [];
    }

    /**
     * Get transkrip data (generate)
     * Corresponds to: C_transkripmahasiswa->getTranskrip()
     */
    public function generate_transkrip($mhswID, $type = 1, $userID = null)
    {
        $mahasiswa = DB::table('mahasiswa')->where('ID', $mhswID)->first();

        if (!$mahasiswa) {
            return [
                'status' => '0',
                'message' => 'Mahasiswa tidak ditemukan.'
            ];
        }

        $totalUpdate = 0;
        $totalInsert = 0;
        $createAt = date('Y-m-d H:i:s');

        // Get nilai data
        $listNilai = $this->get_data_nilai($mhswID, null);

        // Delete existing transkrip if type == 0
        if ($type == 0) {
            DB::table('transkrip')->where('NPM', $mahasiswa->NPM)->delete();
        }

        foreach ($listNilai as $dataNilai) {
            $nilai = $dataNilai->hurufNilai;
            $nilaiAkhir = $dataNilai->akhirNilai;

            if (empty($nilai)) {
                continue;
            }

            $getDetailkurikulum = DB::table('detailkurikulum')->where('ID', $dataNilai->matkulID)->first();
            $getTahun = DB::table('tahun')->where('ID', $dataNilai->TahunID)->first();

            if (!$getDetailkurikulum || !$getTahun) {
                continue;
            }

            $cekNilai = DB::table('transkrip')
                ->where('Program', $dataNilai->namaProgram)
                ->where('NamaProdi', $dataNilai->namaProdi)
                ->where('NPM', $dataNilai->npm)
                ->where('MKKode', $getDetailkurikulum->MKKode)
                ->where('TahunID', $getTahun->TahunID)
                ->first();

            $getBobot = $this->get_data_bobot($dataNilai->prodiID, $dataNilai->bobotMasterID, $nilai);

            if ($cekNilai) {
                // Update existing
                $update = [
                    'Bobot' => $getBobot->Bobot ?? 0,
                    'NilaiHuruf' => $nilai,
                    'NilaiBobot' => ($getBobot->Bobot ?? 0) * $getDetailkurikulum->TotalSKS,
                    'NilaiAkhir' => $nilaiAkhir,
                    'TglUpdate' => date('Y-m-d'),
                    'userID' => $userID ?? Session::get('UserID'),
                    'updateAt' => $createAt,
                ];

                DB::table('transkrip')->where('ID', $cekNilai->ID)->update($update);
                $totalUpdate++;
            } else {
                // Insert new
                if (!empty($getBobot->Bobot)) {
                    $insert = [
                        'TahunID' => $getTahun->TahunID,
                        'DetailkurikulumID' => $getDetailkurikulum->ID,
                        'MhswID' => $mahasiswa->ID,
                        'IDTahun' => $getTahun->ID,
                        'NamaTahun' => $getTahun->Nama,
                        'NamaProdi' => $dataNilai->namaProdi,
                        'NamaJenjang' => $dataNilai->namaJenjang,
                        'Program' => $dataNilai->namaProgram,
                        'Konsentrasi' => get_field($getDetailkurikulum->KonsentrasiID, 'konsentrasi'),
                        'TahunMasuk' => $dataNilai->tahunMasuk,
                        'Semester' => $getDetailkurikulum->Semester,
                        'NPM' => $dataNilai->npm,
                        'MKKode' => $getDetailkurikulum->MKKode,
                        'NamaMatakuliah' => $getDetailkurikulum->Nama,
                        'TotalSKS' => $getDetailkurikulum->TotalSKS,
                        'Bobot' => $getBobot->Bobot,
                        'NilaiBobot' => $getBobot->Bobot * $getDetailkurikulum->TotalSKS,
                        'NilaiHuruf' => $nilai,
                        'NilaiAkhir' => $nilaiAkhir,
                        'userID' => $userID ?? Session::get('UserID'),
                        'createAt' => $createAt,
                    ];

                    DB::table('transkrip')->insert($insert);
                    $totalInsert++;
                }
            }
        }

        if ($totalInsert > 0 || $totalUpdate > 0) {
            $alert = intval($totalInsert) . ' data nilai berhasil ditambahkan ke Transkrip !<br>';
            $alert .= intval($totalUpdate) . ' data berhasil diperbaharui ke Transkrip !';

            return [
                'status' => '1',
                'message' => $alert
            ];
        } else {
            return [
                'status' => '0',
                'message' => 'Mohon maaf tidak ada data nilai yang terpublish ke Transkrip.<br>Pastikan Nilai yang akan dipublish telah divalidasi.'
            ];
        }
    }

    /**
     * Generate KHS
     * Corresponds to: C_transkripmahasiswa->gen_khs()
     */
    public function generate_khs($mhswID, $tahunID, $type = 1, $userID = null)
    {
        $mahasiswa = DB::table('mahasiswa')->where('ID', $mhswID)->first();
        $getTahun = DB::table('tahun')->where('ID', $tahunID)->first();

        if (!$mahasiswa || !$getTahun) {
            return [
                'status' => '0',
                'message' => 'Data tidak valid.'
            ];
        }

        $totalUpdate = 0;
        $totalInsert = 0;
        $createAt = date('Y-m-d H:i:s');

        // Get nilai data
        $listNilai = $this->get_data_nilai($mhswID, $tahunID);

        // Delete existing KHS if type == 0
        if ($type == 0) {
            DB::table('nilai')
                ->where('NPM', $mahasiswa->NPM)
                ->where('TahunID', $getTahun->TahunID)
                ->delete();
        }

        foreach ($listNilai as $dataNilai) {
            $nilai = $dataNilai->hurufNilai;
            $nilaiAkhir = $dataNilai->akhirNilai;

            if (empty($nilai)) {
                continue;
            }

            $getDetailkurikulum = DB::table('detailkurikulum')->where('ID', $dataNilai->matkulID)->first();

            if (!$getDetailkurikulum) {
                continue;
            }

            $cekKhs = DB::table('nilai')
                ->where('Program', $dataNilai->namaProgram)
                ->where('NamaProdi', $dataNilai->namaProdi)
                ->where('NPM', $dataNilai->npm)
                ->where('MKKode', $getDetailkurikulum->MKKode)
                ->where('TahunID', $getTahun->TahunID)
                ->first();

            $getBobot = $this->get_data_bobot($dataNilai->prodiID, $dataNilai->bobotMasterID, $nilai);

            if ($cekKhs) {
                // Update existing
                $update = [
                    'Bobot' => $getBobot->Bobot ?? 0,
                    'NilaiHuruf' => $nilai,
                    'NilaiBobot' => ($getBobot->Bobot ?? 0) * $getDetailkurikulum->TotalSKS,
                    'NilaiAkhir' => $nilaiAkhir,
                    'userID' => $userID ?? Session::get('UserID'),
                    'updateAt' => $createAt,
                ];

                DB::table('nilai')->where('ID', $cekKhs->ID)->update($update);
                $totalUpdate++;
            } else {
                // Insert new
                if (!empty($getBobot->Bobot)) {
                    $insert = [
                        'TahunID' => $getTahun->TahunID,
                        'DetailkurikulumID' => $getDetailkurikulum->ID,
                        'MhswID' => $mahasiswa->ID,
                        'IDTahun' => $getTahun->ID,
                        'NamaTahun' => $getTahun->Nama,
                        'NamaProdi' => $dataNilai->namaProdi,
                        'NamaJenjang' => $dataNilai->namaJenjang,
                        'Program' => $dataNilai->namaProgram,
                        'Semester' => $getDetailkurikulum->Semester,
                        'NPM' => $dataNilai->npm,
                        'Nama' => $dataNilai->nama,
                        'MKKode' => $getDetailkurikulum->MKKode,
                        'NamaMatakuliah' => $getDetailkurikulum->Nama,
                        'TotalSKS' => $getDetailkurikulum->TotalSKS,
                        'Bobot' => $getBobot->Bobot,
                        'NilaiBobot' => $getBobot->Bobot * $getDetailkurikulum->TotalSKS,
                        'NilaiHuruf' => $nilai,
                        'NilaiAkhir' => $nilaiAkhir,
                        'createAt' => $createAt,
                        'userID' => $userID ?? Session::get('UserID'),
                    ];

                    DB::table('nilai')->insert($insert);
                    $totalInsert++;
                }
            }
        }

        if ($totalInsert > 0 || $totalUpdate > 0) {
            $alert = intval($totalInsert) . ' data nilai berhasil ditambahkan ke KHS !<br>';
            $alert .= intval($totalUpdate) . ' data berhasil diperbaharui ke KHS !';

            return [
                'status' => '1',
                'message' => $alert
            ];
        } else {
            return [
                'status' => '0',
                'message' => 'Mohon maaf tidak ada data nilai yang terpublish ke KHS.<br>Pastikan Nilai yang akan dipublish telah divalidasi.'
            ];
        }
    }

    /**
     * Get data nilai for mahasiswa
     * Helper function
     */
    private function get_data_nilai($mhswID, $tahunID = null)
    {
        $query = DB::table('nilai')
            ->select(
                'nilai.*',
                'nilai.NilaiHuruf as hurufNilai',
                'nilai.NilaiAkhir as akhirNilai',
                'nilai.DetailKurikulumID as matkulID',
                'nilai.TahunID',
                'mahasiswa.NPM as npm',
                'mahasiswa.Nama as nama',
                'mahasiswa.TahunMasuk as tahunMasuk',
                'mahasiswa.ProdiID as prodiID',
                'mahasiswa.BobotMasterID as bobotMasterID',
                'programstudi.Nama as namaProdi',
                'jenjang.Nama as namaJenjang',
                'program.Nama as namaProgram',
                'mahasiswa.KonsentrasiID as konsentrasiID'
            )
            ->join('mahasiswa', 'nilai.MhswID = mahasiswa.ID')
            ->join('programstudi', 'mahasiswa.ProdiID = programstudi.ID')
            ->join('jenjang', 'programstudi.JenjangID = jenjang.ID')
            ->join('program', 'mahasiswa.ProgramID = program.ID')
            ->where('nilai.MhswID', $mhswID)
            ->where('nilai.PublishTranskrip', '1')
            ->where('nilai.NilaiHuruf', '!=', '-')
            ->where('nilai.NilaiHuruf', '!=', 'T')
            ->where('nilai.NilaiHuruf', '!=', '');

        if ($tahunID) {
            $query->where('nilai.TahunID', $tahunID);
        }

        return $query->get();
    }

    /**
     * Get bobot data
     * Helper function
     */
    private function get_data_bobot($prodiID, $bobotMasterID, $nilai)
    {
        return DB::table('bobot')
            ->where('BobotMasterID', $bobotMasterID)
            ->where('Nilai', $nilai)
            ->first();
    }

    /**
     * Save transkrip single entry
     * Corresponds to: C_transkripmahasiswa->save()
     */
    public function save_transkrip($mhswID, $detailkurikulumid, $semester, $totalSKS, $nilaiHuruf, $userID = null)
    {
        $dMhs = DB::table('mahasiswa')->where('ID', $mhswID)->first();
        $dMk = DB::table('detailkurikulum')->where('ID', $detailkurikulumid)->first();

        if (!$dMhs || !$dMk) {
            return false;
        }

        $input = [
            'Semester' => $semester,
            'TotalSKS' => $totalSKS,
            'NilaiHuruf' => $nilaiHuruf,
            'namaMataKuliah' => $dMk->Nama,
            'NPM' => $dMhs->NPM,
            'MKKode' => $dMk->MKKode,
            'TglInput' => date('Y-m-d H:i:s'),
            'TglUpdate' => date('Y-m-d H:i:s'),
            'userID' => $userID ?? Session::get('UserID'),
        ];

        return DB::table('transkrip')->insert($input);
    }

    /**
     * Save KHS single entry
     * Corresponds to: C_transkripmahasiswa->save_khs()
     */
    public function save_khs($mhswID, $detailkurikulumid, $tahunID, $nilaiHuruf, $userID = null)
    {
        $dMhs = DB::table('mahasiswa as a')
            ->select('a.ID', 'a.Nama', 'a.NPM', 'b.Nama as NamaProgram', 'c.Nama as NamaProdi', 'd.Nama as NamaJenjang')
            ->join('program as b', 'a.ProgramID', '=', 'b.ID')
            ->join('programstudi as c', 'a.ProdiID', '=', 'c.ID')
            ->join('jenjang as d', 'c.JenjangID', '=', 'd.ID')
            ->where('a.ID', $mhswID)
            ->first();

        $dMk = DB::table('detailkurikulum')->where('ID', $detailkurikulumid)->first();
        $dThn = DB::table('tahun')->where('ID', $tahunID)->first();

        if (!$dMhs || !$dMk || !$dThn) {
            return false;
        }

        // Get max semester
        $maxSemester = DB::table('nilai')
            ->where('NPM', $dMhs->NPM)
            ->max('Semester');

        $sem = $maxSemester ? $maxSemester + 1 : 1;

        // Parse nilai (format: "A_4")
        $nilai = explode('_', $nilaiHuruf);
        $huruf = $nilai[0];
        $bobot = $nilai[1] ?? 0;

        $insert = [
            'TahunID' => $dThn->TahunID,
            'NamaTahun' => $dThn->Nama,
            'NamaProdi' => $dMhs->NamaProdi,
            'NamaJenjang' => $dMhs->NamaJenjang,
            'Semester' => $sem,
            'NPM' => $dMhs->NPM,
            'Nama' => $dMhs->Nama,
            'MKKode' => $dMk->MKKode,
            'NamaMatakuliah' => $dMk->Nama,
            'TotalSKS' => $dMk->TotalSKS,
            'NilaiHuruf' => $huruf,
            'Bobot' => $bobot,
            'NilaiBobot' => $dMk->TotalSKS * $bobot,
            'userID' => $userID ?? Session::get('UserID'),
            'createAt' => date('Y-m-d H:i:s'),
        ];

        return DB::table('nilai')->insert($insert);
    }

    /**
     * Update transkrip inline (single field)
     * Corresponds to: C_transkripmahasiswa->update()
     */
    public function update_transkrip_field($id, $param, $value)
    {
        $fieldMap = [
            1 => 'Semester',
            2 => 'MKKode',
            3 => 'namaMataKuliah',
            4 => 'TotalSKS',
        ];

        if (!isset($fieldMap[$param])) {
            return false;
        }

        return DB::table('transkrip')
            ->where('ID', $id)
            ->update([$fieldMap[$param] => $value]);
    }

    /**
     * Batch update transkrip (revision)
     * Corresponds to: C_transkripmahasiswa->saverevisinilai()
     */
    public function batch_update_transkrip($data)
    {
        $insertData = [];

        foreach ($data as $index => $row) {
            $insertData[] = [
                'ID' => $row['ID'],
                'Semester' => $row['Semester'] ?? null,
                'MKKode' => $row['MKKode'] ?? null,
                'NamaMataKuliah' => $row['NamaMataKuliah'] ?? null,
                'TotalSKS' => $row['TotalSKS'] ?? null,
                'NilaiHuruf' => $row['NilaiHuruf'] ?? null,
            ];
        }

        // Remove null values from each row
        $cleanData = array_map('array_filter', $insertData);

        // For batch update, we need to loop
        foreach ($cleanData as $row) {
            $id = $row['ID'];
            unset($row['ID']);
            DB::table('transkrip')->where('ID', $id)->update($row);
        }

        return count($cleanData);
    }

    /**
     * Batch update KHS (revision)
     * Corresponds to: C_transkripmahasiswa->saverevisinilaikhs()
     */
    public function batch_update_khs($data)
    {
        foreach ($data as $row) {
            $id = $row['ID'];
            $update = [
                'Semester' => $row['Semester'] ?? null,
                'MKKode' => $row['MKKode'] ?? null,
                'NamaMatakuliah' => $row['NamaMatakuliah'] ?? null,
                'TotalSKS' => $row['TotalSKS'] ?? null,
                'NilaiHuruf' => $row['NilaiHuruf'] ?? null,
                'Bobot' => $row['Bobot'] ?? null,
                'NilaiBobot' => $row['Bobot'] ?? null,
            ];

            // Remove null values
            $update = array_filter($update);

            DB::table('nilai')->where('ID', $id)->update($update);
        }

        return count($data);
    }

    /**
     * Delete transkrip
     * Corresponds to: C_transkripmahasiswa->delete()
     */
    public function delete_transkrip($checkid)
    {
        if (is_array($checkid)) {
            return DB::table('transkrip')->whereIn('ID', $checkid)->delete();
        }
        return DB::table('transkrip')->where('ID', $checkid)->delete();
    }

    /**
     * Delete KHS data
     * Corresponds to: C_transkripmahasiswa->deleteDataKHS()
     */
    public function delete_khs($checkid)
    {
        $totalRow = 0;

        if (is_array($checkid)) {
            foreach ($checkid as $value) {
                $cekData = DB::table('nilai')->where('ID', $value)->first();

                if ($cekData) {
                    DB::table('nilai')->where('ID', $value)->delete();
                    $totalRow += DB::getPdo()->rowCount();
                }
            }
        }

        return $totalRow;
    }

    /**
     * Get transkrip data for PDF/Excel print
     * Corresponds to: C_transkripmahasiswa->cetak() data preparation
     */
    public function get_transkrip_data_for_print($mhswID, $dataInput = [])
    {
        $row = DB::table('mahasiswa')->where('ID', $mhswID)->first();

        if (!$row) {
            return null;
        }

        $tahunAktif = DB::table('tahun')->where('ProsesBuka', '1')->first();

        // Update mahasiswa data
        $updMhs = [
            'TanggalLulus' => $dataInput['TanggalLulus'] ?? null,
            'NoIjazahNasional' => $dataInput['Nomor'] ?? null,
            'NoSeriIjazah' => $dataInput['nomorSeriIjazah'] ?? null,
            'NoTranskrip' => $dataInput['transkrip'] ?? null,
            'JudulSkripsi' => $dataInput['JudulSkripsi'] ?? null,
            'JudulSkripsi_eng' => $dataInput['JudulSkripsiEn'] ?? null,
            'TglCetakTranskripNilai' => $dataInput['tgl_cetak'] ?? null,
        ];

        DB::table('mahasiswa')->where('ID', $mhswID)->update(array_filter($updMhs));

        // Get transkrip data
        $setupTranskrip = get_setup_app("setup_cetak_transkrip");
        $transkripCustom = json_decode($setupTranskrip->metadata ?? '{}', true);

        $order = $transkripCustom['order_by'] ?? 'KelID,NamaMataKuliah,Semester,MKKode ASC';

        $sql = "SELECT nilai.*, detailkurikulum.KelID, detailkurikulum.NamaInggris 
                FROM nilai 
                LEFT JOIN detailkurikulum ON detailkurikulum.ID = nilai.DetailKurikulumID 
                WHERE MhswID = '" . $row->ID . "' 
                AND PublishTranskrip='1' 
                AND NilaiHuruf != '-' 
                AND NilaiHuruf !='T' 
                AND NilaiHuruf !='' 
                ORDER BY $order";

        $query = DB::select($sql);

        // Process data for transkrip
        $mkList = [];
        $mkList2 = [];
        $mkBobot = [];
        $listData = [];
        $listData2 = [];
        $countMk = [];
        $arrNum = [];

        $exceptNilai = [];

        $rowHideNilaiHuruf = get_setup_app("setup_hide_nilai_huruf");
        $metadataHideNilaiHuruf = json_decode($rowHideNilaiHuruf->metadata ?? '{}', true);

        if ($metadataHideNilaiHuruf && isset($metadataHideNilaiHuruf['hide_nilai_huruf'])) {
            $expHuruf = array_filter(explode(",", $metadataHideNilaiHuruf['hide_nilai_huruf']));
            if ($expHuruf) {
                $exceptNilai = $expHuruf;
            }
        }

        foreach ($query as $valAwal) {
            $countMk[$valAwal->MKKode][] = $valAwal->MKKode;

            if (!in_array($valAwal->NilaiHuruf, $exceptNilai)) {
                if (!in_array($valAwal->MKKode, $mkList)) {
                    $listData[$valAwal->MKKode] = $valAwal;
                    $mkList[] = $valAwal->MKKode;
                    $mkBobot[$valAwal->MKKode] = $valAwal->Bobot;
                } else if ($valAwal->Bobot > $mkBobot[$valAwal->MKKode]) {
                    unset($listData[$valAwal->MKKode]);
                    $listData[$valAwal->MKKode] = $valAwal;
                    $mkBobot[$valAwal->MKKode] = $valAwal->Bobot;
                }
            }
        }

        foreach ($countMk as $key => $value) {
            $arrNum[] = count($value);
        }

        $megulang = (array_sum($arrNum) > count($listData)) ? '1' : '2';

        foreach ($listData as $valAwal) {
            if (!in_array(strtolower($valAwal->NamaMataKuliah), $mkList2)) {
                $listData2[strtolower($valAwal->NamaMataKuliah)] = $valAwal;
                $mkList2[] = strtolower($valAwal->NamaMataKuliah);
                $mkBobot[strtolower($valAwal->NamaMataKuliah)] = $valAwal->Bobot;
            } else if ($valAwal->Bobot > $mkBobot[strtolower($valAwal->NamaMataKuliah)]) {
                unset($listData2[strtolower($valAwal->NamaMataKuliah)]);
                $listData2[strtolower($valAwal->NamaMataKuliah)] = $valAwal;
                $mkBobot[strtolower($valAwal->NamaMataKuliah)] = $valAwal->Bobot;
            }
        }

        if (isset($transkripCustom['transkrip_only_cek_MKKode']) && $transkripCustom['transkrip_only_cek_MKKode']) {
            $listData2 = $listData;
        }

        // Split data for 2-column layout
        $maxpoint = 0;
        $newtrans = [];
        $dataTranskripType3 = [];
        $dataTranskripType2 = [];
        $dataKelMatkulType2 = [];
        $nilaiMahasiswa = [];

        $sksTotalFinal = 0;
        $bobotTotalFinal = 0;

        foreach ($listData2 as $trans) {
            $dataTranskripType3[] = $trans;
            $dataTranskripType2[$trans->KelID ?? 0][] = $trans;
            $nilaiMahasiswa[$trans->NilaiHuruf] = $trans->NilaiHuruf;
            $dataKelMatkulType2[$trans->KelID ?? 0] = $trans->KelID ?? 0;

            $point = (strlen($trans->NamaMataKuliah) >= 56) ? 2 : 1;
            $maxpoint += $point;

            $trans->Point = $point;
            $newtrans[] = $trans;

            $sksTotalFinal += $trans->TotalSKS;
            $bobotTotalFinal += ($trans->TotalSKS * $trans->Bobot);
        }

        $tablepoint = 30;
        $roundcounttrans = 30;

        if (isset($transkripCustom['breakpoint']) && ($transkripCustom['breakpoint'] > 0 || $transkripCustom['breakpoint'] == '/2')) {
            if ($transkripCustom['breakpoint'] == '/2') {
                $angkaPembagi = (int)round(count($listData2) / 2);
                $tablepoint = $angkaPembagi;
                $roundcounttrans = $angkaPembagi;
            } else {
                $tablepoint = $transkripCustom['breakpoint'];
                $roundcounttrans = $transkripCustom['breakpoint'];
            }
        }

        $sumpoint = 0;
        $arrFirst = [];
        $arrSecond = [];
        $arrFirst2 = [];
        $arrSecond2 = [];

        foreach ($newtrans as $fetch) {
            if ($sumpoint < $tablepoint && count($arrFirst) < $roundcounttrans) {
                $arrFirst[] = $fetch;
                $arrFirst2[$fetch->KelID ?? 0][] = $fetch;
            } else {
                $arrSecond[] = $fetch;
                $arrSecond2[$fetch->KelID ?? 0][] = $fetch;
            }
            $sumpoint += $fetch->Point;
        }

        $result = [];
        if (count($arrFirst) >= count($arrSecond)) {
            foreach ($arrFirst as $key => $value) {
                $val = empty($arrSecond[$key]) ? [] : $arrSecond[$key];
                $result[$key] = [$value, $val];
            }
        } else {
            foreach ($arrSecond as $key => $value) {
                $val = empty($arrFirst[$key]) ? [] : $arrFirst[$key];
                $result[$key] = [$value, $val];
            }
        }

        // Get identitas
        $identitas = DB::table('identitas')->where('ID', 1)->first();

        // Get Dekan and KA info
        $prodi = DB::table('programstudi')->where('ID', $row->ProdiID)->first();
        $namaFakultas = get_field($prodi->FakultasID ?? 0, 'fakultas');
        $dekanID = get_field($prodi->FakultasID ?? 0, 'fakultas', 'dekan');

        $ka = DB::table('karyawan')->where('Jabatan1', '1')->first();
        $dekan = DB::table('karyawan')->where('ID', $dekanID)->first();

        // Calculate lama perkuliahan
        $lamaPerkuliahan = get_semester_tahunmasuk($row->TahunMasuk, $tahunAktif->TahunID ?? '', $row->SemesterMasuk);

        $lamaPerkuliahan2 = DB::table('keteranganstatusmahasiswa')
            ->where('StatusMahasiswaID', '1')
            ->where('MhswID', $row->ID)
            ->orderBy('ID', 'DESC')
            ->first();

        $lamaPerkuliahan2 = $lamaPerkuliahan2 ? get_semester_tahunmasuk($row->TahunMasuk, get_id($lamaPerkuliahan2->TahunID, 'tahun')->TahunID ?? '', $row->SemesterMasuk) : $lamaPerkuliahan;

        $exDate = explode("-", $dataInput['tgl_cetak'] ?? date('Y-m-d'));

        // Predikat logic
        $findInArray = [];
        foreach($nilaiMahasiswa as $val){
            $findInArray[] = "FIND_IN_SET('$val', predikatipk.NilaiList)";
        }
        $imNilai = !empty($findInArray) ? implode(" OR ", $findInArray) : '1=0';
        
        $ipkFinal = ($sksTotalFinal > 0) ? ($bobotTotalFinal / $sksTotalFinal) : 0;
        $ipkFormatted = number_format($ipkFinal, 2, '.', ',');

        $getPredikat = DB::table('predikatipk')
            ->where('RangeAwal', '<=', $ipkFinal)
            ->where('RangeAkhir', '>=', $ipkFinal)
            ->where(function($q) use ($row) {
                $q->whereRaw("FIND_IN_SET('{$row->ProdiID}', ProdiID)")
                  ->orWhereNull('ProdiID')
                  ->orWhere('ProdiID', '');
            })
            ->where(function($q) use ($lamaPerkuliahan) {
                $q->where('BatasPerkuliahan', '>=', $lamaPerkuliahan)
                  ->orWhere('BatasPerkuliahan', 0);
            })
            ->where(function($q) use ($row) {
                $q->where('JenisPendaftarID', $row->StatusPindahan)
                  ->orWhere('JenisPendaftarID', 0);
            })
            ->where('MKMengulang', $megulang)
            ->whereRaw("NOT ($imNilai)")
            ->orderBy('ID', 'DESC')
            ->first();

        return [
            'query' => $query,
            'data_transkrip' => $result,
            'datatranskrip_type3' => $dataTranskripType3,
            'datatranskrip_type2' => $dataTranskripType2,
            'datakelmatkul_type2' => $dataKelMatkulType2,
            'table_1' => $arrFirst,
            'table_2' => $arrSecond,
            'table_1_ver2' => $arrFirst2,
            'table_2_ver2' => $arrSecond2,
            'maxpoint' => $tablepoint,
            'max_tablerow' => (count($arrFirst) >= count($arrSecond)) ? count($arrFirst) : count($arrSecond),
            'transkrip_unproses' => $newtrans,
            'nilai_mahasiswa' => $nilaiMahasiswa,
            'megulang' => $megulang,
            'mhs' => $row,
            'MhswID' => $mhswID,
            'NPM' => $row->NPM,
            'Nama' => ucwords($row->Nama),
            'JenjangID' => get_field($row->JenjangID, 'jenjang'),
            'ProdiID' => get_field($row->ProdiID, 'programstudi'),
            'IDProdiID' => $row->ProdiID,
            'ProgramID' => $row->ProgramID,
            'TempatLahir' => ucwords(strtolower($row->TempatLahir)),
            'TanggalLahir' => $row->TanggalLahir,
            'NamaFakultas' => $namaFakultas,
            'jenjang' => get_id($prodi->JenjangID ?? 0, 'jenjang'),
            'NomorAkreditasi' => $prodi->NoSKBAN ?? '',
            'KA' => $ka ? $ka->Title . ' ' . $ka->Nama . ' ' . $ka->Gelar : '',
            'NIPKA' => $ka->NIP ?? '',
            'Dekan' => $dekan ? $dekan->Title . ' ' . $dekan->Nama . ' ' . $dekan->Gelar : '',
            'NIPDekan' => $dekan->NIP ?? '',
            'img' => $identitas->Gambar ?? '',
            'NoSKPT' => $identitas->NoSKPT ?? '',
            'TglSKPT' => $identitas->TglSKPT ?? '',
            'lama_perkuliahan' => $lamaPerkuliahan,
            'lama_perkuliahan2' => $lamaPerkuliahan2,
            'kota' => get_wilayah($identitas->KotaPT ?? 0),
            'TahunAktif' => $tahunAktif,
            'Tahun' => $tahunAktif,
            'valueQR' => $row->NPM . ($tahunAktif->TahunID ?? '') . "EDUEDU",
            'month' => number_to_romanic((int)($exDate[1] ?? date('m'))),
            'year' => (int)($exDate[0] ?? date('Y')),
            'tgl_cetak' => $dataInput['tgl_cetak'] ?? date('Y-m-d'),
            'Nomor' => $dataInput['nomor'] ?? '',
            'nomorSeriIjazah' => $dataInput['nomorSeriIjazah'] ?? '',
            'transkrip' => $dataInput['transkrip'] ?? '',
            'TanggalLulus' => $dataInput['TanggalLulus'] ?? '',
            'JudulSkripsi' => $dataInput['JudulSkripsi'] ?? '',
            'JudulSkripsiEn' => $dataInput['JudulSkripsiEn'] ?? '',
            'breakpoint' => $tablepoint,
            'skstotal' => $sksTotalFinal,
            'ipk_hitung_titik' => $ipkFormatted,
            'predikat' => $getPredikat->Predikat ?? '',
        ];
    }

    /**
     * Get KHS data for batch PDF print
     * Corresponds to: C_transkripmahasiswa->cetak_all() data preparation
     */
    public function get_khs_batch_data($programID, $prodiID, $tahunMasuk, $tahunID)
    {
        $dataMahasiswa = DB::table('mahasiswa')
            ->select('ID')
            ->where('ProdiID', $prodiID)
            ->where('TahunMasuk', $tahunMasuk)
            ->where('ProgramID', $programID)
            ->get();

        $dataMhsArr = [];
        $date = date('Y-m-d');
        $tglCetak = $date;

        foreach ($dataMahasiswa as $dataMhsw) {
            $row = DB::table('mahasiswa')->where('ID', $dataMhsw->ID)->first();

            $bobotMaster = DB::table('setting_pemberlakuan_bobot')
                ->join('bobot_master', 'bobot_master.ID', '=', 'setting_pemberlakuan_bobot.BobotMasterID')
                ->whereRaw("FIND_IN_SET('$row->ProdiID', ProdiID)")
                ->whereRaw("FIND_IN_SET('$row->TahunMasuk', TahunMasuk)")
                ->whereRaw("'$date' BETWEEN DATE(TanggalMulai) AND DATE(TanggalSelesai)")
                ->select('bobot_master.BobotMasterID')
                ->first();

            $bobot = [];
            if ($bobotMaster) {
                $bobot = DB::table('bobot')
                    ->where('BobotMasterID', $bobotMaster->BobotMasterID)
                    ->orderBy('Urut', 'asc')
                    ->get();
            }

            if (function_exists('view_khs')) {
                $queryKhs = view_khs($dataMhsw->ID, $tahunID);
            } else {
                $queryKhs = [];
            }

            if (function_exists('view_ips')) {
                $ips = view_ips($dataMhsw->ID, $tahunID);
            } else {
                $ips = 0;
            }

            if (function_exists('view_ipk')) {
                $ipk = view_ipk($dataMhsw->ID, $tahunID);
            } else {
                $ipk = 0;
            }

            if (function_exists('get_semester_khs')) {
                $semester = get_semester_khs($dataMhsw->ID, $tahunID);
            } else {
                $semester = 1;
            }

            $prodi = DB::table('programstudi')->where('ID', $row->ProdiID)->first();
            $kaProdi = $prodi ? DB::table('dosen')->where('ID', $prodi->KaProdiID)->first() : null;

            $khsArr = [
                'tgl_cetak' => $tglCetak,
                'grade_nilai' => $bobot,
                'MhswID' => $dataMhsw->ID,
                'TahunID' => $tahunID,
                'Tahun' => DB::table('tahun')->where('ID', $tahunID)->first(),
                'NPM' => $row->NPM,
                'Nama' => ucwords($row->Nama),
                'ProdiID' => $prodi->Nama ?? '',
                'NamaFakultas' => get_field($prodi->FakultasID ?? 0, 'fakultas') ?? '',
                'Dekan' => get_field($prodi->FakultasID ?? 0, 'fakultas', 'Dekan') ?? '',
                'IDProdiID' => $row->ProdiID,
                'ProgramID' => $row->ProgramID,
                'JenjangID' => $row->JenjangID,
                'TempatLahir' => ucwords(strtolower($row->TempatLahir)),
                'TanggalLahir' => $row->TanggalLahir,
                'TahunMasuk' => $row->TahunMasuk,
                'PEMBID' => $row->PembimbingID,
                'query' => $queryKhs,
                'ips' => $ips,
                'prodi' => $prodi,
                'kaProdi' => $kaProdi,
                'ipk' => $ipk,
                'semester' => $semester,
            ];

            $dataMhsArr[$dataMhsw->ID] = $khsArr;
        }

        return [
            'data_mahasiswa' => $dataMhsArr,
            'valueQR' => !empty($dataMhsArr) ? array_values($dataMhsArr)[0]['NPM'] . ($dataMhsArr[array_key_first($dataMhsArr)]['Tahun']->TahunID ?? '') . "EDUEDU" : '',
        ];
    }

    /**
     * Process upload Excel for nomor transkrip
     * Corresponds to: C_transkripmahasiswa->upload_excel_nomor()
     */
    public function process_upload_nomor_transkrip($filePath)
    {
        if (!file_exists($filePath)) {
            return [
                'status' => false,
                'title' => "Data Gagal Diproses",
                'message' => "File tidak ditemukan",
                'type' => 'error',
                'Persen' => 0
            ];
        }

        // Read Excel file using PhpSpreadsheet
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Skip header row
        array_shift($rows);

        $total = 0;
        $berhasil = 0;
        $gagalData = [];

        foreach ($rows as $index => $row) {
            // Skip empty rows
            if (empty($row[0])) {
                continue;
            }

            $total++;
            $npm = (string) $row[0];
            $noIjazah = $row[1] ?? '';
            $noSeriIjazah = $row[2] ?? '';
            $noTranskrip = $row[3] ?? '';
            $judulSkripsi = $row[4] ?? '';
            $judulSkripsiEng = $row[5] ?? '';
            $tanggalYudisium = $row[6] ?? '';
            $tanggalCetak = $row[7] ?? '';

            // Validate date formats
            $validTanggalYudisium = $this->validate_and_format_date($tanggalYudisium);
            $validTanggalCetak = $this->validate_and_format_date($tanggalCetak);

            $errors = [];

            // Check NPM exists
            $mahasiswa = DB::table('mahasiswa')->where('NPM', $npm)->first();
            if (!$mahasiswa) {
                $errors[] = 'Isian NPM tidak ditemukan';
            }

            if (!$validTanggalYudisium && !empty($tanggalYudisium)) {
                $errors[] = 'Isian Tanggal Yudisium Tidak Sesuai';
            }

            if (!$validTanggalCetak && !empty($tanggalCetak)) {
                $errors[] = 'Isian Tanggal Cetak Tidak Sesuai';
            }

            if (!empty($errors)) {
                $gagalData[] = [
                    'row' => $index + 2,
                    'npm' => $npm,
                    'errors' => $errors,
                    'data' => $row
                ];
                continue;
            }

            // Update mahasiswa data
            $input = [
                'NPM' => $npm,
                'NoIjazahNasional' => $noIjazah,
                'NoSeriIjazah' => $noSeriIjazah,
                'NoTranskrip' => $noTranskrip,
                'JudulSkripsi' => $judulSkripsi,
                'JudulSkripsi_eng' => $judulSkripsiEng,
            ];

            if ($validTanggalYudisium) {
                $input['TanggalLulus'] = $validTanggalYudisium;
            }

            if ($validTanggalCetak) {
                $input['TglCetakTranskripNilai'] = $validTanggalCetak;
            }

            DB::table('mahasiswa')->where('NPM', $npm)->update(array_filter($input));
            $berhasil++;
        }

        // Clean up uploaded file
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $persen = $total > 0 ? number_format($berhasil / $total * 100, 2) : 0;

        if ($total == 0) {
            return [
                'status' => false,
                'title' => "Data Gagal Diproses",
                'message' => "Tidak Ada Data yang Diproses",
                'type' => 'error',
                'Persen' => 0,
                'gagalData' => []
            ];
        }

        if ($berhasil >= $total) {
            return [
                'status' => true,
                'title' => "Data Berhasil Diproses",
                'message' => "Tersimpan " . $berhasil . " Data",
                'type' => 'success',
                'Persen' => $persen,
                'gagalData' => []
            ];
        } else if ($berhasil > 0) {
            return [
                'status' => false,
                'title' => "Data Berhasil Diproses Sebagian",
                'message' => "Tersimpan " . $berhasil . " Data, Gagal " . ($total - $berhasil) . " Data. Silahkan Coba Upload Kembali",
                'type' => 'warning',
                'Persen' => $persen,
                'gagalData' => $gagalData
            ];
        } else {
            return [
                'status' => false,
                'title' => "Data Gagal Diproses",
                'message' => ($total - $berhasil) . " Data Gagal Tersimpan. Silahkan Coba Upload Kembali",
                'type' => 'error',
                'Persen' => $persen,
                'gagalData' => $gagalData
            ];
        }
    }

    /**
     * Helper: Validate and format date
     */
    private function validate_and_format_date($dateStr)
    {
        if (empty($dateStr)) {
            return null;
        }

        // Try standard format
        $tempDate = str_replace("/", "-", $dateStr);
        if ($this->is_valid_date($tempDate)) {
            return date('Y-m-d', strtotime($tempDate));
        }

        // Try Excel serial date format
        if (is_numeric($dateStr)) {
            $excelDate = (int) $dateStr;
            if ($excelDate > 0) {
                $unixDate = ($excelDate - 25569) * 86400;
                $gmDate = gmdate("Y-m-d", $unixDate);
                if ($this->is_valid_date($gmDate)) {
                    return $gmDate;
                }
            }
        }

        return null;
    }

    /**
     * Helper: Check if date is valid
     */
    private function is_valid_date($dateStr)
    {
        if (empty($dateStr)) {
            return false;
        }

        $d = DateTime::createFromFormat('Y-m-d', $dateStr);
        return $d && $d->format('Y-m-d') === $dateStr;
    }
}
