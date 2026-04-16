<?php

namespace App\Http\Controllers;

use App\Services\ApprovalRekomendasiBatalRencanaStudiService;
use Illuminate\Http\Request;

class ApprovalRekomendasiBatalRencanaStudiProdiController extends BaseController
{
    protected $service;

    public function __construct(ApprovalRekomendasiBatalRencanaStudiService $service)
    {
        $this->service = $service;
    }

    /**
     * Display main view for Prodi
     */
    public function index()
    {
        log_akses('View', 'Melihat Daftar Data Pengajuan Rekomendasi Batal KRS Prodi');
        return view('approval_rekomendasi_batal_rencanastudi.v_prodi');
    }

    /**
     * Search data for Prodi
     */
    public function search(Request $request, $offset = 0)
    {
        try {
            $filters = $request->only([
                'programID',
                'prodiID',
                'tahunID',
                'kurikulumID',
                'tahunMasuk',
                'kelasID',
                'statusPindahan',
                'keyword'
            ]);

            $type = 'prodi';
            $limit = 20;

            $result = $this->service->searchData($type, $limit, $offset, $filters);

            $data = [
                'offset' => $offset,
                'query' => $result['data'],
                'link' => load_pagination($result['total'], $limit, $offset, 'search', 'filter'),
                'total_row' => total_row($result['total'], $limit, $offset),
                'Update' => $this->hasPermission('c_approval_rekomendasi_batal_rencanastudi/prodi', 'Update') ? 'YA' : 'TIDAK',
                'Delete' => $this->hasPermission('c_approval_rekomendasi_batal_rencanastudi/prodi', 'Delete') ? 'YA' : 'TIDAK'
            ];

            return view('approval_rekomendasi_batal_rencanastudi.s_prodi', $data);

        } catch (\Exception $e) {
            $this->logError('search_prodi', $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rekomendasi Prodi (Single)
     */
    public function rekomendasiProdi(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|exists:rekomendasi_batal_rencanastudi,ID',
                'rekomendasi_prodi' => 'required|in:0,1,2',
                'opsi_keuangan' => 'nullable|in:1,2'
            ]);

            $opsiKeuangan = $validated['opsi_keuangan'] ?? 1;

            $result = $this->service->rekomendasiProdi(
                $validated['id'],
                $validated['rekomendasi_prodi'],
                $opsiKeuangan
            );

            if ($result['success']) {
                return $this->successResponse(null, $result['message']);
            } else {
                return $this->errorResponse($result['message']);
            }

        } catch (\Exception $e) {
            $this->logError('rekomendasi_prodi', $e->getMessage(), $request->all());
            return $this->errorResponse('Data gagal diproses!.');
        }
    }

    /**
     * Rekomendasi Prodi (Bulk)
     */
    public function rekomendasiProdiAll(Request $request)
    {
        try {
            $validated = $request->validate([
                'selected' => 'required|array',
                'selected.*' => 'exists:rekomendasi_batal_rencanastudi,ID',
                'rekomendasi_prodi' => 'required|in:0,1,2',
                'opsi_keuangan' => 'nullable|in:1,2'
            ]);

            $opsiKeuangan = $validated['opsi_keuangan'] ?? 1;

            $result = $this->service->rekomendasiProdiAll(
                $validated['selected'],
                $validated['rekomendasi_prodi'],
                $opsiKeuangan
            );

            if ($result['success']) {
                return $this->successResponse(null, $result['message']);
            } else {
                return $this->errorResponse($result['message']);
            }

        } catch (\Exception $e) {
            $this->logError('rekomendasi_prodi_all', $e->getMessage(), $request->all());
            return $this->errorResponse('Data gagal diproses!.');
        }
    }

    /**
     * Save catatan
     */
    public function saveCatatan(Request $request)
    {
        try {
            $validated = $request->validate([
                'ID' => 'required|exists:rekomendasi_batal_rencanastudi,ID',
                'Catatan' => 'required|string',
                'tipe' => 'required|in:prodi,keuangan'
            ]);

            $this->service->saveCatatan(
                $validated['ID'],
                $validated['tipe'],
                $validated['Catatan']
            );

            return $this->successResponse(null, 'Catatan berhasil disimpan');

        } catch (\Exception $e) {
            $this->logError('save_catatan', $e->getMessage(), $request->all());
            return $this->errorResponse('Catatan gagal disimpan');
        }
    }

    /**
     * Get Kurikulum
     */
    public function changeKurikulum(Request $request)
    {
        $programID = $request->input('programID');
        $prodiID = $request->input('prodiID');

        $listData = $this->service->getKurikulum($programID, $prodiID);

        $temp = '<option value="">-- Pilih Semua --</option>';
        if (count($listData) > 0) {
            foreach ($listData as $value) {
                $temp .= '<option value="' . $value->kurikulumID . '">' . $value->namaKurikulum . '</option>';
            }
        } else {
            $temp = '<option value="">-- Maaf Kurikulum Tidak Ditemukan --</option>';
        }

        return $temp;
    }

    /**
     * Get Tahun Masuk
     */
    public function changeTahunMasuk(Request $request)
    {
        $programID = $request->input('programID');
        $prodiID = $request->input('prodiID');
        $kurikulumID = $request->input('kurikulumID');

        $listData = $this->service->getTahunMasuk($programID, $prodiID, $kurikulumID);

        $temp = '<option value="">-- Pilih Semua --</option>';
        if (count($listData) > 0) {
            foreach ($listData as $value) {
                $temp .= '<option value="' . $value->tahunMasuk . '">' . $value->tahunMasuk . '</option>';
            }
        } else {
            $temp = '<option value="">-- Maaf Tahun Masuk Tidak Ditemukan --</option>';
        }

        return $temp;
    }

    /**
     * Get Kelas
     */
    public function changeKelas(Request $request)
    {
        $prodiID = $request->input('prodiID');
        $listData = $this->service->getKelas($prodiID);

        $temp = '<option value="">-- Pilih Semua --</option>';
        if (count($listData) > 0) {
            foreach ($listData as $value) {
                $temp .= '<option value="' . $value->kelasID . '">' . $value->namaKelas . '</option>';
            }
        } else {
            $temp = '<option value="">-- Maaf Kelas Tidak Ditemukan --</option>';
        }

        return $temp;
    }

    /**
     * Get Data Nilai
     */
    public function getDataNilai(Request $request)
    {
        $rencanastudiID = $request->input('rencanastudi');
        $result = $this->service->getDataNilai($rencanastudiID);

        return response()->json($result);
    }
}
