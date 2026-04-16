<?php

namespace App\Services;

use App\Models\{
    DraftTagihanMahasiswa,
    DraftTagihanMahasiswaSemester,
    DraftTagihanMahasiswaTermin,
    DraftTagihanMahasiswaDetail,
    DraftTagihanMahasiswaTerminSemester,
    DraftTagihanMahasiswaTerminTotal,
    TagihanMahasiswa,
    TagihanMahasiswaSemester,
    TagihanMahasiswaTermin,
    TagihanMahasiswaDetail,
    TagihanMahasiswaTerminSemester,
    TagihanMahasiswaTerminTotal,
    Mahasiswa,
    Tahun
};
use Illuminate\Support\Facades\DB;

class PostingTagihanService
{
    /**
     * Search data posting tagihan dengan filter
     */
    public function searchData($limit, $offset, $filters)
    {
        extract($filters);

        $query = DB::table('mahasiswa as mhs')
            ->select(
                'mhs.ID as MhswID',
                'mhs.ID',
                'p.Nama as namaProgram',
                DB::raw('CONCAT(j.Nama, " | ", ps.Nama) as namaProdi'),
                'mhs.TahunMasuk as tahunMasuk',
                'mhs.jalur_pmb as jalurPMB',
                'mhs.StatusPindahan as statusPindahan',
                'mhs.NPM as npm',
                'mhs.Nama as namaMahasiswa',
                'dt.Periode',
                't.TahunID as KodeTahun',
                'dt.StatusPosting',
                DB::raw('IF(dt.ID IS NOT NULL, 1, 0) as statusDraft'),
                DB::raw('SUM(dt.Jumlah) as jumlahBiaya')
            )
            ->leftJoin('draft_tagihan_mahasiswa as dt', function($join) use ($tahunID) {
                $join->on('mhs.ID', '=', 'dt.MhswID')
                     ->on('dt.Periode', '=', DB::raw("'{$tahunID}'"));
            })
            ->leftJoin('tahun as t', 't.ID', '=', 'dt.Periode')
            ->leftJoin('program as p', 'p.ID', '=', 'mhs.ProgramID')
            ->leftJoin('programstudi as ps', 'ps.ID', '=', 'mhs.ProdiID')
            ->leftJoin('jenjang as j', 'j.ID', '=', 'ps.JenjangID')
            ->where('mhs.jenis_mhsw', 'mhsw')
            ->whereIn('mhs.StatusMhswID', ['2', '3']);

        // Apply filters
        if (!empty($programID)) {
            $query->where('mhs.ProgramID', $programID);
        }
        if (!empty($prodiID)) {
            $query->where('mhs.ProdiID', $prodiID);
        }
        if (!empty($tahunMasuk)) {
            $query->where('mhs.TahunMasuk', $tahunMasuk);
        }
        if (!empty($jalurPendaftaran)) {
            $query->where('mhs.jalur_pmb', $jalurPendaftaran);
        }
        if (!empty($jenisPendaftaran)) {
            $query->where('mhs.StatusPindahan', $jenisPendaftaran);
        }
        if (!empty($keyword)) {
            $query->whereRaw('(mhs.NPM LIKE "%' . $keyword . '%" OR mhs.Nama LIKE "%' . $keyword . '%")');
        }
        if (!empty($SemesterMasuk)) {
            $query->where('mhs.SemesterMasuk', $SemesterMasuk);
        }
        if (!empty($GelombangKe)) {
            $query->where('mhs.GelombangKe', $GelombangKe);
        }

        if (!empty($statusPosting)) {
            $valStatusPosting = '';
            if ($statusPosting == 'sudah') {
                $valStatusPosting = 1;
            } else if ($statusPosting == 'belum') {
                $valStatusPosting = 0;
            }
            $query->where('dt.StatusPosting', $valStatusPosting);
        }

        if (!empty($statusDraft)) {
            if ($statusDraft == 'sudah') {
                $query->whereNotNull('dt.ID');
            } else {
                $query->whereNull('dt.ID');
            }
        }

        $query->groupBy('mhs.ID')
              ->orderBy('mhs.NPM', 'ASC');

        // countData - hitung total records dengan subquery
        $countQuery = DB::table('mahasiswa as mhs')
            ->leftJoin('draft_tagihan_mahasiswa as dt', function($join) use ($tahunID) {
                $join->on('mhs.ID', '=', 'dt.MhswID')
                     ->on('dt.Periode', '=', DB::raw("'{$tahunID}'"));
            })
            ->where('mhs.jenis_mhsw', 'mhsw')
            ->whereIn('mhs.StatusMhswID', ['2', '3']);

        if (!empty($programID)) {
            $countQuery->where('mhs.ProgramID', $programID);
        }
        if (!empty($prodiID)) {
            $countQuery->where('mhs.ProdiID', $prodiID);
        }
        if (!empty($tahunMasuk)) {
            $countQuery->where('mhs.TahunMasuk', $tahunMasuk);
        }
        if (!empty($jalurPendaftaran)) {
            $countQuery->where('mhs.jalur_pmb', $jalurPendaftaran);
        }
        if (!empty($jenisPendaftaran)) {
            $countQuery->where('mhs.StatusPindahan', $jenisPendaftaran);
        }
        if (!empty($keyword)) {
            $countQuery->whereRaw('(mhs.NPM LIKE "%' . $keyword . '%" OR mhs.Nama LIKE "%' . $keyword . '%")');
        }
        if (!empty($SemesterMasuk)) {
            $countQuery->where('mhs.SemesterMasuk', $SemesterMasuk);
        }
        if (!empty($GelombangKe)) {
            $countQuery->where('mhs.GelombangKe', $GelombangKe);
        }
        if (!empty($statusPosting)) {
            $valStatusPosting = ($statusPosting == 'sudah') ? 1 : 0;
            $countQuery->where('dt.StatusPosting', $valStatusPosting);
        }
        if (!empty($statusDraft)) {
            if ($statusDraft == 'sudah') {
                $countQuery->whereNotNull('dt.ID');
            } else {
                $countQuery->whereNull('dt.ID');
            }
        }

        $total = $countQuery->distinct()->count('mhs.ID');

        $data = $query->offset($offset)
            ->limit($limit)
            ->get();

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * Process posting/unposting untuk single mahasiswa
     */
    public function processPosting($mhsId, $tahunId, $posting)
    {
        $mahasiswa = Mahasiswa::find($mhsId);

        if (!$mahasiswa) {
            return ['success' => false, 'message' => 'Mahasiswa tidak ditemukan'];
        }

        if ($posting == 1) {
            return $this->postingDraftTagihan($mhsId, $tahunId);
        } else {
            return $this->hapusDraftTagihan($mhsId, $tahunId);
        }
    }

    /**
     * Process posting/unposting untuk multiple mahasiswa
     */
    public function processPostingAll($selectedIds, $tahunId, $posting)
    {
        $success = 0;
        $listGagal = [];

        foreach ($selectedIds as $id) {
            $mahasiswa = Mahasiswa::find($id);

            if (!$mahasiswa) {
                continue;
            }

            if ($posting == 1) {
                // Cek apakah sudah ada tagihan dengan jenis biaya yang sama
                $cekDraftTagihan = DraftTagihanMahasiswa::where('MhswID', $id)
                    ->where('Periode', $tahunId)
                    ->get();

                $listJb = $cekDraftTagihan->pluck('JenisBiayaID')->toArray();

                $jumTagihan = 0;
                if (count($listJb) > 0) {
                    $jumTagihan = TagihanMahasiswa::whereIn('JenisBiayaID', $listJb)
                        ->where('MhswID', $id)
                        ->where('Periode', $tahunId)
                        ->count();
                }

                if ($jumTagihan == 0) {
                    $result = $this->postingDraftTagihan($id, $tahunId);
                    if ($result['success']) {
                        $success++;
                    } else {
                        $listGagal[] = "NIM {$mahasiswa->NPM} - {$result['message']}";
                    }
                } else {
                    $listGagal[] = "NIM {$mahasiswa->NPM} - Sudah Ada Tagihan Yang tergenerate dengan komponen biaya yang sama";
                }
            } else {
                $result = $this->hapusDraftTagihan($id, $tahunId);
                if ($result['success']) {
                    $success++;
                }
            }
        }

        $message = '';
        if ($success > 0) {
            $message = "{$success} Data berhasil diproses!.";
            if (count($listGagal) > 0) {
                $message .= "<br>" . count($listGagal) . ' Data gagal diproses!.';
            }
        } else {
            $message = 'Data gagal diproses!.';
        }

        if (count($listGagal) > 0) {
            $message .= '<br>';
            foreach ($listGagal as $gagal) {
                $message .= $gagal . ' <br>';
            }
        }

        return [
            'success' => $success > 0,
            'message' => $message
        ];
    }

    /**
     * Posting draft tagihan - convert draft to active tagihan
     */
    protected function postingDraftTagihan($mhsId, $tahunId)
    {
        try {
            DB::beginTransaction();

            $mhs = Mahasiswa::find($mhsId);
            $rowTahun = Tahun::find($tahunId);
            $rand = mt_rand(100, 999);
            $noInvoiceGenerate = date("Y") . "-KUL" . $rowTahun->TahunID . "-" . $mhs->NPM . "-" . $rand;

            // Step 1: Insert/Update Tagihan Semester
            $getDraftSemester = DraftTagihanMahasiswaSemester::where('MhswID', $mhsId)
                ->where('Periode', $tahunId)
                ->get();

            $idTagihanSemesterByDraft = [];

            foreach ($getDraftSemester as $rowDraftSemester) {
                $cekTagihanSemester = TagihanMahasiswaSemester::where('MhswID', $mhsId)
                    ->where('Periode', $tahunId)
                    ->first();

                if (!$cekTagihanSemester) {
                    $tagihanSemester = TagihanMahasiswaSemester::create([
                        'DraftTagihanMahasiswaSemesterID' => $rowDraftSemester->ID,
                        'BiayaSemesterID' => $rowDraftSemester->BiayaSemesterID,
                        'MhswID' => $mhs->ID,
                        'ProdiID' => $mhs->ProdiID,
                        'ProgramID' => $mhs->ProgramID,
                        'Periode' => $tahunId,
                        'Semester' => $rowDraftSemester->Semester,
                        'TotalTagihan' => $rowDraftSemester->TotalTagihan,
                        'JumlahDiskon' => $rowDraftSemester->JumlahDiskon,
                        'TotalCicilan' => 0,
                        'Jumlah' => $rowDraftSemester->Jumlah,
                        'Sisa' => $rowDraftSemester->Jumlah,
                        'createdAt' => now(),
                        'updatedAt' => now(),
                        'UserID' => auth()->id() ?? 0
                    ]);
                    $tagihanMahasiswaSemesterID = $tagihanSemester->ID;
                } else {
                    $cekTagihanSemester->update([
                        'DraftTagihanMahasiswaSemesterID' => $rowDraftSemester->ID,
                        'BiayaSemesterID' => $rowDraftSemester->BiayaSemesterID
                    ]);
                    $tagihanMahasiswaSemesterID = $cekTagihanSemester->ID;
                }

                $idTagihanSemesterByDraft[$rowDraftSemester->ID] = $tagihanMahasiswaSemesterID;
            }

            // Step 2: Insert/Update Tagihan Mahasiswa
            $getDraft = DraftTagihanMahasiswa::where('MhswID', $mhsId)
                ->where('Periode', $tahunId)
                ->get();

            $idTagihanByDraft = [];
            $arrTahunID = [];

            foreach ($getDraft as $rowDraft) {
                $cekTagihan = TagihanMahasiswa::where('MhswID', $mhsId)
                    ->where('Periode', $tahunId)
                    ->where('JenisBiayaID', $rowDraft->JenisBiayaID)
                    ->first();

                if (!$cekTagihan) {
                    $tagihan = TagihanMahasiswa::create([
                        'MasterDiskonID' => $rowDraft->MasterDiskonID,
                        'DraftTagihanMahasiswaID' => $rowDraft->ID,
                        'BiayaID' => $rowDraft->BiayaID,
                        'TagihanMahasiswaSemesterID' => $idTagihanSemesterByDraft[$rowDraft->DraftTagihanMahasiswaSemesterID] ?? null,
                        'Periode' => $tahunId,
                        'ProgramID' => $mhs->ProgramID,
                        'ProdiID' => $mhs->ProdiID,
                        'TahunID' => $rowTahun->TahunID,
                        'JenisBiayaID' => $rowDraft->JenisBiayaID,
                        'JenisMahasiswa' => 'mhsw',
                        'MhswID' => $mhs->ID,
                        'NPM' => $mhs->NPM,
                        'NoInvoice' => $noInvoiceGenerate,
                        'TotalTagihan' => $rowDraft->TotalTagihan,
                        'JumlahDiskon' => $rowDraft->JumlahDiskon,
                        'Jumlah' => $rowDraft->Jumlah,
                        'Sisa' => $rowDraft->Jumlah,
                        'TotalCicilan' => 0,
                        'Lunas' => 0,
                        'DueDate' => null,
                        'Tanggal' => date("Y-m-d h:i:s"),
                        'Update' => date("Y-m-d h:i:s"),
                        'TanggalTagihan' => date("Y-m-d"),
                        'DikalikanSKS' => '0',
                        'UserCreate' => auth()->id() ?? 0
                    ]);
                    $tagihanMahasiswaID = $tagihan->ID;
                    $idTagihanByDraft[$rowDraft->ID] = $tagihanMahasiswaID;
                    $arrTahunID[$tahunId] = $tahunId;
                } else {
                    $tagihanMahasiswaID = $cekTagihan->ID;
                    $idTagihanByDraft[$rowDraft->ID] = $tagihanMahasiswaID;
                    $arrTahunID[$cekTagihan->Periode] = $cekTagihan->Periode;
                }
            }

            // Step 3: Insert/Update Tagihan Termin
            $getDraftTermin = DraftTagihanMahasiswaTermin::where('MhswID', $mhsId)
                ->where('Periode', $tahunId)
                ->get();

            $idTagihanTerminByDraft = [];

            foreach ($getDraftTermin as $rowDraftTermin) {
                $cekTagihanTermin = TagihanMahasiswaTermin::where('MhswID', $mhsId)
                    ->where('Periode', $tahunId)
                    ->where('JenisBiayaID', $rowDraftTermin->JenisBiayaID)
                    ->where('TerminKe', $rowDraftTermin->TerminKe)
                    ->first();

                if (!$cekTagihanTermin && isset($idTagihanByDraft[$rowDraftTermin->DraftTagihanMahasiswaID])) {
                    $tagihanTermin = TagihanMahasiswaTermin::create([
                        'DraftTagihanMahasiswaTerminID' => $rowDraftTermin->ID,
                        'TagihanMahasiswaID' => $idTagihanByDraft[$rowDraftTermin->DraftTagihanMahasiswaID],
                        'BiayaTerminID' => $rowDraftTermin->BiayaTerminID,
                        'ProgramID' => $mhs->ProgramID,
                        'Periode' => $tahunId,
                        'ProdiID' => $mhs->ProdiID,
                        'JenisBiayaID' => $rowDraftTermin->JenisBiayaID,
                        'MhswID' => $mhs->ID,
                        'TotalTagihan' => $rowDraftTermin->TotalTagihan,
                        'JumlahDiskon' => $rowDraftTermin->JumlahDiskon,
                        'TotalCicilan' => 0,
                        'Sisa' => $rowDraftTermin->Jumlah,
                        'Jumlah' => $rowDraftTermin->Jumlah,
                        'Semester' => $rowDraftTermin->Semester,
                        'TerminKe' => $rowDraftTermin->TerminKe,
                        'Tanggal' => date('Y-m-d h:i:s'),
                        'Update' => date('Y-m-d h:i:s'),
                        'UserID' => auth()->id() ?? 0
                    ]);
                    $idTagihanTerminByDraft[$rowDraftTermin->ID] = $tagihanTermin->ID;
                } elseif ($cekTagihanTermin) {
                    $idTagihanTerminByDraft[$rowDraftTermin->ID] = $cekTagihanTermin->ID;
                }
            }

            // Step 4: Insert/Update Tagihan Detail
            $getDraftDetail = DraftTagihanMahasiswaDetail::where('MhswID', $mhsId)
                ->where('Periode', $tahunId)
                ->get();

            $idTagihanDetailByDraft = [];

            foreach ($getDraftDetail as $rowDraftDetail) {
                $cekTagihanDetail = TagihanMahasiswaDetail::where('MhswID', $mhsId)
                    ->where('Periode', $tahunId)
                    ->where('JenisBiayaID', $rowDraftDetail->JenisBiayaID)
                    ->where('JenisBiayaID_Detail', $rowDraftDetail->JenisBiayaID_Detail)
                    ->first();

                if (!$cekTagihanDetail && isset($idTagihanByDraft[$rowDraftDetail->DraftTagihanMahasiswaID])) {
                    $tagihanDetail = TagihanMahasiswaDetail::create([
                        'DraftTagihanMahasiswaDetailID' => $rowDraftDetail->ID,
                        'TagihanMahasiswaID' => $idTagihanByDraft[$rowDraftDetail->DraftTagihanMahasiswaID],
                        'BiayaDetailID' => $rowDraftDetail->BiayaDetailID,
                        'ProgramID' => $mhs->ProgramID,
                        'Periode' => $tahunId,
                        'ProdiID' => $mhs->ProdiID,
                        'JenisBiayaID' => $rowDraftDetail->JenisBiayaID,
                        'JenisBiayaID_Detail' => $rowDraftDetail->JenisBiayaID_Detail,
                        'MhswID' => $mhs->ID,
                        'TotalTagihan' => $rowDraftDetail->TotalTagihan,
                        'JumlahDiskon' => $rowDraftDetail->JumlahDiskon,
                        'TotalCicilan' => 0,
                        'Sisa' => $rowDraftDetail->Jumlah,
                        'Jumlah' => $rowDraftDetail->Jumlah,
                        'Semester' => $rowDraftDetail->Semester,
                        'Tanggal' => date('Y-m-d h:i:s'),
                        'Update' => date('Y-m-d h:i:s'),
                        'UserID' => auth()->id() ?? 0
                    ]);
                    $idTagihanDetailByDraft[$rowDraftDetail->ID] = $tagihanDetail->ID;
                } elseif ($cekTagihanDetail) {
                    $idTagihanDetailByDraft[$rowDraftDetail->ID] = $cekTagihanDetail->ID;
                }
            }

            // Step 5: Insert/Update Tagihan Termin Semester
            $getDraftTerminSemester = DraftTagihanMahasiswaTerminSemester::where('MhswID', $mhsId)
                ->where('Periode', $tahunId)
                ->get();

            $idTagihanTerminSemesterByDraft = [];

            foreach ($getDraftTerminSemester as $rowDraftTerminSemester) {
                $cekTagihanTerminSemester = TagihanMahasiswaTerminSemester::where('MhswID', $mhsId)
                    ->where('Periode', $tahunId)
                    ->where('TerminKe', $rowDraftTerminSemester->TerminKe)
                    ->first();

                if (!$cekTagihanTerminSemester) {
                    $expDraftTermin = array_filter(explode(",", $rowDraftTerminSemester->DraftTagihanMahasiswaTerminID_list));
                    if (count($expDraftTermin) == 0) {
                        $expDraftTermin[] = 0;
                    }

                    $listTagihanTermin = TagihanMahasiswaTermin::whereIn('DraftTagihanMahasiswaTerminID', $expDraftTermin)
                        ->where('MhswID', $mhsId)
                        ->where('Periode', $tahunId)
                        ->get();

                    $arrTagihanMahasiswaTerminID = $listTagihanTermin->pluck('ID')->toArray();

                    $tagihanTerminSemester = TagihanMahasiswaTerminSemester::create([
                        'TagihanMahasiswaSemesterID' => $idTagihanSemesterByDraft[$rowDraftTerminSemester->DraftTagihanMahasiswaSemesterID] ?? null,
                        'BiayaTerminSemesterID' => $rowDraftTerminSemester->BiayaTerminSemesterID,
                        'DraftTagihanMahasiswaTerminSemesterID' => $rowDraftTerminSemester->ID,
                        'MhswID' => $mhs->ID,
                        'ProdiID' => $mhs->ProdiID,
                        'ProgramID' => $mhs->ProgramID,
                        'Periode' => $tahunId,
                        'Semester' => $rowDraftTerminSemester->Semester,
                        'Jumlah' => $rowDraftTerminSemester->Jumlah,
                        'Sisa' => $rowDraftTerminSemester->Jumlah,
                        'TerminKe' => $rowDraftTerminSemester->TerminKe,
                        'TagihanMahasiswaTerminID_list' => implode(",", $arrTagihanMahasiswaTerminID),
                        'createdAt' => now(),
                        'updatedAt' => now(),
                        'UserID' => auth()->id() ?? 0
                    ]);
                    $idTagihanTerminSemesterByDraft[$rowDraftTerminSemester->ID] = $tagihanTerminSemester->ID;
                } else {
                    $idTagihanTerminSemesterByDraft[$rowDraftTerminSemester->ID] = $cekTagihanTerminSemester->ID;
                }
            }

            // Step 6: Sync jumlah tagihan semester
            $getTagihanSemester = TagihanMahasiswaSemester::where('MhswID', $mhsId)
                ->where('Periode', $tahunId)
                ->first();

            if ($getTagihanSemester) {
                $tagihanMahasiswaSemesterID = $getTagihanSemester->ID;

                $jumAllSemester = TagihanMahasiswa::select(
                    DB::raw('SUM(IFNULL(TotalTagihan,0)) as sum_total_tagihan'),
                    DB::raw('SUM(IFNULL(Jumlah,0)) as sum_jumlah'),
                    DB::raw('SUM(IFNULL(Sisa,0)) as sum_sisa'),
                    DB::raw('SUM(IFNULL(TotalCicilan,0)) as sum_totalcicilan'),
                    DB::raw('SUM(IFNULL(JumlahDiskon,0)) as sum_diskon')
                )
                    ->where('TagihanMahasiswaSemesterID', $tagihanMahasiswaSemesterID)
                    ->first();

                $updSemester = [
                    'TotalTagihan' => $jumAllSemester->sum_total_tagihan,
                    'Jumlah' => $jumAllSemester->sum_total_tagihan - $jumAllSemester->sum_diskon,
                    'Sisa' => ($jumAllSemester->sum_total_tagihan - $jumAllSemester->sum_diskon) - $jumAllSemester->sum_totalcicilan,
                    'JumlahDiskon' => $jumAllSemester->sum_diskon,
                    'TotalCicilan' => $jumAllSemester->sum_totalcicilan
                ];

                TagihanMahasiswaSemester::where('ID', $tagihanMahasiswaSemesterID)->update($updSemester);
            }

            // Step 7: Update StatusPosting to 1 for all draft records
            if (count($idTagihanByDraft) > 0) {
                // Update status posting - pakai array_keys langsung (ID draft)
                $idDraftSemester = array_keys($idTagihanSemesterByDraft);
                $idDraft = array_keys($idTagihanByDraft);
                $idDraftTermin = array_keys($idTagihanTerminByDraft);
                $idDraftDetail = array_keys($idTagihanDetailByDraft);
                $idDraftTerminSemester = array_keys($idTagihanTerminSemesterByDraft);

                if (count($idDraftSemester) > 0) {
                    DraftTagihanMahasiswaSemester::whereIn('ID', $idDraftSemester)
                        ->update(['StatusPosting' => 1]);
                }
                if (count($idDraft) > 0) {
                    DraftTagihanMahasiswa::whereIn('ID', $idDraft)
                        ->update(['StatusPosting' => 1]);
                }
                if (count($idDraftTermin) > 0) {
                    DraftTagihanMahasiswaTermin::whereIn('ID', $idDraftTermin)
                        ->update(['StatusPosting' => 1]);
                }
                if (count($idDraftDetail) > 0) {
                    DraftTagihanMahasiswaDetail::whereIn('ID', $idDraftDetail)
                        ->update(['StatusPosting' => 1]);
                }
                if (count($idDraftTerminSemester) > 0) {
                    DraftTagihanMahasiswaTerminSemester::whereIn('ID', $idDraftTerminSemester)
                        ->update(['StatusPosting' => 1]);
                }

                DB::commit();
                return ['success' => true, 'message' => 'Data berhasil diproses!'];
            } else {
                DB::rollBack();
                return ['success' => false, 'message' => 'Tidak ada data draft yang diproses'];
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Hapus draft tagihan
     */
    protected function hapusDraftTagihan($mhsId, $tahunId)
    {
        try {
            DB::beginTransaction();

            // Delete draft detail
            DraftTagihanMahasiswaDetail::where('MhswID', $mhsId)
                ->where('Periode', $tahunId)
                ->delete();

            // Delete draft termin
            DraftTagihanMahasiswaTermin::where('MhswID', $mhsId)
                ->where('Periode', $tahunId)
                ->delete();

            // Delete draft tagihan
            DraftTagihanMahasiswa::where('MhswID', $mhsId)
                ->where('Periode', $tahunId)
                ->delete();

            // Delete draft termin semester
            DraftTagihanMahasiswaTerminSemester::where('MhswID', $mhsId)
                ->where('Periode', $tahunId)
                ->delete();

            // Handle draft semester
            $draftTagihanSemester = DraftTagihanMahasiswaSemester::where('MhswID', $mhsId)
                ->where('Periode', $tahunId)
                ->first();

            if ($draftTagihanSemester) {
                $cekSemester = TagihanMahasiswaSemester::where('DraftTagihanMahasiswaSemesterID', $draftTagihanSemester->ID)
                    ->first();

                if (!$cekSemester) {
                    // Delete if not referenced
                    DraftTagihanMahasiswaSemester::where('MhswID', $mhsId)
                        ->where('Periode', $tahunId)
                        ->delete();
                } else {
                    // Update to zero
                    DraftTagihanMahasiswaSemester::where('MhswID', $mhsId)
                        ->where('Periode', $tahunId)
                        ->update([
                            'Jumlah' => 0,
                            'TotalTagihan' => 0,
                            'JumlahDiskon' => 0
                        ]);
                }

                // Handle draft termin total
                $draftTagihanTerminTotal = DB::table('draft_tagihan_mahasiswa_termin_total')
                    ->whereRaw("FIND_IN_SET(?, DraftTagihanMahasiswaSemesterID_list)", [$draftTagihanSemester->ID])
                    ->where('MhswID', $mhsId)
                    ->first();

                if ($draftTagihanTerminTotal) {
                    $arrTagihanMahasiswaSemesterID = explode(",", $draftTagihanTerminTotal->DraftTagihanMahasiswaSemesterID_list);
                    if (($key = array_search($draftTagihanSemester->ID, $arrTagihanMahasiswaSemesterID)) !== false) {
                        unset($arrTagihanMahasiswaSemesterID[$key]);
                    }

                    $sumJumlahSemester = DraftTagihanMahasiswaSemester::whereIn('ID', $arrTagihanMahasiswaSemesterID)
                        ->where('MhswID', $mhsId)
                        ->sum('Jumlah');

                    if ($sumJumlahSemester > 0) {
                        DB::table('draft_tagihan_mahasiswa_termin_total')
                            ->where('ID', $draftTagihanTerminTotal->ID)
                            ->update([
                                'Jumlah' => $draftTagihanTerminTotal->Jumlah,
                                'DraftTagihanMahasiswaSemesterID_list' => implode(",", $arrTagihanMahasiswaSemesterID),
                                'UserID' => auth()->id() ?? 0
                            ]);
                    } else {
                        $cekTerminTotal = TagihanMahasiswaTerminTotal::where('DraftTagihanMahasiswaTerminTotalID', $draftTagihanTerminTotal->ID)
                            ->first();

                        if (!$cekTerminTotal) {
                            DB::table('draft_tagihan_mahasiswa_termin_total')
                                ->where('ID', $draftTagihanTerminTotal->ID)
                                ->delete();
                        } else {
                            DB::table('draft_tagihan_mahasiswa_termin_total')
                                ->where('ID', $draftTagihanTerminTotal->ID)
                                ->update(['Jumlah' => 0]);
                        }
                    }
                }
            }

            DB::commit();
            return ['success' => true, 'message' => 'Data berhasil diproses!'];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Generate draft tagihan untuk multiple mahasiswa
     */
    public function generateDraftAll($selectedIds, $tahunId)
    {
        try {
            DB::beginTransaction();

            $tahun = Tahun::find($tahunId);
            $kodeTahun = $tahun->TahunID;
            $tanggalTagihan = date('Y-m-d');

            $count = 0;
            $listBiayaBelumDiSet = [];

            $mahasiswaList = Mahasiswa::whereIn('ID', $selectedIds)
                ->where('jenis_mhsw', 'mhsw')
                ->get();

            foreach ($mahasiswaList as $mahasiswa) {
                $jumlah = [];
                $jumlahTagihan = [];
                $jumlahDiskon = [];
                $dikalikanSKS = [];
                $biaya = [];

                // Get biaya mahasiswa
                $queryBiaya = DB::table('biaya')
                    ->select('ID', 'Jumlah', 'JumlahTagihan', 'JumlahDiskon', 'DikalikanSKS', 'MasterDiskonID_list', 'JenisBiayaID')
                    ->where('KodeTahun', $kodeTahun)
                    ->where('ProgramID', $mahasiswa->ProgramID)
                    ->where('ProdiID', $mahasiswa->ProdiID)
                    ->where('TahunMasuk', $mahasiswa->TahunMasuk)
                    ->where('JenisPendaftaran', $mahasiswa->StatusPindahan)
                    ->where('JalurPendaftaran', $mahasiswa->jalur_pmb)
                    ->where('SemesterMasuk', $mahasiswa->SemesterMasuk)
                    ->where('GelombangKe', $mahasiswa->GelombangKe)
                    ->where('JenisMahasiswa', 1)
                    ->get();

                foreach ($queryBiaya as $dataBiaya) {
                    $biaya[$dataBiaya->JenisBiayaID] = $dataBiaya->JenisBiayaID;
                    $jumlah[$dataBiaya->JenisBiayaID] = $dataBiaya->Jumlah;
                    $jumlahTagihan[$dataBiaya->JenisBiayaID] = $dataBiaya->JumlahTagihan;
                    $jumlahDiskon[$dataBiaya->JenisBiayaID] = $dataBiaya->JumlahDiskon;
                    $dikalikanSKS[$dataBiaya->JenisBiayaID] = $dataBiaya->DikalikanSKS;
                }

                if (empty($biaya)) {
                    $listBiayaBelumDiSet[] = $mahasiswa->NPM;
                }

                // Get setup diskon
                $cekSetupDiskon = DB::table('setup_mahasiswa_diskon_sampai_lulus')
                    ->where(function($q) use ($tahunId) {
                        $q->where('PerTahunID', 0)
                          ->orWhereRaw("FIND_IN_SET(?, ListTahunID)", [$tahunId]);
                    })
                    ->where('MhswID', $mahasiswa->ID)
                    ->where('StatusAktif', 1)
                    ->first();

                $listDiskon = [];
                if ($cekSetupDiskon) {
                    $arrListDiskon = json_decode($cekSetupDiskon->ListDiskon, true);

                    foreach ($arrListDiskon as $rowListDiskon) {
                        if (!isset($listDiskon[$rowListDiskon['JenisBiayaID']])) {
                            $listDiskon[$rowListDiskon['JenisBiayaID']] = $rowListDiskon['ListMasterDiskonID'];
                        } else {
                            $arrDisk = $listDiskon[$rowListDiskon['JenisBiayaID']];
                            foreach ($rowListDiskon['ListMasterDiskonID'] as $idListDiskon) {
                                $arrDisk[] = $idListDiskon;
                            }
                            $arrDisk = array_unique($arrDisk);
                            $listDiskon[$rowListDiskon['JenisBiayaID']] = $arrDisk;
                        }
                    }
                }

                $rand = mt_rand(100, 999);
                $noInvoiceGenerate = date("Y") . "-" . $kodeTahun . "-" . $mahasiswa->NPM . "-" . $rand;

                // Process each biaya
                foreach ($biaya as $jb) {
                    $jumlahVal = $jumlah[$jb];

                    if ($jumlahVal) {
                        // Get biaya detail
                        if ($jb != 33) {
                            $cekBiaya = DB::table('biaya')
                                ->select('ID', 'Jumlah', 'JumlahTagihan', 'JumlahDiskon', 'MasterDiskonID_list')
                                ->where('KodeTahun', $kodeTahun)
                                ->where('ProgramID', $mahasiswa->ProgramID)
                                ->where('ProdiID', $mahasiswa->ProdiID)
                                ->where('TahunMasuk', $mahasiswa->TahunMasuk)
                                ->where('JenisPendaftaran', $mahasiswa->StatusPindahan)
                                ->where('JalurPendaftaran', $mahasiswa->jalur_pmb)
                                ->where('SemesterMasuk', $mahasiswa->SemesterMasuk)
                                ->where('GelombangKe', $mahasiswa->GelombangKe)
                                ->where('JenisMahasiswa', '1')
                                ->where('JenisBiayaID', $jb)
                                ->first();
                        } else {
                            $cekBiaya = (object) [
                                'ID' => null,
                                'Jumlah' => $jumlahVal,
                                'JumlahTagihan' => $jumlahVal,
                                'JumlahDiskon' => 0,
                                'MasterDiskonID_list' => ''
                            ];
                        }

                        // Check if draft already exists
                        $cekAda = DraftTagihanMahasiswa::select('ID')
                            ->where('Periode', $tahunId)
                            ->where('JenisBiayaID', $jb)
                            ->where('MhswID', $mahasiswa->ID)
                            ->first();

                        if (!$cekAda) {
                            $nilaiTagihan = $jumlahVal;
                            $nilaiTagihanReal = $jumlahVal;
                            $jumlahDiskonVal = 0;

                            // Apply diskon
                            if (isset($listDiskon[$jb]) && count($listDiskon[$jb]) > 0) {
                                $getDiskon = DB::table('master_diskon')
                                    ->whereIn('ID', $listDiskon[$jb])
                                    ->orderByRaw("FIELD(JenisDiskon, 'potong_dari_total', 'potong_dari_sisa')")
                                    ->get();

                                foreach ($getDiskon as $raw) {
                                    if ($raw->Tipe == 'nominal') {
                                        if ($raw->Jumlah > $nilaiTagihan) {
                                            $jumlahDiskonVal += $nilaiTagihan;
                                            $nilaiTagihan = 0;
                                        } else {
                                            $nilaiTagihan -= $raw->Jumlah;
                                            $jumlahDiskonVal += $raw->Jumlah;
                                        }
                                    } else if ($raw->Tipe == 'persen') {
                                        if ($raw->JenisDiskon == 'potong_dari_sisa') {
                                            $tempPersen = ($nilaiTagihan * $raw->Jumlah) / 100;
                                        } else {
                                            $tempPersen = ($nilaiTagihanReal * $raw->Jumlah) / 100;
                                        }
                                        if ($tempPersen > $nilaiTagihan) {
                                            $jumlahDiskonVal += $nilaiTagihan;
                                            $nilaiTagihan = 0;
                                        } else {
                                            $nilaiTagihan -= $tempPersen;
                                            $jumlahDiskonVal += $tempPersen;
                                        }
                                    }
                                }
                            }

                            // Insert draft tagihan
                            $insert = [
                                'DraftTagihanMahasiswaSemesterID' => null,
                                'BiayaID' => $cekBiaya->ID,
                                'MasterDiskonID' => implode(',', $listDiskon[$jb] ?? []),
                                'ProgramID' => $mahasiswa->ProgramID,
                                'NoInvoice' => $noInvoiceGenerate,
                                'Periode' => $tahunId,
                                'ProdiID' => $mahasiswa->ProdiID,
                                'JenisBiayaID' => $jb,
                                'JenisMahasiswa' => 'mhsw',
                                'TahunID' => $kodeTahun,
                                'NPM' => $mahasiswa->NPM,
                                'MhswID' => $mahasiswa->ID,
                                'TotalTagihan' => $nilaiTagihanReal,
                                'JumlahDiskon' => $jumlahDiskonVal,
                                'Jumlah' => $nilaiTagihan,
                                'Tanggal' => $tanggalTagihan,
                                'Update' => date('Y-m-d h:i:s'),
                                'TanggalTagihan' => $tanggalTagihan,
                                'UserCreate' => auth()->id() ?? 0
                            ];

                            $draftTagihanId = DB::table('draft_tagihan_mahasiswa')->insertGetId($insert);
                            $count++;

                            // Insert draft termin
                            if ($draftTagihanId) {
                                if ($jb != 33) {
                                    $listBiayaTermin = DB::table('biaya_termin')
                                        ->where('BiayaID', $cekBiaya->ID)
                                        ->get();
                                } else {
                                    $listBiayaTermin = collect([(object) [
                                        'ID' => null,
                                        'Jumlah' => $jumlahVal,
                                        'JumlahTagihan' => $jumlahVal,
                                        'JumlahDiskon' => 0,
                                        'TerminKe' => 1
                                    ]]);
                                }

                                foreach ($listBiayaTermin as $valueTermin) {
                                    $jumlahDiskonTermin = 0;
                                    $jumlahTermin = $valueTermin->JumlahTagihan;

                                    if ($jumlahDiskonVal > 0) {
                                        $jumlahDiskonTermin = ($jumlahTermin / $nilaiTagihanReal) * $jumlahDiskonVal;
                                        if ($jumlahDiskonTermin > 0) {
                                            $jumlahTermin = $jumlahTermin - $jumlahDiskonTermin;
                                        }
                                    }

                                    $inputTagihanTermin = [
                                        'DraftTagihanMahasiswaID' => $draftTagihanId,
                                        'BiayaTerminID' => $valueTermin->ID,
                                        'ProgramID' => $mahasiswa->ProgramID,
                                        'Periode' => $tahunId,
                                        'ProdiID' => $mahasiswa->ProdiID,
                                        'JenisBiayaID' => $jb,
                                        'MhswID' => $mahasiswa->ID,
                                        'TotalTagihan' => $valueTermin->JumlahTagihan,
                                        'JumlahDiskon' => $jumlahDiskonTermin,
                                        'Jumlah' => $jumlahTermin,
                                        'TerminKe' => $valueTermin->TerminKe,
                                        'Tanggal' => date('Y-m-d h:i:s'),
                                        'Update' => date('Y-m-d h:i:s'),
                                        'UserID' => auth()->id() ?? 0
                                    ];

                                    DB::table('draft_tagihan_mahasiswa_termin')->insert($inputTagihanTermin);
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();

            $message = $count . ' data berhasil dibuat!';
            if (count($listBiayaBelumDiSet) > 0) {
                $message .= '|' . count($listBiayaBelumDiSet) . ' mahasiswa belum memiliki setting biaya';
            }

            return ['success' => true, 'message' => $message];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
