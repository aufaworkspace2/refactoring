<?php

namespace App\Http\Controllers;

use App\Services\BatalTagihanService;
use Illuminate\Http\Request;

class BatalTagihanController extends BaseController
{
    protected $service;

    public function __construct(BatalTagihanService $service)
    {
        $this->service = $service;
    }

    /**
     * Display main view
     */
    public function index()
    {
        return view('batal_tagihan.v_batal_tagihan');
    }

    /**
     * Search tagihan data
     */
    public function searchTagihan(Request $request, $offset = 0)
    {
        try {
            $filters = $request->only([
                'PeriodeID',
                'ProdiID',
                'ProgramID',
                'Angkatan',
                'JenisBiayaID',
                'MhswID'
            ]);

            $limit = 50;

            $result = $this->service->searchData($limit, $offset, $filters);

            $data = [
                'offset' => $offset,
                'query' => $result['data'],
                'link' => load_pagination($result['total'], $limit, $offset, 'search_tagihan', 'filter'),
                'total_row' => total_row($result['total'], $limit, $offset),
            ];

            return view('batal_tagihan.list_tagihan', $data);

        } catch (\Exception $e) {
            $this->logError('search_tagihan', $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Prodi dropdown
     */
    public function changeProdi(Request $request)
    {
        $tahunID = $request->input('TahunID');
        $listData = $this->service->getProdiList($tahunID);

        $result = '<option value="">-- Lihat Semua Programstudi --</option>';
        if (count($listData) > 0) {
            foreach ($listData as $row) {
                $result .= '<option value="' . $row->ID . '">' . $row->NamaJenjang . ' || ' . $row->Nama . '</option>';
            }
        } else {
            $result .= '<option>Maaf data tidak di temukan</option>';
        }

        return $result;
    }

    /**
     * Get Program dropdown
     */
    public function changeProgram(Request $request)
    {
        $tahunID = $request->input('TahunID');
        $listData = $this->service->getProgramList($tahunID);

        $result = '<option value="">-- Lihat Semua Program Kuliah --</option>';
        if (count($listData) > 0) {
            foreach ($listData as $row) {
                $result .= '<option value="' . $row->ID . '">' . $row->Nama . '</option>';
            }
        } else {
            $result .= '<option>Maaf data tidak di temukan</option>';
        }

        return $result;
    }

    /**
     * Get Angkatan dropdown
     */
    public function changeAngkatan(Request $request)
    {
        $tahunID = $request->input('TahunID');
        $listData = $this->service->getAngkatanList($tahunID);

        $result = '<option value="">-- Lihat Semua Angkatan --</option>';
        if (count($listData) > 0) {
            foreach ($listData as $row) {
                $result .= '<option value="' . $row->TahunMasuk . '">' . $row->TahunMasuk . '</option>';
            }
        } else {
            $result .= '<option>Maaf data tidak di temukan</option>';
        }

        return $result;
    }

    /**
     * Get Mahasiswa dropdown
     */
    public function changeMahasiswa(Request $request)
    {
        $filters = $request->only([
            'TahunID',
            'ProdiID',
            'Angkatan',
            'ProgramID',
            'JenisBiayaID'
        ]);

        $listData = $this->service->getMahasiswaList($filters);

        $result = '<option value="">-- Lihat Semua Mahasiswa --</option>';
        if (count($listData) > 0) {
            foreach ($listData as $row) {
                $result .= '<option value="' . $row->ID . '">' . $row->NPM . ' || ' . $row->Nama . '</option>';
            }
        } else {
            $result .= '<option>Maaf data tidak di temukan</option>';
        }

        return $result;
    }

    /**
     * Delete selected tagihan
     */
    public function delete(Request $request)
    {
        try {
            $validated = $request->validate([
                'checkID' => 'required|array',
                'checkID.*' => 'exists:tagihan_mahasiswa,ID'
            ]);

            $result = $this->service->deleteTagihan($validated['checkID']);

            if ($result['success']) {
                return response()->json([
                    'status' => 1,
                    'message' => $result['message']
                ]);
            } else {
                return response()->json([
                    'status' => 0,
                    'message' => $result['message']
                ], 400);
            }

        } catch (\Exception $e) {
            $this->logError('delete', $e->getMessage(), $request->all());
            return response()->json([
                'status' => 0,
                'message' => 'Data gagal diproses!.'
            ], 500);
        }
    }
}
