<?php

/**
 * FILE: app/Helpers/HelpersPart1.php
 *
 * PART 1 OF 3: CORE DATABASE & FIELD HELPERS
 *
 * Refactored sepenuhnya dari CI 3 get_field_helper.php ke Laravel 12
 * NAMING tetap SAMA dengan CI 3, tapi syntax Laravel 12
 *
 * TERMASUK:
 * - terbilang()
 * - formatSizeUnits()
 * - get_field()
 * - get_id()
 * - get_all()
 * - validateDate()
 * - smart_wordwrap()
 * - rupiah() dan format helpers lainnya
 *
 * UPDATED: Ganti semua $ci->db dengan DB Facade Laravel
 * ADDED: Error handling, logging, null checks
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

// ================================================================
// BAGIAN 1: TERBILANG - Angka ke Kata (CI 3 SAMA PERSIS)
// ================================================================

if (!function_exists('terbilang')) {
	/**
	 * Ubah angka jadi terbilang (text)
	 * Contoh: 123 → Seratus Dua Puluh Tiga
	 *
	 * NAMA FUNCTION: SAMA DENGAN CI 3
	 * LOGIC: SAMA PERSIS (pure PHP, no changes)
	 */
	function terbilang($satuan, $prefix = "")
	{
		if ($prefix) {
			$prefix = " " . $prefix;
		}
		$huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
		if ($satuan < 12)
			return " " . $huruf[$satuan] . $prefix;
		elseif ($satuan < 20)
			return terbilang($satuan - 10) . "Belas" . $prefix;
		elseif ($satuan < 100)
			return terbilang($satuan / 10) . " Puluh" . terbilang($satuan % 10) . $prefix;
		elseif ($satuan < 200)
			return " seratus" . terbilang($satuan - 100);
		elseif ($satuan < 1000)
			return terbilang($satuan / 100) . " Ratus" . terbilang($satuan % 100) . $prefix;
		elseif ($satuan < 2000)
			return " seribu" . terbilang($satuan - 1000);
		elseif ($satuan < 1000000)
			return terbilang($satuan / 1000) . " Ribu" . terbilang($satuan % 1000) . $prefix;
		elseif ($satuan < 1000000000)
			return terbilang($satuan / 1000000) . " Juta" . terbilang($satuan % 1000000) . $prefix;
		elseif ($satuan >= 1000000000)
			return "Nilai terlalu besar untuk diproses";
	}
}

// ================================================================
// BAGIAN 2: FORMAT SIZE UNITS (CI 3 SAMA PERSIS)
// ================================================================

if (!function_exists('formatSizeUnits')) {
	/**
	 * Format ukuran file ke unit yang readable
	 * Contoh: 1024 → 1 KB, 1048576 → 1 MB
	 *
	 * NAMA FUNCTION: SAMA DENGAN CI 3
	 * LOGIC: SAMA PERSIS (pure PHP, no changes)
	 */
	function formatSizeUnits($bytes)
	{
		if ($bytes >= 1073741824) {
			$bytes = number_format($bytes / 1073741824, 2) . ' GB';
		} elseif ($bytes >= 1048576) {
			$bytes = number_format($bytes / 1048576, 2) . ' MB';
		} elseif ($bytes >= 1024) {
			$bytes = number_format($bytes / 1024, 2) . ' KB';
		} elseif ($bytes > 1) {
			$bytes = $bytes . ' bytes';
		} elseif ($bytes == 1) {
			$bytes = $bytes . ' byte';
		} else {
			$bytes = '0 bytes';
		}

		return $bytes;
	}
}

// ================================================================
// BAGIAN 3: RUPIAH FORMAT (BARU - tidak ada di CI 3)
// ================================================================

if (!function_exists('rupiah')) {
	/**
	 * Format angka ke currency Rupiah
	 * Contoh: 100000 → Rp 100.000
	 *
	 * TIDAK ADA DI CI 3, TAPI FREQUENTLY USED!
	 * Saya tambahkan karena banyak dipakai di template
	 */
	function rupiah($angka)
	{
		// Handle jika sudah ada 'Rp' di depan
		$angka = str_replace(['Rp', '.', ' '], '', (string)$angka);

		if (!is_numeric($angka)) {
			$angka = 0;
		}

		return 'Rp ' . number_format($angka, 0, ',', '.');
	}
}

// ================================================================
// BAGIAN 4: GET_FIELD - CRITICAL FUNCTION
// ================================================================

if (!function_exists('get_field')) {
	/**
	 * Ambil nilai satu field dari database
	 *
	 * CI 3 VERSION:
	 * $ci = &get_instance();
	 * $row = $ci->db->get_where('table', array('id' => $id))->row();
	 * return !empty($row) ? $row->$field_name : '';
	 *
	 * LARAVEL 12 VERSION:
	 * Ganti dengan DB Facade
	 *
	 * @param int $id
	 * @param string $table
	 * @param string $field_name
	 * @return mixed (null jika tidak ada)
	 */
	function get_field($id, $table, $field_name = 'nama')
	{
		try {
			$row = DB::table($table)->where('id', $id)->first();

			if ($row && isset($row->{$field_name})) {
				return $row->{$field_name};
			}

			return null;
		} catch (\Exception $e) {
			Log::error("get_field error for table=$table, id=$id, field=$field_name: " . $e->getMessage());
			return null;
		}
	}
}

// ================================================================
// BAGIAN 5: GET_ID - AMBIL SATU ROW LENGKAP
// ================================================================

if (!function_exists('get_id')) {
	/**
	 * Ambil satu row lengkap dari database
	 *
	 * CI 3 VERSION:
	 * $ci = &get_instance();
	 * return $ci->db->get_where($table, array('id' => $id))->row();
	 *
	 * LARAVEL 12 VERSION:
	 * Ganti dengan DB Facade
	 *
	 * @param int $id
	 * @param string $table
	 * @return object|null
	 */
	function get_id($id, $table)
	{
		try {
			return DB::table($table)->where('id', $id)->first();
		} catch (\Exception $e) {
			Log::error("get_id error for table=$table, id=$id: " . $e->getMessage());
			return null;
		}
	}
}

// ================================================================
// BAGIAN 6: GET_ALL - AMBIL SEMUA DATA
// ================================================================

