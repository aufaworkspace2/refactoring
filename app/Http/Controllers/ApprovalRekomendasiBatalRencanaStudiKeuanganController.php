<?php

namespace App\Http\Controllers;

use App\Services\ApprovalRekomendasiBatalRencanaStudiService;
use Illuminate\Http\Request;

class ApprovalRekomendasiBatalRencanaStudiKeuanganController extends BaseController
{
    protected $service;

    public function __construct(ApprovalRekomendasiBatalRencanaStudiService $service)
    {
        $this->service = $service;
    }

    /**
     * Display main view for Keuangan
     */
    public function index()
    {
        log_akses('View', 'Melihat Daftar Data Pengajuan Rekomendasi Batal KRS Keuangan');
        return view('approval_rekomendasi_batal_rencanastudi.v_keuangan');
    }

    /**
     * Search data for Keuangan
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
                'keyword',
                'statusPembatalan'
            ]);

            $type = 'keuangan';
            $limit = 20;

            $result = $this->service->searchData($type, $limit, $offset, $filters);

            $data = [
                'offset' => $offset,
                'query' => $result['data'],
                'link' => load_pagination($result['total'], $limit, $offset, 'search', 'filter'),
                'total_row' => total_row($result['total'], $limit, $offset),
                'Update' => $this->hasPermission('c_approval_rekomendasi_batal_rencanastudi/keuangan', 'Update') ? 'YA' : 'TIDAK',
                'Delete' => $this->hasPermission('c_approval_rekomendasi_batal_rencanastudi/keuangan', 'Delete') ? 'YA' : 'TIDAK'
            ];

            return view('approval_rekomendasi_batal_rencanastudi.s_keuangan', $data);

        } catch (\Exception $e) {
            $this->logError('search_keuangan', $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rekomendasi Keuangan (Single)
     */
    public function rekomendasiKeuangan(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|exists:rekomendasi_batal_rencanastudi,ID',
                'rekomendasi_keuangan' => 'required|in:0,1,2'
            ]);

            $result = $this->service->rekomendasiKeuangan(
                $validated['id'],
                $validated['rekomendasi_keuangan']
            );

            if ($result['success']) {
                return $this->successResponse(null, $result['message']);
            } else {
                return $this->errorResponse($result['message']);
            }

        } catch (\Exception $e) {
            $this->logError('rekomendasi_keuangan', $e->getMessage(), $request->all());
            return $this->errorResponse('Data gagal diproses!.');
        }
    }

    /**
     * Rekomendasi Keuangan (Bulk)
     */
    public function rekomendasiKeuanganAll(Request $request)
    {
        try {
            $validated = $request->validate([
                'selected' => 'required|array',
                'selected.*' => 'exists:rekomendasi_batal_rencanastudi,ID',
                'rekomendasi_keuangan' => 'required|in:0,1,2'
            ]);

            $result = $this->service->rekomendasiKeuanganAll(
                $validated['selected'],
                $validated['rekomendasi_keuangan']
            );

            if ($result['success']) {
                return $this->successResponse(null, $result['message']);
            } else {
                return $this->errorResponse($result['message']);
            }

        } catch (\Exception $e) {
            $this->logError('rekomendasi_keuangan_all', $e->getMessage(), $request->all());
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
     * Get Data Nilai
     */
    public function getDataNilai(Request $request)
    {
        $rencanastudiID = $request->input('rencanastudi');
        $result = $this->service->getDataNilai($rencanastudiID);

        return response()->json($result);
    }
}
