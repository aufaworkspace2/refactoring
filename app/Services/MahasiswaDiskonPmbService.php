<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Service for Mahasiswa Diskon PMB
 * Handles student discount management
 */
class MahasiswaDiskonPmbService
{
    /**
     * Get mahasiswa with discount filters
     * 
     * @param array $filters
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function get_data(array $filters = [], ?int $limit = null, ?int $offset = null): array
    {
        try {
            $query = DB::table('mahasiswa')
                ->select('mahasiswa.*', 
                    DB::raw('SUM(draft_tagihan_mahasiswa.TotalTagihan) as JumlahTagihan'),
                    DB::raw('SUM(draft_tagihan_mahasiswa.JumlahDiskon) as JumlahDiskon')
                )
                ->join('pmb_tbl_gelombang_detail', 'pmb_tbl_gelombang_detail.id', '=', 'mahasiswa.gelombang_detail_pmb')
                ->join('draft_tagihan_mahasiswa', function($join) use ($filters) {
                    $join->on('draft_tagihan_mahasiswa.MhswID', '=', 'mahasiswa.ID')
                        ->where('draft_tagihan_mahasiswa.Periode', $filters['TahunID'] ?? '')
                        ->where('draft_tagihan_mahasiswa.StatusPosting', 0);
                });

            // Apply filters
            if (!empty($filters['ProdiID'])) {
                $query->where('mahasiswa.prodilulus_pmb', $filters['ProdiID']);
            }

            if (!empty($filters['ProgramID'])) {
                $query->where('mahasiswa.ProgramID', $filters['ProgramID']);
            }

            if (!empty($filters['KelasID'])) {
                $query->where('mahasiswa.KelasID', $filters['KelasID']);
            }

            if (!empty($filters['TahunMasuk'])) {
                $query->whereIn('mahasiswa.TahunMasuk', $filters['TahunMasuk']);
            }

            if (!empty($filters['gelombang'])) {
                $query->where('pmb_tbl_gelombang_detail.gelombang_id', $filters['gelombang']);
            }

            if (!empty($filters['gelombang_detail'])) {
                $query->where('mahasiswa.gelombang_detail_pmb', $filters['gelombang_detail']);
            }

            if (!empty($filters['jenis_pendaftaran'])) {
                $query->where('mahasiswa.StatusPindahan', $filters['jenis_pendaftaran']);
            }

            if (!empty($filters['pilihan1'])) {
                $query->where('mahasiswa.pilihan1', $filters['pilihan1']);
            }

            if (!empty($filters['pilihan2'])) {
                $query->where('mahasiswa.pilihan2', $filters['pilihan2']);
            }

            if (!empty($filters['ujian_online_pmb'])) {
                if ($filters['ujian_online_pmb'] == '1') {
                    $query->where('mahasiswa.ujian_online_pmb', '1');
                } elseif ($filters['ujian_online_pmb'] == '2') {
                    $query->where('mahasiswa.ujian_online_pmb', '!=', '1');
                }
            }

            if (!empty($filters['keyword'])) {
                $query->where('mahasiswa.gelombang_detail_pmb', $filters['gelombang_detail']);
            }

            if (!empty($filters['keyword'])) {
                $query->where(function($q) use ($filters) {
                    $q->where('mahasiswa.noujian_pmb', 'LIKE', "%{$filters['keyword']}%")
                      ->orWhere('mahasiswa.Nama', 'LIKE', "%{$filters['keyword']}%");
                });
            }

            if (!empty($filters['Tgl1']) && !empty($filters['Tgl2'])) {
                $query->whereBetween(DB::raw('DATE(mahasiswa.TglBuat)'), [$filters['Tgl1'], $filters['Tgl2']]);
            }

            // Only calon or lulus students
            $query->where(function($q) {
                $q->where('mahasiswa.jenis_mhsw', 'calon')
                  ->orWhere('mahasiswa.statuslulus_pmb', '1');
            });

            // Only students with statusdraftregistrasi_pmb = 1
            $query->where('mahasiswa.statusdraftregistrasi_pmb', 1);

            $query->groupBy('mahasiswa.ID');

            if ($limit !== null) {
                $query->limit($limit);
            }
            if ($offset !== null) {
                $query->offset($offset);
            }

            return $query->get()->map(fn($item) => (array) $item)->toArray();
        } catch (Exception $e) {
            \Log::error('MahasiswaDiskonPmbService::get_data - Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Count total mahasiswa with filters
     * 
     * @param array $filters
     * @return int
     */
    public function count_all(array $filters = []): int
    {
        try {
            $query = DB::table('mahasiswa')
                ->join('pmb_tbl_gelombang_detail', 'pmb_tbl_gelombang_detail.id', '=', 'mahasiswa.gelombang_detail_pmb')
                ->join('draft_tagihan_mahasiswa', function($join) use ($filters) {
                    $join->on('draft_tagihan_mahasiswa.MhswID', '=', 'mahasiswa.ID')
                        ->where('draft_tagihan_mahasiswa.Periode', $filters['TahunID'] ?? '')
                        ->where('draft_tagihan_mahasiswa.StatusPosting', 0);
                });

            // Apply filters
            if (!empty($filters['ProdiID'])) {
                $query->where('mahasiswa.prodilulus_pmb', $filters['ProdiID']);
            }

            if (!empty($filters['ProgramID'])) {
                $query->where('mahasiswa.ProgramID', $filters['ProgramID']);
            }

            if (!empty($filters['KelasID'])) {
                $query->where('mahasiswa.KelasID', $filters['KelasID']);
            }

            if (!empty($filters['TahunMasuk'])) {
                $query->whereIn('mahasiswa.TahunMasuk', $filters['TahunMasuk']);
            }

            if (!empty($filters['gelombang'])) {
                $query->where('pmb_tbl_gelombang_detail.gelombang_id', $filters['gelombang']);
            }

            if (!empty($filters['gelombang_detail'])) {
                $query->where('mahasiswa.gelombang_detail_pmb', $filters['gelombang_detail']);
            }

            if (!empty($filters['jenis_pendaftaran'])) {
                $query->where('mahasiswa.StatusPindahan', $filters['jenis_pendaftaran']);
            }

            if (!empty($filters['pilihan1'])) {
                $query->where('mahasiswa.pilihan1', $filters['pilihan1']);
            }

            if (!empty($filters['pilihan2'])) {
                $query->where('mahasiswa.pilihan2', $filters['pilihan2']);
            }

            if (!empty($filters['ujian_online_pmb'])) {
                if ($filters['ujian_online_pmb'] == '1') {
                    $query->where('mahasiswa.ujian_online_pmb', '1');
                } elseif ($filters['ujian_online_pmb'] == '2') {
                    $query->where('mahasiswa.ujian_online_pmb', '!=', '1');
                }
            }

            if (!empty($filters['keyword'])) {
                $query->where('mahasiswa.gelombang_detail_pmb', $filters['gelombang_detail']);
            }

            if (!empty($filters['keyword'])) {
                $query->where(function($q) use ($filters) {
                    $q->where('mahasiswa.noujian_pmb', 'LIKE', "%{$filters['keyword']}%")
                      ->orWhere('mahasiswa.Nama', 'LIKE', "%{$filters['keyword']}%");
                });
            }

            if (!empty($filters['Tgl1']) && !empty($filters['Tgl2'])) {
                $query->whereBetween(DB::raw('DATE(mahasiswa.TglBuat)'), [$filters['Tgl1'], $filters['Tgl2']]);
            }

            // Only calon or lulus students
            $query->where(function($q) {
                $q->where('mahasiswa.jenis_mhsw', 'calon')
                  ->orWhere('mahasiswa.statuslulus_pmb', '1');
            });

            // Only students with statusdraftregistrasi_pmb = 1
            $query->where('mahasiswa.statusdraftregistrasi_pmb', 1);

            return $query->distinct('mahasiswa.ID')->count('mahasiswa.ID');
        } catch (Exception $e) {
            \Log::error('MahasiswaDiskonPmbService::count_all - Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Filter mahasiswa eligible for discount
     * 
     * @param array $filters
     * @return array
     */
    public function filtermhs(array $filters = []): array
    {
        try {
            // Get mahasiswa
            $query = DB::table('mahasiswa')
                ->select('mahasiswa.*', 
                    DB::raw('SUM(draft_tagihan_mahasiswa.TotalTagihan) as JumlahTagihan'),
                    DB::raw('SUM(draft_tagihan_mahasiswa.JumlahDiskon) as JumlahDiskon')
                )
                ->join('pmb_tbl_gelombang_detail', 'pmb_tbl_gelombang_detail.id', '=', 'mahasiswa.gelombang_detail_pmb')
                ->join('draft_tagihan_mahasiswa', function($join) use ($filters) {
                    $join->on('draft_tagihan_mahasiswa.MhswID', '=', 'mahasiswa.ID')
                        ->where('draft_tagihan_mahasiswa.Periode', $filters['TahunID'] ?? '')
                        ->where('draft_tagihan_mahasiswa.StatusPosting', 0);
                });

            if (!empty($filters['ProdiID'])) {
                $query->where('mahasiswa.prodilulus_pmb', $filters['ProdiID']);
            }

            if (!empty($filters['ProgramID'])) {
                $query->where('mahasiswa.ProgramID', $filters['ProgramID']);
            }

            if (!empty($filters['KelasID'])) {
                $query->where('mahasiswa.KelasID', $filters['KelasID']);
            }

            if (!empty($filters['TahunMasuk'])) {
                $query->whereIn('mahasiswa.TahunMasuk', $filters['TahunMasuk']);
            }

            if (!empty($filters['gelombang'])) {
                $query->where('pmb_tbl_gelombang_detail.gelombang_id', $filters['gelombang']);
            }

            if (!empty($filters['gelombang_detail'])) {
                $query->where('mahasiswa.gelombang_detail_pmb', $filters['gelombang_detail']);
            }

            if (!empty($filters['jenis_pendaftaran'])) {
                $query->where('mahasiswa.StatusPindahan', $filters['jenis_pendaftaran']);
            }

            if (!empty($filters['pilihan1'])) {
                $query->where('mahasiswa.pilihan1', $filters['pilihan1']);
            }

            if (!empty($filters['pilihan2'])) {
                $query->where('mahasiswa.pilihan2', $filters['pilihan2']);
            }

            if (!empty($filters['ujian_online_pmb'])) {
                if ($filters['ujian_online_pmb'] == '1') {
                    $query->where('mahasiswa.ujian_online_pmb', '1');
                } elseif ($filters['ujian_online_pmb'] == '2') {
                    $query->where('mahasiswa.ujian_online_pmb', '!=', '1');
                }
            }

            if (!empty($filters['keyword'])) {
                $query->where('mahasiswa.gelombang_detail_pmb', $filters['gelombang_detail']);
            }

            if (!empty($filters['keyword'])) {
                $query->where(function($q) use ($filters) {
                    $q->where('mahasiswa.noujian_pmb', 'LIKE', "%{$filters['keyword']}%")
                      ->orWhere('mahasiswa.Nama', 'LIKE', "%{$filters['keyword']}%");
                });
            }

            if (!empty($filters['Tgl1']) && !empty($filters['Tgl2'])) {
                $query->whereBetween(DB::raw('DATE(mahasiswa.TglBuat)'), [$filters['Tgl1'], $filters['Tgl2']]);
            }

            $query->where(function($q) {
                $q->where('mahasiswa.jenis_mhsw', 'calon')
                  ->orWhere('mahasiswa.statuslulus_pmb', '1');
            });

            $query->where('mahasiswa.statusdraftregistrasi_pmb', 1);
            $query->groupBy('mahasiswa.ID');

            $get_mhs = $query->get();

            $MhswID_arr = $get_mhs->pluck('ID')->toArray();

            // Get jenis biaya
            if (count($MhswID_arr) > 0) {
                $query_jenisbiaya = DB::table('jenisbiaya')
                    ->select('jenisbiaya.*')
                    ->join('draft_tagihan_mahasiswa', 'draft_tagihan_mahasiswa.JenisBiayaID', '=', 'jenisbiaya.ID')
                    ->where('draft_tagihan_mahasiswa.Periode', $filters['TahunID'] ?? '')
                    ->whereIn('draft_tagihan_mahasiswa.MhswID', $MhswID_arr)
                    ->groupBy('jenisbiaya.ID')
                    ->get();
            } else {
                $query_jenisbiaya = collect();
            }

            // Get master diskon
            $diskon = DB::table('master_diskon')
                ->where(function($q) use ($filters) {
                    $q->where('ProdiID', $filters['ProdiID'] ?? '')
                      ->orWhere('ProdiID', '')
                      ->orWhereNull('ProdiID')
                      ->orWhere('ProdiID', 0);
                })
                ->orderByRaw("FIELD(JenisDiskon, 'potong_dari_total', 'potong_dari_sisa')")
                ->get();

            return [
                'get_mhs' => $get_mhs->map(fn($item) => (array) $item)->toArray(),
                'query_jenisbiaya' => $query_jenisbiaya->map(fn($item) => (array) $item)->toArray(),
                'diskon' => $diskon->map(fn($item) => (array) $item)->toArray(),
                'MhswID_arr' => $MhswID_arr
            ];
        } catch (Exception $e) {
            \Log::error('MahasiswaDiskonPmbService::filtermhs - Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Save discount for multiple students
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
                $JenisBiayaIDs = $data['JenisBiayaID'] ?? [];
                $MasterDiskonID = $data['MasterDiskonID'] ?? null;
                $TahunID = $data['TahunID'] ?? null;
                $UserID = $data['UserID'] ?? null;

                foreach ($MhswIDs as $MhswID) {
                    foreach ($JenisBiayaIDs as $JenisBiayaID) {
                        $Nominal = $data['Nominal'][$JenisBiayaID] ?? 0;

                        if ($Nominal > 0) {
                            // Check if discount already exists
                            $existing = DB::table('mahasiswa_diskon')
                                ->where('MhswID', $MhswID)
                                ->where('JenisBiayaID', $JenisBiayaID)
                                ->where('Periode', $TahunID)
                                ->first();

                            if ($existing) {
                                // Update existing
                                $updated = DB::table('mahasiswa_diskon')
                                    ->where('MhswDiskonID', $existing->MhswDiskonID)
                                    ->update([
                                        'MasterDiskonID' => $MasterDiskonID,
                                        'Nominal' => $Nominal,
                                        'UpdatedBy' => $UserID,
                                        'UpdatedAt' => date('Y-m-d H:i:s')
                                    ]);

                                if ($updated) {
                                    $success++;
                                } else {
                                    $failed++;
                                }
                            } else {
                                // Insert new
                                $inserted = DB::table('mahasiswa_diskon')->insert([
                                    'MhswID' => $MhswID,
                                    'JenisBiayaID' => $JenisBiayaID,
                                    'MasterDiskonID' => $MasterDiskonID,
                                    'Nominal' => $Nominal,
                                    'Periode' => $TahunID,
                                    'CreatedBy' => $UserID,
                                    'CreatedAt' => date('Y-m-d H:i:s')
                                ]);

                                if ($inserted) {
                                    $success++;
                                } else {
                                    $failed++;
                                }
                            }
                        }
                    }
                }

                return ['success' => $success, 'failed' => $failed];
            });
        } catch (Exception $e) {
            \Log::error('MahasiswaDiskonPmbService::save - Error: ' . $e->getMessage());
            return ['success' => 0, 'failed' => 0];
        }
    }

    /**
     * Get nominal for discount
     * 
     * @param int $PemberiDiskonID
     * @return array
     */
    public function changenominal(int $PemberiDiskonID): array
    {
        try {
            $row = DB::table('discount')
                ->where('PemberiDiskonID', $PemberiDiskonID)
                ->first();

            return $row ? (array) $row : [];
        } catch (Exception $e) {
            \Log::error('MahasiswaDiskonPmbService::changenominal - Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get single record by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function get_id(int $id): ?array
    {
        try {
            $result = DB::table('mahasiswa_diskon')
                ->where('MhswDiskonID', $id)
                ->first();
            return $result ? (array) $result : null;
        } catch (Exception $e) {
            \Log::error('MahasiswaDiskonPmbService::get_id - Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete discount
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            return (bool) DB::table('mahasiswa_diskon')
                ->where('MhswDiskonID', $id)
                ->delete();
        } catch (Exception $e) {
            \Log::error('MahasiswaDiskonPmbService::delete - Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Activate discount
     * 
     * @param int $id
     * @return bool
     */
    public function aktifkan(int $id): bool
    {
        try {
            return (bool) DB::table('mahasiswa_diskon')
                ->where('MhswDiskonID', $id)
                ->update(['StatusAktif' => 1]);
        } catch (Exception $e) {
            \Log::error('MahasiswaDiskonPmbService::aktifkan - Error: ' . $e->getMessage());
            return false;
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
            return DB::table('master_diskon')
                ->orderBy('Nama', 'ASC')
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray();
        } catch (Exception $e) {
            \Log::error('MahasiswaDiskonPmbService::getAllMasterDiskon - Error: ' . $e->getMessage());
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
            return DB::table('jenisbiaya')
                ->orderBy('Nama', 'ASC')
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray();
        } catch (Exception $e) {
            \Log::error('MahasiswaDiskonPmbService::getAllJenisBiaya - Error: ' . $e->getMessage());
            return [];
        }
    }
}
