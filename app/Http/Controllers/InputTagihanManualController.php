<?php

namespace App\Http\Controllers;

use App\Services\InputTagihanManualService;
use Illuminate\Http\Request;

class InputTagihanManualController extends BaseController
{
    protected $service;

    public function __construct(InputTagihanManualService $service)
    {
        $this->service = $service;
    }

    /**
     * Display main view
     */
    public function index()
    {
        return view('input_tagihan_manual.v_input_tagihan_manual');
    }

    /**
     * Search mahasiswa
     */
    public function searchMahasiswa(Request $request)
    {
        try {
            $filters = $request->only([
                'ProgramID',
                'ProdiID',
                'TahunID',
                'Angkatan',
                'JenisPendaftaran',
                'JalurPendaftaran',
                'SemesterMasuk',
                'KelasID',
                'KelasIH'
            ]);

            $result = $this->service->searchMahasiswa($filters);

            return response()->json($result);

        } catch (\Exception $e) {
            $this->logError('searchMahasiswa', $e->getMessage());
            return response()->json([
                'temp' => '<option value="">Error terjadi</option>',
                'jumlah' => 0,
                'temp_tidak_aktif' => '',
                'jumlahTidakAktif' => 0
            ], 500);
        }
    }

    /**
     * Change angkatan dropdown
     */
    public function changeAngkatan(Request $request)
    {
        $option = $this->service->getAngkatanList();
        return $option;
    }

    /**
     * Content biaya
     */
    public function contentBiaya(Request $request)
    {
        try {
            $filters = $request->only([
                'ProgramID',
                'ProdiID',
                'TahunID',
                'Angkatan',
                'JenisPendaftaran',
                'JalurPendaftaran',
                'SemesterMasuk',
                'KelasID',
                'KelasIH'
            ]);

            $data = $this->service->getContentBiaya($filters);

            return view('input_tagihan_manual.s_content_biaya', $data);

        } catch (\Exception $e) {
            $this->logError('contentBiaya', $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Input tagihan manual
     */
    public function inputTagihanManual(Request $request)
    {
        try {
            $validated = $request->validate([
                'PeriodeID' => 'required',
                'ProgramID' => 'required',
                'ProdiID' => 'required',
                'Angkatan' => 'required',
                'JenisPendaftaran' => 'required',
                'JalurPendaftaran' => 'required',
                'SemesterMasuk' => 'required',
                'tipe' => 'required|in:1,2',
                'TanggalTagihan' => 'required|date',
                'biaya' => 'required|array',
                'jumlah' => 'required|array',
                'mhswID' => 'nullable|array',
                'jumlahdetail' => 'nullable|array',
            ]);

            $result = $this->service->inputTagihanManual($validated);

            return response()->json($result);

        } catch (\Exception $e) {
            $this->logError('inputTagihanManual', $e->getMessage(), $request->all());
            return response()->json([
                'status' => '0',
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
