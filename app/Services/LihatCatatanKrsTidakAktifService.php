<?php

namespace App\Services;

use App\Models\TagihanAkademik;
use App\Models\OpsiMahasiswa;
use Illuminate\Support\Facades\DB;

class LihatCatatanKrsTidakAktifService
{
    /**
     * Search data with filters
     */
    public function searchData($limit, $offset, $filters)
    {
        extract($filters);

        $sql = "SELECT
                    tagihan_akademik.*,
                    mahasiswa.NPM,
                    mahasiswa.Nama,
                    SUM(tagihan_mahasiswa.Jumlah) as TotalJumlah,
                    SUM(tagihan_mahasiswa.TotalCicilan) as TotalBayar,
                    tahun.TahunID as KodeTahunID
                FROM tagihan_akademik
                INNER JOIN mahasiswa ON mahasiswa.ID = tagihan_akademik.MhswID
                INNER JOIN tagihan_mahasiswa ON tagihan_mahasiswa.MhswID = mahasiswa.ID
                    AND tagihan_mahasiswa.Periode = ?
                INNER JOIN tahun ON tahun.ID = tagihan_mahasiswa.Periode
                WHERE tagihan_akademik.KRS = 0
                    AND tagihan_akademik.Pengajuan = 0
        ";

        $params = [$TahunID ?? ''];

        if (!empty($TahunMasuk)) {
            $sql .= " AND mahasiswa.TahunMasuk = ?";
            $params[] = $TahunMasuk;
        }
        if (!empty($TahunID)) {
            $sql .= " AND tagihan_akademik.TahunID = ?";
            $params[] = $TahunID;
        }
        if (!empty($ProgramID)) {
            $sql .= " AND mahasiswa.ProgramID = ?";
            $params[] = $ProgramID;
        }
        if (!empty($ProdiID)) {
            $sql .= " AND mahasiswa.ProdiID = ?";
            $params[] = $ProdiID;
        }
        if (!empty($keyword)) {
            $sql .= " AND (mahasiswa.NPM LIKE ? OR mahasiswa.Nama LIKE ?)";
            $params[] = "%{$keyword}%";
            $params[] = "%{$keyword}%";
        }
        if (!empty($Status)) {
            if ($Status == 'approve') {
                $sql .= " AND tagihan_akademik.Approve = 1";
            } elseif ($Status == 'tidak') {
                $sql .= " AND tagihan_akademik.Approve = 2";
            } elseif ($Status == 'belum') {
                $sql .= " AND tagihan_akademik.Approve = 0";
            }
        }
        if (!empty($Tgl1)) {
            $sql .= " AND tagihan_akademik.TanggalBuat >= ?";
            $params[] = $Tgl1;
        }
        if (!empty($Tgl2)) {
            $sql .= " AND tagihan_akademik.TanggalBuat <= ?";
            $params[] = $Tgl2;
        }
        if ($SetKRSYa !== '' && $SetKRSYa !== null) {
            $sql .= " AND tagihan_akademik.SetKRSYa = ?";
            $params[] = $SetKRSYa;
        }

        $sql .= " GROUP BY tagihan_akademik.ID ORDER BY tagihan_akademik.ID DESC";

        // Get data with pagination
        $data = DB::select($sql . " LIMIT ? OFFSET ?", array_merge($params, [$limit, $offset]));

        // Get count
        $countSql = "SELECT COUNT(*) as total FROM (" . $sql . ") as count_table";
        $total = DB::selectOne($countSql, $params)->total;

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * Get tahun semester list
     */
    public function getDataTahunSemester()
    {
        return DB::table('tahun')
            ->select('ID', 'TahunID', 'Nama', 'Semester', 'ProsesBuka')
            ->orderBy('TahunID', 'DESC')
            ->get();
    }

    /**
     * Get tahun angkatan list
     */
    public function getDataTahunAngkatan()
    {
        return DB::table('mahasiswa')
            ->select('TahunMasuk')
            ->where('TahunMasuk', '!=', '')
            ->groupBy('TahunMasuk')
            ->orderBy('TahunMasuk', 'DESC')
            ->get();
    }

    /**
     * Approve/Reject multiple records
     */
    public function approveRecords($checkIDs, $status, $catatanTambahan = '')
    {
        $updated = 0;

        foreach ($checkIDs as $id) {
            // Update tagihan_akademik
            DB::table('tagihan_akademik')
                ->where('ID', $id)
                ->update([
                    'SetKRSYa' => $status,
                    'CatatanTambahan' => $catatanTambahan
                ]);

            // Get tagihan_akademik data
            $tagihanAkademik = DB::table('tagihan_akademik')->where('ID', $id)->first();

            if ($tagihanAkademik) {
                $mhswID = $tagihanAkademik->MhswID;
                $tahunID = $tagihanAkademik->TahunID;

                // Update or insert opsi_mahasiswa
                $opsiMhs = DB::table('opsi_mahasiswa')
                    ->where('MhswID', $mhswID)
                    ->where('TahunID', $tahunID)
                    ->first();

                if ($opsiMhs) {
                    DB::table('opsi_mahasiswa')
                        ->where('ID', $opsiMhs->ID)
                        ->update(['KRS' => $status ? '1' : '0']);
                } else {
                    DB::table('opsi_mahasiswa')->insert([
                        'MhswID' => $mhswID,
                        'TahunID' => $tahunID,
                        'KRS' => $status ? '1' : '0'
                    ]);
                }

                $updated++;
            }
        }

        return [
            'success' => $updated > 0,
            'message' => $updated . ' data berhasil diupdate'
        ];
    }
}
