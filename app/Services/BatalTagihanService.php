<?php

namespace App\Services;

use App\Models\BatalTagihan;
use Illuminate\Support\Facades\DB;

class BatalTagihanService
{
    /**
     * Search data tagihan yang bisa dibatalkan
     */
    public function searchData($limit, $offset, $filters)
    {
        extract($filters);

        // Build base query
        $baseQuery = function($query) use ($PeriodeID, $ProdiID, $ProgramID, $Angkatan, $JenisBiayaID, $MhswID) {
            $query->select(
                    'tagihan_mahasiswa.ID',
                    'mahasiswa.NPM',
                    'mahasiswa.Nama',
                    'mahasiswa.TahunMasuk',
                    'jenisbiaya.Nama as JenisBiaya',
                    'tagihan_mahasiswa.Jumlah'
                )
                ->where('cicilan_tagihan_mahasiswa.ID', null)
                ->where('pengajuan_pembayaran.ID', null)
                ->where('mahasiswa.jenis_mhsw', 'mhsw')
                ->leftJoin('cicilan_tagihan_mahasiswa', function($join) {
                    $join->on('cicilan_tagihan_mahasiswa.TagihanMahasiswaID', '=', 'tagihan_mahasiswa.ID')
                         ->where('cicilan_tagihan_mahasiswa.Jumlah', '!=', 0);
                })
                ->join('mahasiswa', 'mahasiswa.ID', '=', 'tagihan_mahasiswa.MhswID')
                ->join('jenisbiaya', 'jenisbiaya.ID', '=', 'tagihan_mahasiswa.JenisBiayaID')
                ->leftJoin('pengajuan_pembayaran_detail', function($join) {
                    $join->on('pengajuan_pembayaran_detail.EntitasID', '=', 'tagihan_mahasiswa.ID')
                         ->where('pengajuan_pembayaran_detail.Jenis', '=', 'tagihan_mahasiswa');
                })
                ->leftJoin('pengajuan_pembayaran', function($join) {
                    $join->on('pengajuan_pembayaran.ID', '=', 'pengajuan_pembayaran_detail.PengajuanPembayaranID')
                         ->where('pengajuan_pembayaran.Status', '=', 0);
                })
                ->groupBy('tagihan_mahasiswa.ID')
                ->orderBy('mahasiswa.TahunMasuk', 'DESC')
                ->orderBy('mahasiswa.Nama', 'ASC');

            // Apply filters
            if (!empty($PeriodeID)) {
                $query->where('tagihan_mahasiswa.Periode', $PeriodeID);
            }
            if (!empty($ProdiID)) {
                $query->where('mahasiswa.ProdiID', $ProdiID);
            }
            if (!empty($ProgramID)) {
                $query->where('mahasiswa.ProgramID', $ProgramID);
            }
            if (!empty($Angkatan)) {
                $query->where('mahasiswa.TahunMasuk', $Angkatan);
            }
            if (!empty($JenisBiayaID)) {
                $query->where('jenisbiaya.ID', $JenisBiayaID);
            }
            if (!empty($MhswID)) {
                $query->where('mahasiswa.ID', $MhswID);
            }
        };

        // Count query - use distinct count without groupBy
        $countQuery = DB::table('tagihan_mahasiswa')
            ->where('mahasiswa.jenis_mhsw', 'mhsw')
            ->leftJoin('cicilan_tagihan_mahasiswa', function($join) {
                $join->on('cicilan_tagihan_mahasiswa.TagihanMahasiswaID', '=', 'tagihan_mahasiswa.ID')
                     ->where('cicilan_tagihan_mahasiswa.Jumlah', '!=', 0);
            })
            ->join('mahasiswa', 'mahasiswa.ID', '=', 'tagihan_mahasiswa.MhswID')
            ->join('jenisbiaya', 'jenisbiaya.ID', '=', 'tagihan_mahasiswa.JenisBiayaID')
            ->leftJoin('pengajuan_pembayaran_detail', function($join) {
                $join->on('pengajuan_pembayaran_detail.EntitasID', '=', 'tagihan_mahasiswa.ID')
                     ->where('pengajuan_pembayaran_detail.Jenis', '=', 'tagihan_mahasiswa');
            })
            ->leftJoin('pengajuan_pembayaran', function($join) {
                $join->on('pengajuan_pembayaran.ID', '=', 'pengajuan_pembayaran_detail.PengajuanPembayaranID')
                     ->where('pengajuan_pembayaran.Status', '=', 0);
            })
            ->where('cicilan_tagihan_mahasiswa.ID', null)
            ->where('pengajuan_pembayaran.ID', null);

        // Apply same filters to count query
        if (!empty($PeriodeID)) {
            $countQuery->where('tagihan_mahasiswa.Periode', $PeriodeID);
        }
        if (!empty($ProdiID)) {
            $countQuery->where('mahasiswa.ProdiID', $ProdiID);
        }
        if (!empty($ProgramID)) {
            $countQuery->where('mahasiswa.ProgramID', $ProgramID);
        }
        if (!empty($Angkatan)) {
            $countQuery->where('mahasiswa.TahunMasuk', $Angkatan);
        }
        if (!empty($JenisBiayaID)) {
            $countQuery->where('jenisbiaya.ID', $JenisBiayaID);
        }
        if (!empty($MhswID)) {
            $countQuery->where('mahasiswa.ID', $MhswID);
        }

        // Use distinct count for accurate results with groupBy
        $total = $countQuery->distinct()->count('tagihan_mahasiswa.ID');

        // Data query with groupBy and pagination
        $dataQuery = DB::table('tagihan_mahasiswa');
        $baseQuery($dataQuery);
        
        $data = $dataQuery->offset($offset)->limit($limit)->get();

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * Get Prodi list for dropdown
     */
    public function getProdiList($tahunID)
    {
        return DB::table('tagihan_mahasiswa')
            ->join('mahasiswa', function($join) {
                $join->on('mahasiswa.ID', '=', 'tagihan_mahasiswa.MhswID')
                     ->where('mahasiswa.jenis_mhsw', 'mhsw');
            })
            ->join('programstudi', 'programstudi.ID', '=', 'mahasiswa.ProdiID')
            ->leftJoin('jenjang', 'jenjang.ID', '=', 'programstudi.JenjangID')
            ->where('tagihan_mahasiswa.Periode', $tahunID)
            ->groupBy('mahasiswa.ProdiID')
            ->select('programstudi.ID', 'programstudi.Nama', 'jenjang.Nama as NamaJenjang')
            ->get();
    }

    /**
     * Get Program list for dropdown
     */
    public function getProgramList($tahunID)
    {
        return DB::table('tagihan_mahasiswa')
            ->join('mahasiswa', function($join) {
                $join->on('mahasiswa.ID', '=', 'tagihan_mahasiswa.MhswID')
                     ->where('mahasiswa.jenis_mhsw', 'mhsw');
            })
            ->join('program', 'program.ID', '=', 'mahasiswa.ProgramID')
            ->where('tagihan_mahasiswa.Periode', $tahunID)
            ->groupBy('mahasiswa.ProgramID')
            ->select('program.ID', 'program.Nama')
            ->get();
    }

    /**
     * Get Angkatan list for dropdown
     */
    public function getAngkatanList($tahunID)
    {
        return DB::table('tagihan_mahasiswa')
            ->join('mahasiswa', function($join) {
                $join->on('mahasiswa.ID', '=', 'tagihan_mahasiswa.MhswID')
                     ->where('mahasiswa.jenis_mhsw', 'mhsw');
            })
            ->where('tagihan_mahasiswa.Periode', $tahunID)
            ->groupBy('mahasiswa.TahunMasuk')
            ->select('mahasiswa.TahunMasuk')
            ->get();
    }

    /**
     * Get Mahasiswa list for dropdown
     */
    public function getMahasiswaList($filters)
    {
        extract($filters);

        $query = DB::table('tagihan_mahasiswa')
            ->join('mahasiswa', function($join) {
                $join->on('mahasiswa.ID', '=', 'tagihan_mahasiswa.MhswID')
                     ->where('mahasiswa.jenis_mhsw', 'mhsw');
            })
            ->join('jenisbiaya', 'jenisbiaya.ID', '=', 'tagihan_mahasiswa.JenisBiayaID')
            ->where('tagihan_mahasiswa.Periode', $TahunID ?? null)
            ->groupBy('mahasiswa.ID')
            ->select('mahasiswa.*');

        if (!empty($ProdiID)) {
            $query->where('mahasiswa.ProdiID', $ProdiID);
        }
        if (!empty($Angkatan)) {
            $query->where('mahasiswa.TahunMasuk', $Angkatan);
        }
        if (!empty($ProgramID)) {
            $query->where('mahasiswa.ProgramID', $ProgramID);
        }

        return $query->get();
    }

    /**
     * Delete tagihan (with helper logic)
     */
    public function deleteTagihan($checkIDs)
    {
        $deleted = 0;
        $failed = 0;

        foreach ($checkIDs as $id) {
            try {
                DB::beginTransaction();

                // Call delete_tagihan helper logic
                $result = $this->deleteTagihanLogic($id);

                if ($result['status'] == 1) {
                    $deleted++;
                    DB::commit();
                } else {
                    $failed++;
                    DB::rollBack();
                }
            } catch (\Exception $e) {
                $failed++;
                DB::rollBack();
            }
        }

        return [
            'success' => $deleted > 0,
            'deleted' => $deleted,
            'failed' => $failed,
            'message' => $deleted . ' data berhasil dihapus. ' . ($failed > 0 ? $failed . ' data gagal dihapus.' : '')
        ];
    }

    /**
     * Logic for deleting single tagihan (from CI helper delete_tagihan)
     */
    protected function deleteTagihanLogic($tagihanID)
    {
        try {
            $tagihan = DB::table('tagihan_mahasiswa')->where('ID', $tagihanID)->first();

            if (!$tagihan) {
                return ['status' => 0, 'message' => 'Tagihan tidak ditemukan'];
            }

            // Check if already paid
            $totalCicilan = DB::table('cicilan_tagihan_mahasiswa')
                ->where('TagihanMahasiswaID', $tagihanID)
                ->sum('Jumlah');

            if ($totalCicilan > 0) {
                return ['status' => 0, 'message' => 'Tagihan sudah ada cicilan, tidak bisa dihapus'];
            }

            // Check if there's a pending payment request
            $pendingPayment = DB::table('pengajuan_pembayaran_detail')
                ->join('pengajuan_pembayaran', 'pengajuan_pembayaran.ID', '=', 'pengajuan_pembayaran_detail.PengajuanPembayaranID')
                ->where('pengajuan_pembayaran_detail.EntitasID', $tagihanID)
                ->where('pengajuan_pembayaran_detail.Jenis', 'tagihan_mahasiswa')
                ->where('pengajuan_pembayaran.Status', 0)
                ->exists();

            if ($pendingPayment) {
                return ['status' => 0, 'message' => 'Tagihan sedang dalam proses pengajuan pembayaran'];
            }

            // Delete related records first
            DB::table('cicilan_tagihan_mahasiswa')->where('TagihanMahasiswaID', $tagihanID)->delete();
            DB::table('tagihan_mahasiswa_detail')->where('TagihanMahasiswaID', $tagihanID)->delete();
            DB::table('tagihan_mahasiswa_termin')->where('TagihanMahasiswaID', $tagihanID)->delete();

            // Delete the main tagihan
            $deleted = DB::table('tagihan_mahasiswa')->where('ID', $tagihanID)->delete();

            if ($deleted) {
                // Update tagihan semester totals
                if ($tagihan->TagihanMahasiswaSemesterID) {
                    $this->updateSemesterTotals($tagihan->TagihanMahasiswaSemesterID);
                }

                return ['status' => 1, 'message' => 'Tagihan berhasil dihapus'];
            } else {
                return ['status' => 0, 'message' => 'Gagal menghapus tagihan'];
            }

        } catch (\Exception $e) {
            return ['status' => 0, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Update semester totals after deletion
     */
    protected function updateSemesterTotals($semesterID)
    {
        $sums = DB::table('tagihan_mahasiswa')
            ->where('TagihanMahasiswaSemesterID', $semesterID)
            ->select(
                DB::raw('SUM(IFNULL(TotalTagihan,0)) as sum_total_tagihan'),
                DB::raw('SUM(IFNULL(Jumlah,0)) as sum_jumlah'),
                DB::raw('SUM(IFNULL(Sisa,0)) as sum_sisa'),
                DB::raw('SUM(IFNULL(TotalCicilan,0)) as sum_totalcicilan'),
                DB::raw('SUM(IFNULL(JumlahDiskon,0)) as sum_diskon')
            )
            ->first();

        if ($sums) {
            DB::table('tagihan_mahasiswa_semester')
                ->where('ID', $semesterID)
                ->update([
                    'TotalTagihan' => $sums->sum_total_tagihan,
                    'Jumlah' => $sums->sum_total_tagihan - $sums->sum_diskon,
                    'Sisa' => ($sums->sum_total_tagihan - $sums->sum_diskon) - $sums->sum_totalcicilan,
                    'JumlahDiskon' => $sums->sum_diskon,
                    'TotalCicilan' => $sums->sum_totalcicilan
                ]);
        }
    }
}
