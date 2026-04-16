<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Service for Set Registrasi Ulang
 * Handles mahasiswa registration status management
 */
class SetRegistrasiUlangService
{
    /**
     * Get mahasiswa with registration status filters
     * 
     * @param string $whr
     * @param string $bayar
     * @param string $orderby
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function getMahasiswaPMBRegis(string $whr = '', string $bayar = '1', string $orderby = '', ?int $limit = null, ?int $offset = null): array
    {
        try {
            $wbayar = '';
            if ($bayar !== '' && $bayar !== '0') {
                $wbayar .= " AND statusbayar_pmb = '$bayar' ";
            }

            // Only mahasiswa who are lulus USM
            $whr .= " AND jenis_mhsw = 'calon'";
            $whr .= " AND statuslulus_pmb = '1'";

            $limitClause = '';
            if ($limit !== null) {
                $limitClause = " LIMIT " . (int)$limit;
            }
            if ($offset !== null) {
                $limitClause .= " OFFSET " . (int)$offset;
            }

            $query = DB::select("SELECT mahasiswa.*,
                program.Nama as programNama,
                programstudi.Nama as prodiNama,
                pmb_tbl_gelombang.nama as gelombangNama,
                agama.Nama as agamaNama,
                statussipil.Nama as statussipilNama,
                COUNT(pmb_tbl_kategori_soal.id) AS jumlahUjian,
                COUNT(pmb_tbl_hasil_test.id) AS jumlahSelesai
                FROM mahasiswa
                LEFT JOIN program ON program.ID = mahasiswa.ProgramID
                INNER JOIN programstudi ON programstudi.ID = mahasiswa.pilihan1
                LEFT JOIN agama ON agama.ID = mahasiswa.AgamaID
                LEFT JOIN statussipil ON statussipil.Kode = mahasiswa.StatusSipil
                INNER JOIN pmb_tbl_gelombang_detail ON pmb_tbl_gelombang_detail.id = mahasiswa.gelombang_detail_pmb
                INNER JOIN pmb_tbl_gelombang ON pmb_tbl_gelombang_detail.gelombang_id = pmb_tbl_gelombang.id
                LEFT JOIN pmb_edu_jadwalusm ON pmb_edu_jadwalusm.gelombang = pmb_tbl_gelombang.id
                LEFT JOIN pmb_edu_map_peserta ON pmb_edu_map_peserta.idjadwal = pmb_edu_jadwalusm.id 
                    AND pmb_edu_map_peserta.idpendaftaran = mahasiswa.ID
                LEFT JOIN pmb_tbl_kategori_soal ON pmb_tbl_kategori_soal.id = pmb_edu_jadwalusm.kategori_soal_id
                LEFT JOIN pmb_tbl_hasil_test ON pmb_tbl_hasil_test.idmember = mahasiswa.id
                WHERE 1=1
                $whr $wbayar
                GROUP BY mahasiswa.ID
                $orderby
                $limitClause
            ");

            return array_map(fn($item) => (array) $item, $query);
        } catch (Exception $e) {
            \Log::error('SetRegistrasiUlangService::getMahasiswaPMBRegis - Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Count total mahasiswa with registration status
     * 
     * @param string $whr
     * @param string $bayar
     * @param string $orderby
     * @return int
     */
    public function countVerifikasiPMBRegis(string $whr = '', string $bayar = '1', string $orderby = ''): int
    {
        try {
            $wbayar = '';
            if ($bayar !== '' && $bayar !== '0') {
                $wbayar .= " AND mahasiswa.statusbayar_pmb = '$bayar' ";
            }

            // Only mahasiswa who are lulus USM
            $whr .= " AND jenis_mhsw = 'calon'";
            $whr .= " AND statuslulus_pmb = '1'";

            $result = DB::select("SELECT COUNT(DISTINCT mahasiswa.ID) as c
                FROM mahasiswa
                INNER JOIN pmb_tbl_gelombang_detail ON mahasiswa.gelombang_detail_pmb = pmb_tbl_gelombang_detail.id
                INNER JOIN pmb_tbl_gelombang ON pmb_tbl_gelombang_detail.gelombang_id = pmb_tbl_gelombang.id
                LEFT JOIN pmb_edu_jadwalusm ON pmb_edu_jadwalusm.gelombang = pmb_tbl_gelombang.id
                LEFT JOIN pmb_edu_map_peserta ON pmb_edu_map_peserta.idjadwal = pmb_edu_jadwalusm.id 
                    AND pmb_edu_map_peserta.idpendaftaran = mahasiswa.ID
                LEFT JOIN pmb_tbl_kategori_soal ON pmb_tbl_kategori_soal.id = pmb_edu_jadwalusm.kategori_soal_id
                LEFT JOIN pmb_tbl_hasil_test ON pmb_tbl_hasil_test.idmember = mahasiswa.id
                WHERE 1=1
                $whr $wbayar
                $orderby
            ");

            return (int) ($result[0]->c ?? 0);
        } catch (Exception $e) {
            \Log::error('SetRegistrasiUlangService::countVerifikasiPMBRegis - Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update status registrasi mahasiswa
     * 
     * @param int $mahasiswa_id
     * @param int $status (0=batalkan, 1=sudah registrasi, 2=tidak registrasi)
     * @return bool
     */
    public function updateStatusRegistrasi(int $mahasiswa_id, int $status): bool
    {
        try {
            return DB::table('mahasiswa')
                ->where('ID', $mahasiswa_id)
                ->update(['statusregistrasi_pmb' => $status]);
        } catch (Exception $e) {
            \Log::error('SetRegistrasiUlangService::updateStatusRegistrasi - Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get single mahasiswa by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function get_id(int $id): ?array
    {
        try {
            $result = DB::table('mahasiswa')->where('ID', $id)->first();
            return $result ? (array) $result : null;
        } catch (Exception $e) {
            \Log::error('SetRegistrasiUlangService::get_id - Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get score ujian mahasiswa
     *
     * @param int $mahasiswa_id
     * @return float
     */
    public function getScoreUjian(int $mahasiswa_id): float
    {
        try {
            $result = DB::table('pmb_tbl_hasil_test')
                ->where('idmember', $mahasiswa_id)
                ->select(DB::raw('SUM(score) as jml'))
                ->first();

            return (float) ($result->jml ?? 0);
        } catch (Exception $e) {
            \Log::error('SetRegistrasiUlangService::getScoreUjian - Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get jumlah tagihan for mahasiswa
     *
     * @param int $mahasiswa_id
     * @return float
     */
    public function getJumlahTagihan(int $mahasiswa_id): float
    {
        try {
            $result = DB::table('draft_tagihan_mahasiswa')
                ->where('MhswID', $mahasiswa_id)
                ->where('JenisMahasiswa', 'calon')
                ->select(DB::raw('SUM(Jumlah) as jml'))
                ->first();

            return (float) ($result->jml ?? 0);
        } catch (Exception $e) {
            \Log::error('SetRegistrasiUlangService::getJumlahTagihan - Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Check if mahasiswa has cicilan registrasiulang
     *
     * @param int $mahasiswa_id
     * @return object|null
     */
    public function checkCicilanRegistrasiulang(int $mahasiswa_id): ?object
    {
        try {
            $result = DB::table('cicilan_tagihan_mahasiswa')
                ->join('tagihan_mahasiswa', 'tagihan_mahasiswa.ID', '=', 'cicilan_tagihan_mahasiswa.TagihanMahasiswaID')
                ->where('tagihan_mahasiswa.MhswID', $mahasiswa_id)
                ->where('tagihan_mahasiswa.JenisBiayaID', '!=', 32)
                ->select('cicilan_tagihan_mahasiswa.ID')
                ->first();

            return $result;
        } catch (Exception $e) {
            \Log::error('SetRegistrasiUlangService::checkCicilanRegistrasiulang - Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update status registrasi PMB mahasiswa (API replacement)
     *
     * @param int $mahasiswa_id
     * @param int $status
     * @param int $user_id
     * @return array ['status' => int, 'message' => string, 'double_diskon' => int]
     */
    public function updateStatusRegistrasiPMBMahasiswa(int $mahasiswa_id, int $status, int $user_id): array
    {
        try {
            // Check for double diskon
            $double_diskon = 0;
            $message = '';

            // Get mahasiswa data
            $mahasiswa = DB::table('mahasiswa')->where('ID', $mahasiswa_id)->first();

            if (!$mahasiswa) {
                return [
                    'status' => 0,
                    'message' => 'Mahasiswa tidak ditemukan',
                    'double_diskon' => 0
                ];
            }

            // Check for double diskon logic
            // This is a simplified version - you may need to adjust based on your business logic
            if ($status == 1) {
                // Check if already has registration status
                if ($mahasiswa->statusregistrasi_pmb == 1) {
                    // Already registered, check for double diskon
                    $double_diskon = $this->checkDoubleDiskon($mahasiswa_id);
                    if ($double_diskon) {
                        return [
                            'status' => 0,
                            'message' => 'Diskon double terdeteksi',
                            'double_diskon' => 1
                        ];
                    }
                }
            }

            // Update status
            $updated = DB::table('mahasiswa')
                ->where('ID', $mahasiswa_id)
                ->update(['statusregistrasi_pmb' => $status]);

            if ($updated) {
                return [
                    'status' => 1,
                    'message' => 'Status berhasil diubah',
                    'double_diskon' => 0
                ];
            } else {
                return [
                    'status' => 0,
                    'message' => 'Gagal mengubah status',
                    'double_diskon' => 0
                ];
            }
        } catch (Exception $e) {
            \Log::error('SetRegistrasiUlangService::updateStatusRegistrasiPMBMahasiswa - Error: ' . $e->getMessage());
            return [
                'status' => 0,
                'message' => 'Terjadi kesalahan',
                'double_diskon' => 0
            ];
        }
    }

    /**
     * Check if mahasiswa has double diskon
     *
     * @param int $mahasiswa_id
     * @return bool
     */
    private function checkDoubleDiskon(int $mahasiswa_id): bool
    {
        try {
            // Check for duplicate diskon in draft_tagihan_mahasiswa
            // This logic may need to be adjusted based on your specific business rules
            $count = DB::table('draft_tagihan_mahasiswa')
                ->where('MhswID', $mahasiswa_id)
                ->where('JenisMahasiswa', 'calon')
                ->where('Diskon', '>', 0)
                ->count();

            return $count > 1;
        } catch (Exception $e) {
            \Log::error('SetRegistrasiUlangService::checkDoubleDiskon - Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Save registration status for multiple students
     *
     * @param array $data
     * @return array ['success' => count, 'failed' => count]
     */
    public function save(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $success = 0;
                $failed = 0;

                $MhswIDs = $data['checkID'] ?? [];
                $statusregistrasi = $data['statusregistrasi'] ?? 1;

                foreach ($MhswIDs as $MhswID) {
                    $updated = DB::table('mahasiswa')
                        ->where('ID', $MhswID)
                        ->update(['statusregistrasi_pmb' => $statusregistrasi]);

                    if ($updated) {
                        $success++;
                    } else {
                        $failed++;
                    }
                }

                return ['success' => $success, 'failed' => $failed];
            });
        } catch (Exception $e) {
            \Log::error('SetRegistrasiUlangService::save - Error: ' . $e->getMessage());
            return ['success' => 0, 'failed' => 0];
        }
    }
}
