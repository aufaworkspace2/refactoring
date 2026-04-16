# Laravel Refactoring Guide - General Template

## 📋 Panduan Umum Refactoring CodeIgniter ke Laravel

Dokumentasi ini adalah **template umum** untuk refactoring semua menu dari CodeIgniter ke Laravel. Gunakan sebagai referensi standar untuk memastikan konsistensi dan kualitas code.

**Versi:** 1.0  
**Last Updated:** April 2, 2026  
**Status:** Living Document

---

## 🎯 Daftar Isi

1. [Persiapan Refactoring](#persiapan-refactoring)
2. [Struktur File & Konvensi](#struktur-file--konvensi)
3. [Common Issues & Solutions](#common-issues--solutions)
4. [Code Patterns](#code-patterns)
5. [Checklist Refactoring](#checklist-refactoring)
6. [Testing Guidelines](#testing-guidelines)
7. [Troubleshooting](#troubleshooting)
8. [Best Practices](#best-practices)

---

## 📦 Persiapan Refactoring

### 1. Identifikasi Menu Referensi

Pilih menu yang sudah berhasil direfactor sebagai referensi:

```bash
# Menu referensi yang sudah stabil:
- kegiatan_skpi
- kategori_kegiatan_skpi
- nilai_kegiatan_skpi
```

### 2. Analisis Menu yang Akan Direfactor

```bash
# 1. Cek semua file di folder menu
ls -la resources/views/{nama_menu}/

# 2. Identifikasi tipe file:
# v_*.blade.php  - View utama (list data)
# s_*.blade.php  - Search results
# f_*.blade.php  - Form add/edit
# e_*.blade.php  - Edit view (jika dipisah)
# p_*.blade.php  - Pagination (jika ada)
```

### 3. Catat Endpoint yang Digunakan

```javascript
// Contoh endpoint yang umum:
GET  /{menu}/add          - Form tambah
POST /{menu}/save         - Simpan data
GET  /{menu}/view/{id}    - Detail data
POST /{menu}/update       - Update data
POST /{menu}/delete       - Delete data
POST /{menu}/search       - Search/filter
GET  /{menu}/pdf          - Export PDF
GET  /{menu}/excel        - Export Excel
```

### 4. Backup File Lama

```bash
# Backup sebelum refactoring
cp -r resources/views/{nama_menu}/ resources/views/{nama_menu}_backup/
```

---

## 📁 Struktur File & Konvensi

### File Naming Convention

```
resources/views/{module_name}/
├── v_{module_name}.blade.php       # Main view (list)
├── s_{module_name}.blade.php       # Search results partial
├── f_{module_name}.blade.php       # Form add/edit
├── e_{module_name}.blade.php       # Edit view (optional)
└── p_{module_name}.blade.php       # Pagination (optional)
```

### Controller Pattern

```php
// app/Http/Controllers/{ModuleName}Controller.php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\{ModuleName}Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class {ModuleName}Controller extends Controller
{
    protected $service;

    public function __construct({ModuleName}Service $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        // Load view with permissions
    }

    public function add()
    {
        // Show add form
    }

    public function save(Request $request)
    {
        // Validate and save
    }

    public function view($id)
    {
        // Show detail
    }

    public function edit($id)
    {
        // Show edit form
    }

    public function update(Request $request)
    {
        // Validate and update
    }

    public function delete(Request $request)
    {
        // Delete data
    }

    public function search(Request $request)
    {
        // Search/filter data
    }
}
```

### Service Pattern

```php
// app/Services/{ModuleName}Service.php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class {ModuleName}Service
{
    public function getAll($filters = [])
    {
        // Get all data with filters
    }

    public function getById($id)
    {
        // Get single data
    }

    public function create($data)
    {
        // Create new data
    }

    public function update($id, $data)
    {
        // Update existing data
    }

    public function delete($ids)
    {
        // Delete data (support multiple)
    }

    public function checkDuplicate($nama, $excludeId = null)
    {
        // Check for duplicate data
    }
}
```

---

## 🔴 Common Issues & Solutions

### Issue 1: Missing CSRF Token

**Symptoms:**
- Error 419 (Page Expired)
- Form submit tidak berfungsi
- AJAX request ditolak

**Solution:**

```blade
<!-- Di Form -->
<form action="{{ url('module/save') }}" method="POST">
    @csrf
    <!-- form fields -->
</form>
```

```javascript
// Di AJAX Request
$.ajax({
    url: "{{ url('module/save') }}",
    type: "POST",
    data: {
        _token: "{{ csrf_token() }}",
        // other data
    }
});
```

---

### Issue 2: Translation Keys Tidak Dikenali

**Symptoms:**
- Modal menampilkan text literal seperti `confirm_header`
- Button menampilkan `delete` bukan `Hapus` atau `Delete`
- Alert menampilkan key bukan text

**Root Cause:**
Translation file ada di `lang/{en|id}/app.php` tapi tidak menggunakan prefix `app.`

**Solution:**

```blade
<!-- ❌ SALAH -->
{{ __('confirm_header') }}
{{ __('confirm_message') }}
{{ __('delete') }}
{{ __('close') }}
{{ __('add') }}
{{ __('edit') }}
{{ __('save') }}
{{ __('alert-success-delete') }}

<!-- ✅ BENAR -->
{{ __('app.confirm_header') }}
{{ __('app.confirm_message') }}
{{ __('app.delete') }}
{{ __('app.close') }}
{{ __('app.add') }}
{{ __('app.edit') }}
{{ __('app.save') }}
{{ __('app.alert-success-delete') }}
```

**Translation File Structure:**

```php
// lang/en/app.php
return [
    'confirm_header' => 'Confirm',
    'confirm_message' => 'Are you sure you want to delete this data ?',
    'delete' => 'Delete',
    'close' => 'Close',
    'add' => 'Add',
    'edit' => 'Edit',
    'save' => 'Save',
    'alert-success-delete' => 'Data deleted successfully',
    'alert-error-delete' => 'Failed to delete data',
    'Pilih dahulu data yang akan di hapus' => 'Please select data to delete first',
];
```

---

### Issue 3: Delete Button Tidak Disabled by Default

**Symptoms:**
- Button delete aktif meskipun belum ada data yang dipilih
- User bisa klik delete tanpa memilih data

**Solution:**

```blade
<!-- ❌ SALAH -->
<button id="btnDelete" data-toggle="modal">
    <i class="mdi mdi-delete"></i> {{ __('app.delete') }}
</button>

<!-- ✅ BENAR -->
<button id="btnDelete" data-toggle="modal" disabled>
    <i class="mdi mdi-delete"></i> {{ __('app.delete') }}
</button>
```

---

### Issue 4: AJAX Data Serialize Tidak Benar

**Symptoms:**
- Checkbox yang dipilih tidak terkirim ke server
- Error di endpoint `welcome/test`
- Modal delete tidak menampilkan nama data

**Root Cause:**
`.serialize()` tidak mengirim data array checkbox dengan benar

**Solution:**

```javascript
// ❌ SALAH
data: $("input:checkbox[name='checkID[]']:checked").serialize()

// ✅ BENAR
data: {
    checkID: $("input:checkbox[name='checkID[]']:checked").map(function(){
        return this.value;
    }).get(),
    _token: "{{ csrf_token() }}"
}
```

**URL Format:**

```javascript
// ❌ SALAH
url: "{{ url('welcome/test/?table=module&field=Nama') }}"

// ✅ BENAR
url: "{{ url('welcome/test') }}/?table=module&field=Nama"
```

---

### Issue 5: Modal Backdrop Tidak Terhapus

**Symptoms:**
- Setelah delete, modal hilang tapi halaman tetap abu-abu/gelap
- User tidak bisa klik apapun
- Success alert muncul tapi halaman tidak bisa diinteraksi

**Root Cause:**
Bootstrap modal tidak cleanup backdrop secara otomatis

**Solution:**

```javascript
$("#hapus").modal("hide");
$("body").removeClass("modal-open");
$(".modal-backdrop").remove();
```

**Full Implementation:**

```javascript
success:function(response){
    // Remove rows
    if(response.status === 'success' && response.removed_ids) {
        response.removed_ids.forEach(function(id) {
            var className = '.' + response.class_prefix + id;
            $(className).remove();
        });
    }

    // Hide modal and cleanup
    $("#hapus").modal("hide");
    $("body").removeClass("modal-open");
    $(".modal-backdrop").remove();

    // Show success alert
    $(".alert-success").show();
    $(".alert-success-content").html("{{ __('app.alert-success-delete') }}");
    window.setTimeout(function() { $(".alert-success").slideUp(); }, 10000);

    // Refresh data
    filter();
}
```

---

### Issue 6: show_btnDelete Tidak Berfungsi

**Symptoms:**
- Button delete tidak aktif meskipun sudah centang data
- Error: `show_btnDelete is not defined`

**Root Cause:**
Function `show_btnDelete` dipanggil dari inline onclick tapi belum dideklarasikan sebagai global

**Solution:**

```javascript
// Deklarasikan sebagai global function
window.show_btnDelete = function(){
    i=0; hasil = false;
    while(document.getElementsByName('checkID[]').length > i) {
        var checkname = document.getElementById('checkID'+i);
        if(checkname && checkname.checked == true) {
            hasil = true;
        }
        i++;
    }
    if(hasil == true) {
        $('#btnDelete').removeAttr('disabled');
        $('#btnDelete').removeAttr('href');
        $('#btnDelete').removeAttr('title');
        $('#btnDelete').attr('href', '#hapus');
    } else {
        $('#btnDelete').attr('disabled','disabled');
        $('#btnDelete').attr('href','#');
        $('#btnDelete').attr('title', '{{ __('app.Pilih dahulu data yang akan di hapus') }}');
    }
}

// Panggil saat pertama kali load
show_btnDelete();
```

---

### Issue 7: Checkbox Row Tidak Highlight

**Symptoms:**
- Saat checkbox dicentang, row tidak berubah warna (tidak ada class `table-danger`)

**Solution:**

```javascript
$("input:checkbox[name='checkID[]']").click(function(){
    if(this.checked == true){
        $(this).parents('tr').addClass('table-danger');
    } else {
        $(this).parents('tr').removeClass('table-danger');
    }
});
```

---

### Issue 8: checkAll Tidak Berfungsi

**Symptoms:**
- Checkbox "select all" tidak mencentang semua data
- Error: `checkall is not defined`

**Solution:**

```javascript
// Function checkall (bisa ditaruh di v_*.blade.php)
function checkall(chkAll, checkid) {
    if (checkid != null) {
        if (checkid.length == null) {
            checkid.checked = chkAll.checked;
        } else {
            for (i=0; i<checkid.length; i++) {
                checkid[i].checked = chkAll.checked;
            }
        }
        
        // Optional: Highlight rows
        $("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
        $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
        
        // Trigger show_btnDelete
        show_btnDelete();
    }
}
```

```blade
<!-- Di HTML -->
<input type="checkbox" name="checkAll" id="checkAll"
       onClick="checkall(this, document.forms.namedItem('f_delete_module')); show_btnDelete();">
```

---

## 💻 Code Patterns

### Pattern 1: Main View (v_*.blade.php)

```blade
@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="button-list">
                    @if($Create == 'YA')
                        <a href="{{ url('{module}/add') }}" 
                           class="btn btn-bordered-primary waves-effect width-md waves-light">
                            <i class="mdi mdi-plus"></i> {{ __('app.add') }} Data
                        </a>
                    @endif
                    <button class="btn btn-bordered-danger waves-effect width-md waves-light" 
                            id="btnDelete" 
                            data-placement="top" 
                            title="Silahkan pilih data terlebih dahulu." 
                            data-toggle="modal" 
                            disabled>
                        <i class="mdi mdi-delete"></i> {{ __('app.delete') }}
                    </button>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-12">
                <label class="col-form-label">
                    <h4 class="m-0">{{ __('app.keyword_legend') }}</h4>
                </label>
                <input type="text" class="form-control keyword" 
                       onkeyup="filter()" 
                       placeholder="{{ __('app.keyword') }} ..">
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div id="konten"></div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

function filter(url) {
    if(url == null) {
        url = "{{ url('{module}/search') }}";
    }

    $.ajax({
        type: "POST",
        url: url,
        data: {
            keyword : $(".keyword").val(),
            _token: "{{ csrf_token() }}"
        },
        beforeSend: function(data) {
            $("#konten").html("<center><h3><i class='fa fa-spin fa-spinner'></i> Loading.. </h3></center>");
        },
        success: function(data) {
            $("#konten").html(data);
        }
    });
    return false;
}

filter();
</script>
@endpush
```

---

### Pattern 2: Search Results (s_*.blade.php)

```blade
<p>{!! $total_row !!}</p>
<form id="f_delete_{module}" action="{{ url('{module}/delete') }}" >
    @csrf
    <div class="table-responsive">
        <table class="table table-bordered mb-0 table-hover tablesorter">
            <thead class="bg-primary text-white">
                <tr>
                @if($Delete == 'YA')
                    <th width="2%">
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkAll" id="checkAll"
                                   onClick="checkall(this, document.forms.namedItem('f_delete_{module}')); show_btnDelete();">
                            <label for="checkAll"></label>
                        </div>
                    </th>
                @endif
                    <th class="text-center" width="2%">No.</th>
                    <th width="93%">Nama</th>
                </tr>
            </thead>
            <tbody>
            @php $no=$offset; $i=0; @endphp
            @foreach($query as $row)
                <tr class="{module}_{{ $row->ID }}">
                @if($Delete == 'YA')
                    <td>
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkID[]" id="checkID{{ $i }}"
                                   onclick="show_btnDelete()" value="{{ $row->ID }}">
                            <label for="checkID{{ $i }}"></label>
                        </div>
                    </td>
                @endif
                    <td class="text-center">{{ ++$no }}.</td>
                    <td>
                        @if($Update == 'YA')
                            <a href="{{ url('{module}/view/'.$row->ID) }}">{{ $row->Nama }}</a>
                        @else
                            {{ $row->Nama }}
                        @endif
                    </td>
                </tr>
                @php $i++; @endphp
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="row">
        <div class="col-md-12">
            {!! $link !!}
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="hapus" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="hapus" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="hapus">{{ __('app.confirm_header') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <p>{{ __('app.confirm_message') }}</p>
                    <p class="data_name"></p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger waves-effect">{{ __('app.delete') }}</button>
                    <button type="button" class="btn btn-primary waves-effect waves-light" data-dismiss="modal">{{ __('app.close') }}</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
tablesorter();

// Global function for show_btnDelete
window.show_btnDelete = function(){
    i=0; hasil = false;
    while(document.getElementsByName('checkID[]').length > i) {
        var checkname = document.getElementById('checkID'+i);
        if(checkname && checkname.checked == true) {
            hasil = true;
        }
        i++;
    }
    if(hasil == true) {
        $('#btnDelete').removeAttr('disabled');
        $('#btnDelete').removeAttr('href');
        $('#btnDelete').removeAttr('title');
        $('#btnDelete').attr('href', '#hapus');
    } else {
        $('#btnDelete').attr('disabled','disabled');
        $('#btnDelete').attr('href','#');
        $('#btnDelete').attr('title', '{{ __('app.Pilih dahulu data yang akan di hapus') }}');
    }
}

// Form submit handler
$("#f_delete_{module}").submit(function(){
    $.ajax({
        type: "POST",
        url: $("#f_delete_{module}").attr('action'),
        data: $("#f_delete_{module}").serialize(),
        dataType: 'json',
        success:function(response){
            if(response.status === 'success' && response.removed_ids) {
                response.removed_ids.forEach(function(id) {
                    var className = '.' + response.class_prefix + id;
                    $(className).remove();
                });
            }

            // Hide modal and cleanup
            $("#hapus").modal("hide");
            $("body").removeClass("modal-open");
            $(".modal-backdrop").remove();

            // Show success alert
            $(".alert-success").show();
            $(".alert-success-content").html("{{ __('app.alert-success-delete') }}");
            window.setTimeout(function() { $(".alert-success").slideUp(); }, 10000);

            // Refresh filter
            filter();
        },
        error: function(data){
            $(".alert-error").show();
            $(".alert-error-content").html("{{ __('app.alert-error-delete') }}");
            window.setTimeout(function() { $(".alert-error").slideUp('slow'); }, 6000);
        }
    });
    return false;
});

// Checkbox row highlight
$("input:checkbox[name='checkID[]']").click(function(){
    if(this.checked == true){
        $(this).parents('tr').addClass('table-danger');
    } else {
        $(this).parents('tr').removeClass('table-danger');
    }
});

// Get selected data names for modal
$('#btnDelete').click(function(){
    $.ajax({
        url: "{{ url('welcome/test') }}/?table={table_name}&field={field_name}",
        type: "POST",
        data: {
            checkID: $("input:checkbox[name='checkID[]']:checked").map(function(){
                return this.value;
            }).get(),
            _token: "{{ csrf_token() }}"
        },
        success: function(data){
            $('.data_name').html(data);
        }
    });
});

// Initialize on load
show_btnDelete();
</script>

<script src="{{ asset('assets/theme/scripts/responsive-table.js') }}"></script>
```

---

### Pattern 3: Check All Function (Global)

```javascript
// Bisa ditaruh di v_*.blade.php atau file JS global
function checkall(chkAll, checkid) {
    if (checkid != null) {
        if (checkid.length == null) {
            checkid.checked = chkAll.checked;
        } else {
            for (i=0; i<checkid.length; i++) {
                checkid[i].checked = chkAll.checked;
            }
        }
        
        // Clear all highlights
        $("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
        
        // Add highlight to checked rows
        $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
        
        // Update delete button state
        show_btnDelete();
    }
}
```

---

## ✅ Checklist Refactoring

### Phase 1: Preparation

- [ ] Pilih menu referensi yang sudah stabil
- [ ] Backup file lama
- [ ] Catat semua endpoint yang digunakan
- [ ] Identifikasi semua fitur CRUD
- [ ] Cek translation keys yang dibutuhkan

### Phase 2: View Files

#### v_*.blade.php (Main View)

- [ ] Button delete memiliki attribute `disabled`
- [ ] Button structure sama dengan referensi
- [ ] Translation keys menggunakan prefix `app.`
- [ ] Filter function ada dan berfungsi
- [ ] AJAX setup dengan CSRF token
- [ ] Loading indicator saat filter

#### s_*.blade.php (Search Results)

- [ ] Form delete memiliki `@csrf` directive
- [ ] Modal delete menggunakan `__('app.*')` untuk semua text
- [ ] AJAX btnDelete click handler mengirim data sebagai array
- [ ] AJAX request menyertakan `_token`
- [ ] Modal backdrop removal ditambahkan setelah hide
- [ ] JavaScript translations konsisten
- [ ] Function `show_btnDelete` dideklarasikan sebagai global
- [ ] Function `checkall` dipanggil dengan benar
- [ ] Row highlight saat checkbox dicentang
- [ ] Initialize `show_btnDelete()` on load

#### f_*.blade.php (Form Add/Edit)

- [ ] Form memiliki `@csrf` directive
- [ ] Validation errors ditampilkan dengan benar
- [ ] Translasi menggunakan prefix `app.`
- [ ] Form validation client-side
- [ ] Success/error handling

### Phase 3: Controller & Service

- [ ] Controller method lengkap (index, add, save, view, edit, update, delete, search)
- [ ] Service layer untuk business logic
- [ ] Validation di controller
- [ ] Error handling yang proper
- [ ] Response format konsisten (JSON untuk AJAX)

### Phase 4: Routes

- [ ] Semua routes terdaftar di `routes/web.php`
- [ ] Route naming convention konsisten
- [ ] Middleware yang sesuai

### Phase 5: Testing

- [ ] Test Create data
- [ ] Test Read/View data
- [ ] Test Update data
- [ ] Test Delete data (single & multiple)
- [ ] Test modal delete muncul dengan text yang benar
- [ ] Test modal delete tertutup sempurna setelah delete
- [ ] Test success/error alert muncul
- [ ] Test pagination (jika ada)
- [ ] Test search/keyword filter
- [ ] Test select all / deselect all
- [ ] Test row highlight saat checkbox dicentang
- [ ] Clear cache: `php artisan view:clear`

---

## 🧪 Testing Guidelines

### Test Case Template

```markdown
## TC-{ID}: {Test Case Name}

**Objective:** {What to test}

**Preconditions:**
- User logged in
- On {module} page

**Test Steps:**
1. {Step 1}
2. {Step 2}
3. {Step 3}

**Expected Result:**
- {Expected outcome 1}
- {Expected outcome 2}

**Status:** ⬜ Pass  Fail

**Notes:**
{Any additional information}
```

### Essential Test Cases

#### TC-001: Delete Single Data

```markdown
**Objective:** Test delete single data with confirmation

**Test Steps:**
1. Open module page
2. Check 1 data checkbox
3. Click delete button
4. Verify modal shows data name
5. Click "Delete" in modal
6. Verify success alert
7. Verify data removed from table

**Expected Result:**
- Delete button enabled after checking
- Modal shows "Konfirmasi" and data name
- Modal closes completely after delete
- Success alert: "Data berhasil dihapus"
- Data removed from table
- Table refreshed

**Status:** ⬜ Pass  Fail
```

#### TC-002: Delete Multiple Data

```markdown
**Objective:** Test delete multiple data at once

**Test Steps:**
1. Open module page
2. Check 2 or more checkboxes
3. Click delete button
4. Verify modal shows all selected data names
5. Click "Delete" in modal
6. Verify all data removed

**Expected Result:**
- All selected data names shown in modal
- All data deleted successfully
- Success alert shown
- Table refreshed

**Status:** ⬜ Pass ⬜ Fail
```

#### TC-003: Delete Without Selection

```markdown
**Objective:** Test delete button disabled when no selection

**Test Steps:**
1. Open module page
2. Don't check any checkbox
3. Try to click delete button

**Expected Result:**
- Delete button is disabled
- Button cannot be clicked
- No modal appears

**Status:** ⬜ Pass ⬜ Fail
```

#### TC-004: Cancel Delete

```markdown
**Objective:** Test cancel delete operation

**Test Steps:**
1. Check data checkbox
2. Click delete button
3. Click "Close" or X in modal
4. Verify data not deleted

**Expected Result:**
- Modal closes
- Data not deleted
- Checkbox remains checked
- Delete button remains enabled

**Status:** ⬜ Pass ⬜ Fail
```

#### TC-005: Select All / Deselect All

```markdown
**Objective:** Test select all functionality

**Test Steps:**
1. Open module page
2. Click "Select All" checkbox
3. Verify all checkboxes checked
4. Click "Select All" again to uncheck
5. Verify all checkboxes unchecked

**Expected Result:**
- All checkboxes checked when select all clicked
- All checkboxes unchecked when deselected
- Delete button state updates correctly
- Row highlights update correctly

**Status:** ⬜ Pass  Fail
```

#### TC-006: Translation Test

```markdown
**Objective:** Test translations in different languages

**Test Steps:**
1. Set language to Indonesian
2. Open module page
3. Click delete button
4. Verify modal text in Indonesian
5. Change language to English
6. Click delete button again
7. Verify modal text in English

**Expected Result:**
- Indonesian: "Konfirmasi", "Apakah anda yakin...", "Hapus", "Tutup"
- English: "Confirm", "Are you sure...", "Delete", "Close"

**Status:** ⬜ Pass ⬜ Fail
```

---

## 🚨 Troubleshooting

### Error: Modal shows literal text like `confirm_header`

**Diagnosis:**
```bash
# Check translation file exists
ls -la lang/en/app.php
ls -la lang/id/app.php

# Check translation key exists
grep "confirm_header" lang/en/app.php
```

**Solution:**
Change `__('confirm_header')` to `__('app.confirm_header')`

---

### Error: 419 Page Expired on form submit

**Diagnosis:**
```bash
# Check if @csrf exists in form
grep -n "@csrf" resources/views/{module}/s_{module}.blade.php
```

**Solution:**
Add `@csrf` directive to form:
```blade
<form action="{{ url('module/save') }}" method="POST">
    @csrf
    <!-- fields -->
</form>
```

---

### Error: Modal backdrop not removed after delete

**Diagnosis:**
Check browser console for modal-related errors

**Solution:**
Add cleanup code after modal hide:
```javascript
$("#hapus").modal("hide");
$("body").removeClass("modal-open");
$(".modal-backdrop").remove();
```

---

### Error: welcome/test endpoint returns error

**Diagnosis:**
Check network tab for AJAX request details

**Solution:**
Fix AJAX data format:
```javascript
// Wrong
data: $("input:checkbox[name='checkID[]']:checked").serialize()

// Correct
data: {
    checkID: $("input:checkbox[name='checkID[]']:checked").map(function(){
        return this.value;
    }).get(),
    _token: "{{ csrf_token() }}"
}
```

---

### Error: show_btnDelete is not defined

**Diagnosis:**
Check browser console for JavaScript errors

**Solution:**
Declare function as global:
```javascript
window.show_btnDelete = function(){
    // implementation
}
```

---

### Error: Cache issues after changes

**Diagnosis:**
Changes not reflected in browser

**Solution:**
```bash
# Clear view cache
php artisan view:clear

# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Hard refresh browser: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
```

---

## 📚 Best Practices

### 1. Code Consistency

- Use same patterns as reference menus
- Follow naming conventions strictly
- Keep code structure consistent across modules

### 2. Translation Management

- Always use `__('app.key')` for common text
- Keep translation keys in `lang/{en|id}/app.php`
- Don't hardcode text in views

### 3. Error Handling

- Always handle both success and error cases
- Show user-friendly error messages
- Log errors for debugging

### 4. Security

- Always use CSRF tokens
- Validate all input data
- Check user permissions before actions
- Use prepared statements for queries

### 5. Performance

- Use eager loading for relationships
- Implement pagination for large datasets
- Cache static data when possible
- Optimize database queries

### 6. User Experience

- Show loading indicators during AJAX calls
- Provide clear success/error feedback
- Disable buttons when appropriate
- Confirm destructive actions (delete)

### 7. Code Documentation

- Comment complex logic
- Use meaningful variable names
- Document function parameters and return values
- Keep functions small and focused

---

## 📖 Additional Resources

### Laravel Documentation
- [Forms & CSRF](https://laravel.com/docs/csrf)
- [Blade Templates](https://laravel.com/docs/blade)
- [AJAX & Laravel](https://laravel.com/docs/responses)
- [Validation](https://laravel.com/docs/validation)

### Bootstrap Documentation
- [Modals](https://getbootstrap.com/docs/4.6/components/modal/)
- [Buttons](https://getbootstrap.com/docs/4.6/components/buttons/)
- [Forms](https://getbootstrap.com/docs/4.6/components/forms/)

### jQuery Documentation
- [AJAX](https://api.jquery.com/category/ajax/)
- [Selectors](https://api.jquery.com/category/selectors/)
- [Event Handlers](https://api.jquery.com/category/events/)

---

## 📝 Module-Specific Documentation

For module-specific refactoring details, see:
- [REFACTORING_METODE_PEMBAYARAN.md](./REFACTORING_METODE_PEMBAYARAN.md)

---

## 🔄 Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | April 2, 2026 | Refactoring Team | Initial release |

---

**Last Updated:** April 2, 2026  
**Author:** Refactoring Team  
**Status:** ✅ Active  
**Next Review:** As needed
