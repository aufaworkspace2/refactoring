<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\TagihanMahasiswa;
use App\Models\TagihanMahasiswaSemester;
use App\Models\TagihanMahasiswaTermin;
use App\Models\TagihanMahasiswaDetail;
use App\Models\Tahun;
use Illuminate\Support\Facades\DB;

class InputTagihanManualService
{
    /**
     * Search mahasiswa berdasarkan filter
     */
    public function searchMahasiswa($filters)
    {
        extract($filters);

        $tahunAktif = DB::table('tahun')->where('ProsesBuka', 1)->first();
        $tahunDipilih = DB::table('tahun')->where('ID', $TahunID ?? null)->first();

        $query = DB::table('mahasiswa')
            ->select('mahasiswa.ID', 'mahasiswa.NPM', 'mahasiswa.Nama')
            ->whereIn('mahasiswa.StatusMhswID', ['2', '3'])
            ->where('mahasiswa.jenis_mhsw', 'mhsw')
            ->orderBy('mahasiswa.NPM', 'ASC')
            ->groupBy('mahasiswa.ID');

        // Check for tidak aktif status if semester 1 or 2
        if ($tahunDipilih && ($tahunDipilih->Semester == 1 || $tahunDipilih->Semester == 2)) {
            $tahunIDSebelumnya = $this->getTahunIdSebelumnya($tahunDipilih->TahunID);
            $tahunSebelumnya = DB::table('tahun')->where('TahunID', $tahunIDSebelumnya)->first();

            if ($tahunSebelumnya) {
                $query->select('mahasiswa.ID', 'mahasiswa.NPM', 'mahasiswa.Nama',
                    DB::raw('COUNT(keteranganstatusmahasiswa.ID) AS tidakAktif'))
                    ->leftJoin('keteranganstatusmahasiswa', function($join) use ($tahunSebelumnya) {
                        $join->on('keteranganstatusmahasiswa.MhswID', '=', 'mahasiswa.ID')
                             ->where('keteranganstatusmahasiswa.StatusMahasiswaID', 6)
                             ->where('keteranganstatusmahasiswa.TahunID', $tahunSebelumnya->ID);
                    });
            }
        }

        if (!empty($ProgramID)) {
            $query->where('mahasiswa.ProgramID', $ProgramID);
        }
        if (!empty($ProdiID)) {
            $query->where('mahasiswa.ProdiID', $ProdiID);
        }
        if (!empty($Angkatan)) {
            $query->where('mahasiswa.TahunMasuk', $Angkatan);
        }
        if (!empty($JenisPendaftaran)) {
            $query->where('mahasiswa.StatusPindahan', $JenisPendaftaran);
        }
        if (!empty($JalurPendaftaran)) {
            $query->where('mahasiswa.jalur_pmb', $JalurPendaftaran);
        }
        if (!empty($SemesterMasuk)) {
            $query->where('mahasiswa.SemesterMasuk', $SemesterMasuk);
        }
        if (!empty($KelasID)) {
            $query->where('mahasiswa.KelasID', $KelasID);
        }
        if (!empty($KelasIH)) {
            $kelasIHArray = is_array($KelasIH) ? $KelasIH : explode(',', $KelasIH);
            $query->whereIn('mahasiswa.GroupkelasID', $kelasIHArray);
        }

        $result = $query->get();

        $temp = '';
        $tempTidakAktif = '';
        $jumlahTidakAktif = 0;
        $jumlah = count($result);

        if ($jumlah > 0) {
            $noTidakAktif = 0;
            foreach ($result as $value) {
                $temp .= '<option value="' . $value->ID . '">' . $value->NPM . ' | ' . $value->Nama . '</option>';

                if (isset($value->tidakAktif) && $value->tidakAktif > 0) {
                    $jumlahTidakAktif++;
                    $noTidakAktif++;
                    $tempTidakAktif .= '<tr>';
                    $tempTidakAktif .= '<td style="text-align:center">' . $noTidakAktif . '</td>';
                    $tempTidakAktif .= '<td style="text-align:center">' . $value->NPM . '</td>';
                    $tempTidakAktif .= '<td>' . $value->Nama . '</td>';
                    $tempTidakAktif .= '</tr>';
                }
            }
        } else {
            $temp = '<option value="">Maaf, mahasiswa tidak ditemukan</option>';
        }

        return [
            'temp' => $temp,
            'jumlah' => $jumlah,
            'temp_tidak_aktif' => $tempTidakAktif,
            'jumlahTidakAktif' => $jumlahTidakAktif
        ];
    }

