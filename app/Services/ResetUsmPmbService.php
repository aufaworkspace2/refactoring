<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Service untuk Reset USM PMB
 * Handles mahasiswa USM test reset operations
 */
class ResetUsmPmbService
{
    /**
     * Get mahasiswa PMB dengan filter kompleks
     * 
     * @param string $whr WHERE clause conditions
     * @param string $bayar Payment status filter
     * @param string $orderby_calon ORDER BY clause
     * @return array Query results
     */
    public function getMahasiswaPMB(string $whr = '', string $bayar = '', string $orderby_calon = ''): array
    {
        try {
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
                                      COUNT(pmb_tbl_hasil_test.id) AS jumlahSelesai,
                                      pmb_edu_jenisusm.nama as namajenisusm,
                                      pmb_tbl_hasil_test.id as id_hasil_test,
                                      pmb_tbl_hasil_test.score
                                FROM mahasiswa
                                LEFT JOIN program ON program.ID=mahasiswa.ProgramID
                                INNER JOIN programstudi ON programstudi.ID=mahasiswa.pilihan1
                                LEFT JOIN agama ON agama.ID=mahasiswa.AgamaID
                                LEFT JOIN statussipil ON statussipil.Kode=mahasiswa.StatusSipil
                                INNER JOIN pmb_tbl_gelombang_detail ON pmb_tbl_gelombang_detail.id=mahasiswa.gelombang_detail_pmb
                                INNER JOIN pmb_tbl_gelombang ON pmb_tbl_gelombang_detail.gelombang_id=pmb_tbl_gelombang.id
                                LEFT JOIN pmb_edu_jadwalusm on pmb_edu_jadwalusm.gelombang=pmb_tbl_gelombang.id
                                LEFT JOIN pmb_edu_map_peserta on pmb_edu_map_peserta.idjadwal=pmb_edu_jadwalusm.id and pmb_edu_map_peserta.idpendaftaran=mahasiswa.ID
                                LEFT JOIN pmb_tbl_kategori_soal ON pmb_tbl_kategori_soal.id = pmb_edu_jadwalusm.kategori_soal_id
                                INNER JOIN pmb_tbl_hasil_test ON pmb_tbl_hasil_test.idmember = mahasiswa.id
                                LEFT JOIN pmb_edu_jenisusm ON pmb_edu_jenisusm.id = pmb_tbl_hasil_test.jenis
                                WHERE (jenis_mhsw='calon' OR statuslulus_pmb='1') $whr $wbayar 
                                GROUP BY mahasiswa.ID,pmb_tbl_hasil_test.id 
                                $orderby_calon");

            return $query ?: [];
        } catch (Exception $e) {
            \Log::error('ResetUsmPmbService::getMahasiswaPMB - Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Count verifikasi PMB dengan filter
     * 
     * @param string $whr WHERE clause conditions
     * @param string $bayar Payment status filter
     * @param string $orderby_calon ORDER BY clause
     * @return int Total count
     */
    public function countVerifikasiPMB(string $whr = '', string $bayar = '', string $orderby_calon = ''): int
    {
        try {
            $wbayar = '';
            if ($bayar !== '' && $bayar !== '0') {
                $wbayar .= " AND mahasiswa.statusbayar_pmb='$bayar' ";
            }

            $query = DB::select("SELECT mahasiswa.ID
                               FROM mahasiswa
                               INNER JOIN pmb_tbl_gelombang_detail
                               ON mahasiswa.gelombang_detail_pmb=pmb_tbl_gelombang_detail.id
                               INNER JOIN pmb_tbl_gelombang
                               ON pmb_tbl_gelombang_detail.gelombang_id=pmb_tbl_gelombang.id
                               LEFT JOIN pmb_edu_jadwalusm on pmb_edu_jadwalusm.gelombang=pmb_tbl_gelombang.id
                               LEFT JOIN pmb_edu_map_peserta on pmb_edu_map_peserta.idjadwal=pmb_edu_jadwalusm.id and pmb_edu_map_peserta.idpendaftaran=mahasiswa.ID
                               LEFT JOIN pmb_tbl_kategori_soal ON pmb_tbl_kategori_soal.id = pmb_edu_jadwalusm.kategori_soal_id
                               INNER JOIN pmb_tbl_hasil_test ON pmb_tbl_hasil_test.idmember = mahasiswa.id
                               LEFT JOIN pmb_edu_jenisusm ON pmb_edu_jenisusm.id = pmb_tbl_hasil_test.jenis
                               WHERE (mahasiswa.jenis_mhsw='calon' OR mahasiswa.statuslulus_pmb='1') $whr $wbayar 
                               GROUP BY mahasiswa.ID,pmb_tbl_hasil_test.id 
                               $orderby_calon");

            return count($query);
        } catch (Exception $e) {
            \Log::error('ResetUsmPmbService::countVerifikasiPMB - Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Reset hasil test mahasiswa
     * Delete hasil test dan last save data
     * 
     * @param int $id Mahasiswa ID
     * @return bool Success status
     */
    public function resetHasilTest(int $id): bool
    {
        try {
            return DB::transaction(function () use ($id) {
                $get_hasil_test = DB::table('pmb_tbl_hasil_test')
                    ->where('idmember', $id)
                    ->first();
                
                if (!$get_hasil_test) {
                    return false;
                }
                
                // Delete last save
                DB::table('pmb_tbl_last_save')
                    ->where('idmember', $get_hasil_test->idmember)
                    ->delete();
                
                // Delete hasil test
                DB::table('pmb_tbl_hasil_test')
                    ->where('idmember', $get_hasil_test->idmember)
                    ->where('jenis', $get_hasil_test->jenis)
                    ->delete();
                
                return true;
            });
        } catch (Exception $e) {
            \Log::error('ResetUsmPmbService::resetHasilTest - Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reset multiple mahasiswa tests
     * 
     * @param array $ids Array of mahasiswa IDs
     * @return array ['success' => count, 'failed' => count]
     */
    public function resetMultipleHasilTest(array $ids): array
    {
        $success = 0;
        $failed = 0;
        
        foreach ($ids as $id) {
            if ($this->resetHasilTest((int) $id)) {
                $success++;
            } else {
                $failed++;
            }
        }
        
        return [
            'success' => $success,
            'failed' => $failed,
            'total' => count($ids)
        ];
    }
}
