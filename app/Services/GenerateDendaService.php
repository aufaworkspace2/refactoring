<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class GenerateDendaService
{
    /**
     * Search data generate denda
     */
    public function searchData($limit, $offset, $filters)
    {
        extract($filters);

        $sql = "SELECT
                tagihan_mahasiswa.ID,
                jenisbiaya.ID AS JenisBiayaID,
                jenisbiaya.Nama AS NamaBiaya,
                mahasiswa.NPM AS NIM,
                mahasiswa.Nama AS NamaMahasiswa,
                mahasiswa.ProdiID,
                mahasiswa.ProgramID,
                mahasiswa.TahunMasuk,
                tagihan_mahasiswa.Jumlah,
                tagihan_mahasiswa.Periode,
                histori_generate_denda.ID as HistoriDendaID,
                histori_generate_denda.Jumlah as JumlahDenda,
                IF (
                    setup_duedate_pembayaran.Tipe ='Tanggal',
                    setup_duedate_pembayaran.Tanggal,
                    DATE_ADD(
                    tagihan_mahasiswa.TanggalTagihan,
                    INTERVAL setup_duedate_pembayaran.Hari DAY
                    )
                ) AS Duedate
                FROM
                tagihan_mahasiswa
                INNER JOIN mahasiswa
                    ON tagihan_mahasiswa.MhswID = mahasiswa.ID
                INNER JOIN jenisbiaya
                    ON jenisbiaya.ID = tagihan_mahasiswa.JenisBiayaID
                INNER JOIN setup_duedate_pembayaran
                    ON (
                    setup_duedate_pembayaran.ProgramID = mahasiswa.ProgramID
                    OR setup_duedate_pembayaran.ProgramID = '0'
                    )
                    AND (
                    setup_duedate_pembayaran.ProdiID = mahasiswa.ProdiID
                    OR setup_duedate_pembayaran.ProdiID = '0'
                    )
                    AND (
                    setup_duedate_pembayaran.TahunMasuk = mahasiswa.TahunMasuk
                    OR setup_duedate_pembayaran.TahunMasuk = '0'
                    )
                    AND setup_duedate_pembayaran.JenisBiayaID=tagihan_mahasiswa.JenisBiayaID
                    AND setup_duedate_pembayaran.TahunID=tagihan_mahasiswa.Periode
                LEFT JOIN histori_generate_denda ON histori_generate_denda.TagihanMahasiswaID=tagihan_mahasiswa.ID
                WHERE tagihan_mahasiswa.TotalCicilan = 0 AND mahasiswa.jenis_mhsw='mhsw'
                AND tagihan_mahasiswa.Periode = ?
        ";

        $params = [$tahunID ?? ''];

        if (!empty($programID)) {
            $sql .= " AND mahasiswa.ProgramID=?";
            $params[] = $programID;
        }
        if (!empty($prodiID)) {
            $sql .= " AND mahasiswa.ProdiID=?";
            $params[] = $prodiID;
        }
        if (!empty($tahunMasuk)) {
            $sql .= " AND mahasiswa.TahunMasuk=?";
            $params[] = $tahunMasuk;
        }
        if (!empty($jalurPendaftaran)) {
            $sql .= " AND mahasiswa.jalur_pmb=?";
            $params[] = $jalurPendaftaran;
        }
        if (!empty($jenisPendaftaran)) {
            $sql .= " AND mahasiswa.StatusPindahan=?";
            $params[] = $jenisPendaftaran;
        }
        if (!empty($keyword)) {
            $sql .= " AND (mahasiswa.NPM LIKE ? OR mahasiswa.Nama LIKE ?)";
            $params[] = "%{$keyword}%";
            $params[] = "%{$keyword}%";
        }

        $sql .= " GROUP BY tagihan_mahasiswa.ID ORDER BY mahasiswa.NPM ASC";

        // Get data
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
     * Get setup denda configuration
     */
    public function getSetupDenda($tahunID, $prodiID = null, $programID = null, $tahunMasuk = null)
    {
        $query = DB::table('setup_denda')
            ->where('TahunID', $tahunID);

        if ($prodiID) {
            $query->whereRaw("(setup_denda.ProdiID=? OR setup_denda.ProdiID=0)", [$prodiID]);
        }
        if ($programID) {
            $query->whereRaw("(setup_denda.ProgramID=? OR setup_denda.ProgramID=0)", [$programID]);
        }
        if ($tahunMasuk) {
            $query->whereRaw("(setup_denda.TahunMasuk=? OR setup_denda.TahunMasuk=0)", [$tahunMasuk]);
        }

        $query->orderBy('Hari', 'DESC');
        $result = $query->get();

        $setupDenda = [];
        foreach ($result as $row) {
            $setupDenda[$row->JenisBiayaID][$row->ProgramID][$row->ProdiID][$row->TahunMasuk][$row->Hari] = $row;
        }

        return $setupDenda;
    }

    /**
     * Calculate denda amount
     */
    public function calculateDenda($tagihan, $setupDenda, $datenow)
    {
        $tanggalDuedate = date('Y-m-d', strtotime($tagihan->Duedate));
        $absDiff = 0;
        $jumlahDenda = 0;

        if ($datenow > $tanggalDuedate && empty($tagihan->HistoriDendaID)) {
            $earlier = new \DateTime($tanggalDuedate);
            $later = new \DateTime($datenow);
            $absDiff = $later->diff($earlier)->format("%a");

            $jb = $tagihan->JenisBiayaID;
            $prodiID = $tagihan->ProdiID;
            $programID = $tagihan->ProgramID;
            $tahunMasuk = $tagihan->TahunMasuk;

            $dendaJb = $setupDenda[$jb] ?? null;
            $dendaJbProgram = null;
            $dendaJbProgramProdi = null;
            $dendaJbProgramProdiTahunmasuk = null;

            if ($dendaJb) {
                $dendaJbProgram = $dendaJb[$programID] ?? $dendaJb[0] ?? null;
            }

            if ($dendaJbProgram) {
                $dendaJbProgramProdi = $dendaJbProgram[$prodiID] ?? $dendaJbProgram[0] ?? null;
            }

            if ($dendaJbProgramProdi) {
                $dendaJbProgramProdiTahunmasuk = $dendaJbProgramProdi[$tahunMasuk] ?? $dendaJbProgramProdi[0] ?? null;
            }

            if ($dendaJbProgramProdiTahunmasuk) {
                foreach ($dendaJbProgramProdiTahunmasuk as $hari => $rowDenda) {
                    if ($absDiff > $hari) {
                        if ($rowDenda->Tipe == 'persen') {
                            $jumlahDenda = $tagihan->Jumlah * $rowDenda->Jumlah / 100;
                        } else if ($rowDenda->Tipe == 'nominal') {
                            $jumlahDenda = $rowDenda->Jumlah;
                        }
                        break;
                    }
                }
            }
        } else {
            $jumlahDenda = $tagihan->JumlahDenda ?? 0;
        }

        return [
            'abs_diff' => $absDiff,
            'jumlah_denda' => $jumlahDenda,
            'tanggal_duedate' => $tanggalDuedate
        ];
    }

    /**
     * Posting denda untuk single tagihan
     */
    public function postingDenda($tagihanID, $jumlahDenda, $tahunID)
    {
        try {
            $tagihan = DB::table('tagihan_mahasiswa')->where('ID', $tagihanID)->first();

            if (!$tagihan) {
                return ['success' => false, 'message' => 'Tagihan tidak ditemukan'];
            }

            if ($jumlahDenda <= 0) {
                return ['success' => false, 'message' => 'Jumlah denda tidak valid'];
            }

            // Update tagihan with denda
            $updated = DB::table('tagihan_mahasiswa')
                ->where('ID', $tagihanID)
                ->update([
                    'Jumlah' => $tagihan->Jumlah + $jumlahDenda,
                    'Sisa' => $tagihan->Sisa + $jumlahDenda,
                    'Update' => date('Y-m-d h:i:s')
                ]);

            if ($updated) {
                // Insert histori
                DB::table('histori_generate_denda')->insert([
                    'MhswID' => $tagihan->MhswID,
                    'TagihanMahasiswaID' => $tagihanID,
                    'JenisBiayaID' => $tagihan->JenisBiayaID,
                    'NamaBiaya' => DB::table('jenisbiaya')->where('ID', $tagihan->JenisBiayaID)->value('Nama'),
                    'TahunID' => $tahunID,
                    'Jumlah' => $jumlahDenda,
                    'createdAt' => date('Y-m-d H:i:s'),
                    'UserID' => auth()->id() ?? 0
                ]);

                return ['success' => true, 'message' => 'Data berhasil diproses!.'];
            } else {
                return ['success' => false, 'message' => 'Data gagal diproses!.'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Posting denda untuk multiple tagihan
     */
    public function postingDendaAll($selectedItems, $tahunID)
    {
        $success = 0;

        foreach ($selectedItems as $item) {
            $exp = explode('_', $item);
            $tagihanID = $exp[0];
            $jumlahDenda = $exp[1] ?? 0;

            $result = $this->postingDenda($tagihanID, $jumlahDenda, $tahunID);

            if ($result['success']) {
                $success++;
            }
        }

        return [
            'success' => $success > 0,
            'message' => $success . ' Data berhasil diproses!.'
        ];
    }
}
