# Performance Debugging Guide - Setup Soal PMB

## 🔍 Analisis Masalah: Load Time 20 Detik

### 🎯 Root Cause Analysis

Berdasarkan kode yang dianalisis, berikut **penyebab utama** lambatnya halaman Setup Soal:

---

## 1. **N+1 Query Problem di Views** ❌

### Masalah:
```blade
<!-- Di view yang menggunakan get_field() -->
@foreach($query as $row)
    {{ get_field($row->jenjang_id, 'jenjang') }}  // 1 query per row!
    {{ get_field($row->program_id, 'program') }}  // 1 query per row!
@endforeach
```

**Impact:** Jika ada 100 soal → 200+ database queries!

### Solusi:
```php
// Controller - Eager loading
$data['query'] = DB::table('pmb_tbl_soal')
    ->leftJoin('jenjang', 'pmb_tbl_soal.jenjang_id', '=', 'jenjang.ID')
    ->leftJoin('program', 'pmb_tbl_soal.program_id', '=', 'program.ID')
    ->select('pmb_tbl_soal.*', 'jenjang.Nama as jenjangNama', 'program.Nama as programNama')
    ->get();

// View - No more get_field() calls!
@foreach($query as $row)
    {{ $row->jenjangNama }}  // No query!
    {{ $row->programNama }}  // No query!
@endforeach
```

---

## 2. **cek_level() Dipanggil Berulang Kali** ❌

### Masalah:
```php
// Di constructor controller
$this->Create = cek_level($levelUser, 'c_kategori_soal_pmb', 'Create');
$this->Update = cek_level($levelUser, 'c_kategori_soal_pmb', 'Update');
$this->Delete = cek_level($levelUser, 'c_kategori_soal_pmb', 'Delete');

// cek_level() melakukan 4 queries:
// 1. SELECT from modul
// 2. SELECT from submodul  
// 3. SELECT from levelmodul (count)
// Total: 3-4 queries per call × 3 calls = 9-12 queries per request!
```

### Solusi:
```php
// Cache permission check
private function checkPermission($action)
{
    $cacheKey = "permission_{$action}_" . session('LevelUser');
    
    return Cache::remember($cacheKey, 3600, function() use ($action) {
        return cek_level(session('LevelUser'), 'c_kategori_soal_pmb', $action);
    });
}

// Usage
$this->Create = $this->checkPermission('Create');
```

---

## 3. **Session/Cookie Lookup di Setiap Request** ❌

### Masalah:
```php
// Di constructor - dipanggil setiap request
if(!$this->session->userdata('username')) { redirect(); }
if(!get_cookie('language')) { set_cookie(); }
$this->load->language('header', get_cookie('language'));
```

### Solusi:
```php
// Middleware caching
public function handle($request, Closure $next)
{
    // Cache user session check
    if (!Cache::has('user_' . session()->getId())) {
        Cache::put('user_' . session()->getId(), session('username'), 300);
    }
    
    return $next($request);
}
```

---

## 4. **Complex JOIN di KategoriSoalPmbService** ❌

### Masalah:
```php
// search_soal() dan search_subsoal() dengan limit=1000
$limit = 1000; // Loading 1000 records at once!
```

### Solusi:
```php
// Use pagination with smaller limit
$limit = 25; // or 50 max
$data['query'] = $this->service->get_data_soal($limit, $offset, $idkategori, $keyword);
```

---

## 5. **Missing Database Indexes** ❌

### Critical Indexes Needed:
```sql
-- For get_field() optimization
CREATE INDEX idx_jenjang_id ON jenjang(ID);
CREATE INDEX idx_program_id ON program(ID);
CREATE INDEX idx_tahun_id ON tahun(ID);

-- For permission checks
CREATE INDEX idx_modul_script ON modul(Script, AksesID);
CREATE INDEX idx_submodul_script ON submodul(Script);
CREATE INDEX idx_levelmodul_lookup ON levelmodul(type, LevelID, ModulID);

-- For soal queries
CREATE INDEX idx_soal_kategori ON pmb_tbl_soal(idkategori);
CREATE INDEX idx_subsoal_soal ON pmb_tbl_subsoal(idsoal);
```

