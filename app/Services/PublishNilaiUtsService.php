<?php

namespace App\Services;

use App\Models\BobotMahasiswa;
use App\Models\JadwalWaktu;
use App\Models\Rombel;
use App\Models\PesertaRombel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PublishNilaiUtsService
{
    /**
     * Get jadwal mengajar dosen with filters
     * Corresponds to: C_publish_nilai_uts->search_publish_nilai_uts_mengajar_dosen()
     */
    public function get_jadwal_dosen($dosenID, $programID = '', $prodiID = '', $tahunID = '', $mkID = '', $limit = 100000)
    {
        $query = DB::table('jadwal')
            ->select(
                'jadwal.ID as jadwalID',
                'jadwal.DosenID as dosenID',
                'jadwal.DosenAnggota as dosenAnggota',
                'jadwal.TahunID as tahunID',
                'jadwal.DetailKurikulumID as detailkurikulumID',
                'jadwal.KelasID as kelasID',
                'jadwal.Aktif as aktif',
                'jadwal.Pengumuman as pengumuman',
                'jadwal.JumlahPeserta as totalPeserta',
                'jadwal.Gambar',
                'jadwal.Deskripsi',
                'jadwal.gabungan',
                'detailkurikulum.MKKode as mkkode',
                'detailkurikulum.Nama as namaMatkul',
                'detailkurikulum.TotalSKS as totalSKS',
                'detailkurikulum.ProgramID as programID',
                'detailkurikulum.ProdiID as prodiID'
            )
            ->join('detailkurikulum', 'jadwal.DetailKurikulumID', '=', 'detailkurikulum.ID');

        if ($programID && $programID != 'semua') {
            $query->where('jadwal.ProgramID', $programID);
        }
        if ($tahunID && $tahunID != 'semua') {
            $query->where('jadwal.TahunID', $tahunID);
        }
        if ($prodiID && $prodiID != 'semua') {
            $query->where('jadwal.ProdiID', $prodiID);
        }
        if ($mkID) {
            $query->where('detailkurikulum.ID', $mkID);
        }
        
        // Dosen filter logic: DosenID OR FIND_IN_SET in DosenAnggota
        if ($dosenID) {
            $query->whereRaw("(jadwal.DosenID = '$dosenID' OR FIND_IN_SET('$dosenID', jadwal.DosenAnggota))");
        }

        $query->where('jadwal.Aktif', 'Ya');
        $query->take($limit);

        $results = $query->get();

        return $results->map(function($item) {
            return (object) (array) $item;
        })->toArray();
    }

    /**
     * Get list jadwal gabungan
     * Corresponds to: C_publish_nilai_uts->search_publish_nilai_uts_mengajar_dosen()
     */
    public function get_list_jadwal_gabungan($tahunID = '', $mkID = '')
    {
        $query = DB::table('jadwal_gabungan')
            ->select('jadwal_gabungan.ID', 'jadwal_gabungan.jadwalID', 'jadwal_gabungan.jadwalGabungan');

        if ($mkID) {
            // Assuming mkID filters might be related to jadwal details if available, 
            // but for safety, we'll just get all records and filter via PHP if needed 
            // or remove this condition if it causes errors.
            // Usually jadwal_gabungan doesn't store MKID directly.
        }

        $results = $query->get();

        return $results->map(function($item) {
            return (object) (array) $item;
        })->toArray();
    }

    /**
     * Get jadwal waktu for a jadwal
     * Corresponds to: C_publish_nilai_uts->search_publish_nilai_uts_mengajar_dosen()
     */
    public function get_jadwal_waktu($jadwalID)
    {
        $query = DB::table('jadwalwaktu')
            ->select(
                'jadwalwaktu.ID',
                'jadwalwaktu.Tanggal',
                'jadwalwaktu.TahunID',
                'jadwalwaktu.WaktuID',
                'jadwalwaktu.JadwalID',
                'jadwalwaktu.HariID',
                'jadwalwaktu.RuangID',
                'jadwalwaktu.Sesi',
                'jadwalwaktu.Pertemuan',
                'jadwalwaktu.IDRombel',
                'jadwalwaktu.TipeJadwal',
                'kodewaktu.JamMulai',
                'kodewaktu.JamSelesai'
            )
            ->join('kodewaktu', 'kodewaktu.ID', '=', 'jadwalwaktu.WaktuID')
            ->where('jadwalwaktu.JadwalID', $jadwalID)
            ->whereNotIn('jadwalwaktu.Pertemuan', [98, 99])
            ->orderBy('jadwalwaktu.Pertemuan', 'ASC')
            ->orderBy('jadwalwaktu.Sesi', 'ASC');

        $results = $query->get();

        return $results->map(function($item) {
            return (object) (array) $item;
        })->toArray();
    }

    /**
     * Get bobot mahasiswa data for jadwal list
     * Corresponds to: C_publish_nilai_uts->search_publish_nilai_uts_mengajar_dosen()
     */
    public function get_bobot_mahasiswa_for_jadwal($listJadwalID)
    {
        if (empty($listJadwalID)) {
            return [];
        }

        $sql = "SELECT
            rencanastudi.JadwalID,
            rencanastudi.MhswID,
            bobot_mahasiswa.ID AS BobotMahasiswaID,
            bobot_mahasiswa.ValidasiDosen,
            bobot_mahasiswa.Publish
            FROM rencanastudi
            INNER JOIN rombel ON rombel.JadwalID = rencanastudi.JadwalID
            INNER JOIN peserta_rombel ON peserta_rombel.GroupPesertaID = rombel.ID
                AND peserta_rombel.MhswID = rencanastudi.MhswID
            LEFT JOIN bobot_mahasiswa ON bobot_mahasiswa.DetailKurikulumID = rencanastudi.DetailKurikulumID
                AND bobot_mahasiswa.TahunID = rencanastudi.TahunID
                AND bobot_mahasiswa.MhswID = rencanastudi.MhswID
                AND bobot_mahasiswa.JenisBobotID='3'
            WHERE rencanastudi.JadwalID IN ('" . implode("','", $listJadwalID) . "')
            GROUP BY rencanastudi.MhswID, rencanastudi.JadwalID";

        $results = DB::select($sql);

        return array_map(function($item) {
            return (object) (array) $item;
        }, $results);
    }

    /**
     * Get peserta KRS for detail view
     * Corresponds to: C_publish_nilai_uts->detail_publish_nilai_uts_mhsw()
     */
    public function get_peserta_krs($jadwalID, $kelasID = '')
    {
        $query = DB::table('rencanastudi')
            ->select(
                'rencanastudi.ID',
                'rencanastudi.MhswID',
                'rencanastudi.JadwalID',
                'rencanastudi.NPM',
                'mahasiswa.Nama',
                'mahasiswa.NPM as NpmMhs',
                'bobot_mahasiswa.ID as BobotMahasiswaID',
                'bobot_mahasiswa.ValidasiDosen',
                'bobot_mahasiswa.Publish',
                'bobot_mahasiswa.Nilai'
            )
            ->join('mahasiswa', 'rencanastudi.MhswID', '=', 'mahasiswa.ID')
            ->leftJoin('bobot_mahasiswa', function($join) {
                $join->on('bobot_mahasiswa.DetailKurikulumID', '=', 'rencanastudi.DetailKurikulumID')
                     ->on('bobot_mahasiswa.TahunID', '=', 'rencanastudi.TahunID')
                     ->on('bobot_mahasiswa.MhswID', '=', 'rencanastudi.MhswID')
                     ->where('bobot_mahasiswa.JenisBobotID', '=', '3');
            })
            ->where('rencanastudi.JadwalID', $jadwalID);

        if ($kelasID) {
            $query->where('rencanastudi.KelasID', $kelasID);
        }

        $results = $query->get();

        return $results->map(function($item) {
            return (object) (array) $item;
        })->toArray();
    }

    /**
     * Publish/validate all UTS nilai for selected mahasiswa
     * Corresponds to: C_publish_nilai_uts->publish_all_uts()
     */
    public function publish_all_uts($jadwalID, $tahunID, $selected, $valid, $tipe = 'Publish', ?int $userID = null)
    {
        $jadwal = DB::table('jadwal')->where('ID', $jadwalID)->first();

        if (!$jadwal) {
            return [
                'status' => '0',
                'message' => 'Jadwal tidak ditemukan.'
            ];
        }

        $detailkurikulumID = $jadwal->DetailKurikulumID;
        $jenisBobotUts = 3;

        // Get bobot_mahasiswa records
        $listBobot = DB::table('bobot_mahasiswa')
            ->where('DetailKurikulumID', $detailkurikulumID)
            ->where('TahunID', $tahunID)
            ->where('JenisBobotID', $jenisBobotUts)
            ->whereIn('MhswID', $selected)
            ->get();

        $bobotMhswID = [];
        foreach ($listBobot as $id) {
            $bobotMhswID[] = $id->ID;
        }

        if (empty($bobotMhswID)) {
            return [
                'status' => '0',
                'message' => 'Tidak ada data nilai UTS untuk mahasiswa yang dipilih.'
            ];
        }

        // Update bobot_mahasiswa
        $updateData = [];
        if ($tipe == 'Publish') {
            $updateData['Publish'] = $valid;
        } elseif ($tipe == 'ValidasiDosen') {
            $updateData['ValidasiDosen'] = $valid;
        }

        $update = DB::table('bobot_mahasiswa')
            ->whereIn('ID', $bobotMhswID)
            ->update($updateData);

        if ($update) {
            // Call e-learning API if exists
            if (class_exists('\App\Services\ApiElearningService')) {
                $apiService = new \App\Services\ApiElearningService();
                $arrIntegrasi = [
                    'course_unique_id' => $jadwalID,
                ];

                if ($tipe == 'Publish') {
                    $arrIntegrasi['lock_nilai'] = $valid;
                    $apiService->lock_nilai_uts($arrIntegrasi);
                } elseif ($tipe == 'ValidasiDosen') {
                    $arrIntegrasi['validasi'] = $valid;
                    $apiService->validasi_nilai_uts($arrIntegrasi);
                }
            }

            return [
                'status' => '1',
                'message' => 'Data Berhasil diubah'
            ];
        } else {
            return [
                'status' => '0',
                'message' => 'Data Gagal diubah'
            ];
        }
    }
}
