<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Service untuk Jenis USM PMB
 * Handles jenis ujian entrance management
 */
class JenisUsmPmbService
{
    private string $table = 'pmb_edu_jenisusm';
    private string $pk = 'id';

    /**
     * Get data dengan pagination dan search
     * 
     * @param int|null $limit
     * @param int|null $offset
     * @param string $keyword
     * @return array
     */
    public function get_data(?int $limit = null, ?int $offset = null, ?string $keyword = '',$active = ''): array
    {
        try {
            $query = DB::table($this->table);
            
            if (!empty($keyword)) {
                $query->where(function($q) use ($keyword) {
                    $q->where('nama', 'LIKE', "%{$keyword}%")
                      ->orWhere('kode', 'LIKE', "%{$keyword}%");
                });
            }
            
            if ($limit !== null) { $query->limit($limit); }
            if ($offset !== null) { $query->offset($offset); }
            
            return $query->orderBy('nama', 'ASC')
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray();
        } catch (Exception $e) {
            \Log::error('JenisUsmPmbService::get_data - Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Count total data dengan filter
     * 
     * @param string $keyword
     * @return int
     */
    // Tambahkan parameter $active
    public function count_all(?string $keyword = '', $active = ''): int
    {
       
        $query = DB::table($this->table);
        
        if (!empty($keyword)) {
            $query->where(function($q) use ($keyword) {
                $q->where('nama', 'LIKE', "%{$keyword}%")
                ->orWhere('kode', 'LIKE', "%{$keyword}%");
            });
        }

        // Tambahkan logika untuk $active jika diperlukan
        if ($active !== '') {
            $query->where('is_active', $active);
        }
        
        return $query->count();
        
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
            $result = DB::table($this->table)->where($this->pk, $id)->first();
            return $result ? (array) $result : null;
        } catch (Exception $e) {
            \Log::error('JenisUsmPmbService::get_id - Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Insert new record
     * 
     * @param array $data
     * @return int Insert ID
     */
    public function add(array $data): int
    {
        return DB::table($this->table)->insertGetId($data);
    }

    /**
     * Update existing record
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function edit(int $id, array $data): bool
    {
        return (bool) DB::table($this->table)
            ->where($this->pk, $id)
            ->update($data);
    }

    /**
     * Delete record
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return (bool) DB::table($this->table)->where($this->pk, $id)->delete();
    }

    /**
     * Check duplicate nama dengan exclusion
     * 
     * @param string $nama
     * @param int|null $excludeId
     * @return object|null
     */
    public function checkDuplicateNama(string $nama, ?int $excludeId = null): ?object
    {
        try {
            $query = DB::table($this->table)
                ->select('id', 'nama', 'jenis')
                ->where('nama', $nama);
            
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            
            return $query->first();
        } catch (Exception $e) {
            \Log::error('JenisUsmPmbService::checkDuplicateNama - Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if can delete (no relations)
     * 
     * @param int $id
     * @return bool
     */
    public function canDelete(int $id): bool
    {
        try {
            // Check if used in jadwal usm
            $count = DB::table('pmb_edu_jadwalusm')
                ->whereRaw('FIND_IN_SET(?, jenis_ujin)', [$id])
                ->count();
            
            return $count === 0;
        } catch (Exception $e) {
            \Log::error('JenisUsmPmbService::canDelete - Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all active jenis usm for dropdown
     * 
     * @return array
     */
    public function getAllForDropdown(): array
    {
        try {
            return DB::table($this->table)
                ->select('id', 'nama', 'jenis')
                ->orderBy('nama', 'ASC')
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray();
        } catch (Exception $e) {
            \Log::error('JenisUsmPmbService::getAllForDropdown - Error: ' . $e->getMessage());
            return [];
        }
    }
}
