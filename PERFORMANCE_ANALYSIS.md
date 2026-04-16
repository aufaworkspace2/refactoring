# 🐢 Analisis Performa Laravel — Edivizta Refactoring

> **Tanggal Analisis:** 30 Maret 2026
> **Environment:** Local Development (`APP_ENV=local`, `APP_DEBUG=true`)
> **Laravel:** v12.x | **PHP:** ^8.2

---

## 📋 Ringkasan Eksekutif

Project ini memiliki **12+ bottleneck performa** yang menyebabkan loading lambat bahkan di local. Di production, dampaknya akan jauh lebih besar karena beban request yang lebih tinggi. Masalah paling kritis adalah **koneksi ke database remote**, **N+1 query pada menu sidebar**, dan **file [helpers.php](file:///home/aufa/Documents/edivizta-refactoring/app/Helpers/helpers.php) 255KB yang di-load setiap request**.

---

## 🔴 KRITIS — Bottleneck Utama

### 1. Database Host Remote, Bukan Localhost

**File:** [.env](file:///home/aufa/Documents/edivizta-refactoring/.env) baris 24

```env
DB_HOST=157.15.165.36   ← Remote server!
DB_PORT=3306
DB_DATABASE=edufectacampus_dev_ais
```

**Masalah:**
Setiap query database harus melakukan network round-trip ke server `157.15.165.36`. Pada jaringan normal, ini menambah **latency 10–100ms per query**. Jika satu halaman membuat 20 query, total overhead bisa **200ms–2 detik** hanya dari latency jaringan, belum termasuk waktu eksekusi query itu sendiri.

**Dampak di Production:** Semakin banyak pengguna → semakin banyak koneksi paralel ke remote DB → koneksi bisa timeout atau antri.

**Solusi:**
```env
# Untuk local development, gunakan MySQL lokal
DB_HOST=127.0.0.1
# Dump database remote ke local:
# mysqldump -h 157.15.165.36 -u devmaster -p edufectacampus_dev_ais > dump.sql
# mysql -u root -p edufectacampus_local < dump.sql
```

---

### 2. N+1 Query Problem di Sidebar Menu

**File:** [app/Services/WelcomeService.php](file:///home/aufa/Documents/edivizta-refactoring/app/Services/WelcomeService.php), baris 126–216

```php
// Query 1: Ambil semua modul
$modulQuery = DB::select("SELECT DISTINCT ... FROM modul ...", [$modulgrup, $userId]);

foreach ($modulQuery as $row) {
    // ⚠️ Query ke-2, ke-3, ke-4... untuk SETIAP modul!
    $submodulQuery = DB::select("SELECT DISTINCT ... FROM submodul ...", [$row->ModulID, $userId]);

    // ⚠️ Query EXTRA untuk modul 430
    if ($row->ModulID == 430) {
        $jmlVerifResult = DB::select("SELECT count(mahasiswa.ID) ...");
    }
}
```

**Masalah:**
Jika ada **15 modul**, maka ada **1 + 15 = 16 queries minimum** hanya untuk render sidebar. Ini adalah pola **N+1 Query** klasik yang berbahaya.

**Solusi:**
```php
// Ambil semua modul + submodul dalam satu query LEFT JOIN
$allData = DB::select("
    SELECT m.*, s.ID as sub_id, s.Nama as sub_nama, s.Script as sub_script
    FROM modul m
    LEFT JOIN submodul s ON s.ModulID = m.ID
    JOIN levelmodul lm ON m.ID = lm.ModulID
    WHERE m.MdlGrpID = ? AND lm.Read = 'YA'
    AND lm.LevelID IN (SELECT LevelID FROM leveluser WHERE UserID = ?)
    ORDER BY m.Urut ASC, s.Urut ASC
", [$modulgrup, $userId]);

// Group di PHP, bukan di DB
$menus = collect($allData)->groupBy('ModulID');
```

---

### 3. [helpers.php](file:///home/aufa/Documents/edivizta-refactoring/app/Helpers/helpers.php) 255KB Di-autoload Setiap Request

**File:** [composer.json](file:///home/aufa/Documents/edivizta-refactoring/composer.json) baris 30–32

```json
"autoload": {
    "files": [
        "app/Helpers/helpers.php"   // ← 255KB, 6.095 baris!
    ]
}
```

**Masalah:**
File ini berisi **6.095 baris kode** dengan ratusan fungsi dari kode CI3 lama. PHP harus mem-**parse, compile, dan load seluruh file ini di setiap request**, bahkan jika request tersebut hanya butuh 2-3 fungsi. Memori dan CPU dihabiskan untuk load fungsi yang tidak terpakai, seperti:
- [get_data_card_pembayaran()](file:///home/aufa/Documents/edivizta-refactoring/app/Helpers/helpers.php#502-527) — query 6 tabel sekaligus
- [sinkron_field_totalcicilan()](file:///home/aufa/Documents/edivizta-refactoring/app/Helpers/helpers.php#189-302) — update banyak tabel
- [proses_pengajuan_otomatis()](file:///home/aufa/Documents/edivizta-refactoring/app/Helpers/helpers.php#437-499) — logika bisnis berat

**Solusi:** Pecah menjadi Service/Repository class yang di-lazy load:
```
app/
├── Services/
│   ├── AuthService.php      (cek_level, dll)
│   ├── FinanceService.php   (sinkron_field_totalcicilan, dll)
│   └── UtilityService.php   (terbilang, format_date, dll)
```

---

## 🟠 TINGGI — Masalah Signifikan

### 4. [cek_level()](file:///home/aufa/Documents/edivizta-refactoring/app/Helpers/helpers.php#2279-2317) Menjalankan 3 Query Setiap Dipanggil

**File:** [app/Helpers/helpers.php](file:///home/aufa/Documents/edivizta-refactoring/app/Helpers/helpers.php), baris 2278–2316

```php
function cek_level($LevelID, $Url, $Akses)
{
    // Query 1: Cek di tabel modul
    $hasilmodul = DB::table('modul')
        ->selectRaw('GROUP_CONCAT(ID) as ID')
        ->where('Script', $Url)->where('AksesID', '1')
        ->first();

    // Query 2: Cek di tabel submodul
    $hasilmodul2 = DB::table('submodul')
        ->selectRaw('GROUP_CONCAT(ID) as ID')
        ->where('Script', $Url)
        ->first();

    // Query 3: Cek levelmodul
    $hasil = DB::table('levelmodul')
        ->where('type', $type)->whereIn('LevelID', $levelIds)
        ->where($Akses, 'YA')->whereIn('ModulID', $modulIds)
        ->count();
}
```

**Masalah:**
Dipanggil di `WelcomeService::cek_logout()` dan `BaseController::hasPermission()` **tanpa caching sama sekali** → 3 queries per pengecekan akses.

**Solusi:**
```php
function cek_level($LevelID, $Url, $Akses)
{
    $cacheKey = "cek_level_{$LevelID}_{$Url}_{$Akses}";
    return Cache::remember($cacheKey, 300, function() use ($LevelID, $Url, $Akses) {
        // ... logika DB query yang ada
    });
}
```

---

### 5. Cache Tidak Dioptimalkan — Redis Sudah Ada Tapi Tidak Dipakai

**File:** [.env](file:///home/aufa/Documents/edivizta-refactoring/.env) baris 40–48

```env
CACHE_STORE=file          # ← Pakai file system!

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1      # ← Redis sudah terkonfigurasi!
REDIS_PORT=6379
```

**Masalah:**
- `CACHE_STORE=file` → Setiap cache read/write membaca/menulis file disk
- Redis sudah *dikonfigurasi* tapi tidak digunakan sama sekali
- Menu sidebar, data identitas kampus, data referensi tidak di-cache → di-query setiap request

**Solusi:**
```env
CACHE_STORE=redis
SESSION_DRIVER=redis
```

```php
// Cache menu sidebar
public function getMenus($userId)
{
    return Cache::remember("menus_user_{$userId}", 1800, function() use ($userId) {
        return DB::select($query, [$userId]);
    });
}
```

---

### 6. Session Driver Berbasis File

**File:** [.env](file:///home/aufa/Documents/edivizta-refactoring/.env) baris 30

```env
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

**Masalah:**
Hampir semua halaman menggunakan [CheckUserSession](file:///home/aufa/Documents/edivizta-refactoring/app/Http/Middleware/CheckUserSession.php#9-32) middleware → setiap request **membaca dan menulis file session** di disk. Di production dengan ratusan concurrent users, ini bisa jadi I/O bottleneck.

**Solusi:**
```env
SESSION_DRIVER=redis
```

---

### 7. Queue Connection Berbasis Database

**File:** [.env](file:///home/aufa/Documents/edivizta-refactoring/.env) baris 38

```env
QUEUE_CONNECTION=database
```

**Masalah:**
Queue worker harus **polling tabel `jobs` secara berkala** dengan SELECT query. Setiap job yang di-dispatch → INSERT ke database. Ini menambah beban unnecessary ke database yang sudah terhubung via remote.

**Solusi:**
```env
QUEUE_CONNECTION=redis
```

---

## 🟡 SEDANG — Perlu Perhatian

### 8. Route Tidak Di-cache + Duplikasi Route

**File:** [routes/web.php](file:///home/aufa/Documents/edivizta-refactoring/routes/web.php) — 470 baris, 30+ route group

**Masalah A:** Laravel harus mem-parse seluruh file route setiap request di development.

**Masalah B:** Ada duplikasi route yang menggunakan controller yang sama:
```php
// Line 319: calon_mahasiswa → CalonMahasiswaController
Route::middleware(['CheckUserSession'])->prefix('calon_mahasiswa')
    ->group(fn() => [Route::get('/{offset?}/{bayar?}', [CalonMahasiswaController::class, 'index'])]);

// Line 391: calon_mahasiswa_baru → CalonMahasiswaController (SAMA!)
Route::middleware(['CheckUserSession'])->prefix('calon_mahasiswa_baru')
    ->group(fn() => [Route::get('/{offset?}/{bayar?}', [CalonMahasiswaController::class, 'index'])]);
```

**Solusi:**
```bash
# Production
php artisan route:cache
php artisan config:cache
php artisan view:cache
```

---

### 9. Hardcode Session di Middleware — Auth Tidak Berfungsi

**File:** [app/Http/Middleware/CheckUserSession.php](file:///home/aufa/Documents/edivizta-refactoring/app/Http/Middleware/CheckUserSession.php)

```php
public function handle(Request $request, Closure $next): Response
{
    // ⚠️ HARDCODE untuk testing — masih aktif!
    session([
        'UserID' => 1,
        'username' => 'admin',
        'LevelUser' => '20'
    ]);

    // Ini TIDAK PERNAH redirect karena session selalu di-set di atas!
    if (!session()->has('username')) {
        return redirect()->route('welcome')...;
    }
```

**Masalah:**
- Setiap request menulis 3 nilai ke session (overhead I/O)
- Logika pengecekan session di bawahnya tidak pernah bekerja
- Semua user dianggap "admin" — security risk

---

### 10. `LOG_LEVEL=debug` — Log Berlebihan

**File:** [.env](file:///home/aufa/Documents/edivizta-refactoring/.env) baris 21

```env
LOG_LEVEL=debug
```

**Masalah:**
Level debug menyebabkan Laravel mencatat SEMUA aktivitas termasuk setiap SQL query ke file log. Menulis log ke disk menambah I/O overhead, dan file log bisa membengkak cepat hingga ratusan MB.

**Solusi:**
```env
LOG_LEVEL=warning    # Untuk production
LOG_LEVEL=info       # Untuk staging/testing
```

---

### 11. Model CI3 Lama Masih Ada

**File:** [app/Models/M_mahasiswa.php](file:///home/aufa/Documents/edivizta-refactoring/app/Models/M_mahasiswa.php), [app/Models/M_gelombang_pmb.php](file:///home/aufa/Documents/edivizta-refactoring/app/Models/M_gelombang_pmb.php)

```php
// M_mahasiswa.php — INI KODE CODEIGNITER 3, BUKAN LARAVEL!
class m_mahasiswa extends CI_Model  // ← CI3 class!
{
    function get_data_list($limit, $offset, ...) {
        $this->db->where_in(...);  // ← CI3 syntax!
    }
}
```

**Masalah:**
File CI3 ini tidak bisa berjalan di Laravel. Keberadaannya membingungkan developer dan bisa menyebabkan konflik naming. Total ada **2 file CI3** di folder `Models` yang seharusnya sudah dikonversi.

---

### 12. N+1 di [get_penawaran()](file:///home/aufa/Documents/edivizta-refactoring/app/Services/GelombangPmbService.php#455-504) dalam Loop

**File:** [app/Services/GelombangPmbService.php](file:///home/aufa/Documents/edivizta-refactoring/app/Services/GelombangPmbService.php), baris 472–480

```php
foreach ($jenis_pendaftaran as $jp) {
    // ⚠️ 1 query per item dalam loop!
    $jp_field = DB::table('jenis_pendaftaran')->where('ID', $jp)->first();
    if ($jp_field && $jp_field->Kode) {
        $kode_jenis_pendaftaran[] = "'" . $jp_field->Kode . "'";
    }
}
```

**Solusi:**
```php
// 1 query, ambil semua sekaligus
$kode_jenis_pendaftaran = DB::table('jenis_pendaftaran')
    ->whereIn('ID', $jenis_pendaftaran)
    ->pluck('Kode')
    ->map(fn($k) => "'$k'")
    ->toArray();
```

---

## 📊 Summary Prioritas Perbaikan

| # | Masalah | Severity | Effort | Estimasi Dampak |
|---|---------|----------|--------|-----------------|
| 1 | DB Host Remote | 🔴 Kritis | Rendah | -500ms–2s/request |
| 2 | N+1 Query Sidebar Menu | 🔴 Kritis | Sedang | -16+ queries/page |
| 3 | helpers.php 255KB autoload | 🔴 Kritis | Tinggi | -Memory & CPU |
| 4 | cek_level() 3 query tanpa cache | 🟠 Tinggi | Rendah | -3 queries/check |
| 5 | CACHE_STORE=file, Redis tidak dipakai | 🟠 Tinggi | Rendah | -I/O overhead |
| 6 | SESSION_DRIVER=file | 🟠 Tinggi | Rendah | -I/O per request |
| 7 | QUEUE_CONNECTION=database | 🟠 Tinggi | Rendah | -DB polling |
| 8 | Route tidak di-cache + duplikasi | 🟡 Sedang | Rendah | -Parse time |
| 9 | Hardcode session di middleware | 🟡 Sedang | Rendah | -Security + I/O |
| 10 | LOG_LEVEL=debug | 🟡 Sedang | Rendah | -Disk I/O |
| 11 | Model CI3 di folder Models | 🟡 Sedang | Tinggi | -Dead code |
| 12 | N+1 di get_penawaran() | 🟡 Sedang | Rendah | -N queries/call |

---

## 🚀 Quick Wins (Bisa Langsung Dikerjakan Hari Ini)

```bash
# 1. Edit .env — perubahan paling impactful
DB_HOST=127.0.0.1           # ← pindah ke DB lokal!
CACHE_STORE=redis            # ← aktifkan Redis cache
SESSION_DRIVER=redis         # ← session ke Redis
QUEUE_CONNECTION=redis       # ← queue ke Redis
LOG_LEVEL=info               # ← kurangi log noise

# 2. Cache semua untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 3. Bersihkan cache development
php artisan optimize:clear
```

---

## 🏗️ Rekomendasi Jangka Menengah

1. **Refactor sidebar menu** → Single JOIN query + groupBy di PHP, bukan N+1
2. **Cache menu per user** → `Cache::remember("menus_{$userId}", 1800, ...)`
3. **Cache [cek_level()](file:///home/aufa/Documents/edivizta-refactoring/app/Helpers/helpers.php#2279-2317)** → `Cache::remember("level_{$levelId}_{$url}_{$akses}", 300, ...)`
4. **Pecah [helpers.php](file:///home/aufa/Documents/edivizta-refactoring/app/Helpers/helpers.php)** → Buat Service class yang terpisah per domain bisnis
5. **Tambah database indexes** pada kolom yang sering di-query:
   - `modul.Script`
   - `submodul.Script`
   - `levelmodul.LevelID`
   - `leveluser.UserID`
6. **Hapus model CI3 lama** dari folder `app/Models/`
7. **Perbaiki middleware** → Hapus hardcode session, implementasi auth yang proper

---

## 🏭 Checklist Production Deployment

- [ ] `APP_DEBUG=false`
- [ ] `LOG_LEVEL=warning`
- [ ] `CACHE_STORE=redis`
- [ ] `SESSION_DRIVER=redis`
- [ ] `QUEUE_CONNECTION=redis`
- [ ] DB host lokal/VPS yang dekat dengan server app
- [ ] `php artisan optimize` (cache route, config, view, event)
- [ ] PHP OPcache aktif (percepat 30–50%)
- [ ] Database indexes pada kolom kritis
- [ ] CDN untuk aset statis (CSS, JS, gambar)

---

> **Estimasi Improvement:** Dengan menyelesaikan TOP 3 masalah saja (remote DB → lokal, N+1 query → single query, Redis cache aktif), response time bisa turun dari **3–10 detik** menjadi **200–800ms** di local environment.