    /**
     * Get angkatan list
     */
    public function getAngkatanList()
    {
        $angkatanTerakhir = DB::table('mahasiswa')
            ->select('TahunMasuk')
            ->groupBy('TahunMasuk')
            ->orderBy('TahunMasuk', 'DESC')
            ->first();

        $option = '';
        $startYear = $angkatanTerakhir ? $angkatanTerakhir->TahunMasuk : date('Y');

        for ($n = 0; $n <= 20; $n++) {
            $tahun = $startYear - $n;
            $option .= '<option value="' . $tahun . '">' . $tahun . '</option>';
        }

        return $option;
    }

    /**
     * Get content biaya berdasarkan filter
     */
    public function getContentBiaya($filters)
    {
        extract($filters);

        // Get jenis biaya yang sesuai - sama persis dengan CI3
        $queryJenisBiaya = DB::table('jenisbiaya')
            ->whereRaw("(FIND_IN_SET(?, Program) > 0 OR Program = '0')", [$ProgramID])
            ->whereRaw("(FIND_IN_SET(?, Prodi) > 0 OR Prodi = '0')", [$ProdiID])
            ->whereRaw("(FIND_IN_SET(?, TahunMasuk) > 0 OR TahunMasuk = '0')", [$Angkatan])
            ->where('StatusHide', '0')
            ->whereRaw("(TipeMhsw = 'mhsw' OR ID = 32)")
            ->whereIn('frekuensi', ['Satu Kali', 'Per Semester', 'Variable'])
            ->orderBy('Urut', 'ASC')
            ->get();

        $biaya = [];
        $getDetail = [];
        $tmpBiayaDet = [];

        foreach ($queryJenisBiaya as $row) {
            // Get detail biaya
            $details = DB::table('jenisbiaya_detail')
                ->where('JenisBiayaID', $row->ID)
                ->get();

            $getDetail[$row->ID] = $details;

            // Simpan tmp_biaya_det untuk detail
            foreach ($details as $detail) {
                $tmpBiayaDet[$row->ID][$detail->ID] = '';
            }

            $biaya[$row->ID] = [
                'ID' => $row->ID,
                'Nama' => $row->Nama,
                'Jumlah' => '',
                'JumlahTagihan' => '',
                'JumlahDiskon' => '',
                'DikalikanSKS' => 0
            ];
        }

        // Get master diskon
        $diskon = DB::table('master_diskon')->get();

        return [
            'biaya' => $biaya,
            'get_detail' => $getDetail,
            'diskon' => $diskon,
            'tmp_biaya_det' => $tmpBiayaDet,
            'ProgramID' => $ProgramID,
            'ProdiID' => $ProdiID,
            'TahunMasuk' => $Angkatan,
            'TahunID' => $TahunID,
            'JenisPendaftaran' => $JenisPendaftaran,
            'JalurPendaftaran' => $JalurPendaftaran,
            'SemesterMasuk' => $SemesterMasuk,
            'KelasID' => $KelasID ?? '',
            'KelasIH' => $KelasIH ?? ''
        ];
    }

