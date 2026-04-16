<?php

namespace App\Http\Controllers;

use App\Services\GenerateDendaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenerateDendaController extends BaseController
{
    protected $service;

    public function __construct(GenerateDendaService $service)
    {
        $this->service = $service;
    }

    /**
     * Display main view
     */
    public function index()
    {
        log_akses('View', 'Melihat Daftar Modul Generate Denda');
        return view('generate_denda.v_generate_denda');
    }

    /**
     * Search data
     */
    public function search(Request $request, $offset = 0)
    {
        try {
            $filters = $request->only([
                'programID',
                'prodiID',
                'tahunID',
                'jalurPendaftaran',
                'tahunMasuk',
                'jenisPendaftaran',
                'keyword'
            ]);

            $limit = 100000000000;
            $result = $this->service->searchData($limit, $offset, $filters);

            // Get setup denda
            $setupDenda = $this->service->getSetupDenda(
                $filters['tahunID'] ?? null,
                $filters['prodiID'] ?? null,
                $filters['programID'] ?? null,
                $filters['tahunMasuk'] ?? null
            );

            // Get tahun info
            $tahun = [];
            if (!empty($filters['tahunID'])) {
                $tahun[$filters['tahunID']] = DB::table('tahun')->where('ID', $filters['tahunID'])->first();
            }

            $data = [
                'offset' => $offset,
                'query' => $result['data'],
                'link' => load_pagination($result['total'], $limit, $offset, 'search', 'filter'),
                'total_row' => total_row($result['total'], $limit, $offset),
                'Update' => $this->hasPermission('c_generate_denda', 'Update') ? 'YA' : 'TIDAK',
                'Delete' => $this->hasPermission('c_generate_denda', 'Delete') ? 'YA' : 'TIDAK',
                'tahunID' => $filters['tahunID'] ?? '',
                'tahun' => $tahun,
                'setup_denda' => $setupDenda,
                'datenow' => date('Y-m-d')
            ];

            return view('generate_denda.s_generate_denda', $data);

        } catch (\Exception $e) {
            $this->logError('search', $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Posting denda single
     */
    public function posting(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required',
                'tahunID' => 'required'
            ]);

            $exp = explode('_', $validated['id']);
            $tagihanID = $exp[0];
            $jumlahDenda = $exp[1] ?? 0;

            $result = $this->service->postingDenda($tagihanID, $jumlahDenda, $validated['tahunID']);

            if ($result['success']) {
                return $this->successResponse(null, $result['message']);
            } else {
                return $this->errorResponse($result['message']);
            }

        } catch (\Exception $e) {
            $this->logError('posting', $e->getMessage(), $request->all());
            return $this->errorResponse('Data gagal diproses!.');
        }
    }

    /**
     * Posting denda all
     */
    public function postingAll(Request $request)
    {
        try {
            $validated = $request->validate([
                'selected' => 'required|array',
                'tahunID' => 'required'
            ]);

            $result = $this->service->postingDendaAll($validated['selected'], $validated['tahunID']);

            if ($result['success']) {
                return $this->successResponse(null, $result['message']);
            } else {
                return $this->errorResponse($result['message']);
            }

        } catch (\Exception $e) {
            $this->logError('postingAll', $e->getMessage(), $request->all());
            return $this->errorResponse('Data gagal diproses!.');
        }
    }
}
