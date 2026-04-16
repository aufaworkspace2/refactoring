<?php

namespace App\Services;

use App\Models\RekomendasiBatalRencanaStudi;
use App\Models\Mahasiswa;
use App\Models\Tahun;
use App\Models\Kurikulum;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;

class ApprovalRekomendasiBatalRencanaStudiService
{
    /**
     * Search data dengan filter untuk Prodi/Keuangan
     */
    public function searchData($type, $limit, $offset, $filters)
    {
        extract($filters);

        $query = DB::table('mahasiswa')
            ->select(
                'mahasiswa.ID as MhswID',
                'program.Nama as namaProgram',
                DB::raw('CONCAT(jenjang.Nama, " | ", programstudi.Nama) as namaProdi'),
                DB::raw('"" as namaKelas'),
                'kurikulum.Nama as namaKurikulum',
                'mahasiswa.TahunMasuk as tahunMasuk',
                'mahasiswa.StatusPindahan as statusPindahan',
                'rekomendasi_batal_rencanastudi.NPM as npm',
                'rekomendasi_batal_rencanastudi.NamaMahasiswa as namaMahasiswa',
                'rekomendasi_batal_rencanastudi.*'
            )
            ->join('rekomendasi_batal_rencanastudi', 'mahasiswa.ID', '=', 'rekomendasi_batal_rencanastudi.MhswID')
            ->leftJoin('program', 'program.ID', '=', 'mahasiswa.ProgramID')
            ->leftJoin('programstudi', 'programstudi.ID', '=', 'mahasiswa.ProdiID')
            ->leftJoin('jenjang', 'programstudi.JenjangID', '=', 'jenjang.ID')
            ->leftJoin('kurikulum', 'kurikulum.ID', '=', 'mahasiswa.KurikulumID');

        // Apply filters
        if (!empty($programID)) {
            $query->where('mahasiswa.ProgramID', $programID);
        }
        if (!empty($prodiID)) {
            $query->where('mahasiswa.ProdiID', $prodiID);
        }
        if (!empty($tahunMasuk)) {
            $query->where('mahasiswa.TahunMasuk', $tahunMasuk);
        }
        if (!empty($kurikulumID)) {
            $query->where('mahasiswa.KurikulumID', $kurikulumID);
        }
        if (!empty($kelasID)) {
            $query->where('mahasiswa.KelasID', $kelasID);
        }
        if (!empty($statusPindahan)) {
            $query->where('mahasiswa.StatusPindahan', $statusPindahan);
        }
        if (!empty($tahunID)) {
            $query->where('rekomendasi_batal_rencanastudi.TahunID', $tahunID);
        }
        if (!empty($keyword)) {
            $query->where(function($q) use ($keyword) {
                $q->where('mahasiswa.NPM', 'like', "%{$keyword}%")
                  ->orWhere('mahasiswa.Nama', 'like', "%{$keyword}%");
            });
        }

        // Type-specific filter
        if ($type == 'keuangan') {
            $query->where('rekomendasi_batal_rencanastudi.rekomendasi_prodi', 1);
        }

        // Status pembatalan filter (only for keuangan)
        if (isset($statusPembatalan) && $statusPembatalan !== '' && $statusPembatalan !== null) {
            $query->where('rekomendasi_batal_rencanastudi.StatusBatal', $statusPembatalan);
        }

        $query->orderBy('rekomendasi_batal_rencanastudi.NPM', 'ASC');

        $total = $query->count();
        $data = $query->offset($offset)->limit($limit)->get();

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * Rekomendasi Prodi (Single)
     */
    public function rekomendasiProdi($id, $rekomendasiProdi, $opsiKeuangan = 1)
    {
        try {
            DB::beginTransaction();

            $getPR = RekomendasiBatalRencanaStudi::find($id);

            if (!$getPR) {
                return ['success' => false, 'message' => 'Data tidak ditemukan'];
            }

            $update = [
                'rekomendasi_prodi' => $rekomendasiProdi,
                'LastUpdateUserID' => auth()->id() ?? 0
            ];

            $akses = false;

            if ($opsiKeuangan == 2) {
                $cek = $this->deleteKrs($getPR->rencanastudiID, $getPR->HapusNilai);
                if ($cek['status'] == 1) {
                    $update['rekomendasi_keuangan'] = 3;
                    $update['StatusBatal'] = 1;
                    $akses = true;
                }
            } else {
                $akses = true;
            }

            if ($akses) {
                $getPR->update($update);

                log_akses('Update', 'Melakukan Approval Rekomendasi Pembatalan KRS ' . $getPR->NPM . ' MKKode ' . $getPR->MKKode . ' Tahun ' . $getPR->KodeTahun);

                DB::commit();
                return ['success' => true, 'message' => 'Data berhasil diproses!.'];
            } else {
                DB::rollBack();
                return ['success' => false, 'message' => 'Data gagal diproses!.'];
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Rekomendasi Prodi (Bulk)
     */
    public function rekomendasiProdiAll($selectedIds, $rekomendasiProdi, $opsiKeuangan = 1)
    {
        $success = 0;
        $failed = 0;

        foreach ($selectedIds as $id) {
            $result = $this->rekomendasiProdi($id, $rekomendasiProdi, $opsiKeuangan);
            if ($result['success']) {
                $success++;
            } else {
                $failed++;
            }
        }

        $message = $success . ' Data berhasil diproses!.';
        if ($failed > 0) {
            $message .= ' ' . $failed . ' Data gagal diproses!.';
        }

        return [
            'success' => $success > 0,
            'message' => $message
        ];
    }

    /**
     * Rekomendasi Keuangan (Single)
     */
    public function rekomendasiKeuangan($id, $rekomendasiKeuangan)
    {
        try {
            DB::beginTransaction();

            $getPR = RekomendasiBatalRencanaStudi::find($id);

            if (!$getPR) {
                return ['success' => false, 'message' => 'Data tidak ditemukan'];
            }

            $akses = false;
            $update = [];

            if ($rekomendasiKeuangan == 1) {
                $cek = $this->deleteKrs($getPR->rencanastudiID, $getPR->HapusNilai);
                if ($cek['status'] == 1) {
                    $update['StatusBatal'] = 1;
                    $akses = true;
                }
            } else {
                $akses = true;
            }

            if ($akses) {
                $update['rekomendasi_keuangan'] = $rekomendasiKeuangan;
                $update['LastUpdateUserID'] = auth()->id() ?? 0;

                $getPR->update($update);

                DB::commit();
                return ['success' => true, 'message' => 'Data berhasil diproses!.'];
            } else {
                DB::rollBack();
                return ['success' => false, 'message' => 'Data gagal diproses!.'];
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Rekomendasi Keuangan (Bulk)
     */
    public function rekomendasiKeuanganAll($selectedIds, $rekomendasiKeuangan)
    {
        $success = 0;
        $failed = 0;

        foreach ($selectedIds as $id) {
            $result = $this->rekomendasiKeuangan($id, $rekomendasiKeuangan);
            if ($result['success']) {
                $success++;
            } else {
                $failed++;
            }
        }

        $message = $success . ' Data berhasil diproses!.';
        if ($failed > 0) {
            $message .= ' ' . $failed . ' Data gagal diproses!.';
        }

        return [
            'success' => $success > 0,
            'message' => $message
        ];
    }

    /**
     * Delete KRS - Complex logic from CI
     */
    protected function deleteKrs($id, $statusHapusNilai = '0')
    {
        try {
            DB::beginTransaction();

            $dataKRS = DB::table('rencanastudi')->where('ID', $id)->first();

            if (!$dataKRS) {
                return ['status' => 0, 'message' => 'Data KRS tidak ditemukan'];
            }

            $tahunID = $dataKRS->TahunID;
            $mhs = $dataKRS->MhswID;
            $matkul = DB::table('detailkurikulum')->where('ID', $dataKRS->DetailKurikulumID)->first();

            // Update skripsi if exists
            $cekSkripsi = DB::table('skripsi')->where('KRSID', $dataKRS->ID)->get();
            foreach ($cekSkripsi as $rowSkripsi) {
                DB::table('skripsi')->where('ID', $rowSkripsi->ID)->update(['KRSID' => null]);
            }

            // Delete from rencanastudi
            $prosesDelete = DB::table('rencanastudi')->where('ID', $id)->delete();

            if ($prosesDelete) {
                log_rencanastudi($dataKRS, 'hapus', 'ais', auth()->id() ?? 0);

                // Update tagihan SKS
                // update_tagihan_sks($mhs, $tahunID, '', 1);

                // Delete nilai if StatusHapusNilai = 1
                if ($statusHapusNilai == '1') {
                    $dataNilai = DB::table('nilai')->where('rencanastudiID', $dataKRS->ID)->first();
                    if ($dataNilai) {
                        log_nilai($dataNilai, "hapus", "ais", auth()->id() ?? 0);
                        DB::table('nilai')->where('rencanastudiID', $id)->delete();
                    }
                }

                // Delete rencanastudi_waiting
                DB::table('rencanastudi_waiting')->where('rencanastudiID', $id)->delete();

                // Check events for PKRS
                $cekKrs = DB::table('events_detail')
                    ->join('tahun', 'tahun.ID', '=', 'events_detail.TahunID')
                    ->where('events_detail.EventID', '2')
                    ->where('tahun.ProsesBuka', '1')
                    ->whereRaw('"' . date('Y-m-d') . '" BETWEEN DATE(events_detail.TglMulai) AND DATE(events_detail.TglSelesai)')
                    ->first();

                $cekTanggalPkrs = DB::table('events_detail')
                    ->join('tahun', 'tahun.ID', '=', 'events_detail.TahunID')
                    ->where('events_detail.EventID', '6')
                    ->where('tahun.ProsesBuka', '1')
                    ->whereRaw('"' . date('Y-m-d') . '" BETWEEN DATE(events_detail.TglMulai) AND DATE(events_detail.TglSelesai)')
                    ->first();

                if (!empty($cekTanggalPkrs->ID)) {
                    $cekPkrs = DB::table('rencanastudi_perubahan')->where('rencanastudiID', $id)->first();
                    if (!empty($cekPkrs->id)) {
                        DB::table('rencanastudi_perubahan')->where('id', $cekPkrs->id)->update(['tipe' => '2']);
                    } else {
                        DB::table('rencanastudi_perubahan')->insert([
                            'mhswID' => $mhs,
                            'detailkurikulumID' => $dataKRS->DetailKurikulumID,
                            'rencanastudiID' => $id,
                            'tahunID' => $dataKRS->TahunID,
                            'jadwalID' => $dataKRS->JadwalID,
                            'approval' => $dataKRS->approval,
                            'tipe' => '3',
                            'createAt' => date('Y-m-d H:i:s'),
                            'userID' => auth()->id() ?? 0
                        ]);
                    }
                }

                if (!empty($cekKrs->ID)) {
                    $cekData = DB::table('rencanastudi_totalsks')
                        ->where('mhswID', $mhs)
                        ->where('tahunID', $dataKRS->TahunID)
                        ->first();

                    $totalSKS = DB::table('detailkurikulum')->where('ID', $dataKRS->DetailKurikulumID)->value('TotalSKS');

                    if (!empty($cekData->id)) {
                        DB::table('rencanastudi_totalsks')
                            ->where('id', $cekData->id)
                            ->update(['totalSKS' => $cekData->totalSKS - $totalSKS]);
                    }
                }

                // Delete peserta rombel
                $cekRombel = DB::table('rombel')->where('JadwalID', $dataKRS->JadwalID)->first();
                if (!empty($cekRombel->ID)) {
                    $deleted = DB::table('peserta_rombel')
                        ->where('GroupPesertaID', $cekRombel->ID)
                        ->where('MhswID', $dataKRS->MhswID)
                        ->delete();

                    if ($deleted) {
                        $jumlahPeserta = (!empty($cekRombel->JmlPeserta) ? $cekRombel->JmlPeserta : 0) - 1;
                        $jumlahPesertaClear = ($jumlahPeserta <= 0 ? 0 : $jumlahPeserta);

                        DB::table('rombel')->where('ID', $cekRombel->ID)->update(['JmlPeserta' => $jumlahPesertaClear]);
                        DB::table('jadwal')->where('ID', $cekRombel->JadwalID)->update(['JumlahPeserta' => $jumlahPesertaClear]);
                    }
                }

                DB::commit();
                return [
                    'status' => 1,
                    'message' => 'Mata Kuliah ' . ($matkul->MKKode ?? '') . ' - ' . ($matkul->Nama ?? '') . ' berhasil dibatalkan!.'
                ];
            } else {
                DB::rollBack();
                return [
                    'status' => 0,
                    'message' => 'Mata Kuliah ' . ($matkul->MKKode ?? '') . ' - ' . ($matkul->Nama ?? '') . ' gagal dibatalkan!.'
                ];
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 0, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Save catatan
     */
    public function saveCatatan($id, $tipe, $catatan)
    {
        $update = [];
        if ($tipe == 'prodi') {
            $update['catatan_prodi'] = $catatan;
        } else if ($tipe == 'keuangan') {
            $update['catatan_keuangan'] = $catatan;
        }

        return DB::table('rekomendasi_batal_rencanastudi')
            ->where('ID', $id)
            ->update($update);
    }

    /**
     * Get Kurikulum
     */
    public function getKurikulum($programID, $prodiID)
    {
        $query = DB::table('kurikulum')
            ->select('ID as kurikulumID', 'Nama as namaKurikulum')
            ->where(function($q) use ($programID, $prodiID) {
                if (!empty($programID)) {
                    $q->whereRaw('FIND_IN_SET(' . $programID . ', kurikulum.Program)');
                }
                if (!empty($prodiID)) {
                    $q->whereRaw('FIND_IN_SET(' . $prodiID . ', kurikulum.Prodi2)');
                }
            })
            ->orderBy('kurikulum.Nama', 'ASC');

        return $query->get();
    }

    /**
     * Get Tahun Masuk
     */
    public function getTahunMasuk($programID, $prodiID, $kurikulumID = '')
    {
        $query = DB::table('mahasiswa')
            ->select(DB::raw('DISTINCT TahunMasuk as tahunMasuk'))
            ->where('TahunMasuk', '!=', '');

        if (!empty($programID)) {
            $query->where('ProgramID', $programID);
        }
        if (!empty($prodiID)) {
            $query->where('ProdiID', $prodiID);
        }
        if (!empty($kurikulumID)) {
            $query->where('KurikulumID', $kurikulumID);
        }

        return $query->orderBy('TahunMasuk', 'DESC')->get();
    }

    /**
     * Get Kelas
     */
    public function getKelas($prodiID)
    {
        return DB::table('kelas')
            ->select('ID as kelasID', 'Nama as namaKelas')
            ->where(function($q) use ($prodiID) {
                $q->where('ProdiID', $prodiID)
                  ->orWhere('ProdiID', 0);
            })
            ->orderBy('kelas.Nama', 'ASC')
            ->get();
    }

    /**
     * Get Data Nilai
     */
    public function getDataNilai($rencanastudiID)
    {
        $dataNilai = DB::table('nilai')
            ->where('rencanastudiID', $rencanastudiID)
            ->get();

        return [
            'status' => count($dataNilai) > 0,
            'message' => count($dataNilai) > 0 ? 'Data berhasil ditemukan.' : 'Data gagal ditemukan.',
            'data' => $dataNilai
        ];
    }
}