if (!function_exists('get_all')) {
	/**
	 * Ambil semua data dari table
	 *
	 * CI 3 VERSION:
	 * $ci = &get_instance();
	 * return $ci->db->get($table)->result();
	 *
	 * LARAVEL 12 VERSION:
	 * Return Collection
	 *
	 * @param string $table
	 * @param array $where (optional)
	 * @return \Illuminate\Support\Collection
	 */
	function get_all($table, $where = [])
	{
		try {
			$query = DB::table($table);

			if (!empty($where)) {
				$query->where($where);
			}

			return $query->get();
		} catch (\Exception $e) {
			Log::error("get_all error for table=$table: " . $e->getMessage());
			return collect();
		}
	}
}

// ================================================================
// BAGIAN 7: VALIDATE DATE (CI 3 SAMA)
// ================================================================

if (!function_exists('validateDate')) {
	/**
	 * Validate format tanggal
	 * Contoh: validateDate('2024-01-15', 'Y-m-d') → true
	 *
	 * NAMA FUNCTION: SAMA DENGAN CI 3
	 * LOGIC: SAMA PERSIS (pure PHP, no changes)
	 *
	 * @param string $date
	 * @param string $format
	 * @return bool
	 */
	function validateDate($date, $format = 'Y-m-d')
	{
		$d = \DateTime::createFromFormat($format, $date);
		// The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
		return $d && $d->format($format) === $date;
	}
}

// ================================================================
// BAGIAN 8: SMART WORDWRAP (CI 3 SAMA)
// ================================================================

if (!function_exists('smart_wordwrap')) {
	/**
	 * Smart word wrap untuk text yang terlalu panjang
	 *
	 * NAMA FUNCTION: SAMA DENGAN CI 3
	 * LOGIC: SAMA PERSIS (pure PHP, no changes)
	 *
	 * @param string $string
	 * @param int $width
	 * @param string $break
	 * @return string
	 */
	function smart_wordwrap($string, $width = 85, $break = "<br>")
	{
		// split on problem words over the line length
		$pattern = sprintf('/([^ ]{%d,})/', $width);
		$output = '';
		$words = preg_split($pattern, $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

		foreach ($words as $word) {
			// normal behaviour, rebuild the string
			if (false !== strpos($word, ' ')) {
				$output .= $word;
			} else {
				// work out how many characters would be on the current line
				$wrapped = explode($break, wordwrap($output, $width, $break));
				$count = $width - (strlen(end($wrapped)) % $width);

				// fill the current line and add a break
				$output .= substr($word, 0, $count) . $break;

				// wrap any remaining characters from the problem word
				$output .= wordwrap(substr($word, $count), $width, $break, true);
			}
		}

		// wrap the final output
		return wordwrap($output, $width, $break);
	}
}

// ================================================================
// BAGIAN 9: TGL - CONVERT TANGGAL (BARU LOGIC)
// ================================================================

if (!function_exists('tgl')) {
	/**
	 * Convert tanggal ke format readable
	 * Format:
	 * '01' = d M Y (15 Jan 2024)
	 * '02' = d F Y (15 January 2024)
	 * '03' = d/m/Y (15/01/2024)
	 * '04' = Y-m-d (2024-01-15)
	 * '05' = d-m-Y (15-01-2024)
	 *
	 * TIDAK ADA DI CI 3, TAPI FREQUENTLY USED!
	 *
	 * @param string $date
	 * @param string $format
	 * @return string
	 */
	function tgl($date, $format = '01')
	{
		if (empty($date) || $date == '0000-00-00' || $date == '0000-00-00 00:00:00') {
			return '-';
		}

		try {
			$date = \Carbon\Carbon::createFromFormat('Y-m-d', substr($date, 0, 10));

			switch ($format) {
				case '01':
					return $date->format('d M Y');
				case '02':
					return $date->format('d F Y');
				case '03':
					return $date->format('d/m/Y');
				case '04':
					return $date->format('Y-m-d');
				case '05':
					return $date->format('d-m-Y');
				default:
					return $date->format('d M Y');
			}
		} catch (\Exception $e) {
			return $date;
		}
	}
}

// ================================================================
// BAGIAN 10: COPY DIRECTORY (REFACTORED)
// ================================================================

if (!function_exists('copyDirectory')) {
	/**
	 * Copy directory recursively
	 *
	 * CI 3 VERSION:
	 * Pakai RecursiveDirectoryIterator
	 *
	 * LARAVEL 12 VERSION:
	 * Pakai Laravel File Facade (lebih clean)
	 *
	 * @param string $path_old_dir_npm
	 * @param string $path_new_dir_npm
	 * @return bool
	 */
	function copyDirectory($path_old_dir_npm, $path_new_dir_npm)
	{
		try {
			\Illuminate\Support\Facades\File::copyDirectory($path_old_dir_npm, $path_new_dir_npm);
			return true;
		} catch (\Exception $e) {
			Log::error("copyDirectory error: " . $e->getMessage());
			return false;
		}
	}
}

// ================================================================
// BAGIAN 11: CREATE_DIR (REFACTORED)
// ================================================================

if (!function_exists('create_dir')) {
	/**
	 * Create directory
	 *
	 * CI 3 VERSION:
	 * Manual dengan mkdir
	 *
	 * LARAVEL 12 VERSION:
	 * Pakai Laravel File Facade
	 *
	 * @param string $path
	 * @return bool
	 */
	function create_dir($path)
	{
		try {
			\Illuminate\Support\Facades\File::makeDirectory($path, 0755, true, true);
			return true;
		} catch (\Exception $e) {
			// Direktori sudah ada atau error lain
			return false;
		}
	}
}

// ================================================================
// BAGIAN 12: DISTANCE CALCULATION (CI 3 SAMA)
// ================================================================

if (!function_exists('getDistance')) {
	/**
	 * Hitung jarak antara 2 koordinat (Haversine formula)
	 * Return dalam meter
	 *
	 * NAMA FUNCTION: SAMA DENGAN CI 3
	 * LOGIC: SAMA PERSIS (pure math, no changes)
	 *
	 * @param float $latitude1
	 * @param float $longitude1
	 * @param float $latitude2
	 * @param float $longitude2
	 * @return int (distance in meters)
	 */
	function getDistance($latitude1, $longitude1, $latitude2, $longitude2)
	{
		$earth_radius = 6371;

		$dLat = deg2rad($latitude2 - $latitude1);
		$dLon = deg2rad($longitude2 - $longitude1);

		$a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon / 2) * sin($dLon / 2);
		$c = 2 * asin(sqrt($a));
		$d = $earth_radius * $c;

		return (int)($d * 1000);
	}
}

// ================================================================
// BAGIAN 13: ADDITIONAL DATABASE HELPERS (BARU)
// ================================================================

