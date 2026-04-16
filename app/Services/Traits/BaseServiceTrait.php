<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Base Service dengan fungsi-fungsi umum yang sering digunakan
 */
trait BaseServiceTrait
{
    /**
     * Execute callback with transaction
     */
    protected function withTransaction(callable $callback)
    {
        return DB::transaction($callback);
    }

    /**
     * Get field from table with caching
     */
    protected function getFieldCached($table, $field, $id, $ttl = 3600)
    {
        if (!$id) return '';
        
        $cacheKey = "field_{$table}_{$field}_{$id}";
        
        // Try to get from cache first (if Redis/Memcached available)
        // For now, direct query
        $result = DB::table($table)->where('ID', $id)->value($field);
        return $result ?? '';
    }

    /**
     * Bulk insert with chunking
     */
    protected function bulkInsert($table, array $data, $chunkSize = 100)
    {
        $chunks = array_chunk($data, $chunkSize);
        foreach ($chunks as $chunk) {
            DB::table($table)->insert($chunk);
        }
    }

    /**
     * Update with null check
     */
    protected function updateIfExists($table, $id, array $data)
    {
        if (empty($data)) return false;
        
        return DB::table($table)
            ->where('id', $id)
            ->update($data);
    }

    /**
     * Delete with soft check (check if has relations)
     */
    protected function canDelete($table, $id, array $relations = [])
    {
        foreach ($relations as $relationTable => $foreignColumn) {
            $count = DB::table($relationTable)
                ->where($foreignColumn, $id)
                ->count();
            
            if ($count > 0) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Format date from Indonesian format (DD/MM/YYYY) to MySQL (YYYY-MM-DD)
     */
    protected function formatDateToMySQL($date)
    {
        if (!$date) return null;
        
        if (strpos($date, '/') !== false) {
            $parts = explode('/', $date);
            if (count($parts) === 3) {
                return "{$parts[2]}-{$parts[1]}-{$parts[0]}";
            }
        }
        
        return $date;
    }

    /**
     * Format date from MySQL to Indonesian format
     */
    protected function formatDateToIndonesian($date)
    {
        if (!$date) return '';
        
        if (strpos($date, '-') !== false) {
            $parts = explode('-', $date);
            if (count($parts) === 3) {
                return "{$parts[2]}/{$parts[1]}/{$parts[0]}";
            }
        }
        
        return $date;
    }

    /**
     * Implode array with null check
     */
    protected function safeImplode(array $array, $glue = ',')
    {
        return implode($glue, array_filter($array, fn($v) => $v !== null && $v !== ''));
    }

    /**
     * Explode string with null check
     */
    protected function safeExplode($string, $glue = ',')
    {
        if (!$string) return [];
        return array_filter(explode($glue, $string));
    }

    /**
     * Generate alias/slug from string
     */
    protected function generateAlias($string)
    {
        $alias = strtolower($string);
        $alias = preg_replace('/[^a-z0-9-]/', '-', $alias);
        $alias = preg_replace('/-+/', '-', $alias);
        return trim($alias, '-');
    }

    /**
     * Check if record exists with exclusion
     */
    protected function existsWithExclusion($table, $column, $value, $excludeId = null)
    {
        $query = DB::table($table)->where($column, $value);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Get count with relation check
     */
    protected function countWithRelation($table, $relationTable, $foreignColumn, $id)
    {
        return DB::table($table)
            ->leftJoin($relationTable, "{$table}.id", '=', "{$relationTable}.{$foreignColumn}")
            ->where("{$table}.id", $id)
            ->count("{$relationTable}.id");
    }
}
