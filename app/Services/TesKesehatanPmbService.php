<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Service untuk Tes Kesehatan PMB
 * Handles tes kesehatan mahasiswa management
 */
class TesKesehatanPmbService
{
    private string $table = 'mahasiswa';
    private string $pk = 'ID';

    /**
     * Get mahasiswa untuk tes kesehatan dengan filter
     * 
     * @param array $filters
     * @param string $bayar
     * @param string $orderby
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function getMahasiswaPMB(
        array $filters = [],
        string $bayar = '1',
        string $orderby = '',
        ?int $limit = null,
        ?int $offset = null
    ): array {
        try {
            // Get jenis biaya tagihan_kesehatan
            $tagihan_biaya_id = DB::table('jenisbiaya')
                ->where('Kode', 'TSKSHTN')
                ->value('ID');

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
                statussipil.Nama as statussipilNama
                FROM mahasiswa
                LEFT JOIN program ON program.ID = mahasiswa.ProgramID
                INNER JOIN programstudi ON programstudi.ID = mahasiswa.pilihan1
                LEFT JOIN agama ON agama.ID = mahasiswa.AgamaID
                LEFT JOIN statussipil ON statussipil.Kode = mahasiswa.StatusSipil
                INNER JOIN pmb_tbl_gelombang_detail ON pmb_tbl_gelombang_detail.id = mahasiswa.gelombang_detail_pmb
                INNER JOIN pmb_tbl_gelombang ON pmb_tbl_gelombang_detail.gelombang_id = pmb_tbl_gelombang.id
                INNER JOIN tagihan_mahasiswa ON tagihan_mahasiswa.MhswID = mahasiswa.ID AND JenisBiayaID = '$tagihan_biaya_id'
                WHERE (mahasiswa.jenis_mhsw = 'calon' OR mahasiswa.statuslulus_pmb = '1') 
                AND statusBayarBiayaKesehatan = 0
                $whr $wbayar
                GROUP BY mahasiswa.ID
                $orderby
                " . ($limit !== null ? "LIMIT $limit OFFSET $offset" : "")
            );

            return array_map(fn($item) => (array) $item, $query);
        } catch (Exception $e) {
            \Log::error('TesKesehatanPmbService::getMahasiswaPMB - Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Count total mahasiswa untuk tes kesehatan
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
            // Get jenis biaya tagihan_kesehatan
            $tagihan_biaya_id = DB::table('jenisbiaya')
                ->where('Kode', 'TSKSHTN')
                ->value('ID');

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
                INNER JOIN tagihan_mahasiswa ON tagihan_mahasiswa.MhswID = mahasiswa.ID AND JenisBiayaID = '$tagihan_biaya_id'
                WHERE (mahasiswa.jenis_mhsw = 'calon' OR mahasiswa.statuslulus_pmb = '1')
                AND statusBayarBiayaKesehatan = 0
                $whr $wbayar
                $orderby
            ");

            return (int) ($result[0]->c ?? 0);
        } catch (Exception $e) {
            \Log::error('TesKesehatanPmbService::countVerifikasiPMB - Error: ' . $e->getMessage());
            return 0;
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
            $result = DB::table($this->table)->where($this->pk, $id)->first();
            return $result ? (array) $result : null;
        } catch (Exception $e) {
            \Log::error('TesKesehatanPmbService::get_id - Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update status bayar biaya kesehatan
     * 
     * @param int $mahasiswa_id
     * @param int $status
     * @return bool
     */
    public function updateStatusKesehatan(int $mahasiswa_id, int $status): bool
    {
        try {
            return DB::table($this->table)
                ->where($this->pk, $mahasiswa_id)
                ->update(['statusBayarBiayaKesehatan' => $status]);
        } catch (Exception $e) {
            \Log::error('TesKesehatanPmbService::updateStatusKesehatan - Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get data for export
     * 
     * @param array $filters
     * @param string $bayar
     * @return array
     */
    public function getExportData(array $filters = [], string $bayar = '1'): array
    {
        return $this->getMahasiswaPMB($filters, $bayar, 'ORDER BY mahasiswa.Nama ASC');
    }

    /**
     * Get tagihan biaya kesehatan ID
     * 
     * @return int|null
     */
    public function getTagihanKesehatanID(): ?int
    {
        try {
            return DB::table('jenisbiaya')
                ->where('Kode', 'TSKSHTN')
                ->value('ID');
        } catch (Exception $e) {
            \Log::error('TesKesehatanPmbService::getTagihanKesehatanID - Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Save hasil tes kesehatan from Excel upload
     * 
     * @param array $data
     * @return array ['success' => count, 'failed' => count]
     */
    public function saveHasilTes(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $success = 0;
                $failed = 0;

                foreach ($data as $row) {
                    $mahasiswa_id = $row['mahasiswa_id'] ?? null;
                    $nilai = $row['nilai'] ?? null;

                    if (!$mahasiswa_id || $nilai === null) {
                        $failed++;
                        continue;
                    }

                    // Update nilai PMB mahasiswa via API or direct update
                    $updated = DB::table('mahasiswa')
                        ->where('ID', $mahasiswa_id)
                        ->update(['NilaiTesKesehatan' => $nilai]);

                    if ($updated) {
                        $success++;
                    } else {
                        $failed++;
                    }
                }

                return ['success' => $success, 'failed' => $failed];
            });
        } catch (Exception $e) {
            \Log::error('TesKesehatanPmbService::saveHasilTes - Error: ' . $e->getMessage());
            return ['success' => 0, 'failed' => 0];
        }
    }

    /**
     * Update status lulus tes kesehatan
     * 
     * @param int $mahasiswa_id
     * @param int $status (0=batalkan, 1=lulus, 2=tidak lulus)
     * @return bool
     */
    public function updateStatusLulusKesehatan(int $mahasiswa_id, int $status): bool
    {
        try {
            return DB::table('mahasiswa')
                ->where('ID', $mahasiswa_id)
                ->update(['statusBayarBiayaKesehatan' => $status]);
        } catch (Exception $e) {
            \Log::error('TesKesehatanPmbService::updateStatusLulusKesehatan - Error: ' . $e->getMessage());
            return false;
        }
    }
}
