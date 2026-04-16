<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Service untuk Set Draft Registrasi Ulang
 * Handles draft registrasi mahasiswa management
 */
class SetDraftRegistrasiUlangService
{
    private string $table = 'mahasiswa';
    private string $pk = 'ID';

    /**
     * Get mahasiswa untuk draft registrasi dengan filter
     * Only mahasiswa who are lulus USM (statuslulus_pmb='1')
     *
     * @param string $whr
     * @param string $bayar
     * @param string $orderby
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function getMahasiswaPMB(string $whr = '', string $bayar = '1', string $orderby = '', ?int $limit = null, ?int $offset = null): array
    {
        try {
            $wbayar = '';
            if ($bayar !== '' && $bayar !== '0') {
                $wbayar .= " AND mahasiswa.statusbayar_pmb = '$bayar' ";
            }

            // Only mahasiswa who are lulus USM
            $whr .= " AND jenis_mhsw = 'calon'";
            $whr .= " AND statuslulus_pmb = '1'";

            $limitClause = '';
            if ($limit !== null && $limit > 0) {
                $offset = $offset ?? 0;
                $limitClause = " LIMIT " . (int)$offset . ", " . (int)$limit;
            }

            // Query sesuai dengan CI3 model
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
                WHERE 1=1
                $whr $wbayar
                GROUP BY mahasiswa.ID
                $orderby
                $limitClause
            ");

            return array_map(fn($item) => (array) $item, $query);
        } catch (Exception $e) {
            \Log::error('SetDraftRegistrasiUlangService::getMahasiswaPMB - Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Count total mahasiswa untuk draft registrasi
     *
     * @param string $whr
     * @param string $bayar
     * @param string $orderby
     * @return int
     */
    public function countVerifikasiPMB(string $whr = '', string $bayar = '1', string $orderby = ''): int
    {
        try {
            $wbayar = '';
            if ($bayar !== '' && $bayar !== '0') {
                $wbayar .= " AND mahasiswa.statusbayar_pmb = '$bayar' ";
            }

            // Only mahasiswa who are lulus USM
            $whr .= " AND jenis_mhsw = 'calon'";
            $whr .= " AND statuslulus_pmb = '1'";

            // Query sesuai dengan CI3 model
            $result = DB::select("SELECT mahasiswa.ID
                FROM mahasiswa
                INNER JOIN pmb_tbl_gelombang_detail ON mahasiswa.gelombang_detail_pmb = pmb_tbl_gelombang_detail.id
                INNER JOIN pmb_tbl_gelombang ON pmb_tbl_gelombang_detail.gelombang_id = pmb_tbl_gelombang.id
                WHERE 1=1
                $whr $wbayar
                GROUP BY mahasiswa.ID
            ");

            return count($result);
        } catch (Exception $e) {
            \Log::error('SetDraftRegistrasiUlangService::countVerifikasiPMB - Error: ' . $e->getMessage());
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
            \Log::error('SetDraftRegistrasiUlangService::get_id - Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update status draft registrasi mahasiswa
     *
     * @param int $mahasiswa_id
     * @param int $status (0=batalkan, 1=sudah registrasi, 2=tidak registrasi)
     * @return bool
     */
    public function updateStatusDraftRegistrasi(int $mahasiswa_id, int $status): bool
    {
        try {
            $result = DB::table($this->table)
                ->where($this->pk, $mahasiswa_id)
                ->update(['statusdraftregistrasi_pmb' => $status]);
            
            // Return true if update was successful (affected rows > 0)
            return $result > 0;
        } catch (Exception $e) {
            \Log::error('SetDraftRegistrasiUlangService::updateStatusDraftRegistrasi - Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get detail draft tagihan for mahasiswa
     *
     * @param int $mahasiswa_id
     * @return array
     */
    public function getDetailDraft(int $mahasiswa_id): array
    {
        try {
            $draft_tagihan = DB::table('draft_tagihan_mahasiswa')
                ->where('MhswID', $mahasiswa_id)
                ->get();

            // Group by DraftTagihanMahasiswaSemesterID
            $query = [];
            foreach ($draft_tagihan as $row_draft) {
                $query[$row_draft->DraftTagihanMahasiswaSemesterID][] = (array) $row_draft;
            }

            return $query;
        } catch (Exception $e) {
            \Log::error('SetDraftRegistrasiUlangService::getDetailDraft - Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all jenis biaya
     *
     * @return array
     */
    public function getAllJenisBiaya(): array
    {
        try {
            $result = DB::table('jenisbiaya')
                ->orderBy('ID', 'ASC')
                ->get();

            $jenisbiaya = [];
            foreach ($result as $row) {
                $jenisbiaya[$row->ID] = (array) $row;
            }

            return $jenisbiaya;
        } catch (Exception $e) {
            \Log::error('SetDraftRegistrasiUlangService::getAllJenisBiaya - Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all master diskon
     *
     * @return array
     */
    public function getAllMasterDiskon(): array
    {
        try {
            $result = DB::table('master_diskon')
                ->orderBy('ID', 'ASC')
                ->get();

            $master_diskon = [];
            foreach ($result as $row) {
                $master_diskon[$row->ID] = (array) $row;
            }

            return $master_diskon;
        } catch (Exception $e) {
            \Log::error('SetDraftRegistrasiUlangService::getAllMasterDiskon - Error: ' . $e->getMessage());
            return [];
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
            \Log::error('SetDraftRegistrasiUlangService::getScoreUjian - Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get all prodi with jenjang
     *
     * @return array
     */
    public function getAllProdi(): array
    {
        try {
            $all_jenjang = [];
            $all_prodi = [];

            $result = DB::table('programstudi')
                ->orderBy('ID', 'ASC')
                ->get();

            foreach ($result as $row_prodi) {
                $row_prodi = (array) $row_prodi;

                if (!isset($all_jenjang[$row_prodi['JenjangID']])) {
                    $jenjang = DB::table('jenjang')
                        ->where('ID', $row_prodi['JenjangID'])
                        ->first();
                    $all_jenjang[$row_prodi['JenjangID']] = $jenjang ? (array) $jenjang : [];
                }

                $row_prodi['NamaJenjang'] = $all_jenjang[$row_prodi['JenjangID']]['Nama'] ?? '';
                $all_prodi[$row_prodi['ID']] = $row_prodi;
            }

            return $all_prodi;
        } catch (Exception $e) {
            \Log::error('SetDraftRegistrasiUlangService::getAllProdi - Error: ' . $e->getMessage());
            return [];
        }
    }
}
