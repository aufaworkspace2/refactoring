<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class GenerateTagihanService
{
    public function searchMahasiswa($programID, $prodiID, $tahunMasuk, $tahunID, $jenisPendaftaran, $jalurPendaftaran, $semesterMasuk, $gelombangKe, $tahun_yg_dipilih)
    {
        $query = DB::table('mahasiswa')
            ->select('mahasiswa.ID', 'mahasiswa.NPM', 'mahasiswa.Nama');

        if ($tahun_yg_dipilih->Semester == 1 || $tahun_yg_dipilih->Semester == 2) {
            $tahunIDsebelumnya = get_tahun_id($tahun_yg_dipilih->TahunID, 1, '-');
            $tahun_sebelumnya = DB::table('tahun')->where('TahunID', $tahunIDsebelumnya)->first();

            $query->selectRaw('mahasiswa.ID, mahasiswa.NPM, mahasiswa.Nama, COUNT(keteranganstatusmahasiswa.ID) AS tidakAktif');
            $query->leftJoin('keteranganstatusmahasiswa', function($join) use ($tahun_sebelumnya) {
                $join->on('keteranganstatusmahasiswa.MhswID', '=', 'mahasiswa.ID')
                     ->where('keteranganstatusmahasiswa.StatusMahasiswaID', 6)
                     ->where('keteranganstatusmahasiswa.TahunID', $tahun_sebelumnya->ID);
            });
        }

        if (!empty($programID)) $query->where('mahasiswa.ProgramID', $programID);
        if (!empty($prodiID)) $query->where('mahasiswa.ProdiID', $prodiID);
        if (!empty($tahunMasuk)) $query->where('mahasiswa.TahunMasuk', $tahunMasuk);
        if (!empty($jenisPendaftaran)) $query->where('mahasiswa.StatusPindahan', $jenisPendaftaran);
        if (!empty($jalurPendaftaran)) $query->where('mahasiswa.jalur_pmb', $jalurPendaftaran);
        if (!empty($semesterMasuk)) $query->where('mahasiswa.SemesterMasuk', $semesterMasuk);
        if (!empty($gelombangKe)) $query->where('mahasiswa.GelombangKe', $gelombangKe);

        $query->leftJoin('draft_tagihan_mahasiswa', function($join) use ($tahunID) {
            $join->on('draft_tagihan_mahasiswa.MhswID', '=', 'mahasiswa.ID')
                 ->where('draft_tagihan_mahasiswa.StatusPosting', '1')
                 ->where('draft_tagihan_mahasiswa.Periode', $tahunID);
        });

        $query->whereNull('draft_tagihan_mahasiswa.ID');
        $query->whereIn('mahasiswa.StatusMhswID', ['3', '2']);
        $query->where('mahasiswa.jenis_mhsw', 'mhsw');
        $query->orderBy('mahasiswa.NPM', 'ASC');
        $query->groupBy('mahasiswa.ID');

        return $query->get();
    }

    public function getBiayaContent($post)
    {
        $ProgramID = $post['ProgramID'] ?? '';
        $ProdiID = $post['ProdiID'] ?? '';
        $Angkatan = $post['Angkatan'] ?? '';
        $TahunID = $post['TahunID'] ?? '';
        $JenisPendaftaran = $post['JenisPendaftaran'] ?? '';
        $JalurPendaftaran = $post['JalurPendaftaran'] ?? '';
        $SemesterMasuk = $post['SemesterMasuk'] ?? '';
        $GelombangKe = $post['GelombangKe'] ?? '';

        $KodeTahun = get_field($TahunID, 'tahun', 'TahunID');

        $queryJenisBiaya = DB::table('jenisbiaya')
            ->whereRaw("(FIND_IN_SET(?,Program) > 0 OR Program='0')", [$ProgramID])
            ->whereRaw("(FIND_IN_SET(?,Prodi) > 0 OR Prodi='0')", [$ProdiID])
            ->whereRaw("(FIND_IN_SET(?,TahunMasuk) > 0 OR TahunMasuk='0')", [$Angkatan])
            ->where('StatusHide', '0')
            ->whereRaw("(TipeMhsw='mhsw' OR ID=32)")
            ->whereIn('frekuensi', ['Satu Kali', 'Per Semester'])
            ->orderBy('Urut', 'ASC')
            ->get();
       
        $biaya = [];
        $get_detail = [];
        $tmp_biaya_det = [];

        foreach ($queryJenisBiaya as $row) {
            $queryBiaya = DB::table('biaya')
                ->select('ID', 'Jumlah', 'JumlahTagihan', 'JumlahDiskon', 'DikalikanSKS', 'MasterDiskonID_list')
                ->where('KodeTahun', $KodeTahun)
                ->where('ProgramID', $ProgramID)
                ->where('ProdiID', $ProdiID)
                ->where('TahunMasuk', $Angkatan)
                ->where('JenisPendaftaran', $JenisPendaftaran)
                ->where('JalurPendaftaran', $JalurPendaftaran)
                ->where('SemesterMasuk', $SemesterMasuk)
                ->where('GelombangKe', $GelombangKe)
                ->where('JenisBiayaID', $row->ID)
                ->where('JenisMahasiswa', 1)
                ->first();
            
            if ($queryBiaya) {
                $get_detail[$row->ID] = DB::table('jenisbiaya_detail')
                    ->where('JenisBiayaID', $row->ID)
                    ->get();

                foreach ($get_detail[$row->ID] as $val) {
                    $queryDetail = DB::table('biaya_detail')
                        ->where('KodeTahun', $KodeTahun)
                        ->where('ProgramID', $ProgramID)
                        ->where('ProdiID', $ProdiID)
                        ->where('TahunMasuk', $Angkatan)
                        ->where('JenisPendaftaran', $JenisPendaftaran)
                        ->where('JalurPendaftaran', $JalurPendaftaran)
                        ->where('SemesterMasuk', $SemesterMasuk)
                        ->where('GelombangKe', $GelombangKe)
                        ->where('JenisBiayaID', $row->ID)
                        ->where('JenisBiayaID_Detail', $val->ID)
                        ->first();

                    $tmp_biaya_det[$row->ID][$val->ID] = $queryDetail->JumlahTagihan ?? 0;
                }
                 
                $biaya[$row->ID] = [
                    'ID' => $row->ID,
                    'Nama' => $row->Nama,
                    'Jumlah' => $queryBiaya->Jumlah,
                    'JumlahTagihan' => $queryBiaya->JumlahTagihan,
                    'JumlahDiskon' => $queryBiaya->JumlahDiskon,
                    'DikalikanSKS' => $queryBiaya->DikalikanSKS
                ];
            }
        }
       
        // Handle SKS biaya
        $setup_harga_biaya_variable = DB::selectOne("SELECT * from setup_harga_biaya_variable
            WHERE (ProgramID=? OR ProgramID='0')
            AND (ProdiID=? OR ProdiID='0')
            AND (TahunMasuk=? OR TahunMasuk='0')
            AND (JenisPendaftaran=? OR JenisPendaftaran='0')
            AND Jenis='SKS'", [$ProgramID, $ProdiID, $Angkatan, $JenisPendaftaran]);

        $semester_tahunmasuk = get_semester_tahunmasuk($Angkatan, $KodeTahun);
        $paket_sks = DB::selectOne("SELECT ID from paket_sks where ProdiID=? and FIND_IN_SET(?,SemesterPaket)", [$ProdiID, $semester_tahunmasuk]);

        if ($paket_sks && isset($setup_harga_biaya_variable->NominalPaket) && $setup_harga_biaya_variable->NominalPaket) {
            $biaya[33] = [
                'ID' => 33,
                'Nama' => 'SKS',
                'Jumlah' => $setup_harga_biaya_variable->NominalPaket,
                'JumlahTagihan' => $setup_harga_biaya_variable->NominalPaket,
                'JumlahDiskon' => 0,
                'DikalikanSKS' => 0
            ];
        }

        $data['get_detail'] = $get_detail;
        $data['tmp_biaya_det'] = $tmp_biaya_det;
        $data['biaya'] = $biaya;
        $data['diskon'] = DB::table('master_diskon')->get();
        $data['ProgramID'] = $ProgramID;
        $data['ProdiID'] = $ProdiID;
        $data['TahunMasuk'] = $Angkatan;
        $data['TahunID'] = $TahunID;
        $data['JenisPendaftaran'] = $JenisPendaftaran;
        $data['JalurPendaftaran'] = $JalurPendaftaran;
        $data['SemesterMasuk'] = $SemesterMasuk;
        $data['GelombangKe'] = $GelombangKe;

        return $data;
    }

    public function generateTagihan($post, $detailjumlah)
    {
        $post_jumlah = $post['jumlah'] ?? [];
        $count = 0;
        $TahunID = $post['PeriodeID'];
        $ProgramID = $post['ProgramID'];
        $ProdiID = $post['ProdiID'];
        $Angkatan = $post['Angkatan'];
        $JenisPendaftaran = $post['JenisPendaftaran'];
        $JalurPendaftaran = $post['JalurPendaftaran'];
        $SemesterMasuk = $post['SemesterMasuk'];
        $GelombangKe = $post['GelombangKe'];
        $tipe = $post['tipe'];
        $mhswID = $post['mhswID'] ?? [];
        $TanggalTagihan = $post['TanggalTagihan'];

        $tahun_yg_dipilih = DB::table('tahun')->where('ID', $TahunID)->first();
        $KodeTahun = $tahun_yg_dipilih->TahunID;

        $query = [];
        if ($tipe == '1') {
            $query = $this->searchMahasiswa(
                $ProgramID, $ProdiID, $Angkatan, $TahunID,
                $JenisPendaftaran, $JalurPendaftaran,
                $SemesterMasuk, $GelombangKe, $tahun_yg_dipilih
            );
        } else if ($tipe == '2' && !empty($mhswID)) {
            $query = DB::table('mahasiswa')
                ->whereIn('ID', $mhswID)
                ->where('jenis_mhsw', 'mhsw')
                ->get();
        }

        $no = 1;
        $kondisi = [0 => 0, 1 => 0];
        $tempTagihan = [];
        $tampung = [];

        foreach ($query as $mahasiswa) {
            $id_tagihan_ais = [];
            $ID = $mahasiswa->ID;
            $mahasiswa = DB::table('mahasiswa')->where('ID', $ID)->first();

            // Get diskon setup
            $cek_setup_diskon = DB::table('setup_mahasiswa_diskon_sampai_lulus')
                ->whereRaw("(PerTahunID=0 OR FIND_IN_SET(?,ListTahunID) != 0)", [$TahunID])
                ->where(['MhswID' => $mahasiswa->ID, 'StatusAktif' => 1])
                ->first();

            $ListDiskon = [];
            if ($cek_setup_diskon) {
                $arr_list_diskon = json_decode($cek_setup_diskon->ListDiskon, true);
                foreach ($arr_list_diskon as $row_list_diskon) {
                    if (!isset($ListDiskon[$row_list_diskon['JenisBiayaID']])) {
                        $ListDiskon[$row_list_diskon['JenisBiayaID']] = $row_list_diskon['ListMasterDiskonID'];
                    } else {
                        $arr_disk = $ListDiskon[$row_list_diskon['JenisBiayaID']];
                        foreach ($row_list_diskon['ListMasterDiskonID'] as $id_list_diskon) {
                            $arr_disk[] = $id_list_diskon;
                        }
                        $ListDiskon[$row_list_diskon['JenisBiayaID']] = array_unique($arr_disk);
                    }
                }
            }

            $rand = mt_rand(100, 999);
            $noInvoiceGenerate = date("Y") . "-" . get_field($TahunID, 'tahun', 'TahunID') . "-" . $mahasiswa->NPM . "-" . $rand;
            $TotalNilaiTagihan = 0;

            foreach ($post['biaya'] as $jb) {
                $biaya = $post_jumlah[$jb] ?? 0;

                if ($jb != 33) {
                    $cekbiaya = DB::table('biaya')
                        ->select('ID', 'Jumlah', 'JumlahTagihan', 'JumlahDiskon', 'MasterDiskonID_list')
                        ->where('KodeTahun', $KodeTahun)
                        ->where('ProgramID', $ProgramID)
                        ->where('ProdiID', $ProdiID)
                        ->where('TahunMasuk', $Angkatan)
                        ->where('JenisPendaftaran', $JenisPendaftaran)
                        ->where('JalurPendaftaran', $JalurPendaftaran)
                        ->where('SemesterMasuk', $SemesterMasuk)
                        ->where('GelombangKe', $GelombangKe)
                        ->where('JenisMahasiswa', '1')
                        ->where('JenisBiayaID', $jb)
                        ->first();
                } else {
                    $cekbiaya = (object) [
                        'ID' => null,
                        'Jumlah' => $biaya,
                        'JumlahTagihan' => $biaya,
                        'JumlahDiskon' => 0,
                        'MasterDiskonID_list' => ''
                    ];
                }

                $cekada = DB::table('draft_tagihan_mahasiswa')
                    ->where('Periode', $TahunID)
                    ->where('JenisBiayaID', $jb)
                    ->where('MhswID', $mahasiswa->ID)
                    ->first();

                $NilaiTagihan = 0;
                $NilaiTagihanReal = 0;
                $JumlahDiskon = 0;

                if (empty($cekada->ID)) {
                    $NilaiTagihan = $biaya;
                    $NilaiTagihanReal = $biaya;

                    $get_diskon = [];
                    if (isset($ListDiskon[$jb]) && count($ListDiskon[$jb]) > 0) {
                        $get_diskon = DB::table('master_diskon')
                            ->whereIn('ID', $ListDiskon[$jb])
                            ->orderByRaw("FIELD(JenisDiskon, 'potong_dari_total', 'potong_dari_sisa')")
                            ->get();
                    }

                    foreach ($get_diskon as $raw) {
                        if ($raw->Tipe == 'nominal') {
                            if ($raw->Jumlah > $NilaiTagihan) {
                                $JumlahDiskon += $NilaiTagihan;
                                $NilaiTagihan = 0;
                            } else {
                                $NilaiTagihan -= $raw->Jumlah;
                                $JumlahDiskon += $raw->Jumlah;
                            }
                        } else if ($raw->Tipe == 'persen') {
                            if ($raw->JenisDiskon == 'potong_dari_sisa') {
                                $tempPersen = ($NilaiTagihan * $raw->Jumlah) / 100;
                            } else {
                                $tempPersen = ($NilaiTagihanReal * $raw->Jumlah) / 100;
                            }
                            if ($tempPersen > $NilaiTagihan) {
                                $JumlahDiskon += $NilaiTagihan;
                                $NilaiTagihan = 0;
                            } else {
                                $NilaiTagihan -= $tempPersen;
                                $JumlahDiskon += $tempPersen;
                            }
                        }
                    }

                    $insert = [
                        'DraftTagihanMahasiswaSemesterID' => null,
                        'BiayaID' => $cekbiaya->ID,
                        'MasterDiskonID' => implode(',', $ListDiskon[$jb] ?? []),
                        'ProgramID' => $mahasiswa->ProgramID,
                        'NoInvoice' => $noInvoiceGenerate,
                        'Periode' => $TahunID,
                        'ProdiID' => $mahasiswa->ProdiID,
                        'JenisBiayaID' => $jb,
                        'JenisMahasiswa' => 'mhsw',
                        'TahunID' => $KodeTahun,
                        'NPM' => $mahasiswa->NPM,
                        'MhswID' => $mahasiswa->ID,
                        'TotalTagihan' => $NilaiTagihanReal,
                        'JumlahDiskon' => $JumlahDiskon,
                        'Jumlah' => $NilaiTagihan,
                        'Tanggal' => $TanggalTagihan,
                        'Update' => date('Y-m-d H:i:s'),
                        'TanggalTagihan' => $TanggalTagihan,
                        'UserCreate' => Session::get('UserID')
                    ];

                    DB::table('draft_tagihan_mahasiswa')->insert($insert);
                    $draftTagihanMahasiswaID = DB::getPdo()->lastInsertId();
                    $id_tagihan_ais[] = $draftTagihanMahasiswaID;
                    $TagihanParentID = $draftTagihanMahasiswaID;
                    $count += DB::getPdo()->lastInsertId() ? 1 : 0;

                    // Insert termin
                    if ($TagihanParentID) {
                        $list_biaya_termin = [];
                        if ($jb != 33 && $cekbiaya->ID) {
                            $list_biaya_termin = DB::table('biaya_termin')->where('BiayaID', $cekbiaya->ID)->get();
                        } else {
                            $termin_sks = (object) [
                                'ID' => null,
                                'Jumlah' => $biaya,
                                'JumlahTagihan' => $biaya,
                                'JumlahDiskon' => 0,
                                'TerminKe' => 1
                            ];
                            $list_biaya_termin = [$termin_sks];
                        }

                        foreach ($list_biaya_termin as $valueTermin) {
                            $JumlahDiskonTermin = 0;
                            $JumlahTermin = $valueTermin->JumlahTagihan;
                            if ($JumlahDiskon > 0) {
                                $JumlahDiskonTermin = ($JumlahTermin / $NilaiTagihanReal) * $JumlahDiskon;
                                if ($JumlahDiskonTermin > 0) {
                                    $JumlahTermin = $JumlahTermin - $JumlahDiskonTermin;
                                }
                            }

                            DB::table('draft_tagihan_mahasiswa_termin')->insert([
                                'DraftTagihanMahasiswaID' => $TagihanParentID,
                                'BiayaTerminID' => $valueTermin->ID,
                                'ProgramID' => $mahasiswa->ProgramID,
                                'Periode' => $TahunID,
                                'ProdiID' => $mahasiswa->ProdiID,
                                'JenisBiayaID' => $jb,
                                'MhswID' => $mahasiswa->ID,
                                'TotalTagihan' => $valueTermin->JumlahTagihan,
                                'JumlahDiskon' => $JumlahDiskonTermin,
                                'Jumlah' => $JumlahTermin,
                                'TerminKe' => $valueTermin->TerminKe,
                                'Tanggal' => date('Y-m-d H:i:s'),
                                'Update' => date('Y-m-d H:i:s'),
                                'UserID' => Session::get('UserID')
                            ]);
                        }
                    }

                    // Insert detail
                    if (isset($detailjumlah[$jb]) && count($detailjumlah[$jb]) > 0 && $jb != 33) {
                        foreach ($detailjumlah[$jb] as $jb_detail => $jumlah_detail) {
                            if ($TagihanParentID) {
                                $cekbiaya_detail = DB::table('biaya_detail')
                                    ->where('KodeTahun', $KodeTahun)
                                    ->where('ProgramID', $ProgramID)
                                    ->where('ProdiID', $ProdiID)
                                    ->where('TahunMasuk', $Angkatan)
                                    ->where('JenisPendaftaran', $JenisPendaftaran)
                                    ->where('JalurPendaftaran', $JalurPendaftaran)
                                    ->where('SemesterMasuk', $SemesterMasuk)
                                    ->where('GelombangKe', $GelombangKe)
                                    ->where('JenisBiayaID', $jb)
                                    ->where('JenisBiayaID_Detail', $jb_detail)
                                    ->first();

                                $NilaiTagihanDetail_real = $jumlah_detail;
                                $NilaiTagihanDetail_akumulasi = $jumlah_detail;
                                $JumlahDiskonDetail = 0;

                                foreach ($get_diskon as $raw) {
                                    if ($raw->Tipe == 'nominal') {
                                        if ($raw->Jumlah > $NilaiTagihanDetail_akumulasi) {
                                            $JumlahDiskonDetail += $NilaiTagihanDetail_akumulasi;
                                            $NilaiTagihanDetail_akumulasi = 0;
                                        } else {
                                            $NilaiTagihanDetail_akumulasi -= $raw->Jumlah;
                                            $JumlahDiskonDetail += $raw->Jumlah;
                                        }
                                    } else if ($raw->Tipe == 'persen') {
                                        if ($raw->JenisDiskon == 'potong_dari_sisa') {
                                            $tempPersen = ($NilaiTagihanDetail_akumulasi * $raw->Jumlah) / 100;
                                        } else {
                                            $tempPersen = ($NilaiTagihanDetail_real * $raw->Jumlah) / 100;
                                        }
                                        if ($tempPersen > $NilaiTagihanDetail_akumulasi) {
                                            $JumlahDiskonDetail += $NilaiTagihanDetail_akumulasi;
                                            $NilaiTagihanDetail_akumulasi = 0;
                                        } else {
                                            $NilaiTagihanDetail_akumulasi -= $tempPersen;
                                            $JumlahDiskonDetail += $tempPersen;
                                        }
                                    }
                                }

                                DB::table('draft_tagihan_mahasiswa_detail')->insert([
                                    'DraftTagihanMahasiswaID' => $TagihanParentID,
                                    'BiayaDetailID' => $cekbiaya_detail->ID,
                                    'ProgramID' => $mahasiswa->ProgramID,
                                    'Periode' => $TahunID,
                                    'ProdiID' => $mahasiswa->ProdiID,
                                    'JenisBiayaID' => $jb,
                                    'JenisBiayaID_Detail' => $jb_detail,
                                    'MhswID' => $mahasiswa->ID,
                                    'JenisMahasiswa' => 'mhsw',
                                    'TotalTagihan' => $NilaiTagihanDetail_real,
                                    'JumlahDiskon' => $JumlahDiskonDetail,
                                    'Jumlah' => $NilaiTagihanDetail_akumulasi,
                                    'Tanggal' => $TanggalTagihan,
                                    'Update' => date('Y-m-d H:i:s'),
                                    'UserID' => Session::get('UserID')
                                ]);
                            }
                        }
                    }

                    if (array_key_exists($jb, $post['jumlah'])) {
                        $TotalNilaiTagihan += $NilaiTagihan;
                    }
                    $kondisi[0] += 1;
                } else {
                    $kondisi[1] += 1;
                    $tempTagihan[] = get_field($jb, 'jenisbiaya');
                    $id_tagihan_ais[] = $cekada->ID;
                }
            }

            $tampung['jumlah_peserta'] = count($query);
            $tampung['NoInvoice'][$no] = $noInvoiceGenerate;
            $tampung['NPM'][$no] = $mahasiswa->NPM;
            $tampung['Nama'][$no] = $mahasiswa->Nama;
            $tampung['TotalTagihan'][$no] = rupiah($TotalNilaiTagihan);
            $tampung['TglTransaksi'][$no] = tgl(date("Y-m-d"), "04");
            $tampung['Total'] = ($tampung['Total'] ?? 0) + $TotalNilaiTagihan;

            $no++;

            if (count($id_tagihan_ais) > 0) {
                $this->sinkron_draft_tagihan_mahasiswa($mahasiswa->ID, $TahunID, $id_tagihan_ais);
            }
        }

        // Truncate and insert to tmp_generate_tagihan
        DB::table('tmp_generate_tagihan')->truncate();

        $label = '';
        if ($kondisi[1] > 0) {
            $label = '||' . $kondisi[1] . ' data tagihan gagal digenerate !. Karena anda telah melakukan generate tagihan sebagai berikut : ' . implode(', ', $tempTagihan);
        }

        $tampung['status'] = '1';
        $tampung['message'] = $kondisi[0] . ' data tagihan berhasil digenerate. ' . $label;

        for ($i = 1; $i <= ($tampung['jumlah_peserta'] ?? 0); $i++) {
            DB::table('tmp_generate_tagihan')->insert([
                'A' => $tampung['NoInvoice'][$i] ?? '',
                'B' => $tampung['NPM'][$i] ?? '',
                'C' => $tampung['Nama'][$i] ?? '',
                'D' => $tampung['TotalTagihan'][$i] ?? '',
                'E' => $tampung['TglTransaksi'][$i] ?? ''
            ]);
        }

        return $tampung;
    }

    public function sinkron_draft_tagihan_mahasiswa($MhswID, $TahunID, $id_tagihan_ais)
    {
        $mahasiswa = DB::table('mahasiswa')
            ->select('ID', 'NPM', 'Nama', 'ProgramID', 'ProdiID', 'TahunMasuk', 'jalur_pmb', 'StatusPindahan', 'SemesterMasuk', 'GelombangKe')
            ->where('ID', $MhswID)
            ->first();

        $row_tahun = DB::table('tahun')->where('ID', $TahunID)->first();
        $KodeTahun = $row_tahun->TahunID;

        $list_tagihan = DB::table('draft_tagihan_mahasiswa')
            ->where('MhswID', $MhswID)
            ->where('Periode', $TahunID)
            ->whereIn('ID', $id_tagihan_ais)
            ->get();

        $draftTagihanMahasiswaSemesterID = null;
        $arr_TagihanMahasiswaSemesterID = [];
        $arr_BiayaID = [];
        $BiayaSemesterID = null;

        foreach ($list_tagihan as $row_tagihan) {
            $BiayaID = $row_tagihan->BiayaID;

            if (!in_array($BiayaID, $arr_BiayaID)) {
                $biaya = DB::table('biaya')->where('ID', $BiayaID)->first();
                $arr_BiayaID[] = $BiayaID;

                $tagihan_semester = DB::table('draft_tagihan_mahasiswa_semester')
                    ->where('Periode', $TahunID)
                    ->where('MhswID', $mahasiswa->ID)
                    ->first();

                if ($tagihan_semester) {
                    $draftTagihanMahasiswaSemesterID = $tagihan_semester->ID;
                } else {
                    $biaya_semester = DB::table('biaya_semester')->where('ID', $biaya->BiayaSemesterID)->first();

                    if (empty($biaya->Semester)) {
                        $biaya->Semester = get_semester_tahunmasuk($KodeTahun, $mahasiswa->TahunMasuk);
                    }

                    $insertSemester = DB::table('draft_tagihan_mahasiswa_semester')->insertGetId([
                        'BiayaSemesterID' => $biaya->BiayaSemesterID,
                        'MhswID' => $MhswID,
                        'ProdiID' => $mahasiswa->ProdiID,
                        'ProgramID' => $mahasiswa->ProgramID,
                        'Periode' => $TahunID,
                        'Semester' => $biaya->Semester,
                        'TotalTagihan' => $biaya_semester->JumlahTagihan ?? 0,
                        'JumlahDiskon' => $biaya_semester->JumlahDiskon ?? 0,
                        'Jumlah' => $biaya_semester->Jumlah ?? 0,
                        'createdAt' => date('Y-m-d H:i:s'),
                        'updatedAt' => date('Y-m-d H:i:s'),
                        'UserID' => Session::get('UserID')
                    ]);

                    $draftTagihanMahasiswaSemesterID = $insertSemester;
                }

                if ($draftTagihanMahasiswaSemesterID) {
                    $arr_TagihanMahasiswaSemesterID[] = $draftTagihanMahasiswaSemesterID;
                }
            }

            DB::table('draft_tagihan_mahasiswa')
                ->where('ID', $row_tagihan->ID)
                ->update(['DraftTagihanMahasiswaSemesterID' => $draftTagihanMahasiswaSemesterID]);
        }

        foreach ($arr_TagihanMahasiswaSemesterID as $semId) {
            $tagihan_semester = DB::table('draft_tagihan_mahasiswa_semester')->where('ID', $semId)->first();

            $jum_all_semester = DB::selectOne("SELECT
                SUM(ifnull(Jumlah,0)) as sum_jumlah,
                SUM(ifnull(TotalTagihan,0)) as sum_total_tagihan,
                SUM(ifnull(JumlahDiskon,0)) as sum_jumlah_diskon
                from draft_tagihan_mahasiswa
                where DraftTagihanMahasiswaSemesterID = ?", [$tagihan_semester->ID]);

            DB::table('draft_tagihan_mahasiswa_semester')
                ->where('ID', $tagihan_semester->ID)
                ->update([
                    'Jumlah' => $jum_all_semester->sum_jumlah,
                    'TotalTagihan' => $jum_all_semester->sum_total_tagihan,
                    'JumlahDiskon' => $jum_all_semester->sum_jumlah_diskon
                ]);
        }
    }
}
