<?php

namespace App\Http\Controllers;

use App\Services\DepositMahasiswaService;
use Illuminate\Http\Request;

class DepositMahasiswaController extends BaseController
{
    protected $service;

    public function __construct(DepositMahasiswaService $service)
    {
        $this->service = $service;
    }

    /**
     * Display main view
     */
    public function index()
    {
        log_akses('View', 'Melihat Modul deposit mahasiswa');
        return view('deposit_mahasiswa.v_deposit_mahasiswa', [
            'Create' => $this->hasPermission('c_deposit_mahasiswa', 'Create') ? 'YA' : 'TIDAK',
            'Update' => $this->hasPermission('c_deposit_mahasiswa', 'Update') ? 'YA' : 'TIDAK',
            'Delete' => $this->hasPermission('c_deposit_mahasiswa', 'Delete') ? 'YA' : 'TIDAK',
        ]);
    }

    /**
     * Search deposit data
     */
    public function search(Request $request, $offset = 0)
    {
        try {
            $filters = $request->only([
                'ProgramID',
                'ProdiID',
                'TahunMasuk',
                'keyword'
            ]);

            $limit = 10;
            $result = $this->service->searchData($limit, $offset, $filters);

            return view('deposit_mahasiswa.s_deposit_mahasiswa', [
                'offset' => $offset,
                'query' => $result['data'],
                'link' => load_pagination($result['total'], $limit, $offset, 'search', 'filter'),
                'total_row' => total_row($result['total'], $limit, $offset),
                'Update' => $this->hasPermission('c_deposit_mahasiswa', 'Update') ? 'YA' : 'TIDAK',
                'Delete' => $this->hasPermission('c_deposit_mahasiswa', 'Delete') ? 'YA' : 'TIDAK',
            ]);

        } catch (\Exception $e) {
            $this->logError('search', $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show add form
     */
    public function add()
    {
        return view('deposit_mahasiswa.f_deposit_mahasiswa', ['save' => 1, 'row' => null]);
    }

    /**
     * Show view/edit form
     */
    public function view($id)
    {
        $row = $this->service->getById($id);
        return view('deposit_mahasiswa.f_deposit_mahasiswa', ['save' => 2, 'row' => $row]);
    }

    /**
     * Show history deposit
     */
    public function historyDeposit($id)
    {
        $data = $this->service->getHistoryDeposit($id);

        if (!$data) {
            return redirect()->route('deposit_mahasiswa.index')->with('error', 'Data tidak ditemukan');
        }

        return view('deposit_mahasiswa.history_deposit_mahasiswa', $data);
    }

    /**
     * Save deposit (add or update)
     */
    public function save(Request $request, $save)
    {
        try {
            $validated = $request->validate([
                'MhswID' => $save == 1 ? 'required|exists:mahasiswa,ID' : 'nullable',
                'DepositBaru' => 'required|numeric|min:0',
                'ID' => $save == 2 ? 'required|exists:deposit_mahasiswa,ID' : 'nullable',
            ]);

            if ($save == 1) {
                $result = $this->service->addDeposit($validated);
            } else {
                $result = $this->service->updateDeposit($validated['ID'], $validated);
            }

            if ($result['success']) {
                return response()->json(['status' => 1, 'message' => $result['message']]);
            } else {
                return response()->json(['status' => 0, 'message' => $result['message']], 400);
            }

        } catch (\Exception $e) {
            $this->logError('save', $e->getMessage(), $request->all());
            return response()->json(['status' => 0, 'message' => 'Data gagal disimpan'], 500);
        }
    }

    /**
     * Delete deposit
     */
    public function delete(Request $request)
    {
        try {
            $validated = $request->validate([
                'checkID' => 'required|array',
                'checkID.*' => 'exists:deposit_mahasiswa,ID'
            ]);

            $result = $this->service->deleteDeposit($validated['checkID']);

            return response()->json($result);

        } catch (\Exception $e) {
            $this->logError('delete', $e->getMessage(), $request->all());
            return response()->json(['status' => 0, 'message' => 'Data gagal dihapus'], 500);
        }
    }

    /**
     * Get mahasiswa for Select2 (JSON)
     */
    public function jsonMahasiswa(Request $request)
    {
        $search = $request->input('q', '');
        $page = $request->input('page', 1);
        $programID = $request->input('ProgramID', '');
        $prodiID = $request->input('ProdiID', '');

        $result = $this->service->getMahasiswaForSelect2($search, $page, $programID, $prodiID);

        return response()->json($result);
    }
}
