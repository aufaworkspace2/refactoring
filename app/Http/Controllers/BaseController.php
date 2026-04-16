<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Base Controller dengan fungsi-fungsi helper yang sering digunakan
 */
abstract class BaseController extends Controller
{
    /**
     * Return success JSON response
     */
    protected function successResponse($data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status' => 1,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Return error JSON response
     */
    protected function errorResponse(string $message = 'Error', int $statusCode = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'status' => 0,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }

    /**
     * Return validation error response
     */
    protected function validationErrorResponse($errors): JsonResponse
    {
        return response()->json([
            'status' => 0,
            'message' => 'Validation failed',
            'errors' => $errors
        ], 422);
    }

    /**
     * Log error with context
     */
    protected function logError(string $method, string $message, array $context = []): void
    {
        $fullMessage = get_class($this) . "::{$method} - {$message}";
        Log::error($fullMessage, $context);
    }

    /**
     * Parse date from Indonesian format (DD/MM/YYYY) to MySQL (YYYY-MM-DD)
     */
    protected function parseDate(string $date): ?string
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
    protected function formatDate(string $date): string
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
     * Safe array filter - remove null and empty values
     */
    protected function filterArray(array $array): array
    {
        return array_filter($array, fn($value) => $value !== null && $value !== '');
    }

    /**
     * Get pagination data
     */
    protected function getPaginationData(int $total, int $limit, int $offset): array
    {
        $currentPage = floor($offset / $limit) + 1;
        $totalPages = ceil($total / $limit);
        
        return [
            'total' => $total,
            'per_page' => $limit,
            'current_page' => $currentPage,
            'last_page' => $totalPages,
            'from' => $offset + 1,
            'to' => min($offset + $limit, $total)
        ];
    }

    /**
     * Check if user has permission
     */
    protected function hasPermission(string $module, string $action): bool
    {
        $levelUser = session('LevelUser');
        if (!$levelUser) return false;
        
        $result = cek_level($levelUser, $module, $action);
        return $result === 'YA';
    }
}