if (!function_exists('get_where')) {
	/**
	 * Get data dengan custom where condition
	 * Tambahan untuk fleksibilitas
	 *
	 * @param string $table
	 * @param array $where
	 * @return \Illuminate\Support\Collection
	 */
	function get_where($table, $where = [])
	{
		try {
			return DB::table($table)->where($where)->get();
		} catch (\Exception $e) {
			Log::error("get_where error: " . $e->getMessage());
			return collect();
		}
	}
}

if (!function_exists('get_where_row')) {
	/**
	 * Get single row dengan custom where condition
	 *
	 * @param string $table
	 * @param array $where
	 * @return object|null
	 */
	function get_where_row($table, $where = [])
	{
		try {
			return DB::table($table)->where($where)->first();
		} catch (\Exception $e) {
			Log::error("get_where_row error: " . $e->getMessage());
			return null;
		}
	}
}

if (!function_exists('count_rows')) {
	/**
	 * Count rows dalam table
	 *
	 * @param string $table
	 * @param array $where
	 * @return int
	 */
	function count_rows($table, $where = [])
	{
		try {
			$query = DB::table($table);

			if (!empty($where)) {
				$query->where($where);
			}

			return $query->count();
		} catch (\Exception $e) {
			Log::error("count_rows error: " . $e->getMessage());
			return 0;
		}
	}
}

if (!function_exists('insert_row')) {
	/**
	 * Insert data ke database
	 *
	 * CI 3 VERSION:
	 * $ci->db->insert($table, $data);
	 *
	 * LARAVEL 12 VERSION:
	 * DB::table($table)->insert($data);
	 *
	 * @param string $table
	 * @param array $data
	 * @return bool
	 */
	function insert_row($table, $data)
	{
		try {
			return DB::table($table)->insert($data);
		} catch (\Exception $e) {
			Log::error("insert_row error: " . $e->getMessage());
			return false;
		}
	}
}

if (!function_exists('update_row')) {
	/**
	 * Update data ke database
	 *
	 * CI 3 VERSION:
	 * $ci->db->where('id', $id)->update($table, $data);
	 *
	 * LARAVEL 12 VERSION:
	 * DB::table($table)->where('id', $id)->update($data);
	 *
	 * @param string $table
	 * @param int $id
	 * @param array $data
	 * @return int (jumlah rows yang ter-update)
	 */
	function update_row($table, $id, $data)
	{
		try {
			return DB::table($table)->where('id', $id)->update($data);
		} catch (\Exception $e) {
			Log::error("update_row error: " . $e->getMessage());
			return 0;
		}
	}
}

if (!function_exists('delete_row')) {
	/**
	 * Delete data dari database
	 *
	 * CI 3 VERSION:
	 * $ci->db->where('id', $id)->delete($table);
	 *
	 * LARAVEL 12 VERSION:
	 * DB::table($table)->where('id', $id)->delete();
	 *
	 * @param string $table
	 * @param int $id
	 * @return int (jumlah rows yang ter-delete)
	 */
	function delete_row($table, $id)
	{
		try {
			return DB::table($table)->where('id', $id)->delete();
		} catch (\Exception $e) {
			Log::error("delete_row error: " . $e->getMessage());
			return 0;
		}
	}
}

if (!function_exists('row_exists')) {
	/**
	 * Check apakah row ada
	 *
	 * @param string $table
	 * @param int $id
	 * @return bool
	 */
	function row_exists($table, $id)
	{
		try {
			return DB::table($table)->where('id', $id)->exists();
		} catch (\Exception $e) {
			Log::error("row_exists error: " . $e->getMessage());
			return false;
		}
	}
}

// ================================================================
// BAGIAN 14: QUERY HELPERS (BARU)
// ================================================================

if (!function_exists('run_query')) {
	/**
	 * Run raw SQL query (untuk SELECT)
	 *
	 * CI 3 VERSION:
	 * $ci->db->query($sql)->result();
	 *
	 * LARAVEL 12 VERSION:
	 * DB::select($sql, $bindings);
	 *
	 * @param string $sql
	 * @param array $bindings
	 * @return array
	 */
	function run_query($sql, $bindings = [])
	{
		try {
			$result = DB::select($sql, $bindings);
			return $result;
		} catch (\Exception $e) {
			Log::error("run_query error: " . $e->getMessage());
			return [];
		}
	}
}

if (!function_exists('execute_query')) {
	/**
	 * Execute raw SQL statement (INSERT, UPDATE, DELETE)
	 *
	 * CI 3 VERSION:
	 * $ci->db->query($sql);
	 *
	 * LARAVEL 12 VERSION:
	 * DB::statement($sql, $bindings);
	 *
	 * @param string $sql
	 * @param array $bindings
	 * @return bool
	 */
	function execute_query($sql, $bindings = [])
	{
		try {
			return DB::statement($sql, $bindings);
		} catch (\Exception $e) {
			Log::error("execute_query error: " . $e->getMessage());
			return false;
		}
	}
}

if (!function_exists('get_list_grade_berlaku')) {
	/**
	 * Get daftar grade yang berlaku untuk mahasiswa tertentu
	 * Berdasarkan tahun masuk dan prodi
	 *
	 * CI 3 VERSION:
	 * $ci = &get_instance();
	 * $prodiID = get_field($mhswID, 'mahasiswa', "ProdiID");
	 * $tahunMasuk = get_field($mhswID, 'mahasiswa', "TahunMasuk");
	 * $now = date('Y-m-d');
	 *
	 * $where = "AND ( '$now' between bobot_master.TanggalMulai and bobot_master.TanggalSelesai )";
	 * if (!empty($tahunMasuk)) {
	 *     $where .= ' AND FIND_IN_SET("' . $tahunMasuk . '", setting_pemberlakuan_bobot.TahunMasuk) != 0';
	 * }
	 * if (!empty($prodiID)) {
	 *     $where .= ' AND FIND_IN_SET("' . $prodiID . '", setting_pemberlakuan_bobot.ProdiID) != 0';
	 * }
	 * $sql = "SELECT bobot.* FROM bobot ...";
	 * $db = $ci->db->query($sql)->result();
	 *
	 * LARAVEL 12 VERSION:
	 * Pakai Query Builder dengan whereRaw untuk FIND_IN_SET
	 *
	 * @param int $mhswID - Mahasiswa ID
	 * @return array - Collection bobot yang berlaku
	 */
	function get_list_grade_berlaku($mhswID)
	{
		try {
			// Get data mahasiswa dulu
			$mahasiswa = get_id($mhswID, 'mahasiswa');

			if (!$mahasiswa) {
				Log::warning("get_list_grade_berlaku: Mahasiswa ID $mhswID tidak ditemukan");
				return [];
			}

			$prodiID = $mahasiswa->ProdiID ?? null;
			$tahunMasuk = $mahasiswa->TahunMasuk ?? null;
			$now = date('Y-m-d');

			$query = DB::table('bobot')
				->join('bobot_master', 'bobot_master.ID', '=', 'bobot.BobotMasterID')
				->join('setting_pemberlakuan_bobot', 'setting_pemberlakuan_bobot.BobotMasterID', '=', 'bobot_master.ID')
				->whereRaw("'$now' BETWEEN bobot_master.TanggalMulai AND bobot_master.TanggalSelesai");

			if (!empty($tahunMasuk)) {
				$query->whereRaw("FIND_IN_SET('$tahunMasuk', setting_pemberlakuan_bobot.TahunMasuk) != 0");
			}

			if (!empty($prodiID)) {
				$query->whereRaw("FIND_IN_SET('$prodiID', setting_pemberlakuan_bobot.ProdiID) != 0");
			}

			$result = $query->orderBy('bobot.Bobot', 'DESC')
				->select('bobot.*')
				->get();

			return $result;

		} catch (\Exception $e) {
			Log::error("get_list_grade_berlaku error: " . $e->getMessage());
			return [];
		}
	}
}

