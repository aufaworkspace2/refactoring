<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CalonMahasiswaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;

class CalonMahasiswaController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(CalonMahasiswaService $service)
    {
        $this->service = $service;

        $this->middleware(function ($request, $next) {
            if (!Session::get('username')) {
                return redirect('/');
            }

            $lang = Session::get('language');
            if (!$lang && !request()->cookie('language')) {
                $lang = 'indonesia';
                Session::put('language', $lang);
            }

            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);

            $levelUser = Session::get('LevelUser');
            $this->Create = cek_level($levelUser, 'c_calon_mahasiswa', 'Create');
            $this->Update = cek_level($levelUser, 'c_calon_mahasiswa', 'Update');
            $this->Delete = cek_level($levelUser, 'c_calon_mahasiswa', 'Delete');

            return $next($request);
        });
    }

    /**
     * Display list of calon mahasiswa
     */
    public function index(Request $request, $offset = 0, $bayar = 0)
    {
        $data['Create'] = $this->Create;
        $data['bayar'] = $bayar;

        // Get format nomor pendaftaran
        $data['format_pmb'] = $this->service->getFormatNomor();

        // Load dropdown data directly from database
        $data['data_gelombang'] = DB::table('pmb_tbl_gelombang')
            ->orderBy('nama', 'ASC')
            ->get();

        $data['data_gelombang_detail'] = DB::table('pmb_tbl_gelombang_detail')
            ->leftJoin('pmb_tbl_gelombang', 'pmb_tbl_gelombang.id', '=', 'pmb_tbl_gelombang_detail.gelombang_id')
            ->select('pmb_tbl_gelombang_detail.id', 'pmb_tbl_gelombang.nama', 'pmb_tbl_gelombang_detail.gelombang_id')
            ->orderBy('pmb_tbl_gelombang.nama', 'ASC')
            ->get();

        $data['data_jalur'] = DB::table('pmb_edu_jalur_pendaftaran')
            ->where('aktif', '1')
            ->orderBy('nama', 'ASC')
            ->get();

        $data['data_jenis_pendaftaran'] = DB::table('jenis_pendaftaran')->where('Aktif', 'ya')->orderBy('Nama', 'ASC')->get();

        $data['data_programstudi'] = DB::table('programstudi')->join('jenjang', 'jenjang.ID', '=', 'programstudi.JenjangID')->orderBy('programstudi.Nama', 'ASC')->select('programstudi.*', 'jenjang.Nama as JenjangNama')->get();

        $data['data_program'] = DB::table('program')
            ->orderBy('Nama', 'ASC')
            ->get();

        $data['data_prodi'] = DB::table('programstudi')
            ->orderBy('Nama', 'ASC')
            ->get();

        // Get filter values from request for auto-select
        $data['selected_gelombang'] = $request->input('gelombang', '');
        $data['selected_gelombang_detail'] = $request->input('gelombang_detail', '');
        $data['selected_jalur'] = $request->input('jalur_pendaftaran', '');
        $data['selected_program'] = $request->input('program', '');
        $data['selected_bayar'] = $request->input('bayar', $bayar);

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Pendaftar');
        }

        return view('calon_mahasiswa.v_calon_mahasiswa', $data);
    }

    /**
     * Search calon mahasiswa dengan filters
     */
    public function search(Request $request, $offset = 0)
    {
        $post = $request->all();
        $filters = [];

        // Build filters array - same as CI3
        $filters['bayar'] = $post['bayar'] ?? 0;

        if (!empty($post['gelombang'])) {
            $filters['gelombang'] = $post['gelombang'];
        }

        if (!empty($post['gelombang_detail'])) {
            $filters['gelombang_detail'] = $post['gelombang_detail'];
        }

        if (!empty($post['jenis_pendaftaran'])) {
            $filters['jenis_pendaftaran'] = $post['jenis_pendaftaran'];
        }

        if (!empty($post['jalur_pendaftaran'])) {
            $filters['jalur_pendaftaran'] = $post['jalur_pendaftaran'];
        }

        if (!empty($post['program'])) {
            $filters['program'] = $post['program'];
        }

        if (!empty($post['pilihan1'])) {
            $filters['pilihan1'] = $post['pilihan1'];
        }

        if (!empty($post['pilihan2'])) {
            $filters['pilihan2'] = $post['pilihan2'];
        }

        if (!empty($post['ujian'])) {
            $filters['ujian'] = $post['ujian'];
        }

        if (!empty($post['ikut_ujian'])) {
            $filters['ikut_ujian'] = $post['ikut_ujian'];
        }

        if (!empty($post['keyword'])) {
            $filters['keyword'] = $post['keyword'];
        }

        $limit = 10;
        
        // Build ORDER BY same as CI3
        if ($filters['bayar'] == 1) {
            $orderby = "order by mahasiswa.urutall_pmb DESC, mahasiswa.noujian_pmb DESC";
        } else {
            $orderby = "order by mahasiswa.ID desc";
        }
        
        // Add limit
        $orderby .= " limit $offset, $limit";

        $jml = $this->service->countVerifikasiPMB('', $filters['bayar'], '');
        $data['offset'] = $offset;
        $data['bayar'] = $filters['bayar'];

        // Get data lengkap with processed rows
        $data['query'] = $this->service->getDataLengkap($filters, $filters['bayar'], null, null);
        
        // Manually apply limit since getDataLengkap doesn't use it
        $data['query'] = array_slice($data['query'], $offset, $limit);

        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('calon_mahasiswa.s_calon_mahasiswa', $data);
    }

    /**
     * Show form to create new calon mahasiswa
     */
    public function add()
    {
        $data['save'] = 1;
        $data['Create'] = $this->Create;
        
        // Load dropdown data
        $data['data_gelombang'] = DB::table('pmb_tbl_gelombang')
            ->orderBy('nama', 'ASC')
            ->get();
        
        $data['data_jalur'] = DB::table('pmb_edu_jalur_pendaftaran')
            ->where('aktif', '1')
            ->orderBy('nama', 'ASC')
            ->get();
        
        $data['data_jenis_pendaftaran'] = DB::table('jenis_pendaftaran')->where('Aktif', 'ya')->orderBy('Nama', 'ASC')->get();

        $data['data_programstudi'] = DB::table('programstudi')->join('jenjang', 'jenjang.ID', '=', 'programstudi.JenjangID')->orderBy('programstudi.Nama', 'ASC')->select('programstudi.*', 'jenjang.Nama as JenjangNama')->get();

        $data['data_program'] = DB::table('program')
            ->orderBy('Nama', 'ASC')
            ->get();
        
        $data['data_prodi'] = DB::table('programstudi')
            ->orderBy('Nama', 'ASC')
            ->get();

        if (function_exists('log_akses')) {
            log_akses('View', 'Form Tambah Pendaftar');
        }

        return view('calon_mahasiswa.f_calon_mahasiswa', $data);
    }

    /**
     * Display specified calon mahasiswa
     */
    public function view($id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['save'] = 2;
        $data['Update'] = $this->Update;
        
        // Load dropdown data
        $data['data_gelombang'] = DB::table('pmb_tbl_gelombang')
            ->orderBy('nama', 'ASC')
            ->get();
        
        $data['data_jalur'] = DB::table('pmb_edu_jalur_pendaftaran')
            ->where('aktif', '1')
            ->orderBy('nama', 'ASC')
            ->get();
        
        $data['data_jenis_pendaftaran'] = DB::table('jenis_pendaftaran')->where('Aktif', 'ya')->orderBy('Nama', 'ASC')->get();

        $data['data_programstudi'] = DB::table('programstudi')->join('jenjang', 'jenjang.ID', '=', 'programstudi.JenjangID')->orderBy('programstudi.Nama', 'ASC')->select('programstudi.*', 'jenjang.Nama as JenjangNama')->get();

        $data['data_program'] = DB::table('program')
            ->orderBy('Nama', 'ASC')
            ->get();
        
        $data['data_prodi'] = DB::table('programstudi')
            ->orderBy('Nama', 'ASC')
            ->get();

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Detail Pendaftar ID: ' . $id);
        }

        return view('calon_mahasiswa.f_calon_mahasiswa', $data);
    }

    /**
     * Store new calon mahasiswa
     */
    public function save(Request $request, $save)
    {
        try {
            $validated = $request->validate([
                'Nama' => 'required|string|max:255',
                'NoIdentitas' => 'required|string|max:50',
                'TempatLahir' => 'required|string|max:100',
                'TanggalLahir' => 'nullable|date',
                'Kelamin' => 'required|in:L,P',
                'AgamaID' => 'nullable|integer',
                'Alamat' => 'required|string|max:500',
                'KotaID' => 'nullable|integer',
                'KecamatanID' => 'nullable|integer',
                'Kelurahan' => 'nullable|string|max:255',
                'KodePos' => 'nullable|string|max:10',
                'HP' => 'nullable|string|max:20',
                'Email' => 'nullable|email|max:255',
            ], [
                'Nama.required' => 'Nama lengkap wajib diisi',
                'NoIdentitas.required' => 'No KTP/NIK wajib diisi',
                'TempatLahir.required' => 'Tempat lahir wajib diisi',
            ]);

            $id = $request->input('ID', null);
            
            // Build data array
            $data = $request->except(['ID', '_token', '_method']);
            
            // Handle file upload if exists
            if ($request->hasFile('foto_pmb')) {
                $file = $request->file('foto_pmb');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('pmb/foto'), $fileName);
                $data['foto_pmb'] = $fileName;
            }

            if ($save == 1) {
                // Generate nomor pendaftaran if not set
                if (empty($data['noujian_pmb'])) {
                    $format = $this->service->getFormatNomor();
                    $data['noujian_pmb'] = $this->generateNomorPendaftaran($format);
                }
                
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['user_create'] = Session::get('UserID');
                
                if (function_exists('logs')) {
                    logs("Menambah pendaftar baru: {$data['Nama']}");
                }
                
                $insertId = DB::table('mahasiswa')->insertGetId($data);
                
                return response()->json([
                    'status' => 1,
                    'message' => 'Data berhasil ditambahkan',
                    'data' => ['id' => $insertId]
                ]);
            }
            
            if ($save == 2) {
                if (function_exists('logs')) {
                    logs("Mengubah data pendaftar ID: $id");
                }
                
                DB::table('mahasiswa')
                    ->where('ID', $id)
                    ->update($data);
                
                return response()->json([
                    'status' => 1,
                    'message' => 'Data berhasil diubah'
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('CalonMahasiswaController::save - Error: ' . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Terjadi kesalahan saat menyimpan data'
            ], 500);
        }
    }

    /**
     * Delete calon mahasiswa
     */
    public function delete(Request $request)
    {
        try {
            $request->validate([
                'checkID' => 'required|array|min:1',
                'checkID.*' => 'required|integer|exists:mahasiswa,ID'
            ]);

            $checkid = $request->input('checkID', []);
            $removedIds = [];
            $failed = 0;

            foreach ($checkid as $id) {
                if (function_exists('log_akses')) {
                    $nama = DB::table('mahasiswa')->where('ID', $id)->value('Nama');
                    log_akses('Hapus', 'Menghapus Data Pendaftar Dengan Nama ' . $nama);
                }

                if ($this->service->delete($id)) {
                    $removedIds[] = $id;
                } else {
                    $failed++;
                }
            }

            return response()->json([
                'status' => 'success',
                'removed_ids' => $removedIds,
                'class_prefix' => 'calon_mahasiswa_',
                'message' => count($removedIds) . " data berhasil dihapus. {$failed} data gagal."
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pilih minimal 1 data untuk dihapus',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('CalonMahasiswaController::delete - Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus data'
            ], 500);
        }
    }

    /**
     * Upload file (foto, dokumen, etc)
     */
    public function upload_file(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:5120', // Max 5MB
            ]);

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('pmb/uploads'), $fileName);

            return response()->json([
                'location' => asset('pmb/uploads/' . $fileName)
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('CalonMahasiswaController::upload_file - Error: ' . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Upload gagal'
            ], 500);
        }
    }

    /**
     * Generate nomor pendaftaran
     */
    private function generateNomorPendaftaran(?string $format): string
    {
        if (!$format) {
            return date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        }

        // Parse format and generate number
        $replacements = [
            '{YYYY}' => date('Y'),
            '{MM}' => date('m'),
            '{DD}' => date('d'),
            '{NNNN}' => str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
        ];

        return strtr($format, $replacements);
    }

    /**
     * Update status pembayaran calon mahasiswa
     */
    public function updateStatusBayar(Request $request)
    {
        try {
            $id = $request->input('ID');
            $status = $request->input('status');
            $nominalJumlahBayar = str_replace('.', '', $request->input('nominal_jumlah_bayar'));

            $mhsw = DB::table('mahasiswa')->where('ID', $id)->first();
            if (!$mhsw) {
                return response()->json(['status' => false, 'message' => 'Data tidak ditemukan']);
            }

            $gelombang = $mhsw->gelombang_detail_pmb;
            $pilihan = $mhsw->Pilihan1;
            $jalur = $mhsw->jalur_pmb;
            $program = $mhsw->ProgramID;

            if ($status == 1) {
                // Generate nomor ujian
                $generateNomor = $this->generateNomor($id, $gelombang);

                if ($generateNomor['status'] == 1 && $generateNomor['message'] != null) {
                    $noujian = $generateNomor['message'];

                    // Assign jadwal USM
                    $jadwalusm = DB::table('pmb_edu_jadwalusm')
                        ->where('gelombang', $gelombang)
                        ->get();

                    foreach ($jadwalusm as $rowJadwalusm) {
                        $jadwalusmDetail = DB::table('pmb_edu_jadwalusm_detail')
                            ->join('ruang', 'ruang.id', '=', 'pmb_edu_jadwalusm_detail.ruang_id')
                            ->where('pmb_edu_jadwalusm_detail.jadwalusm_id', $rowJadwalusm->id)
                            ->where(function($q) {
                                $q->whereRaw('pmb_edu_jadwalusm_detail.jumlah_peserta < ruang.KapPMB')
                                  ->orWhere('ruang.UnlimitedPMB', '1');
                            })
                            ->limit(1)
                            ->first();

                        if ($jadwalusmDetail) {
                            // Update kapasitas ruang
                            DB::table('pmb_edu_jadwalusm_detail')
                                ->where('id', $jadwalusmDetail->id)
                                ->increment('jumlah_peserta');

                            // Insert ke map peserta
                            DB::table('pmb_edu_map_peserta')->insert([
                                'idjadwal' => $rowJadwalusm->id,
                                'idpendaftaran' => $id,
                                'idjadwalusm_detail' => $jadwalusmDetail->id
                            ]);
                        }
                    }

                    // Kirim email
                    $this->kirimEmail($mhsw->ID);

                    $prosesUbahStatus = 1;
                } else {
                    $prosesUbahStatus = 0;
                }
            } else {
                $noujianCek = $mhsw->noujian_pmb;
                $jmlreg = $mhsw->statuslulus_pmb == 1 ? 1 : 0;

                if ($jmlreg == 0) {
                    // Hapus dari ruangan
                    $mapPeserta = DB::table('pmb_edu_map_peserta')
                        ->where('idpendaftaran', $id)
                        ->get();

                    foreach ($mapPeserta as $dataJ) {
                        DB::table('pmb_edu_jadwalusm_detail')
                            ->where('id', $dataJ->idjadwalusm_detail)
                            ->decrement('jumlah_peserta');
                    }

                    DB::table('pmb_edu_map_peserta')
                        ->where('idpendaftaran', $id)
                        ->delete();

                    $noujian = '';
                    $prosesUbahStatus = 1;
                } else {
                    $prosesUbahStatus = 2; // Sudah registrasi ulang
                }
            }

            $statusResponse = false;
            $message = 'Anda gagal mengubah status calon Mahasiswa ini karena Ada Kesalahan Teknis.';

            if ($prosesUbahStatus == 1) {
                $tanggalTransfer = DB::table('pmb_tbl_konfirmasi_bayar')
                    ->where('pendaftaranid', $id)
                    ->value('tanggal');

                // Call API untuk update
                $apiUrl = getenv('API_URL') . "/updateNoUjianPMB/?ID={$id}&noujian=" . urlencode($noujian) . 
                          "&bayar=" . urlencode($status) . "&TanggalTransfer=" . urlencode($tanggalTransfer) . 
                          "&nominal_jumlah_bayar=" . urlencode($nominalJumlahBayar);

                $runEdit = file_get_contents($apiUrl);

                if ($runEdit) {
                    $statusResponse = true;
                    $message = 'Anda telah berhasil mengubah status calon Mahasiswa ini.';
                }

                $statusPesan = (int)($runEdit);
            } elseif ($prosesUbahStatus == 0) {
                $statusPesan = 0;
                if (!empty($generateNomor['message'])) {
                    $message = $generateNomor['message'];
                }
            } elseif ($prosesUbahStatus == 2) {
                $message = 'Anda gagal mengubah status calon Mahasiswa ini Karena Mahasiswa ini sudah registrasi ulang.';
                $statusPesan = 2;
            }

            // Log aktivitas
            if (function_exists('log_verifikasi_calon_mahasiswa')) {
                log_verifikasi_calon_mahasiswa($id, $status, $nominalJumlahBayar, $statusPesan, 
                    Session::get('UserID'), Session::get('username'));
            }

            return response()->json(['status' => $statusResponse, 'message' => $message]);

        } catch (\Exception $e) {
            \Log::error('CalonMahasiswaController::updateStatusBayar - Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Verifikasi semua calon mahasiswa yang dipilih (massal)
     */
    public function verifikasiAll(Request $request)
    {
        try {
            $post = $request->all();
            $status = 1;
            $success = 0;
            $fail = 0;
            $registrasiUlang = false;

            foreach ($post['checkID'] as $id) {
                $mhsw = DB::table('mahasiswa')->where('ID', $id)->first();
                if (!$mhsw) continue;

                $gelombang = $mhsw->gelombang_detail_pmb ?? null;
                $pilihan = $mhsw->pilihan1 ?? null;
                $jalur = $mhsw->jalur_pmb ?? null;
                $program = $mhsw->ProgramID ?? null;

                if ($status == 1) {
                    $generateNomor = $this->generateNomor($id, $gelombang);

                    if ($generateNomor['status'] == 1) {
                        $noujian = $generateNomor['message'];

                        // Assign jadwal USM
                        $jadwalusm = DB::table('pmb_edu_jadwalusm')
                            ->where('gelombang', $gelombang)
                            ->get();

                        foreach ($jadwalusm as $rowJadwalusm) {
                            $jadwalusmDetail = DB::table('pmb_edu_jadwalusm_detail')
                                ->join('ruang', 'ruang.id', '=', 'pmb_edu_jadwalusm_detail.ruang_id')
                                ->where('pmb_edu_jadwalusm_detail.jadwalusm_id', $rowJadwalusm->id)
                                ->where(function($q) {
                                    $q->whereRaw('pmb_edu_jadwalusm_detail.jumlah_peserta < ruang.KapPMB')
                                      ->orWhere('ruang.UnlimitedPMB', '1');
                                })
                                ->limit(1)
                                ->first();

                            if ($jadwalusmDetail) {
                                DB::table('pmb_edu_jadwalusm_detail')
                                    ->where('id', $jadwalusmDetail->id)
                                    ->increment('jumlah_peserta');

                                DB::table('pmb_edu_map_peserta')->insert([
                                    'idjadwal' => $rowJadwalusm->id,
                                    'idpendaftaran' => $id,
                                    'idjadwalusm_detail' => $jadwalusmDetail->id
                                ]);
                            }
                        }
                    } else {
                        return response()->json([
                            'status' => 0,
                            'message' => $generateNomor['message']
                        ]);
                    }
                } else {
                    $noujianCek = $mhsw->noujian_pmb ?? null;
                    $jmlreg = $mhsw->statuslulus_pmb == 1 ? 1 : 0;

                    if ($jmlreg == 0) {
                        $mapPeserta = DB::table('pmb_edu_map_peserta')
                            ->where('idpendaftaran', $id)
                            ->get();

                        foreach ($mapPeserta as $dataJ) {
                            DB::table('pmb_edu_jadwalusm_detail')
                                ->where('id', $dataJ->idjadwalusm_detail)
                                ->decrement('jumlah_peserta');
                        }

                        DB::table('pmb_edu_map_peserta')
                            ->where('idpendaftaran', $id)
                            ->delete();

                        $noujian = '';
                    } else {
                        $registrasiUlang = true;
                    }
                }

                $nominalJumlahBayar = !empty($mhsw->jumlahbayar_pmb) 
                    ? $mhsw->jumlahbayar_pmb + ($mhsw->biaya_tambahan_formulir_pmb ?? 0) : 0;

                if (!$registrasiUlang) {
                    $tanggalTransfer = DB::table('pmb_tbl_konfirmasi_bayar')
                        ->where('pendaftaranid', $id)
                        ->value('tanggal');

                    $baseUrl = rtrim(getenv('API_URL'), '/');
                    $response = Http::get($baseUrl . "/updateNoUjianPMB/", [
                        'ID' => $id,
                        'noujian' => $noujian ?? '',
                        'bayar' => $status,
                        'TanggalTransfer' => $tanggalTransfer ?? '',
                        'nominal_jumlah_bayar' => $nominalJumlahBayar
                    ]);

                    $statusPesan = $response->successful() ? 1 : 0;
                } else {
                    $statusPesan = 2;
                }

                // Log
                if (function_exists('log_verifikasi_calon_mahasiswa')) {
                    log_verifikasi_calon_mahasiswa($id, $status, $nominalJumlahBayar, $statusPesan, 
                        Session::get('UserID'), Session::get('username'));
                }

                if ($statusPesan == 1) {
                    $success++;
                } else {
                    $fail++;
                }
            }

            $resStatus = 0;
            if ($success > 0 && $fail == 0) {
                $resStatus = 1;
                $message = "Anda telah berhasil mengubah status bayar semua mahasiswa yang dipilih";
            } elseif ($success > 0 && $fail > 0) {
                $resStatus = 1;
                $message = "Jumlah Mahasiswa Yang berhasil diubah statusnya {$success} Orang. Jumlah Mahasiswa Yang Gagal diubah statusnya {$fail} Orang.";
            } else {
                $message = "Status Bayar Mahasiswa yang dipilih gagal diubah karena data setting no ujian belum lengkap";
            }

            return response()->json(['status' => $resStatus, 'message' => $message]);

        } catch (\Exception $e) {
            \Log::error('CalonMahasiswaController::verifikasiAll - Error: ' . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Set status ujian online (bisa/tidak bisa ujian)
     */
    public function setUjian(Request $request)
    {
        try {
            $checkid = $request->input('checkID', []);
            $status = $request->input('status', '');

            foreach ($checkid as $id) {
                $apiUrl = getenv('API_URL') . "/updateUjianOnlinePMB/?ID={$id}&Status={$status}";
                file_get_contents($apiUrl);
            }

            return response(1)->header('Content-Type', 'text/html');

        } catch (\Exception $e) {
            \Log::error('CalonMahasiswaController::setUjian - Error: ' . $e->getMessage());
            return response(0)->header('Content-Type', 'text/html');
        }
    }

    /**
     * Set status ikut ujian (ikut/tidak ikut)
     */
    public function setIkutUjian(Request $request)
    {
        try {
            $checkid = $request->input('checkID', []);
            $status = $request->input('status', '');

            foreach ($checkid as $id) {
                $apiUrl = getenv('API_URL') . "/updateIkutUjianPMB/?ID={$id}&Status={$status}";
                file_get_contents($apiUrl);
            }

            return response(1)->header('Content-Type', 'text/html');

        } catch (\Exception $e) {
            \Log::error('CalonMahasiswaController::setIkutUjian - Error: ' . $e->getMessage());
            return response(0)->header('Content-Type', 'text/html');
        }
    }

    /**
     * Generate nomor ujian untuk calon mahasiswa
     */
    private function generateNomor($id, $gelombang)
    {
        try {
            $baseUrl = rtrim(getenv('API_URL'), '/');
            
            // Menggunakan Http bawaan Laravel, parameter dipisah jadi array supaya aman dari karakter aneh
            $response = Http::get($baseUrl . "/getNoUjianPMB/", [
                'ID' => $id,
                'gelombang_detail' => $gelombang
            ]);

            // Cek apakah response dari API sukses (kode 200-an)
            if ($response->successful()) {
                return $response->json() ?? ['status' => 0, 'message' => 'Format JSON tidak valid'];
            }

            return ['status' => 0, 'message' => 'Gagal generate nomor. API merespons dengan kode: ' . $response->status()];

        } catch (\Exception $e) {
            \Log::error('CalonMahasiswaController::generateNomor - Error: ' . $e->getMessage());
            return ['status' => 0, 'message' => 'Error koneksi ke API: ' . $e->getMessage()];
        }
    }

    /**
     * Kirim email notifikasi ke calon mahasiswa
     */
    private function kirimEmail($mhswId)
    {
        try {
            $mhsw = DB::table('mahasiswa')->where('ID', $mhswId)->first();
            if (!$mhsw || !$mhsw->Email) {
                return false;
            }

            // Get data lengkap untuk email
            $gelombangDetail = DB::table('pmb_tbl_gelombang_detail')
                ->where('id', $mhsw->gelombang_detail_pmb)
                ->first();

            $gelombang = DB::table('pmb_tbl_gelombang')
                ->where('id', $gelombangDetail->gelombang_id)
                ->first();

            $prodi = DB::table('programstudi')
                ->where('ID', $mhsw->pilihan1)
                ->first();

            // Kirim email menggunakan API
            $apiUrl = getenv('API_URL') . "/kirimEmailPMB/";
            $data = [
                'email' => $mhsw->Email,
                'nama' => $mhsw->Nama,
                'noujian' => $mhsw->noujian_pmb,
                'gelombang' => $gelombang->nama ?? '',
                'prodi' => $prodi->Nama ?? '',
            ];

            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            $result = curl_exec($ch);
            curl_close($ch);

            return $result ? true : false;

        } catch (\Exception $e) {
            \Log::error('CalonMahasiswaController::kirimEmail - Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get gelombang detail by gelombang ID for AJAX dropdown
     */
    public function getGelombangDetail(Request $request)
    {
        try {
            $gelombangId = $request->input('gelombang_id');

            $gelombangDetail = DB::table('pmb_tbl_gelombang_detail')
                ->leftJoin('pmb_tbl_gelombang', 'pmb_tbl_gelombang.id', '=', 'pmb_tbl_gelombang_detail.gelombang_id')
                ->where('pmb_tbl_gelombang_detail.gelombang_id', $gelombangId)
                ->select('pmb_tbl_gelombang_detail.id', 'pmb_tbl_gelombang.nama', 'pmb_tbl_gelombang_detail.gelombang_id')
                ->orderBy('pmb_tbl_gelombang_detail.id', 'ASC')
                ->get();

            $options = '';
            foreach ($gelombangDetail as $gd) {
                $options .= '<option value="' . $gd->id . '">' . $gd->nama . '</option>';
            }

            return response($options)->header('Content-Type', 'text/html');

        } catch (\Exception $e) {
            \Log::error('CalonMahasiswaController::getGelombangDetail - Error: ' . $e->getMessage());
            return response('')->header('Content-Type', 'text/html');
        }
    }

    /**
     * Change prodi based on jalur and gelombang (AJAX)
     */
    public function changeProdi(Request $request)
    {
        try {
            $jalurPmb = $request->input('jalur_pmb');
            $gelombangDetailPmb = $request->input('gelombang_detail_pmb');
            $pilihan2 = $request->input('selected_2');
            $pilihan3 = $request->input('selected_3');

            $gelombangDetail = DB::table('pmb_tbl_gelombang_detail')
                ->where('id', $gelombangDetailPmb)
                ->first();

            // Check setting prodi tambahan jurusan
            $cekSettingProdiTambahanJurusan = null;
            if (!empty($jalurPmb) && !empty($gelombangDetail)) {
                $cekSettingProdiTambahanJurusan = DB::table('setting_prodi_tambahan_jurusan')
                    ->whereRaw("FIND_IN_SET($jalurPmb, JalurID)")
                    ->whereRaw("FIND_IN_SET($gelombangDetail->prodi_id, ProdiID)")
                    ->first();
            }

            // Get all prodi
            $allProdi = DB::table('programstudi')->get();
            $dataProdi = [];
            foreach ($allProdi as $prodi) {
                $dataProdi[$prodi->ID] = $prodi->Nama;
            }

            // Generate option HTML
            $option2 = '<option value="">-- Pilih Pilihan Kedua --</option>';
            $option3 = '<option value="">-- Pilih Pilihan Ketiga --</option>';
            $jumlahProdi = 2; // default

            if (!empty($cekSettingProdiTambahanJurusan)) {
                $listProdi2 = explode(',', $cekSettingProdiTambahanJurusan->ListProdi2);
                $listProdi3 = explode(',', $cekSettingProdiTambahanJurusan->ListProdi3);
                $jumlahProdi = $cekSettingProdiTambahanJurusan->JumlahProdiTambahan;

                foreach ($dataProdi as $key => $data) {
                    if (in_array($key, $listProdi2)) {
                        $jenjangId = DB::table('jenjang')->where('ID', DB::table('programstudi')->where('ID', $key)->value('JenjangID'))->value('Nama');
                        $selected = ($pilihan2 == $key) ? "selected" : "";
                        $option2 .= '<option value="' . $key . '" ' . $selected . '>' . $jenjangId . " || " . $data . '</option>';
                    }
                }

                foreach ($dataProdi as $key => $data) {
                    if (in_array($key, $listProdi3)) {
                        $jenjangId = DB::table('jenjang')->where('ID', DB::table('programstudi')->where('ID', $key)->value('JenjangID'))->value('Nama');
                        $selected = ($pilihan3 == $key) ? "selected" : "";
                        $option3 .= '<option value="' . $key . '" ' . $selected . '>' . $jenjangId . " || " . $data . '</option>';
                    }
                }
            } else {
                // Use default setup
                foreach ($dataProdi as $key => $data) {
                    $jenjangId = DB::table('jenjang')->where('ID', DB::table('programstudi')->where('ID', $key)->value('JenjangID'))->value('Nama');
                    $selected2 = ($pilihan2 == $key) ? "selected" : "";
                    $selected3 = ($pilihan3 == $key) ? "selected" : "";

                    $option2 .= '<option value="' . $key . '" ' . $selected2 . '>' . $jenjangId . " || " . $data . '</option>';
                    $option3 .= '<option value="' . $key . '" ' . $selected3 . '>' . $jenjangId . " || " . $data . '</option>';
                }
            }

            return response()->json([
                'jumlah' => (int)$jumlahProdi,
                'option2' => $option2,
                'option3' => $option3
            ]);

        } catch (\Exception $e) {
            \Log::error('CalonMahasiswaController::changeProdi - Error: ' . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Export to Excel
     */
    public function excel(Request $request)
    {
        try {
            $filters = [];
            $filters['bayar'] = $request->input('bayar', 0);
            $filters['gelombang'] = $request->input('gelombang', '');
            $filters['gelombang_detail'] = $request->input('gelombang_detail', '');
            $filters['jenis_pendaftaran'] = $request->input('jenis_pendaftaran', '');
            $filters['jalur_pendaftaran'] = $request->input('jalur_pendaftaran', '');
            $filters['program'] = $request->input('program', '');
            $filters['pilihan1'] = $request->input('pilihan1', '');
            $filters['pilihan2'] = $request->input('pilihan2', '');
            $filters['ujian'] = $request->input('ujian', '');
            $filters['ikut_ujian'] = $request->input('ikut_ujian', '');
            $filters['keyword'] = $request->input('keyword', '');

            // Get data without pagination
            $data = $this->service->getDataLengkap($filters, $filters['bayar'], null, null);

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $spreadsheet->setActiveSheetIndex(0);
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Data Calon Mahasiswa');

            $rowNum = 1;

            // Add header with kop if function exists
            if (function_exists('cetak_kop_phpspreadsheet')) {
                $rowNum = cetak_kop_phpspreadsheet($sheet, 'P');
            }

            // Title
            $sheet->setCellValue('A'.$rowNum, 'DATA CALON MAHASISWA BARU');
            $sheet->mergeCells('A'.$rowNum.':P'.$rowNum);
            $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A'.$rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $rowNum += 2;

            // Table headers
            $startRow = $rowNum;
            $sheet->setCellValue('A'.$rowNum, 'No.');
            $sheet->setCellValue('B'.$rowNum, 'No. Ujian');
            $sheet->setCellValue('C'.$rowNum, 'Nama');
            $sheet->setCellValue('D'.$rowNum, 'Email');
            $sheet->setCellValue('E'.$rowNum, 'No HP');
            $sheet->setCellValue('F'.$rowNum, 'Program');
            $sheet->setCellValue('G'.$rowNum, 'Prodi Pilihan 1');
            $sheet->setCellValue('H'.$rowNum, 'Gelombang');
            $sheet->setCellValue('I'.$rowNum, 'Jalur Pendaftaran');
            $sheet->setCellValue('J'.$rowNum, 'Tanggal Daftar');
            $sheet->setCellValue('K'.$rowNum, 'Status Bayar');
            $sheet->setCellValue('L'.$rowNum, 'Kelengkapan Data');
            $sheet->setCellValue('M'.$rowNum, 'Agent PMB');
            $sheet->setCellValue('N'.$rowNum, 'Kode Referal');
            $sheet->setCellValue('O'.$rowNum, 'Petugas');
            $sheet->setCellValue('P'.$rowNum, 'Status Ujian');

            $sheet->getStyle('A'.$rowNum.':P'.$rowNum)->getFont()->setBold(true);
            $sheet->getStyle('A'.$rowNum.':P'.$rowNum)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFC000');
            $sheet->getStyle('A'.$rowNum.':P'.$rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $rowNum++;

            // Data rows
            $no = 1;
            foreach ($data as $row) {
                $row = (object) $row;
                
                $sheet->setCellValue('A'.$rowNum, $no++);
                $sheet->setCellValue('B'.$rowNum, $row->noujian_pmb ?? '-');
                $sheet->setCellValue('C'.$rowNum, $row->Nama ?? '');
                $sheet->setCellValue('D'.$rowNum, $row->Email ?? '');
                $sheet->setCellValue('E'.$rowNum, $row->HP ?? '');
                $sheet->setCellValue('F'.$rowNum, $row->programNama ?? '-');
                $sheet->setCellValue('G'.$rowNum, $row->prodiNama ?? '-');
                $sheet->setCellValue('H'.$rowNum, $row->gelombangNama ?? '-');
                $sheet->setCellValue('I'.$rowNum, $row->jalur_pmb ?? '-');
                $sheet->setCellValue('J'.$rowNum, $row->TglBuat ?? '-');
                $sheet->setCellValue('K'.$rowNum, $row->statusbayar_pmb == 1 ? 'Sudah Bayar' : 'Belum Bayar');
                $sheet->setCellValue('L'.$rowNum, ($row->persentase_kelengkapan_data ?? 0) . '%');
                $sheet->setCellValue('M'.$rowNum, $row->nama_agent ?? '-');
                $sheet->setCellValue('N'.$rowNum, $row->kode_referal_pmb ?? '-');
                $sheet->setCellValue('O'.$rowNum, $row->namauser ?? '-');
                $sheet->setCellValue('P'.$rowNum, $row->ujian_online_pmb == 1 ? 'Bisa Ujian' : ($row->ujian_online_pmb == 2 ? 'Tidak Bisa Ujian' : '-'));

                $rowNum++;
            }

            // Apply borders
            $styleBorder = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    ]
                ]
            ];
            $sheet->getStyle('A'.$startRow.':P'.($rowNum-1))->applyFromArray($styleBorder);

            // Auto-size columns
            foreach(range('A','P') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            // Output
            while (ob_get_level() > 0) ob_end_clean();
            $filename = "data_calon_mahasiswa_" . date('d-m-Y') . ".xlsx";
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');

            $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $objWriter->save('php://output');
            exit;

        } catch (\Exception $e) {
            \Log::error('CalonMahasiswaController::excel - Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Export Excel gagal: ' . $e->getMessage());
        }
    }

    /**
     * Export Referal Agent to Excel
     */
    public function excelReferal(Request $request)
    {
        try {
            $filters = [];
            $filters['bayar'] = $request->input('bayar', 1);
            $filters['gelombang'] = $request->input('gelombang', '');
            $filters['gelombang_detail'] = $request->input('gelombang_detail', '');
            $filters['jenis_pendaftaran'] = $request->input('jenis_pendaftaran', '');
            $filters['jalur_pendaftaran'] = $request->input('jalur_pendaftaran', '');
            $filters['program'] = $request->input('program', '');
            $filters['pilihan1'] = $request->input('pilihan1', '');
            $filters['pilihan2'] = $request->input('pilihan2', '');
            $filters['ujian'] = $request->input('ujian', '');
            $filters['ikut_ujian'] = $request->input('ikut_ujian', '');
            $filters['keyword'] = $request->input('keyword', '');

            // Get data
            $data = $this->service->getDataLengkap($filters, $filters['bayar'], null, null);

            // Get all prodi for lookup
            $allProdi = DB::table('programstudi')->get();
            $arrProdi = [];
            foreach ($allProdi as $prodi) {
                $arrProdi[$prodi->ID] = $prodi;
            }

            // Group by referal
            $getReferal = [];
            foreach ($data as $row) {
                $row = (object) $row;
                if (!empty($row->kode_referal_pmb)) {
                    $getReferal[$row->kode_referal_pmb][] = $row;
                }
            }

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $spreadsheet->setActiveSheetIndex(0);
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Data Referal Agent');

            $rowNum = 1;

            // Add header with kop if function exists
            if (function_exists('cetak_kop_phpspreadsheet')) {
                $rowNum = cetak_kop_phpspreadsheet($sheet, 'F');
            }

            // Title
            $sheet->setCellValue('A'.$rowNum, 'REKAP DAFTAR REFERAL PMB');
            $sheet->mergeCells('A'.$rowNum.':F'.$rowNum);
            $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A'.$rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $rowNum += 2;

            // Loop through each referal code
            foreach ($getReferal as $kodeReferal => $rows) {
                // Get agent data
                $dataAgent = DB::table('pmb_tbl_agent')
                    ->where('kode_referal', $kodeReferal)
                    ->first();

                // Agent info
                $sheet->setCellValue('A'.$rowNum, 'Kode Referal');
                $sheet->setCellValue('B'.$rowNum, ': ' . ($dataAgent->kode_referal ?? ''));
                $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true);
                $rowNum++;

                $sheet->setCellValue('A'.$rowNum, 'Nama Agent');
                $sheet->setCellValue('B'.$rowNum, ': ' . ($dataAgent->nama ?? ''));
                $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true);
                $rowNum++;

                $sheet->setCellValue('A'.$rowNum, 'Nama Institusi');
                $sheet->setCellValue('B'.$rowNum, ': ' . ($dataAgent->institusi ?? ''));
                $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true);
                $rowNum++;

                // Table headers
                $startRow = $rowNum;
                $sheet->setCellValue('A'.$rowNum, 'No Reg/ No Ujian');
                $sheet->setCellValue('B'.$rowNum, 'Nama');
                $sheet->setCellValue('C'.$rowNum, 'Pilihan 1');
                $sheet->setCellValue('D'.$rowNum, 'Pilihan 2');
                $sheet->setCellValue('E'.$rowNum, 'Pilihan 3');
                $sheet->setCellValue('F'.$rowNum, 'No HP');

                $sheet->getStyle('A'.$rowNum.':F'.$rowNum)->getFont()->setBold(true);
                $sheet->getStyle('A'.$rowNum.':F'.$rowNum)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFC000');
                $sheet->getStyle('A'.$rowNum.':F'.$rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $rowNum++;

                // Data rows
                foreach ($rows as $row) {
                    $row = (object) $row;
                    
                    $sheet->setCellValueExplicit('A'.$rowNum, $row->noujian_pmb ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValue('B'.$rowNum, $row->Nama ?? '');
                    $sheet->setCellValue('C'.$rowNum, $row->prodiNama ?? '');
                    
                    $pilihan2 = isset($arrProdi[$row->pilihan2]) ? $arrProdi[$row->pilihan2]->Nama : '-';
                    $pilihan3 = isset($arrProdi[$row->pilihan3]) ? $arrProdi[$row->pilihan3]->Nama : '-';
                    
                    $sheet->setCellValue('D'.$rowNum, $pilihan2);
                    $sheet->setCellValue('E'.$rowNum, $pilihan3);
                    $sheet->setCellValueExplicit('F'.$rowNum, $row->HP ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    
                    $rowNum++;
                }

                // Apply borders
                $styleBorder = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                        ]
                    ]
                ];
                $sheet->getStyle('A'.$startRow.':F'.($rowNum-1))->applyFromArray($styleBorder);

                $rowNum += 2;
            }

            // Auto-size columns
            foreach(range('A','F') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            // Output
            while (ob_get_level() > 0) ob_end_clean();
            $filename = "data_agent_referal_pmb_" . date('d-m-Y') . ".xlsx";
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');

            $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $objWriter->save('php://output');
            exit;

        } catch (\Exception $e) {
            \Log::error('CalonMahasiswaController::excelReferal - Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Export Excel Referal gagal: ' . $e->getMessage());
        }
    }

    /**
     * Export to PDF
     */
    public function pdf(Request $request)
    {
        try {
            $filters = [];
            $filters['bayar'] = $request->input('bayar', 0);
            $filters['gelombang'] = $request->input('gelombang', '');
            $filters['gelombang_detail'] = $request->input('gelombang_detail', '');
            $filters['jenis_pendaftaran'] = $request->input('jenis_pendaftaran', '');
            $filters['jalur_pendaftaran'] = $request->input('jalur_pendaftaran', '');
            $filters['program'] = $request->input('program', '');
            $filters['pilihan1'] = $request->input('pilihan1', '');
            $filters['pilihan2'] = $request->input('pilihan2', '');
            $filters['ujian'] = $request->input('ujian', '');
            $filters['ikut_ujian'] = $request->input('ikut_ujian', '');
            $filters['keyword'] = $request->input('keyword', '');

            $data['query'] = $this->service->getDataLengkap($filters, $filters['bayar'], null, null);
            $data['bayar'] = $filters['bayar'];
            $data['title'] = 'Data Calon Mahasiswa';

            $pdf = \PDF::loadView('calon_mahasiswa.p_calon_mahasiswa', $data);
            $pdf->setPaper('A4', 'P');
            return $pdf->stream('data_calon_mahasiswa_' . date('d-m-Y') . '.pdf');

        } catch (\Exception $e) {
            \Log::error('CalonMahasiswaController::pdf - Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Export PDF gagal: ' . $e->getMessage());
        }
    }

    /**
     * Show form for upload Excel
     */
    public function addUpload()
    {
        $data['Create'] = $this->Create;
        
        if (function_exists('log_akses')) {
            log_akses('View', 'Form Upload Data Calon Mahasiswa Dari Excel');
        }

        return view('calon_mahasiswa.f_upload_calon_mahasiswa', $data);
    }

    /**
     * Upload Excel massal
     */
    public function uploadExcel(Request $request)
    {
        try {
            $request->validate([
                'file_excel' => 'required|mimes:xlsx,xls|max:10240',
            ]);

            $file = $request->file('file_excel');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/calon_mahasiswa'), $fileName);

            // Read Excel file
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(public_path('uploads/calon_mahasiswa/' . $fileName));
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $success = 0;
            $failed = 0;
            $errors = [];

            // Skip header row
            array_shift($rows);

            foreach ($rows as $index => $row) {
                try {
                    // Map Excel columns to database fields
                    $data = [
                        'Nama' => $row[0] ?? '',
                        'NoIdentitas' => $row[1] ?? '',
                        'Email' => $row[2] ?? '',
                        'HP' => $row[3] ?? '',
                        'TempatLahir' => $row[4] ?? '',
                        'TanggalLahir' => $row[5] ?? '',
                        'Kelamin' => $row[6] ?? 'L',
                        'AgamaID' => $row[7] ?? '',
                        'Alamat' => $row[8] ?? '',
                        // Add more fields as needed
                        'jenis_mhsw' => 'calon',
                        'statusbayar_pmb' => 0,
                        'TglBuat' => date('Y-m-d'),
                        'userid_pmb' => Session::get('UserID'),
                    ];

                    // Insert to database
                    DB::table('mahasiswa')->insert($data);
                    $success++;

                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            // Delete uploaded file
            @unlink(public_path('uploads/calon_mahasiswa/' . $fileName));

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil import {$success} data. Gagal: {$failed} data.",
                'success' => $success,
                'failed' => $failed,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            \Log::error('CalonMahasiswaController::uploadExcel - Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Upload Excel gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show histori pindah prodi calon mahasiswa
     */
    public function historiPindahProdi($mhswId)
    {
        try {
            // Get log pindah prodi
            $query = DB::table('log_pindah_prodi_pmb')
                ->where('MhswID', $mhswId)
                ->get();

            // Get all prodi for lookup
            $allProdi = DB::table('programstudi')->get();
            $arrProdi = [];
            foreach ($allProdi as $prodi) {
                // Get jenjang
                $jenjang = DB::table('jenjang')->where('ID', $prodi->JenjangID)->first();
                $prodi->NamaJenjang = $jenjang->Nama ?? '';
                $arrProdi[$prodi->ID] = $prodi;
            }

            // Langsung panggil file blade yang sudah kamu buat
            return response()
                ->view('calon_mahasiswa.histori_pindah_prodi', [
                    'query' => $query,
                    'all_prodi' => $arrProdi
                ])
                ->header('Content-Type', 'text/html')
                ->header('X-Laravel-Partial', 'true');

        } catch (\Exception $e) {
            \Log::error('CalonMahasiswaController::historiPindahProdi - Error: ' . $e->getMessage());
            $errorHtml = '<div class="alert alert-danger">Gagal memuat histori pindah prodi: ' . $e->getMessage() . '</div>';
            return response($errorHtml, 500)
                ->header('Content-Type', 'text/html')
                ->header('X-Laravel-Partial', 'true');
        }
    }

    /**
     * Show pindah channel pembayaran
     */
    public function lihatPindahChannel($mhswId)
    {
        try {
            // Get data mahasiswa
            $mhsw = DB::table('mahasiswa')->where('ID', $mhswId)->first();
            
            if (!$mhsw) {
                throw new \Exception('Data mahasiswa tidak ditemukan');
            }
            
            // Get channel pembayaran dengan detail
            $channels = DB::table('channel_pembayaran as cp')
                ->leftJoin('metode_pembayaran as mp', 'cp.MetodePembayaranID', '=', 'mp.ID')
                ->where('cp.Status', '1')
                ->select('cp.*', 'mp.Nama as MetodeNama')
                ->orderBy('mp.Nama', 'ASC')
                ->orderBy('cp.Nama', 'ASC')
                ->get();
            
            // Group by metode pembayaran
            $listChannelPembayaran = [];
            foreach ($channels as $channel) {
                $metodeNama = $channel->MetodeNama ?? 'Lainnya';
                if (!isset($listChannelPembayaran[$metodeNama])) {
                    $listChannelPembayaran[$metodeNama] = [];
                }
                $listChannelPembayaran[$metodeNama][] = $channel;
            }

            // Start output buffering to capture HTML
            ob_start();
            ?>
            <style>
               .radiodiv.selected{
                  border:3px solid;
                  border-color: #6861c3 !important;
               }
            </style>

            <input type="hidden" id="channel_pembayaran_id" name="channel_pembayaran_id" class="form-control" value="<?=$mhsw->channel_pembayaran_formulir_pmb ?? ''?>" >
            <input type="hidden" id="bank_id" name="bank_id" class="form-control" value="<?=$mhsw->bank_formulir_pmb ?? ''?>" >
            
            <?php foreach($listChannelPembayaran as $namaMetodeBayar => $listDetailChannel){ ?>
            <div class="list-pembayaran-header bg-grey font-weight-bold border pt-2 pb-2 pl-3 pr-3">
                <p class="font-size-16px"><?=$namaMetodeBayar?></p>
            </div>
            <div class="list-pembayaran bg-white radio-groupdiv">
                <ul>
                <?php foreach($listDetailChannel as $keyDetailChannel => $listRowChannel){ ?>

                    <?php if($listRowChannel->Status == '1'){ ?>
                    <a href="javascript:void(0);">
                        <li class="pt-3 pb-3 border-bottom radiodiv rounded list-unstyled <?=($mhsw->channel_pembayaran_formulir_pmb == $listRowChannel->ID) ? 'selected' : '';?>" data-value="<?=$listRowChannel->ID?>" data-value2="<?=$listRowChannel->BankID ?? ''?>">
                            <div class="d-flex justify-content-between">
                                <div class="content-pembayaran d-flex">
                                    <img src="<?=url('metodebayar/channelbayar/' . ($listRowChannel->Icon ?? ''))?>" alt="" class="mr-4 align-self-center" style="max-height: 40px;">
                                    <p class="font-size-16px text-dark align-self-center"><?=$listRowChannel->Nama?></p>
                                </div>
                            </div>
                        </li>
                    </a>
                    <?php }else{ ?>
                    <li class="pt-3 pb-3 border-bottom list-unstyled" style="opacity: 0.5;">
                        <div class="d-flex justify-content-between">
                            <div class="content-pembayaran d-flex">
                                <img src="<?=url('metodebayar/channelbayar/' . ($listRowChannel->Icon ?? ''))?>" alt="" class="mr-4 align-self-center" style="max-height: 40px;">
                                <div>
                                    <p class="font-size-16px text-dark align-self-center"><?=$listRowChannel->Nama?></p>
                                    <small>Tidak tersedia untuk transaksi ini</small>
                                </div>
                            </div>
                        </div>
                    </li>
                    <?php } ?>
                <?php } ?>
                </ul>
            </div>
            <?php } ?>

            <button type="button" onclick="simpan_channel(<?=$mhsw->ID?>)" class="btn btn-success float-right waves-effect waves-light mt-2 mb-2 btnSave" id="btnSimpanChannel"> Simpan</button>

            <script>
            $('.radio-groupdiv .radiodiv').click(function(){
                $('.radiodiv').removeClass('selected');
                $(this).addClass('selected');
                var val = $(this).attr('data-value');
                var val2 = $(this).attr('data-value2');
                $('#channel_pembayaran_id').val(val);
                $('#bank_id').val(val2);
            });

            function simpan_channel(mhswId)
            {
                var channel_pembayaran_id = $('#channel_pembayaran_id').val();
                var bank_id = $('#bank_id').val();

                if(channel_pembayaran_id == ''){
                    swal('Pemberitahuan','Pilih Channel Terlebih Dahulu','info');
                    return;
                }else{
                    $.ajax({
                        type: "POST",
                        dataType: 'JSON',
                        url: "<?=url('calon_mahasiswa/set_pindah_channel')?>",
                        beforeSend: function() {
                            //$('#konten').html('<center><i class="fa fa-spin fa-spinner"></i> Silahkan Tunggu...</center>');
                        },
                        data: {
                            ID: mhswId,
                            channel_pembayaran_id: channel_pembayaran_id,
                            bank_id: bank_id,
                        },
                        success: function(data) {
                            if (data.status == 1) {
                                swal('Pemberitahuan', data.message, 'success');
                            } else {
                                swal('Pemberitahuan', data.message, 'error');
                            }
                            $('#modal-dynamic').modal('hide');
                            $('.modal-backdrop').hide();
                            document.body.className = document.body.className.replace("modal-open","");

                        },
                        error: function (data) {
                            swal('Pemberitahuan', 'Maaf, data gagal diproses !.', 'error');
                            $('#modal-dynamic').modal('hide');
                            $('.modal-backdrop').hide();
                            document.body.className = document.body.className.replace("modal-open","");
                        }
                    });
                }
            }
            </script>
            <?php
            $html = ob_get_clean();
            
            return response($html, 200)
                ->header('Content-Type', 'text/html')
                ->header('X-Laravel-Partial', 'true');

        } catch (\Exception $e) {
            \Log::error('CalonMahasiswaController::lihatPindahChannel - Error: ' . $e->getMessage());
            $errorHtml = '<div class="alert alert-danger">Gagal memuat data: ' . e($e->getMessage()) . '</div>';
            return response($errorHtml, 500)
                ->header('Content-Type', 'text/html')
                ->header('X-Laravel-Partial', 'true');
        }
    }
}
