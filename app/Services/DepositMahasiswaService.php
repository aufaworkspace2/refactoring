<?php

namespace App\Services;

use App\Models\DepositMahasiswa;
use App\Models\HistoriDeposit;
use Illuminate\Support\Facades\DB;

class DepositMahasiswaService
{
    /**
     * Search deposit data
     */
    public function searchData($limit, $offset, $filters)
    {
        extract($filters);

        $query = DB::table('deposit_mahasiswa')
            ->select(
                'mahasiswa.NPM',
                'mahasiswa.Nama',
                'mahasiswa.ProgramID',
                'mahasiswa.ProdiID',
                'mahasiswa.TahunMasuk',
                'deposit_mahasiswa.Deposit',
                'deposit_mahasiswa.TanggalEntry',
                DB::raw('DATE(deposit_mahasiswa.TanggalEntry) as Tanggal'),
                DB::raw('TIME(deposit_mahasiswa.TanggalEntry) as jam'),
                'deposit_mahasiswa.ID'
            )
            ->join('mahasiswa', 'deposit_mahasiswa.MhswID', '=', 'mahasiswa.ID')
            ->orderBy('mahasiswa.Nama', 'ASC');

        // Apply filters
        if (!empty($keyword)) {
            $query->where(function($q) use ($keyword) {
                $q->where('mahasiswa.NPM', 'like', "%{$keyword}%")
                  ->orWhere('mahasiswa.Nama', 'like', "%{$keyword}%");
            });
        }
        if (!empty($ProgramID)) {
            $query->where('mahasiswa.ProgramID', $ProgramID);
        }
        if (!empty($ProdiID)) {
            $query->where('mahasiswa.ProdiID', $ProdiID);
        }
        if (!empty($TahunMasuk)) {
            $query->where('mahasiswa.TahunMasuk', $TahunMasuk);
        }

        $total = $query->count();
        $data = $query->offset($offset)->limit($limit)->get();

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * Get single deposit by ID
     */
    public function getById($id)
    {
        return DB::table('deposit_mahasiswa')->where('ID', $id)->first();
    }

    /**
     * Get history deposit by deposit ID
     */
    public function getHistoryDeposit($depositID)
    {
        $deposit = DB::table('deposit_mahasiswa')->where('ID', $depositID)->first();

        if (!$deposit) {
            return null;
        }

        $mahasiswa = DB::table('mahasiswa')->where('ID', $deposit->MhswID)->first();

        $detailHistory = DB::table('histori_deposit')
            ->select(
                DB::raw("IFNULL(jenisbiaya.Nama, '-') as NamaTagihan"),
                'mahasiswa.Nama as NamaMhsw',
                'histori_deposit.*',
                'mahasiswa.NPM'
            )
            ->join('deposit_mahasiswa', 'histori_deposit.MhswID', '=', 'deposit_mahasiswa.MhswID')
            ->join('mahasiswa', 'mahasiswa.ID', '=', 'deposit_mahasiswa.MhswID')
            ->leftJoin('jenisbiaya', 'jenisbiaya.ID', '=', 'histori_deposit.JenisBiayaID')
            ->where('deposit_mahasiswa.ID', $depositID)
            ->groupBy('histori_deposit.ID')
            ->get();

        return [
            'row' => $deposit,
            'mahasiswa' => $mahasiswa,
            'detail_history' => $detailHistory
        ];
    }

    /**
     * Add new deposit
     */
    public function addDeposit($data)
    {
        try {
            DB::beginTransaction();

            $tahun = DB::table('tahun')->where('ProsesBuka', 1)->first();
            $tahunID = $tahun ? $tahun->TahunID : null;

            // Insert deposit
            $depositID = DB::table('deposit_mahasiswa')->insertGetId([
                'MhswID' => $data['MhswID'],
                'Deposit' => $data['DepositBaru'],
                'TanggalEntry' => date('Y-m-d H:i:s')
            ]);

            // Insert history
            DB::table('histori_deposit')->insert([
                'MhswID' => $data['MhswID'],
                'TahunID' => $tahunID,
                'NoKwitansi' => '',
                'Status' => 1,
                'Deposit' => $data['DepositBaru'],
                'Manual' => 1,
                'CreatedAt' => date('Y-m-d H:i:s'),
                'UserID' => auth()->id() ?? 0
            ]);

            DB::commit();
            return ['success' => true, 'message' => 'Data berhasil disimpan'];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Update deposit
     */
    public function updateDeposit($id, $data)
    {
        try {
            DB::beginTransaction();

            $deposit = DB::table('deposit_mahasiswa')->where('ID', $id)->first();

            if (!$deposit) {
                return ['success' => false, 'message' => 'Data tidak ditemukan'];
            }

            $depositLama = $deposit->Deposit;
            $depositBaru = $data['DepositBaru'];

            $perubahan = $depositBaru - $depositLama;
            $status = ($depositBaru > $depositLama) ? 1 : 0;

            $tahun = DB::table('tahun')->where('ProsesBuka', 1)->first();
            $tahunID = $tahun ? $tahun->TahunID : null;

            // Update deposit
            DB::table('deposit_mahasiswa')
                ->where('ID', $id)
                ->update([
                    'Deposit' => $depositBaru,
                    'TanggalEntry' => date('Y-m-d H:i:s')
                ]);

            // Insert history
            DB::table('histori_deposit')->insert([
                'MhswID' => $deposit->MhswID,
                'TahunID' => $tahunID,
                'NoKwitansi' => '',
                'Status' => $status,
                'Deposit' => abs($perubahan),
                'Manual' => 1,
                'CreatedAt' => date('Y-m-d H:i:s'),
                'UserID' => auth()->id() ?? 0
            ]);

            DB::commit();
            return ['success' => true, 'message' => 'Data berhasil diupdate'];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Delete deposit
     */
    public function deleteDeposit($checkIDs)
    {
        $deleted = 0;

        foreach ($checkIDs as $id) {
            $deleted += DB::table('deposit_mahasiswa')->where('ID', $id)->delete();
        }

        return [
            'success' => $deleted > 0,
            'message' => $deleted . ' data berhasil dihapus'
        ];
    }

    /**
     * Get mahasiswa for select2 (for add form)
     */
    public function getMahasiswaForSelect2($search = '', $page = 1, $programID = '', $prodiID = '')
    {
        $limit = 30;
        $offset = ($page - 1) * $limit;

        $query = DB::table('mahasiswa')
            ->select(
                'mahasiswa.ID',
                'mahasiswa.NPM',
                'mahasiswa.Nama',
                'mahasiswa.Foto',
                'mahasiswa.Kelamin',
                'mahasiswa.ProdiID',
                'programstudi.Nama as NamaProdi'
            )
            ->leftJoin('programstudi', 'programstudi.ID', '=', 'mahasiswa.ProdiID')
            ->leftJoin('deposit_mahasiswa', 'deposit_mahasiswa.MhswID', '=', 'mahasiswa.ID')
            ->where('mahasiswa.jenis_mhsw', 'mhsw')
            ->where('mahasiswa.NPM', '!=', '')
            ->whereNull('deposit_mahasiswa.ID');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('mahasiswa.NPM', 'like', "%{$search}%")
                  ->orWhere('mahasiswa.Nama', 'like', "%{$search}%");
            });
        }

        if (!empty($prodiID)) {
            $query->where('mahasiswa.ProdiID', $prodiID);
        }
        if (!empty($programID)) {
            $query->where('mahasiswa.ProgramID', $programID);
        }

        $totalCount = $query->count();
        $data = $query->offset($offset)->limit($limit)->orderBy('mahasiswa.NPM')->get();

        $items = [];
        foreach ($data as $value) {
            $items[] = [
                'id' => $value->ID,
                'text' => $value->NPM . ' | ' . $value->Nama
            ];
        }

        if (count($items) == 0) {
            $items[] = [
                'id' => '',
                'text' => 'Mahasiswa tidak ditemukan.'
            ];
        }

        return [
            'total_count' => $totalCount,
            'items' => $items
        ];
    }
}