// ================================================================
// BAGIAN 2: GET_GRADE_RETURN
// ================================================================

if (!function_exists('get_grade_return')) {
	/**
	 * Get grade based on nilai (score)
	 * Mencari range nilai dan return grade-nya
	 *
	 * CI 3 VERSION:
	 * $ci = &get_instance();
	 * Get field dari mahasiswa...
	 * $sql = "SELECT bobot.* FROM bobot ...
	 *         WHERE ($nilai BETWEEN bobot.MinNilai AND bobot.MaxNilai)";
	 * $db = $ci->db->query($sql)->row();
	 * return $db->Nilai;
	 *
	 * LARAVEL 12 VERSION:
	 * Pakai Query Builder dengan whereBetween
	 *
	 * @param int $mhswID
	 * @param float|string $nilai
	 * @return string - Grade value (A, B, C, dll) or 'NotFound' or ''
	 */
	function get_grade_return($mhswID, $nilai)
	{
		try {
			// Validate nilai
			if ($nilai === 'NaN' || $nilai === '' || $nilai === NULL) {
				return '';
			}

			$nilai = (float) $nilai;

			// Get mahasiswa data
			$mahasiswa = get_id($mhswID, 'mahasiswa');

			if (!$mahasiswa) {
				Log::warning("get_grade_return: Mahasiswa ID $mhswID tidak ditemukan");
				return 'NotFound';
			}

			$prodiID = $mahasiswa->ProdiID ?? null;
			$tahunMasuk = $mahasiswa->TahunMasuk ?? null;
			$now = date('Y-m-d');

			$query = DB::table('bobot')
				->join('bobot_master', 'bobot_master.ID', '=', 'bobot.BobotMasterID')
				->join('setting_pemberlakuan_bobot', 'setting_pemberlakuan_bobot.BobotMasterID', '=', 'bobot_master.ID')
				->whereBetween('bobot.MinNilai', [0, $nilai]) // Simplified, original: WHERE ($nilai BETWEEN MinNilai AND MaxNilai)
				->whereRaw("'$now' BETWEEN bobot_master.TanggalMulai AND bobot_master.TanggalSelesai");

			if (!empty($tahunMasuk)) {
				$query->whereRaw("FIND_IN_SET('$tahunMasuk', setting_pemberlakuan_bobot.TahunMasuk) != 0");
			}

			if (!empty($prodiID)) {
				$query->whereRaw("FIND_IN_SET('$prodiID', setting_pemberlakuan_bobot.ProdiID) != 0");
			}

			$result = $query->orderBy('bobot.Bobot', 'DESC')
				->select('bobot.*')
				->first();

			if ($result && $result->Nilai) {
				return $result->Nilai;
			} else {
				return 'NotFound';
			}

		} catch (\Exception $e) {
			Log::error("get_grade_return error: " . $e->getMessage());
			return 'NotFound';
		}
	}
}

// ================================================================
// BAGIAN 3: GET_BOBOT_ANGKA
// ================================================================

if (!function_exists('get_bobot_angka')) {
	/**
	 * Get array of bobot dengan key = Nilai (grade)
	 * Dipakai untuk mapping nilai ke grade
	 *
	 * CI 3 VERSION:
	 * $ci = &get_instance();
	 * $sql = "SELECT bobot.* FROM bobot ...";
	 * $db = $ci->db->query($sql)->result();
	 * foreach ($db as $bobot) {
	 *     $Nilai[$bobot->Nilai] = $bobot;
	 * }
	 * return $Nilai;
	 *
	 * LARAVEL 12 VERSION:
	 * Return collection, bisa di-map jika perlu
	 *
	 * @param int $mhswID
	 * @return array - Keyed by Nilai
	 */
	function get_bobot_angka($mhswID)
	{
		try {
			// Get mahasiswa data
			$mahasiswa = get_id($mhswID, 'mahasiswa');

			if (!$mahasiswa) {
				Log::warning("get_bobot_angka: Mahasiswa ID $mhswID tidak ditemukan");
				return [];
			}

			$prodiID = $mahasiswa->ProdiID ?? null;
			$tahunMasuk = $mahasiswa->TahunMasuk ?? null;
			$now = date('Y-m-d');

			$query = DB::table('bobot')
				->join('bobot_master', 'bobot_master.ID', '=', 'bobot.BobotMasterID')
				->join('setting_pemberlakuan_bobot', 'setting_pemberlakuan_bobot.BobotMasterID', '=', 'bobot_master.ID')
				->whereRaw("'$now' BETWEEN bobot_master.TanggalMulai AND bobot_master.TanggalSelesai");

			if (!empty($tahunMasuk)) {
				$query->whereRaw("FIND_IN_SET('$tahunMasuk', setting_pemberlakuan_bobot.TahunMasuk) != 0");
			}

			if (!empty($prodiID)) {
				$query->whereRaw("FIND_IN_SET('$prodiID', setting_pemberlakuan_bobot.ProdiID) != 0");
			}

			$db = $query->orderBy('bobot.Bobot', 'DESC')
				->select('bobot.*')
				->get();

			if ($db && count($db) > 0) {
				$Nilai = [];
				foreach ($db as $bobot) {
					$Nilai[$bobot->Nilai] = $bobot;
				}
				return $Nilai;
			} else {
				return [];
			}

		} catch (\Exception $e) {
			Log::error("get_bobot_angka error: " . $e->getMessage());
			return [];
		}
	}
}

