<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Service untuk Data Leads PMB
 * Handles calon mahasiswa leads management
 */
class DataLeadsPmbService
{
    private string $table = 'pmb_user_moreinfo';
    private string $pk = 'id';

    /**
     * Get leads data dengan filter kompleks
     * 
     * @param int|null $limit
     * @param int|null $offset
     * @param string|null $keyword
     * @param string|null $StatusMendaftar
     * @param string|null $tgl1
     * @param string|null $tgl2
     * @return array
     */
    public function get_data(
        ?int $limit = null, 
        ?int $offset = null, 
        ?string $keyword = '', 
        ?string $StatusMendaftar = '',
        ?string $tgl1 = '',
        ?string $tgl2 = ''
    ): array {
        try {
            $query = DB::table($this->table)
                ->select($this->table . '.*', DB::raw('mahasiswa.ID as MhswID'))
                ->leftJoin('mahasiswa', function($join) {
                    $join->on(DB::raw("(mahasiswa.jenis_mhsw='mhsw' or mahasiswa.statuslulus_pmb='1') and (" . $this->table . ".telepon=mahasiswa.HP OR mahasiswa.Email=mahasiswa.Email)"), '=', DB::raw('1=1'));
                });

            // Date filters
            if (!empty($tgl1)) {
                $query->whereRaw('date(' . $this->table . '.created_at) >= ?', [$tgl1]);
            }
            if (!empty($tgl2)) {
                $query->whereRaw('date(' . $this->table . '.updated_at) <= ?', [$tgl2]);
            }

            // Status mendaftar filter
            if (!empty($StatusMendaftar)) {
                if ($StatusMendaftar == 1) {
                    $query->whereNotNull('mahasiswa.ID');
                } else if ($StatusMendaftar == 2) {
                    $query->whereNull('mahasiswa.ID');
                }
            }

            // Keyword search
            if (!empty($keyword)) {
                $query->where(function($q) use ($keyword) {
                    $q->where($this->table . '.nama', 'LIKE', "%{$keyword}%")
                      ->orWhere($this->table . '.email', 'LIKE', "%{$keyword}%")
                      ->orWhere($this->table . '.telepon', 'LIKE', "%{$keyword}%");
                });
            }

            $query->orderBy($this->table . '.created_at', 'DESC')
                ->groupBy($this->table . '.ID');

            if ($limit !== null) { $query->limit($limit); }
            if ($offset !== null) { $query->offset($offset); }

            return $query->get()
                ->map(fn($item) => (array) $item)
                ->toArray();
        } catch (Exception $e) {
            \Log::error('DataLeadsPmbService::get_data - Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Count total leads dengan filter
     * 
     * @param string|null $keyword
     * @param string|null $StatusMendaftar
     * @param string|null $tgl1
     * @param string|null $tgl2
     * @return int
     */
    public function count_all(
        ?string $keyword = '', 
        ?string $StatusMendaftar = '',
        ?string $tgl1 = '',
        ?string $tgl2 = ''
    ): int {
        try {
            $query = DB::table($this->table)
                ->select($this->table . '.ID')
                ->leftJoin('mahasiswa', function($join) {
                    $join->on(DB::raw("(mahasiswa.jenis_mhsw='mhsw' or mahasiswa.statuslulus_pmb='1') and (" . $this->table . ".telepon=mahasiswa.HP OR mahasiswa.Email=mahasiswa.Email)"), '=', DB::raw('1=1'));
                });

            // Date filters
            if (!empty($tgl1)) {
                $query->whereRaw('date(' . $this->table . '.created_at) >= ?', [$tgl1]);
            }
            if (!empty($tgl2)) {
                $query->whereRaw('date(' . $this->table . '.updated_at) <= ?', [$tgl2]);
            }

            // Status mendaftar filter
            if (!empty($StatusMendaftar)) {
                if ($StatusMendaftar == 1) {
                    $query->whereNotNull('mahasiswa.ID');
                } else if ($StatusMendaftar == 2) {
                    $query->whereNull('mahasiswa.ID');
                }
            }

            // Keyword search
            if (!empty($keyword)) {
                $query->where(function($q) use ($keyword) {
                    $q->where($this->table . '.nama', 'LIKE', "%{$keyword}%")
                      ->orWhere($this->table . '.email', 'LIKE', "%{$keyword}%")
                      ->orWhere($this->table . '.telepon', 'LIKE', "%{$keyword}%");
                });
            }

            $query->orderBy($this->table . '.created_at', 'DESC')
                ->groupBy($this->table . '.ID');

            return $query->count();
        } catch (Exception $e) {
            \Log::error('DataLeadsPmbService::count_all - Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Delete lead record
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            return (bool) DB::table($this->table)->where($this->pk, $id)->delete();
        } catch (Exception $e) {
            \Log::error('DataLeadsPmbService::delete - Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get nama by ID
     * 
     * @param int $id
     * @return string
     */
    public function getNamaById(int $id): string
    {
        try {
            return DB::table($this->table)->where($this->pk, $id)->value('nama') ?? '';
        } catch (Exception $e) {
            \Log::error('DataLeadsPmbService::getNamaById - Error: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Get leads statistics
     * 
     * @param string $tgl1
     * @param string $tgl2
     * @return array
     */
    public function getStatistics(string $tgl1 = '', string $tgl2 = ''): array
    {
        try {
            $query = DB::table($this->table)
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN mahasiswa.ID IS NOT NULL THEN 1 ELSE 0 END) as sudah_daftar')
                ->selectRaw('SUM(CASE WHEN mahasiswa.ID IS NULL THEN 1 ELSE 0 END) as belum_daftar')
                ->leftJoin('mahasiswa', function($join) {
                    $join->on(DB::raw("(mahasiswa.jenis_mhsw='mhsw' or mahasiswa.statuslulus_pmb='1') and (" . $this->table . ".telepon=mahasiswa.HP OR mahasiswa.Email=mahasiswa.Email)"), '=', DB::raw('1=1'));
                });

            if (!empty($tgl1)) {
                $query->whereRaw('date(' . $this->table . '.created_at) >= ?', [$tgl1]);
            }
            if (!empty($tgl2)) {
                $query->whereRaw('date(' . $this->table . '.updated_at) <= ?', [$tgl2]);
            }

            $stats = $query->first();

            return [
                'total' => (int) ($stats->total ?? 0),
                'sudah_daftar' => (int) ($stats->sudah_daftar ?? 0),
                'belum_daftar' => (int) ($stats->belum_daftar ?? 0),
                'conversion_rate' => $stats->total > 0 
                    ? round(($stats->sudah_daftar / $stats->total) * 100, 2) 
                    : 0
            ];
        } catch (Exception $e) {
            \Log::error('DataLeadsPmbService::getStatistics - Error: ' . $e->getMessage());
            return [
                'total' => 0,
                'sudah_daftar' => 0,
                'belum_daftar' => 0,
                'conversion_rate' => 0
            ];
        }
    }

    /**
     * Export leads to array for Excel/PDF
     * 
     * @param string $keyword
     * @param string $StatusMendaftar
     * @param string $tgl1
     * @param string $tgl2
     * @return array
     */
    public function exportData(
        string $keyword = '', 
        string $StatusMendaftar = '',
        string $tgl1 = '',
        string $tgl2 = ''
    ): array {
        return $this->get_data(null, null, $keyword, $StatusMendaftar, $tgl1, $tgl2);
    }
}
