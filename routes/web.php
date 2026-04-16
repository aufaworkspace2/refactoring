<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\LevelModulController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\PilihanPendaftaranPmbController;
use App\Http\Controllers\GelombangPmbController;
use App\Http\Controllers\SumberInformasiPendaftaranController;
use App\Http\Controllers\JalurPendaftaranPmbController;
use App\Http\Controllers\SyaratPmbController;
use App\Http\Controllers\MasterFormatNimPmbController;
use App\Http\Controllers\SettingProdiTambahanJurusanController;
use App\Http\Controllers\SettingNomorPmbController;
use App\Http\Controllers\AgentPmbController;
use App\Http\Controllers\SettingPilihanTambahanPmbController;
use App\Http\Controllers\SettingRedaksiPmbController;
use App\Http\Controllers\AgendaPmbController;
use App\Http\Controllers\ArtikelPmbController;
use App\Http\Controllers\BannerPmbController;
use App\Http\Controllers\InfoPmbController;
use App\Http\Controllers\MenuPmbController;
use App\Http\Controllers\PagePmbController;
use App\Http\Controllers\DataLeadsPmbController;
use App\Http\Controllers\KategoriSoalPmbController;
use App\Http\Controllers\ResetUsmPmbController;
use App\Http\Controllers\JadwalUsmPmbController;
use App\Http\Controllers\JenisUsmPmbController;
use App\Http\Controllers\CalonMahasiswaController;
use App\Http\Controllers\NilaiUsmPmbController;
use App\Http\Controllers\TesKesehatanPmbController;
use App\Http\Controllers\SetDraftRegistrasiUlangController;
use App\Http\Controllers\MahasiswaDiskonPmbController;
use App\Http\Controllers\SetRegistrasiUlangController;
use App\Http\Controllers\CetakPerprodiPmbController;
use App\Http\Controllers\JumlahSudahBayarRegistrasiUlangPmbController;
use App\Http\Controllers\RekapitulasiPendaftaranPmbController;
use App\Http\Controllers\KegiatanSkpiController;
use App\Http\Controllers\JenisKategoriSkpiController;
use App\Http\Controllers\KategoriKegiatanSkpiController;
use App\Http\Controllers\RekapitulasiReferensiDaftarPmbController;
use App\Http\Controllers\NilaiKegiatanSkpiController;
use App\Http\Controllers\KategoriPencapaianController;
use App\Http\Controllers\InformasiController;
use App\Http\Controllers\PencapaianController;
use App\Http\Controllers\ApproveInformasiController;
use App\Http\Controllers\SkpiController;
use App\Http\Controllers\ProgramStudiController;
use App\Http\Controllers\GeneralAjaxController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\KeteranganStatusMahasiswaController;
use App\Http\Controllers\MetodePembayaranController;
use App\Http\Controllers\ChannelPembayaranController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\OpsiMahasiswaController;
use App\Http\Controllers\JenisBiayaController;
use App\Http\Controllers\MasterDiskonController;
use App\Http\Controllers\BiayaController;
use App\Http\Controllers\PaketSksController;
use App\Http\Controllers\SetupPersentaseBayarController;
use App\Http\Controllers\SetupMahasiswaDiskonSampaiLulusController;
use App\Http\Controllers\SetupDuedatePembayaranController;
use App\Http\Controllers\SettingTerminPembayaranMahasiswaController;
use App\Http\Controllers\SetupHargaBiayaVariableController;
use App\Http\Controllers\SetupMinimalBayarGenerateNimController;
use App\Http\Controllers\SetupDendaController;
use App\Http\Controllers\SetupUktController;
use App\Http\Controllers\SetupModePembayaranStudentController;
use App\Http\Controllers\SettingDuedatePembayaranKeseluruhanController;
use App\Http\Controllers\MahasiswaDiskonController;
use App\Http\Controllers\MahasiswaDiskonTelatController;
use App\Http\Controllers\SetupMinimalBayarCicilanBebasStudentController;
use App\Http\Controllers\SettingBiayaLainnyaController;
use App\Http\Controllers\ListTidakKrsController;
use App\Http\Controllers\GenerateTagihanController;
use App\Http\Controllers\HasilStudiController;
use App\Http\Controllers\PerkembanganAkademikController;
use App\Http\Controllers\RencanaStudiController;
use App\Http\Controllers\LaporanStatusInputNilaiController;
use App\Http\Controllers\RekapNilaiController;
use App\Http\Controllers\KonversiController;
use App\Http\Controllers\TranskripMahasiswaController;
use App\Http\Controllers\PublishNilaiUasController;

// Main route - shows login page if not logged in, otherwise redirects to dashboard

Route::get('/', [LoginController::class, 'index'])->name('welcome');

// Login/Logout Routes (Public)
Route::prefix('welcome')->group(function () {
    Route::post('login', [LoginController::class, 'login'])->name('welcome.login');
    Route::get('logout', [LoginController::class, 'logout'])->name('welcome.logout');
    Route::post('cek_logout', [LoginController::class, 'cekLogout'])->name('welcome.cek_logout');
    Route::get('captcha', [LoginController::class, 'captcha'])->name('welcome.captcha');
    Route::get('change_language/{lang}/{i18?}', [LoginController::class, 'changeLanguage'])->name('welcome.change_language');
    Route::post('savepass', [LoginController::class, 'savePass'])->middleware('CheckUserSession')->name('welcome.savepass');
    
    // Other welcome routes that need authentication
    Route::middleware(['CheckUserSession'])->group(function () {
        Route::get('/search_alur', [WelcomeController::class, 'search_alur']);
        Route::get('/menu/{id}', [WelcomeController::class, 'menu']);
        Route::get('/help/{id}', [WelcomeController::class, 'help']);
        Route::get('/cariuser/{id}', [WelcomeController::class, 'cariuser']);
        Route::match(['get', 'post'], '/test', [WelcomeController::class, 'test']);
    });
});

// Dashboard protected by Session Login
Route::middleware([\App\Http\Middleware\CheckUserSession::class])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/reset', [DashboardController::class, 'reset'])->name('dashboard.reset');
    Route::get('dashboard/modul/{id}', [DashboardController::class, 'set_modul'])->name('dashboard.set_modul');
});