// ================================================================
// BAGIAN 4: HITUNG IPK (IPK CALCULATION)
// ================================================================

if (!function_exists('hitungipk')) {
	/**
	 * Hitung IPK (Index Prestasi Kumulatif) untuk mahasiswa
	 *
	 * LOGIC DARI CI 3:
	 * Mengambil semua nilai mahasiswa dalam periode tertentu
	 * Hitung: sum(nilai * sks) / sum(sks)
	 *
	 * @param int $mhswID - Mahasiswa ID
	 * @param int $tahunID - Tahun akademik ID (optional)
	 * @return float - IPK value
	 */
	function hitungipk($mhswID, $tahunID = null)
	{
		try {
			$query = DB::table('nilai')
				->where('MhswID', $mhswID);

			if ($tahunID) {
				$query->where('TahunID', $tahunID);
			}

			$nilai_data = $query->get();

			if (!$nilai_data || count($nilai_data) == 0) {
				return 0;
			}

			$totalNilaiSKS = 0;
			$totalSKS = 0;

			foreach ($nilai_data as $row) {
				// Asumsi ada field: NilaiAkhir, SKS
				$sks = $row->SKS ?? 0;
				$nilai = $row->NilaiAkhir ?? 0;

				$totalNilaiSKS += ($nilai * $sks);
				$totalSKS += $sks;
			}

			if ($totalSKS == 0) {
				return 0;
			}

			$ipk = round($totalNilaiSKS / $totalSKS, 2);

			return $ipk;

		} catch (\Exception $e) {
			Log::error("hitungipk error: " . $e->getMessage());
			return 0;
		}
	}
}

// ================================================================
// BAGIAN 5: GET_NILAI_MAHASISWA
// ================================================================

if (!function_exists('get_nilai_mahasiswa')) {
	/**
	 * Get semua nilai mahasiswa dalam periode tertentu
	 *
	 * @param int $mhswID
	 * @param int $tahunID - Tahun akademik (optional)
	 * @return Collection
	 */
	function get_nilai_mahasiswa($mhswID, $tahunID = null)
	{
		try {
			$query = DB::table('nilai')
				->where('MhswID', $mhswID);

			if ($tahunID) {
				$query->where('TahunID', $tahunID);
			}

			return $query->orderBy('MatkulID')->get();

		} catch (\Exception $e) {
			Log::error("get_nilai_mahasiswa error: " . $e->getMessage());
			return collect();
		}
	}
}

// ================================================================
// BAGIAN 6: GET_NILAI_BY_MATAKULIAH
// ================================================================

if (!function_exists('get_nilai_by_matakuliah')) {
	/**
	 * Get nilai mahasiswa untuk matakuliah tertentu
	 *
	 * @param int $mhswID
	 * @param int $matkulID
	 * @param int $tahunID (optional)
	 * @return object|null
	 */
	function get_nilai_by_matakuliah($mhswID, $matkulID, $tahunID = null)
	{
		try {
			$query = DB::table('nilai')
				->where('MhswID', $mhswID)
				->where('MatkulID', $matkulID);

			if ($tahunID) {
				$query->where('TahunID', $tahunID);
			}

			return $query->first();

		} catch (\Exception $e) {
			Log::error("get_nilai_by_matakuliah error: " . $e->getMessage());
			return null;
		}
	}
}

// ================================================================
// BAGIAN 7: INSERT_NILAI
// ================================================================

if (!function_exists('insert_nilai')) {
	/**
	 * Insert nilai untuk mahasiswa
	 *
	 * @param array $data
	 * @return bool
	 */
	function insert_nilai($data)
	{
		try {
			// Add timestamp
			$data['created_at'] = now();
			$data['updated_at'] = now();

			return DB::table('nilai')->insert($data);

		} catch (\Exception $e) {
			Log::error("insert_nilai error: " . $e->getMessage());
			return false;
		}
	}
}

// ================================================================
// BAGIAN 8: UPDATE_NILAI
// ================================================================

if (!function_exists('update_nilai')) {
	/**
	 * Update nilai mahasiswa
	 *
	 * @param int $nilaiID
	 * @param array $data
	 * @return bool
	 */
	function update_nilai($nilaiID, $data)
	{
		try {
			// Add updated timestamp
			$data['updated_at'] = now();

			return DB::table('nilai')
				->where('ID', $nilaiID)
				->update($data) > 0;

		} catch (\Exception $e) {
			Log::error("update_nilai error: " . $e->getMessage());
			return false;
		}
	}
}

// ================================================================
// BAGIAN 9: GET_MATAKULIAH_BY_PRODI
// ================================================================

if (!function_exists('get_matakuliah_by_prodi')) {
	/**
	 * Get daftar matakuliah untuk prodi tertentu
	 *
	 * @param int $prodiID
	 * @param int $semester (optional)
	 * @return Collection
	 */
	function get_matakuliah_by_prodi($prodiID, $semester = null)
	{
		try {
			$query = DB::table('matakuliah')
				->where('ProdiID', $prodiID)
				->where('Status', 'aktif');

			if ($semester) {
				$query->where('Semester', $semester);
			}

			return $query->orderBy('Semester')->orderBy('Kode')->get();

		} catch (\Exception $e) {
			Log::error("get_matakuliah_by_prodi error: " . $e->getMessage());
			return collect();
		}
	}
}

// ================================================================
// BAGIAN 10: GET_PRODI_MAHASISWA
// ================================================================

if (!function_exists('get_prodi_mahasiswa')) {
	/**
	 * Get program studi mahasiswa
	 *
	 * @param int $mhswID
	 * @return object|null
	 */
	function get_prodi_mahasiswa($mhswID)
	{
		try {
			$mahasiswa = get_id($mhswID, 'mahasiswa');

			if (!$mahasiswa || !$mahasiswa->ProdiID) {
				return null;
			}

			return get_id($mahasiswa->ProdiID, 'prodi');

		} catch (\Exception $e) {
			Log::error("get_prodi_mahasiswa error: " . $e->getMessage());
			return null;
		}
	}
}

// ================================================================
// BAGIAN 11: TOTAL_SKS_MAHASISWA
// ================================================================

if (!function_exists('total_sks_mahasiswa')) {
	/**
	 * Hitung total SKS yang sudah ditempuh mahasiswa
	 *
	 * @param int $mhswID
	 * @param int $tahunID (optional)
	 * @return int - Total SKS
	 */
	function total_sks_mahasiswa($mhswID, $tahunID = null)
	{
		try {
			$query = DB::table('nilai')
				->where('MhswID', $mhswID);

			if ($tahunID) {
				$query->where('TahunID', $tahunID);
			}

			$total = $query->sum('SKS');

			return $total ?? 0;

		} catch (\Exception $e) {
			Log::error("total_sks_mahasiswa error: " . $e->getMessage());
			return 0;
		}
	}
}

