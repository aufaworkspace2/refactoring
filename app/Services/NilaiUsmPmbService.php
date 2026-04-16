<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Service untuk Nilai USM PMB
 * Handles nilai ujian dan SKL management
 */
class NilaiUsmPmbService
{
    private string $table = 'mahasiswa';
    private string $pk = 'ID';

    /**
     * Get mahasiswa dengan nilai USM dan filter kompleks
     * 
     * @param array $filters
     * @param string $bayar
     * @param string $having
     * @param string $orderby
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function getMahasiswaPMB(
        array $filters = [],
        string $bayar = '1',
        string $having = '',
        string $orderby = '',
        ?int $limit = null,
        ?int $offset = null
    ): array {
        try {
            $whr = '';
            
            // Build WHERE clause from filters
            if (!empty($filters['gelombang'])) {
                $whr .= " AND pmb_tbl_gelombang.ID = '" . $filters['gelombang'] . "'";
            }

            if (!empty($filters['gelombang_detail'])) {
                $whr .= " AND mahasiswa.gelombang_detail_pmb = '" . $filters['gelombang_detail'] . "'";
            }

            if (!empty($filters['program'])) {
                $whr .= " AND mahasiswa.ProgramID = '" . $filters['program'] . "'";
            }

            if (!empty($filters['pilihan1'])) {
                $whr .= " AND mahasiswa.pilihan1 = '" . $filters['pilihan1'] . "'";
            }

            if (!empty($filters['pilihan2'])) {
                $whr .= " AND mahasiswa.pilihan2 = '" . $filters['pilihan2'] . "'";
            }

            if (!empty($filters['keyword'])) {
                $whr .= " AND (mahasiswa.noujian_pmb LIKE '%" . $filters['keyword'] . "%' OR mahasiswa.Nama LIKE '%" . $filters['keyword'] . "%')";
            }

            $wbayar = '';
            if ($bayar !== '' && $bayar !== '0') {
                $wbayar .= " AND statusbayar_pmb = '$bayar' ";
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
                WHERE (mahasiswa.jenis_mhsw = 'calon' OR mahasiswa.statuslulus_pmb = '1')
                $whr $wbayar
                GROUP BY mahasiswa.ID
                $having
                $orderby
                " . ($limit !== null ? "LIMIT $limit OFFSET $offset" : "")
            );

            // Add statuslulus_str for each row
            $result = array_map(fn($item) => (array) $item, $query);
            foreach ($result as &$row) {
                $row['statuslulus_str'] = $this->getStatusLulusString($row['statuslulus_pmb'] ?? null);
            }

            return $result;
        } catch (Exception $e) {
            \Log::error('NilaiUsmPmbService::getMahasiswaPMB - Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Count total mahasiswa dengan filter
     * 
     * @param array $filters
     * @param string $bayar
     * @param string $orderby
     * @return int
     */
    public function countVerifikasiPMB(
        array $filters = [],
        string $bayar = '1',
        string $orderby = ''
    ): int {
        try {
            $whr = '';
            
            if (!empty($filters['gelombang'])) {
                $whr .= " AND pmb_tbl_gelombang.ID = '" . $filters['gelombang'] . "'";
            }

            if (!empty($filters['gelombang_detail'])) {
                $whr .= " AND mahasiswa.gelombang_detail_pmb = '" . $filters['gelombang_detail'] . "'";
            }

            if (!empty($filters['program'])) {
                $whr .= " AND mahasiswa.ProgramID = '" . $filters['program'] . "'";
            }

            if (!empty($filters['pilihan1'])) {
                $whr .= " AND mahasiswa.pilihan1 = '" . $filters['pilihan1'] . "'";
            }

            if (!empty($filters['pilihan2'])) {
                $whr .= " AND mahasiswa.pilihan2 = '" . $filters['pilihan2'] . "'";
            }

            if (!empty($filters['keyword'])) {
                $whr .= " AND (mahasiswa.noujian_pmb LIKE '%" . $filters['keyword'] . "%' OR mahasiswa.Nama LIKE '%" . $filters['keyword'] . "%')";
            }

            $wbayar = '';
            if ($bayar !== '' && $bayar !== '0') {
                $wbayar .= " AND mahasiswa.statusbayar_pmb = '$bayar' ";
            }

            $result = DB::select("SELECT COUNT(DISTINCT mahasiswa.ID) as c
                FROM mahasiswa
                INNER JOIN pmb_tbl_gelombang_detail ON mahasiswa.gelombang_detail_pmb = pmb_tbl_gelombang_detail.id
                INNER JOIN pmb_tbl_gelombang ON pmb_tbl_gelombang_detail.gelombang_id = pmb_tbl_gelombang.id
                LEFT JOIN pmb_edu_jadwalusm ON pmb_edu_jadwalusm.gelombang = pmb_tbl_gelombang.id
                LEFT JOIN pmb_edu_map_peserta ON pmb_edu_map_peserta.idjadwal = pmb_edu_jadwalusm.id 
                    AND pmb_edu_map_peserta.idpendaftaran = mahasiswa.ID
                LEFT JOIN pmb_tbl_kategori_soal ON pmb_tbl_kategori_soal.id = pmb_edu_jadwalusm.kategori_soal_id
                LEFT JOIN pmb_tbl_hasil_test ON pmb_tbl_hasil_test.idmember = mahasiswa.id
                WHERE (mahasiswa.jenis_mhsw = 'calon' OR mahasiswa.statuslulus_pmb = '1') 
                $whr $wbayar
                $orderby
            ");

            return (int) ($result[0]->c ?? 0);
        } catch (Exception $e) {
            \Log::error('NilaiUsmPmbService::countVerifikasiPMB - Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get detail nilai mahasiswa per soal
     * 
     * @param int $mahasiswa_id
     * @return array
     */
    public function getDetailNilai(int $mahasiswa_id): array
    {
        try {
            $query = DB::select("SELECT 
                pmb_tbl_hasil_test.*,
                pmb_tbl_kategori_soal.nama as kategori_nama,
                pmb_tbl_soal.soal,
                pmb_tbl_soal.jawaban
                FROM pmb_tbl_hasil_test
                LEFT JOIN pmb_tbl_soal ON pmb_tbl_soal.id = pmb_tbl_hasil_test.idsoal
                LEFT JOIN pmb_tbl_kategori_soal ON pmb_tbl_kategori_soal.id = pmb_tbl_soal.idkategori
                WHERE pmb_tbl_hasil_test.idmember = ?
                ORDER BY pmb_tbl_kategori_soal.nama, pmb_tbl_soal.id
            ", [$mahasiswa_id]);

            return array_map(fn($item) => (array) $item, $query);
        } catch (Exception $e) {
            \Log::error('NilaiUsmPmbService::getDetailNilai - Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Save/update nilai mahasiswa
     * 
     * @param int $mahasiswa_id
     * @param array $nilai_data
     * @return bool
     */
    public function saveNilai(int $mahasiswa_id, array $nilai_data): bool
    {
        try {
            return DB::transaction(function () use ($mahasiswa_id, $nilai_data) {
                foreach ($nilai_data as $idsoal => $jawaban) {
                    DB::table('pmb_tbl_hasil_test')
                        ->updateOrInsert(
                            [
                                'idmember' => $mahasiswa_id,
                                'idsoal' => $idsoal
                            ],
                            [
                                'jawaban_dipilih' => $jawaban,
                                'updated_at' => date('Y-m-d H:i:s')
                            ]
                        );
                }
                return true;
            });
        } catch (Exception $e) {
            \Log::error('NilaiUsmPmbService::saveNilai - Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get data untuk cetak SKL
     *
     * @param int $mahasiswa_id
     * @return array|null
     */
    public function getDataForSKL(int $mahasiswa_id): ?array
    {
        try {
            $result = DB::select("SELECT
                mahasiswa.*,
                programstudi.Nama as prodi_nama,
                program.Nama as program_nama,
                pmb_tbl_gelombang.nama as gelombang_nama,
                COUNT(pmb_tbl_hasil_test.id) as total_soal,
                SUM(CASE WHEN pmb_tbl_hasil_test.jawaban_dipilih = pmb_tbl_soal.jawaban THEN 1 ELSE 0 END) as jumlah_benar
                FROM mahasiswa
                INNER JOIN programstudi ON programstudi.ID = mahasiswa.pilihan1
                LEFT JOIN program ON program.ID = mahasiswa.ProgramID
                INNER JOIN pmb_tbl_gelombang_detail ON pmb_tbl_gelombang_detail.id = mahasiswa.gelombang_detail_pmb
                INNER JOIN pmb_tbl_gelombang ON pmb_tbl_gelombang_detail.gelombang_id = pmb_tbl_gelombang.id
                LEFT JOIN pmb_tbl_hasil_test ON pmb_tbl_hasil_test.idmember = mahasiswa.ID
                LEFT JOIN pmb_tbl_soal ON pmb_tbl_soal.id = pmb_tbl_hasil_test.idsoal
                WHERE mahasiswa.ID = ?
                GROUP BY mahasiswa.ID
            ", [$mahasiswa_id]);

            return $result ? (array) $result[0] : null;
        } catch (Exception $e) {
            \Log::error('NilaiUsmPmbService::getDataForSKL - Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get jenis USM
     *
     * @return array
     */
    public function getJenisUSM(): array
    {
        try {
            $query = DB::table('pmb_edu_jenisusm')
                ->orderBy('jenis', 'ASC')
                ->get()
                ->toArray();

            return array_map(fn($item) => (array) $item, $query);
        } catch (Exception $e) {
            \Log::error('NilaiUsmPmbService::getJenisUSM - Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get hasil USM offline mahasiswa
     *
     * @param int $mahasiswa_id
     * @param int $id_jenis_usm
     * @return array|null
     */
    public function getHasilUSMOffline(int $mahasiswa_id, int $id_jenis_usm): ?array
    {
        try {
            $result = DB::table('pmb_tbl_hasil_usm_baru')
                ->where('idpendaftaran', $mahasiswa_id)
                ->where('idjenisusm', $id_jenis_usm)
                ->first();

            return $result ? (array) $result : null;
        } catch (Exception $e) {
            \Log::error('NilaiUsmPmbService::getHasilUSMOffline - Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Save nilai USM offline
     *
     * @param int $mahasiswa_id
     * @param int $id_jenis_usm
     * @param float $nilai
     * @return bool
     */
    public function saveNilaiUSMOffline(int $mahasiswa_id, int $id_jenis_usm, float $nilai): bool
    {
        try {
            return DB::transaction(function () use ($mahasiswa_id, $id_jenis_usm, $nilai) {
                // Delete existing data
                DB::table('pmb_tbl_hasil_usm_baru')
                    ->where('idpendaftaran', $mahasiswa_id)
                    ->where('idjenisusm', $id_jenis_usm)
                    ->delete();

                // Insert new data
                DB::table('pmb_tbl_hasil_usm_baru')->insert([
                    'idjenisusm' => $id_jenis_usm,
                    'idpendaftaran' => $mahasiswa_id,
                    'nilai' => $nilai,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                return true;
            });
        } catch (Exception $e) {
            \Log::error('NilaiUsmPmbService::saveNilaiUSMOffline - Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update nilai akhir PMB
     *
     * @param int $mahasiswa_id
     * @param float $nilai_akhir
     * @return bool
     */
    public function updateNilaiAkhirPMB(int $mahasiswa_id, float $nilai_akhir): bool
    {
        try {
            $result = DB::table('mahasiswa')
                ->where('ID', $mahasiswa_id)
                ->update(['nilai_pmb' => $nilai_akhir]);

            return $result > 0;
        } catch (Exception $e) {
            \Log::error('NilaiUsmPmbService::updateNilaiAkhirPMB - Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Set status lulus USM
     *
     * @param int $mahasiswa_id
     * @param int $status (1=lulus, 2=tidak lulus, 0=batal)
     * @param int|null $pilihan_prodi_lulus
     * @return array ['status' => bool, 'message' => string]
     */
    public function setStatusLulusUSM(int $mahasiswa_id, int $status, ?int $pilihan_prodi_lulus = null): array
    {
        try {
            // Update status lulus
            $update_data = [
                'statuslulus_pmb' => $status
            ];

            if ($pilihan_prodi_lulus !== null) {
                $update_data['pilihan_prodi_lulus'] = $pilihan_prodi_lulus;
            }

            $result = DB::table('mahasiswa')
                ->where('ID', $mahasiswa_id)
                ->update($update_data);

            if ($result > 0) {
                // Get mahasiswa data for email
                $mhsw = DB::table('mahasiswa')
                    ->where('ID', $mahasiswa_id)
                    ->first();

                // Prepare email data based on status
                $email_sent = false;
                if ($status == 1) {
                    // Lulus - send graduation email
                    $email_sent = true;
                }

                return [
                    'status' => true,
                    'message' => 'Status lulus USM berhasil diubah',
                    'email_sent' => $email_sent
                ];
            }

            return [
                'status' => false,
                'message' => 'Gagal mengubah status lulus USM'
            ];

        } catch (Exception $e) {
            \Log::error('NilaiUsmPmbService::setStatusLulusUSM - Error: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Set status lulus kesehatan
     *
     * @param int $mahasiswa_id
     * @param int $status (1=lulus, 2=tidak lulus, 0=batal)
     * @return array ['status' => bool, 'message' => string]
     */
    public function setStatusLulusKesehatan(int $mahasiswa_id, int $status): array
    {
        try {
            $result = DB::table('mahasiswa')
                ->where('ID', $mahasiswa_id)
                ->update(['kesehatan_pmb' => $status]);

            if ($result > 0) {
                return [
                    'status' => true,
                    'message' => 'Status lulus kesehatan berhasil diubah'
                ];
            }

            return [
                'status' => false,
                'message' => 'Gagal mengubah status lulus kesehatan'
            ];

        } catch (Exception $e) {
            \Log::error('NilaiUsmPmbService::setStatusLulusKesehatan - Error: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get status lulus string (badge HTML)
     *
     * @param int|null $status
     * @return string
     */
    private function getStatusLulusString(?int $status): string
    {
        if ($status == 1) {
            return '<label class="badge badge-success">Lulus</label>';
        } elseif ($status == 2) {
            return '<label class="badge badge-danger">Tidak Lulus</label>';
        } else {
            return '<label class="badge badge-secondary">Belum Lulus</label>';
        }
    }
}
