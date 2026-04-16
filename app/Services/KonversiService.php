<?php

namespace App\Services;

use App\Models\Konversi;
use App\Models\KonversiDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class KonversiService
{
    /**
     * Get konversi data with filters
     * Corresponds to: C_konversi->search() and C_konversi->index()
     */
    public function get_data($limit = null, $offset = null, $programID = '', $prodiID = '', $tahunMasuk = '', $semesterMasuk = '', $keyword = '')
    {
        $query = DB::table('konversi');
        $query->select(
            'konversi.ID',
            'konversi.KodeKonversi',
            'konversi.MhswID',
            'konversi.Alasan',
            'konversi.TotalMKAsal',
            'konversi.TotalSKSAsal',
            'konversi.TotalMKTujuan',
            'konversi.TotalSKSTujuan',
            'konversi.statuskonversi',
            'konversi.internal',
            'konversi.create_at',
            'mahasiswa.NPM',
            'mahasiswa.Nama',
            'mahasiswa.ProgramID',
            'mahasiswa.ProdiID'
        );
        $query->join('mahasiswa', 'konversi.MhswID', '=', 'mahasiswa.ID');

        if (!empty($programID)) {
            $query->where('mahasiswa.ProgramID', $programID);
        }

        if (!empty($prodiID)) {
            $query->where('mahasiswa.ProdiID', $prodiID);
        }

        if (!empty($tahunMasuk)) {
            $query->where('mahasiswa.TahunMasuk', $tahunMasuk);
        }

        if (!empty($semesterMasuk)) {
            $query->where('mahasiswa.SemesterMasuk', $semesterMasuk);
        }

        if (!empty($keyword)) {
            $query->where(function($q) use ($keyword) {
                $q->where('mahasiswa.NPM', 'LIKE', "%{$keyword}%")
                  ->orWhere('mahasiswa.Nama', 'LIKE', "%{$keyword}%")
                  ->orWhere('konversi.KodeKonversi', 'LIKE', "%{$keyword}%");
            });
        }

        $query->orderBy('konversi.ID', 'DESC');

        if ($limit !== null) {
            $query->take($limit);
        }
        if ($offset !== null) {
            $query->skip($offset);
        }

        $results = $query->get();

        // Convert to array of objects to maintain consistency with CI3
        return $results->map(function($item) {
            return (object) (array) $item;
        })->toArray();
    }

    /**
     * Count all konversi data with filters
     * Corresponds to: C_konversi->search() counData
     */
    public function count_all($programID = '', $prodiID = '', $tahunMasuk = '', $semesterMasuk = '', $keyword = '')
    {
        $query = DB::table('konversi');
        $query->join('mahasiswa', 'konversi.MhswID', '=', 'mahasiswa.ID');

        if (!empty($programID)) {
            $query->where('mahasiswa.ProgramID', $programID);
        }

        if (!empty($prodiID)) {
            $query->where('mahasiswa.ProdiID', $prodiID);
        }

        if (!empty($tahunMasuk)) {
            $query->where('mahasiswa.TahunMasuk', $tahunMasuk);
        }

        if (!empty($semesterMasuk)) {
            $query->where('mahasiswa.SemesterMasuk', $semesterMasuk);
        }

        if (!empty($keyword)) {
            $query->where(function($q) use ($keyword) {
                $q->where('mahasiswa.NPM', 'LIKE', "%{$keyword}%")
                  ->orWhere('mahasiswa.Nama', 'LIKE', "%{$keyword}%")
                  ->orWhere('konversi.KodeKonversi', 'LIKE', "%{$keyword}%");
            });
        }

        return $query->count('konversi.ID');
    }

    /**
     * Get single konversi by ID
     * Corresponds to: C_konversi->view() and model->get_id()
     */
    public function get_id($id)
    {
        return DB::table('konversi')
            ->select(
                'konversi.*',
                'mahasiswa.NPM',
                'mahasiswa.Nama',
                'mahasiswa.ProgramID',
                'mahasiswa.ProdiID',
                'mahasiswa.KurikulumID',
                'mahasiswa.TahunMasuk'
            )
            ->join('mahasiswa', 'konversi.MhswID', '=', 'mahasiswa.ID')
            ->where('konversi.ID', $id)
            ->first();
    }

    /**
     * Delete konversi by ID(s)
     * Corresponds to: C_konversi->delete()
     */
    public function delete($checkid)
    {
        if (is_array($checkid)) {
            return DB::table('konversi')->whereIn('ID', $checkid)->delete();
        }
        return DB::table('konversi')->where('ID', $checkid)->delete();
    }

    /**
     * Delete konversi_detail by ID(s)
     * Corresponds to: C_konversi->delete_detail()
     */
    public function delete_detail($checkid)
    {
        if (is_array($checkid)) {
            return DB::table('konversi_detail')->whereIn('ID', $checkid)->delete();
        }
        return DB::table('konversi_detail')->where('ID', $checkid)->delete();
    }

    /**
     * Get konversi detail data by KonversiID
     * Corresponds to: C_konversi->json_konversi()
     */
    public function get_konversi_detail($konversiID)
    {
        $query = DB::table('konversi_detail');
        $query->select(
            'detailkurikulum.MKKode',
            'detailkurikulum.Nama as NamaMK',
            'konversi_detail.ID as IDDetail',
            'konversi_detail.MKKodeAsal',
            'konversi_detail.NamaMKAsal',
            'konversi_detail.SKSAsal',
            'konversi_detail.NilaiAsal',
            'konversi_detail.DetailkurikulumID',
            'konversi_detail.NilaiKonversi',
            'konversi_detail.generate',
            DB::raw('IF(konversi_detail.Semester IS NULL or konversi_detail.Semester = "", detailkurikulum.Semester, konversi_detail.Semester) as Semester')
        );
        $query->join('detailkurikulum', 'konversi_detail.DetailkurikulumID', '=', 'detailkurikulum.ID');
        $query->where('konversi_detail.KonversiID', $konversiID);

        $results = $query->get();

        return $results->map(function($item) {
            return (object) (array) $item;
        })->toArray();
    }

    /**
     * Get nilai data for mahasiswa
     * Corresponds to: C_konversi->json_nilai()
     */
    public function get_nilai_for_mahasiswa($mhswID)
    {
        if (empty($mhswID)) {
            return [];
        }

        $npm = DB::table('mahasiswa')->where('ID', $mhswID)->value('NPM');

        // Get nilai data
        $sql = "SELECT nilai.*, nilai.NamaMatakuliah as NamaMK 
                FROM nilai 
                WHERE MhswID = '" . $mhswID . "' 
                ORDER BY Semester, MKKode ASC";
        $queryNilai = DB::select($sql);

        // Get KRS data
        $sqlKrs = "SELECT rencanastudi.ID, detailkurikulum.MKKode, detailkurikulum.Nama as NamaMK, 
                          detailkurikulum.TotalSKS, '0' as Bobot, '0' as TotalBobot, 
                          '0' as NilaiAkhir, '-' as NilaiHuruf, tahun.TahunID as TahunID 
                   FROM rencanastudi 
                   INNER JOIN tahun ON tahun.ID = rencanastudi.TahunID 
                   INNER JOIN detailkurikulum ON detailkurikulum.ID = rencanastudi.DetailKurikulumID 
                   WHERE rencanastudi.MhswID = '$mhswID' 
                   GROUP BY rencanastudi.ID 
                   ORDER BY detailkurikulum.Semester, detailkurikulum.MKKode ASC";
        $queryKrs = DB::select($sqlKrs);

        // Merge both arrays
        $query = array_merge($queryNilai, $queryKrs);

        // Process to get unique MK with highest bobot
        $mkList = [];
        $mkList2 = [];
        $mkBobot = [];
        $listData = [];
        $listData2 = [];

        foreach ($query as $valAwal) {
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

        foreach ($listData as $valAwal) {
            if (!in_array($valAwal->NamaMK, $mkList2)) {
                $listData2[$valAwal->NamaMK] = $valAwal;
                $mkList2[] = $valAwal->NamaMK;
                $mkBobot[$valAwal->NamaMK] = $valAwal->Bobot;
            } else if ($valAwal->Bobot > $mkBobot[$valAwal->NamaMK]) {
                unset($listData2[$valAwal->NamaMK]);
                $listData2[$valAwal->NamaMK] = $valAwal;
                $mkBobot[$valAwal->MKKode] = $valAwal->Bobot;
            }
        }

        // Format output
        $data = [];
        foreach ($listData2 as $konv) {
            $row = [
                'IDDetail' => null,
                'MKKodeAsal' => $konv->MKKode,
                'NamaMKAsal' => $konv->NamaMK,
                'SKSAsal' => $konv->TotalSKS,
                'NilaiAsal' => $konv->NilaiHuruf,
                'MKKode' => null,
                'NilaiKonversi' => null,
                'DetailkurikulumID' => null,
                'NamaMK' => null,
                'Semester' => null,
            ];
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Search mata kuliah for dropdown
     * Corresponds to: C_konversi->json_mk()
     */
    public function search_mata_kuliah($programID, $prodiID, $kurikulumID, $search = '', $page = 0)
    {
        $limit = 30;
        $offset = $limit * $page;

        if (empty($programID)) {
            return [
                'total_count' => 0,
                'items' => [['id' => '', 'text' => 'Harap Set Program Kuliah Mahasiswa terlebih dahulu ']]
            ];
        }

        if (empty($prodiID)) {
            return [
                'total_count' => 0,
                'items' => [['id' => '', 'text' => 'Harap Set Prodi Mahasiswa terlebih dahulu ']]
            ];
        }

        if (empty($kurikulumID)) {
            return [
                'total_count' => 0,
                'items' => [['id' => '', 'text' => 'Harap Set Kurikulum Mahasiswa ini terlebih dahulu']]
            ];
        }

        $query = DB::table('detailkurikulum');
        $query->select('detailkurikulum.ID', 'detailkurikulum.MKKode', 'detailkurikulum.Nama', 'detailkurikulum.TotalSKS');
        $query->where('ProgramID', $programID);
        $query->where('ProdiID', $prodiID);
        $query->where('KurikulumID', $kurikulumID);

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('detailkurikulum.MKKode', 'LIKE', "%{$search}%")
                  ->orWhere('detailkurikulum.Nama', 'LIKE', "%{$search}%");
            });
        }

        $query->orderBy('detailkurikulum.MKKode', 'ASC');

        $total = DB::table('detailkurikulum')
            ->where('ProgramID', $programID)
            ->where('ProdiID', $prodiID)
            ->where('KurikulumID', $kurikulumID);

        if (!empty($search)) {
            $total->where(function($q) use ($search) {
                $q->where('detailkurikulum.MKKode', 'LIKE', "%{$search}%")
                  ->orWhere('detailkurikulum.Nama', 'LIKE', "%{$search}%");
            });
        }

        $tot = $total->count();

        $mk = $query->take($limit)->skip($offset)->get();

        if ($mk->count() > 0) {
            $data = [];
            foreach ($mk as $value) {
                $data[] = [
                    'id' => $value->ID,
                    'text' => $value->MKKode . ' | ' . $value->Nama . ' | ' . $value->TotalSKS . ' SKS',
                ];
            }
        } else {
            $data = [['id' => '', 'text' => 'Mata Kuliah tidak ditemukan.']];
        }

        return [
            'total_count' => $tot,
            'items' => $data
        ];
    }

    /**
     * Check if NPM exists
     * Corresponds to: C_konversi->cek_npm()
     */
    public function check_npm_exists($npm)
    {
        $count = DB::table('mahasiswa')->where('NPM', $npm)->count();
        return $count > 0;
    }

    /**
     * Get bobot values for a student
     * Corresponds to: C_konversi->change_bobot()
     */
    public function get_bobot_values($npm = '', $asArray = false)
    {
        $prodiID = null;
        $tahunMasuk = null;

        if (!empty($npm)) {
            $mahasiswa = DB::table('mahasiswa')->where('NPM', $npm)->first();
            if ($mahasiswa) {
                $prodiID = $mahasiswa->ProdiID;
                $tahunMasuk = $mahasiswa->TahunMasuk;
            }
        }

        if (empty($prodiID) || empty($tahunMasuk)) {
            if ($asArray) {
                return [];
            }
            return [
                'status' => '0',
                'data' => '<option value="" selected>Maaf grade nilai untuk Prodi dan Tahun Masuk belum disetting</option>'
            ];
        }

        $now = date('Y-m-d');

        $where = "AND ( '$now' between bobot_master.TanggalMulai and bobot_master.TanggalSelesai )";
        $where .= ' AND FIND_IN_SET("' . $tahunMasuk . '", setting_pemberlakuan_bobot.TahunMasuk) != 0';
        $where .= ' AND FIND_IN_SET("' . $prodiID . '", setting_pemberlakuan_bobot.ProdiID) != 0';

        $sql = "SELECT bobot.* FROM bobot
            INNER JOIN bobot_master on bobot_master.ID = bobot.BobotMasterID
            INNER JOIN setting_pemberlakuan_bobot on setting_pemberlakuan_bobot.BobotMasterID = bobot.BobotMasterID
            WHERE 1=1 " . $where;

        $result = DB::select($sql);

        if (count($result) > 0) {
            if ($asArray) {
                $nilaiBobot = [];
                foreach ($result as $dataBobot) {
                    $nilaiBobot[$dataBobot->Nilai] = $dataBobot->Bobot;
                }
                return $nilaiBobot;
            }
            return [
                'status' => '1',
                'data' => $result
            ];
        } else {
            if ($asArray) {
                return [];
            }
            return [
                'status' => '0',
                'data' => '<option value="" selected>Maaf grade nilai untuk Prodi ' . get_field($prodiID, 'programstudi') . ' dan Tahun Masuk ' . $tahunMasuk . ' belum disetting</option>'
            ];
        }
    }

    /**
     * Get single bobot value
     * Corresponds to: C_konversi->nilai_bobot()
     */
    public function get_nilai_bobot($npm = '', $bobotNilai = '')
    {
        $prodiID = null;
        $tahunMasuk = null;

        if (!empty($npm)) {
            $mahasiswa = DB::table('mahasiswa')->where('NPM', $npm)->first();
            if ($mahasiswa) {
                $prodiID = $mahasiswa->ProdiID;
                $tahunMasuk = $mahasiswa->TahunMasuk;
            }
        }

        $now = date('Y-m-d');

        $where = "AND ( '$now' between bobot_master.TanggalMulai and bobot_master.TanggalSelesai )";

        if (!empty($tahunMasuk)) {
            $where .= ' AND FIND_IN_SET("' . $tahunMasuk . '", setting_pemberlakuan_bobot.TahunMasuk) != 0';
        }
        if (!empty($prodiID)) {
            $where .= ' AND FIND_IN_SET("' . $prodiID . '", setting_pemberlakuan_bobot.ProdiID) != 0';
        }

        $sql = "SELECT bobot.* FROM bobot
            INNER JOIN bobot_master on bobot_master.ID = bobot.BobotMasterID
            INNER JOIN setting_pemberlukan_bobot on setting_pemberlakuan_bobot.BobotMasterID = bobot.BobotMasterID
            WHERE 1=1 AND bobot.Nilai = '$bobotNilai' " . $where;

        $result = DB::select($sql);

        return !empty($result) ? $result[0]->Bobot : 0;
    }

    /**
     * Get mahasiswa parameters
     * Corresponds to: C_konversi->get_param_mahasiswa()
     */
    public function get_mahasiswa_param($npm)
    {
        $mhsw = DB::table('mahasiswa')
            ->select('ID', 'NPM', 'Nama', 'TahunMasuk', 'KurikulumID', 'ProdiID', 'ProgramID')
            ->where('NPM', $npm)
            ->first();

        if (!$mhsw) {
            return null;
        }

        return [
            'KurikulumID' => $mhsw->KurikulumID,
            'ProdiID' => $mhsw->ProdiID,
            'ProgramID' => $mhsw->ProgramID,
        ];
    }

    /**
     * Change semester lookup
     * Corresponds to: C_konversi->changeSemester()
     */
    public function change_semester($detailKurikulumID)
    {
        $detailkurikulum = DB::table('detailkurikulum')->where('ID', $detailKurikulumID)->first();
        return $detailkurikulum ? $detailkurikulum->Semester : null;
    }

    /**
     * Get data for PDF single konversi
     * Corresponds to: C_konversi->cetakNilaiKonversi()
     */
    public function get_data_for_pdf($id)
    {
        // Data Perguruan Tinggi
        $identitas = DB::table('identitas')->first();

        // Data Mahasiswa
        $dataMahasiswa = DB::table('konversi')
            ->select(
                'mahasiswa.ID',
                'mahasiswa.NPM',
                'mahasiswa.Nama',
                'fakultas.Nama as Fakultas',
                'programstudi.Nama as Prodi',
                'mahasiswa.AsalPT as AsalPT',
                'mahasiswa.ProdiIDPT',
                'konversi.TotalMKAsal',
                'konversi.TotalSKSAsal',
                'konversi.TotalMKTujuan',
                'konversi.TotalSKSTujuan'
            )
            ->join('mahasiswa', 'konversi.MhswID', '=', 'mahasiswa.ID')
            ->join('programstudi', 'mahasiswa.ProdiID', '=', 'programstudi.ID')
            ->join('fakultas', 'programstudi.FakultasID', '=', 'fakultas.ID')
            ->where('konversi.ID', $id)
            ->first();

        $mahasiswa = DB::table('mahasiswa')->where('ID', $dataMahasiswa->ID)->first();

        // Data Ketua Prodi
        $dataKaProdi = DB::table('programstudi')
            ->select('dosen.NIP', 'dosen.Nama', 'dosen.Title', 'dosen.Gelar')
            ->join('dosen', 'programstudi.KaProdiID', '=', 'dosen.ID')
            ->where('programstudi.ID', $mahasiswa->ProdiID)
            ->first();

        // Data Matakuliah Konversi
        $konvDetail = DB::table('konversi_detail')
            ->select(
                'konversi_detail.ID as detailID',
                'konversi_detail.MKKodeAsal as mkkodeAsal',
                'konversi_detail.NamaMKAsal as namaMKAsal',
                'konversi_detail.SKSAsal as sksAsal',
                'konversi_detail.NilaiAsal as nilaiAsal',
                'konversi_detail.DetailkurikulumID as idMatkul',
                'konversi_detail.NilaiKonversi as nilaiKonversi',
                'konversi_detail.generate',
                'detailkurikulum.MKKode as mkkode',
                'detailkurikulum.TotalSKS as sks',
                'detailkurikulum.Nama as namaMK'
            )
            ->join('detailkurikulum', 'konversi_detail.DetailkurikulumID', '=', 'detailkurikulum.ID')
            ->where('konversi_detail.KonversiID', $id)
            ->get();

        // Convert to array of objects
        $konvDetail = $konvDetail->map(function($item) {
            return (object) (array) $item;
        })->toArray();

        // Asal PT and Prodi
        $asalPT = DB::table('ref_pt')->where('KodePT', $dataMahasiswa->AsalPT)->first();
        $asalProdi = DB::table('ref_programstudi')
            ->where('KodePT', $dataMahasiswa->AsalPT)
            ->where('KodeProdi', $dataMahasiswa->ProdiIDPT)
            ->first();

        // Data Matkul
        $dataMatkul = DB::table('detailkurikulum')
            ->select(DB::raw('count(ID) as totalData, sum(TotalSKS) as totalSKS'))
            ->where('ProdiID', $mahasiswa->ProdiID)
            ->where('KurikulumID', $mahasiswa->KurikulumID)
            ->first();

        $dataKurikulum = DB::table('kurikulum')->where('ID', $mahasiswa->KurikulumID)->first();

        return [
            'data' => $konvDetail,
            'mahasiswa' => $dataMahasiswa,
            'identitas' => $identitas,
            'kaProdi' => $dataKaProdi,
            'asalPT' => $asalPT->NamaPT ?? '',
            'asalProdi' => $asalProdi->NamaProdi ?? '',
            'dataMatkul' => $dataMatkul,
            'dataKurikulum' => $dataKurikulum,
        ];
    }

    /**
     * Generate konversi to nilai
     * Corresponds to: C_konversi->genKonversi()
     */
    public function gen_konversi($konversiID, $userID = null, $echoReturn = true)
    {
        $thn = DB::table('tahun')->where('ProsesBuka', 1)->first();
        $tahunMasuk = substr($thn->TahunID ?? '', 0, 4);

        $konversi = DB::table('konversi')->where('ID', $konversiID)->first();
        $mhsw = DB::table('mahasiswa')
            ->select('ID', 'NPM', 'Nama', 'TahunMasuk', 'ProdiID')
            ->where('ID', $konversi->MhswID)
            ->first();

        // Insert log
        $insLog = [
            'KonversiID' => $konversi->ID,
            'MhswID' => $mhsw->ID,
            'NPM' => $mhsw->NPM,
            'Jenis' => 'konversikan',
            'Internal' => $konversi->internal,
            'createdAt' => date('Y-m-d H:i:s'),
            'UserID' => $userID ?? Session::get('UserID'),
        ];
        DB::table('log_konversi')->insert($insLog);

        $now = date('Y-m-d');

        $where = "AND ( '$now' between bobot_master.TanggalMulai and bobot_master.TanggalSelesai )";
        $where .= 'AND FIND_IN_SET("' . $mhsw->TahunMasuk . '", setting_pemberlakuan_bobot.TahunMasuk)';
        $where .= 'AND FIND_IN_SET("' . $mhsw->ProdiID . '", setting_pemberlakuan_bobot.ProdiID)';

        $sql = "SELECT bobot.* FROM bobot
            INNER JOIN bobot_master on bobot_master.ID=bobot.BobotMasterID
            INNER JOIN setting_pemberlakuan_bobot on setting_pemberlakuan_bobot.BobotMasterID=bobot.BobotMasterID
            WHERE 1=1 " . $where . " GROUP BY bobot_master.ID";
        $bobotResult = DB::select($sql);
        $bobotResult = !empty($bobotResult) ? $bobotResult[0] : null;

        if (!$bobotResult) {
            return ['status' => '0', 'message' => 'Maaf bobot nilai tidak ditemukan !.', 'count' => 0];
        }

        $qkonversi = "
            SELECT
            NULL AS ID,
            NULL AS TahunID,
            NULL AS NamaTahun,
            programstudi.Nama AS NamaProdi,
            programstudi.ProdiID AS ProdiID,
            jenjang.Nama AS NamaJenjang,
            program.Nama AS Program,
            program.ID AS ProgramID,
            mahasiswa.NPM,
            mahasiswa.ID as MhswID,
            mahasiswa.Nama as NamaMahasiswa,
            mahasiswa.ProdiID as ProdiIDMhsw,
            detailkurikulum.MKKode,
            detailkurikulum.Nama AS NamaMatakuliah,
            detailkurikulum.ID AS DetailKurikulumID,
            detailkurikulum.SKSTatapMuka,
            detailkurikulum.SKSPraktikum,
            detailkurikulum.SKSPraktekLap,
            NULL AS Konsentrasi,
            detailkurikulum.Semester AS Semester,
            konversi_detail.Semester AS SemesterKonversi,
            detailkurikulum.TotalSKS AS TotalSKS,
            bobot.Bobot AS Bobot,
            (bobot.Bobot * detailkurikulum.TotalSKS) AS NilaiBobot,
            konversi_detail.NilaiKonversi AS NilaiHuruf,
            0 AS NilaiAkhir,
            NOW() AS TglInput,
            NOW() AS TglUpdate,
            nilai.ID as nilaiID,
            nilai.NilaiHuruf as NilaiHurufNilai,
            mahasiswa.TahunMasuk,
            konversi_detail.ID as KonversiDetailID
            FROM (konversi_detail)
            INNER JOIN detailkurikulum ON detailkurikulum.ID = konversi_detail.DetailkurikulumID
            INNER JOIN konversi ON konversi.ID = konversi_detail.KonversiID
            INNER JOIN mahasiswa ON konversi.MhswID = mahasiswa.ID
            INNER JOIN programstudi ON programstudi.ID = mahasiswa.ProdiID
            INNER JOIN jenjang ON jenjang.ID = programstudi.JenjangID
            INNER JOIN program ON program.ID = mahasiswa.ProgramID
            INNER JOIN bobot ON bobot.Nilai = konversi_detail.NilaiKonversi
            AND bobot.BobotMasterID = '" . $bobotResult->BobotMasterID . "'
            LEFT JOIN nilai ON nilai.NPM = mahasiswa.NPM
            AND nilai.MKKode = detailkurikulum.MKKode
            AND nilai.TotalSKS = detailkurikulum.TotalSKS
            AND nilai.Semester = detailkurikulum.Semester
            WHERE konversi_detail.KonversiID = '" . $konversiID . "'
            AND konversi_detail.generate = 0";

        $cekData = DB::select($qkonversi);

        $arrGradeTidakLulus = ['E', 'D'];
        $matkulSuccess = [];
        $matkulFail = [];
        $success = 0;

        if (count($cekData) > 0) {
            foreach ($cekData as $value) {
                $shouldSkip = false;

                // Check if nilai exists and has failing grade
                if (!empty($value->nilaiID) && in_array($value->NilaiHurufNilai, $arrGradeTidakLulus)) {
                    $shouldSkip = true;
                }

                if (!$shouldSkip) {
                    $insert = [
                        'ID' => null,
                        'KodeTahun' => '00000',
                        'TahunID' => 0,
                        'MhswID' => $value->MhswID,
                        'ProdiID' => $value->ProdiIDMhsw,
                        'ProgramID' => $value->ProgramID,
                        'DetailKurikulumID' => $value->DetailKurikulumID,
                        'Konversi' => '1',
                        'NamaProdi' => $value->NamaProdi,
                        'NamaProgram' => $value->Program,
                        'NPM' => $value->NPM,
                        'NamaMahasiswa' => $value->NamaMahasiswa,
                        'MKKode' => $value->MKKode,
                        'NamaMatakuliah' => $value->NamaMatakuliah,
                        'Semester' => ($value->SemesterKonversi) ? $value->SemesterKonversi : $value->Semester,
                        'SKSTatapMuka' => $value->SKSTatapMuka,
                        'SKSPraktikum' => $value->SKSPraktikum,
                        'SKSPraktekLap' => $value->SKSPraktekLap,
                        'TotalSKS' => $value->TotalSKS,
                        'Bobot' => $value->Bobot,
                        'NilaiBobot' => $value->NilaiBobot,
                        'NilaiHuruf' => $value->NilaiHuruf,
                        'NilaiAkhir' => $value->NilaiAkhir,
                        'TahunMasuk' => $value->TahunMasuk,
                        'PublishTranskrip' => 1,
                        'createAt' => date('Y-m-d H:i:s'),
                        'userID' => $userID ?? Session::get('UserID'),
                    ];

                    $insertData = DB::table('nilai')->insert($insert);

                    if ($insertData) {
                        DB::table('konversi')->where('ID', $konversiID)->update(['statuskonversi' => 1]);
                        $matkulSuccess[] = $value->MKKode;
                        $success++;
                    } else {
                        $matkulFail[] = $value->MKKode;
                    }
                } else {
                    $matkulFail[] = $value->MKKode;
                }
            }

            return [
                'status' => '1',
                'matkulFail' => $matkulFail,
                'matkulSuccess' => $matkulSuccess,
                'message' => 'Proses generate konversi nilai !.',
                'count' => $success
            ];
        } else {
            return [
                'status' => '0',
                'message' => 'Maaf mata kuliah konversi tidak ditemukan !.',
                'count' => 0
            ];
        }
    }

    /**
     * Generate all konversi
     * Corresponds to: C_konversi->konversi_all()
     */
    public function konversi_all($programID = '', $prodiID = '', $tahunMasuk = '')
    {
        $query = DB::table('konversi');
        $query->join('mahasiswa', 'konversi.MhswID', '=', 'mahasiswa.ID');

        if (!empty($programID)) {
            $query->where('mahasiswa.ProdiID', $programID);
        }

        if (!empty($prodiID)) {
            $query->where('mahasiswa.ProgramID', $prodiID);
        }

        if (!empty($tahunMasuk)) {
            $query->where('mahasiswa.TahunMasuk', $tahunMasuk);
        }

        $query->where('statusKonversi', 0);
        $listKonversi = $query->get();

        $success = 0;
        foreach ($listKonversi as $data) {
            $generate = $this->gen_konversi($data->ID, null, true);
            if (isset($generate['count']) && $generate['count'] > 0) {
                $success++;
            }
        }

        if (count($listKonversi) == $success) {
            return [
                'status' => 1,
                'message' => 'Seluruh Data Berhasil Dikonversikan'
            ];
        } else if (count($listKonversi) > $success) {
            return [
                'status' => 1,
                'message' => $success . " Data Berhasil Dikonversikan " . (count($listKonversi) - $success) . " Data Gagal Dikonversikan."
            ];
        } else {
            return [
                'status' => 0,
                'message' => 'Data Gagal Dikonversikan'
            ];
        }
    }

    /**
     * Cancel/batalkan konversi
     * Corresponds to: C_konversi->batalKonversi()
     */
    public function batal_konversi($konversiID, $userID = null)
    {
        $konversi = DB::table('konversi')->where('ID', $konversiID)->first();
        $mhsw = DB::table('mahasiswa')->select('ID', 'NPM', 'Nama')->where('ID', $konversi->MhswID)->first();

        // Insert log
        $insLog = [
            'KonversiID' => $konversi->ID,
            'MhswID' => $mhsw->ID,
            'NPM' => $mhsw->NPM,
            'Jenis' => 'konversikan',
            'Internal' => $konversi->internal,
            'createdAt' => date('Y-m-d H:i:s'),
            'UserID' => $userID ?? Session::get('UserID'),
        ];
        DB::table('log_konversi')->insert($insLog);

        $qkonversi = "
            SELECT
            mahasiswa.ID as MhswID,
            nilai.ID as nilaiID,
            detailkurikulum.MKKode,
            konversi_detail.ID as KonversiDetailID
            FROM (konversi_detail)
            INNER JOIN detailkurikulum ON detailkurikulum.ID = konversi_detail.DetailkurikulumID
            INNER JOIN konversi ON konversi.ID = konversi_detail.KonversiID
            INNER JOIN mahasiswa ON konversi.MhswID = mahasiswa.ID
            LEFT JOIN nilai ON nilai.NPM = mahasiswa.NPM
            AND nilai.MKKode = detailkurikulum.MKKode
            AND nilai.TotalSKS = detailkurikulum.TotalSKS
            AND (nilai.Semester = detailkurikulum.Semester OR nilai.Semester = konversi_detail.Semester)
            AND nilai.Konversi = '1'
            WHERE konversi_detail.KonversiID = '" . $konversiID . "'
            AND konversi.statuskonversi = '1'";

        $cekData = DB::select($qkonversi);

        $matkulSuccess = [];
        $matkulFail = [];

        if (count($cekData) > 0) {
            foreach ($cekData as $value) {
                if (!empty($value->nilaiID)) {
                    DB::table('nilai')->where('ID', $value->nilaiID)->delete();
                    DB::table('konversi')->where('ID', $konversiID)->update(['statuskonversi' => '0']);
                    DB::table('konversi_detail')->where('KonversiID', $konversiID)->update(['generate' => '0']);
                    $matkulSuccess[] = $value->MKKode;
                } else {
                    $matkulFail[] = $value->MKKode;
                }
            }

            return [
                'status' => '1',
                'matkulFail' => $matkulFail,
                'matkulSuccess' => $matkulSuccess,
                'message' => 'Proses batal konversi nilai !.'
            ];
        } else {
            return [
                'status' => '0',
                'message' => 'Maaf mata kuliah konversi tidak ditemukan !.'
            ];
        }
    }

    /**
     * Get last NPM for internal conversion
     * Corresponds to: C_konversi->get_last_npm()
     */
    public function get_last_npm($prodiID, $programID)
    {
        if (empty($prodiID) || empty($programID)) {
            return '';
        }

        $thn = DB::table('tahun')->where('ProsesBuka', 1)->first();
        $tahunMasuk = substr($thn->TahunID, 0, 4);

        $kodeProdi = get_field($prodiID, 'programstudi', 'ProdiID');
        $kodeProgram = get_field($programID, 'program', 'ProgramID');
        $kodeTahunMasuk = substr($tahunMasuk, 2, 4);
        $statusPindahan = 9; // hardcode

        $nim1 = $kodeProdi . $kodeProgram . $kodeTahunMasuk . $statusPindahan;

        $cekNim = DB::table('mahasiswa')
            ->whereRaw("NPM LIKE '{$nim1}%'")
            ->where('ProdiID', $prodiID)
            ->orderBy('NPM', 'ASC')
            ->get();

        $countNim = count($cekNim);

        $urutNim = [];
        $urutan = 0;
        $maxNim = 0;

        if ($countNim > 0) {
            foreach ($cekNim as $keyNim => $valNim) {
                $urutNim[] = intval(substr($valNim->NPM, -3));
                if (intval($keyNim + 1) == $countNim) {
                    $maxNim = intval(substr($valNim->NPM, -3));
                }
            }

            for ($i = 1; $i <= $maxNim + 1; $i++) {
                if (!in_array($i, $urutNim)) {
                    $urutan = $i;
                    break;
                }
            }
        } else {
            $urutan = 1;
        }

        return $nim1 . str_pad($urutan, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Save internal konversi
     * Corresponds to: C_konversi->save_internal()
     */
    public function save_internal($post, $save = '1', $userID = null)
    {
        $mhsw = DB::table('mahasiswa')->where('NPM', $post['NPM'])->first();
        $thn = DB::table('tahun')->where('ProsesBuka', 1)->first();
        $tahunMasuk = substr($thn->TahunID, 0, 4);

        $dataBobot = $this->get_bobot_values($post['NPM'], true);

        // Update status mahasiswa
        DB::table('mahasiswa')->where('ID', $mhsw->ID)->update(['StatusMhswID' => '6']);

        $statusMhswID = 6;
        $namaStatus = get_field($statusMhswID, 'statusmahasiswa', 'Nama');
        $alasan = 'Konversi Internal';

        $input = [
            'ProdiID' => $mhsw->ProdiID,
            'TahunID' => $thn->ID,
            'MhswID' => $mhsw->ID,
            'StatusMahasiswaID' => $statusMhswID,
            'Status' => $namaStatus,
            'Alasan' => $alasan,
            'Tgl' => date('Y-m-d'),
        ];

        if ($save == 1) {
            DB::table('keteranganstatusmahasiswa')->insert($input);
            DB::table('mahasiswa')->where('ID', $mhsw->ID)->update(['StatusMhswID' => $statusMhswID]);

            $insLog = [
                'MhswID' => $mhsw->ID,
                'NPM' => $mhsw->NPM,
                'TahunID' => $thn->ID,
                'StatusMhswID' => $statusMhswID,
                'Status' => $namaStatus,
                'createdAt' => date('Y-m-d H:i:s'),
                'UserID' => $userID ?? Session::get('UserID'),
            ];
            DB::table('log_set_status')->insert($insLog);
        }

        // Handle NPM baru
        $idnya = null;
        if ($post['Opsi'] == 1) {
            // Create new mahasiswa record
            $getMhsw = DB::table('mahasiswa')->where('NPM', $post['NPM'])->first();
            $getMhswArr = (array) $getMhsw;

            $newMhsw = $getMhswArr;
            $newMhsw['ID'] = null;
            $newMhsw['NPM'] = $post['NPMBaru'];
            $newMhsw['KurikulumID'] = $post['KurikulumID'];
            $newMhsw['StatusMhswID'] = 3;
            $newMhsw['TahunMasuk'] = $tahunMasuk;
            $newMhsw['BobotMasterID'] = $post['BobotMasterID'];
            $newMhsw['ProgramID'] = $post['ProgramID'];
            $newMhsw['ProdiID'] = $post['ProdiID'];
            $newMhsw['SumberData'] = 'konversi';

            // Remove auto-increment ID if present
            unset($newMhsw['ID']);

            DB::table('mahasiswa')->insert($newMhsw);
            $idnya = DB::getPdo()->lastInsertId();
        } else {
            // Use existing mahasiswa
            $getMhsw = DB::table('mahasiswa')->where('NPM', $post['PilihMhswYangDituju'])->first();
            $idnya = $getMhsw->ID;
            $post['NPMBaru'] = $getMhsw->NPM;
        }

        // Log pindah prodi if applicable
        if (($post['type'] ?? '') == 'prodi' && $post['Opsi'] == 1) {
            $mhswBaru = DB::table('mahasiswa')
                ->select('ID', 'Nama', 'NPM', 'ProdiID', 'TahunMasuk', 'ProgramID')
                ->where('ID', $idnya)
                ->first();

            $inputLogPindahProdi = [
                'Kode' => $post['KodeKonversi'],
                'MhswIDAsal' => $mhsw->ID,
                'NPMAsal' => $mhsw->NPM,
                'ProgramIDAsal' => $mhsw->ProgramID,
                'ProdiIDAsal' => $mhsw->ProdiID,
                'TahunMasukAsal' => $mhsw->TahunMasuk,
                'MhswIDBaru' => $mhswBaru->ID,
                'NPMBaru' => $mhswBaru->NPM,
                'ProgramIDBaru' => $mhswBaru->ProgramID,
                'ProdiIDBaru' => $mhswBaru->ProdiID,
                'TahunMasukBaru' => $mhswBaru->TahunMasuk,
                'Nama' => $mhswBaru->Nama,
                'createdAt' => date('Y-m-d H:i:s'),
                'UserID' => $userID ?? Session::get('UserID'),
            ];
            DB::table('log_pindah_prodi')->insert($inputLogPindahProdi);
        }

        // Copy files and directories if Opsi == 1
        if ($post['Opsi'] == 1) {
            $pathOldDirNpm = CLIENT_PATH . '/mahasiswa/' . $mhsw->NPM . '/';
            $pathNewDirNpm = CLIENT_PATH . '/mahasiswa/' . $post['NPMBaru'] . '/';
            $pathOldDirPmb = CLIENT_PATH . '/pmb/' . $mhsw->ID . '/document/lainnya/';
            $pathNewDirPmb = CLIENT_PATH . '/pmb/' . $idnya . '/document/lainnya/';

            if (defined('CLIENT_PATH') && file_exists($pathOldDirNpm)) {
                $this->copyDirectoryRecursive($pathOldDirNpm, $pathNewDirNpm);
            }
            if (defined('CLIENT_PATH') && file_exists($pathOldDirPmb)) {
                $this->copyDirectoryRecursive($pathOldDirPmb, $pathNewDirPmb);
            }

            // Copy file_upload records
            $dataFileLama = DB::table('file_upload')
                ->where('UserID', $mhsw->ID)
                ->where('TypeUser', '1')
                ->orderBy('TypeFile', 'DESC')
                ->get();

            foreach ($dataFileLama as $data) {
                DB::table('file_upload')->insert([
                    'UserID' => $idnya,
                    'TypeUser' => 1,
                    'TypeFile' => $data->TypeFile,
                    'File' => $data->File,
                    'TanggalInput' => date('Y-m-d H:i:s'),
                ]);
            }

            // Copy pmb_edu_file_syarat records
            $dataJalur = DB::table('pmb_edu_syarat')
                ->where('idpendaftaran', $mhsw->ID)
                ->whereRaw("FIND_IN_SET('" . ($mhsw->jalur_pmb ?? '') . "', jalur_pendaftaran) != 0")
                ->join('pmb_edu_file_syarat', 'pmb_edu_file_syarat.idsyarat', '=', 'pmb_edu_syarat.id')
                ->get();

            foreach ($dataJalur as $data) {
                DB::table('pmb_edu_file_syarat')->insert([
                    'idpendaftaran' => $idnya,
                    'namafile' => $data->namafile,
                    'idsyarat' => $data->idsyarat,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            // Copy ortu records
            $getOrtu = DB::table('ortu')->where('MhswID', $mhsw->ID)->get();
            foreach ($getOrtu as $ortu) {
                $ortuArr = (array) $ortu;
                unset($ortuArr['ID']);
                $ortuArr['MhswID'] = $idnya;
                DB::table('ortu')->insert($ortuArr);
            }

            // Create user account
            $inputUser = [
                'TabelUserID' => 4,
                'Nama' => $post['NPMBaru'],
                'NamaEntity' => $getMhsw->Nama,
                'Password' => md5($post['NPMBaru']),
                'akses_student' => 1,
                'akses_elearning' => 1,
                'EntityID' => $idnya,
            ];
            DB::table('user')->insert($inputUser);
            $userId = DB::getPdo()->lastInsertId();

            DB::table('leveluser')->insert([
                'LevelID' => 4,
                'UserID' => $userId,
            ]);
        }

        // Save konversi data
        $input = [
            'ID' => $post['ID'],
            'KodeKonversi' => $post['KodeKonversi'],
            'MhswID' => $idnya,
            'internal' => 'Ya',
            'SemesterMulai' => 1,
            'user' => Session::get('username'),
            'Alasan' => $post['Alasan'],
            'last_update' => date('Y-m-d H:i:s'),
        ];

        DB::table('konversi')->updateOrInsert(['ID' => $post['ID']], $input);
        $id = DB::getPdo()->lastInsertId();
        if (empty($id)) {
            $id = $post['ID'];
        }

        $mhswBaru = DB::table('mahasiswa')->select('ID', 'NPM', 'Nama')->where('ID', $idnya)->first();

        $insLog = [
            'KonversiID' => $id,
            'MhswID' => $mhswBaru->ID,
            'NPM' => $mhswBaru->NPM,
            'Jenis' => 'tambah',
            'Internal' => 'Ya',
            'createdAt' => date('Y-m-d H:i:s'),
            'UserID' => $userID ?? Session::get('UserID'),
        ];
        DB::table('log_konversi')->insert($insLog);

        // Save detail konversi
        $totalMKAsal = 0;
        $totalSKSAsal = 0;
        $totalMKTujuan = 0;
        $totalSKSTujuan = 0;
        $inputDetailBatch = [];
        $arrayCek = [];

        foreach ($post['MKKodeAsal'] as $index => $val) {
            if (!in_array($post['DetailkurikulumID'][$index], $arrayCek)) {
                $inputDetail = [
                    'ID' => $post['IDDetail'][$index],
                    'MhswID' => $mhswBaru->ID,
                    'KonversiID' => $id,
                    'MKKodeAsal' => $post['MKKodeAsal'][$index],
                    'NamaMKAsal' => $post['NamaMKAsal'][$index],
                    'SKSAsal' => $post['SKSAsal'][$index],
                    'NilaiAsal' => $post['NilaiAsal'][$index],
                    'DetailkurikulumID' => (empty($post['DetailkurikulumID'][$index])) ? null : $post['DetailkurikulumID'][$index],
                    'NilaiKonversi' => (empty($post['NilaiKonversi'][$index])) ? null : $post['NilaiKonversi'][$index],
                    'NilaiAngkaKonversi' => $dataBobot[$post['NilaiKonversi'][$index]] ?? null,
                    'Semester' => get_field($post['DetailkurikulumID'][$index], 'detailkurikulum', 'Semester'),
                    'create_at' => date('Y-m-d H:i:s'),
                ];
                $inputDetailBatch[] = $inputDetail;

                $totalMKAsal += (!empty($post['MKKodeAsal'][$index]) ? 1 : 0);
                $totalSKSAsal += (!empty($post['SKSAsal'][$index]) ? $post['SKSAsal'][$index] : 0);
                $totalMKTujuan += (!empty($post['DetailkurikulumID'][$index]) ? 1 : 0);
                $totalSKSTujuan += (!empty(get_field($post['DetailkurikulumID'][$index], 'detailkurikulum', 'TotalSKS')) ? get_field($post['DetailkurikulumID'][$index], 'detailkurikulum', 'TotalSKS') : 0);

                $arrayCek[] = $post['DetailkurikulumID'][$index];
            }
        }

        if (count($inputDetailBatch) > 0) {
            foreach ($inputDetailBatch as $detail) {
                if (!empty($detail['ID'])) {
                    DB::table('konversi_detail')->where('ID', $detail['ID'])->update($detail);
                } else {
                    $tempDetail = $detail;
                    unset($tempDetail['ID']);
                    DB::table('konversi_detail')->insert($tempDetail);
                }
            }
        }

        // Update konversi totals
        $updateKonversi = [
            'TotalMKAsal' => $totalMKAsal,
            'TotalSKSAsal' => $totalSKSAsal,
            'TotalMKTujuan' => $totalMKTujuan,
            'TotalSKSTujuan' => $totalSKSTujuan,
        ];

        if (count($inputDetailBatch) == 0) {
            $updateKonversi['statuskonversi'] = 1;
        }

        DB::table('konversi')->where('ID', $id)->update($updateKonversi);

        $insLog = [
            'KonversiID' => $id,
            'MhswID' => $mhswBaru->ID,
            'NPM' => $mhswBaru->NPM,
            'Jenis' => 'edit',
            'Internal' => 'Ya',
            'createdAt' => date('Y-m-d H:i:s'),
            'UserID' => $userID ?? Session::get('UserID'),
        ];
        DB::table('log_konversi')->insert($insLog);

        // Auto generate konversi if there are details
        $konversi = DB::table('konversi')->where('ID', $id)->first();

        $bobotMasterPilihan = $post['BobotMasterPilihan'] ?? null;
        if ($bobotMasterPilihan) {
            $qkonversi = "
            SELECT
            NULL AS ID,
            NULL AS TahunID,
            NULL AS NamaTahun,
            programstudi.Nama AS NamaProdi,
            programstudi.ProdiID AS ProdiID,
            jenjang.Nama AS NamaJenjang,
            program.Nama AS Program,
            program.ID AS ProgramID,
            mahasiswa.NPM,
            mahasiswa.ID as MhswID,
            mahasiswa.Nama as NamaMahasiswa,
            mahasiswa.ProdiID as ProdiIDMhsw,
            detailkurikulum.MKKode,
            detailkurikulum.Nama AS NamaMatakuliah,
            detailkurikulum.ID AS DetailKurikulumID,
            detailkurikulum.SKSTatapMuka,
            detailkurikulum.SKSPraktikum,
            detailkurikulum.SKSPraktekLap,
            NULL AS Konsentrasi,
            detailkurikulum.Semester AS Semester,
            detailkurikulum.TotalSKS AS TotalSKS,
            bobot.Bobot AS Bobot,
            (bobot.Bobot * detailkurikulum.TotalSKS) AS NilaiBobot,
            konversi_detail.NilaiKonversi AS NilaiHuruf,
            0 AS NilaiAkhir,
            NOW() AS TglInput,
            NOW() AS TglUpdate,
            nilai.ID as nilaiID,
            mahasiswa.TahunMasuk,
            konversi_detail.ID as KonversiDetailID
            FROM (konversi_detail)
            INNER JOIN detailkurikulum ON detailkurikulum.ID = konversi_detail.DetailkurikulumID
            INNER JOIN konversi ON konversi.ID = konversi_detail.KonversiID
            INNER JOIN mahasiswa ON konversi.MhswID = mahasiswa.ID
            INNER JOIN programstudi ON programstudi.ID = mahasiswa.ProdiID
            INNER JOIN jenjang ON jenjang.ID = programstudi.JenjangID
            INNER JOIN program ON program.ID = mahasiswa.ProgramID
            INNER JOIN bobot ON bobot.Nilai = konversi_detail.NilaiKonversi
            AND bobot.BobotMasterID = '" . $bobotMasterPilihan . "'
            LEFT JOIN nilai ON nilai.NPM = mahasiswa.NPM
            AND nilai.MKKode = detailkurikulum.MKKode
            AND nilai.TotalSKS = detailkurikulum.TotalSKS
            AND nilai.Semester = detailkurikulum.Semester
            WHERE konversi_detail.KonversiID = '" . $id . "'
            AND konversi_detail.generate = 0";

            $cekData = DB::select($qkonversi);

            $matkulSuccess = [];
            $matkulFail = [];

            if (count($cekData) > 0) {
                foreach ($cekData as $value) {
                    if (empty($value->nilaiID)) {
                        $insertDataNilai = [
                            'ID' => null,
                            'KodeTahun' => '00000',
                            'TahunID' => 0,
                            'MhswID' => $value->MhswID,
                            'ProdiID' => $value->ProdiIDMhsw,
                            'ProgramID' => $value->ProgramID,
                            'DetailKurikulumID' => $value->DetailKurikulumID,
                            'Konversi' => '1',
                            'NamaProdi' => $value->NamaProdi,
                            'NamaProgram' => $value->Program,
                            'NPM' => $value->NPM,
                            'NamaMahasiswa' => $value->NamaMahasiswa,
                            'MKKode' => $value->MKKode,
                            'NamaMatakuliah' => $value->NamaMatakuliah,
                            'Semester' => $value->Semester,
                            'SKSTatapMuka' => $value->SKSTatapMuka,
                            'SKSPraktikum' => $value->SKSPraktikum,
                            'SKSPraktekLap' => $value->SKSPraktekLap,
                            'TotalSKS' => $value->TotalSKS,
                            'Bobot' => $value->Bobot,
                            'NilaiBobot' => $value->NilaiBobot,
                            'NilaiHuruf' => $value->NilaiHuruf,
                            'NilaiAkhir' => $value->NilaiAkhir,
                            'TahunMasuk' => $value->TahunMasuk,
                            'PublishTranskrip' => 1,
                            'createAt' => date('Y-m-d H:i:s'),
                            'userID' => $userID ?? Session::get('UserID'),
                        ];

                        $insertData = DB::table('nilai')->insert($insertDataNilai);

                        if ($insertData) {
                            DB::table('konversi')->where('ID', $id)->update(['statuskonversi' => 1]);
                            $matkulSuccess[] = $value->MKKode;
                        } else {
                            $matkulFail[] = $value->MKKode;
                        }
                    } else {
                        $matkulFail[] = $value->MKKode;
                    }
                }

                $response = [
                    'status' => '1',
                    'matkulFail' => $matkulFail,
                    'matkulSuccess' => $matkulSuccess,
                    'message' => 'Proses generate konversi nilai !.',
                ];
            } else {
                $response = [
                    'status' => '0',
                    'message' => 'Maaf mata kuliah konversi tidak ditemukan !.',
                ];
            }
        } else {
            if (($post['type'] ?? '') === '') {
                $response = [
                    'status' => '0',
                    'message' => 'Maaf mata kuliah konversi tidak ditemukan !.',
                ];
            } else {
                $response = [
                    'status' => '1',
                    'message' => 'Proses Data Berhasil !.',
                ];
            }
        }

        $response['data'] = [];
        $response['url'] = 'konversi/view/' . $id;

        return $response;
    }

    /**
     * Copy directory recursively
     */
    private function copyDirectoryRecursive($source, $destination)
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file == '.' || $file == '..') continue;

            $sourcePath = $source . '/' . $file;
            $destPath = $destination . '/' . $file;

            if (is_dir($sourcePath)) {
                $this->copyDirectoryRecursive($sourcePath, $destPath);
            } else {
                copy($sourcePath, $destPath);
            }
        }
        closedir($dir);
    }

    /**
     * Process upload Excel for batch konversi
     * Corresponds to: C_konversi->uploadExcel()
     */
    public function process_upload_excel($filePath, $userID = null)
    {
        // Note: This assumes the use of PhpSpreadsheet for reading Excel files
        // The actual implementation would depend on your Excel reading library
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $arrData = [];

        foreach ($worksheet->getRowIterator(2) as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [];
            $index = 0;
            foreach ($cellIterator as $cell) {
                $rowData[chr(65 + $index)] = $cell->getValue();
                $index++;
            }

            if (!empty($rowData['A'])) {
                $arrData[] = $rowData;
            }
        }

        // Group by NPM
        $dataReal = [];
        foreach ($arrData as $row) {
            if (!empty($row['A'])) {
                $dataMk = [
                    'MKKodeAsal' => trim($row['C']),
                    'NamaMKAsal' => trim($row['D']),
                    'SKSAsal' => trim($row['E']),
                    'NilaiHurufAsal' => trim($row['F']),
                    'IDMK' => trim($row['G']),
                    'NilaiHurufDiakui' => trim($row['I']),
                ];
                $dataReal[$row['A']][] = $dataMk;
            }
        }

        if (count($dataReal) > 0) {
            foreach ($dataReal as $npm => $dataMk) {
                $mhsw = DB::table('mahasiswa')->select('ID', 'NPM')->where('NPM', $npm)->first();

                if (!empty($mhsw->ID)) {
                    // Check for duplicate mata kuliah
                    $tempDataMk = [];
                    foreach ($dataMk as $mk) {
                        $tempDataMk[$mk['IDMK']] = ($tempDataMk[$mk['IDMK']] ?? 0) + 1;
                    }

                    $isClear = [];
                    $dataFail = [];
                    foreach ($tempDataMk as $key => $val) {
                        if ($val > 1) {
                            $isClear[] = 0;
                            $dataFail[] = get_field($key, 'detailkurikulum', 'MKKode');
                        } else {
                            $isClear[] = 1;
                        }
                    }

                    if (!in_array(0, $isClear) || true) {
                        $input = [
                            'KodeKonversi' => "Konv-" . $mhsw->NPM,
                            'MhswID' => $mhsw->ID,
                            'SemesterMulai' => 1,
                            'user' => Session::get('username'),
                            'Alasan' => "-",
                        ];

                        $cekKonversi = DB::table('konversi')->where('MhswID', $mhsw->ID)->first();
                        if (!empty($cekKonversi->ID)) {
                            DB::table('konversi')->where('ID', $cekKonversi->ID)->update($input);
                            $id = $cekKonversi->ID;
                        } else {
                            $input['create_at'] = date('Y-m-d H:i:s');
                            DB::table('konversi')->insert($input);
                            $id = DB::getPdo()->lastInsertId();
                        }

                        if ($id) {
                            $totalMKAsal = 0;
                            $totalSKSAsal = 0;
                            $totalMKTujuan = 0;
                            $totalSKSTujuan = 0;

                            foreach ($dataMk as $listMk) {
                                $mkDiakui = DB::table('detailkurikulum')
                                    ->select('ID', 'MKKode', 'Nama', 'TotalSKS')
                                    ->where('ID', $listMk['IDMK'])
                                    ->first();

                                $inputDetail = [
                                    'KonversiID' => $id,
                                    'MhswID' => $mhsw->ID,
                                    'MKKodeAsal' => $listMk['MKKodeAsal'],
                                    'NamaMKAsal' => $listMk['NamaMKAsal'],
                                    'SKSAsal' => $listMk['SKSAsal'],
                                    'NilaiAsal' => strtoupper($listMk['NilaiHurufAsal']),
                                    'DetailkurikulumID' => (empty($mkDiakui->ID)) ? null : $mkDiakui->ID,
                                    'NilaiKonversi' => (empty($listMk['NilaiHurufDiakui'])) ? null : $listMk['NilaiHurufDiakui'],
                                    'Semester' => get_field($mkDiakui->ID, 'detailkurikulum', 'Semester'),
                                    'create_at' => date('Y-m-d H:i:s'),
                                ];

                                $cekData = DB::table('konversi_detail')
                                    ->select('ID', 'DetailkurikulumID')
                                    ->where('DetailkurikulumID', $mkDiakui->ID)
                                    ->where('KonversiID', $id)
                                    ->first();

                                $totalMKAsal += (!empty($listMk['MKKodeAsal']) ? 1 : 0);
                                $totalSKSAsal += (!empty($listMk['SKSAsal']) ? $listMk['SKSAsal'] : 0);
                                $totalMKTujuan += (!empty($mkDiakui->ID) ? 1 : 0);
                                $totalSKSTujuan += (!empty($mkDiakui->TotalSKS) ? $mkDiakui->TotalSKS : 0);

                                if (empty($cekData->ID)) {
                                    DB::table('konversi_detail')->insert($inputDetail);
                                } else {
                                    DB::table('konversi_detail')->where('ID', $cekData->ID)->update($inputDetail);
                                }
                            }

                            $updateKonversi = [
                                'TotalMKAsal' => $totalMKAsal,
                                'TotalSKSAsal' => $totalSKSAsal,
                                'TotalMKTujuan' => $totalMKTujuan,
                                'TotalSKSTujuan' => $totalSKSTujuan,
                                'statuskonversi' => '0',
                                'last_update' => date('Y-m-d H:i:s'),
                            ];

                            DB::table('konversi')->where('ID', $id)->update($updateKonversi);

                            unset($totalMKAsal);
                            unset($totalSKSAsal);
                            unset($totalMKTujuan);
                            unset($totalSKSTujuan);
                        } else {
                            return [
                                'status' => 0,
                                'message' => 'Maaf data gagal disimpan !.',
                            ];
                        }
                    } else {
                        $uniqDataFail = array_unique($dataFail);
                        $dataMkDouble = implode("<br>", $uniqDataFail);
                        return [
                            'status' => 0,
                            'message' => 'Maaf anda tidak boleh menginput mata kuliah yang sama untuk mahasiswa ' . $npm . '. Berikut ini Mata Kuliah yang sama :  <br> ' . $dataMkDouble . ' ',
                        ];
                    }
                    unset($tempDataMk);
                } else {
                    return [
                        'status' => '0',
                        'message' => 'Mahasiswa Tidak Ditemukan',
                    ];
                }
            }

            return [
                'status' => 1,
                'message' => 'Data berhasil disimpan !.',
            ];
        } else {
            return [
                'status' => '0',
                'message' => 'Tidak Ada Data yang Diproses !.',
            ];
        }
    }
}
