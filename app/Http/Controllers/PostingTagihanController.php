<?php

namespace App\Http\Controllers;

use App\Services\PostingTagihanService;
use Illuminate\Http\Request;

class PostingTagihanController extends BaseController
{
    protected $postingTagihanService;

    public function __construct(PostingTagihanService $postingTagihanService)
    {
        $this->postingTagihanService = $postingTagihanService;
    }

    /**
     * Display main view for posting tagihan
     */
    public function index()
    {
        log_akses('View', 'Melihat Daftar Modul Posting Tagihan');

        return view('posting_tagihan.v_posting_tagihan');
    }

    /**
     * Search and filter data posting tagihan
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
                'keyword',
                'statusPosting',
                'statusDraft',
                'SemesterMasuk',
                'GelombangKe'
            ]);

            $limit = 100000000000;

            $result = $this->postingTagihanService->searchData(
                $limit,
                $offset,
                $filters
            );

            $data = [
                'offset' => $offset,
                'query' => $result['data'],
                'link' => load_pagination($result['total'], $limit, $offset, 'search', 'filter'),
                'total_row' => total_row($result['total'], $limit, $offset),
                'Update' => $this->hasPermission('c_posting_tagihan', 'Update') ? 'YA' : 'TIDAK',
                'Delete' => $this->hasPermission('c_posting_tagihan', 'Delete') ? 'YA' : 'TIDAK',
                'tahunID' => $filters['tahunID'] ?? ''
            ];

            return view('posting_tagihan.s_posting_tagihan', $data);

        } catch (\Exception $e) {
            $this->logError('search', $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process single posting/unposting of draft tagihan
     */
    public function posting(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|exists:mahasiswa,ID',
                'tahunID' => 'required',
                'posting' => 'required|in:0,1'
            ]);

            $idData = $validated['id'];
            $tahunID = $validated['tahunID'];
            $posting = $validated['posting'];

            $result = $this->postingTagihanService->processPosting($idData, $tahunID, $posting);

            if ($result['success']) {
                return $this->successResponse(null, $result['message']);
            } else {
                return $this->errorResponse($result['message']);
            }

        } catch (\Exception $e) {
            $this->logError('posting', $e->getMessage(), $request->all());
            return $this->errorResponse('Data gagal diproses!');
        }
    }

    /**
     * Process bulk posting/unposting of draft tagihan
     */
    public function postingAll(Request $request)
    {
        try {
            $validated = $request->validate([
                'selected' => 'required|array',
                'selected.*' => 'exists:mahasiswa,ID',
                'tahunID' => 'required',
                'posting' => 'required|in:0,1'
            ]);

            $selectedIds = $validated['selected'];
            $tahunID = $validated['tahunID'];
            $posting = $validated['posting'];

            $result = $this->postingTagihanService->processPostingAll($selectedIds, $tahunID, $posting);

            if ($result['success']) {
                return $this->successResponse(null, $result['message']);
            } else {
                return $this->errorResponse($result['message']);
            }

        } catch (\Exception $e) {
            $this->logError('postingAll', $e->getMessage(), $request->all());
            return $this->errorResponse('Data gagal diproses!');
        }
    }

    /**
     * Generate draft tagihan for selected students
     */
    public function draftAll(Request $request)
    {
        try {
            $validated = $request->validate([
                'selected' => 'required|array',
                'selected.*' => 'exists:mahasiswa,ID',
                'tahunID' => 'required'
            ]);

            $selectedIds = $validated['selected'];
            $tahunID = $validated['tahunID'];

            $result = $this->postingTagihanService->generateDraftAll($selectedIds, $tahunID);

            if ($result['success']) {
                return $this->successResponse(null, $result['message']);
            } else {
                return $this->errorResponse($result['message']);
            }

        } catch (\Exception $e) {
            $this->logError('draftAll', $e->getMessage(), $request->all());
            return $this->errorResponse('Data gagal diproses!');
        }
    }
}