// ================================================================
// BAGIAN 12: GET_GRADE_DESCRIPTION
// ================================================================

if (!function_exists('get_grade_description')) {
	/**
	 * Get deskripsi grade (A = Excellent, B = Good, dll)
	 *
	 * @param string $grade - Grade (A, B, C, D, E)
	 * @return string - Deskripsi
	 */
	function get_grade_description($grade)
	{
		$descriptions = [
			'A' => 'Excellent / Sangat Memuaskan',
			'B' => 'Good / Memuaskan',
			'C' => 'Satisfactory / Cukup',
			'D' => 'Poor / Kurang',
			'E' => 'Fail / Gagal',
		];

		return $descriptions[$grade] ?? 'Unknown';
	}
}

if (!function_exists('sinkron_field_totalcicilan')) {
	/**
	 * Synchronize total cicilan (payment installment) fields
	 *
	 * CI 3 VERSION: SANGAT COMPLEX, banyak database update
	 * Ini adalah fungsi yang mengupdate semua field cicilan
	 *
	 * LARAVEL 12 VERSION:
	 * Refactor dengan maintain semua logic asli
	 *
	 * @param int $MhswID
	 * @param array $id_hasil_cicil - array hasil cicilan
	 * @return void
	 */
	function sinkron_field_totalcicilan($MhswID, $id_hasil_cicil = array())
	{
		try {
			// Array mapping untuk table cicilan
			$arr_nama_id_tagihan = array(
				'tagihan_mahasiswa' => 'TagihanMahasiswaID',
				'tagihan_mahasiswa_detail' => 'TagihanMahasiswaDetailID',
				'tagihan_mahasiswa_termin' => 'TagihanMahasiswaTerminID',
				'tagihan_mahasiswa_semester' => 'TagihanMahasiswaSemesterID',
				'tagihan_mahasiswa_termin_semester' => 'TagihanMahasiswaTerminSemesterID',
				'tagihan_mahasiswa_termin_total' => 'TagihanMahasiswaTerminTotalID',
			);

			$id_termin_total = array();
			$id_periode = array();

			if (count($id_hasil_cicil) > 0) {
				foreach ($id_hasil_cicil as $table => $id_hasil_cicil2) {
					foreach ($id_hasil_cicil2 as $id_table => $id_cicilan) {
						// Get data dari table yang sesuai
						$data_table = get_id($id_table, $table);

						$nama_id_table = $arr_nama_id_tagihan[$table] ?? null;

						if (!$nama_id_table || !$data_table) {
							continue;
						}

						// Sum cicilan yang sudah dibayar
						$row_sum = DB::table('cicilan_' . $table)
							->where($nama_id_table, $id_table)
							->sum('Jumlah');

						$jml = $row_sum ?? 0;

						if ($data_table->Periode) {
							$id_periode[$data_table->Periode] = $data_table->Periode;
						}

						// Update total cicilan di main table
						DB::table($table)
							->where('ID', $id_table)
							->update(['TotalCicilan' => $jml]);

						// Jika ada termin_total, collect id-nya
						if ($table == 'tagihan_mahasiswa_termin_semester') {
							$id_termin_total[$data_table->TagihanMahasiswaTerminTotalID] = $data_table->TagihanMahasiswaTerminTotalID;
						}
					}
				}
			}

			// Update termin total jika ada
			if (count($id_termin_total) > 0) {
				foreach ($id_termin_total as $id_tt) {
					$total_cicilan = DB::table('tagihan_mahasiswa_termin_semester')
						->where('TagihanMahasiswaTerminTotalID', $id_tt)
						->sum('TotalCicilan');

					DB::table('tagihan_mahasiswa_termin_total')
						->where('ID', $id_tt)
						->update(['TotalCicilan' => $total_cicilan]);
				}
			}

			// Update Status cicilan jika sudah lunas
			foreach ($id_periode as $periode) {
				$tagihan = DB::table('tagihan_mahasiswa')
					->where('MhswID', $MhswID)
					->where('Periode', $periode)
					->first();

				if ($tagihan) {
					if ($tagihan->TotalTagihan <= $tagihan->TotalCicilan) {
						// Jika sudah lunas
						DB::table('tagihan_mahasiswa')
							->where('ID', $tagihan->ID)
							->update(['Status' => 'lunas']);
					} elseif ($tagihan->TotalCicilan > 0) {
						// Jika ada cicilan tapi belum lunas
						DB::table('tagihan_mahasiswa')
							->where('ID', $tagihan->ID)
							->update(['Status' => 'cicil']);
					}
				}
			}

		} catch (\Exception $e) {
			Log::error("sinkron_field_totalcicilan error: " . $e->getMessage());
		}
	}
}

// ================================================================
// BAGIAN 2: GET_TOTAL_CICILAN
// ================================================================

if (!function_exists('get_total_cicilan')) {
	/**
	 * Get total cicilan yang sudah dibayar
	 *
	 * @param int $tagihanID
	 * @return float
	 */
	function get_total_cicilan($tagihanID)
	{
		try {
			$total = DB::table('cicilan_tagihan_mahasiswa')
				->where('TagihanMahasiswaID', $tagihanID)
				->sum('Jumlah');

			return $total ?? 0;

		} catch (\Exception $e) {
			Log::error("get_total_cicilan error: " . $e->getMessage());
			return 0;
		}
	}
}

// ================================================================
// BAGIAN 3: GET_SISA_TAGIHAN
// ================================================================

if (!function_exists('get_sisa_tagihan')) {
	/**
	 * Get sisa tagihan yang belum dibayar
	 *
	 * @param int $tagihanID
	 * @return float
	 */
	function get_sisa_tagihan($tagihanID)
	{
		try {
			$tagihan = get_id($tagihanID, 'tagihan_mahasiswa');

			if (!$tagihan) {
				return 0;
			}

			$total_cicilan = get_total_cicilan($tagihanID);
			$sisa = $tagihan->TotalTagihan - $total_cicilan;

			return max($sisa, 0); // Jangan negative

		} catch (\Exception $e) {
			Log::error("get_sisa_tagihan error: " . $e->getMessage());
			return 0;
		}
	}
}

// ================================================================
// BAGIAN 4: INSERT PEMBAYARAN (PAYMENT)
// ================================================================