    /**
     * Input tagihan manual
     */
    public function inputTagihanManual($postData)
    {
        try {
            $tipe = $postData['tipe'];
            $TahunID = $postData['PeriodeID'];
            $ProgramID = $postData['ProgramID'];
            $ProdiID = $postData['ProdiID'];
            $Angkatan = $postData['Angkatan'];
            $JenisPendaftaran = $postData['JenisPendaftaran'];
            $JalurPendaftaran = $postData['JalurPendaftaran'];
            $SemesterMasuk = $postData['SemesterMasuk'];
            $KelasID = $postData['KelasID'] ?? '';
            $KelasIH = $postData['KelasIH'] ?? '';
            $TanggalTagihan = $postData['TanggalTagihan'];

            $tahunDipilih = DB::table('tahun')->where('ID', $TahunID)->first();
            if (!$tahunDipilih) {
                return ['status' => '0', 'message' => 'Tahun tidak ditemukan'];
            }
            $kodeTahun = $tahunDipilih->TahunID;

            // Get mahasiswa list
            $query = DB::table('mahasiswa')
                ->select('mahasiswa.ID')
                ->whereIn('mahasiswa.StatusMhswID', ['2', '3'])
                ->where('mahasiswa.jenis_mhsw', 'mhsw')
                ->groupBy('mahasiswa.ID');

            if (!empty($ProgramID)) $query->where('mahasiswa.ProgramID', $ProgramID);
            if (!empty($ProdiID)) $query->where('mahasiswa.ProdiID', $ProdiID);
            if (!empty($Angkatan)) $query->where('mahasiswa.TahunMasuk', $Angkatan);
            if (!empty($JenisPendaftaran)) $query->where('mahasiswa.StatusPindahan', $JenisPendaftaran);
            if (!empty($JalurPendaftaran)) $query->where('mahasiswa.jalur_pmb', $JalurPendaftaran);
            if (!empty($SemesterMasuk)) $query->where('mahasiswa.SemesterMasuk', $SemesterMasuk);
            if (!empty($KelasID)) $query->where('mahasiswa.KelasID', $KelasID);
            if (!empty($KelasIH)) {
                $kelasIHArray = is_array($KelasIH) ? $KelasIH : explode(',', $KelasIH);
                $query->whereIn('mahasiswa.GroupkelasID', $kelasIHArray);
            }

            if ($tipe == '2' && !empty($postData['mhswID'])) {
                $query->whereIn('mahasiswa.ID', $postData['mhswID']);
            }

            $mahasiswaList = $query->get();

            $count = 0;
            $kondisi = [0 => 0, 1 => 0];
            $tempTagihan = [];
            $no = 1;
            $tampung = ['Total' => 0];

            foreach ($mahasiswaList as $mhsw) {
                $mahasiswa = DB::table('mahasiswa')->where('ID', $mhsw->ID)->first();
                if (!$mahasiswa) continue;

                // Get diskon setup
                $setupDiskon = DB::table('setup_mahasiswa_diskon_sampai_lulus')
                    ->where(function($q) use ($TahunID) {
                        $q->where('PerTahunID', 0)
                          ->orWhereRaw("FIND_IN_SET(?, ListTahunID) != 0", [$TahunID]);
                    })
                    ->where('MhswID', $mahasiswa->ID)
                    ->where('StatusAktif', 1)
                    ->first();

                $listDiskon = [];
                if ($setupDiskon && $setupDiskon->ListDiskon) {
                    $arrListDiskon = json_decode($setupDiskon->ListDiskon, true) ?? [];
                    foreach ($arrListDiskon as $rowListDiskon) {
                        if (!isset($listDiskon[$rowListDiskon['JenisBiayaID']])) {
                            $listDiskon[$rowListDiskon['JenisBiayaID']] = $rowListDiskon['ListMasterDiskonID'];
                        } else {
                            $arrDisk = $listDiskon[$rowListDiskon['JenisBiayaID']];
                            foreach ($rowListDiskon['ListMasterDiskonID'] as $idListDiskon) {
                                $arrDisk[] = $idListDiskon;
                            }
                            $listDiskon[$rowListDiskon['JenisBiayaID']] = array_unique($arrDisk);
                        }
                    }
                }

                $rand = mt_rand(100, 999);
                $noInvoiceGenerate = date("Y") . "-" . $kodeTahun . "-" . $mahasiswa->NPM . "-" . $rand;
                $totalNilaiTagihan = 0;
                $idTagihanAis = [];

                // Loop biaya
                if (isset($postData['biaya']) && is_array($postData['biaya'])) {
                    foreach ($postData['biaya'] as $jb) {
                        $biaya = $postData['jumlah'][$jb] ?? 0;

                        if ($biaya > 0) {
                            // Cek apakah sudah ada tagihan
                            $cekAda = DB::table('tagihan_mahasiswa')
                                ->where('Periode', $TahunID)
                                ->where('JenisBiayaID', $jb)
                                ->where('MhswID', $mahasiswa->ID)
                                ->first();

                            if (!$cekAda) {
                                $nilaiTagihan = $biaya;
                                $nilaiTagihanReal = $biaya;
                                $jumlahDiskon = 0;

                                // Apply diskon
                                if (isset($listDiskon[$jb]) && count($listDiskon[$jb]) > 0) {
                                    $getDiskon = DB::table('master_diskon')
                                        ->whereIn('ID', $listDiskon[$jb])
                                        ->orderByRaw("FIELD(JenisDiskon, 'potong_dari_total', 'potong_dari_sisa')")
                                        ->orderByRaw("FIELD(Tipe, 'nominal', 'persen')")
                                        ->get();

                                    foreach ($getDiskon as $raw) {
                                        if ($raw->Tipe == 'nominal') {
                                            if ($raw->Jumlah > $nilaiTagihan) {
                                                $jumlahDiskon += $nilaiTagihan;
                                                $nilaiTagihan = 0;
                                            } else {
                                                $nilaiTagihan -= $raw->Jumlah;
                                                $jumlahDiskon += $raw->Jumlah;
                                            }
                                        } else if ($raw->Tipe == 'persen') {
                                            if ($raw->JenisDiskon == 'potong_dari_sisa') {
                                                $tempPersen = ($nilaiTagihan * $raw->Jumlah) / 100;
                                            } else {
                                                $tempPersen = ($nilaiTagihanReal * $raw->Jumlah) / 100;
                                            }
                                            if ($tempPersen > $nilaiTagihan) {
                                                $jumlahDiskon += $nilaiTagihan;
                                                $nilaiTagihan = 0;
                                            } else {
                                                $nilaiTagihan -= $tempPersen;
                                                $jumlahDiskon += $tempPersen;
                                            }
                                        }
                                    }
                                }

                                // Insert tagihan
                                $tagihanID = DB::table('tagihan_mahasiswa')->insertGetId([
                                    'TagihanMahasiswaSemesterID' => null,
                                    'BiayaID' => null,
                                    'MasterDiskonID' => null,
                                    'ProgramID' => $mahasiswa->ProgramID,
                                    'NoInvoice' => $noInvoiceGenerate,
                                    'Periode' => $TahunID,
                                    'ProdiID' => $mahasiswa->ProdiID,
                                    'JenisBiayaID' => $jb,
                                    'JenisMahasiswa' => 'mhsw',
                                    'TahunID' => $kodeTahun,
                                    'NPM' => $mahasiswa->NPM,
                                    'MhswID' => $mahasiswa->ID,
                                    'TotalTagihan' => $nilaiTagihanReal,
                                    'JumlahDiskon' => $jumlahDiskon,
                                    'Jumlah' => $nilaiTagihan,
                                    'TotalCicilan' => 0,
                                    'Sisa' => $nilaiTagihan,
                                    'Tanggal' => $TanggalTagihan,
                                    'Update' => date('Y-m-d h:i:s'),
                                    'TanggalTagihan' => $TanggalTagihan,
                                    'UserCreate' => auth()->id() ?? 0,
                                    'Lunas' => 0,
                                    'DikalikanSKS' => 0,
                                    'DueDate' => null
                                ]);

                                $idTagihanAis[] = $tagihanID;
                                $count++;

                                // Insert termin
                                DB::table('tagihan_mahasiswa_termin')->insert([
                                    'DraftTagihanMahasiswaTerminID' => null,
                                    'TagihanMahasiswaID' => $tagihanID,
                                    'BiayaTerminID' => null,
                                    'ProgramID' => $mahasiswa->ProgramID,
                                    'Periode' => $TahunID,
                                    'ProdiID' => $mahasiswa->ProdiID,
                                    'JenisBiayaID' => $jb,
                                    'MhswID' => $mahasiswa->ID,
                                    'TotalTagihan' => $nilaiTagihanReal,
                                    'JumlahDiskon' => $jumlahDiskon,
                                    'TotalCicilan' => 0,
                                    'Sisa' => $nilaiTagihan,
                                    'Jumlah' => $nilaiTagihan,
                                    'Semester' => 0,
                                    'TerminKe' => 1,
                                    'Tanggal' => date('Y-m-d h:i:s'),
                                    'Update' => date('Y-m-d h:i:s'),
                                    'UserID' => auth()->id() ?? 0
                                ]);

                                // Insert detail jika ada
                                if (isset($postData['jumlahdetail'][$jb]) && is_array($postData['jumlahdetail'][$jb])) {
                                    foreach ($postData['jumlahdetail'][$jb] as $jbDetail => $jumlahDetail) {
                                        if ($jumlahDetail > 0) {
                                            $nilaiDetailReal = $jumlahDetail;
                                            $nilaiDetailAkumulasi = $jumlahDetail;
                                            $jumlahDiskonDetail = 0;

                                            DB::table('tagihan_mahasiswa_detail')->insert([
                                                'DraftTagihanMahasiswaDetailID' => null,
                                                'TagihanMahasiswaID' => $tagihanID,
                                                'BiayaDetailID' => null,
                                                'ProgramID' => $mahasiswa->ProgramID,
                                                'Periode' => $TahunID,
                                                'ProdiID' => $mahasiswa->ProdiID,
                                                'JenisBiayaID' => $jb,
                                                'JenisBiayaID_Detail' => $jbDetail,
                                                'MhswID' => $mahasiswa->ID,
                                                'JenisMahasiswa' => 'mhsw',
                                                'TotalTagihan' => $nilaiDetailReal,
                                                'JumlahDiskon' => $jumlahDiskonDetail,
                                                'Jumlah' => $nilaiDetailAkumulasi,
                                                'TotalCicilan' => 0,
                                                'Sisa' => $nilaiDetailAkumulasi,
                                                'Semester' => 0,
                                                'Tanggal' => $TanggalTagihan,
                                                'Update' => date('Y-m-d h:i:s'),
                                                'UserID' => auth()->id() ?? 0
                                            ]);
                                        }
                                    }
                                }

                                $totalNilaiTagihan += $nilaiTagihan;
                                $kondisi[0]++;
                            } else {
                                $kondisi[1]++;
                                $jenisBiayaNama = DB::table('jenisbiaya')->where('ID', $jb)->value('Nama');
                                if ($jenisBiayaNama) {
                                    $tempTagihan[] = $jenisBiayaNama;
                                }
                                $idTagihanAis[] = $cekAda->ID;
                            }
                        }
                    }
                }

                // Tampung data untuk ditampilkan
                $tampung['NoInvoice'][$no] = $noInvoiceGenerate;
                $tampung['NPM'][$no] = $mahasiswa->NPM;
                $tampung['Nama'][$no] = $mahasiswa->Nama;
                $tampung['TotalTagihan'][$no] = number_format($totalNilaiTagihan, 2, ',', '.');
                $tampung['TglTransaksi'][$no] = \Carbon\Carbon::parse($TanggalTagihan)->format('d/m/Y');
                $tampung['Total'] = ($tampung['Total'] ?? 0) + $totalNilaiTagihan;
                $no++;

                // Sinkronisasi tagihan semester
                if (count($idTagihanAis) > 0) {
                    $this->sinkronTagihanMahasiswa($mahasiswa->ID, $TahunID, $idTagihanAis);
                }
            }

            // Simpan ke tmp table
            try {
                DB::table('tmp_input_tagihan_manual')->truncate();
            } catch (\Exception $e) {
                // Table might not exist, ignore
            }

            $loop = $no - 1;
            for ($i = 1; $i <= $loop; $i++) {
                try {
                    DB::table('tmp_input_tagihan_manual')->insert([
                        'A' => $tampung['NoInvoice'][$i] ?? '',
                        'B' => $tampung['NPM'][$i] ?? '',
                        'C' => $tampung['Nama'][$i] ?? '',
                        'D' => $tampung['TotalTagihan'][$i] ?? '',
                        'E' => $tampung['TglTransaksi'][$i] ?? ''
                    ]);
                } catch (\Exception $e) {
                    // Ignore tmp table errors
                }
            }

            $label = '';
            if ($kondisi[1] > 0) {
                $tempTagihan = array_unique($tempTagihan);
                $label = '||' . $kondisi[1] . ' data tagihan gagal digenerate!. Karena anda telah melakukan generate tagihan sebagai berikut: ' . implode(', ', $tempTagihan);
            }

            return [
                'status' => '1',
                'message' => $kondisi[0] . ' data tagihan berhasil digenerate. ' . $label,
                'jumlah_peserta' => $kondisi[0],
                'NoInvoice' => $tampung['NoInvoice'] ?? [],
                'NPM' => $tampung['NPM'] ?? [],
                'Nama' => $tampung['Nama'] ?? [],
                'TotalTagihan' => $tampung['TotalTagihan'] ?? [],
                'TglTransaksi' => $tampung['TglTransaksi'] ?? [],
                'sksKosong' => []
            ];

        } catch (\Exception $e) {
            return [
                'status' => '0',
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sinkronisasi tagihan mahasiswa dengan semester
     */
    protected function sinkronTagihanMahasiswa($mhswID, $tahunID, $idTagihanAis)
    {
        $mahasiswa = DB::table('mahasiswa')->where('ID', $mhswID)->first();
        $rowTahun = DB::table('tahun')->where('ID', $tahunID)->first();
        $kodeTahun = $rowTahun->TahunID;

        $listTagihan = DB::table('tagihan_mahasiswa')
            ->where('MhswID', $mhswID)
            ->where('Periode', $tahunID)
            ->whereIn('ID', $idTagihanAis)
            ->get();

        $tagihanMahasiswaSemesterID = null;
        $arrTagihanMahasiswaSemesterID = [];
        $arrBiayaID = [];

        foreach ($listTagihan as $rowTagihan) {
            $biayaID = $rowTagihan->BiayaID;

            if (!in_array($biayaID, $arrBiayaID) && $biayaID) {
                $arrBiayaID[] = $biayaID;

                $tagihanSemester = DB::table('tagihan_mahasiswa_semester')
                    ->where('Periode', $tahunID)
                    ->where('MhswID', $mahasiswa->ID)
                    ->first();

                if ($tagihanSemester) {
                    $tagihanMahasiswaSemesterID = $tagihanSemester->ID;
                } else {
                    $semesterTahunMasuk = $this->getSemesterTahunMasuk($mahasiswa->TahunMasuk, $kodeTahun);

                    $tagihanMahasiswaSemesterID = DB::table('tagihan_mahasiswa_semester')->insertGetId([
                        'BiayaSemesterID' => null,
                        'MhswID' => $mhswID,
                        'ProdiID' => $mahasiswa->ProdiID,
                        'ProgramID' => $mahasiswa->ProgramID,
                        'Periode' => $tahunID,
                        'Semester' => $semesterTahunMasuk,
                        'TotalTagihan' => 0,
                        'JumlahDiskon' => 0,
                        'Jumlah' => 0,
                        'createdAt' => date('Y-m-d H:i:s'),
                        'updatedAt' => date('Y-m-d H:i:s'),
                        'UserID' => auth()->id() ?? 0
                    ]);
                }

                if ($tagihanMahasiswaSemesterID) {
                    $arrTagihanMahasiswaSemesterID[] = $tagihanMahasiswaSemesterID;
                }
            }

            DB::table('tagihan_mahasiswa')
                ->where('ID', $rowTagihan->ID)
                ->update(['TagihanMahasiswaSemesterID' => $tagihanMahasiswaSemesterID]);
        }

        // Update semester totals
        foreach (array_unique($arrTagihanMahasiswaSemesterID) as $semID) {
            $sums = DB::table('tagihan_mahasiswa')
                ->where('TagihanMahasiswaSemesterID', $semID)
                ->where('JenisBiayaID', '!=', '32')
                ->select(
                    DB::raw('SUM(IFNULL(TotalTagihan,0)) as sum_total_tagihan'),
                    DB::raw('SUM(IFNULL(Jumlah,0)) as sum_jumlah'),
                    DB::raw('SUM(IFNULL(Sisa,0)) as sum_sisa'),
                    DB::raw('SUM(IFNULL(TotalCicilan,0)) as sum_totalcicilan'),
                    DB::raw('SUM(IFNULL(JumlahDiskon,0)) as sum_diskon')
                )
                ->first();

            DB::table('tagihan_mahasiswa_semester')
                ->where('ID', $semID)
                ->update([
                    'TotalTagihan' => $sums->sum_total_tagihan ?? 0,
                    'Jumlah' => ($sums->sum_total_tagihan ?? 0) - ($sums->sum_diskon ?? 0),
                    'Sisa' => (($sums->sum_total_tagihan ?? 0) - ($sums->sum_diskon ?? 0)) - ($sums->sum_totalcicilan ?? 0),
                    'JumlahDiskon' => $sums->sum_diskon ?? 0,
                    'TotalCicilan' => $sums->sum_totalcicilan ?? 0
                ]);
        }
    }

    /**
     * Get semester dari tahun masuk
     */
    protected function getSemesterTahunMasuk($tahunMasuk, $kodeTahun)
    {
        // Simplified logic - bisa disesuaikan dengan business rule
        return 1;
    }

    /**
     * Get tahun ID sebelumnya
     */
    protected function getTahunIdSebelumnya($tahunID, $offset = 1, $operator = '-')
    {
        $tahun = (int) $tahunID;
        return $operator == '-' ? $tahun - $offset : $tahun + $offset;
    }
}