Route::prefix('level')->group(function () {
    Route::get('/', [LevelController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [LevelController::class , 'search']);
    Route::get('add', [LevelController::class , 'add']);
    Route::get('view/{id}', [LevelController::class , 'view']);
    Route::post('save/{save}', [LevelController::class , 'save']);
    Route::post('delete', [LevelController::class , 'delete']);
    Route::get('pdf', [LevelController::class , 'pdf']);
    Route::get('excel', [LevelController::class , 'excel']);
});

Route::prefix('levelmodul')->group(function () {
    Route::get('/', [LevelModulController::class , 'index']);
    Route::match(['get', 'post'], 'search', [LevelModulController::class , 'search']);
    Route::get('add', [LevelModulController::class , 'add']);
    Route::get('view/{id}', [LevelModulController::class , 'view']);
    Route::post('save/{save}', [LevelModulController::class , 'save']);
    Route::post('delete', [LevelModulController::class , 'delete']);
    Route::get('pdf', [LevelModulController::class , 'pdf']);
    Route::get('excel', [LevelModulController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('mahasiswa')->group(function () {
    Route::get('/', [MahasiswaController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [MahasiswaController::class , 'search']);
    Route::get('add', [MahasiswaController::class , 'add']);
    Route::get('view/{id}', [MahasiswaController::class , 'view']);
    Route::post('save/{save}', [MahasiswaController::class , 'save']);
    Route::post('delete', [MahasiswaController::class , 'delete']);
    Route::post('changeStatusKetua', [MahasiswaController::class , 'changeStatusKetua']);
    Route::post('saveKons', [MahasiswaController::class , 'saveKons']);
    Route::post('daftarFile', [MahasiswaController::class , 'daftarFile']);
    Route::get('showDocument', [MahasiswaController::class , 'showDocument']); // Updated to GET since redirect uses it
    Route::get('add_upload', [MahasiswaController::class , 'add_upload']);
    Route::post('upload_excel', [MahasiswaController::class , 'upload_excel']);
    Route::get('template_upload', [MahasiswaController::class , 'template_upload']);
    Route::get('template_upload_biografi', [MahasiswaController::class , 'template_upload_biografi']);
    Route::post('edit_excel', [MahasiswaController::class , 'edit_excel']);
    Route::get('add_student', [MahasiswaController::class , 'add_student']);
    Route::post('generate', [MahasiswaController::class , 'generate']);
    Route::get('pdf', [MahasiswaController::class , 'pdf']);
    Route::get('excel', [MahasiswaController::class , 'excel']);
    Route::get('pdf_ktm', [MahasiswaController::class , 'pdf_ktm']);

    // Ajax endpoints
    Route::get('jsonSekolah', [MahasiswaController::class , 'jsonSekolah']);
    Route::get('jsonPT', [MahasiswaController::class , 'jsonPT']);
    Route::get('jsonNegara', [MahasiswaController::class , 'jsonNegara']);
    Route::get('changeProdiPT', [MahasiswaController::class , 'changeProdiPT']);
    Route::post('changeWilayah', [MahasiswaController::class , 'changeWilayah']);
});

Route::middleware(['CheckUserSession'])->prefix('gelombang_pmb')->group(function () {
    Route::get('/', [GelombangPmbController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [GelombangPmbController::class , 'search']);
    Route::match(['get', 'post'], 'detail/{offset?}', [GelombangPmbController::class , 'detail']);
    Route::match(['get', 'post'], 'search_detail', [GelombangPmbController::class , 'search_detail']);
    Route::post('edit_tanggal_batch', [GelombangPmbController::class , 'edit_tanggal_batch']);
    Route::get('add', [GelombangPmbController::class , 'add']);
    Route::get('add_detail', [GelombangPmbController::class , 'add_detail']);
    Route::get('view/{id}', [GelombangPmbController::class , 'view']);
    Route::get('view_detail/{id}', [GelombangPmbController::class , 'view_detail']);
    Route::post('save/{save}', [GelombangPmbController::class , 'save']);
    Route::post('save_detail/{save}', [GelombangPmbController::class , 'save_detail']);
    Route::post('change_tahunmasuk', [GelombangPmbController::class , 'change_tahunmasuk']);
    Route::post('delete', [GelombangPmbController::class , 'delete']);
    Route::post('delete_detail', [GelombangPmbController::class , 'delete_detail']);
    Route::get('pdf', [GelombangPmbController::class , 'pdf']);
    Route::get('excel', [GelombangPmbController::class , 'excel']);
    Route::post('change_gelombang_detail_pmb', [GelombangPmbController::class , 'change_gelombang_detail_pmb']);
    Route::post('change_penawaran', [GelombangPmbController::class , 'change_penawaran']);
    Route::post('get_detail_pilihan_pendaftaran', [GelombangPmbController::class , 'get_detail_pilihan_pendaftaran']);
    Route::get('generate_gelombang', [GelombangPmbController::class , 'generate_gelombang']);
    Route::match(['get', 'post'], 'search_generate_gelombang/{gelombang_id?}', [GelombangPmbController::class , 'search_generate_gelombang']);
    Route::post('proses_generate_gelombang', [GelombangPmbController::class , 'proses_generate_gelombang']);
});

Route::middleware(['CheckUserSession'])->prefix('setting_redaksi_pmb')->group(function () {
    Route::get('/', [SettingRedaksiPmbController::class , 'index']);
    Route::post('get_default_setting_redaksi_pmb', [SettingRedaksiPmbController::class , 'get_default_setting_redaksi_pmb']);
    
    // Alert Kelulusan
    Route::get('alert_kelulusan', [SettingRedaksiPmbController::class , 'alert_kelulusan']);
    Route::post('save_alert_kelulusan', [SettingRedaksiPmbController::class , 'save_alert_kelulusan']);
    
    // Info Kelulusan
    Route::get('info_kelulusan', [SettingRedaksiPmbController::class , 'info_kelulusan']);
    Route::post('save_info_kelulusan', [SettingRedaksiPmbController::class , 'save_info_kelulusan']);
    
    // Info Pembayaran Formulir
    Route::get('info_pembayaran_formulir', [SettingRedaksiPmbController::class , 'info_pembayaran_formulir']);
    Route::post('save_info_pembayaran_formulir', [SettingRedaksiPmbController::class , 'save_info_pembayaran_formulir']);
    
    // Email Format
    Route::get('email', [SettingRedaksiPmbController::class , 'email']);
    Route::post('save_format_email', [SettingRedaksiPmbController::class , 'save_format_email']);
    
    // Upload File
    Route::post('upload_file', [SettingRedaksiPmbController::class , 'upload_file']);
});

Route::middleware(['CheckUserSession'])->prefix('agent_pmb')->group(function () {
    Route::get('/', [AgentPmbController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [AgentPmbController::class , 'search']);
    Route::get('add', [AgentPmbController::class , 'add']);
    Route::get('add_detail', [AgentPmbController::class , 'add_detail']);
    Route::get('view/{id}', [AgentPmbController::class , 'view']);
    Route::post('save/{save}', [AgentPmbController::class , 'save']);
    Route::post('delete', [AgentPmbController::class , 'delete']);
    Route::get('pdf', [AgentPmbController::class , 'pdf']);
    Route::get('excel', [AgentPmbController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('setting_pilihan_tambahan_pmb')->group(function () {
    Route::get('/', [SettingPilihanTambahanPmbController::class , 'index']);
    Route::post('set_publish_all/{save}', [SettingPilihanTambahanPmbController::class , 'set_publish_all']);
});

Route::middleware(['CheckUserSession'])->prefix('setting_nomor_pmb')->group(function () {
    Route::get('/', [SettingNomorPmbController::class , 'index']);
    Route::post('save/{save}', [SettingNomorPmbController::class , 'save']);
    Route::get('setting_nomor_invoice', [SettingNomorPmbController::class , 'setting_nomor_invoice']);
    Route::post('save_invoice/{save}', [SettingNomorPmbController::class , 'save_invoice']);
    Route::get('setting_nomor_nim', [SettingNomorPmbController::class , 'setting_nomor_nim']);
    Route::post('save_nim/{save}', [SettingNomorPmbController::class , 'save_nim']);
    Route::get('getMaster', [SettingNomorPmbController::class , 'getMaster']);
});

Route::middleware(['CheckUserSession'])->prefix('agenda_pmb')->group(function () {
    Route::get('/', [AgendaPmbController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [AgendaPmbController::class , 'search']);
    Route::get('add', [AgendaPmbController::class , 'add']);
    Route::get('view/{id}', [AgendaPmbController::class , 'view']);
    Route::post('save/{save}', [AgendaPmbController::class , 'save']);
    Route::post('delete', [AgendaPmbController::class , 'delete']);
    Route::post('getJudul', [AgendaPmbController::class , 'getJudul']);
    Route::post('upload_file', [AgendaPmbController::class , 'upload_file']);
});

Route::middleware(['CheckUserSession'])->prefix('artikel_pmb')->group(function () {
    Route::get('/', [ArtikelPmbController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [ArtikelPmbController::class , 'search']);
    Route::get('add', [ArtikelPmbController::class , 'add']);
    Route::get('view/{id}', [ArtikelPmbController::class , 'view']);
    Route::post('save/{save}', [ArtikelPmbController::class , 'save']);
    Route::post('delete', [ArtikelPmbController::class , 'delete']);
    Route::post('getJudul', [ArtikelPmbController::class , 'getJudul']);
    Route::post('upload_file', [ArtikelPmbController::class , 'upload_file']);
});

Route::middleware(['CheckUserSession'])->prefix('banner_pmb')->group(function () {
    Route::get('/', [BannerPmbController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [BannerPmbController::class , 'search']);
    Route::get('add', [BannerPmbController::class , 'add']);
    Route::get('view/{id}', [BannerPmbController::class , 'view']);
    Route::post('save/{save}', [BannerPmbController::class , 'save']);
    Route::post('delete', [BannerPmbController::class , 'delete'])->name('banner_pmb.delete');
    Route::post('getJudul', [BannerPmbController::class , 'getJudul']);
});

Route::middleware(['CheckUserSession'])->prefix('info_pmb')->group(function () {
    Route::get('/', [InfoPmbController::class , 'index']);
    Route::post('save', [InfoPmbController::class , 'save']);
});

Route::middleware(['CheckUserSession'])->prefix('menu_pmb')->group(function () {
    Route::get('/', [MenuPmbController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [MenuPmbController::class , 'search']);
    Route::get('add', [MenuPmbController::class , 'add']);
    Route::get('view/{id}', [MenuPmbController::class , 'view']);
    Route::post('save/{save}', [MenuPmbController::class , 'save']);
    Route::post('delete', [MenuPmbController::class , 'delete']);
    Route::post('getJudul', [MenuPmbController::class , 'getJudul']);
    Route::get('sort', [MenuPmbController::class , 'sort']);
    Route::post('save_sort', [MenuPmbController::class , 'save_sort']);
});

Route::middleware(['CheckUserSession'])->prefix('page_pmb')->group(function () {
    Route::get('/', [PagePmbController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [PagePmbController::class , 'search']);
    Route::get('add', [PagePmbController::class , 'add']);
    Route::get('view/{id}', [PagePmbController::class , 'view']);
    Route::post('save/{save}', [PagePmbController::class , 'save']);
    Route::post('delete', [PagePmbController::class , 'delete']);
    Route::post('getJudul', [PagePmbController::class , 'getJudul']);
    Route::post('upload_file', [PagePmbController::class , 'upload_file']);
});

Route::middleware(['CheckUserSession'])->prefix('data_leads_pmb')->group(function () {
    Route::get('/', [DataLeadsPmbController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [DataLeadsPmbController::class , 'search']);
    Route::post('delete', [DataLeadsPmbController::class , 'delete']);
    Route::get('pdf', [DataLeadsPmbController::class , 'pdf']);
    Route::get('excel', [DataLeadsPmbController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('kategori_soal_pmb')->group(function () {
    Route::get('/', [KategoriSoalPmbController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [KategoriSoalPmbController::class , 'search']);
    Route::get('add', [KategoriSoalPmbController::class , 'add']);
    Route::get('view/{id}', [KategoriSoalPmbController::class , 'view']);
    Route::post('save/{save}', [KategoriSoalPmbController::class , 'save']);
    Route::post('delete', [KategoriSoalPmbController::class , 'delete']);
    
    Route::get('soal', [KategoriSoalPmbController::class , 'soal']);
    Route::match(['get', 'post'], 'search_soal/{idkategori?}', [KategoriSoalPmbController::class , 'search_soal']);
    Route::get('add_soal', [KategoriSoalPmbController::class , 'add_soal']);
    Route::get('view_soal/{id}', [KategoriSoalPmbController::class , 'view_soal']);
    Route::post('save_soal/{save}', [KategoriSoalPmbController::class , 'save_soal']);
    Route::post('delete_soal', [KategoriSoalPmbController::class , 'delete_soal']);
    Route::get('copy_soal/{subaksi}', [KategoriSoalPmbController::class , 'copy_soal']);
    Route::post('save_copy_soal/{subaksi}', [KategoriSoalPmbController::class , 'save_copy_soal']);
    
    Route::get('subsoal', [KategoriSoalPmbController::class , 'subsoal']);
    Route::match(['get', 'post'], 'search_subsoal/{idkategori?}/{idsoal?}', [KategoriSoalPmbController::class , 'search_subsoal']);
    Route::get('add_subsoal', [KategoriSoalPmbController::class , 'add_subsoal']);
    Route::get('view_subsoal/{id}', [KategoriSoalPmbController::class , 'view_subsoal']);
    Route::post('save_subsoal/{save}', [KategoriSoalPmbController::class , 'save_subsoal']);
    Route::post('delete_subsoal', [KategoriSoalPmbController::class , 'delete_subsoal']);
    
    Route::get('pdf', [KategoriSoalPmbController::class , 'pdf']);
    Route::get('excel', [KategoriSoalPmbController::class , 'excel']);
    Route::post('upload_file', [KategoriSoalPmbController::class , 'upload_file']);
});

Route::middleware(['CheckUserSession'])->prefix('reset_usm_pmb')->group(function () {
    Route::get('/{offset?}/{bayar?}', [ResetUsmPmbController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [ResetUsmPmbController::class , 'search']);
    Route::post('save', [ResetUsmPmbController::class , 'save']);
});

Route::middleware(['CheckUserSession'])->prefix('jadwal_usm_pmb')->group(function () {
    Route::get('/', [JadwalUsmPmbController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [JadwalUsmPmbController::class , 'search']);
    Route::get('add', [JadwalUsmPmbController::class , 'add']);
    Route::get('view/{id}', [JadwalUsmPmbController::class , 'view']);
    Route::post('save/{save}', [JadwalUsmPmbController::class , 'save']);
    Route::post('delete', [JadwalUsmPmbController::class , 'delete']);
    
    Route::get('detail', [JadwalUsmPmbController::class , 'detail']);
    Route::match(['get', 'post'], 'search_detail/{jadwalusm_id?}', [JadwalUsmPmbController::class , 'search_detail']);
    Route::get('add_detail', [JadwalUsmPmbController::class , 'add_detail']);
    Route::get('view_detail/{id}', [JadwalUsmPmbController::class , 'view_detail']);
    Route::post('delete_detail', [JadwalUsmPmbController::class , 'delete_detail']);
    
    Route::get('pdf', [JadwalUsmPmbController::class , 'pdf']);
    Route::get('excel', [JadwalUsmPmbController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('jenis_usm_pmb')->group(function () {
    Route::get('/', [JenisUsmPmbController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [JenisUsmPmbController::class , 'search']);
    Route::get('add', [JenisUsmPmbController::class , 'add']);
    Route::get('view/{id}', [JenisUsmPmbController::class , 'view']);
    Route::post('save/{save}', [JenisUsmPmbController::class , 'save']);
    Route::post('delete', [JenisUsmPmbController::class , 'delete']);
    Route::get('pdf', [JenisUsmPmbController::class , 'pdf']);
    Route::get('excel', [JenisUsmPmbController::class , 'excel']);
});

Route::get('/calon_mahasiswa/histori_pindah_prodi/{id}', [CalonMahasiswaController::class, 'historiPindahProdi']);
Route::get('/calon_mahasiswa/lihat_pindah_channel/{id}', [CalonMahasiswaController::class, 'lihatPindahChannel']);
Route::get('/calon_mahasiswa/view/{id}', [CalonMahasiswaController::class, 'view']);
Route::middleware(['CheckUserSession'])->prefix('calon_mahasiswa')->group(function () {
    Route::get('/{offset?}/{bayar?}', [CalonMahasiswaController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [CalonMahasiswaController::class , 'search']);
    Route::get('add', [CalonMahasiswaController::class , 'add']);
   
  
    Route::post('save/{save}', [CalonMahasiswaController::class , 'save']);
    Route::post('delete', [CalonMahasiswaController::class , 'delete']);
    Route::post('upload_file', [CalonMahasiswaController::class , 'upload_file']);
    Route::post('updateStatusBayar', [CalonMahasiswaController::class , 'updateStatusBayar']);
    Route::post('verifikasi_all', [CalonMahasiswaController::class , 'verifikasiAll']);
    Route::post('setujian', [CalonMahasiswaController::class , 'setUjian']);
    Route::post('set_ikut_ujian', [CalonMahasiswaController::class , 'setIkutUjian']);
    Route::get('excel', [CalonMahasiswaController::class , 'excel']);
    Route::get('excel_referal', [CalonMahasiswaController::class , 'excelReferal']);
    Route::get('pdf', [CalonMahasiswaController::class , 'pdf']);
    Route::get('change_prodi', [CalonMahasiswaController::class , 'changeProdi']);
    Route::get('add_upload', [CalonMahasiswaController::class , 'addUpload']);
    Route::post('upload_excel', [CalonMahasiswaController::class , 'uploadExcel']);
    Route::get('get_gelombang_detail', [CalonMahasiswaController::class , 'getGelombangDetail']);
 
});
Route::prefix('nilai_usm_pmb')->get('print_skl/{id}', [NilaiUsmPmbController::class , 'print_skl']);
Route::middleware(['CheckUserSession'])->prefix('nilai_usm_pmb')->group(function () {
    Route::get('/{offset?}/{bayar?}', [NilaiUsmPmbController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [NilaiUsmPmbController::class , 'search']);
    Route::get('edit/{id}', [NilaiUsmPmbController::class , 'edit']);
    Route::post('save', [NilaiUsmPmbController::class , 'save']);
    Route::get('print_skl/{id}', [NilaiUsmPmbController::class , 'print_skl']);
    Route::get('export_excel', [NilaiUsmPmbController::class , 'export_excel']);
    Route::match(['get', 'post'], 'export', [NilaiUsmPmbController::class , 'export']);
});

Route::middleware(['CheckUserSession'])->prefix('tes_kesehatan_pmb')->group(function () {
    Route::get('/{offset?}/{bayar?}', [TesKesehatanPmbController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [TesKesehatanPmbController::class , 'search']);
    Route::post('set_lulus', [TesKesehatanPmbController::class , 'set_lulus']);
    Route::post('set_status_lulus', [TesKesehatanPmbController::class , 'set_status_lulus']);
    Route::get('download_template', [TesKesehatanPmbController::class , 'download_template']);
    Route::post('upload_excel', [TesKesehatanPmbController::class , 'upload_excel']);
    Route::get('export_excel', [TesKesehatanPmbController::class , 'export_excel']);
});

Route::middleware(['CheckUserSession'])->prefix('set_draft_registrasiulang')->group(function () {
    Route::get('/', [SetDraftRegistrasiUlangController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [SetDraftRegistrasiUlangController::class , 'search']);
    Route::get('get_tahun', [SetDraftRegistrasiUlangController::class , 'get_tahun']);
    Route::get('get_program', [SetDraftRegistrasiUlangController::class , 'get_program']);
    Route::get('get_prodi', [SetDraftRegistrasiUlangController::class , 'get_prodi']);
    Route::get('get_gelombang', [SetDraftRegistrasiUlangController::class , 'get_gelombang']);
    Route::get('get_gelombang_detail', [SetDraftRegistrasiUlangController::class , 'get_gelombang_detail']);
    Route::post('save', [SetDraftRegistrasiUlangController::class , 'save']);
    Route::get('detail_draft/{ID}', [SetDraftRegistrasiUlangController::class , 'detail_draft']);
});

Route::middleware(['CheckUserSession'])->prefix('setregistrasiulang')->group(function () {
    Route::get('/', [SetRegistrasiUlangController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [SetRegistrasiUlangController::class , 'search']);
    Route::post('save', [SetRegistrasiUlangController::class , 'save']);
    Route::get('get_tahun', [SetRegistrasiUlangController::class , 'get_tahun']);
    Route::get('get_program', [SetRegistrasiUlangController::class , 'get_program']);
    Route::get('get_prodi', [SetRegistrasiUlangController::class , 'get_prodi']);
    Route::get('get_gelombang', [SetRegistrasiUlangController::class , 'get_gelombang']);
    Route::get('get_gelombang_detail', [SetRegistrasiUlangController::class , 'get_gelombang_detail']);
});

Route::middleware(['CheckUserSession'])->prefix('jumlah_sudah_bayar_registrasi_ulang_pmb')->group(function () {
    Route::get('/', [JumlahSudahBayarRegistrasiUlangPmbController::class , 'index']);
    Route::match(['get', 'post'], 'search', [JumlahSudahBayarRegistrasiUlangPmbController::class , 'search']);
    Route::get('pdf', [JumlahSudahBayarRegistrasiUlangPmbController::class , 'pdf']);
    Route::get('excel', [JumlahSudahBayarRegistrasiUlangPmbController::class , 'excel']);
    Route::get('get_sekolah', [JumlahSudahBayarRegistrasiUlangPmbController::class , 'get_sekolah']);
});

Route::middleware(['CheckUserSession'])->prefix('rekapitulasi_pendaftaran_pmb')->group(function () {
    Route::get('/', [RekapitulasiPendaftaranPmbController::class , 'index']);
    Route::match(['get', 'post'], 'search', [RekapitulasiPendaftaranPmbController::class , 'search']);
    Route::get('pdf', [RekapitulasiPendaftaranPmbController::class , 'pdf']);
    Route::get('excel', [RekapitulasiPendaftaranPmbController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('rekapitulasi_referensi_daftar_pmb')->group(function () {
    Route::get('/', [RekapitulasiReferensiDaftarPmbController::class , 'index']);
    Route::match(['get', 'post'], 'search', [RekapitulasiReferensiDaftarPmbController::class , 'search']);
    Route::get('pdf', [RekapitulasiReferensiDaftarPmbController::class , 'pdf']);
    Route::get('excel', [RekapitulasiReferensiDaftarPmbController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('cetakperprodi_pmb')->group(function () {
    Route::get('/', [CetakPerprodiPmbController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [CetakPerprodiPmbController::class , 'search']);
    Route::get('cetak', [CetakPerprodiPmbController::class , 'cetak']);
});

Route::middleware(['CheckUserSession'])->prefix('mahasiswa_diskon_pmb')->group(function () {
    Route::get('/', [MahasiswaDiskonPmbController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [MahasiswaDiskonPmbController::class , 'search']);
    Route::get('get_tahun', [MahasiswaDiskonPmbController::class , 'get_tahun']);
    Route::get('get_program', [MahasiswaDiskonPmbController::class , 'get_program']);
    Route::get('get_prodi', [MahasiswaDiskonPmbController::class , 'get_prodi']);
    Route::get('get_gelombang', [MahasiswaDiskonPmbController::class , 'get_gelombang']);
    Route::get('get_gelombang_detail', [MahasiswaDiskonPmbController::class , 'get_gelombang_detail']);
    Route::post('changenominal', [MahasiswaDiskonPmbController::class , 'changenominal']);
    Route::get('add', [MahasiswaDiskonPmbController::class , 'add']);
    Route::get('view/{id}', [MahasiswaDiskonPmbController::class , 'view']);
    Route::post('filtermhs', [MahasiswaDiskonPmbController::class , 'filtermhs']);
    Route::post('save', [MahasiswaDiskonPmbController::class , 'save']);
    Route::post('set_diskon/{MhswDiskonID}', [MahasiswaDiskonPmbController::class , 'set_diskon']);
    Route::post('unset_diskon/{MhswDiskonID}', [MahasiswaDiskonPmbController::class , 'unset_diskon']);
    Route::get('lihat_detail/{MhswDiskonID}', [MahasiswaDiskonPmbController::class , 'lihat_detail']);
    Route::post('delete', [MahasiswaDiskonPmbController::class , 'delete'])->name('mahasiswa_diskon_pmb.delete');
    Route::post('aktifkan', [MahasiswaDiskonPmbController::class , 'aktifkan']);
    Route::get('excel', [MahasiswaDiskonPmbController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('calon_mahasiswa_baru')->group(function () {
    Route::get('/{offset?}/{bayar?}', [CalonMahasiswaController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [CalonMahasiswaController::class , 'search']);
    Route::get('add', [CalonMahasiswaController::class , 'add']);
    Route::get('view/{id}', [CalonMahasiswaController::class , 'view']);
    Route::post('save/{save}', [CalonMahasiswaController::class , 'save']);
    Route::post('delete', [CalonMahasiswaController::class , 'delete']);
    Route::post('upload_file', [CalonMahasiswaController::class , 'upload_file']);
});

Route::middleware(['CheckUserSession'])->prefix('setting_prodi_tambahan_jurusan')->group(function () {
    Route::get('/', [SettingProdiTambahanJurusanController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [SettingProdiTambahanJurusanController::class , 'search']);
    Route::get('add', [SettingProdiTambahanJurusanController::class , 'add']);
    Route::get('view/{id}', [SettingProdiTambahanJurusanController::class , 'view']);
    Route::post('save/{save}', [SettingProdiTambahanJurusanController::class , 'save']);
    Route::post('delete', [SettingProdiTambahanJurusanController::class , 'delete']);
});

Route::middleware(['CheckUserSession'])->prefix('master_format_nim_pmb')->group(function () {
    Route::get('/', [MasterFormatNimPmbController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [MasterFormatNimPmbController::class , 'search']);
    Route::get('add', [MasterFormatNimPmbController::class , 'add']);
    Route::get('add_detail', [MasterFormatNimPmbController::class , 'add_detail']);
    Route::get('view/{id}', [MasterFormatNimPmbController::class , 'view']);
    Route::post('save/{save}', [MasterFormatNimPmbController::class , 'save']);
    Route::post('delete', [MasterFormatNimPmbController::class , 'delete']);
    Route::get('pdf', [MasterFormatNimPmbController::class , 'pdf']);
    Route::get('excel', [MasterFormatNimPmbController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('syarat_pmb')->group(function () {
    Route::get('/', [SyaratPmbController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [SyaratPmbController::class , 'search']);
    Route::get('add', [SyaratPmbController::class , 'add']);
    Route::get('add_detail', [SyaratPmbController::class , 'add_detail']);
    Route::get('view/{id}', [SyaratPmbController::class , 'view']);
    Route::post('save/{save}', [SyaratPmbController::class , 'save']);
    Route::post('delete', [SyaratPmbController::class , 'delete']);
    Route::get('pdf', [SyaratPmbController::class , 'pdf']);
    Route::get('excel', [SyaratPmbController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('jalur_pendaftaran_pmb')->group(function () {
    Route::get('/', [JalurPendaftaranPmbController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [JalurPendaftaranPmbController::class , 'search']);
    Route::get('add', [JalurPendaftaranPmbController::class , 'add']);
    Route::get('add_detail', [JalurPendaftaranPmbController::class , 'add_detail']);
    Route::get('view/{id}', [JalurPendaftaranPmbController::class , 'view']);
    Route::post('save/{save}', [JalurPendaftaranPmbController::class , 'save']);
    Route::post('delete', [JalurPendaftaranPmbController::class , 'delete']);
    Route::post('aktif', [JalurPendaftaranPmbController::class , 'aktif']);
    Route::get('pdf', [JalurPendaftaranPmbController::class , 'pdf']);
    Route::get('excel', [JalurPendaftaranPmbController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('sumber_informasi_pendaftaran')->group(function () {
    Route::get('/', [SumberInformasiPendaftaranController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [SumberInformasiPendaftaranController::class , 'search']);
    Route::get('add', [SumberInformasiPendaftaranController::class , 'add']);
    Route::get('view/{id}', [SumberInformasiPendaftaranController::class , 'view']);
    Route::post('save/{save}', [SumberInformasiPendaftaranController::class , 'save']);
    Route::post('delete', [SumberInformasiPendaftaranController::class , 'delete']);
    Route::get('pdf', [SumberInformasiPendaftaranController::class , 'pdf']);
    Route::get('excel', [SumberInformasiPendaftaranController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('pilihan_pendaftaran_pmb')->group(function () {
    Route::get('/', [PilihanPendaftaranPmbController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [PilihanPendaftaranPmbController::class , 'search']);
    Route::get('add', [PilihanPendaftaranPmbController::class , 'add']);
    Route::get('add_detail', [PilihanPendaftaranPmbController::class , 'add_detail']);
    Route::get('view/{id}', [PilihanPendaftaranPmbController::class , 'view']);
    Route::post('save/{save}', [PilihanPendaftaranPmbController::class , 'save']);
    Route::post('delete', [PilihanPendaftaranPmbController::class , 'delete']);
    Route::post('aktif', [PilihanPendaftaranPmbController::class , 'aktif']);
    Route::post('changediskon', [PilihanPendaftaranPmbController::class , 'changediskon']);
    Route::get('pdf', [PilihanPendaftaranPmbController::class , 'pdf']);
    Route::get('excel', [PilihanPendaftaranPmbController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('kegiatan_skpi')->group(function () {
    Route::get('/', [KegiatanSkpiController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [KegiatanSkpiController::class , 'search']);
    Route::get('add', [KegiatanSkpiController::class , 'add']);
    Route::get('view/{id}', [KegiatanSkpiController::class , 'view']);
    Route::post('save/{save}', [KegiatanSkpiController::class , 'save']);
    Route::post('delete', [KegiatanSkpiController::class , 'delete']);
    Route::get('pdf', [KegiatanSkpiController::class , 'pdf']);
    Route::get('excel', [KegiatanSkpiController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('jenis_kategori_skpi')->group(function () {
    Route::get('/', [JenisKategoriSkpiController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [JenisKategoriSkpiController::class , 'search']);
    Route::get('add', [JenisKategoriSkpiController::class , 'add']);
    Route::get('view/{id}', [JenisKategoriSkpiController::class , 'view']);
    Route::post('save/{save}', [JenisKategoriSkpiController::class , 'save']);
    Route::post('delete', [JenisKategoriSkpiController::class , 'delete']);
    Route::get('pdf', [JenisKategoriSkpiController::class , 'pdf']);
    Route::get('excel', [JenisKategoriSkpiController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('kategori_kegiatan_skpi')->group(function () {
    Route::get('/', [KategoriKegiatanSkpiController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [KategoriKegiatanSkpiController::class , 'search']);
    Route::get('add', [KategoriKegiatanSkpiController::class , 'add']);
    Route::get('view/{id}', [KategoriKegiatanSkpiController::class , 'view']);
    Route::post('save/{save}', [KategoriKegiatanSkpiController::class , 'save']);
    Route::post('delete', [KategoriKegiatanSkpiController::class , 'delete']);
    Route::get('pdf', [KategoriKegiatanSkpiController::class , 'pdf']);
    Route::get('excel', [KategoriKegiatanSkpiController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('nilai_kegiatan_skpi')->group(function () {
    Route::get('/', [NilaiKegiatanSkpiController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [NilaiKegiatanSkpiController::class , 'search']);
    Route::get('add', [NilaiKegiatanSkpiController::class , 'add']);
    Route::get('view/{id}', [NilaiKegiatanSkpiController::class , 'view']);
    Route::post('save/{save}', [NilaiKegiatanSkpiController::class , 'save']);
    Route::post('delete', [NilaiKegiatanSkpiController::class , 'delete']);
    Route::get('pdf', [NilaiKegiatanSkpiController::class , 'pdf']);
    Route::get('excel', [NilaiKegiatanSkpiController::class , 'excel']);
});

// SKPI Identitas Mahasiswa Routes
Route::middleware(['CheckUserSession'])->prefix('skpi')->group(function () {
    Route::get('/', [SkpiController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [SkpiController::class , 'search']);
    Route::get('add', [SkpiController::class , 'add']);
    Route::get('view/{id}', [SkpiController::class , 'view']);
    Route::post('save/{save}', [SkpiController::class , 'save']);
    Route::post('delete', [SkpiController::class , 'delete']);
    Route::get('pdf', [SkpiController::class , 'pdf']);
    Route::post('load_form_identitas', [SkpiController::class , 'load_form_identitas']);
    Route::post('changemahasiswaprodi', [SkpiController::class , 'changemahasiswaprodi']);
    Route::get('loadinfo/{id}', [SkpiController::class , 'loadinfo']);
});

Route::middleware(['CheckUserSession'])->prefix('skpi/kategoriPencapaian')->group(function () {
    Route::get('/', [KategoriPencapaianController::class , 'index']);
    Route::match(['get', 'post'], 'searchKategoriCapaian/{offset?}', [KategoriPencapaianController::class , 'searchKategoriCapaian']);
    Route::get('addKategoriPencapaian', [KategoriPencapaianController::class , 'addKategoriPencapaian']);
    Route::get('viewKategoriPencapaian/{id}', [KategoriPencapaianController::class , 'viewKategoriPencapaian']);
    Route::post('saveKategoriPencapaian/{save}', [KategoriPencapaianController::class , 'saveKategoriPencapaian']);
    Route::post('deleteKategoriPencapaian', [KategoriPencapaianController::class , 'deleteKategoriPencapaian']);
});

Route::middleware(['CheckUserSession'])->prefix('skpi/pencapaian')->group(function () {
    Route::get('/', [PencapaianController::class , 'index']);
    Route::match(['get', 'post'], 'searchCapaian/{offset?}', [PencapaianController::class , 'searchCapaian']);
    Route::get('addPencapaian', [PencapaianController::class , 'addPencapaian']);
    Route::get('viewPencapaian/{id}', [PencapaianController::class , 'viewPencapaian']);
    Route::post('savePencapaian/{save}', [PencapaianController::class , 'savePencapaian']);
    Route::post('deletePencapaian', [PencapaianController::class , 'deletePencapaian']);
    Route::post('searchMahasiswa', [PencapaianController::class , 'searchMahasiswa']);
});

Route::middleware(['CheckUserSession'])->prefix('skpi/informasi')->group(function () {
    Route::get('/', [InformasiController::class , 'index']);
    Route::match(['get', 'post'], 'searchInformasi/{offset?}', [InformasiController::class , 'searchInformasi']);
    Route::get('addInformasi', [InformasiController::class , 'addInformasi']);
    Route::get('viewInformasi/{id}', [InformasiController::class , 'viewInformasi']);
    Route::post('saveInformasi/{save}', [InformasiController::class , 'saveInformasi']);
    Route::post('deleteInformasi', [InformasiController::class , 'deleteInformasi']);
});

Route::middleware(['CheckUserSession'])->prefix('skpi/approveInformasi')->group(function () {
    Route::get('/', [ApproveInformasiController::class , 'index']);
    Route::match(['get', 'post'], 'searchApproveInformasi/{offset?}', [ApproveInformasiController::class , 'searchApproveInformasi']);
    Route::post('lihatInformasi', [ApproveInformasiController::class , 'lihatInformasi']);
    Route::post('approveInformasi', [ApproveInformasiController::class , 'approveInformasi']);
    Route::post('rejectInformasi', [ApproveInformasiController::class , 'rejectInformasi']);
});

Route::middleware(['CheckUserSession'])->prefix('metode_pembayaran')->group(function () {
    Route::get('/', [MetodePembayaranController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [MetodePembayaranController::class , 'search']);
    Route::get('add', [MetodePembayaranController::class , 'add']);
    Route::get('view/{id}', [MetodePembayaranController::class , 'view']);
    Route::post('save/{save}', [MetodePembayaranController::class , 'save']);
    Route::post('delete', [MetodePembayaranController::class , 'delete']);
});

Route::middleware(['CheckUserSession'])->prefix('channel_pembayaran')->group(function () {
    Route::get('/', [ChannelPembayaranController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [ChannelPembayaranController::class , 'search']);
    Route::get('add', [ChannelPembayaranController::class , 'add']);
    Route::get('view/{id}', [ChannelPembayaranController::class , 'view']);
    Route::post('save/{save}', [ChannelPembayaranController::class , 'save']);
    Route::post('delete', [ChannelPembayaranController::class , 'delete']);
    Route::post('set_aktif', [ChannelPembayaranController::class , 'set_aktif']);
});

Route::middleware(['CheckUserSession'])->prefix('bank')->group(function () {
    Route::get('/', [BankController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [BankController::class , 'search']);
    Route::get('add', [BankController::class , 'add']);
    Route::get('view/{id}', [BankController::class , 'view']);
    Route::post('save/{save}', [BankController::class , 'save']);
    Route::post('delete', [BankController::class , 'delete']);
    Route::get('pdf', [BankController::class , 'pdf']);
    Route::get('excel', [BankController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('opsi_mahasiswa')->group(function () {
    Route::get('/', [OpsiMahasiswaController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [OpsiMahasiswaController::class , 'search']);
    Route::post('save', [OpsiMahasiswaController::class , 'save']);
    Route::get('excel', [OpsiMahasiswaController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('jenisbiaya')->group(function () {
    Route::get('/', [JenisBiayaController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [JenisBiayaController::class , 'search']);
    Route::get('add', [JenisBiayaController::class , 'add']);
    Route::get('view/{id}', [JenisBiayaController::class , 'view']);
    Route::post('save/{save}', [JenisBiayaController::class , 'save']);
    Route::post('delete', [JenisBiayaController::class , 'delete']);
    Route::post('load_list_komponen', [JenisBiayaController::class , 'load_list_komponen']);
    Route::get('lihat_detail/{JenisBiayaID}', [JenisBiayaController::class , 'lihat_detail']);
    Route::post('jenisbiaya_detail_delete', [JenisBiayaController::class , 'jenisbiaya_detail_delete']);
    Route::get('pdf', [JenisBiayaController::class , 'pdf']);
    Route::get('excel', [JenisBiayaController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('master_diskon')->group(function () {
    Route::get('/', [MasterDiskonController::class , 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [MasterDiskonController::class , 'search']);
    Route::get('add', [MasterDiskonController::class , 'add']);
    Route::get('view/{id}', [MasterDiskonController::class , 'view']);
    Route::post('save/{save}', [MasterDiskonController::class , 'save']);
    Route::post('delete', [MasterDiskonController::class , 'delete']);
});

Route::middleware(['CheckUserSession'])->prefix('biaya')->group(function () {
    Route::get('/', [BiayaController::class , 'index']);
    Route::post('search', [BiayaController::class , 'search']);
    Route::post('get_semester_biaya', [BiayaController::class , 'get_semester_biaya']);
    Route::post('save/{save}', [BiayaController::class , 'save']);
    Route::post('copy_biaya', [BiayaController::class , 'copy_biaya']);
    Route::post('reset', [BiayaController::class , 'reset']);
    Route::get('excel', [BiayaController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('paketsks')->group(function () {
    Route::get('/', [PaketSksController::class , 'index']);
    Route::post('search/{offset?}', [PaketSksController::class , 'search']);
    Route::get('add', [PaketSksController::class , 'add']);
    Route::get('view/{ProdiID}', [PaketSksController::class , 'view']);
    Route::post('save/{save}', [PaketSksController::class , 'save']);
});

Route::middleware(['CheckUserSession'])->prefix('setup_persentase_bayar')->group(function () {
    Route::get('/', [SetupPersentaseBayarController::class , 'index']);
    Route::post('search/{offset?}', [SetupPersentaseBayarController::class , 'search']);
    Route::get('add', [SetupPersentaseBayarController::class , 'add']);
    Route::get('view/{id}', [SetupPersentaseBayarController::class , 'view']);
    Route::post('save/{save}', [SetupPersentaseBayarController::class , 'save']);
    Route::post('delete', [SetupPersentaseBayarController::class , 'delete']);
    Route::get('pdf', [SetupPersentaseBayarController::class , 'pdf']);
    Route::get('excel', [SetupPersentaseBayarController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('setup_mahasiswa_diskon_sampai_lulus')->group(function () {
    Route::get('/', [SetupMahasiswaDiskonSampaiLulusController::class , 'index']);
    Route::post('search/{offset?}', [SetupMahasiswaDiskonSampaiLulusController::class , 'search']);
    Route::get('add', [SetupMahasiswaDiskonSampaiLulusController::class , 'add']);
    Route::get('view/{id}', [SetupMahasiswaDiskonSampaiLulusController::class , 'view']);
    Route::post('save/{save}', [SetupMahasiswaDiskonSampaiLulusController::class , 'save']);
    Route::post('save_alone/{save}', [SetupMahasiswaDiskonSampaiLulusController::class , 'save_alone']);
    Route::post('delete', [SetupMahasiswaDiskonSampaiLulusController::class , 'delete']);
    Route::get('aktifkan/{id}', [SetupMahasiswaDiskonSampaiLulusController::class , 'aktifkan']);
    Route::post('filtermhs', [SetupMahasiswaDiskonSampaiLulusController::class , 'filtermhs']);
    Route::post('filtermhscalon', [SetupMahasiswaDiskonSampaiLulusController::class , 'filtermhscalon']);
    Route::post('changenominal', [SetupMahasiswaDiskonSampaiLulusController::class , 'changenominal']);
    Route::get('excel', [SetupMahasiswaDiskonSampaiLulusController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('setup_duedate_pembayaran')->group(function () {
    Route::get('/', [SetupDuedatePembayaranController::class , 'index']);
    Route::post('search/{offset?}', [SetupDuedatePembayaranController::class , 'search']);
    Route::get('add', [SetupDuedatePembayaranController::class , 'add']);
    Route::get('view/{id}', [SetupDuedatePembayaranController::class , 'view']);
    Route::post('save/{save}', [SetupDuedatePembayaranController::class , 'save']);
    Route::post('delete', [SetupDuedatePembayaranController::class , 'delete']);
    Route::get('pdf', [SetupDuedatePembayaranController::class , 'pdf']);
    Route::get('excel', [SetupDuedatePembayaranController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('setting_termin_pembayaran_mahasiswa')->group(function () {
    Route::get('/', [SettingTerminPembayaranMahasiswaController::class , 'index']);
    Route::post('content_opsi', [SettingTerminPembayaranMahasiswaController::class , 'content_opsi']);
    Route::post('set_opsi', [SettingTerminPembayaranMahasiswaController::class , 'set_opsi']);
});

Route::middleware(['CheckUserSession'])->prefix('setup_harga_biaya_variable')->group(function () {
    Route::get('/', [SetupHargaBiayaVariableController::class , 'index']);
    Route::post('search/{offset?}', [SetupHargaBiayaVariableController::class , 'search']);
    Route::get('add', [SetupHargaBiayaVariableController::class , 'add']);
    Route::get('view/{id}', [SetupHargaBiayaVariableController::class , 'view']);
    Route::post('save/{save}', [SetupHargaBiayaVariableController::class , 'save']);
    Route::post('delete', [SetupHargaBiayaVariableController::class , 'delete']);
    Route::get('pdf', [SetupHargaBiayaVariableController::class , 'pdf']);
    Route::get('excel', [SetupHargaBiayaVariableController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('setup_minimal_bayar_generate_nim')->group(function () {
    Route::get('/', [SetupMinimalBayarGenerateNimController::class , 'index']);
    Route::post('search/{offset?}', [SetupMinimalBayarGenerateNimController::class , 'search']);
    Route::get('add', [SetupMinimalBayarGenerateNimController::class , 'add']);
    Route::get('view/{id}', [SetupMinimalBayarGenerateNimController::class , 'view']);
    Route::post('save/{save}', [SetupMinimalBayarGenerateNimController::class , 'save']);
    Route::post('delete', [SetupMinimalBayarGenerateNimController::class , 'delete']);
    Route::get('pdf', [SetupMinimalBayarGenerateNimController::class , 'pdf']);
    Route::get('excel', [SetupMinimalBayarGenerateNimController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('setup_denda')->group(function () {
    Route::get('/', [SetupDendaController::class , 'index']);
    Route::post('search/{offset?}', [SetupDendaController::class , 'search']);
    Route::get('add', [SetupDendaController::class , 'add']);
    Route::get('view/{id}', [SetupDendaController::class , 'view']);
    Route::post('save/{save}', [SetupDendaController::class , 'save']);
    Route::post('delete', [SetupDendaController::class , 'delete']);
    Route::get('pdf', [SetupDendaController::class , 'pdf']);
    Route::get('excel', [SetupDendaController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('setup_ukt')->group(function () {
    Route::get('/', [SetupUktController::class , 'index']);
    Route::post('search/{offset?}', [SetupUktController::class , 'search']);
    Route::get('add', [SetupUktController::class , 'add']);
    Route::get('view/{id}', [SetupUktController::class , 'view']);
    Route::post('save/{save}', [SetupUktController::class , 'save']);
    Route::post('delete', [SetupUktController::class , 'delete']);
    Route::get('pdf', [SetupUktController::class , 'pdf']);
    Route::get('excel', [SetupUktController::class , 'excel']);
});

Route::middleware(['CheckUserSession'])->prefix('setup_mode_pembayaran_student')->group(function () {
    Route::get('/', [SetupModePembayaranStudentController::class , 'index'])->name('setup_mode_pembayaran_student.index');
    Route::post('set_publish_all/{save?}', [SetupModePembayaranStudentController::class , 'set_publish_all']);
});

Route::middleware(['CheckUserSession'])->prefix('setting_duedate_pembayaran_keseluruhan')->group(function () {
    Route::get('/', [SettingDuedatePembayaranKeseluruhanController::class , 'index']);
    Route::post('content_opsi', [SettingDuedatePembayaranKeseluruhanController::class , 'content_opsi']);
    Route::post('set_opsi', [SettingDuedatePembayaranKeseluruhanController::class , 'set_opsi']);
});

Route::middleware(['CheckUserSession'])->prefix('mahasiswa_diskon')->group(function () {
    Route::get('/', [MahasiswaDiskonController::class , 'index']);
    Route::post('search/{offset?}', [MahasiswaDiskonController::class , 'search']);
    Route::get('add', [MahasiswaDiskonController::class , 'add']);
    Route::get('view/{id}', [MahasiswaDiskonController::class , 'view']);
    Route::get('edit/{id}', [MahasiswaDiskonController::class , 'edit']);
    Route::post('update', [MahasiswaDiskonController::class , 'update']);
    Route::post('save/{save}', [MahasiswaDiskonController::class , 'save']);
    Route::post('delete', [MahasiswaDiskonController::class , 'delete']);
    Route::post('changenominal', [MahasiswaDiskonController::class , 'changenominal']);
    Route::post('filtermhs', [MahasiswaDiskonController::class , 'filtermhs']);
    Route::post('set_diskon/{id}', [MahasiswaDiskonController::class , 'set_diskon']);
    Route::post('unset_diskon/{id}', [MahasiswaDiskonController::class , 'unset_diskon']);
    Route::get('lihat_detail/{id}', [MahasiswaDiskonController::class , 'lihat_detail']);
    Route::post('aktivkan/{id}', [MahasiswaDiskonController::class , 'aktivkan']);
});

// Setup Minimal Bayar Cicilan Bebas Student Routes
Route::middleware(['CheckUserSession'])->prefix('setup_minimal_bayar_cicilan_bebas_student')->group(function () {
    Route::get('/', [SetupMinimalBayarCicilanBebasStudentController::class , 'index']);
    Route::post('search/{offset?}', [SetupMinimalBayarCicilanBebasStudentController::class , 'search']);
    Route::get('add', [SetupMinimalBayarCicilanBebasStudentController::class , 'add']);
    Route::get('view/{id}', [SetupMinimalBayarCicilanBebasStudentController::class , 'view']);
    Route::post('save/{save}', [SetupMinimalBayarCicilanBebasStudentController::class , 'save']);
    Route::post('delete', [SetupMinimalBayarCicilanBebasStudentController::class , 'delete']);
    Route::get('pdf', [SetupMinimalBayarCicilanBebasStudentController::class , 'pdf']);
    Route::get('excel', [SetupMinimalBayarCicilanBebasStudentController::class , 'excel']);
});

// Mahasiswa Diskon Telat Routes
Route::middleware(['CheckUserSession'])->prefix('mahasiswa_diskon_telat')->group(function () {
    Route::get('/', [MahasiswaDiskonTelatController::class , 'index']);
    Route::post('search/{offset?}', [MahasiswaDiskonTelatController::class , 'search']);
    Route::get('add', [MahasiswaDiskonTelatController::class , 'add']);
    Route::get('view/{id}', [MahasiswaDiskonTelatController::class , 'view']);
    Route::get('edit/{id}', [MahasiswaDiskonTelatController::class , 'edit']);
    Route::post('update', [MahasiswaDiskonTelatController::class , 'update']);
    Route::post('save/{save}', [MahasiswaDiskonTelatController::class , 'save']);
    Route::post('delete', [MahasiswaDiskonTelatController::class , 'delete']);
    Route::post('changenominal', [MahasiswaDiskonTelatController::class , 'changenominal']);
    Route::post('filtermhs', [MahasiswaDiskonTelatController::class , 'filtermhs']);
    Route::post('set_diskon/{id}', [MahasiswaDiskonTelatController::class , 'set_diskon']);
    Route::post('unset_diskon/{id}', [MahasiswaDiskonTelatController::class , 'unset_diskon']);
    Route::get('lihat_detail/{id}', [MahasiswaDiskonTelatController::class , 'lihat_detail']);
    Route::post('aktifkan/{id}', [MahasiswaDiskonTelatController::class , 'aktifkan']);
});

// Setting Biaya Lainnya Routes
Route::middleware(['CheckUserSession'])->prefix('setting_biaya_lainnya')->group(function () {
    Route::get('/', [SettingBiayaLainnyaController::class , 'index']);
    Route::post('search/{offset?}', [SettingBiayaLainnyaController::class , 'search']);
    Route::get('add', [SettingBiayaLainnyaController::class , 'add']);
    Route::get('view/{id}', [SettingBiayaLainnyaController::class , 'view']);
    Route::post('save/{save}', [SettingBiayaLainnyaController::class , 'save']);
    Route::post('delete', [SettingBiayaLainnyaController::class , 'delete']);
    Route::get('pdf', [SettingBiayaLainnyaController::class , 'pdf']);
    Route::get('excel', [SettingBiayaLainnyaController::class , 'excel']);
});

// List Tidak KRS Routes
Route::middleware(['CheckUserSession'])->prefix('list_tidak_krs')->group(function () {
    Route::get('/', [ListTidakKrsController::class , 'index']);
    Route::post('search/{offset?}', [ListTidakKrsController::class , 'search']);
    Route::post('update_data', [ListTidakKrsController::class , 'update_data']);
    Route::get('pdf', [ListTidakKrsController::class , 'pdf']);
    Route::get('excel', [ListTidakKrsController::class , 'excel']);
    Route::post('set_status', [ListTidakKrsController::class , 'set_status']);
    Route::post('set_statusall/{status?}', [ListTidakKrsController::class , 'set_statusall']);
});

// Generate Tagihan Routes
Route::middleware(['CheckUserSession'])->prefix('generate_tagihan')->group(function () {
    Route::get('/', [GenerateTagihanController::class , 'index']);
    Route::post('searchMahasiswa', [GenerateTagihanController::class , 'searchMahasiswa']);
    Route::post('changeAngkatan', [GenerateTagihanController::class , 'changeAngkatan']);
    Route::post('content_biaya', [GenerateTagihanController::class , 'content_biaya']);
    Route::post('generate_tagihan', [GenerateTagihanController::class , 'generate_tagihan']);
    Route::get('excel', [GenerateTagihanController::class , 'excel']);
});

// Hasil Studi
Route::prefix('hasilstudi')->middleware(['CheckUserSession'])->group(function () {
    Route::get('/', [HasilStudiController::class , 'index'])->name('hasilstudi.index');
    Route::match(['get', 'post'], 'search/{offset?}', [HasilStudiController::class , 'search'])->name('hasilstudi.search');
    Route::post('simpanipk', [HasilStudiController::class , 'simpanipk'])->name('hasilstudi.simpanipk');
    Route::get('filterALLPDF/{TahunID}/{ProdiID}/{ProgramID}/{Jenis}/{MhswID?}/{JmlCheck?}', [HasilStudiController::class , 'filterALLPDF'])->name('hasilstudi.filterALLPDF');
    Route::get('filterPDF', [HasilStudiController::class , 'filterPDF'])->name('hasilstudi.filterPDF');
    Route::get('filterPDFKRS', [HasilStudiController::class , 'filterPDFKRS'])->name('hasilstudi.filterPDFKRS');
    Route::get('add', [HasilStudiController::class , 'add'])->name('hasilstudi.add');
    Route::get('view/{id}', [HasilStudiController::class , 'view'])->name('hasilstudi.view');
    Route::post('save/{save}', [HasilStudiController::class , 'save'])->name('hasilstudi.save');
    Route::post('delete', [HasilStudiController::class , 'delete'])->name('hasilstudi.delete');
    Route::get('pdf', [HasilStudiController::class , 'pdf'])->name('hasilstudi.pdf');
    Route::get('excel', [HasilStudiController::class , 'excel'])->name('hasilstudi.excel');
    Route::get('laporanIpsIpk', [HasilStudiController::class , 'laporanIpsIpk'])->name('hasilstudi.laporanIpsIpk');
    Route::get('laporanIpsIpkAngkatan', [HasilStudiController::class , 'laporanIpsIpkAngkatan'])->name('hasilstudi.laporanIpsIpkAngkatan');
    Route::get('LaporanIpsDanIpkAngkatan', [HasilStudiController::class , 'LaporanIpsDanIpkAngkatan'])->name('hasilstudi.LaporanIpsDanIpkAngkatan');
    Route::post('filterIpk', [HasilStudiController::class , 'filterIpk'])->name('hasilstudi.filterIpk');
    Route::get('cetak', [HasilStudiController::class , 'cetak'])->name('hasilstudi.cetak');
    Route::post('filterIpkAngkatan', [HasilStudiController::class , 'filterIpkAngkatan'])->name('hasilstudi.filterIpkAngkatan');
    Route::get('cetakPerAngkatan', [HasilStudiController::class , 'cetakPerAngkatan'])->name('hasilstudi.cetakPerAngkatan');
    Route::get('devmode', [HasilStudiController::class , 'devmode'])->name('hasilstudi.devmode');
    Route::get('LaporanIpsDanIpk',[HasilStudiController::class , 'LaporanIpsDanIpk'])->name('hasilstudi.devmode');
});

// Laporan Status Input Nilai
Route::prefix('laporan_status_input_nilai')->middleware(['CheckUserSession'])->group(function () {
    Route::get('/', [LaporanStatusInputNilaiController::class, 'index'])->name('laporan_status_input_nilai.index');
    Route::get('LaporanStatusInputNilai', [LaporanStatusInputNilaiController::class, 'LaporanStatusInputNilai'])->name('laporan_status_input_nilai.LaporanStatusInputNilai');
    Route::match(['get', 'post'], 'search/{offset?}', [LaporanStatusInputNilaiController::class, 'search'])->name('laporan_status_input_nilai.search');
    Route::get('add', [LaporanStatusInputNilaiController::class, 'add'])->name('laporan_status_input_nilai.add');
    Route::get('view/{id}', [LaporanStatusInputNilaiController::class, 'view'])->name('laporan_status_input_nilai.view');
    Route::post('save/{save}', [LaporanStatusInputNilaiController::class, 'save'])->name('laporan_status_input_nilai.save');
    Route::post('delete', [LaporanStatusInputNilaiController::class, 'delete'])->name('laporan_status_input_nilai.delete');
    Route::get('pdf', [LaporanStatusInputNilaiController::class, 'pdf'])->name('laporan_status_input_nilai.pdf');
    Route::get('excel', [LaporanStatusInputNilaiController::class, 'excel'])->name('laporan_status_input_nilai.excel');
});

// Rekap Nilai
Route::prefix('rekapnilai')->middleware(['CheckUserSession'])->group(function () {
    Route::get('/', [RekapNilaiController::class, 'index'])->name('rekapnilai.index');
    Route::get('RekapNilai', [RekapNilaiController::class, 'RekapNilai'])->name('rekapnilai.RekapNilai');
    Route::post('search', [RekapNilaiController::class, 'search'])->name('rekapnilai.search');
    Route::get('excel', [RekapNilaiController::class, 'excel'])->name('rekapnilai.excel');
});

// Konversi
Route::prefix('konversi')->middleware(['CheckUserSession'])->group(function () {
    Route::get('/', [KonversiController::class, 'index'])->name('konversi.index');
    Route::get('Konversi', [KonversiController::class, 'Konversi'])->name('konversi.Konversi');
    Route::match(['get', 'post'], 'search/{offset?}', [KonversiController::class, 'search'])->name('konversi.search');
    Route::get('add', [KonversiController::class, 'add'])->name('konversi.add');
    Route::get('add_internal/{type?}', [KonversiController::class, 'add_internal'])->name('konversi.add_internal');
    Route::get('view/{id}', [KonversiController::class, 'view'])->name('konversi.view');
    Route::post('save/{save}', [KonversiController::class, 'save'])->name('konversi.save');
    Route::post('json_konversi', [KonversiController::class, 'json_konversi'])->name('konversi.json_konversi');
    Route::post('json_nilai', [KonversiController::class, 'json_nilai'])->name('konversi.json_nilai');
    Route::post('delete', [KonversiController::class, 'delete'])->name('konversi.delete');
    Route::post('delete_detail', [KonversiController::class, 'delete_detail'])->name('konversi.delete_detail');
    Route::post('genKonversi', [KonversiController::class, 'genKonversi'])->name('konversi.genKonversi');
    Route::post('batalKonversi', [KonversiController::class, 'batalKonversi'])->name('konversi.batalKonversi');
    Route::post('konversi_all', [KonversiController::class, 'konversi_all'])->name('konversi.konversi_all');
    Route::get('pdf', [KonversiController::class, 'pdf'])->name('konversi.pdf');
    Route::get('excel', [KonversiController::class, 'excel'])->name('konversi.excel');
    Route::get('cetakNilaiKonversi/{id}', [KonversiController::class, 'cetakNilaiKonversi'])->name('konversi.cetakNilaiKonversi');
    Route::post('save_internal/{save?}', [KonversiController::class, 'save_internal'])->name('konversi.save_internal');
    Route::post('uploadExcel', [KonversiController::class, 'uploadExcel'])->name('konversi.uploadExcel');
    Route::get('excelNilai', [KonversiController::class, 'excelNilai'])->name('konversi.excelNilai');
    Route::post('json_mk', [KonversiController::class, 'json_mk'])->name('konversi.json_mk');
    Route::post('cek_npm', [KonversiController::class, 'cek_npm'])->name('konversi.cek_npm');
    Route::post('change_bobot', [KonversiController::class, 'change_bobot'])->name('konversi.change_bobot');
    Route::post('nilai_bobot', [KonversiController::class, 'nilai_bobot'])->name('konversi.nilai_bobot');
    Route::post('get_param_mahasiswa', [KonversiController::class, 'get_param_mahasiswa'])->name('konversi.get_param_mahasiswa');
    Route::post('changeSemester', [KonversiController::class, 'changeSemester'])->name('konversi.changeSemester');
    Route::post('get_last_npm', [KonversiController::class, 'get_last_npm'])->name('konversi.get_last_npm');
    Route::get('toolsGenKonversi', [KonversiController::class, 'toolsGenKonversi'])->name('konversi.toolsGenKonversi');
});

// Perkembangan Akademik
Route::prefix('perkembanganakademik')->middleware(['CheckUserSession'])->group(function () {
    Route::get('/', [PerkembanganAkademikController::class, 'index'])->name('perkembanganakademik.index');
    Route::match(['get', 'post'], 'search/{offset?}', [PerkembanganAkademikController::class, 'search'])->name('perkembanganakademik.search');
    Route::get('loadinfo/{id}', [PerkembanganAkademikController::class, 'loadinfo']);
    Route::get('cetak/{mhswId}/{jenis?}/{bahasa?}', [PerkembanganAkademikController::class, 'cetak']);
    Route::get('pdf_kelas', [PerkembanganAkademikController::class, 'pdf_kelas']);
});

// Rencana Studi / FRS
Route::prefix('rencanastudi')->middleware(['CheckUserSession'])->group(function () {
    Route::get('loadtranskrip', [RencanaStudiController::class, 'loadtranskrip'])->name('rencanastudi.loadtranskrip');
    Route::match(['get', 'post'], 'searchtranskrip/{offset?}', [RencanaStudiController::class, 'searchtranskrip'])->name('rencanastudi.searchtranskrip');
    Route::get('loadinfo/{id}', [RencanaStudiController::class, 'loadinfo']);
    Route::post('no_transkrip', [RencanaStudiController::class, 'no_transkrip']);
    Route::get('cetak/{mhswId}/{jenis?}/{bahasa?}', [RencanaStudiController::class , 'cetak']);
    Route::get('perkembangan/{mhswId}', [RencanaStudiController::class , 'perkembangan']);
    });

    // Publish Nilai UAS
    Route::prefix('publish_nilai_uas')->middleware(['CheckUserSession'])->group(function () {
    Route::get('/', [PublishNilaiUasController::class, 'index'])->name('publish_nilai_uas.index');
    Route::match(['get', 'post'], 'search/{offset?}', [PublishNilaiUasController::class, 'search'])->name('publish_nilai_uas.search');
    Route::get('detail_publish_nilai_uas_mhsw/{JadwalID}/{kelasID?}/{dosenID?}', [PublishNilaiUasController::class, 'detail_publish_nilai_uas_mhsw']);
    Route::post('publish_all_uas', [PublishNilaiUasController::class, 'publish_all_uas']);
    Route::get('devmode', [PublishNilaiUasController::class, 'devmode']);
    });


// Transkrip Mahasiswa Routes
Route::middleware(['CheckUserSession'])->prefix('transkripmahasiswa')->group(function () {
    Route::get('/', [TranskripMahasiswaController::class, 'index']);
    Route::match(['get', 'post'], 'search/{offset?}', [TranskripMahasiswaController::class, 'search']);
    Route::get('loadinfo/{id}', [TranskripMahasiswaController::class, 'loadinfo']);
    Route::get('edit', [TranskripMahasiswaController::class, 'edit']);
    Route::get('edit_transkrip/{mhswID}', [TranskripMahasiswaController::class, 'edit_transkrip']);
    Route::get('edit_khs/{mhswID}', [TranskripMahasiswaController::class, 'edit_khs']);
    Route::post('search_edit_khs', [TranskripMahasiswaController::class, 'search_edit_khs']);
    Route::get('add_khs', [TranskripMahasiswaController::class, 'add_khs']);
    Route::post('getTranskrip', [TranskripMahasiswaController::class, 'getTranskrip']);
    Route::post('gen_khs', [TranskripMahasiswaController::class, 'gen_khs']);
    Route::post('save', [TranskripMahasiswaController::class, 'save']);
    Route::post('save_khs', [TranskripMahasiswaController::class, 'save_khs']);
    Route::post('update', [TranskripMahasiswaController::class, 'update']);
    Route::post('saverevisinilai', [TranskripMahasiswaController::class, 'saverevisinilai']);
    Route::post('saverevisinilaikhs', [TranskripMahasiswaController::class, 'saverevisinilaikhs']);
    Route::post('delete', [TranskripMahasiswaController::class, 'delete']);
    Route::post('deleteDataKHS', [TranskripMahasiswaController::class, 'deleteDataKHS']);
    Route::get('cetak/{mhswID}/{jenis?}/{bahasa?}', [TranskripMahasiswaController::class, 'cetak']);
    Route::get('excel/{mhswID}/{jenis?}/{bahasa?}', [TranskripMahasiswaController::class, 'excel']);
    Route::get('cetak_all', [TranskripMahasiswaController::class, 'cetak_all']);
    Route::get('add_upload_nomor', [TranskripMahasiswaController::class, 'add_upload_nomor']);
    Route::get('template_upload_nomor', [TranskripMahasiswaController::class, 'template_upload_nomor']);
    Route::post('upload_excel_nomor', [TranskripMahasiswaController::class, 'upload_excel_nomor']);
});

// AJAX Helper Routes
Route::middleware(['CheckUserSession'])->group(function () {
    Route::post('programstudi/changeprodi', [ProgramStudiController::class, 'changeprodi']);
    Route::post('kurikulum/onchange', [GeneralAjaxController::class, 'changekurikulum']);
    Route::post('detailkurikulum/changekonsentrasi', [GeneralAjaxController::class, 'changekonsentrasi']);
    Route::post('kelas/changekelas', [GeneralAjaxController::class, 'changekelas']);
    Route::post('jadwal/changesemester', [GeneralAjaxController::class, 'changesemester']);
});

// Posting Tagihan Routes
Route::middleware(['CheckUserSession'])->prefix('posting_tagihan')->group(function () {
    Route::get('/', [App\Http\Controllers\PostingTagihanController::class, 'index'])->name('posting_tagihan.index');
    Route::post('search/{offset?}', [App\Http\Controllers\PostingTagihanController::class, 'search'])->name('posting_tagihan.search');
    Route::post('posting', [App\Http\Controllers\PostingTagihanController::class, 'posting'])->name('posting_tagihan.posting');
    Route::post('posting_all', [App\Http\Controllers\PostingTagihanController::class, 'postingAll'])->name('posting_tagihan.postingAll');
    Route::post('draft_all', [App\Http\Controllers\PostingTagihanController::class, 'draftAll'])->name('posting_tagihan.draftAll');
});

// Approval Rekomendasi Batal Rencana Studi - Prodi Routes
Route::middleware(['CheckUserSession'])->prefix('approval_rekomendasi_batal_rencanastudi/prodi')->group(function () {
    Route::get('/', [App\Http\Controllers\ApprovalRekomendasiBatalRencanaStudiProdiController::class, 'index'])->name('approval_rekomendasi_batal_rencanastudi_prodi.index');
    Route::post('search/{offset?}', [App\Http\Controllers\ApprovalRekomendasiBatalRencanaStudiProdiController::class, 'search'])->name('approval_rekomendasi_batal_rencanastudi_prodi.search');
    Route::post('rekomendasi_prodi', [App\Http\Controllers\ApprovalRekomendasiBatalRencanaStudiProdiController::class, 'rekomendasiProdi'])->name('approval_rekomendasi_batal_rencanastudi_prodi.rekomendasiProdi');
    Route::post('rekomendasi_prodi_all', [App\Http\Controllers\ApprovalRekomendasiBatalRencanaStudiProdiController::class, 'rekomendasiProdiAll'])->name('approval_rekomendasi_batal_rencanastudi_prodi.rekomendasiProdiAll');
    Route::post('save_catatan', [App\Http\Controllers\ApprovalRekomendasiBatalRencanaStudiProdiController::class, 'saveCatatan'])->name('approval_rekomendasi_batal_rencanastudi_prodi.saveCatatan');
    Route::post('changeKurikulum', [App\Http\Controllers\ApprovalRekomendasiBatalRencanaStudiProdiController::class, 'changeKurikulum'])->name('approval_rekomendasi_batal_rencanastudi_prodi.changeKurikulum');
    Route::post('changeTahunMasuk', [App\Http\Controllers\ApprovalRekomendasiBatalRencanaStudiProdiController::class, 'changeTahunMasuk'])->name('approval_rekomendasi_batal_rencanastudi_prodi.changeTahunMasuk');
    Route::post('changeKelas', [App\Http\Controllers\ApprovalRekomendasiBatalRencanaStudiProdiController::class, 'changeKelas'])->name('approval_rekomendasi_batal_rencanastudi_prodi.changeKelas');
    Route::get('getDataNilai', [App\Http\Controllers\ApprovalRekomendasiBatalRencanaStudiProdiController::class, 'getDataNilai'])->name('approval_rekomendasi_batal_rencanastudi_prodi.getDataNilai');
});

// Approval Rekomendasi Batal Rencana Studi - Keuangan Routes
Route::middleware(['CheckUserSession'])->prefix('approval_rekomendasi_batal_rencanastudi/keuangan')->group(function () {
    Route::get('/', [App\Http\Controllers\ApprovalRekomendasiBatalRencanaStudiKeuanganController::class, 'index'])->name('approval_rekomendasi_batal_rencanastudi_keuangan.index');
    Route::post('search/{offset?}', [App\Http\Controllers\ApprovalRekomendasiBatalRencanaStudiKeuanganController::class, 'search'])->name('approval_rekomendasi_batal_rencanastudi_keuangan.search');
    Route::post('rekomendasi_keuangan', [App\Http\Controllers\ApprovalRekomendasiBatalRencanaStudiKeuanganController::class, 'rekomendasiKeuangan'])->name('approval_rekomendasi_batal_rencanastudi_keuangan.rekomendasiKeuangan');
    Route::post('rekomendasi_keuangan_all', [App\Http\Controllers\ApprovalRekomendasiBatalRencanaStudiKeuanganController::class, 'rekomendasiKeuanganAll'])->name('approval_rekomendasi_batal_rencanastudi_keuangan.rekomendasiKeuanganAll');
    Route::post('save_catatan', [App\Http\Controllers\ApprovalRekomendasiBatalRencanaStudiKeuanganController::class, 'saveCatatan'])->name('approval_rekomendasi_batal_rencanastudi_keuangan.saveCatatan');
    Route::post('changeKurikulum', [App\Http\Controllers\ApprovalRekomendasiBatalRencanaStudiKeuanganController::class, 'changeKurikulum'])->name('approval_rekomendasi_batal_rencanastudi_keuangan.changeKurikulum');
    Route::post('changeTahunMasuk', [App\Http\Controllers\ApprovalRekomendasiBatalRencanaStudiKeuanganController::class, 'changeTahunMasuk'])->name('approval_rekomendasi_batal_rencanastudi_keuangan.changeTahunMasuk');
    Route::get('getDataNilai', [App\Http\Controllers\ApprovalRekomendasiBatalRencanaStudiKeuanganController::class, 'getDataNilai'])->name('approval_rekomendasi_batal_rencanastudi_keuangan.getDataNilai');
});

// Batal Tagihan Routes
Route::middleware(['CheckUserSession'])->prefix('batal_tagihan')->group(function () {
    Route::get('/', [App\Http\Controllers\BatalTagihanController::class, 'index'])->name('batal_tagihan.index');
    Route::post('search_tagihan/{offset?}', [App\Http\Controllers\BatalTagihanController::class, 'searchTagihan'])->name('batal_tagihan.searchTagihan');
    Route::post('delete', [App\Http\Controllers\BatalTagihanController::class, 'delete'])->name('batal_tagihan.delete');
    Route::post('changeprodi', [App\Http\Controllers\BatalTagihanController::class, 'changeProdi'])->name('batal_tagihan.changeProdi');
    Route::post('changeprogram', [App\Http\Controllers\BatalTagihanController::class, 'changeProgram'])->name('batal_tagihan.changeProgram');
    Route::post('changeangkatan', [App\Http\Controllers\BatalTagihanController::class, 'changeAngkatan'])->name('batal_tagihan.changeAngkatan');
    Route::post('changemahasiswa', [App\Http\Controllers\BatalTagihanController::class, 'changeMahasiswa'])->name('batal_tagihan.changeMahasiswa');
});

// Deposit Mahasiswa Routes
Route::middleware(['CheckUserSession'])->prefix('deposit_mahasiswa')->group(function () {
    Route::get('/', [App\Http\Controllers\DepositMahasiswaController::class, 'index'])->name('deposit_mahasiswa.index');
    Route::post('search/{offset?}', [App\Http\Controllers\DepositMahasiswaController::class, 'search'])->name('deposit_mahasiswa.search');
    Route::get('add', [App\Http\Controllers\DepositMahasiswaController::class, 'add'])->name('deposit_mahasiswa.add');
    Route::get('view/{id}', [App\Http\Controllers\DepositMahasiswaController::class, 'view'])->name('deposit_mahasiswa.view');
    Route::get('history_deposit/{id}', [App\Http\Controllers\DepositMahasiswaController::class, 'historyDeposit'])->name('deposit_mahasiswa.historyDeposit');
    Route::post('save/{save}', [App\Http\Controllers\DepositMahasiswaController::class, 'save'])->name('deposit_mahasiswa.save');
    Route::post('delete', [App\Http\Controllers\DepositMahasiswaController::class, 'delete'])->name('deposit_mahasiswa.delete');
    Route::get('json_mahasiswa', [App\Http\Controllers\DepositMahasiswaController::class, 'jsonMahasiswa'])->name('deposit_mahasiswa.jsonMahasiswa');
    Route::get('excel/{params?}', [App\Http\Controllers\DepositMahasiswaController::class, 'excel'])->name('deposit_mahasiswa.excel');
});

// Input Tagihan Manual Routes
Route::middleware(['CheckUserSession'])->prefix('input_tagihan_manual')->group(function () {
    Route::get('/', [App\Http\Controllers\InputTagihanManualController::class, 'index'])->name('input_tagihan_manual.index');
    Route::post('searchMahasiswa', [App\Http\Controllers\InputTagihanManualController::class, 'searchMahasiswa'])->name('input_tagihan_manual.searchMahasiswa');
    Route::post('changeAngkatan', [App\Http\Controllers\InputTagihanManualController::class, 'changeAngkatan'])->name('input_tagihan_manual.changeAngkatan');
    Route::post('content_biaya', [App\Http\Controllers\InputTagihanManualController::class, 'contentBiaya'])->name('input_tagihan_manual.contentBiaya');
    Route::post('input_tagihan_manual', [App\Http\Controllers\InputTagihanManualController::class, 'inputTagihanManual'])->name('input_tagihan_manual.inputTagihanManual');
    Route::get('excel', [App\Http\Controllers\InputTagihanManualController::class, 'excel'])->name('input_tagihan_manual.excel');
});

// Publish Nilai UTS Routes
Route::middleware(['CheckUserSession'])->prefix('publish_nilai_uts')->group(function () {
    Route::get('/', [App\Http\Controllers\PublishNilaiUtsController::class, 'index'])->name('publish_nilai_uts.index');
    Route::match(['get', 'post'], 'search_publish_nilai_uts_mengajar_dosen/{offset?}', [App\Http\Controllers\PublishNilaiUtsController::class, 'search_publish_nilai_uts_mengajar_dosen'])->name('publish_nilai_uts.search');
    Route::get('detail_publish_nilai_uts_mhsw/{jadwalID}/{kelasID?}/{dosenID?}', [App\Http\Controllers\PublishNilaiUtsController::class, 'detail_publish_nilai_uts_mhsw'])->name('publish_nilai_uts.detail');
    Route::post('publish_all_uts', [App\Http\Controllers\PublishNilaiUtsController::class, 'publish_all_uts'])->name('publish_nilai_uts.publish_all');
});

// Generate Denda Routes
Route::middleware(['CheckUserSession'])->prefix('generate_denda')->group(function () {
    Route::get('/', [App\Http\Controllers\GenerateDendaController::class, 'index'])->name('generate_denda.index');
    Route::post('search/{offset?}', [App\Http\Controllers\GenerateDendaController::class, 'search'])->name('generate_denda.search');
    Route::post('posting', [App\Http\Controllers\GenerateDendaController::class, 'posting'])->name('generate_denda.posting');
    Route::post('posting_all', [App\Http\Controllers\GenerateDendaController::class, 'postingAll'])->name('generate_denda.postingAll');
});

// Lihat Catatan KRS Tidak Aktif Routes
Route::middleware(['CheckUserSession'])->prefix('lihat_catatan_krs_tidak_aktif')->group(function () {
    Route::get('/', [App\Http\Controllers\LihatCatatanKrsTidakAktifController::class, 'index'])->name('lihat_catatan_krs_tidak_aktif.index');
    Route::post('search/{offset?}', [App\Http\Controllers\LihatCatatanKrsTidakAktifController::class, 'search'])->name('lihat_catatan_krs_tidak_aktif.search');
    Route::post('approve/{status}', [App\Http\Controllers\LihatCatatanKrsTidakAktifController::class, 'approve'])->name('lihat_catatan_krs_tidak_aktif.approve');
    Route::get('excel', [App\Http\Controllers\LihatCatatanKrsTidakAktifController::class, 'excel'])->name('lihat_catatan_krs_tidak_aktif.excel');
});
// Nilai / Input Nilai Routes
Route::middleware(['CheckUserSession'])->prefix('nilai')->group(function () {
    Route::get('/', [NilaiController::class, 'index'])->name('nilai.index');
    Route::post('search', [NilaiController::class, 'search'])->name('nilai.search');
    Route::post('filter_peserta', [NilaiController::class, 'filter_peserta'])->name('nilai.filter_peserta');
    Route::get('add/{jadwalID?}', [NilaiController::class, 'add'])->name('nilai.add');
    Route::post('saveBobot', [NilaiController::class, 'saveBobot'])->name('nilai.saveBobot');
    Route::post('saveNilai', [NilaiController::class, 'saveNilai'])->name('nilai.saveNilai');
});

// Keterangan Status Mahasiswa Routes
Route::middleware(['CheckUserSession'])->prefix('keterangan_status_mahasiswa')->group(function () {
    Route::get('/', [KeteranganStatusMahasiswaController::class, 'index'])->name('keterangan_status_mahasiswa.index');
    Route::post('search', [KeteranganStatusMahasiswaController::class, 'search'])->name('keterangan_status_mahasiswa.search');
    Route::post('changemhsw', [KeteranganStatusMahasiswaController::class, 'changemhsw'])->name('keterangan_status_mahasiswa.changemhsw');
    Route::post('changestatus', [KeteranganStatusMahasiswaController::class, 'changestatus'])->name('keterangan_status_mahasiswa.changestatus');
    Route::get('add', [KeteranganStatusMahasiswaController::class, 'add'])->name('keterangan_status_mahasiswa.add');
    Route::get('view/{id}', [KeteranganStatusMahasiswaController::class, 'view'])->name('keterangan_status_mahasiswa.view');
    Route::post('save/{save_type}', [KeteranganStatusMahasiswaController::class, 'save'])->name('keterangan_status_mahasiswa.save');
    Route::post('delete', [KeteranganStatusMahasiswaController::class, 'delete'])->name('keterangan_status_mahasiswa.delete');
    Route::get('pdf', [KeteranganStatusMahasiswaController::class, 'pdf'])->name('keterangan_status_mahasiswa.pdf');
    Route::get('excel', [KeteranganStatusMahasiswaController::class, 'excel'])->name('keterangan_status_mahasiswa.excel');
    Route::get('downloadFormat', [KeteranganStatusMahasiswaController::class, 'downloadFormat'])->name('keterangan_status_mahasiswa.downloadFormat');
    Route::post('import', [KeteranganStatusMahasiswaController::class, 'import'])->name('keterangan_status_mahasiswa.import');
    Route::post('reactive', [KeteranganStatusMahasiswaController::class, 'reactive'])->name('keterangan_status_mahasiswa.reactive');
});