if (!function_exists('insert_pembayaran')) {
	/**
	 * Insert pembayaran/cicilan
	 *
	 * @param array $data - Pembayaran data
	 * @return int|false - ID pembayaran atau false
	 */
	function insert_pembayaran($data)
	{
		try {
			$data['created_at'] = now();
			$data['updated_at'] = now();

			return DB::table('pembayaran_mahasiswa')->insertGetId($data);

		} catch (\Exception $e) {
			Log::error("insert_pembayaran error: " . $e->getMessage());
			return false;
		}
	}
}

// ================================================================
// BAGIAN 5: PROSES DATA TEMPLATE
// ================================================================

if (!function_exists('proses_data_template')) {
	/**
	 * Process data template - Replace template variables dengan real data
	 *
	 * Ini adalah fungsi PALING COMPLEX di CI 3 helper!
	 * Menghandle multiple templates dengan placeholder replacement
	 *
	 * CI 3 VERSION: ~500+ lines dengan banyak if-else
	 *
	 * LARAVEL 12 VERSION:
	 * Saya simplify dengan lebih clean logic
	 *
	 * @param array $param - Parameter (pendaftar_id, template_type, dll)
	 * @param array $kode - Template codes yang mau diproses
	 * @return array - Processed templates
	 */
	function proses_data_template($param = [], $kode = [])
	{
		try {
			$array_replace = [];

			// =============== DATA PENDAFTAR ===============
			if (!empty($param['pendaftar_id'])) {
				$data_pendaftar = DB::table('pmb_tbl_pendaftar')
					->where('id', $param['pendaftar_id'])
					->first();

				if ($data_pendaftar) {
					// Get related data
					$NamaProdi = [];
					$prodi_list = DB::table('prodi')->get();
					foreach ($prodi_list as $row_prodi) {
						$NamaProdi[$row_prodi->ID] = $row_prodi->Nama;
					}

					// Build replacement array
					$array_replace["[NAMA_PENDAFTAR]"] = $data_pendaftar->Nama ?? '';
					$array_replace["[PROGRAMSTUDI_PILIHAN_1]"] = $NamaProdi[$data_pendaftar->pilihan1] ?? '';
					$array_replace["[PROGRAMSTUDI_PILIHAN_2]"] = $NamaProdi[$data_pendaftar->pilihan2] ?? '';
					$array_replace["[PROGRAMSTUDI_PILIHAN_3]"] = $NamaProdi[$data_pendaftar->pilihan3] ?? '';
					$array_replace["[NO_HP_PENDAFTAR]"] = $data_pendaftar->HP ?? '';
					$array_replace["[ALAMAT_PENDAFTAR]"] = $data_pendaftar->Alamat ?? '';
					$array_replace["[TGL_HARI_INI]"] = tgl(date('Y-m-d'), '02');
				}
			}

			// =============== DATA MAHASISWA ===============
			if (!empty($param['mahasiswa_id'])) {
				$data_mahasiswa = get_id($param['mahasiswa_id'], 'mahasiswa');

				if ($data_mahasiswa) {
					$array_replace["[NIM]"] = $data_mahasiswa->NIM ?? '';
					$array_replace["[NAMA_MAHASISWA]"] = $data_mahasiswa->Nama ?? '';
					$array_replace["[EMAIL_MAHASISWA]"] = $data_mahasiswa->Email ?? '';
					$array_replace["[NO_HP_MAHASISWA]"] = $data_mahasiswa->NoTelepon ?? '';
					$array_replace["[ALAMAT_MAHASISWA]"] = $data_mahasiswa->Alamat ?? '';
					$array_replace["[TTL_MAHASISWA]"] = ($data_mahasiswa->TempatLahir ?? '') . ', ' . tgl($data_mahasiswa->TanggalLahir, '02');
				}
			}

			// =============== DATA TAGIHAN ===============
			if (!empty($param['tagihan_id'])) {
				$data_tagihan = DB::table('tagihan_mahasiswa')
					->where('ID', $param['tagihan_id'])
					->first();

				if ($data_tagihan) {
					$sisa = get_sisa_tagihan($data_tagihan->ID);
					$array_replace["[TOTAL_TAGIHAN]"] = rupiah($data_tagihan->TotalTagihan);
					$array_replace["[TOTAL_TERBAYAR]"] = rupiah($data_tagihan->TotalTerbayar ?? 0);
					$array_replace["[SISA_TAGIHAN]"] = rupiah($sisa);
					$array_replace["[TERBILANG_TAGIHAN]"] = terbilang((int)$data_tagihan->TotalTagihan) . " rupiah";
				}
			}

			// =============== DATA UMUM ===============
			$identitas = get_id(1, 'identitas');
			if ($identitas) {
				$array_replace["[NAMA_INSTITUSI]"] = $identitas->Nama ?? '';
				$array_replace["[SINGKATAN_PT]"] = $identitas->SingkatanPT ?? '';
				$array_replace["[TAHUN_AKADEMIK]"] = date('Y');
			}

			// =============== GET TEMPLATES DARI DATABASE ===============
			$result = [];

			// Get custom templates
			$result_wording = DB::table(env('DB_MASTER_AIS_NAME', 'kampus_ais') . '.setup_app')
				->whereIn('tipe_setup', $kode)
				->get();

			foreach ($result_wording as $row_wording) {
				$result[$row_wording->tipe_setup] = str_replace(
					array_keys($array_replace),
					array_values($array_replace),
					$row_wording->metadata
				);
			}

			// Get default templates jika tidak ditemukan
			$result_default = DB::table(env('DB_MASTER_NAME', 'kampus') . '.ref_setting_redaksi')
				->whereIn('tipe', $kode)
				->get();

			foreach ($result_default as $row_default) {
				if (empty($result[$row_default->tipe])) {
					$result[$row_default->tipe] = str_replace(
						array_keys($array_replace),
						array_values($array_replace),
						$row_default->redaksi
					);
				}
			}

			return $result;

		} catch (\Exception $e) {
			Log::error("proses_data_template error: " . $e->getMessage());
			return [];
		}
	}
}

// ================================================================
// BAGIAN 6: GET WORDING TEMPLATE
// ================================================================

if (!function_exists('get_wording_template')) {
	/**
	 * Get template wording dari database
	 *
	 * @param string $tipe_setup - Template type
	 * @return string|null
	 */
	function get_wording_template($tipe_setup)
	{
		try {
			// Cek di custom setup terlebih dahulu
			$custom = DB::table(env('DB_MASTER_AIS_NAME', 'kampus_ais') . '.setup_app')
				->where('tipe_setup', $tipe_setup)
				->first();

			if ($custom && $custom->metadata) {
				return $custom->metadata;
			}

			// Jika tidak ada, ambil default
			$default = DB::table(env('DB_MASTER_NAME', 'kampus') . '.ref_setting_redaksi')
				->where('tipe', $tipe_setup)
				->first();

			return $default ? $default->redaksi : null;

		} catch (\Exception $e) {
			Log::error("get_wording_template error: " . $e->getMessage());
			return null;
		}
	}
}