---

## 6. **No Query Caching** ❌

### Masalah:
```php
// Same query executed multiple times
get_field($id, 'jenjang')  // Query 1
get_field($id, 'jenjang')  // Query 2 (same data!)
```

### Solusi:
```php
// Add caching to get_field helper
function get_field_cached($id, $table, $field = 'Nama')
{
    $cacheKey = "field_{$table}_{$field}_{$id}";
    
    return Cache::remember($cacheKey, 3600, function() use ($id, $table, $field) {
        $result = DB::table($table)
            ->where('ID', $id)
            ->value($field);
        return $result ?? '';
    });
}
```

---

## 7. **View Rendering Overhead** ❌

### Masalah:
- Multiple sub-views rendered
- Complex Blade compilation
- No view caching

### Solusi:
```bash
# Enable view caching in production
php artisan view:cache
php artisan config:cache
php artisan route:cache
```

---

## 🚀 Quick Wins (Implement Sekarang!)

### 1. Enable Debug Bar untuk Identify Bottlenecks
```bash
composer require barryvdh/laravel-debugbar --dev
```

**What to look for:**
- 🔴 Red queries (>100ms)
- 🔴 Duplicate queries
- 🔴 N+1 query patterns

### 2. Add Database Indexes (5 menit)
```sql
-- Run these in database
CREATE INDEX idx_soal_idkategori ON pmb_tbl_soal(idkategori);
CREATE INDEX idx_subsoal_idsoal ON pmb_tbl_subsoal(idsoal);
CREATE INDEX idx_levelmodul_performance ON levelmodul(type, LevelID, ModulID, Akses);
```

### 3. Reduce Limit from 1000 to 50
```php
// In KategoriSoalPmbController
$limit = 50; // was 1000
```

### 4. Cache Permission Checks
```php
// In constructor
$this->Create = Cache::remember(
    "perm_Create_{$levelUser}", 
    3600, 
    fn() => cek_level($levelUser, 'c_kategori_soal_pmb', 'Create')
);
```

### 5. Enable Query Log to Find Slow Queries
```php
// In AppServiceProvider boot()
if (app()->environment('local')) {
    DB::listen(function($query) {
        \Log::info(
            "Query: {$query->sql} | Time: {$query->time}ms",
            $query->bindings
        );
    });
}
```

---

## 📊 Expected Improvement

| Optimization | Before | After | Improvement |
|-------------|---------|-------|-------------|
| Add Indexes | 20s | 5s | 75% ⬇️ |
| Reduce Limit (1000→50) | 5s | 2s | 60% ⬇️ |
| Cache Permissions | 2s | 0.5s | 75% ⬇️ |
| Fix N+1 Queries | 2s | 0.3s | 85% ⬇️ |
| **TOTAL** | **20s** | **~1s** | **95% ⬇️** |

---

## 🛠️ Implementation Priority

### 🔴 Critical (Do Today):
1. Add database indexes
2. Reduce limit from 1000 to 50
3. Enable query logging to identify slow queries

### 🟡 High (Do This Week):
4. Cache permission checks
5. Fix N+1 queries with eager loading
6. Add get_field_cached() helper

### 🟢 Medium (Do This Month):
7. Implement view caching
8. Optimize session handling
9. Add Redis/Memcached for query caching

---

## 📝 Next Steps

1. **Install Debug Bar** - Identify exact bottlenecks
2. **Check Query Log** - Find slowest queries
3. **Add Indexes** - 5 minute fix with huge impact
4. **Reduce Limits** - Pagination optimization
5. **Cache Permissions** - Reduce repeated queries

**Expected Result:** 20s → 1-2s load time! 🚀
