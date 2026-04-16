<?php

/**
 * FILE: app/Http/Controllers/DashboardController.php
 *
 * Basic controller untuk test refactoring
 * Menggunakan semua helpers yang sudah di-refactor
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Constructor - check auth
     */
    public function __construct()
    {
        // Skip auth untuk sekarang, nanti bisa di-enable
        // $this->middleware('auth');
    }

    /**
     * Dashboard - Main page
     * Test semua helpers di sini
     */
    public function index()
    {
        try {
            // ============================================
            // TEST 1: Database Helpers
            // ============================================

            // Test get_id() - Get identitas institusi
            $identitas = get_id(1, 'identitas');

            // Test get_all() - Get semua users
            $users = get_all('users');
            $total_users = count($users);

            // Test count_rows() - Count mahasiswa
            $total_mahasiswa = count_rows('mahasiswa');

            // Test get_where() - Get data dengan condition
            $mahasiswa_aktif = get_where('mahasiswa', ['Status' => 'aktif']);
            $total_aktif = count($mahasiswa_aktif);

            // ============================================
            // TEST 2: Format Helpers
            // ============================================

            // Test rupiah()
            $sample_amount = 1500000;
            $formatted_rupiah = rupiah($sample_amount);

            // Test terbilang()
            $sample_number = 123;
            $terbilang_text = terbilang($sample_number);

            // Test formatSizeUnits()
            $sample_size = 5242880; // 5MB
            $formatted_size = formatSizeUnits($sample_size);

            // ============================================
            // TEST 3: Date Helpers
            // ============================================

            // Test tgl() dengan berbagai format
            $sample_date = '2024-01-15';
            $tgl_format_01 = tgl($sample_date, '01'); // 15 Jan 2024
            $tgl_format_02 = tgl($sample_date, '02'); // 15 January 2024
            $tgl_format_03 = tgl($sample_date, '03'); // 15/01/2024

            // ============================================
            // TEST 4: Academic Helpers
            // ============================================

            // Test get_prodi_mahasiswa() - jika ada mahasiswa
            $sample_mhsw_id = 1;
            $mahasiswa_sample = get_id($sample_mhsw_id, 'mahasiswa');
            $prodi = null;
            $ipk = 0;
            $total_sks = 0;

            if ($mahasiswa_sample) {
                $prodi = get_prodi_mahasiswa($sample_mhsw_id);
                $ipk = hitungipk($sample_mhsw_id); // Test IPK calculation
                $total_sks = total_sks_mahasiswa($sample_mhsw_id); // Test total SKS
            }

            // ============================================
            // TEST 5: Payment Helpers
            // ============================================

            // Test get_total_tagihan_belum_bayar() - jika ada mahasiswa
            $total_tagihan_belum_bayar = 0;
            if ($mahasiswa_sample) {
                $total_tagihan_belum_bayar = get_total_tagihan_belum_bayar($sample_mhsw_id);
            }

            // ============================================
            // Prepare data untuk view
            // ============================================

            $data = [
                // Database Info
                'identitas' => $identitas,
                'total_users' => $total_users,
                'total_mahasiswa' => $total_mahasiswa,
                'total_aktif' => $total_aktif,

                // Format Tests
                'sample_amount' => $sample_amount,
                'formatted_rupiah' => $formatted_rupiah,
                'sample_number' => $sample_number,
                'terbilang_text' => $terbilang_text,
                'sample_size' => $sample_size,
                'formatted_size' => $formatted_size,

                // Date Tests
                'sample_date' => $sample_date,
                'tgl_format_01' => $tgl_format_01,
                'tgl_format_02' => $tgl_format_02,
                'tgl_format_03' => $tgl_format_03,

                // Academic Tests
                'mahasiswa_sample' => $mahasiswa_sample,
                'prodi' => $prodi,
                'ipk' => $ipk,
                'total_sks' => $total_sks,

                // Payment Tests
                'total_tagihan_belum_bayar' => $total_tagihan_belum_bayar,
                'formatted_tagihan' => rupiah($total_tagihan_belum_bayar),
            ];

            return view('dashboard', $data);

        } catch (\Exception $e) {
            return view('error', [
                'message' => 'Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Test Helpers - Dedicated page untuk test semua helpers
     */
    public function testHelpers()
    {
        try {
            $tests = [
                // Core Database Helpers
                'get_field' => [
                    'test' => function() {
                        return get_field(1, 'identitas', 'Nama');
                    },
                    'expected' => 'String (Nama institusi)',
                ],
                'get_id' => [
                    'test' => function() {
                        return get_id(1, 'identitas');
                    },
                    'expected' => 'Object dengan semua fields',
                ],
                'get_all' => [
                    'test' => function() {
                        return get_all('users')->count();
                    },
                    'expected' => 'Integer (count users)',
                ],
                'count_rows' => [
                    'test' => function() {
                        return count_rows('mahasiswa');
                    },
                    'expected' => 'Integer (count mahasiswa)',
                ],

                // Format Helpers
                'rupiah' => [
                    'test' => function() {
                        return rupiah(1500000);
                    },
                    'expected' => 'Rp 1.500.000',
                ],
                'terbilang' => [
                    'test' => function() {
                        return terbilang(123);
                    },
                    'expected' => 'Text number (Seratus Dua Puluh Tiga)',
                ],
                'formatSizeUnits' => [
                    'test' => function() {
                        return formatSizeUnits(1048576);
                    },
                    'expected' => '1 MB',
                ],

                // Date Helpers
                'tgl' => [
                    'test' => function() {
                        return tgl('2024-01-15', '01');
                    },
                    'expected' => '15 Jan 2024',
                ],

                // Academic Helpers
                'hitungipk' => [
                    'test' => function() {
                        return hitungipk(1);
                    },
                    'expected' => 'Float (IPK value)',
                ],
                'total_sks_mahasiswa' => [
                    'test' => function() {
                        return total_sks_mahasiswa(1);
                    },
                    'expected' => 'Integer (total SKS)',
                ],

                // Payment Helpers
                'get_sisa_tagihan' => [
                    'test' => function() {
                        return get_sisa_tagihan(1);
                    },
                    'expected' => 'Float (sisa tagihan)',
                ],
            ];

            $results = [];
            foreach ($tests as $name => $test_data) {
                try {
                    $result = $test_data['test']();
                    $results[$name] = [
                        'status' => 'OK ✅',
                        'result' => is_object($result) ? json_encode($result) : $result,
                        'expected' => $test_data['expected'],
                    ];
                } catch (\Exception $e) {
                    $results[$name] = [
                        'status' => 'ERROR ❌',
                        'result' => $e->getMessage(),
                        'expected' => $test_data['expected'],
                    ];
                }
            }

            return view('test-helpers', ['results' => $results]);

        } catch (\Exception $e) {
            return view('error', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Test Database Connection
     */
    public function testDatabase()
    {
        try {
            $database_info = [
                'connection' => config('database.default'),
                'host' => config('database.connections.mysql.host'),
                'database' => config('database.connections.mysql.database'),
            ];

            // Try connect
            $pdo = DB::connection()->getPdo();
            $database_info['connection_status'] = 'Connected ✅';

            // Get tables
            $tables = DB::select("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?", [
                config('database.connections.mysql.database')
            ]);

            $database_info['tables'] = [];
            foreach ($tables as $table) {
                $count = DB::table($table->TABLE_NAME)->count();
                $database_info['tables'][$table->TABLE_NAME] = $count;
            }

            return view('test-database', ['info' => $database_info]);

        } catch (\Exception $e) {
            return view('error', [
                'message' => 'Database Connection Error: ' . $e->getMessage(),
            ]);
        }
    }
}