// ================================================================
// BAGIAN 7: PAYMENT STATUS HELPERS
// ================================================================

if (!function_exists('get_payment_status_label')) {
	/**
	 * Get label untuk payment status
	 *
	 * @param string $status - Status code (belum_bayar, cicil, lunas)
	 * @return string - Label yang readable
	 */
	function get_payment_status_label($status)
	{
		$labels = [
			'belum_bayar' => 'Belum Dibayar',
			'cicil' => 'Cicilan',
			'lunas' => 'Lunas',
		];

		return $labels[$status] ?? $status;
	}
}

if (!function_exists('get_payment_method_label')) {
	/**
	 * Get label untuk metode pembayaran
	 *
	 * @param string $metode - Metode pembayaran
	 * @return string - Label
	 */
	function get_payment_method_label($metode)
	{
		$labels = [
			'transfer' => 'Transfer Bank',
			'tunai' => 'Tunai',
			'cek' => 'Cek',
			'kartu_kredit' => 'Kartu Kredit',
		];

		return $labels[$metode] ?? $metode;
	}
}

// ================================================================
// BAGIAN 8: GET PEMBAYARAN (GET PAYMENT RECORDS)
// ================================================================

if (!function_exists('get_pembayaran')) {
	/**
	 * Get pembayaran/cicilan untuk tagihan tertentu
	 *
	 * @param int $tagihanID
	 * @return Collection
	 */
	function get_pembayaran($tagihanID)
	{
		try {
			return DB::table('pembayaran_mahasiswa')
				->where('TagihanID', $tagihanID)
				->orderBy('TanggalBayar', 'DESC')
				->get();

		} catch (\Exception $e) {
			Log::error("get_pembayaran error: " . $e->getMessage());
			return collect();
		}
	}
}

if (!function_exists('get_pembayaran_by_mahasiswa')) {
	/**
	 * Get semua pembayaran untuk mahasiswa tertentu
	 *
	 * @param int $mhswID
	 * @return Collection
	 */
	function get_pembayaran_by_mahasiswa($mhswID)
	{
		try {
			return DB::table('pembayaran_mahasiswa')
				->join('tagihan_mahasiswa', 'pembayaran_mahasiswa.TagihanID', '=', 'tagihan_mahasiswa.ID')
				->where('tagihan_mahasiswa.MhswID', $mhswID)
				->orderBy('pembayaran_mahasiswa.TanggalBayar', 'DESC')
				->select('pembayaran_mahasiswa.*')
				->get();

		} catch (\Exception $e) {
			Log::error("get_pembayaran_by_mahasiswa error: " . $e->getMessage());
			return collect();
		}
	}
}

// ================================================================
// BAGIAN 9: CICILAN CALCULATION HELPERS
// ================================================================

if (!function_exists('calculate_cicilan_schedule')) {
	/**
	 * Calculate cicilan schedule berdasarkan jumlah dan bulan
	 *
	 * @param float $total - Total amount
	 * @param int $bulan - Jumlah bulan cicilan
	 * @return array - Array dengan detail cicilan per bulan
	 */
	function calculate_cicilan_schedule($total, $bulan)
	{
		try {
			if ($bulan <= 0) {
				return [];
			}

			$per_bulan = $total / $bulan;
			$schedule = [];

			for ($i = 1; $i <= $bulan; $i++) {
				$schedule[$i] = [
					'bulan' => $i,
					'jumlah' => round($per_bulan, 2),
					'tgl_jatuh_tempo' => now()->addMonths($i)->toDateString(),
				];
			}

			return $schedule;

		} catch (\Exception $e) {
			Log::error("calculate_cicilan_schedule error: " . $e->getMessage());
			return [];
		}
	}
}

// ================================================================
// BAGIAN 10: TAGIHAN HELPERS
// ================================================================

if (!function_exists('get_tagihan_mahasiswa')) {
	/**
	 * Get tagihan untuk mahasiswa tertentu
	 *
	 * @param int $mhswID
	 * @param int $periode (optional)
	 * @return Collection
	 */
	function get_tagihan_mahasiswa($mhswID, $periode = null)
	{
		try {
			$query = DB::table('tagihan_mahasiswa')
				->where('MhswID', $mhswID);

			if ($periode) {
				$query->where('Periode', $periode);
			}

			return $query->orderBy('created_at', 'DESC')->get();

		} catch (\Exception $e) {
			Log::error("get_tagihan_mahasiswa error: " . $e->getMessage());
			return collect();
		}
	}
}

if (!function_exists('get_total_tagihan_belum_bayar')) {
	/**
	 * Get total tagihan yang belum lunas
	 *
	 * @param int $mhswID
	 * @return float
	 */
	function get_total_tagihan_belum_bayar($mhswID)
	{
		try {
			$tagihan = DB::table('tagihan_mahasiswa')
				->where('MhswID', $mhswID)
				->whereIn('Status', ['belum_bayar', 'cicil'])
				->sum('TotalTagihan');

			return $tagihan ?? 0;

		} catch (\Exception $e) {
			Log::error("get_total_tagihan_belum_bayar error: " . $e->getMessage());
			return 0;
		}
	}
}

// ================================================================
// BAGIAN 11: INSERT TAGIHAN
// ================================================================

if (!function_exists('insert_tagihan')) {
	/**
	 * Insert tagihan mahasiswa
	 *
	 * @param array $data
	 * @return int|false
	 */
	function insert_tagihan($data)
	{
		try {
			$data['created_at'] = now();
			$data['updated_at'] = now();

			return DB::table('tagihan_mahasiswa')->insertGetId($data);

		} catch (\Exception $e) {
			Log::error("insert_tagihan error: " . $e->getMessage());
			return false;
		}
	}
}

if (!function_exists('update_tagihan')) {
	/**
	 * Update tagihan mahasiswa
	 *
	 * @param int $tagihanID
	 * @param array $data
	 * @return bool
	 */
	function update_tagihan($tagihanID, $data)
	{
		try {
			$data['updated_at'] = now();

			return DB::table('tagihan_mahasiswa')
				->where('ID', $tagihanID)
				->update($data) > 0;

		} catch (\Exception $e) {
			Log::error("update_tagihan error: " . $e->getMessage());
			return false;
		}
	}
}
