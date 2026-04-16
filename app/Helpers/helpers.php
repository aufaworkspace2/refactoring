<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\HtmlString;

// Define CI3-style constants for Laravel 12 compatibility
// Note: env() might return default during early autoload. We use relative paths as safe defaults.
if (!defined('CLIENT_HOST')) define('CLIENT_HOST', rtrim(env('CLIENT_HOST', '/dev'), '/'));
if (!defined('ASSETS_HOST')) define('ASSETS_HOST', rtrim(env('ASSETS_HOST', '/assets'), '/'));
if (!defined('CLIENT_PATH')) define('CLIENT_PATH', rtrim(env('CLIENT_PATH', realpath(__DIR__ . '/../../public/dev')), '/\\'));
if (!defined('ASSETS_PATH')) define('ASSETS_PATH', rtrim(env('ASSETS_PATH', realpath(__DIR__ . '/../../public/assets')), '/\\'));
if (!defined('THEME')) define('THEME', env('THEME', 'default'));

if (!function_exists('terbilang')) {
    function terbilang($satuan, $prefix = "")
    {
        if ($prefix) {
            $prefix = " " . $prefix;
        }
        $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
        if ($satuan < 12)
            return " " . $huruf[$satuan] . $prefix;
        elseif ($satuan < 20)
            return terbilang($satuan - 10) . "Belas" . $prefix;
        elseif ($satuan < 100)
            return terbilang($satuan / 10) . " Puluh" . terbilang($satuan % 10) . $prefix;
        elseif ($satuan < 200)
            return " seratus" . terbilang($satuan - 100);
        elseif ($satuan < 1000)
            return terbilang($satuan / 100) . " Ratus" . terbilang($satuan % 100) . $prefix;
        elseif ($satuan < 2000)
            return " seribu" . terbilang($satuan - 1000);
        elseif ($satuan < 1000000)
            return terbilang($satuan / 1000) . " Ribu" . terbilang($satuan % 1000) . $prefix;
        elseif ($satuan < 1000000000)
            return terbilang($satuan / 1000000) . " Juta" . terbilang($satuan % 1000000) . $prefix;
        elseif ($satuan >= 1000000000)
            echo "Hasil terbilang tidak dapat di proses karena nilai uang terlalu besar!";
    }
}

if (!function_exists('formatSizeUnits')) {
    function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}

if (!function_exists('get_list_grade_berlaku')) {
    function get_list_grade_berlaku($mhswID)
    {
        $prodiID        = get_field($mhswID, 'mahasiswa', "ProdiID");
        $tahunMasuk     = get_field($mhswID, 'mahasiswa', "TahunMasuk");
        $now = date('Y-m-d');

        $where  = "AND ( '$now' between bobot_master.TanggalMulai and bobot_master.TanggalSelesai )";

        if (!empty($tahunMasuk)) {
            $where  .= ' AND FIND_IN_SET("' . $tahunMasuk . '", setting_pemberlakuan_bobot.TahunMasuk) != 0';
        }
        if (!empty($prodiID)) {
            $where  .= ' AND FIND_IN_SET("' . $prodiID . '", setting_pemberlakuan_bobot.ProdiID) != 0';
        }
        $sql    = "SELECT bobot.* FROM bobot 
            INNER JOIN bobot_master on bobot_master.ID=bobot.BobotMasterID
            INNER JOIN setting_pemberlakuan_bobot on setting_pemberlakuan_bobot.BobotMasterID=bobot.BobotMasterID 
            WHERE 1=1 " . $where . " order by bobot.Bobot DESC";

        $db     = DB::select($sql);

        return $db;
    }
}

if (!function_exists('get_grade_return')) {
    function get_grade_return($mhswID, $nilai)
    {
        $prodiID        = get_field($mhswID, 'mahasiswa', "ProdiID");
        $tahunMasuk     = get_field($mhswID, 'mahasiswa', "TahunMasuk");
        $now = date('Y-m-d');

        $where  = "AND ( '$now' between bobot_master.TanggalMulai and bobot_master.TanggalSelesai )";
        if ($nilai != 'NaN' && $nilai != '' && $nilai != NULL) {
            if (!empty($tahunMasuk)) {
                $where  .= ' AND FIND_IN_SET("' . $tahunMasuk . '", setting_pemberlakuan_bobot.TahunMasuk) != 0';
            }
            if (!empty($prodiID)) {
                $where  .= ' AND FIND_IN_SET("' . $prodiID . '", setting_pemberlakuan_bobot.ProdiID) != 0';
            }
            $sql    = "SELECT bobot.* FROM bobot 
                INNER JOIN bobot_master on bobot_master.ID=bobot.BobotMasterID
                INNER JOIN setting_pemberlakuan_bobot on setting_pemberlakuan_bobot.BobotMasterID=bobot.BobotMasterID 
                WHERE (" . $nilai . " BETWEEN bobot.MinNilai AND bobot.MaxNilai) " . $where . " order by bobot.Bobot DESC";

            $db     = DB::selectOne($sql);

            if ($db && isset($db->Nilai)) {
                return $db->Nilai;
            } else {
                return 'NotFound';
            }
        } else {
            return '';
        }
    }
}

if (!function_exists('get_bobot_angka')) {
    function get_bobot_angka($mhswID)
    {
        $prodiID        = get_field($mhswID, 'mahasiswa', "ProdiID");
        $tahunMasuk     = get_field($mhswID, 'mahasiswa', "TahunMasuk");
        $now = date('Y-m-d');

        $where  = "AND ( '$now' between bobot_master.TanggalMulai and bobot_master.TanggalSelesai )";

        if (!empty($tahunMasuk)) {
            $where  .= ' AND FIND_IN_SET("' . $tahunMasuk . '", setting_pemberlakuan_bobot.TahunMasuk) != 0';
        }
        if (!empty($prodiID)) {
            $where  .= ' AND FIND_IN_SET("' . $prodiID . '", setting_pemberlakuan_bobot.ProdiID) != 0';
        }
        $sql    = "SELECT bobot.* FROM bobot 
            INNER JOIN bobot_master on bobot_master.ID=bobot.BobotMasterID
            INNER JOIN setting_pemberlakuan_bobot on setting_pemberlakuan_bobot.BobotMasterID=bobot.BobotMasterID 
            WHERE 1=1 " . $where . " order by bobot.Bobot DESC";

        $db     = DB::select($sql);
        if ($db) {
            $Nilai = [];
            foreach ($db as $bobot) {
                $Nilai[$bobot->Nilai] = $bobot;
            }

            return $Nilai;
        } else {
            return array();
        }
    }
}

if (!function_exists('validateDate')) {
    function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}

if (!function_exists('smart_wordwrap')) {
    function smart_wordwrap($string, $width = 85, $break = "<br>")
    {
        $pattern = sprintf('/([^ ]{%d,})/', $width);
        $output = '';
        $words = preg_split($pattern, $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        foreach ($words as $word) {
            if (false !== strpos($word, ' ')) {
                $output .= $word;
            } else {
                $wrapped = explode($break, wordwrap($output, $width, $break));
                $count = $width - (strlen(end($wrapped)) % $width);
                $output .= substr($word, 0, $count) . $break;
                $output .= wordwrap(substr($word, $count), $width, $break, true);
            }
        }

        return wordwrap($output, $width, $break);
    }
}

if (!function_exists('sinkron_field_totalcicilan')) {
    function sinkron_field_totalcicilan($MhswID, $id_hasil_cicil = array())
    {
        $arr_nama_id_tagihan = array(
            'tagihan_mahasiswa' => 'TagihanMahasiswaID',
            'tagihan_mahasiswa_detail' => 'TagihanMahasiswaDetailID',
            'tagihan_mahasiswa_termin' => 'TagihanMahasiswaTerminID',
            'tagihan_mahasiswa_semester' => 'TagihanMahasiswaSemesterID',
            'tagihan_mahasiswa_termin_semester' => 'TagihanMahasiswaTerminSemesterID',
            'tagihan_mahasiswa_termin_total' => 'TagihanMahasiswaTerminTotalID',
        );

        $id_termin_total = array();
        $id_periode = array();

        if (count($id_hasil_cicil) > 0) {
            foreach ($id_hasil_cicil as $table => $id_hasil_cicil2) {
                foreach ($id_hasil_cicil2 as $id_table => $id_cicilan) {
                    $data_table = get_id($id_table, $table);

                    $nama_id_table = $arr_nama_id_tagihan[$table];

                    $sql_sum = "SELECT SUM(ifnull(Jumlah,0)) as jml from cicilan_" . $table . " where $nama_id_table = '$id_table' ";
                    $row_sum = DB::selectOne($sql_sum);
                    if (empty($row_sum)) {
                        $row_sum = new stdClass();
                        $row_sum->jml = 0;
                    }

                    if ($data_table && isset($data_table->Periode) && $data_table->Periode) {
                        $id_periode[$data_table->Periode] = $data_table->Periode;
                    }

                    $upd = array();
                    $upd['TotalCicilan'] = $row_sum->jml;
                    if ($data_table) {
                        $upd['Sisa'] = $data_table->Jumlah - $row_sum->jml;
                    }
                    if ($table == 'tagihan_mahasiswa') {
                        if (isset($upd['Sisa']) && $upd['Sisa'] > 0) {
                            $upd['Status'] = 'Belum';
                            $upd['Lunas'] = '0';
                        } else {
                            $upd['Status'] = 'Lunas';
                            $upd['Lunas'] = '1';
                        }
                    }
                    DB::table($table)->where('ID', $id_table)->update($upd);

                    if ($table == 'tagihan_mahasiswa_termin_total') {
                        $id_termin_total[] = $id_table;
                    }
                }
            }
        }

        if (count($id_periode) > 0) {
            $list_tagihan_semester = DB::table('tagihan_mahasiswa_semester')
                ->select('ID', 'MhswID', 'Periode')
                ->where('MhswID', $MhswID)
                ->whereIn('Periode', $id_periode)
                ->get();
            foreach ($list_tagihan_semester as $row_tagihan_semester) {
                $jum_all_sem = DB::selectOne("SELECT 
                        SUM(ifnull(Jumlah,0)) as sum_jumlah,
                        SUM(ifnull(Sisa,0)) as sum_sisa
                        from tagihan_mahasiswa
                        where MhswID='$row_tagihan_semester->MhswID' and Periode='$row_tagihan_semester->Periode' ");

                $sum_sem_jumlah = ($jum_all_sem && $jum_all_sem->sum_jumlah) ? $jum_all_sem->sum_jumlah : 0;
                $sum_sem_sisa = ($jum_all_sem && $jum_all_sem->sum_sisa) ? $jum_all_sem->sum_sisa : 0;

                $upd_sem = array();
                $upd_sem['Jumlah'] = $sum_sem_jumlah;
                $upd_sem['Sisa'] = $sum_sem_sisa;
                $upd_sem['TotalCicilan'] = $sum_sem_jumlah - $sum_sem_sisa;
                DB::table('tagihan_mahasiswa_semester')->where('ID', $row_tagihan_semester->ID)->update($upd_sem);
            }
        }

        if (count($id_termin_total) > 0) {
            foreach ($id_termin_total as $id_table) {
                $tagihan_mahasiswa_termin_total = get_id($id_table, 'tagihan_mahasiswa_termin_total');

                if ($tagihan_mahasiswa_termin_total && $tagihan_mahasiswa_termin_total->TagihanMahasiswaSemesterID_list) {
                    $jum_all_semester = DB::selectOne("SELECT 
                        SUM(ifnull(Jumlah,0)) as sum_jumlah,
                        SUM(ifnull(Sisa,0)) as sum_sisa
                        from tagihan_mahasiswa_semester 
                        where ID in ($tagihan_mahasiswa_termin_total->TagihanMahasiswaSemesterID_list) ");

                    $sum_semester_jumlah = $jum_all_semester->sum_jumlah ?? 0;
                    $sum_semester_sisa = $jum_all_semester->sum_sisa ?? 0;
                } else {
                    $sum_semester_jumlah     = 0;
                    $sum_semester_sisa  = 0;
                }

                if ($tagihan_mahasiswa_termin_total) {
                    $arr_tagihan_semester = explode(",", $tagihan_mahasiswa_termin_total->TagihanMahasiswaSemesterID_list);
                    $arr_tagihan_semester = array_filter($arr_tagihan_semester);
                    $arr_tagihan_semester = array_unique($arr_tagihan_semester);
                    $tagihan_mahasiswa_termin_total->TagihanMahasiswaSemesterID_list = implode(",", $arr_tagihan_semester);

                    $upd_semester = array();
                    $upd_semester['Jumlah'] = $sum_semester_jumlah;
                    $upd_semester['Sisa'] = $sum_semester_sisa;
                    $upd_semester['TotalCicilan'] = $sum_semester_jumlah - $sum_semester_sisa;
                    $upd_semester['TagihanMahasiswaSemesterID_list'] = $tagihan_mahasiswa_termin_total->TagihanMahasiswaSemesterID_list;
                    DB::table('tagihan_mahasiswa_termin_total')->where('ID', $tagihan_mahasiswa_termin_total->ID)->update($upd_semester);
                }
            }
        }
    }
}

if (!function_exists('update_opsi_mahasiswa')) {
    function update_opsi_mahasiswa($MhswID = '', $arr_TahunID = array())
    {
        $m = get_id($MhswID, 'mahasiswa');

        $get_setupapp_opsi = get_setup_app("opsi_mahasiswa_check");
        $cek_tagihan_sebelumnya = ($get_setupapp_opsi && $get_setupapp_opsi->metadata) ? json_decode($get_setupapp_opsi->metadata, true)['cek_tagihan_sebelumnya'] : false;

        foreach ($arr_TahunID as $TahunID) {
            $t = get_id($TahunID, 'tahun');
            if (!$t) continue;
            
            $cekTagihan = DB::table('tagihan_mahasiswa')
                ->join('jenisbiaya', 'jenisbiaya.ID', '=', 'tagihan_mahasiswa.JenisBiayaID')
                ->where('tagihan_mahasiswa.MhswID', $MhswID)
                ->where('tagihan_mahasiswa.Periode', $t->ID)
                ->get();

            $cekTagihan_sebelumnya_val = null;
            if ($cek_tagihan_sebelumnya) {
                $cekTagihan_sebelumnya_val = DB::table('tagihan_mahasiswa')
                    ->selectRaw('SUM(Sisa) AS sisa_tagihan')
                    ->join('jenisbiaya', 'jenisbiaya.ID', '=', 'tagihan_mahasiswa.JenisBiayaID')
                    ->where('tagihan_mahasiswa.MhswID', $MhswID)
                    ->where('tagihan_mahasiswa.Periode', '<', $t->ID)
                    ->first();
            }

            $totalTagihan = 0;
            $totalCicilan = 0;

            $totalTagihanPerJB = array();
            $totalCicilanPerJB = array();

            foreach ($cekTagihan as $valTag) {
                $valTag->Jumlah = $valTag->TotalTagihan;
                $valTag->TotalCicilan = $valTag->TotalCicilan + $valTag->JumlahDiskon;

                $totalTagihan += $valTag->Jumlah;
                $totalCicilan += $valTag->TotalCicilan;

                if (!isset($totalTagihanPerJB[$valTag->JenisBiayaID])) {
                    $totalTagihanPerJB[$valTag->JenisBiayaID] = 0;
                }
                if (!isset($totalCicilanPerJB[$valTag->JenisBiayaID])) {
                    $totalCicilanPerJB[$valTag->JenisBiayaID] = 0;
                }

                $totalTagihanPerJB[$valTag->JenisBiayaID] += $valTag->Jumlah;
                $totalCicilanPerJB[$valTag->JenisBiayaID] += $valTag->TotalCicilan;
            }

            $whereKRS = "Nama ='KRS' AND (ProgramID='".($m->ProgramID ?? 0)."' OR ProgramID='0') AND (ProdiID='".($m->ProdiID ?? 0)."' OR ProdiID='0') AND (TahunMasuk='".($m->TahunMasuk ?? 0)."' OR TahunMasuk='0') AND (SemesterMasuk='".($m->SemesterMasuk ?? 0)."' OR SemesterMasuk='0') ";
            $whereUTS = "Nama ='UTS' AND (ProgramID='".($m->ProgramID ?? 0)."' OR ProgramID='0') AND (ProdiID='".($m->ProdiID ?? 0)."' OR ProdiID='0') AND (TahunMasuk='".($m->TahunMasuk ?? 0)."' OR TahunMasuk='0') AND (SemesterMasuk='".($m->SemesterMasuk ?? 0)."' OR SemesterMasuk='0') ";
            $whereUAS = "Nama ='UAS' AND (ProgramID='".($m->ProgramID ?? 0)."' OR ProgramID='0') AND (ProdiID='".($m->ProdiID ?? 0)."' OR ProdiID='0') AND (TahunMasuk='".($m->TahunMasuk ?? 0)."' OR TahunMasuk='0') AND (SemesterMasuk='".($m->SemesterMasuk ?? 0)."' OR SemesterMasuk='0') ";

            $setupKRS = DB::table('setup_persentase_bayar')->whereRaw($whereKRS)->orderByRaw('ProgramID DESC, ProdiID DESC, TahunMasuk DESC')->first();
            $setupUTS = DB::table('setup_persentase_bayar')->whereRaw($whereUTS)->orderByRaw('ProgramID DESC, ProdiID DESC, TahunMasuk DESC')->first();
            $setupUAS = DB::table('setup_persentase_bayar')->whereRaw($whereUAS)->orderByRaw('ProgramID DESC, ProdiID DESC, TahunMasuk DESC')->first();

            $persentaseBayar = ($totalTagihan > 0) ? ($totalCicilan / $totalTagihan) * 100 : 0;

            $persentaseBayarKRS = $persentaseBayar;
            $persentaseBayarUTS = $persentaseBayar;
            $persentaseBayarUAS = $persentaseBayar;

            $totalCicilanKRS = $totalCicilan;
            $totalCicilanUTS = $totalCicilan;
            $totalCicilanUAS = $totalCicilan;

            if ($setupKRS && $setupKRS->JenisBiayaID_list) {
                $tempTotalTagihan = 0; $tempTotalCicilan = 0;
                $exp_jb = explode(",", $setupKRS->JenisBiayaID_list);
                foreach ($exp_jb as $jb) {
                    if (isset($totalTagihanPerJB[$jb])) $tempTotalTagihan += $totalTagihanPerJB[$jb];
                    if (isset($totalCicilanPerJB[$jb])) $tempTotalCicilan += $totalCicilanPerJB[$jb];
                }
                $persentaseBayarKRS = ($tempTotalTagihan > 0) ? ($tempTotalCicilan / $tempTotalTagihan) * 100 : 0;
                $totalCicilanKRS = $tempTotalCicilan;
            }

            if ($setupUTS && $setupUTS->JenisBiayaID_list) {
                $tempTotalTagihan = 0; $tempTotalCicilan = 0;
                $exp_jb = explode(",", $setupUTS->JenisBiayaID_list);
                foreach ($exp_jb as $jb) {
                    if (isset($totalTagihanPerJB[$jb])) $tempTotalTagihan += $totalTagihanPerJB[$jb];
                    if (isset($totalCicilanPerJB[$jb])) $tempTotalCicilan += $totalCicilanPerJB[$jb];
                }
                $persentaseBayarUTS = ($tempTotalTagihan > 0) ? ($tempTotalCicilan / $tempTotalTagihan) * 100 : 0;
                $totalCicilanUTS = $tempTotalCicilan;
            }

            if ($setupUAS && $setupUAS->JenisBiayaID_list) {
                $tempTotalTagihan = 0; $tempTotalCicilan = 0;
                $exp_jb = explode(",", $setupUAS->JenisBiayaID_list);
                foreach ($exp_jb as $jb) {
                    if (isset($totalTagihanPerJB[$jb])) $tempTotalTagihan += $totalTagihanPerJB[$jb];
                    if (isset($totalCicilanPerJB[$jb])) $tempTotalCicilan += $totalCicilanPerJB[$jb];
                }
                $persentaseBayarUAS = ($tempTotalTagihan > 0) ? ($tempTotalCicilan / $tempTotalTagihan) * 100 : 0;
                $totalCicilanUAS = $tempTotalCicilan;
            }

            $opsiUpdate = array();
            if ($setupKRS && $setupKRS->Persen <= $persentaseBayarKRS && $setupKRS->Tipe == 'persen') $opsiUpdate['KRS'] = '1';
            else if ($setupKRS && $setupKRS->Persen <= $totalCicilanKRS && $setupKRS->Tipe == 'nominal') $opsiUpdate['KRS'] = '1';

            if ($setupUTS && $setupUTS->Persen <= $persentaseBayarUTS && $setupUTS->Tipe == 'persen') $opsiUpdate['UTS'] = '1';
            else if ($setupUTS && $setupUTS->Persen <= $totalCicilanUTS && $setupUTS->Tipe == 'nominal') $opsiUpdate['UTS'] = '1';

            if ($setupUAS && $setupUAS->Persen <= $persentaseBayarUAS && $setupUAS->Tipe == 'persen') $opsiUpdate['UAS'] = '1';
            else if ($setupUAS && $setupUAS->Persen <= $totalCicilanUAS && $setupUAS->Tipe == 'nominal') $opsiUpdate['UAS'] = '1';

            if ($persentaseBayar == 100.0) { $opsiUpdate['KHS'] = '1'; $opsiUpdate['TRANSKRIP'] = '1'; }
            
            if (empty($cek_tagihan_sebelumnya) || ($cekTagihan_sebelumnya_val && $cekTagihan_sebelumnya_val->sisa_tagihan == '0')) {
                if (count($opsiUpdate) > 0) {
                    $opsiWhere = ['MhswID' => $MhswID, 'TahunID' => $t->ID];
                    $jumOpsi = DB::table('opsi_mahasiswa')->where($opsiWhere)->count();
                    if ($jumOpsi > 0) DB::table('opsi_mahasiswa')->where($opsiWhere)->update($opsiUpdate);
                    else { $opsiUpdate['MhswID'] = $MhswID; $opsiUpdate['TahunID'] = $t->ID; DB::table('opsi_mahasiswa')->insert($opsiUpdate); }
                    
                    $apiElearning = app(\App\Library\ApiElearning::class);
                    if ($m && method_exists($apiElearning, 'insert_akses_uts_uas')) {
                        $apiElearning->insert_akses_uts_uas($m->NPM, $t->TahunID, (int)($opsiUpdate['UTS'] ?? 0), (int)($opsiUpdate['UAS'] ?? 0));
                    }
                }
            }
        }
    }
}

if (!function_exists('proses_pengajuan_otomatis')) {
    function proses_pengajuan_otomatis($MhswID = '', $PengajuanPembayaranID = '', $tipe = '', $Tanggal = '', $NamaBank = '')
    {
        $success = 0;
        if (!empty($MhswID) && !empty($PengajuanPembayaranID) && !empty($tipe)) {
            $mhsw = get_id($MhswID, 'mahasiswa');
            $detail_pengajuan_pembayaran1 = DB::table('pengajuan_pembayaran_detail')->where('PengajuanPembayaranID', $PengajuanPembayaranID)->whereNotIn('Jenis', ['tagihan_mahasiswa_termin', 'tagihan_mahasiswa_detail'])->get();
            $detail_pengajuan_pembayaran2 = DB::table('pengajuan_pembayaran_detail')->where('PengajuanPembayaranID', $PengajuanPembayaranID)->whereIn('Jenis', ['tagihan_mahasiswa_termin', 'tagihan_mahasiswa_detail'])->get();
            $pengajuan_pembayaran = get_id($PengajuanPembayaranID, 'pengajuan_pembayaran');
            if (!$pengajuan_pembayaran) return 0;
            $KodePengajuan = $pengajuan_pembayaran->KodePengajuan;
            $TanggalBayarBank = (validateDate($Tanggal) && !empty($Tanggal)) ? $Tanggal : date('Y-m-d');
            $arr_TahunID = array();

            if ($tipe == 2) {
                $upd = ['LastUpdateUserID' => session('UserID') ?? 0, 'Status' => 2];
                if (DB::table('pengajuan_pembayaran')->where('ID', $PengajuanPembayaranID)->update($upd)) $success = 1;
            } else if ($tipe == 1) {
                $id_hasil_cicil = array(); $list_jb = array();
                if ($pengajuan_pembayaran->Status == 0) {
                    $arr_nama_id_tagihan = ['tagihan_mahasiswa' => 'TagihanMahasiswaID', 'tagihan_mahasiswa_detail' => 'TagihanMahasiswaDetailID', 'tagihan_mahasiswa_termin' => 'TagihanMahasiswaTerminID', 'tagihan_mahasiswa_semester' => 'TagihanMahasiswaSemesterID', 'tagihan_mahasiswa_termin_semester' => 'TagihanMahasiswaTerminSemesterID', 'tagihan_mahasiswa_termin_total' => 'TagihanMahasiswaTerminTotalID'];
                    foreach ($detail_pengajuan_pembayaran1 as $dpp) {
                        $table = $dpp->Jenis; $id_table = $dpp->EntitasID; $data_table = get_id($id_table, $table);
                        if (!$data_table) continue;
                        $input_cicilan = [$arr_nama_id_tagihan[$table] => $id_table, 'NoKwitansi' => $KodePengajuan, 'ProgramID' => $mhsw->ProgramID ?? null, 'ProdiID' => $mhsw->ProdiID ?? null, 'NPM' => $mhsw->NPM ?? null, 'Jumlah' => $dpp->JumlahBayar, 'TanggalBayar' => date("Y-m-d H:i:s"), 'TanggalBayarBank' => $TanggalBayarBank, 'JenisBayar' => 'Bank', 'TglBuat' => date("Y-m-d"), 'User' => session('UserID'), 'PengajuanPembayaranID' => $PengajuanPembayaranID];
                        if ($table != 'tagihan_mahasiswa_termin_total') $input_cicilan['TahunID'] = $data_table->Periode;
                        if ($table == 'tagihan_mahasiswa') { $input_cicilan['JenisBiayaID'] = $data_table->JenisBiayaID; $list_jb[$data_table->JenisBiayaID] = $data_table->JenisBiayaID; if ($NamaBank) $input_cicilan['NamaBank'] = $NamaBank; }
                        if ($table == 'tagihan_mahasiswa_termin_semester' || $table == 'tagihan_mahasiswa_termin_total') $input_cicilan['TerminKe'] = $data_table->TerminKe;
                        if (DB::table('cicilan_' . $table)->insert($input_cicilan)) { $last_id = DB::getPdo()->lastInsertId(); $id_hasil_cicil[$table][$id_table] = $last_id; if ($table == 'tagihan_mahasiswa') $arr_TahunID[$data_table->Periode] = $data_table->Periode; }
                    }
                    foreach ($detail_pengajuan_pembayaran2 as $dpp) {
                        $table = $dpp->Jenis; $id_table = $dpp->EntitasID; $data_table = get_id($id_table, $table);
                        if (!$data_table) continue;
                        $input_cicilan = [$arr_nama_id_tagihan[$table] => $id_table, 'CicilanID' => $id_hasil_cicil['tagihan_mahasiswa'][$data_table->TagihanMahasiswaID] ?? null, 'NoKwitansi' => $KodePengajuan, 'TahunID' => $data_table->Periode, 'ProgramID' => $mhsw->ProgramID ?? null, 'ProdiID' => $mhsw->ProdiID ?? null, 'NPM' => $mhsw->NPM ?? null, 'Jumlah' => $dpp->JumlahBayar, 'TanggalBayar' => date("Y-m-d H:i:s"), 'TanggalBayarBank' => $TanggalBayarBank, 'JenisBayar' => 'Bank', 'TglBuat' => date("Y-m-d"), 'User' => session('UserID'), 'PengajuanPembayaranID' => $PengajuanPembayaranID];
                        if ($table == 'tagihan_mahasiswa_detail' || $table == 'tagihan_mahasiswa_termin') $input_cicilan['JenisBiayaID'] = $data_table->JenisBiayaID;
                        if ($table == 'tagihan_mahasiswa_detail') $input_cicilan['JenisBiayaID_Detail'] = $data_table->JenisBiayaID_Detail;
                        if ($table == 'tagihan_mahasiswa_termin') $input_cicilan['TerminKe'] = $data_table->TerminKe;
                        if (DB::table('cicilan_' . $table)->insert($input_cicilan)) { $last_id = DB::getPdo()->lastInsertId(); $id_hasil_cicil[$table][$id_table] = $last_id; }
                    }
                }
                if (count($id_hasil_cicil) > 0) {
                    sinkron_field_totalcicilan($MhswID, $id_hasil_cicil);
                    if (count($arr_TahunID) > 0 && ($mhsw && $mhsw->jenis_mhsw == 'mhsw')) update_opsi_mahasiswa($MhswID, $arr_TahunID);
                    if (count($list_jb) > 0 && $mhsw) {
                        if (in_array(57, $list_jb)) DB::table('wisudawan')->where('MhswID', $mhsw->ID)->update(['StatusBayar' => 1]);
                        if (in_array(59, $list_jb) && count($arr_TahunID) > 0) DB::table('keteranganstatusmahasiswa_cuti')->where('MhswID', $mhsw->ID)->whereIn('TahunID', $arr_TahunID)->update(['StatusBayar' => 1]);
                    }
                    if ($mhsw && $mhsw->jenis_mhsw == 'calon' && $mhsw->statusregistrasi_pmb == 1) {
                        $cekTagihan = DB::table('tagihan_mahasiswa')->join('jenisbiaya', 'jenisbiaya.ID', '=', 'tagihan_mahasiswa.JenisBiayaID')->where('tagihan_mahasiswa.MhswID', $MhswID)->where('jenisbiaya.ID', '!=', 32)->get();
                        $totalTagihan = 0; $totalCicilan = 0;
                        foreach ($cekTagihan as $valTag) { $totalTagihan += $valTag->Jumlah; $totalCicilan += $valTag->TotalCicilan; }
                        $statusbayar = 0;
                        if ($totalTagihan == $totalCicilan && $totalTagihan > 0) $statusbayar = 1;
                        else if ($totalTagihan > 0 && $totalCicilan > 0) $statusbayar = 3;
                        DB::table('mahasiswa')->where('ID', $MhswID)->update(['statusbayar_registrasi_pmb' => $statusbayar]);
                    }
                    DB::table('pengajuan_pembayaran')->where('ID', $PengajuanPembayaranID)->update(['LastUpdateUserID' => session('UserID') ?? 0, 'Status' => 1]);
                    $success = 1;
                }
            }
        }
        return $success;
    }
}

if (!function_exists('get_data_card_pembayaran')) {
    function get_data_card_pembayaran($MhswID, $type = 'mhsw')
    {
        $query = DB::table('tagihan_mahasiswa')->select('tagihan_mahasiswa.ID', 'tagihan_mahasiswa.ID as TagihanMahasiswaID', 'tagihan_mahasiswa.TagihanMahasiswaSemesterID', 'jenisbiaya.Nama as JenisBiaya', 'jenisbiaya.ID as JenisBiayaID', 'tagihan_mahasiswa.DueDate', 'tagihan_mahasiswa.Status', 'tagihan_mahasiswa.Lunas', 'tagihan_mahasiswa.Periode', 'tagihan_mahasiswa.Jumlah', 'tagihan_mahasiswa.TotalCicilan', 'tagihan_mahasiswa.TotalTagihan', 'tagihan_mahasiswa.JumlahDiskon', DB::raw('ifnull(tagihan_mahasiswa.TanggalTagihan,tagihan_mahasiswa.Tanggal) as TanggalTagihan'), 'tagihan_mahasiswa.Sisa', 'tahun.TahunID as KodeTahun', 'tahun.Nama as Tahun')->join('tahun', 'tahun.ID', '=', 'tagihan_mahasiswa.Periode')->leftJoin('jenisbiaya', 'tagihan_mahasiswa.JenisBiayaID', '=', 'jenisbiaya.ID')->leftJoin(DB::raw("(SELECT TagihanMahasiswaID, SUM(Jumlah) as DiskonPMB from cicilan_tagihan_mahasiswa where JenisBayar='Diskon' group by TagihanMahasiswaID) as cicil"), 'tagihan_mahasiswa.ID', '=', 'cicil.TagihanMahasiswaID')->where('MhswID', $MhswID)->where('JenisMahasiswa', $type)->where('tagihan_mahasiswa.Sisa', '>', 0)->get();
        $query_detail = DB::table('tagihan_mahasiswa_detail')->select('tagihan_mahasiswa_detail.ID', 'tagihan_mahasiswa_detail.ID as TagihanMahasiswaDetailID', 'jenisbiaya.Nama as JenisBiaya', 'jenisbiaya.ID as JenisBiayaID', 'tagihan_mahasiswa_detail.Periode', 'tagihan_mahasiswa_detail.Jumlah', 'tagihan_mahasiswa_detail.TotalCicilan', 'tagihan_mahasiswa_detail.TotalTagihan', 'tagihan_mahasiswa_detail.JumlahDiskon', 'tagihan_mahasiswa_detail.Sisa', 'tahun.TahunID as KodeTahun', 'tahun.Nama as Tahun')->join('tahun', 'tahun.ID', '=', 'tagihan_mahasiswa_detail.Periode')->leftJoin('jenisbiaya', 'tagihan_mahasiswa_detail.JenisBiayaID', '=', 'jenisbiaya.ID')->leftJoin(DB::raw("(SELECT TagihanMahasiswaDetailID, SUM(Jumlah) as DiskonPMB from cicilan_tagihan_mahasiswa_detail where JenisBayar='Diskon' group by TagihanMahasiswaDetailID) as cicil"), 'tagihan_mahasiswa_detail.ID', '=', 'cicil.TagihanMahasiswaDetailID')->where('MhswID', $MhswID)->where('JenisMahasiswa', $type)->where('tagihan_mahasiswa_detail.Sisa', '>', 0)->get();
        $query_termin = DB::table('tagihan_mahasiswa_termin')->select('tagihan_mahasiswa_termin.ID', 'tagihan_mahasiswa_termin.ID as TagihanMahasiswaTerminID', 'tagihan_mahasiswa_termin.TagihanMahasiswaID', 'jenisbiaya.Nama as JenisBiaya', 'jenisbiaya.ID as JenisBiayaID', 'tagihan_mahasiswa_termin.Periode', 'tagihan_mahasiswa_termin.TerminKe', 'tagihan_mahasiswa_termin.Jumlah', 'tagihan_mahasiswa_termin.TotalCicilan', 'tagihan_mahasiswa_termin.TotalTagihan', 'tagihan_mahasiswa_termin.JumlahDiskon', 'tagihan_mahasiswa_termin.Sisa', 'tahun.TahunID as KodeTahun', 'tahun.Nama as Tahun')->join('tahun', 'tahun.ID', '=', 'tagihan_mahasiswa_termin.Periode')->leftJoin('jenisbiaya', 'tagihan_mahasiswa_termin.JenisBiayaID', '=', 'jenisbiaya.ID')->leftJoin(DB::raw("(SELECT TagihanMahasiswaTerminID, SUM(Jumlah) as DiskonPMB from cicilan_tagihan_mahasiswa_termin where JenisBayar='Diskon' group by TagihanMahasiswaTerminID) as cicil"), 'tagihan_mahasiswa_termin.ID', '=', 'cicil.TagihanMahasiswaTerminID')->where('MhswID', $MhswID)->where('tagihan_mahasiswa_termin.Sisa', '>', 0)->orderBy('jenisbiaya.Urut', 'ASC')->orderBy('tagihan_mahasiswa_termin.TerminKe', 'ASC')->get();
        $query_semester = DB::table('tagihan_mahasiswa_semester')->select('tagihan_mahasiswa_semester.ID', 'tagihan_mahasiswa_semester.ID as TagihanMahasiswaSemesterID', 'tagihan_mahasiswa_semester.Periode', 'tagihan_mahasiswa_semester.Jumlah', 'tagihan_mahasiswa_semester.Semester', 'tagihan_mahasiswa_semester.TotalCicilan', 'tagihan_mahasiswa_semester.TotalTagihan', 'tagihan_mahasiswa_semester.JumlahDiskon', 'tagihan_mahasiswa_semester.Sisa', 'tahun.TahunID as KodeTahun', 'tahun.Nama as Tahun')->join('tahun', 'tahun.ID', '=', 'tagihan_mahasiswa_semester.Periode')->leftJoin(DB::raw("(SELECT TagihanMahasiswaSemesterID, SUM(Jumlah) as DiskonPMB from cicilan_tagihan_mahasiswa_semester where JenisBayar='Diskon' group by TagihanMahasiswaSemesterID) as cicil"), 'tagihan_mahasiswa_semester.ID', '=', 'cicil.TagihanMahasiswaSemesterID')->where('MhswID', $MhswID)->where('tagihan_mahasiswa_semester.Sisa', '>', 0)->orderBy('tagihan_mahasiswa_semester.Semester', 'ASC')->get();
        $query_termin_semester = DB::table('tagihan_mahasiswa_termin_semester')->select('tagihan_mahasiswa_termin_semester.ID', 'tagihan_mahasiswa_termin_semester.ID as TagihanMahasiswaTerminSemesterID', 'tagihan_mahasiswa_termin_semester.TagihanMahasiswaTerminID_list', 'tagihan_mahasiswa_termin_semester.TagihanMahasiswaSemesterID', 'tagihan_mahasiswa_termin_semester.TerminKe', 'tagihan_mahasiswa_termin_semester.Semester', 'tagihan_mahasiswa_termin_semester.Periode', 'tagihan_mahasiswa_termin_semester.Jumlah', 'tagihan_mahasiswa_termin_semester.TotalCicilan', 'tagihan_mahasiswa_termin_semester.Sisa', 'tahun.TahunID as KodeTahun', 'tahun.Nama as Tahun')->join('tahun', 'tahun.ID', '=', 'tagihan_mahasiswa_termin_semester.Periode')->leftJoin(DB::raw("(SELECT TagihanMahasiswaTerminSemesterID, SUM(Jumlah) as DiskonPMB from cicilan_tagihan_mahasiswa_termin_semester where JenisBayar='Diskon' group by TagihanMahasiswaTerminSemesterID) as cicil"), 'tagihan_mahasiswa_termin_semester.ID', '=', 'cicil.TagihanMahasiswaTerminSemesterID')->where('tagihan_mahasiswa_termin_semester.Sisa', '>', 0)->where('MhswID', $MhswID)->orderBy('tagihan_mahasiswa_termin_semester.TerminKe', 'ASC')->get();
        $query_termin_total = DB::table('tagihan_mahasiswa_termin_total')->select('tagihan_mahasiswa_termin_total.ID', 'tagihan_mahasiswa_termin_total.ID as TagihanMahasiswaTerminTotalID', 'tagihan_mahasiswa_termin_total.TagihanMahasiswaSemesterID_list', 'tagihan_mahasiswa_termin_total.Jumlah', 'tagihan_mahasiswa_termin_total.TerminKe', 'tagihan_mahasiswa_termin_total.Sisa', 'tagihan_mahasiswa_termin_total.TotalCicilan')->leftJoin(DB::raw("(SELECT TagihanMahasiswaTerminTotalID, SUM(Jumlah) as DiskonPMB from cicilan_tagihan_mahasiswa_termin_total where JenisBayar='Diskon' group by TagihanMahasiswaTerminTotalID) as cicil"), 'tagihan_mahasiswa_termin_total.ID', '=', 'cicil.TagihanMahasiswaTerminTotalID')->where('tagihan_mahasiswa_termin_total.Sisa', '>', 0)->where('MhswID', $MhswID)->orderBy('tagihan_mahasiswa_termin_total.TerminKe', 'ASC')->groupBy('tagihan_mahasiswa_termin_total.ID')->get();

        $data_tagihan = []; $data_tagihan_by_id = []; $data_tagihan_detail = []; $data_tagihan_termin = []; $data_tagihan_termin_by_semester = []; $data_tagihan_semester = []; $data_tagihan_termin_total_arr = []; $data_tagihan_termin_semester = [];
        foreach ($query as $row) { $data_tagihan[$row->TagihanMahasiswaSemesterID][] = $row; $data_tagihan_by_id[$row->TagihanMahasiswaID] = $row; }
        foreach ($query_detail as $row) { $data_tagihan_detail[$row->TagihanMahasiswaID][] = $row; }
        foreach ($query_termin as $row) { $data_tagihan_termin[$row->TagihanMahasiswaTerminID] = $row; $id_sem = $data_tagihan_by_id[$row->TagihanMahasiswaID]->TagihanMahasiswaSemesterID ?? null; if ($id_sem) $data_tagihan_termin_by_semester[$id_sem][] = $row; }
        foreach ($query_semester as $row) { $data_tagihan_semester[$row->TagihanMahasiswaSemesterID] = $row; }
        foreach ($query_termin_semester as $row) { $data_tagihan_termin_semester[$row->TagihanMahasiswaSemesterID][] = $row; }
        foreach ($query_termin_total as $row) {
            $tt = get_id($row->TagihanMahasiswaTerminTotalID, 'tagihan_mahasiswa_termin_total');
            if ($tt && $tt->TagihanMahasiswaSemesterID_list) {
                $jum = DB::selectOne("SELECT SUM(ifnull(Jumlah,0)) as sum_j, SUM(ifnull(Sisa,0)) as sum_s from tagihan_mahasiswa_semester where ID in ($tt->TagihanMahasiswaSemesterID_list) ");
                $row->Jumlah = $jum->sum_j ?? 0; $row->Sisa = $jum->sum_s ?? 0; $row->TotalCicilan = ($jum->sum_j ?? 0) - ($jum->sum_s ?? 0);
            } else { $row->Jumlah = 0; $row->Sisa = 0; $row->TotalCicilan = 0; }
            $data_tagihan_termin_total_arr[] = $row;
        }
        return ['data_tagihan' => $data_tagihan, 'data_tagihan_detail' => $data_tagihan_detail, 'data_tagihan_termin' => $data_tagihan_termin, 'data_tagihan_termin_by_semester' => $data_tagihan_termin_by_semester, 'data_tagihan_semester' => $data_tagihan_semester, 'data_tagihan_termin_semester' => $data_tagihan_termin_semester, 'data_tagihan_termin_total' => $data_tagihan_termin_total_arr];
    }
}

if (!function_exists('cek_pengajuan_pembayaran')) {
    function cek_pengajuan_pembayaran($MhswID, $Status = '0')
    {
        $cek = DB::selectOne("SELECT * from pengajuan_pembayaran where MhswID='$MhswID' and Status='$Status' ");
        if (!empty($cek) && $cek->ChannelPembayaranID) {
            $ch = get_id($cek->ChannelPembayaranID, 'channel_pembayaran');
            $cek->MetodePembayaranID = $ch->MetodePembayaranID; $cek->NamaChannel = $ch->Nama; $cek->list_panduan = [];
            $q_p = DB::table('panduan_pembayaran')->where('ChannelPembayaranID', $ch->ID)->get();
            foreach ($q_p as $r_p) { $cek->list_panduan[$r_p->NamaPanduan] = $r_p->TextCaraBayar; }
            $bank = get_id($cek->BankID, 'bank');
            $cek->NamaBank = $bank->NamaBank ?? ''; $cek->NoRekening = $bank->NoRekening ?? ''; $cek->AtasNama = $bank->NamaPemilik ?? ''; $cek->Icon = $ch->Icon; $cek->Prefix = $ch->Prefix; $cek->KodeIntegrasi = $ch->KodeIntegrasi;
            if ($ch->KodeIntegrasi == 'BSI') { $bsi = get_setup_app("setup_bsi"); if ($bsi) $cek->setup_bsi = json_decode($bsi->metadata, true); }
            $cek->NamaMetode = get_field($ch->MetodePembayaranID, 'metode_pembayaran');
        }
        return $cek;
    }
}

if (!function_exists('all_pengajuan_pembayaran_mahasiswa')) {
    function all_pengajuan_pembayaran_mahasiswa($MhswID) { return DB::select("SELECT * from pengajuan_pembayaran where MhswID='$MhswID' and DariMahasiswa='1' order by createdAt ASC "); }
}

if (!function_exists('get_file_bukti_bayar_pengajuan_pembayaran')) {
    function get_file_bukti_bayar_pengajuan_pembayaran($ID) { return DB::table('file_bukti_bayar')->where('PengajuanPembayaranID', $ID)->get(); }
}

if (!function_exists('get_negara')) {
    function get_negara($k = '', $c = '') {
        $q = DB::table('kewarganegaraan');
        if ($c) $q->where('Kode', $c); if ($k) $q->where('Nama', 'like', "%$k%");
        return $q->limit(20)->get();
    }
}

if (!function_exists('hasilstudi')) {
    function hasilstudi($MhswID, $TahunID, $tipe)
    {
        $a = DB::selectOne("SELECT COUNT(ID) as jum FROM hasilstudi WHERE MhswID='$MhswID'");
        $tN = get_id($TahunID, 'tahun'); if (!$tN) return 0;
        $m = DB::table('mahasiswa')->select('ID', 'NPM', 'ProgramID', 'ProdiID', 'TahunMasuk')->where('ID', $MhswID)->first();
        $pT = DB::selectOne("SELECT rencanastudi.TahunID from rencanastudi inner join tahun on tahun.ID=rencanastudi.TahunID where tahun.TahunID < '" . $tN->TahunID . "' AND tahun.Semester != '3' AND rencanastudi.MhswID='$MhswID' group by rencanastudi.TahunID order by tahun.TahunID DESC limit 1");
        $aT = DB::select("SELECT rencanastudi.TahunID from rencanastudi inner join tahun on tahun.ID=rencanastudi.TahunID where tahun.TahunID < '" . $tN->TahunID . "' AND tahun.Semester != '3' AND rencanastudi.MhswID='$MhswID' group by rencanastudi.TahunID order by tahun.TahunID DESC");
        $cT = count($aT);
        if ($tipe == 1) return $cT + 1;
        elseif ($tipe == '2') {
            if ($tN->Semester == 3) { $res = DB::table('sks_semester_pendek')->first(); return $res->Jumlah ?? 0; }
            else {
                $smt = $cT + 1; $prodiID = $m->ProdiID ?? 0; $programID = $m->ProgramID ?? 0;
                if ($smt > 2) {
                    if ($pT) {
                        $ips = view_ips($MhswID, $pT->TahunID);
                        $ips_val = ($ips && $ips->IPS != 'nan' && $ips->IPS !== null) ? $ips->IPS : 0;
                        $rangesks = DB::table('rangesks')->whereRaw("(IPK_Awal <= $ips_val AND IPK_Akhir >= $ips_val)")->whereRaw("FIND_IN_SET('$prodiID',ProdiIDlist) != 0")->whereRaw("FIND_IN_SET('$programID',ProgramIDlist)")->orderBy('SKS', 'DESC')->first();
                        return $rangesks->SKS ?? '24';
                    } else return '24';
                } else return '24';
            }
        } elseif ($tipe == '3') { $b = DB::selectOne("SELECT SUM(b.TotalSKS) as SKS FROM rencanastudi a,detailkurikulum b WHERE a.DetailKurikulumID=b.ID AND a.MhswID='$MhswID' AND a.TahunID='$TahunID'"); return $b->SKS ?? 0; }
        elseif ($tipe == '4') { $b = DB::selectOne("SELECT SUM(ifnull(b.SKSTatapMuka,0)) as SKS FROM rencanastudi a,detailkurikulum b WHERE a.DetailKurikulumID=b.ID AND a.MhswID='$MhswID' AND a.TahunID='$TahunID' "); return $b->SKS ?? 0; }
        elseif ($tipe == '5') { $b = DB::selectOne("SELECT SUM(ifnull(b.SKSPraktikum,0)) + SUM(ifnull(b.SKSPraktekLap,0)) as SKS FROM rencanastudi a,detailkurikulum b WHERE a.DetailKurikulumID=b.ID AND a.MhswID='$MhswID' AND a.TahunID='$TahunID' "); return $b->SKS ?? 0; }
    }
}

if (!function_exists('getKriteriaKuisioner')) {
    function getKriteriaKuisioner($angka) { $k = ''; if ($angka >= 90 && $angka <= 100) $k = 'SANGAT BAIK'; else if ($angka >= 80 && $angka <= 89.9) $k = 'BAIK'; else if ($angka >= 70 && $angka <= 79.9) $k = 'CUKUP'; else if ($angka >= 60 && $angka <= 69.9) $k = 'KURANG'; else if ($angka <= 59.9) $k = 'SANGAT KURANG'; return $k; }
}

if (!function_exists('get_nama_bulan')) {
    function get_nama_bulan($b) { $l = [1 => "Januari", 2 => "Februari", 3 => "Maret", 4 => "April", 5 => "Mei", 6 => "Juni", 7 => "Juli", 8 => "Agustus", 9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember"]; return $l[$b] ?? ""; }
}

if (!function_exists('month_to_yearmonth')) {
    function month_to_yearmonth($i, $a = false) { $y = floor($i / 12); $dy = ($y == 0) ? '' : $y . ' Tahun'; $m = ($i % 12); $dm = ($m == 0) ? '' : $m . ' Bulan'; $d = trim($dy . ' ' . $dm); return $a ? [$y, $m] : $d; }
}

if (!function_exists('in_array_r')) {
    function in_array_r($n, $h, $s = false) { foreach ($h as $item) { if (($s ? $item === $n : $item == $n) || (is_array($item) && in_array_r($n, $item, $s))) return true; } return false; }
}

if (!function_exists('getNoIjazah')) {
    function getNoIjazah($tahunLulus, $prodiID, $noUrut)
    {
        $kodeProdi = DB::table('programstudi')->select("ProdiID")->where('ID', $prodiID)->first();
        $generateNumber = date('Y', strtotime($tahunLulus)) . '.' . (!empty($kodeProdi->ProdiID) ? str_pad($kodeProdi->ProdiID, 1, '0', STR_PAD_LEFT) : '00') . '.' . str_pad($noUrut, 3, '00', STR_PAD_LEFT);

        return $generateNumber;
    }
}

if (!function_exists('array_null_to_string')) {
    function array_null_to_string($row)
    {
        return array_map(function ($value) {
            return $value === NULL ? "" : $value;
        }, $row);
    }
}

if (!function_exists('array_push_before')) {
    function array_push_before($src, $in, $pos)
    {
        if (is_int($pos))
            $R = array_merge(array_slice($src, 0, $pos), $in, array_slice($src, $pos));
        else {
            $R = [];
            foreach ($src as $k => $v) {
                if ($k == $pos)
                    $R = array_merge($R, $in);
                $R[$k] = $v;
            }
        }
        return $R;
    }
}

if (!function_exists('array_push_after')) {
    function array_push_after($src, $in, $pos)
    {
        if (is_int($pos))
            $R = array_merge(array_slice($src, 0, $pos + 1), $in, array_slice($src, $pos + 1));
        else {
            $R = [];
            foreach ($src as $k => $v) {
                $R[$k] = $v;
                if ($k == $pos)
                    $R = array_merge($R, $in);
            }
        }
        return $R;
    }
}


if (!function_exists('number_to_romanic')) {
    function number_to_romanic($integer, $upcase = true)
    {
        $table = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
        $return = '';
        while ($integer > 0) {
            foreach ($table as $rom => $arb) {
                if ($integer >= $arb) {
                    $integer -= $arb;
                    $return .= $rom;
                    break;
                }
            }
        }

        return $return;
    }
}

if (!function_exists('romanic_to_number')) {
    function romanic_to_number($roman)
    {
        $conv = array(
                array("letter" => 'I', "number" => 1),
                array("letter" => 'V', "number" => 5),
                array("letter" => 'X', "number" => 10),
                array("letter" => 'L', "number" => 50),
                array("letter" => 'C', "number" => 100),
                array("letter" => 'D', "number" => 500),
                array("letter" => 'M', "number" => 1000),
                array("letter" => 0, "number" => 0)
        );
        $arabic = 0;
        $state = 0;
        $len = strlen($roman);

        for ($sidx = $len - 1; $sidx >= 0; $sidx--) {
            $char = strtoupper($roman[$sidx]);
            foreach ($conv as $c) {
                if ($char == $c['letter']) {
                    if ($state > $c['number']) {
                        $arabic -= $c['number'];
                    }
                    else {
                        $arabic += $c['number'];
                        $state = $c['number'];
                    }
                    break;
                }
            }
        }

        return ($arabic);
    }
}

// Duplicated array_push_before per original file
if (!function_exists('array_push_before_v2')) {
    // Note: The original file had duplicated functions with same name.
    // In PHP this would cause a fatal error if NOT wrapped in if(!function_exists).
    // The original file DOES wrap them, so they only define if the first one fails?
    // Actually, the wrap name is 'array_push_before', so the second one will NEVER be defined.
    // I will keep the structure exactly as in original.
}

if (!function_exists('array_push_before')) {
    function array_push_before($src, $in, $pos)
    {
        if (is_int($pos))
            $R = array_merge(array_slice($src, 0, $pos), $in, array_slice($src, $pos));
        else {
            $R = [];
            foreach ($src as $k => $v) {
                if ($k == $pos)
                    $R = array_merge($R, $in);
                $R[$k] = $v;
            }
        }
        return $R;
    }
}

if (!function_exists('array_push_after')) {
    function array_push_after($src, $in, $pos)
    {
        if (is_int($pos))
            $R = array_merge(array_slice($src, 0, $pos + 1), $in, array_slice($src, $pos + 1));
        else {
            $R = [];
            foreach ($src as $k => $v) {
                $R[$k] = $v;
                if ($k == $pos)
                    $R = array_merge($R, $in);
            }
        }
        return $R;
    }
}

if (!function_exists('cek_relasi')) {
    function cek_relasi($id, $field, $target)
    {
        $count = DB::table($target)->where($field, $id)->count();
        if ($count > 0) {
            return 'false';
        }
        else {
            return 'true';
        }
    }
}

if (!function_exists('assets')) {
    function assets()
    {
        $query = DB::table('identitas')->first();

        $path = (request()->secure() ? "https" : "http");
        $path .= "://" . request()->getHost();
        return $path . '/client/' . ($query->KodePT ?? '') . "/";
    }
}


if (!function_exists('web_folder')) {
    function web_folder()
    {
        return session("web_folder");
    }
}

if (!function_exists('rand_color')) {
    function rand_color()
    {
        $color = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        return $color;
    }
}

if (!function_exists('add_time')) {
    function add_time($time1, $time2, $operand = "+")
    {
        $time1_unix = strtotime(date('Y-m-d') . ' ' . $time1);
        $time2_unix = strtotime(date('Y-m-d') . ' ' . $time2);
        $begin_day_unix = strtotime(date('Y-m-d') . ' 00:00:00');
        if ($operand == '+')
            $jumlah_time = date('H:i', ($time1_unix + ($time2_unix - $begin_day_unix)));
        elseif ($operand == '-')
            $jumlah_time = date('H:i', ($time1_unix - ($time2_unix - $begin_day_unix)));

        return $jumlah_time;
    }
}

if (!function_exists('gantitanggal')) {
    function gantitanggal($tanggal, $format = "Y-m-d")
    {
        if ($tanggal == '0000-00-00' or $tanggal == '' or $tanggal == null) {
            return null;
        }
        else {
            $date = str_replace('/', '-', $tanggal);
            return date($format, strtotime($date));
        }
    }
}

if (!function_exists('logQuery__')) {
    function logQuery__($txt)
    {
        $insert = file_put_contents('/home/edudev/log.txt', $txt . "\n", FILE_APPEND);
        return $insert;
    }
}


if (!function_exists('log_akses')) {

    function log_akses($aksi, $note = '', $ID_Tabel = '', $Tabel = '')
    {
        if (session('username')) {
            $input = [];
            $input["IP"] = getIp();
            $input["aktifitas"] = $aksi;
            $input["note"] = $note;
            $input["url"] = request()->url();
            $input["user"] = session('username');
            $input["user_id"] = session('UserID') ?? 0;
            $input["ID_Tabel"] = $ID_Tabel;
            $input["Tabel"] = $Tabel;
            return DB::table("log")->insert($input);
        }
        else if (session('sebagai') == 'mahasiswa') {
            $input = [];
            $input["IP"] = getIp();
            $input["aktifitas"] = $aksi;
            $input["note"] = get_field(session('EntityID2'), 'mahasiswa', 'NPM') . " " . get_field(session('EntityID2'), 'mahasiswa');
            $input["url"] = request()->url();
            $input["user"] = session('EntityID2');
            return DB::table("log_student")->insert($input);
        }
    }
}

if (!function_exists('log_note')) {

    function log_note($input, $input2)
    {
        $arr = array_diff_assoc($input, $input2);
        $note = 'Mengubah Data Mahasiswa Dengan';

        $arrField = array("ProdiID" => "programstudi", "StatusMhswID" => "statusmahasiswa");
        $arrField2 = array("ProdiID" => "Nama", "StatusMhswID" => "Nama");

        $arrCek = array_keys($arrField);

        foreach ($arr as $index => $val) {
            if (in_array($index, $arrCek)) {
                $test = get_field($input2[$index], $arrField[$index], $arrField2[$index]);
                $test2 = get_field($input[$index], $arrField[$index], $arrField2[$index]);
            }
            else {
                $test = $input2[$index];
                $test2 = $input[$index];
            }
            // Use __() or trans() for localization if available, or just keep it as is if it's a legacy helper
            $note .= ' ' . ($index) . ' <b>' . $test . '</b> menjadi <b>' . $test2 . '</b>, ';
        }

        return $arrField2[$index] ?? '';
    }
}

if (!function_exists('log_perubahan_konsentrasi')) {
    function log_perubahan_konsentrasi($row_konsen, $kons_baru, $kons_lama, $Asal, $UserID)
    {
        $insLogKons = [];
        $insLogKons['rencanastudiID'] = $row_konsen->ID;
        $insLogKons['MhswID'] = $row_konsen->MhswID;
        $insLogKons['NPM'] = $row_konsen->NPM;
        $insLogKons['DetailKurikulumID'] = $row_konsen->DetailKurikulumID;
        $insLogKons['KonsentrasiID_Baru'] = $kons_baru;
        $insLogKons['KonsentrasiID_Lama'] = $kons_lama;
        $insLogKons['TahunID'] = $row_konsen->TahunID;
        $insLogKons['URL'] = request()->url();
        $insLogKons['Asal'] = $Asal;
        $insLogKons['createdAt'] = date('Y-m-d H:i:s');
        $insLogKons['UserID'] = $UserID;

        $insert = DB::table('log_perubahan_konsentrasi')->insert($insLogKons);
        return $insert;
    }
}

if (!function_exists('log_tugas_mahasiswa')) {
    function log_tugas_mahasiswa($JadwalWaktuID = '', $MhswID = '', $File = '', $Ukuran = '', $UserID = '')
    {
        $input = array();
        $input['JadwalWaktuID'] = $JadwalWaktuID;
        $input['MhswID'] = $MhswID;
        $input['File'] = $File;
        $input['Ukuran'] = $Ukuran;
        $input['URL'] = request()->url();
        $input['TanggalEntry'] = date('Y-m-d H:i:s');
        $input['UserID'] = $UserID;

        $insert = DB::table('log_tugas_mahasiswa')->insert($input);
        return $insert;
    }
}


if (!function_exists('log_verifikasi_calon_mahasiswa')) {
    function log_verifikasi_calon_mahasiswa($MhswID = '', $Jenis = '', $NominalBayarFormulir = 0, $Hasil = 0, $UserID = '', $OlehUsername = '')
    {
        $mhsw = DB::table('mahasiswa')->select('ID', 'Nama', 'noujian_pmb')->where('ID', $MhswID)->first();
        if (!$mhsw) return false;

        $Aksi = ($Jenis == 1) ? 'verifikasi' : 'batal_verifikasi';
        $StatusBerhasil = ($Hasil == 1) ? 1 : 0;

        $input = array();
        $input['MhswID'] = $mhsw->ID;
        $input['Nama'] = $mhsw->Nama;
        $input['noujian_pmb'] = $mhsw->noujian_pmb;
        $input['Aksi'] = $Aksi;
        $input['StatusBerhasil'] = $StatusBerhasil;
        $input['NominalBayarFormulir'] = $NominalBayarFormulir;
        $input['URL'] = request()->url();
        $input['createdAt'] = date('Y-m-d H:i:s');
        $input['updatedAt'] = date('Y-m-d H:i:s');
        $input['UserID'] = $UserID;
        $input['OlehUsername'] = $OlehUsername;

        $insert = DB::table('log_verifikasi_calon_mahasiswa')->insert($input);
        return $insert;
    }
}

if (!function_exists('logs')) {
    function logs($note)
    {
        $userid = session('UserID');
        $u = DB::selectOne("SELECT EntityID, Nama, TabelUserID FROM user WHERE ID=?", [$userid]);
        if ($u && $u->EntityID) {

            if ($u->TabelUserID == 1) {
                $tabel = "karyawan";
            }
            elseif ($u->TabelUserID == 2) {
                $tabel = 'dosen';
            }
            elseif ($u->TabelUserID == 4) {
                $tabel = 'mahasiswa';
            }
            else {
                return;
            }

            $iden = DB::selectOne("SELECT Nama FROM $tabel WHERE ID=?", [$u->EntityID]);
            if (!$iden) return;

            // CLIENT_HOST should be defined elsewhere or replaced with Laravel config
            $host = defined('CLIENT_HOST') ? CLIENT_HOST : 'default';
            $logDir = public_path("client/{$host}/logs/ais");
            if (!file_exists($logDir)) {
                mkdir($logDir, 0777, true);
            }
            $_logfilename = $logDir . "/log_" . date("Y-m") . ".txt";

            $ip = getIp();
            $url = request()->url();
            $date = date("Y-m-d H:i:s");
            $username = $u->Nama;
            $nama = $iden->Nama;
            
            $logContent = "[$ip][$url][$date][$username][$nama] \r\n" . $note . "\r\n";
            file_put_contents($_logfilename, $logContent, FILE_APPEND);
        }
    }
}

if (!function_exists('update_tagihan_sks')) {
    function update_tagihan_sks($MhswID, $TahunID, $TipeParam = '', $ais = 0)
    {
        $mhs = get_id($MhswID, 'mahasiswa');
        $tahunAktif = get_id($TahunID, 'tahun');
        if (!$mhs || !$tahunAktif) return 1;

        if ($mhs->jenis_mhsw == 'calon') {
            $mhs->ProdiID = $mhs->pilihan1;
        }

        $KodeTahun = $tahunAktif->TahunID;
        $ProgramID = $mhs->ProgramID;
        $ProdiID = $mhs->ProdiID;
        $TahunMasuk = $mhs->TahunMasuk;
        $JalurPendaftaran = $mhs->jalur_pmb;
        $JenisPendaftaran = $mhs->StatusPindahan;

        $pengecekanTanggalKRSOnline = pengecekanTanggalKRSOnline($mhs->ProdiID, $mhs->ProgramID, $mhs->TahunMasuk);

        if ($ais == 1) {
            $TipeCheckout = ($pengecekanTanggalKRSOnline['labelKRS'] == 'PKRS') ? 'PKRS' : 'KRS';
            $cek_checkout = DB::table('checkout_rencanastudi')->where(array('MhswID' => $mhs->ID, 'TahunID' => $tahunAktif->ID, 'Tipe' => $TipeCheckout))->first();

            if (!empty($cek_checkout)) {
                $setupHargaSKS = new stdClass();
                $setupHargaSKS->Nominal = $cek_checkout->HargaSKS;
                $setupHargaSKS->NominalPraktek = $cek_checkout->HargaSKSPraktek;
                $setupHargaSKS->HitungPraktek = ($cek_checkout->HargaSKSPraktek > 0) ? 1 : 0;
                $setupHargaSKS->NominalPaket = $cek_checkout->Nominal;
            }
            else {
                $whereHargaSKS = "(JenisPendaftaran='$mhs->StatusPindahan' OR JenisPendaftaran='0') AND (ProgramID='$mhs->ProgramID' OR ProgramID='0') AND (ProdiID='$mhs->ProdiID' OR ProdiID='0') AND (TahunMasuk='$mhs->TahunMasuk' OR TahunMasuk='0') ";
                $setupHargaSKS = DB::table('setup_harga_biaya_variable')
                    ->whereRaw($whereHargaSKS)
                    ->orderByRaw('ProgramID DESC, ProdiID DESC, TahunMasuk DESC')
                    ->where('Jenis', 'SKS')
                    ->first();
            }

            $jumlahSKS = hasilstudi($mhs->ID, $TahunID, 3);
            $jumlahSKSTeori = hasilstudi($mhs->ID, $TahunID, 4);
            $jumlahSKSPraktek = hasilstudi($mhs->ID, $TahunID, 5);
        }
        else {
            $cek_checkout = null;
            $jumlahSKS = hasilstudi($mhs->ID, $TahunID, 3);
            $jumlahSKSTeori = hasilstudi($mhs->ID, $TahunID, 4);
            $jumlahSKSPraktek = hasilstudi($mhs->ID, $TahunID, 5);

            $whereHargaSKS = "(JenisPendaftaran='$mhs->StatusPindahan' OR JenisPendaftaran='0') AND (ProgramID='$mhs->ProgramID' OR ProgramID='0') AND (ProdiID='$mhs->ProdiID' OR ProdiID='0') AND (TahunMasuk='$mhs->TahunMasuk' OR TahunMasuk='0') ";
            $setupHargaSKS = DB::table('setup_harga_biaya_variable')
                ->whereRaw($whereHargaSKS)
                ->orderByRaw('ProgramID DESC, ProdiID DESC, TahunMasuk DESC')
                ->where('Jenis', 'SKS')
                ->first();
        }

        $cek_paket = DB::table('paket_sks')->select('SemesterPaket')->where('ProdiID', $mhs->ProdiID)->first();
        $paket = $cek_paket ? explode(",", $cek_paket->SemesterPaket) : [];

        $count_krs = DB::table('rencanastudi')->where('MhswID', $mhs->ID)->where('TahunID', $TahunID)->count();

        if ($count_krs == 0) {
            $sem_res = get_semester($mhs->ID, $TahunID);
            $semester = ($sem_res->Semester ?? 0) + 1;
        }
        else {
            $sem_res = get_semester($mhs->ID, $TahunID);
            $semester = $sem_res->Semester ?? 0;
        }

        $apakah_paket = in_array($semester, $paket) ? 1 : 0;

        $jb = 33; # Jenis biaya SKS
        $rand = mt_rand(100, 999);
        $id_tagihan_ais = array();

        $noInvoiceGenerate = date("Y") . "-SKS-" . $tahunAktif->TahunID . "-" . ($mhs->NPM ?? '') . "-" . $rand;
        $NilaiTagihan = 0;

        if ($setupHargaSKS) {
            if ($apakah_paket == 0) {
                if ($setupHargaSKS->HitungPraktek == 1) {
                    $NilaiTagihanTeori = $jumlahSKSTeori * $setupHargaSKS->Nominal;
                    $NilaiTagihanPraktek = $jumlahSKSPraktek * $setupHargaSKS->NominalPraktek;
                    $NilaiTagihan = $NilaiTagihanTeori + $NilaiTagihanPraktek;
                }
                else {
                    $NilaiTagihan = $jumlahSKS * $setupHargaSKS->Nominal;
                }
            }
            else {
                $NilaiTagihan = $setupHargaSKS->NominalPaket;
            }

            $cek_apakah_skripsi = DB::table('rencanastudi')
                ->join('detailkurikulum', 'detailkurikulum.ID', '=', 'rencanastudi.DetailKurikulumID')
                ->where('rencanastudi.MhswID', $mhs->ID)
                ->where('rencanastudi.TahunID', $tahunAktif->ID)
                ->where('detailkurikulum.JenisMKID', '4')
                ->first();
            $cek_non_skripsi = DB::table('rencanastudi')
                ->join('detailkurikulum', 'detailkurikulum.ID', '=', 'rencanastudi.DetailKurikulumID')
                ->where('rencanastudi.MhswID', $mhs->ID)
                ->where('rencanastudi.TahunID', $tahunAktif->ID)
                ->where('detailkurikulum.JenisMKID', '!=', '4')
                ->first();

            if ($cek_apakah_skripsi && empty($cek_non_skripsi)) {
                $NilaiTagihan = $setupHargaSKS->NominalSkripsi;
            }
        }

        if ($NilaiTagihan > 0) {
            if ($ais == 1 && $cek_checkout) {
                $upd_c = [
                    'JmlSKSUpdate' => $jumlahSKS,
                    'HargaSKSUpdate' => $setupHargaSKS->Nominal ?? 0,
                    'HargaSKSPraktekUpdate' => $setupHargaSKS->NominalPraktek ?? 0,
                    'NominalUpdate' => $NilaiTagihan
                ];
                DB::table('checkout_rencanastudi')->where('ID', $cek_checkout->ID)->update($upd_c);
            }

            $whereBiaya = ['KodeTahun' => $KodeTahun, 'ProgramID' => $ProgramID, 'ProdiID' => $ProdiID, 'TahunMasuk' => $TahunMasuk, 'JenisPendaftaran' => $JenisPendaftaran, 'JalurPendaftaran' => $JalurPendaftaran];
            $biaya = DB::table('biaya')->where($whereBiaya)->first();
            // $biaya_semester = DB::table('biaya_semester')->where($whereBiaya)->first(); // Not used but in original
            // $biaya_termin_semester = DB::table('biaya_termin_semester')->where($whereBiaya)->first(); // Not used but in original

            $tagihan_semester = DB::table('tagihan_mahasiswa_semester')->where(['MhswID' => $mhs->ID, 'Periode' => $TahunID])->first();

            if ($tagihan_semester) {
                $tagihanMahasiswaSemesterID = $tagihan_semester->ID;
            }
            else {
                $inputTagihanSemester = [
                    'MhswID' => $mhs->ID,
                    'ProdiID' => $mhs->ProdiID,
                    'ProgramID' => $mhs->ProgramID,
                    'Periode' => $TahunID,
                    'Semester' => ($biaya && $biaya->Semester) ? $biaya->Semester : get_semester_tahunmasuk($TahunMasuk, $KodeTahun),
                    'TotalTagihan' => (int)$NilaiTagihan,
                    'Jumlah' => (int)$NilaiTagihan,
                    'Sisa' => (int)$NilaiTagihan,
                    'createdAt' => date('Y-m-d H:i:s'),
                    'UserID' => session('UserID') ?? 0
                ];
                $tagihanMahasiswaSemesterID = DB::table('tagihan_mahasiswa_semester')->insertGetId($inputTagihanSemester);
            }

            $insert = [
                'TagihanMahasiswaSemesterID' => $tagihanMahasiswaSemesterID,
                'Periode' => $TahunID,
                'ProgramID' => $mhs->ProgramID,
                'ProdiID' => $mhs->ProdiID,
                'TahunID' => $tahunAktif->TahunID,
                'JenisBiayaID' => $jb,
                'JenisMahasiswa' => $mhs->jenis_mhsw,
                'MhswID' => $mhs->ID,
                'NPM' => $mhs->NPM,
                'NoInvoice' => $noInvoiceGenerate,
                'TotalTagihan' => $NilaiTagihan,
                'Jumlah' => $NilaiTagihan,
                'Sisa' => $NilaiTagihan,
                'Tanggal' => date("Y-m-d H:i:s"),
                'TanggalTagihan' => date("Y-m-d"),
                'DikalikanSKS' => '1',
                'UserCreate' => session('UserID') ?? 0
            ];

            $cek = DB::table('tagihan_mahasiswa')->select('ID')->where(['MhswID' => $mhs->ID, 'Periode' => $TahunID, 'JenisBiayaID' => $jb])->first();

            if (empty($cek)) {
                $tagihanMahasiswaID = DB::table('tagihan_mahasiswa')->insertGetId($insert);
                $id_tagihan_ais[] = $tagihanMahasiswaID;
            }
            else {
                DB::table('tagihan_mahasiswa')->where('ID', $cek->ID)->update([
                    'TotalTagihan' => (int)$NilaiTagihan,
                    'Jumlah' => (int)$NilaiTagihan,
                    'Sisa' => DB::raw("CAST(".intval($NilaiTagihan)." AS SIGNED) - IFNULL(TotalCicilan, 0)")
                ]);
                $tagihanMahasiswaID = $cek->ID;
                $id_tagihan_ais[] = $cek->ID;
            }

            if ($tagihanMahasiswaID && $tagihanMahasiswaSemesterID) {
                $jum_all_semester = DB::table('tagihan_mahasiswa')->where('TagihanMahasiswaSemesterID', $tagihanMahasiswaSemesterID)
                    ->selectRaw('SUM(ifnull(TotalTagihan,0)) as sum_t, SUM(ifnull(Jumlah,0)) as sum_j, SUM(ifnull(Sisa,0)) as sum_s')->first();
                
                DB::table('tagihan_mahasiswa_semester')->where('ID', $tagihanMahasiswaSemesterID)->update([
                    'TotalTagihan' => $jum_all_semester->sum_t,
                    'Jumlah' => $jum_all_semester->sum_j,
                    'Sisa' => $jum_all_semester->sum_s
                ]);

                $tt_total = DB::table('tagihan_mahasiswa_termin_total')->whereRaw("FIND_IN_SET(?, TagihanMahasiswaSemesterID_list)", [$tagihanMahasiswaSemesterID])->where('MhswID', $mhs->ID)->first();
                if ($tt_total) {
                    $jum = DB::table('tagihan_mahasiswa_semester')->whereRaw("ID IN ($tt_total->TagihanMahasiswaSemesterID_list)")
                        ->selectRaw('SUM(ifnull(Jumlah,0)) as sj, SUM(ifnull(Sisa,0)) as ss')->first();
                    DB::table('tagihan_mahasiswa_termin_total')->where('ID', $tt_total->ID)->update(['Jumlah' => $jum->sj ?? 0, 'Sisa' => $jum->ss ?? 0]);
                }
            }

            $cek_termin = DB::table('tagihan_mahasiswa_termin')->where(['MhswID' => $mhs->ID, 'Periode' => $TahunID, 'JenisBiayaID' => $jb, 'TerminKe' => 1])->first();
            if (empty($cek_termin)) {
                $inputTermin = [
                    'TagihanMahasiswaID' => $tagihanMahasiswaID,
                    'ProgramID' => $mhs->ProgramID,
                    'Periode' => $TahunID,
                    'ProdiID' => $mhs->ProdiID,
                    'JenisBiayaID' => $jb,
                    'MhswID' => $mhs->ID,
                    'TotalTagihan' => $NilaiTagihan,
                    'Sisa' => $NilaiTagihan,
                    'Jumlah' => $NilaiTagihan,
                    'Semester' => ($biaya && $biaya->Semester) ? $biaya->Semester : get_semester_tahunmasuk($TahunMasuk, $KodeTahun),
                    'TerminKe' => 1,
                    'Tanggal' => date('Y-m-d H:i:s'),
                    'UserID' => session('UserID') ?? 0
                ];
                $tagihanMahasiswaTerminID = DB::table('tagihan_mahasiswa_termin')->insertGetId($inputTermin);
            }
            else {
                DB::table('tagihan_mahasiswa_termin')->where('ID', $cek_termin->ID)->update([
                    'TotalTagihan' => (int)$NilaiTagihan,
                    'Jumlah' => (int)$NilaiTagihan,
                    'Sisa' => DB::raw("CAST(".intval($NilaiTagihan)." AS SIGNED) - IFNULL(TotalCicilan, 0)")
                ]);
                $tagihanMahasiswaTerminID = $cek_termin->ID;
            }

            if ($tagihanMahasiswaTerminID) {
                $t_sem = DB::table('tagihan_mahasiswa_termin_semester')->whereRaw("FIND_IN_SET(?, TagihanMahasiswaTerminID_list)", [$tagihanMahasiswaTerminID])->where('MhswID', $mhs->ID)->first();
                if ($t_sem) {
                    $jum = DB::table('tagihan_mahasiswa_termin')->whereRaw("ID IN ($t_sem->TagihanMahasiswaTerminID_list)")
                        ->selectRaw('SUM(ifnull(Jumlah,0)) as sj, SUM(ifnull(Sisa,0)) as ss')->first();
                    DB::table('tagihan_mahasiswa_termin_semester')->where('ID', $t_sem->ID)->update(['Jumlah' => $jum->sj ?? 0, 'Sisa' => $jum->ss ?? 0]);
                }
            }

            if ($ais == 1 && $jumlahSKS == 0 && isset($cek_checkout->ID)) {
                DB::table('checkout_rencanastudi')->where('ID', $cek_checkout->ID)->delete();
            }

            if ($ais == 0 && count($id_tagihan_ais) > 0 && !empty($jumlahSKS)) {
                $ins_c = [
                    'MhswID' => $mhs->ID, 'NPM' => $mhs->NPM, 'Nama' => $mhs->Nama, 'TahunID' => $tahunAktif->ID, 'KodeTahun' => $tahunAktif->TahunID,
                    'Nominal' => $NilaiTagihan, 'JmlSKS' => $jumlahSKS, 'HargaSKS' => $setupHargaSKS->Nominal ?? 0, 'HargaSKSPraktek' => $setupHargaSKS->NominalPraktek ?? 0,
                    'Tipe' => $TipeParam, 'createdAt' => date('Y-m-d H:i:s'), 'UserID' => session('UserID') ?? 0
                ];
                $exists = DB::table('checkout_rencanastudi')->where(['MhswID' => $mhs->ID, 'TahunID' => $tahunAktif->ID, 'Tipe' => $TipeParam])->exists();
                if (!$exists) DB::table('checkout_rencanastudi')->insert($ins_c);
            }

            return $id_tagihan_ais;
        }
        return 1;
    }
}

if (!function_exists('update_tagihan_denda')) {
    function update_tagihan_denda($MhswID, $TahunID, $JumlahDenda = 0)
    {
        $mhs = get_id($MhswID, 'mahasiswa');
        $tahunAktif = get_id($TahunID, 'tahun');
        if (!$mhs || !$tahunAktif) return [];

        $KodeTahun = $tahunAktif->TahunID;
        $ProgramID = $mhs->ProgramID;
        $ProdiID = $mhs->ProdiID;
        $TahunMasuk = $mhs->TahunMasuk;
        $JalurPendaftaran = $mhs->jalur_pmb;
        $JenisPendaftaran = $mhs->StatusPindahan;

        $jb = 68; // DENDA
        $id_tagihan_ais = array();
        $rand = mt_rand(100, 999);
        $Jenis = 'DENDA';
        $noInvoiceGenerate = date("Y") . "-" . $Jenis . "-" . $tahunAktif->TahunID . "-" . ($mhs->NPM ?? '') . "-" . $rand;
        $NilaiTagihan = $JumlahDenda;

        #get sample biaya & biayasemester
        $whereBiaya = ['KodeTahun' => $KodeTahun, 'ProgramID' => $ProgramID, 'ProdiID' => $ProdiID, 'TahunMasuk' => $TahunMasuk, 'JenisPendaftaran' => $JenisPendaftaran, 'JalurPendaftaran' => $JalurPendaftaran];
        $biaya = DB::table('biaya')->where($whereBiaya)->first();
        // $biaya_semester = DB::table('biaya_semester')->where($whereBiaya)->first();
        // $biaya_termin_semester = DB::table('biaya_termin_semester')->where($whereBiaya)->first();

        $tagihan_semester = DB::table('tagihan_mahasiswa_semester')->where(['MhswID' => $mhs->ID, 'Periode' => $TahunID])->first();

        if ($tagihan_semester) {
            $tagihanMahasiswaSemesterID = $tagihan_semester->ID;
        }
        else {
            $inputTagihanSemester = [
                'BiayaSemesterID' => null,
                'MhswID' => $mhs->ID,
                'ProdiID' => $mhs->ProdiID,
                'ProgramID' => $mhs->ProgramID,
                'Periode' => $TahunID,
                'Semester' => ($biaya && $biaya->Semester) ? $biaya->Semester : get_semester_tahunmasuk($TahunMasuk, $KodeTahun),
                'TotalTagihan' => $NilaiTagihan,
                'JumlahDiskon' => 0,
                'TotalCicilan' => 0,
                'Jumlah' => $NilaiTagihan,
                'Sisa' => $NilaiTagihan,
                'createdAt' => date('Y-m-d H:i:s'),
                'updatedAt' => date('Y-m-d H:i:s'),
                'UserID' => session('UserID') ?? 0
            ];
            $tagihanMahasiswaSemesterID = DB::table('tagihan_mahasiswa_semester')->insertGetId($inputTagihanSemester);
        }

        $insert = [
            'TagihanMahasiswaSemesterID' => $tagihanMahasiswaSemesterID,
            'Periode' => $TahunID,
            'ProgramID' => $mhs->ProgramID,
            'ProdiID' => $mhs->ProdiID,
            'TahunID' => $tahunAktif->TahunID,
            'JenisBiayaID' => $jb,
            'JenisMahasiswa' => $mhs->jenis_mhsw,
            'MhswID' => $mhs->ID,
            'NPM' => $mhs->NPM,
            'NoInvoice' => $noInvoiceGenerate,
            'TotalTagihan' => $NilaiTagihan,
            'JumlahDiskon' => 0,
            'Jumlah' => $NilaiTagihan,
            'Sisa' => $NilaiTagihan,
            'TotalCicilan' => 0,
            'Lunas' => 0,
            'DueDate' => null,
            'Tanggal' => date("Y-m-d H:i:s"),
            'Update' => date("Y-m-d H:i:s"),
            'TanggalTagihan' => date("Y-m-d"),
            'DikalikanSKS' => '1',
            'UserCreate' => session('UserID') ?? 0
        ];

        $cek = DB::table('tagihan_mahasiswa')->select('ID')->where(['MhswID' => $mhs->ID, 'Periode' => $TahunID, 'JenisBiayaID' => $jb])->first();

        if (empty($cek)) {
            $tagihanMahasiswaID = DB::table('tagihan_mahasiswa')->insertGetId($insert);
            $id_tagihan_ais[] = $tagihanMahasiswaID;
        }
        else {
            DB::table('tagihan_mahasiswa')->where('ID', $cek->ID)->update([
                'TotalTagihan' => (int)$NilaiTagihan,
                'Jumlah' => (int)$NilaiTagihan,
                'Sisa' => DB::raw("CAST(".intval($NilaiTagihan)." AS SIGNED) - IFNULL(TotalCicilan, 0)")
            ]);
            $tagihanMahasiswaID = $cek->ID;
            $id_tagihan_ais[] = $cek->ID;
        }

        if ($tagihanMahasiswaID && $tagihanMahasiswaSemesterID) {
            $jum_all_semester = DB::table('tagihan_mahasiswa')->where('TagihanMahasiswaSemesterID', $tagihanMahasiswaSemesterID)
                ->selectRaw('SUM(ifnull(TotalTagihan,0)) as sum_t, SUM(ifnull(Jumlah,0)) as sum_j, SUM(ifnull(Sisa,0)) as sum_s')->first();
            
            DB::table('tagihan_mahasiswa_semester')->where('ID', $tagihanMahasiswaSemesterID)->update([
                'TotalTagihan' => $jum_all_semester->sum_t,
                'Jumlah' => $jum_all_semester->sum_j,
                'Sisa' => $jum_all_semester->sum_s
            ]);

            $tt_total = DB::table('tagihan_mahasiswa_termin_total')->whereRaw("FIND_IN_SET(?, TagihanMahasiswaSemesterID_list)", [$tagihanMahasiswaSemesterID])->where('MhswID', $mhs->ID)->first();
            if ($tt_total) {
                $jum = DB::table('tagihan_mahasiswa_semester')->whereRaw("ID IN ($tt_total->TagihanMahasiswaSemesterID_list)")
                    ->selectRaw('SUM(ifnull(Jumlah,0)) as sj, SUM(ifnull(Sisa,0)) as ss')->first();
                DB::table('tagihan_mahasiswa_termin_total')->where('ID', $tt_total->ID)->update(['Jumlah' => $jum->sj ?? 0, 'Sisa' => $jum->ss ?? 0]);
            }
        }

        $cek_termin = DB::table('tagihan_mahasiswa_termin')->where(['MhswID' => $mhs->ID, 'Periode' => $TahunID, 'JenisBiayaID' => $jb, 'TerminKe' => 1])->first();
        if (empty($cek_termin)) {
            $inputTermin = [
                'TagihanMahasiswaID' => $tagihanMahasiswaID,
                'ProgramID' => $mhs->ProgramID,
                'Periode' => $TahunID,
                'ProdiID' => $mhs->ProdiID,
                'JenisBiayaID' => $jb,
                'MhswID' => $mhs->ID,
                'TotalTagihan' => $NilaiTagihan,
                'Sisa' => $NilaiTagihan,
                'Jumlah' => $NilaiTagihan,
                'Semester' => ($biaya && $biaya->Semester) ? $biaya->Semester : get_semester_tahunmasuk($TahunMasuk, $KodeTahun),
                'TerminKe' => 1,
                'Tanggal' => date('Y-m-d H:i:s'),
                'Update' => date('Y-m-d H:i:s'),
                'UserID' => session('UserID') ?? 0
            ];
            $tagihanMahasiswaTerminID = DB::table('tagihan_mahasiswa_termin')->insertGetId($inputTermin);
        }
        else {
            DB::table('tagihan_mahasiswa_termin')->where('ID', $cek_termin->ID)->update([
                'TotalTagihan' => (int)$NilaiTagihan,
                'Jumlah' => (int)$NilaiTagihan,
                'Sisa' => DB::raw("CAST(".intval($NilaiTagihan)." AS SIGNED) - IFNULL(TotalCicilan, 0)")
            ]);
            $tagihanMahasiswaTerminID = $cek_termin->ID;
        }

        if ($tagihanMahasiswaTerminID) {
            $t_sem = DB::table('tagihan_mahasiswa_termin_semester')->whereRaw("FIND_IN_SET(?, TagihanMahasiswaTerminID_list)", [$tagihanMahasiswaTerminID])->where('MhswID', $mhs->ID)->first();
            if ($t_sem) {
                $jum = DB::table('tagihan_mahasiswa_termin')->whereRaw("ID IN ($t_sem->TagihanMahasiswaTerminID_list)")
                    ->selectRaw('SUM(ifnull(Jumlah,0)) as sj, SUM(ifnull(Sisa,0)) as ss')->first();
                DB::table('tagihan_mahasiswa_termin_semester')->where('ID', $t_sem->ID)->update(['Jumlah' => $jum->sj ?? 0, 'Sisa' => $jum->ss ?? 0]);
            }
        }

        return $id_tagihan_ais;
    }
}

if (!function_exists('update_tagihan_lainnya')) {
    function update_tagihan_lainnya($MhswID, $TahunID, $JumlahBiayaLainnya = 0, $JenisBiayaID = 0)
    {
        $mhs = get_id($MhswID, 'mahasiswa');
        $tahunAktif = get_id($TahunID, 'tahun');
        if (!$mhs || !$tahunAktif || !$JenisBiayaID) return [];

        $KodeTahun = $tahunAktif->TahunID;
        $ProgramID = $mhs->ProgramID;
        $ProdiID = $mhs->ProdiID;
        $TahunMasuk = $mhs->TahunMasuk;
        $JalurPendaftaran = $mhs->jalur_pmb;
        $JenisPendaftaran = $mhs->StatusPindahan;

        $jenisbiaya = get_id($JenisBiayaID, 'jenisbiaya');
        $Jenis = strtoupper($jenisbiaya->Nama ?? 'LAINNYA');
        $rand = mt_rand(100, 999);
        $noInvoiceGenerate = date("Y") . "-" . $Jenis . "-" . $tahunAktif->TahunID . "-" . ($mhs->NPM ?? '') . "-" . $rand;
        $NilaiTagihan = $JumlahBiayaLainnya;

        $whereBiaya = ['KodeTahun' => $KodeTahun, 'ProgramID' => $ProgramID, 'ProdiID' => $ProdiID, 'TahunMasuk' => $TahunMasuk, 'JenisPendaftaran' => $JenisPendaftaran, 'JalurPendaftaran' => $JalurPendaftaran];
        $biaya = DB::table('biaya')->where($whereBiaya)->first();

        $tagihan_semester = DB::table('tagihan_mahasiswa_semester')->where(['MhswID' => $mhs->ID, 'Periode' => $TahunID])->first();

        if ($tagihan_semester) {
            $tagihanMahasiswaSemesterID = $tagihan_semester->ID;
        }
        else {
            $inputTagihanSemester = [
                'MhswID' => $mhs->ID,
                'ProdiID' => $mhs->ProdiID,
                'ProgramID' => $mhs->ProgramID,
                'Periode' => $TahunID,
                'Semester' => ($biaya && $biaya->Semester) ? $biaya->Semester : get_semester_tahunmasuk($TahunMasuk, $KodeTahun),
                'TotalTagihan' => $NilaiTagihan,
                'JumlahDiskon' => 0,
                'TotalCicilan' => 0,
                'Jumlah' => $NilaiTagihan,
                'Sisa' => $NilaiTagihan,
                'createdAt' => date('Y-m-d H:i:s'),
                'UserID' => session('UserID') ?? 0
            ];
            $tagihanMahasiswaSemesterID = DB::table('tagihan_mahasiswa_semester')->insertGetId($inputTagihanSemester);
        }

        $insert = [
            'TagihanMahasiswaSemesterID' => $tagihanMahasiswaSemesterID,
            'Periode' => $TahunID,
            'ProgramID' => $mhs->ProgramID,
            'ProdiID' => $mhs->ProdiID,
            'TahunID' => $tahunAktif->TahunID,
            'JenisBiayaID' => $JenisBiayaID,
            'JenisMahasiswa' => $mhs->jenis_mhsw,
            'MhswID' => $mhs->ID,
            'NPM' => $mhs->NPM,
            'NoInvoice' => $noInvoiceGenerate,
            'TotalTagihan' => $NilaiTagihan,
            'JumlahDiskon' => 0,
            'Jumlah' => $NilaiTagihan,
            'Sisa' => $NilaiTagihan,
            'Tanggal' => date("Y-m-d H:i:s"),
            'Update' => date("Y-m-d H:i:s"),
            'TanggalTagihan' => date("Y-m-d"),
            'DikalikanSKS' => '1',
            'UserCreate' => session('UserID') ?? 0
        ];

        $cek = DB::table('tagihan_mahasiswa')->select('ID')->where(['MhswID' => $mhs->ID, 'Periode' => $TahunID, 'JenisBiayaID' => $JenisBiayaID])->first();
        $id_tagihan_ais = [];

        if (empty($cek)) {
            $tagihanMahasiswaID = DB::table('tagihan_mahasiswa')->insertGetId($insert);
            $id_tagihan_ais[] = $tagihanMahasiswaID;
        }
        else {
            DB::table('tagihan_mahasiswa')->where('ID', $cek->ID)->update([
                'TotalTagihan' => (int)$NilaiTagihan,
                'Jumlah' => (int)$NilaiTagihan,
                'Sisa' => DB::raw("CAST(".intval($NilaiTagihan)." AS SIGNED) - IFNULL(TotalCicilan, 0)")
            ]);
            $tagihanMahasiswaID = $cek->ID;
            $id_tagihan_ais[] = $cek->ID;
        }

        if ($tagihanMahasiswaID && $tagihanMahasiswaSemesterID) {
            $jum = DB::table('tagihan_mahasiswa')->where('TagihanMahasiswaSemesterID', $tagihanMahasiswaSemesterID)
                ->selectRaw('SUM(ifnull(TotalTagihan,0)) as st, SUM(ifnull(Jumlah,0)) as sj, SUM(ifnull(Sisa,0)) as ss')->first();
            DB::table('tagihan_mahasiswa_semester')->where('ID', $tagihanMahasiswaSemesterID)->update(['TotalTagihan' => $jum->st, 'Jumlah' => $jum->sj, 'Sisa' => $jum->ss]);

            $tt = DB::table('tagihan_mahasiswa_termin_total')->whereRaw("FIND_IN_SET(?, TagihanMahasiswaSemesterID_list)", [$tagihanMahasiswaSemesterID])->where('MhswID', $mhs->ID)->first();
            if ($tt) {
                $jum_s = DB::table('tagihan_mahasiswa_semester')->whereRaw("ID IN ($tt->TagihanMahasiswaSemesterID_list)")->selectRaw('SUM(ifnull(Jumlah,0)) as sj, SUM(ifnull(Sisa,0)) as ss')->first();
                DB::table('tagihan_mahasiswa_termin_total')->where('ID', $tt->ID)->update(['Jumlah' => $jum_s->sj, 'Sisa' => $jum_s->ss]);
            }
        }

        $cek_termin = DB::table('tagihan_mahasiswa_termin')->where(['MhswID' => $mhs->ID, 'Periode' => $TahunID, 'JenisBiayaID' => $JenisBiayaID, 'TerminKe' => 1])->first();
        if (empty($cek_termin)) {
            $inputTermin = [
                'TagihanMahasiswaID' => $tagihanMahasiswaID,
                'ProgramID' => $mhs->ProgramID,
                'Periode' => $TahunID,
                'ProdiID' => $mhs->ProdiID,
                'JenisBiayaID' => $JenisBiayaID,
                'MhswID' => $mhs->ID,
                'TotalTagihan' => $NilaiTagihan,
                'Sisa' => $NilaiTagihan,
                'Jumlah' => $NilaiTagihan,
                'Semester' => ($biaya && $biaya->Semester) ? $biaya->Semester : get_semester_tahunmasuk($TahunMasuk, $KodeTahun),
                'TerminKe' => 1,
                'Tanggal' => date('Y-m-d H:i:s'),
                'Update' => date('Y-m-d H:i:s'),
                'UserID' => session('UserID') ?? 0
            ];
            $tagihanMahasiswaTerminID = DB::table('tagihan_mahasiswa_termin')->insertGetId($inputTermin);
        }
        else {
            DB::table('tagihan_mahasiswa_termin')->where('ID', $cek_termin->ID)->update([
                'TotalTagihan' => (int)$NilaiTagihan, 'Jumlah' => (int)$NilaiTagihan,
                'Sisa' => DB::raw("CAST(".intval($NilaiTagihan)." AS SIGNED) - IFNULL(TotalCicilan, 0)")
            ]);
            $tagihanMahasiswaTerminID = $cek_termin->ID;
        }

        if ($tagihanMahasiswaTerminID) {
            $t_sem = DB::table('tagihan_mahasiswa_termin_semester')->whereRaw("FIND_IN_SET(?, TagihanMahasiswaTerminID_list)", [$tagihanMahasiswaTerminID])->where('MhswID', $mhs->ID)->first();
            if ($t_sem) {
                $jum_t = DB::table('tagihan_mahasiswa_termin')->whereRaw("ID IN ($t_sem->TagihanMahasiswaTerminID_list)")->selectRaw('SUM(ifnull(Jumlah,0)) as sj, SUM(ifnull(Sisa,0)) as ss')->first();
                DB::table('tagihan_mahasiswa_termin_semester')->where('ID', $t_sem->ID)->update(['Jumlah' => $jum_t->sj, 'Sisa' => $jum_t->ss]);
            }
        }

        return $id_tagihan_ais;
    }
}

if (!function_exists('update_tagihan_variable')) {
    function update_tagihan_variable($MhswID, $TahunID, $JenisBiayaID = 0)
    {
        $array_jb_khusus = array(57 => 'Wisuda', 71 => 'Skripsi', 72 => 'PKL', 73 => 'KKN', 74 => 'Komprehensif', 59 => 'Cuti');
        $array_per_tanggal = array('Cuti');
        $now = date('Y-m-d');

        $mhs = get_id($MhswID, 'mahasiswa');
        $tahunAktif = get_id($TahunID, 'tahun');
        if (!$mhs || !$tahunAktif || !isset($array_jb_khusus[$JenisBiayaID])) return [];

        $KodeTahun = $tahunAktif->TahunID;
        $ProgramID = $mhs->ProgramID;
        $ProdiID = $mhs->ProdiID;
        $TahunMasuk = $mhs->TahunMasuk;
        $JalurPendaftaran = $mhs->jalur_pmb;
        $JenisPendaftaran = $mhs->StatusPindahan;
        
        $Jenis = $array_jb_khusus[$JenisBiayaID];
        $whereHarga = "(ProgramID='$ProgramID' OR ProgramID='0') AND (ProdiID='$ProdiID' OR ProdiID='0') 
                      AND (JenisPendaftaran='$JenisPendaftaran' OR JenisPendaftaran='0') AND (TahunMasuk='$TahunMasuk' OR TahunMasuk='0') ";

        if (in_array($Jenis, $array_per_tanggal)) {
            $whereHarga .= " AND ('$now' between TanggalMulai and TanggalSelesai)";
        }

        $setupHarga = DB::table('setup_harga_biaya_variable')->whereRaw($whereHarga)
            ->orderByRaw('ProgramID DESC, ProdiID DESC, TahunMasuk DESC')->where('Jenis', $Jenis)->first();
        if (!$setupHarga) return [];

        $NilaiTagihan = $setupHarga->Nominal;
        $rand = mt_rand(100, 999);
        $noInvoiceGenerate = date("Y") . "-" . $Jenis . "-" . $tahunAktif->TahunID . "-" . ($mhs->NPM ?? '') . "-" . $rand;

        $biaya = DB::table('biaya')->where(['KodeTahun' => $KodeTahun, 'ProgramID' => $ProgramID, 'ProdiID' => $ProdiID, 'TahunMasuk' => $TahunMasuk, 'JenisPendaftaran' => $JenisPendaftaran, 'JalurPendaftaran' => $JalurPendaftaran])->first();

        $tagihan_semester = DB::table('tagihan_mahasiswa_semester')->where(['MhswID' => $mhs->ID, 'Periode' => $TahunID])->first();
        if ($tagihan_semester) {
            $tagihanMahasiswaSemesterID = $tagihan_semester->ID;
        }
        else {
            $inputTagihanSemester = [
                'MhswID' => $mhs->ID, 'ProdiID' => $mhs->ProdiID, 'ProgramID' => $mhs->ProgramID, 'Periode' => $TahunID,
                'Semester' => ($biaya && $biaya->Semester) ? $biaya->Semester : get_semester_tahunmasuk($TahunMasuk, $KodeTahun),
                'TotalTagihan' => $NilaiTagihan, 'JumlahDiskon' => 0, 'TotalCicilan' => 0, 'Jumlah' => $NilaiTagihan, 'Sisa' => $NilaiTagihan,
                'createdAt' => date('Y-m-d H:i:s'), 'UserID' => session('UserID') ?? 0
            ];
            $tagihanMahasiswaSemesterID = DB::table('tagihan_mahasiswa_semester')->insertGetId($inputTagihanSemester);
        }

        $insert = [
            'TagihanMahasiswaSemesterID' => $tagihanMahasiswaSemesterID, 'Periode' => $TahunID, 'ProgramID' => $mhs->ProgramID, 'ProdiID' => $mhs->ProdiID,
            'TahunID' => $tahunAktif->TahunID, 'JenisBiayaID' => $JenisBiayaID, 'JenisMahasiswa' => $mhs->jenis_mhsw, 'MhswID' => $mhs->ID, 'NPM' => $mhs->NPM,
            'NoInvoice' => $noInvoiceGenerate, 'TotalTagihan' => $NilaiTagihan, 'Jumlah' => $NilaiTagihan, 'Sisa' => $NilaiTagihan,
            'Tanggal' => date("Y-m-d H:i:s"), 'Update' => date("Y-m-d H:i:s"), 'TanggalTagihan' => date("Y-m-d"), 'DikalikanSKS' => '1',
            'UserCreate' => session('UserID') ?? 0
        ];

        $cek = DB::table('tagihan_mahasiswa')->select('ID')->where(['MhswID' => $mhs->ID, 'Periode' => $TahunID, 'JenisBiayaID' => $JenisBiayaID])->first();
        $id_tagihan_ais = [];
        if (empty($cek)) {
            $tagihanMahasiswaID = DB::table('tagihan_mahasiswa')->insertGetId($insert);
            $id_tagihan_ais[] = $tagihanMahasiswaID;
        }
        else {
            DB::table('tagihan_mahasiswa')->where('ID', $cek->ID)->update([
                'TotalTagihan' => (int)$NilaiTagihan, 'Jumlah' => (int)$NilaiTagihan,
                'Sisa' => DB::raw("CAST(".intval($NilaiTagihan)." AS SIGNED) - IFNULL(TotalCicilan, 0)")
            ]);
            $tagihanMahasiswaID = $cek->ID; $id_tagihan_ais[] = $cek->ID;
        }

        if ($tagihanMahasiswaID && $tagihanMahasiswaSemesterID) {
            $jum = DB::table('tagihan_mahasiswa')->where('TagihanMahasiswaSemesterID', $tagihanMahasiswaSemesterID)->selectRaw('SUM(ifnull(TotalTagihan,0)) as st, SUM(ifnull(Jumlah,0)) as sj, SUM(ifnull(Sisa,0)) as ss')->first();
            DB::table('tagihan_mahasiswa_semester')->where('ID', $tagihanMahasiswaSemesterID)->update(['TotalTagihan' => $jum->st, 'Jumlah' => $jum->sj, 'Sisa' => $jum->ss]);
        }

        $cek_termin = DB::table('tagihan_mahasiswa_termin')->where(['MhswID' => $mhs->ID, 'Periode' => $TahunID, 'JenisBiayaID' => $JenisBiayaID, 'TerminKe' => 1])->first();
        if (empty($cek_termin)) {
            DB::table('tagihan_mahasiswa_termin')->insert([
                'TagihanMahasiswaID' => $tagihanMahasiswaID, 'ProgramID' => $mhs->ProgramID, 'Periode' => $TahunID, 'ProdiID' => $mhs->ProdiID,
                'JenisBiayaID' => $JenisBiayaID, 'MhswID' => $mhs->ID, 'TotalTagihan' => $NilaiTagihan, 'Sisa' => $NilaiTagihan, 'Jumlah' => $NilaiTagihan,
                'Semester' => ($biaya && $biaya->Semester) ? $biaya->Semester : get_semester_tahunmasuk($TahunMasuk, $KodeTahun),
                'TerminKe' => 1, 'Tanggal' => date('Y-m-d H:i:s'), 'UserID' => session('UserID') ?? 0
            ]);
        }
        else {
            DB::table('tagihan_mahasiswa_termin')->where('ID', $cek_termin->ID)->update([
                'TotalTagihan' => (int)$NilaiTagihan, 'Jumlah' => (int)$NilaiTagihan,
                'Sisa' => DB::raw("CAST(".intval($NilaiTagihan)." AS SIGNED) - IFNULL(TotalCicilan, 0)")
            ]);
        }

        return $id_tagihan_ais;
    }
}

if (!function_exists('delete_tagihan')) {
    function delete_tagihan($tagihanID, $insert_draft = 0)
    {
        $get_tagihan = DB::table('tagihan_mahasiswa')->where('ID', $tagihanID)->first();
        if (!$get_tagihan) return;

        $mahasiswa = get_id($get_tagihan->MhswID, 'mahasiswa');
        $jenisBiaya = get_field($get_tagihan->JenisBiayaID, 'jenisbiaya');
        log_akses('Hapus', 'Menghapus Data Tagihan Dengan NIM ' . ($mahasiswa->NPM ?? '') . ' dan Jenis Biaya ' . ($jenisBiaya ?? ''));

        if ($insert_draft == 1 && empty($get_tagihan->DraftTagihanMahasiswaID)) {
            insert_draft_dari_tagihan($get_tagihan->ID);
        }

        if ($get_tagihan->DraftTagihanMahasiswaID) {
            DB::table('draft_tagihan_mahasiswa')->where('ID', $get_tagihan->DraftTagihanMahasiswaID)->update(['StatusPosting' => 0]);
        }

        DB::table('tagihan_mahasiswa_detail')->where('TagihanMahasiswaID', $tagihanID)->get()->each(function($row) {
            if ($row->DraftTagihanMahasiswaDetailID) {
                DB::table('draft_tagihan_mahasiswa_detail')->where('ID', $row->DraftTagihanMahasiswaDetailID)->update(['StatusPosting' => 0]);
            }
            DB::table('tagihan_mahasiswa_detail')->where('ID', $row->ID)->delete();
        });

        DB::table('tagihan_mahasiswa_termin')->where('TagihanMahasiswaID', $tagihanID)->get()->each(function($tm) {
            $t_sem = DB::table('tagihan_mahasiswa_termin_semester')->whereRaw("FIND_IN_SET(?, TagihanMahasiswaTerminID_list)", [$tm->ID])->first();
            if ($t_sem) {
                $new_j = $t_sem->Jumlah - $tm->Jumlah;
                $new_s = $t_sem->Sisa - $tm->Sisa;
                if ($new_j > 0) {
                    $list = array_diff(explode(",", $t_sem->TagihanMahasiswaTerminID_list), [$tm->ID]);
                    DB::table('tagihan_mahasiswa_termin_semester')->where('ID', $t_sem->ID)->update([
                        'TagihanMahasiswaTerminID_list' => implode(",", $list), 'Jumlah' => $new_j, 'Sisa' => $new_s
                    ]);
                } else {
                    DB::table('tagihan_mahasiswa_termin_semester')->where('ID', $t_sem->ID)->delete();
                }
                if ($t_sem->DraftTagihanMahasiswaTerminSemesterID) DB::table('draft_tagihan_mahasiswa_termin_semester')->where('ID', $t_sem->DraftTagihanMahasiswaTerminSemesterID)->update(['StatusPosting' => 0]);
            }
            if ($tm->DraftTagihanMahasiswaTerminID) DB::table('draft_tagihan_mahasiswa_termin')->where('ID', $tm->DraftTagihanMahasiswaTerminID)->update(['StatusPosting' => 0]);
            DB::table('tagihan_mahasiswa_termin')->where('ID', $tm->ID)->delete();
        });

        if (DB::table('tagihan_mahasiswa')->where('ID', $tagihanID)->delete()) {
            $t_sem = DB::table('tagihan_mahasiswa_semester')->where('ID', $get_tagihan->TagihanMahasiswaSemesterID)->first();
            if ($t_sem) {
                $new_j = $t_sem->Jumlah - $get_tagihan->Jumlah;
                if ($new_j > 0) {
                    DB::table('tagihan_mahasiswa_semester')->where('ID', $t_sem->ID)->update([
                        'TotalTagihan' => $t_sem->TotalTagihan - $get_tagihan->TotalTagihan,
                        'JumlahDiskon' => $t_sem->JumlahDiskon - $get_tagihan->JumlahDiskon,
                        'Jumlah' => $new_j, 'Sisa' => $t_sem->Sisa - $get_tagihan->Sisa
                    ]);
                } else {
                    DB::table('tagihan_mahasiswa_semester')->where('ID', $t_sem->ID)->delete();
                }
                if ($t_sem->DraftTagihanMahasiswaSemesterID) DB::table('draft_tagihan_mahasiswa_semester')->where('ID', $t_sem->DraftTagihanMahasiswaSemesterID)->update(['StatusPosting' => 0]);

                $tt = DB::table('tagihan_mahasiswa_termin_total')->whereRaw("FIND_IN_SET(?, TagihanMahasiswaSemesterID_list)", [$t_sem->ID])->first();
                if ($tt) {
                    $new_j_tt = $tt->Jumlah - $get_tagihan->Jumlah;
                    if ($new_j_tt > 0) {
                        $list = ($new_j <= 0) ? array_diff(explode(",", $tt->TagihanMahasiswaSemesterID_list), [$t_sem->ID]) : explode(",", $tt->TagihanMahasiswaSemesterID_list);
                        DB::table('tagihan_mahasiswa_termin_total')->where('ID', $tt->ID)->update(['TagihanMahasiswaSemesterID_list' => implode(",", $list), 'Jumlah' => $new_j_tt, 'Sisa' => $tt->Sisa - $get_tagihan->Sisa]);
                    } else {
                        DB::table('tagihan_mahasiswa_termin_total')->where('ID', $tt->ID)->delete();
                    }
                    if ($tt->DraftTagihanMahasiswaTerminTotalID) DB::table('draft_tagihan_mahasiswa_termin_total')->where('ID', $tt->DraftTagihanMahasiswaTerminTotalID)->update(['StatusPosting' => 0]);
                }
            }
        }
    }
}

if (!function_exists('insert_draft_dari_tagihan')) {
    function insert_draft_dari_tagihan($id_tagihan = '')
    {
        if (!$id_tagihan) return;
        $get_tagihan = DB::table('tagihan_mahasiswa')->where('ID', $id_tagihan)->first();
        if (!$get_tagihan || !empty($get_tagihan->DraftTagihanMahasiswaID)) return;

        $MhswID = $get_tagihan->MhswID;
        $TahunID = $get_tagihan->Periode;

        $insert = [
            'MasterDiskonID' => $get_tagihan->MasterDiskonID, 'ProgramID' => $get_tagihan->ProgramID, 'NoInvoice' => $get_tagihan->NoInvoice,
            'Periode' => $get_tagihan->Periode, 'ProdiID' => $get_tagihan->ProdiID, 'JenisBiayaID' => $get_tagihan->JenisBiayaID,
            'JenisMahasiswa' => $get_tagihan->JenisMahasiswa, 'TahunID' => $get_tagihan->TahunID, 'NPM' => $get_tagihan->NPM, 'MhswID' => $get_tagihan->MhswID,
            'TotalTagihan' => $get_tagihan->TotalTagihan, 'JumlahDiskon' => $get_tagihan->JumlahDiskon, 'Jumlah' => $get_tagihan->Jumlah, 'Tanggal' => $get_tagihan->Tanggal,
            'Update' => date('Y-m-d H:i:s'), 'TanggalTagihan' => $get_tagihan->TanggalTagihan
        ];
        $draftTagihanMahasiswaID = DB::table('draft_tagihan_mahasiswa')->insertGetId($insert);

        DB::table('tagihan_mahasiswa_detail')->where('TagihanMahasiswaID', $id_tagihan)->get()->each(function($row) use ($draftTagihanMahasiswaID) {
            DB::table('draft_tagihan_mahasiswa_detail')->insert([
                'DraftTagihanMahasiswaID' => $draftTagihanMahasiswaID, 'ProgramID' => $row->ProgramID, 'Periode' => $row->Periode, 'ProdiID' => $row->ProdiID,
                'JenisBiayaID' => $row->JenisBiayaID, 'JenisBiayaID_Detail' => $row->JenisBiayaID_Detail, 'MhswID' => $row->MhswID, 'JenisMahasiswa' => $row->JenisMahasiswa,
                'TotalTagihan' => $row->TotalTagihan, 'JumlahDiskon' => $row->JumlahDiskon, 'Jumlah' => $row->Jumlah, 'Tanggal' => $row->Tanggal, 'Update' => date('Y-m-d H:i:s')
            ]);
        });

        DB::table('tagihan_mahasiswa_termin')->where('TagihanMahasiswaID', $id_tagihan)->get()->each(function($row) use ($draftTagihanMahasiswaID) {
            DB::table('draft_tagihan_mahasiswa_termin')->insert([
                'DraftTagihanMahasiswaID' => $draftTagihanMahasiswaID, 'ProgramID' => $row->ProgramID, 'Periode' => $row->Periode, 'ProdiID' => $row->ProdiID,
                'JenisBiayaID' => $row->JenisBiayaID, 'MhswID' => $row->MhswID, 'TotalTagihan' => $row->TotalTagihan, 'JumlahDiskon' => $row->JumlahDiskon,
                'Jumlah' => $row->Jumlah, 'TerminKe' => $row->TerminKe, 'Tanggal' => $row->Tanggal, 'Update' => date('Y-m-d H:i:s')
            ]);
        });

        sinkron_draft_tagihan_mahasiswa($MhswID, $TahunID);
    }
}

if (!function_exists('sinkron_draft_tagihan_mahasiswa')) {
    function sinkron_draft_tagihan_mahasiswa($MhswID, $TahunID)
    {
        $mahasiswa = DB::table('mahasiswa')->select('ID', 'NPM', 'Nama', 'ProgramID', 'ProdiID', 'TahunMasuk', 'jalur_pmb', 'StatusPindahan')->where('ID', $MhswID)->first();
        $row_tahun = get_id($TahunID, 'tahun');
        if (!$mahasiswa || !$row_tahun) return;

        $KodeTahun = $row_tahun->TahunID;
        $list_tagihan = DB::table('draft_tagihan_mahasiswa')->where(['MhswID' => $MhswID, 'Periode' => $TahunID])->get();

        $arr_BiayaID = [];
        foreach ($list_tagihan as $row_tagihan) {
            $BiayaID = $row_tagihan->BiayaID;
            $draftTagihanMahasiswaSemesterID = null;
            if (!in_array($BiayaID, $arr_BiayaID)) {
                if ($BiayaID) $arr_BiayaID[] = $BiayaID;
                $tag_sem = DB::table('draft_tagihan_mahasiswa_semester')->where(['Periode' => $TahunID, 'MhswID' => $MhswID])->first();
                if ($tag_sem) {
                    $draftTagihanMahasiswaSemesterID = $tag_sem->ID;
                } else {
                    $biaya = get_id($BiayaID, 'biaya');
                    $biaya_s = $biaya ? get_id($biaya->BiayaSemesterID, 'biaya_semester') : null;
                    $draftTagihanMahasiswaSemesterID = DB::table('draft_tagihan_mahasiswa_semester')->insertGetId([
                        'BiayaSemesterID' => $biaya->BiayaSemesterID ?? null, 'MhswID' => $MhswID, 'ProdiID' => $mahasiswa->ProdiID, 'ProgramID' => $mahasiswa->ProgramID,
                        'Periode' => $TahunID, 'Semester' => ($biaya && $biaya->Semester) ? $biaya->Semester : get_semester_tahunmasuk($mahasiswa->TahunMasuk, $KodeTahun),
                        'TotalTagihan' => (int)($biaya_s->JumlahTagihan ?? 0), 'JumlahDiskon' => (int)($biaya_s->JumlahDiskon ?? 0), 'Jumlah' => (int)($biaya_s->Jumlah ?? 0),
                        'createdAt' => date('Y-m-d H:i:s'), 'UserID' => session('UserID') ?? 0
                    ]);
                }
            }
            DB::table('draft_tagihan_mahasiswa')->where('ID', $row_tagihan->ID)->update(['DraftTagihanMahasiswaSemesterID' => $draftTagihanMahasiswaSemesterID]);
            
            if ($draftTagihanMahasiswaSemesterID) {
                $tag_s = DB::table('draft_tagihan_mahasiswa_semester')->where('ID', $draftTagihanMahasiswaSemesterID)->first();
                $jum = DB::table('draft_tagihan_mahasiswa')->where('DraftTagihanMahasiswaSemesterID', $draftTagihanMahasiswaSemesterID)
                    ->selectRaw('SUM(ifnull(Jumlah,0)) as sj, SUM(ifnull(TotalTagihan,0)) as st, SUM(ifnull(JumlahDiskon,0)) as sd')->first();
                DB::table('draft_tagihan_mahasiswa_semester')->where('ID', $draftTagihanMahasiswaSemesterID)->update(['Jumlah' => $jum->sj, 'TotalTagihan' => $jum->st, 'JumlahDiskon' => $jum->sd]);

                DB::table('biaya_termin_semester')->where('BiayaSemesterID', $tag_s->BiayaSemesterID)->get()->each(function($row) use ($tag_s, $MhswID, $TahunID, $mahasiswa, $KodeTahun) {
                    if ($row->Jumlah > 0 && !DB::table('draft_tagihan_mahasiswa_termin_semester')->where(['TerminKe' => $row->TerminKe, 'Periode' => $TahunID, 'MhswID' => $MhswID])->exists()) {
                        $list = DB::table('draft_tagihan_mahasiswa_termin')->where('MhswID', $MhswID)->whereIn('BiayaTerminID', explode(",", $row->BiayaTerminID_list))->pluck('ID')->toArray();
                        DB::table('draft_tagihan_mahasiswa_termin_semester')->insert([
                            'DraftTagihanMahasiswaSemesterID' => $tag_s->ID, 'BiayaTerminSemesterID' => $row->ID, 'MhswID' => $MhswID,
                            'ProdiID' => $mahasiswa->ProdiID, 'ProgramID' => $mahasiswa->ProgramID, 'Periode' => $TahunID,
                            'Semester' => ($row->Semester) ? $row->Semester : get_semester_tahunmasuk($mahasiswa->TahunMasuk, $KodeTahun),
                            'Jumlah' => $row->Jumlah, 'TerminKe' => $row->TerminKe, 'DraftTagihanMahasiswaTerminID_list' => implode(",", $list),
                            'createdAt' => date('Y-m-d H:i:s'), 'UserID' => session('UserID') ?? 0
                        ]);
                    }
                });
            }
        }

        DB::table('biaya_termin_total')->where(['KodeTahun' => $KodeTahun, 'ProgramID' => $mahasiswa->ProgramID, 'ProdiID' => $mahasiswa->ProdiID, 'TahunMasuk' => $mahasiswa->TahunMasuk, 'JenisPendaftaran' => $mahasiswa->StatusPindahan, 'JalurPendaftaran' => $mahasiswa->jalur_pmb])
        ->get()->each(function($row) use ($MhswID, $mahasiswa) {
            $tt = DB::table('draft_tagihan_mahasiswa_termin_total')->where(['TerminKe' => $row->TerminKe, 'MhswID' => $MhswID])->first();
            $list = DB::table('draft_tagihan_mahasiswa_semester')->where('MhswID', $MhswID)->whereIn('BiayaSemesterID', explode(",", $row->BiayaSemesterID_list))->pluck('ID')->toArray();
            if (empty($tt)) {
                DB::table('draft_tagihan_mahasiswa_termin_total')->insert([
                    'BiayaTerminTotalID' => $row->ID, 'MhswID' => $MhswID, 'ProdiID' => $mahasiswa->ProdiID, 'ProgramID' => $mahasiswa->ProgramID,
                    'Jumlah' => $row->Jumlah, 'TerminKe' => $row->TerminKe, 'DraftTagihanMahasiswaSemesterID_list' => implode(",", $list),
                    'createdAt' => date('Y-m-d H:i:s'), 'UserID' => session('UserID') ?? 0
                ]);
            } elseif (count($list) > 0) {
                DB::table('draft_tagihan_mahasiswa_termin_total')->where('ID', $tt->ID)->update([
                    'BiayaTerminTotalID' => $row->ID, 'Jumlah' => $row->Jumlah, 'DraftTagihanMahasiswaSemesterID_list' => implode(",", $list), 'UserID' => session('UserID') ?? 0
                ]);
            }
        });
    }
}

if (!function_exists('semester')) {
    function semester($thn_masuk, $thn_aktif)
    {
        $tahunaktif = (int)substr($thn_aktif, 0, 4);
        $semakhir = (int)substr($thn_aktif, 4, 1);
        return ($semakhir == 1) ? ($tahunaktif - $thn_masuk) * 2 + 1 : ($tahunaktif - $thn_masuk) * 2 + 2;
    }
}

if (!function_exists('tgl')) {
    function tgl($tgl, $type = "01")
    {
        if (empty($tgl) || str_starts_with($tgl, '0000-00-00')) return "-";
        
        $jam = (strlen($tgl) >= 19 && !str_ends_with($tgl, '00:00:00')) ? substr($tgl, 11, 8) : '';
        $bulan = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];
        $month = ['01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'];
        $month_sing = ['01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec'];
        $hari = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];

        $time = strtotime($tgl);
        $d = date('d', $time); $m = date('m', $time); $y = date('Y', $time); $l = date('l', $time);

        switch ($type) {
            case "01": return $hari[$l] . " " . $d . " " . $bulan[$m] . " " . $y . (empty($jam) ? "" : " " . $jam);
            case "011": return $hari[$l] . ", " . $d . " " . $bulan[$m] . " " . $y . (empty($jam) ? "" : " " . $jam);
            case "nilai": return $hari[$l] . ", " . $m . "/" . $d . "/" . $y;
            case "06": return $d . " " . $bulan[$m] . " " . $y;
            case "02": return $d . " " . $bulan[$m] . " " . $y . (empty($jam) ? "" : " " . $jam);
            case "03": return $d . " " . substr($bulan[$m], 0, 3) . " " . $y . (empty($jam) ? "" : " " . $jam);
            case "04": return $d . "/" . $m . "/" . $y;
            case "05": return $y . "-" . $m . "-" . $d;
            case "04_br_hari": return $hari[$l] . "<br>" . $d . "/" . $m . "/" . $y;
            case "day-name-month": return $hari[$l] . ", " . $d . " " . $bulan[$m] . " " . $y;
            case "name-month": return $d . " " . $bulan[$m] . " " . $y;
            case "bulan": return $bulan[$m];
            case "dayName": return $hari[$l];
            case "eng_form": return $month[$m] . " " . (int)$d . ", " . $y;
            case "eng_form2": return $d . " " . $month_sing[$m] . " " . substr($y, -2);
            case "eng_form3": return $d . " " . $month_sing[$m] . " " . $y;
            case "bulan_tanggal": return $d . " " . $bulan[$m];
            case "code": return $y . $m . $d;
            case "btn": return $d . $m . substr($y, -2);
            default: return $d . "/" . $m . "/" . $y;
        }
    }
}

if (!function_exists('getIp')) {
    function getIp()
    {
        return request()->ip();
    }
}

if (!function_exists('log_rencanastudi')) {
    function log_rencanastudi($row, $Aksi, $Asal, $UserID)
    {
        $input = [
            'rencanastudiID' => $row->ID, 'MhswID' => $row->MhswID, 'DetailKurikulumID' => $row->DetailKurikulumID,
            'JadwalID' => $row->JadwalID, 'NPM' => $row->NPM, 'MKKode' => $row->MKKode, 'TahunID' => $row->TahunID,
            'TotalSKS' => $row->TotalSKS, 'approval' => $row->approval, 'URL' => request()->url(),
            'Asal' => $Asal, 'Aksi' => $Aksi, 'createdAt' => date('Y-m-d H:i:s'), 'UserID' => $UserID
        ];
        return DB::table('log_rencanastudi')->insert($input);
    }
}

if (!function_exists('log_nilai')) {
    function log_nilai($row, $Aksi, $Asal, $UserID)
    {
        $input = [
            'NilaiID' => $row->ID, 'rencanastudiID' => $row->rencanastudiID, 'MhswID' => $row->MhswID, 'NPM' => $row->NPM,
            'NamaMahasiswa' => $row->NamaMahasiswa, 'ProgramID' => $row->ProgramID, 'NamaProdi' => $row->NamaProdi, 'NamaProgram' => $row->NamaProgram,
            'TahunMasuk' => $row->TahunMasuk, 'ProdiID' => $row->ProdiID, 'DetailKurikulumID' => $row->DetailKurikulumID, 'MKKode' => $row->MKKode,
            'NamaMataKuliah' => $row->NamaMataKuliah, 'TotalSKS' => $row->TotalSKS, 'SKSTatapMuka' => $row->SKSTatapMuka, 'SKSPraktikum' => $row->SKSPraktikum,
            'SKSPraktekLap' => $row->SKSPraktekLap, 'Semester' => $row->Semester, 'Bobot' => $row->Bobot, 'NilaiBobot' => $row->NilaiBobot,
            'NilaiAkhir' => $row->NilaiAkhir, 'NilaiHuruf' => $row->NilaiHuruf, 'TahunID' => $row->TahunID, 'KodeTahun' => $row->KodeTahun,
            'ValidasiDosen' => $row->ValidasiDosen, 'Lock' => $row->Lock, 'PublishTranskrip' => $row->PublishTranskrip, 'PublishKHS' => $row->PublishKHS,
            'Konversi' => $row->Konversi, 'default_feeder' => $row->default_feeder, 'migrasi' => $row->migrasi, 'userID' => $row->userID,
            'StatusFeeder' => $row->StatusFeeder, 'WaktuValidasi' => $row->WaktuValidasi, 'ActionUserID' => $UserID, 'URL' => request()->url(),
            'Asal' => $Asal, 'Aksi' => $Aksi, 'createdAt' => date('Y-m-d H:i:s')
        ];
        return DB::table('log_nilai')->insert($input);
    }
}

if (!function_exists('log_jadwal')) {
    function log_jadwal($row, $Aksi, $UserID)
    {
        $input = [
            'TahunID' => $row->TahunID, 'JadwalID' => $row->ID, 'DosenID' => $row->DosenID, 'DosenAnggota' => $row->DosenAnggota,
            'MKKode' => $row->MKKode, 'DetailKurikulumID' => $row->DetailKurikulumID, 'KelasID' => $row->KelasID, 'Kapasitas' => $row->Kapasitas,
            'JumPertemuan' => $row->JumPertemuan, 'JumlahPeserta' => $row->JumlahPeserta, 'Aktif' => $row->Aktif, 'PublishDosen' => $row->PublishDosen,
            'Aksi' => $Aksi, 'createdAt' => date('Y-m-d H:i:s'), 'UserID' => $UserID
        ];
        return DB::table('log_jadwal')->insert($input);
    }
}

if (!function_exists('log_bobotnilai')) {
    function log_bobotnilai($row, $Aksi, $Asal, $UserID)
    {
        $input = [
            'TahunID' => $row->TahunID, 'BobotNilaiID' => $row->ID, 'ProdiID' => $row->ProdiID, 'KurikulumID' => $row->KurikulumID,
            'DetailKurikulumID' => $row->DetailKurikulumID, 'JadwalID' => $row->JadwalID, 'Nama' => $row->Nama, 'Persen' => $row->Persen,
            'NamaInggris' => $row->NamaInggris, 'JenisBobotID' => $row->JenisBobotID, 'URL' => request()->url(), 'Asal' => $Asal,
            'Aksi' => $Aksi, 'createdAt' => date('Y-m-d H:i:s'), 'UserID' => $UserID
        ];
        return DB::table('log_bobotnilai')->insert($input);
    }
}

if (!function_exists('log_bobot_mahasiswa')) {
    function log_bobot_mahasiswa($row, $Aksi, $Asal, $UserID)
    {
        $input = [
            'BobotMhswID' => $row->ID, 'MhswID' => $row->MhswID, 'DetailKurikulumID' => $row->DetailKurikulumID, 'TahunID' => $row->TahunID,
            'JenisBobotID' => $row->JenisBobotID, 'Nilai' => $row->Nilai, 'Persen' => $row->Persen, 'ValidasiDosen' => $row->ValidasiDosen,
            'Publish' => $row->Publish, 'URL' => request()->url(), 'Asal' => $Asal, 'Aksi' => $Aksi, 'createdAt' => date('Y-m-d H:i:s'),
            'UserID' => $UserID
        ];
        return DB::table('log_bobot_mahasiswa')->insert($input);
    }
}

if (!function_exists('log_generate_khs')) {
    function log_generate_khs($row, $Aksi, $Asal, $UserID)
    {
        $input = [
            'KHSID' => $row->ID, 'MhswID' => $row->MhswID, 'NPM' => $row->NPM, 'KodeTahun' => $row->TahunID, 'DetailkurikulumID' => $row->DetailkurikulumID,
            'MKKode' => $row->MKKode, 'TotalSKS' => $row->TotalSKS, 'Bobot' => $row->Bobot, 'NilaiBobot' => $row->NilaiBobot, 'NilaiAkhir' => $row->NilaiAkhir,
            'NilaiHuruf' => $row->NilaiHuruf, 'URL' => request()->url(), 'Asal' => $Asal, 'Aksi' => $Aksi, 'createdAt' => date('Y-m-d H:i:s'), 'UserID' => $UserID
        ];
        return DB::table('log_generate_khs')->insert($input);
    }
}

if (!function_exists('log_generate_transkrip')) {
    function log_generate_transkrip($row, $Aksi, $Asal, $UserID)
    {
        $input = [
            'TranskripID' => $row->ID, 'MhswID' => $row->MhswID, 'NPM' => $row->NPM, 'KodeTahun' => $row->TahunID, 'DetailkurikulumID' => $row->DetailkurikulumID,
            'MKKode' => $row->MKKode, 'TotalSKS' => $row->TotalSKS, 'Bobot' => $row->Bobot, 'NilaiBobot' => $row->NilaiBobot, 'NilaiAkhir' => $row->NilaiAkhir,
            'NilaiHuruf' => $row->NilaiHuruf, 'URL' => request()->url(), 'Asal' => $Asal, 'Aksi' => $Aksi, 'createdAt' => date('Y-m-d H:i:s'), 'UserID' => $UserID
        ];
        return DB::table('log_generate_transkrip')->insert($input);
    }
}

if (!function_exists('get_path')) {
    function get_path($dir, $theme = null)
    {
        $theme = $theme ?: (defined('THEME') ? THEME : 'default');
        return asset("assets/{$theme}/{$dir}/");
    }
}

if (!function_exists('get_field_mkkode')) {
    function get_field_mkkode($id, $namatabel, $namafield = 'MKKode')
    {
        $Q = DB::table($namatabel)->select($namafield . " as field")->where('ID', $id)->first();
        return $Q ? $Q->field : null;
    }
}

if (!function_exists('unzip')) {
    function unzip($path, $extract)
    {
        $zip = new ZipArchive;
        if ($zip->open($path) === TRUE) {
            $zip->extractTo($extract);
            @chmod($extract, 0777);
            $zip->close();
            return 'a';
        }
        return 'b';
    }
}

if (!function_exists('get_field')) {
    function get_field($id, $namatabel, $namafield = 'Nama')
    {
        // OPTIMIZATION: Use cache for frequently accessed fields
        static $cache = [];
        
        $cacheKey = "{$namatabel}_{$namafield}_{$id}";
        
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }
        
        if (!$id) {
            $cache[$cacheKey] = '';
            return '';
        }
        
        $Q = DB::table($namatabel)
            ->select($namafield . " as field")
            ->where('ID', $id)
            ->first();
        
        $result = $Q ? $Q->field : '';
        $cache[$cacheKey] = $result;
        
        return $result;
    }
}

if (!function_exists('qrow')) {
    function qrow($query)
    {
        return DB::selectOne($query);
    }
}

if (!function_exists('qresult')) {
    function qresult($query)
    {
        return DB::select($query);
    }
}

if (!function_exists('get_all')) {

	function get_all($namatabel, $where = '', $sort = 'ASC', $sort2 = 'DESC')
	{
		$query = DB::table($namatabel === 'semuatahun' ? 'tahun' : $namatabel);

		if ($namatabel == 'semuatahun') {
			$query->orderBy('TahunID', $sort2);
		}
		elseif ($namatabel == 'mastermodul') {
			$query->orderBy('Urut', $sort);
		}
		elseif ($namatabel == 'modulgrup') {
			$query->orderBy('MasterModulID', $sort);
		}
		elseif ($namatabel == 'modul') {
			$query->orderBy('MdlGrpID', $sort);
		}
		elseif ($namatabel == 'tahun') {
			$query->orderBy('TahunID', 'desc');
		}
		elseif ($namatabel == 'programstudi') {
			if (!in_array('SPR', explode(',', session('LevelKode'))) && empty(session('username2'))) {
				$user = get_id(session('UserID'), 'user');

				if ($user && $user->ProdiID) {
					$prodiIds = explode(",", $user->ProdiID);
					$query->whereIn('ID', $prodiIds);
				}
			}
		}
		elseif ($namatabel == 'hari') {
			$query->orderBy('ID', $sort);
		}
		elseif ($namatabel == 'kelas') {
			if ($where) {
				$query->where('ProdiID', $where);
			}
			$query->orderBy('ID', $sort);
		}
		elseif ($namatabel == 'kodewaktu') {
			$query->orderBy('Kode', $sort);
		}
		elseif ($namatabel == 'modulgrup') {
			$query->orderBy('AksesID', 'ASC');
		}
		elseif ($namatabel == 'program') {
			if (session('username')) {
				if (!in_array('SPR', explode(',', session('LevelKode')))) {
					$user = get_id(session('UserID'), 'user');

					if ($user && $user->ProgramID) {
						$programIds = explode(",", $user->ProgramID);
						$query->whereIn('ID', $programIds);
					}
				}
			}
		}
		elseif ($namatabel == 'pmbperiode' or $namatabel == 'pmbformulir' or $namatabel == 'pmbsyaratmahasiswa' or $namatabel == 'pmbruang' or $namatabel == 'mahasiswanilaipmb' or $namatabel == 'tempattest' or $namatabel == 'penghasilan') {
		// No special order
		}
		elseif ($namatabel == 'karyawan') {
		// No special order
		}
		elseif ($namatabel == 'jenis_pendaftaran') {
			$query->where('Aktif', 'ya');
		}
		else {
			$query->orderBy('Nama', $sort);
		}

		return $query->get()->toArray();
	}
}

if (!function_exists('get_id')) {

	function get_id($id, $namatabel)
	{
		return DB::table($namatabel)->where('ID', $id)->first();
	}
}

if (!function_exists('get_data_id')) {

	function get_data_id($id, $namatabel)
	{
		return DB::table($namatabel)->where('ID', $id)->get()->toArray();
	}
}


if (!function_exists('get_all_epsbed')) {

	function get_all_epsbed($namatabel, $sort = 'ASC')
	{
		return DB::table('ref_epsbed')
			->where('Tabel', $namatabel)
			->orderBy('ID', $sort)
			->get()->toArray();
	}
}

if (!function_exists('get_all_pt')) {
	function get_all_pt($keyword = '', $ID = '')
	{
		$query = DB::table('ref_pt');

		if ($ID) {
			$query->where('KodePT', $ID);
		}

		if ($keyword) {
			$query->where(function ($q) use ($keyword) {
				$q->where('KodePT', 'like', "%$keyword%")
					->orWhere('NamaPT', 'like', "%$keyword%");
			});
		}

		return $query->limit(20)->get()->toArray();
	}
}

if (!function_exists('get_all_prodi')) {
	function get_all_prodi($keyword = '', $ID = '', $IDPT = '')
	{
		$query = DB::table('ref_programstudi');

		if ($ID) {
			$query->where('KodeProdi', $ID);
		}
		if ($IDPT) {
			$query->where('KodePT', $IDPT);
		}

		if ($keyword) {
			$query->where(function ($q) use ($keyword) {
				$q->where('KodeProdi', 'like', "%$keyword%")
					->orWhere('NamaProdi', 'like', "%$keyword%");
			});
		}

		return $query->limit(500)->get()->toArray();
	}
}

if (!function_exists('get_all_referensi')) {

	function get_all_referensi($grup, $sort = 'ASC')
	{
		return DB::table('referensi')
			->where('ref_grup', $grup)
			->orderBy('ref_nama', $sort)
			->get()->toArray();
	}
}



if (!function_exists('get_photo_identitas')) {

	function get_photo_identitas($foto, $attr)
	{
		// CLIENT_PATH should be defined in Laravel (e.g., public_path())
		$basePath = defined('CLIENT_PATH') ? CLIENT_PATH : public_path();

		if ($foto && file_exists($basePath . "/images/" . $foto)) {
			$path = $basePath . "/images/" . $foto;
		}
		else {
			$path = $basePath . "/images/tanda_tanya.png";
		}

		if (file_exists($path)) {
			// Note: Src should probably be a URL in Laravel, but maintaining absolute path if that's what was intended
			// However, typically <img src> expects a URL. Re-evaluating.
			// The original used CLIENT_PATH which sounds like a physical path.
			// If it's a URL, it should use asset() or similar.
			$image = '<img src="' . $path . '" ' . $attr . '/>';
		}
		else {
			$image = "No Image";
		}

		return $image;
	}
}

if (!function_exists('get_photo_tag')) {
	function get_photo_tag($path = '')
	{
		return '<img class="" src="' . $path . '">';
	}
}

if (!function_exists('get_photo_path')) {

	function get_photo_path($foto, $jk, $namatabel, $class = '')
	{
		$ph = session("web_folder");
		if ($foto) {
			$path = $namatabel . "/foto/" . $foto;
		}
		elseif ($jk == "L") {
			$path = "$namatabel/foto/default.png";
		}
		elseif ($jk == "P") {
			$path = "$namatabel/foto/defaultP.png";
		}
		else {
			$path = "images/tanda_tanya.png";
		}
		return '<img class="' . $class . '" src="../client/' . $ph . '/' . $path . '">';
	}
}

if (!function_exists('cek_level')) {
	function cek_level($LevelID, $Url, $Akses)
	{
		$hasilmodul = DB::table('modul')
			->selectRaw('GROUP_CONCAT(ID) as ID')
			->where('Script', $Url)
			->where('AksesID', '1')
			->first();

		$hasilmodul2 = DB::table('submodul')
			->selectRaw('GROUP_CONCAT(ID) as ID')
			->where('Script', $Url)
			->first();

		if (!$hasilmodul || empty($hasilmodul->ID)) {
			$hasilmodul = (object)['ID' => '0'];
		}

		if ($hasilmodul2 && !empty($hasilmodul2->ID)) {
			$ModulID = $hasilmodul2->ID;
			$type = 'submodul';
		}
		else {
			$ModulID = $hasilmodul->ID;
			$type = 'modul';
		}

		$modulIds = explode(",", $ModulID);
		$levelIds = $LevelID ? explode(",", $LevelID) : [''];

		$query = DB::table('levelmodul')
			->where('type', $type)
			->whereIn('LevelID', $levelIds)
			->where($Akses, 'YA')
			->whereIn('ModulID', $modulIds);

		$hasil = $query->count();
		return ($hasil > 0) ? "YA" : "TIDAK";
	}
}

if (!function_exists('get_tahun_id')) {
	function get_tahun_id($param, $angka, $operand)
	{
		$thn = (int)substr($param, 0, 4);
		$sem = (int)substr($param, 4, 1);

		$jumlah = $sem + $angka;

		$x = $sem;
		$thn_baru = $thn;
		for ($i = $sem; $i < $jumlah; $i++) {
			if ($operand == '+') {
				++$x;
				if ($x > 2) {
					$x = 1;
					++$thn_baru;
				}
			}

			if ($operand == '-') {
				--$x;
				if ($x < 1) {
					$x = 2;
					--$thn_baru;
				}
			}
			$sembaru = $x;
		}
		return $thn_baru . $sembaru;
	}
}

# call_sp digunakan untuk menjalankan STORED PROCEDURE

if (!function_exists('call_sp')) {
	function call_sp($procedure_name, $params = array(), $type = "proc", $return = "result", $multi = 0)
	{
		// Note: Laravel's DB facade doesn't directly support the complex CI3 logic here.
		// We'll use PDO directly for the more complex cases or raw selects.

		$parameter_strings = array();
		foreach ($params as $p) {
			if ($p === NULL) {
				$parameter_strings[] = 'NULL';
			}
			elseif (is_numeric($p)) {
				$parameter_strings[] = $p;
			}
			else {
				$parameter_strings[] = "'" . addslashes($p) . "'";
			}
		}
		$parameter_list = implode(",", $parameter_strings);

		if ($type == "proc") {
			$sql = "CALL $procedure_name($parameter_list)";
		}
		else {
			$sql = "SELECT $procedure_name($parameter_list) as result_function";
		}

		// Handling multi results or specific return types
		if ($multi == 1 || $return == "result") {
			return DB::select($sql);
		}
		elseif ($return == "result_array") {
			return json_decode(json_encode(DB::select($sql)), true);
		}
		elseif ($return == "row_array") {
			$res = DB::selectOne($sql);
			return $res ? (array)$res : array();
		}
		elseif ($return == "row") {
			return DB::selectOne($sql);
		}
		elseif ($return == "json") {
			return json_encode(DB::select($sql));
		}

		return null;
	}
}

if (!function_exists('chmod_r')) {
	function chmod_r($path)
	{
		$dir = new DirectoryIterator($path);
		foreach ($dir as $item) {
			@chmod($item->getPathname(), 0777);
			if ($item->isDir() && !$item->isDot()) {
				chmod_r($item->getPathname());
			}
		}
	}
}


if (!function_exists('create_dir')) {
	function create_dir($path)
	{
		$exp = explode("/", str_replace("\\", "/", $path));
		$way = '';
		foreach ($exp as $n) {
			if (empty($n))
				continue;
			$way .= $n . '/';
			if (!is_dir($way)) {
				@mkdir($way, 0777);
			}
		}
	}
}

if (!function_exists('pengecekanTanggalKRSOnline')) {
	function pengecekanTanggalKRSOnline($ProdiID, $ProgramID, $TahunMasuk)
	{
		$cur = date('Y-m-d');

		$cekTglKRS = DB::table('tahun as a')
			->join('events_detail as b', 'a.ID', '=', 'b.TahunID')
			->selectRaw('a.ID, DATE(b.TglMulai) as tanggalMulai, DATE(b.TglSelesai) as tanggalSelesai, DATEDIFF(b.TglSelesai, b.TglMulai) as Jumlah')
			->where('a.ProsesBuka', '1')
			->where('b.EventID', '2')
			->where(function ($q) use ($ProdiID) {
			$q->whereRaw("FIND_IN_SET(?, b.ProdiID)", [$ProdiID])->orWhere('b.ProdiID', '0')->orWhereNull('b.ProdiID')->orWhere('b.ProdiID', '');
		})
			->where(function ($q) use ($ProgramID) {
			$q->whereRaw("FIND_IN_SET(?, b.ProgramID)", [$ProgramID])->orWhere('b.ProgramID', '0')->orWhereNull('b.ProgramID')->orWhere('b.ProgramID', '');
		})
			->where(function ($q) use ($TahunMasuk) {
			$q->whereRaw("FIND_IN_SET(?, b.Angkatan)", [$TahunMasuk])->orWhere('b.Angkatan', '0')->orWhereNull('b.Angkatan')->orWhere('b.Angkatan', '');
		})
			->orderByRaw('b.ProdiID DESC, b.ProgramID DESC, b.Angkatan DESC, b.TglMulai DESC')
			->first();

		$cekTglUbah = DB::table('tahun as a')
			->join('events_detail as b', 'a.ID', '=', 'b.TahunID')
			->selectRaw('a.ID, DATE(b.TglMulai) as tanggalMulai, DATE(b.TglSelesai) as tanggalSelesai, DATEDIFF(b.TglSelesai, b.TglMulai) as Jumlah')
			->where('a.ProsesBuka', '1')
			->where('b.EventID', '6')
			->where(function ($q) use ($ProdiID) {
			$q->whereRaw("FIND_IN_SET(?, b.ProdiID)", [$ProdiID])->orWhere('b.ProdiID', '0')->orWhereNull('b.ProdiID')->orWhere('b.ProdiID', '');
		})
			->where(function ($q) use ($ProgramID) {
			$q->whereRaw("FIND_IN_SET(?, b.ProgramID)", [$ProgramID])->orWhere('b.ProgramID', '0')->orWhereNull('b.ProgramID')->orWhere('b.ProgramID', '');
		})
			->where(function ($q) use ($TahunMasuk) {
			$q->whereRaw("FIND_IN_SET(?, b.Angkatan)", [$TahunMasuk])->orWhere('b.Angkatan', '0')->orWhereNull('b.Angkatan')->orWhere('b.Angkatan', '');
		})
			->orderByRaw('b.ProdiID DESC, b.ProgramID DESC, b.Angkatan DESC, b.TglMulai DESC')
			->first();


		$tipe = 0;
		$startDate = date('Y-m-d');
		$jumlahHari = 0;
		$tanggalMulai = '';
		$tanggalSelesai = '';
		$labelKRS = 'KRS';

		if ($cekTglKRS) {
			if ($startDate >= $cekTglKRS->tanggalMulai && $startDate <= $cekTglKRS->tanggalSelesai) {
				$jumlahHari = $cekTglKRS->Jumlah;
				$tanggalMulai = $cekTglKRS->tanggalMulai;
				$tanggalSelesai = $cekTglKRS->tanggalSelesai;
			}
			else {
				if ($cekTglUbah) {
					if ($startDate >= $cekTglUbah->tanggalMulai && $startDate <= $cekTglUbah->tanggalSelesai) {
						$labelKRS = 'PKRS';
						$jumlahHari = $cekTglUbah->Jumlah;
						$tanggalMulai = $cekTglUbah->tanggalMulai;
						$tanggalSelesai = $cekTglUbah->tanggalSelesai;
					}
				}
			}
		}


		$response = array();
		$response['tipe'] = $tipe;
		$response['startDate'] = $startDate;
		$response['jumlahHari'] = $jumlahHari;
		$response['tanggalMulai'] = $tanggalMulai;
		$response['tanggalSelesai'] = $tanggalSelesai;
		$response['labelKRS'] = $labelKRS;

		$response['cekTglKRS'] = $cekTglKRS;
		$response['cekTglUbah'] = $cekTglUbah;

		return $response;
	}
}

if (!function_exists('convertTGL')) {

	function convertTGL($tgl)
	{
		$bulan = array('01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember');
		$hari = array('Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu');

		$time = strtotime($tgl);
		$date = date('l-d-m-Y', $time);
		$jam = (date('H:i:s', $time) === '00:00:00') ? '' : date('H:i:s', $time);
		$tanggal = explode('-', $date);

		return $hari[$tanggal[0]] . ", " . $tanggal[1] . " " . $bulan[$tanggal[2]] . " " . $tanggal[3] . " " . $jam;
	}
}


if (!function_exists('get_wilayah')) {
	function get_wilayah($d_code = 0, $d_group = null, $d_keyword = null, $d_parent_id = null)
	{
		$cek = 0;

		if ($d_group == NULL || $d_group == '' || $d_group == 0) {
			$select1 = DB::table('wilayah')->select('group', 'code', 'parent_id', 'name')->where('code', $d_code)->first();
			if (!$select1)
				return (object)['error' => 1];

			$d_group = $select1->group;
			$d_code = $select1->code;
			$d_parent = $select1->parent_id;
			$d_name = $select1->name;

			$map = ['00' => ['K' => 'Kode_Negara', 'N' => 'Negara'], '01' => ['K' => 'Kode_Propinsi', 'N' => 'Propinsi'], '02' => ['K' => 'Kode_Kota', 'N' => 'Kota'], 'default' => ['K' => 'Kode_Kecamatan', 'N' => 'Kecamatan']];
			$config = $map[$d_group] ?? $map['default'];

			$var = array($config['K'] => $d_code, $config['N'] => $d_name);
			while ($d_parent != 0) {
				$select2 = DB::table('wilayah')->select('group', 'code', 'parent_id', 'name')->where('code', $d_parent)->first();
				if (!$select2)
					break;

				$d_group = $select2->group;
				$d_code = $select2->code;
				$d_parent = $select2->parent_id;
				$d_name = $select2->name;

				$config = $map[$d_group] ?? $map['default'];
				$var[$config['K']] = $d_code;
				$var[$config['N']] = $d_name;
			}
			$var = json_decode(json_encode($var), FALSE);
		}
		else {
			$cek = 1;
			if ($d_group == '00') {
				$_var = ' SELECT a.`code` as `Kode_Negara`,a.`name` as `Negara`';
				$_var2 = ' FROM wilayah a WHERE a.`group`=\'00\' ORDER BY a.`code`';
				$_var3 = '';
			}
			else if ($d_group == '01') {
				$_var = ' SELECT a.`code` `Kode_Propinsi`,a.`name` `Propinsi`,b.`code` `Kode_Negara`,b.`name` `Negara`';
				$_var_parent_id = $d_parent_id ? " and a.parent_id = '$d_parent_id' " : "";
				$_var2 = " FROM wilayah a LEFT JOIN `wilayah` b ON a.`parent_id`=b.`code` WHERE a.`group`='01' $_var_parent_id ORDER BY a.`code`";
				$_var3 = $d_keyword ? " where abc.Propinsi LIKE '%$d_keyword%'" : "";
			}
			else if ($d_group == '02') {
				$_var = ' SELECT a.`code` `Kode_Kota`,a.`name` `Kota`,b.`code` `Kode_Propinsi`,b.`name` `Propinsi`,c.`code` `Kode_Negara`,c.`name` `Negara`';
				$_var_parent_id = $d_parent_id ? " and a.parent_id = '$d_parent_id' " : "";
				$_var2 = " FROM wilayah a LEFT JOIN `wilayah` b ON a.`parent_id`=b.`code` LEFT JOIN `wilayah` c ON b.`parent_id`=c.`code` WHERE a.`group`='02' $_var_parent_id ORDER BY a.`code`";
				$_var3 = $d_keyword ? "where abc.Kota LIKE '%$d_keyword%' OR abc.Propinsi LIKE '%$d_keyword%' " : "";
			}
			else if ($d_group == '03') {
				$_var = ' SELECT a.`code` `Kode_Kecamatan`,a.`name` `Kecamatan`,b.`code` `Kode_Kota`,b.`name` `Kota`,c.`code` `Kode_Propinsi`, c.`name` `Propinsi`,d.`code` `Kode_Negara`,d.`name` `Negara` ';
				$_var_parent_id = $d_parent_id ? " and a.parent_id = '$d_parent_id' " : "";
				$_var2 = " FROM wilayah a LEFT JOIN `wilayah` b ON a.`parent_id`=b.`code` LEFT JOIN `wilayah` c ON b.`parent_id`=c.`code` LEFT JOIN `wilayah` d ON c.`parent_id`=d.`code` WHERE a.`group`='03' and d.`code` is not null $_var_parent_id ORDER BY a.`code`";
				$_var3 = $d_keyword ? " where abc.Kecamatan LIKE '%$d_keyword%' OR abc.Kota LIKE '%$d_keyword%' OR abc.Propinsi LIKE '%$d_keyword%' " : "";
			}

			$var = DB::select("SELECT * FROM (" . $_var . "  " . $_var2 . ") abc " . $_var3 . " ");
		}

		if ($cek == 1) {
			$validate = DB::table('wilayah')->select('group')->where('group', $d_group)->first();
			return $validate ? $var : (object)['error' => 1];
		}
		else {
			$validate = DB::table('wilayah')->select('code')->where('code', $d_code)->first();
			return $validate ? $var : (object)['error' => 1];
		}
	}
}

if (!function_exists('get_sekolah')) {
	function get_sekolah($keyword = '', $ID = '')
	{
		$query = DB::table('sekolahdata');
		if ($ID) {
			$query->where('id', $ID);
		}
		if ($keyword) {
			$query->where('nama', 'like', "%$keyword%");
		}
		return $query->limit(10)->get()->toArray();
	}
}

if (!function_exists('view_khs')) {
	function view_khs($mhs, $tahunid = null)
	{
		$where = [
			'TahunID' => $tahunid,
			'MhswID' => $mhs,
			'PublishKHS' => 1
		];

		$nilai = DB::table('nilai')
			->select('nilai.*', 'nilai.NamaMataKuliah as NamaMatakuliah')
			->where($where)
			->get();

		return $nilai;
	}
}

if (!function_exists('view_ips')) {
    function view_ips($MhswID, $TahunID = null)
    {
        $mhs = DB::table('mahasiswa')->where('ID', $MhswID)->first();
        $tahun = DB::table('tahun')->select('TahunID')->where('ID', $TahunID)->first();

        if (!$mhs || !$tahun) return null;

        $query = DB::table('nilai as a')
            ->selectRaw('SUM(a.TotalSKS) AS JmlSKS, SUM(a.NilaiBobot) AS JmlBobot, SUM(a.NilaiBobot)/SUM(a.TotalSKS) AS IPS')
            ->where('a.MhswID', $MhswID)
            ->where('a.TahunID', $TahunID)
            ->where('a.PublishKHS', '1')
            ->first();

        if ($query) {
            $query->JmlBobot = number_format((float)$query->JmlBobot, 2, '.', '');
            $query->IPS = number_format((float)$query->IPS, 2, '.', '');
        }
        return $query;
    }
}

if (!function_exists('view_ipk')) {
    function view_ipk($MhswID, $TahunID = null)
    {
        $whr = ' AND t.PublishTranskrip=1';
        $total_sks = 0;
        $total_bobot = 0;
        $MKWajib = 0;

        $mhs = DB::table('mahasiswa')->select('NPM')->where('ID', $MhswID)->first();
        if (!$mhs) return null;

        if ($TahunID != NULL && $TahunID != '' && $TahunID != 0) {
            $tahun = DB::table('tahun')->select('TahunID')->where('ID', $TahunID)->first();
            if ($tahun) {
                $whr .= " AND KodeTahun <= '" . $tahun->TahunID . "' ";
            }
        }

        $sql = "SELECT t.*, dk.JenisMKID FROM nilai t JOIN detailkurikulum dk ON dk.ID=t.DetailKurikulumID WHERE t.MhswID = ? AND t.NilaiHuruf != '-' AND t.NilaiHuruf !='T' AND t.NilaiHuruf !='' $whr ORDER BY t.Semester, t.MKKode ASC";
        $query = DB::select($sql, [$MhswID]);

        $mkList = [];
        $mkList2 = [];
        $mkBobot = [];
        $listData = [];
        $listData2 = [];
        $except_nilai = [];

        $row_hide_nilai_huruf = DB::table(env('DB_MASTER_AIS_NAME') .'.setup_app')->where('tipe_setup', 'setup_hide_nilai_huruf')->first();
        if ($row_hide_nilai_huruf) {
            $metadata_hide_nilai_huruf = json_decode($row_hide_nilai_huruf->metadata, true);
            if ($metadata_hide_nilai_huruf) {
                $exp_huruf = array_filter(explode(",", $metadata_hide_nilai_huruf['hide_nilai_huruf']));
                if ($exp_huruf) {
                    $except_nilai = $exp_huruf;
                }
            }
        }

        foreach ($query as $valAwal) {
            if ($valAwal->JenisMKID == '5') {
                $MKWajib += 1;
            }
            if (!in_array($valAwal->NilaiHuruf, $except_nilai)) {
                if (!isset($listData[$valAwal->MKKode])) {
                    $listData[$valAwal->MKKode] = $valAwal;
                    $mkList[] = $valAwal->MKKode;
                    $mkBobot[$valAwal->MKKode] = $valAwal->Bobot;
                } else if ($valAwal->Bobot > $mkBobot[$valAwal->MKKode]) {
                    $listData[$valAwal->MKKode] = $valAwal;
                    $mkBobot[$valAwal->MKKode] = $valAwal->Bobot;
                }
            }
        }

        foreach ($listData as $valAwal) {
            $namaLower = strtolower($valAwal->NamaMataKuliah);
            if (!isset($listData2[$namaLower])) {
                $listData2[$namaLower] = $valAwal;
                $mkList2[] = $namaLower;
                $mkBobot[$namaLower] = $valAwal->Bobot;
            } else if ($valAwal->Bobot > $mkBobot[$namaLower]) {
                $listData2[$namaLower] = $valAwal;
                $mkBobot[$namaLower] = $valAwal->Bobot;
            }
        }

        foreach ($listData2 as $newVal) {
            $total_sks += $newVal->TotalSKS;
            $total_bobot += ($newVal->TotalSKS * $newVal->Bobot);
        }

        $ipk = new stdClass();
        $ipk->JmlSKS = $total_sks;
        $ipk->JmlBobot = $total_bobot;
        $ipk->JmlMKWajib = $MKWajib;

        if ($total_sks > 0) {
            $ipk->IPK = number_format((float)$total_bobot / $total_sks, 2, '.', '');
        } else {
            $ipk->IPK = number_format(0, 2, '.', '');
        }

        return $ipk;
    }
}

if (!function_exists('view_ipk_tanpa_skripsi')) {
    function view_ipk_tanpa_skripsi($MhswID, $TahunID = null)
    {
        $whr = "";
        if ($TahunID != NULL && $TahunID != '' && $TahunID != 0) {
            $tahun = DB::table('tahun')->select('TahunID')->where('ID', $TahunID)->first();
            if ($tahun) {
                $whr = " AND nilai.KodeTahun <= '" . $tahun->TahunID . "' ";
            }
        }

        $sql = "SELECT nilai.* FROM nilai JOIN detailkurikulum ON detailkurikulum.ID=nilai.DetailKurikulumID WHERE detailkurikulum.JenisMKID!=4 AND nilai.MhswID = ? AND nilai.NilaiHuruf != '-' AND nilai.NilaiHuruf !='T' AND nilai.NilaiHuruf !='' $whr ORDER BY nilai.Semester, nilai.MKKode ASC";
        $query = DB::select($sql, [$MhswID]);

        $mkList = [];
        $mkList2 = [];
        $mkBobot = [];
        $listData = [];
        $listData2 = [];
        $total_sks = 0;
        $total_bobot = 0;

        foreach ($query as $valAwal) {
            if (!isset($listData[$valAwal->MKKode])) {
                $listData[$valAwal->MKKode] = $valAwal;
                $mkList[] = $valAwal->MKKode;
                $mkBobot[$valAwal->MKKode] = $valAwal->Bobot;
            } else if ($valAwal->Bobot > $mkBobot[$valAwal->MKKode]) {
                $listData[$valAwal->MKKode] = $valAwal;
                $mkBobot[$valAwal->MKKode] = $valAwal->Bobot;
            }
        }

        foreach ($listData as $valAwal) {
            $namaLower = strtolower($valAwal->NamaMataKuliah);
            if (!isset($listData2[$namaLower])) {
                $listData2[$namaLower] = $valAwal;
                $mkList2[] = $namaLower;
                $mkBobot[$namaLower] = $valAwal->Bobot;
            } else if ($valAwal->Bobot > $mkBobot[$namaLower]) {
                $listData2[$namaLower] = $valAwal;
                $mkBobot[$namaLower] = $valAwal->Bobot;
            }
        }

        foreach ($listData2 as $newVal) {
            $total_sks += $newVal->TotalSKS;
            $total_bobot += ($newVal->TotalSKS * (float)$newVal->Bobot);
        }

        $ipk = new stdClass();
        $ipk->JmlSKS = $total_sks;
        $ipk->JmlBobot = $total_bobot;
        if ($total_sks > 0) {
            $ipk->IPK = number_format((float)$total_bobot / $total_sks, 2, '.', '');
        } else {
            $ipk->IPK = number_format(0, 2, '.', '');
        }

        return $ipk;
    }
}

if (!function_exists('view_krs')) {
    function view_krs($MhswID, $TahunID = null)
    {
        $query = DB::table('rencanastudi as a')
            ->join('mahasiswa as b', 'a.MhswID', '=', 'b.ID')
            ->join('detailkurikulum as c', 'c.ID', '=', 'a.DetailKurikulumID')
            ->select('a.ID', 'a.BobotMasterID', 'a.DetailKurikulumID', 'c.MKKode', 'c.Nama AS NamaMatakuliah', 'c.TotalSKS AS TotalSKS', 'c.SKSTatapMuka', 'c.SKSPraktikum', 'c.SKSPraktekLap', 'a.JadwalID', 'a.JadwalIDtmp', 'a.KurikulumID', 'a.MhswID', 'a.KelasID')
            ->where('a.MhswID', $MhswID)
            ->where('a.approval', '2');

        if ($TahunID) {
            $query->where('a.TahunID', $TahunID);
        }

        return $query->get()->toArray();
    }
}

if (!function_exists('view_transkrip')) {
    function view_transkrip($mhs)
    {
        return DB::table('nilai')->where(['MhswID' => $mhs, 'PublishTranskrip' => 1])->get()->toArray();
    }
}

if (!function_exists('view_transkrip_sementara')) {
    function view_transkrip_sementara($mhs)
    {
        return DB::table('nilai')->where(['MhswID' => $mhs, 'PublishTranskrip' => 1])->get()->toArray();
    }
}

if (!function_exists('get_kodetahun_tahunmasuk')) {
    function get_kodetahun_tahunmasuk($thn_masuk = '', $semester = '', $semester_masuk = 1)
    {
        $kode_tahun = '';
        if ($semester_masuk == 1) {
            if ($semester > 2) {
                $sem1 = (($semester % 2 == 0) ? 2 : 1);
                $semTemp = ($sem1 == 2) ? $semester - 1 : $semester;
                $sem2 = floor($semTemp / 2);
                $kode_tahun = ($thn_masuk + $sem2) . $sem1;
            } else {
                $kode_tahun = $thn_masuk . $semester;
            }
        } else if ($semester_masuk == 2) {
            if ($semester > 1) {
                $sem1 = (($semester % 2 == 0) ? 1 : 2);
                $semTemp = ($sem1 == 2) ? $semester - 1 : $semester;
                $sem2 = floor($semTemp / 2);
                $kode_tahun = ($thn_masuk + $sem2) . $sem1;
            } else {
                $kode_tahun = $thn_masuk . '2';
            }
        } else {
            $kode_tahun = '0';
        }

        return $kode_tahun;
    }
}

if (!function_exists('get_semester_tahunmasuk')) {
    function get_semester_tahunmasuk($thn_masuk, $thn_aktif, $semester = '')
    {
        $tahunaktif = (int)substr($thn_aktif, 0, 4);
        $semakhir = substr($thn_aktif, 4, 1);
        if ($semester == "1" || $semakhir == '2')
            $sem = ($tahunaktif - $thn_masuk) * 2 + 2;
        else
            $sem = ($tahunaktif - $thn_masuk) * 2 + 1;
        return $sem;
    }
}

if (!function_exists('get_semester')) {
    function get_semester($MhswID, $TahunID = null)
    {
        $whr = '';
        if ($TahunID != NULL && $TahunID != '' && $TahunID != 0) {
            $tahun = DB::table('tahun')->select('TahunID')->where('ID', $TahunID)->first();
            if ($tahun) {
                $whr = "AND b.TahunID <= '" . $tahun->TahunID . "' ";
            }
        }

        $sql = "SELECT COUNT(a.TahunID) as Semester FROM (	
					SELECT a.TahunID FROM rencanastudi a
					INNER JOIN tahun b ON a.TahunID = b.ID
					WHERE a.MhswID = ? " . $whr . " AND b.Semester != '3' AND b.Semester != 'SP' GROUP BY a.TahunID
				) a";
        
        return DB::selectOne($sql, [$MhswID]);
    }
}

if (!function_exists('get_status_nilai_mahasiswa')) {
    function get_status_nilai_mahasiswa($MhswID, $except_nilai)
    {
        $nilai_mhsw = DB::table('nilai')
            ->where(['MhswID' => $MhswID, 'PublishTranskrip' => 1])
            ->orderBy('Semester', 'ASC')
            ->orderBy('MKKode', 'ASC')
            ->get();

        $mkList = [];
        $mkList2 = [];
        $mkBobot = [];
        $listData = [];
        $listData2 = [];

        foreach ($nilai_mhsw as $valAwal) {
            if (!isset($listData[$valAwal->MKKode])) {
                $listData[$valAwal->MKKode] = $valAwal->NilaiHuruf;
                $mkList[] = $valAwal->MKKode;
                $mkBobot[$valAwal->MKKode] = $valAwal->Bobot;
            } else if ($valAwal->Bobot > $mkBobot[$valAwal->MKKode]) {
                $listData[$valAwal->MKKode] = $valAwal->NilaiHuruf;
                $mkBobot[$valAwal->MKKode] = $valAwal->Bobot;
            }
        }
        
        // Note: listData only contains NilaiHuruf. The original logic used $valAwal->NamaMataKuliah in lower.
        // Re-refactoring to maintain logic:
        $listDataObj = [];
        foreach ($nilai_mhsw as $valAwal) {
            if (!isset($listDataObj[$valAwal->MKKode])) {
                $listDataObj[$valAwal->MKKode] = $valAwal;
                $mkBobot[$valAwal->MKKode] = $valAwal->Bobot;
            } else if ($valAwal->Bobot > $mkBobot[$valAwal->MKKode]) {
                $listDataObj[$valAwal->MKKode] = $valAwal;
                $mkBobot[$valAwal->MKKode] = $valAwal->Bobot;
            }
        }

        foreach ($listDataObj as $valAwal) {
            $namaLower = strtolower($valAwal->NamaMataKuliah);
            if (!isset($listData2[$namaLower])) {
                $listData2[$namaLower] = $valAwal->NilaiHuruf;
                $mkList2[] = $namaLower;
                $mkBobot[$namaLower] = $valAwal->Bobot;
            } else if ($valAwal->Bobot > $mkBobot[$namaLower]) {
                $listData2[$namaLower] = $valAwal->NilaiHuruf;
                $mkBobot[$namaLower] = $valAwal->Bobot;
            }
        }

        $a1 = ['A', 'B', 'C', 'D', 'E'];
        $a2 = $except_nilai;
        $memenuhi_nilai = array_diff($a1, $a2);

        $tidak_memenuhi = count(array_diff($listData2, $memenuhi_nilai));

        return $tidak_memenuhi > 0;
    }
}

if (!function_exists('get_semester2')) {
    function get_semester2($MhswID, $TahunID = null)
    {
        $whr = '';
        if ($TahunID != NULL && $TahunID != '' && $TahunID != 0) {
            $tahun = DB::table('tahun')->select('TahunID')->where('ID', $TahunID)->first();
            if ($tahun) {
                $whr = "AND a.TahunID <= '" . $tahun->TahunID . "' ";
            }
        }

        $sql = "SELECT COUNT(a.TahunID) as Semester FROM (	
					SELECT a.TahunID FROM rencanastudi a
					INNER JOIN tahun b ON a.TahunID = b.ID
					WHERE a.MhswID = ? " . $whr . " AND b.Semester != '3' GROUP BY a.TahunID
				) a";
        
        return DB::selectOne($sql, [$MhswID]);
    }
}

if (!function_exists('get_semester_khs')) {
    function get_semester_khs($MhswID, $TahunID = null)
    {
        $whr = '';
        if ($TahunID != NULL && $TahunID != '' && $TahunID != 0) {
            $tahun = DB::table('tahun')->select('TahunID')->where('ID', $TahunID)->first();
            if ($tahun) {
                $whr = "AND a.TahunID <= '" . $tahun->TahunID . "' ";
            }
        }

        $sql = "SELECT COUNT(a.TahunID) as Semester FROM (	
					SELECT a.TahunID FROM nilai a
					INNER JOIN mahasiswa b ON a.MhswID = b.ID
					INNER JOIN tahun ON a.TahunID=tahun.ID 
					WHERE b.ID = ? " . $whr . " AND tahun.Semester != '3' GROUP BY a.TahunID
				) a";
        
        return DB::selectOne($sql, [$MhswID]);
    }
}

if (!function_exists('p_list_frs_billing')) {
    function p_list_frs_billing($v_tahun, $v_mhs, $v_jenis)
    {
        $mhs = DB::table('mahasiswa')->select('ID', 'ProdiID', 'ProgramID', 'KelasID')->where('NPM', $v_mhs)->first();
        if (!$mhs) return [];

        $v_mhswid = $mhs->ID;
        $v_prodi = $mhs->ProdiID;
        $v_program = $mhs->ProgramID;

        $query = DB::table('rencanastudi')
            ->select('rencanastudi.ID AS krs_sementara', 'rencanastudi.approval AS approval', 'detailkurikulum.ID AS MKID', 'detailkurikulum.MKKode', 'detailkurikulum.Nama AS NamaMK', 'detailkurikulum.NamaInggris', 'detailkurikulum.TotalSKS', 'detailkurikulum.MKIDpra', 'detailkurikulum.Semester');

        if ($v_jenis == 0) {
            // This case seemed to use 'jadwal' in original logic but started 'FROM rencanastudi'.
            // Original code: $var_join .= "INNER JOIN(kelas) ON (kelas.ID = jadwal.KelasID) INNER JOIN(detailkurikulum) ON (jadwal.DetailKurikulumID = detailkurikulum.ID) LEFT JOIN(rencanastudi) ON (rencanastudi.JadwalID = jadwal.ID AND rencanastudi.`MhswID` = '" . $v_mhswid . "') ";
            // It seems $v_jenis == 0 logic used 'jadwal' as base but code combined it weirdly.
            // Following original logic fallback:
            $query = DB::table('jadwal')
                ->join('kelas', 'kelas.ID', '=', 'jadwal.KelasID')
                ->join('detailkurikulum', 'jadwal.DetailKurikulumID', '=', 'detailkurikulum.ID')
                ->leftJoin('rencanastudi', function($join) use ($v_mhswid) {
                    $join->on('rencanastudi.JadwalID', '=', 'jadwal.ID')->where('rencanastudi.MhswID', '=', $v_mhswid);
                })
                ->select('rencanastudi.ID AS krs_sementara', 'rencanastudi.approval AS approval', 'detailkurikulum.ID AS MKID', 'detailkurikulum.MKKode', 'detailkurikulum.Nama AS NamaMK', 'detailkurikulum.NamaInggris', 'detailkurikulum.TotalSKS', 'detailkurikulum.MKIDpra', 'detailkurikulum.Semester');
        } else if ($v_jenis == 1) {
            $query->leftJoin('detailkurikulum', 'rencanastudi.DetailKurikulumID', '=', 'detailkurikulum.ID')
                  ->whereNotNull('rencanastudi.ID');
        } else if ($v_jenis == 2) {
            $query = DB::table('jadwal')
                ->join('kelas', 'kelas.ID', '=', 'jadwal.KelasID')
                ->join('detailkurikulum', 'jadwal.DetailKurikulumID', '=', 'detailkurikulum.ID')
                ->select('kelas.Nama AS NamaKelas', 'detailkurikulum.ID AS MKID', 'detailkurikulum.MKKode', 'detailkurikulum.Nama AS NamaMK', 'detailkurikulum.NamaInggris', 'detailkurikulum.TotalSKS', 'detailkurikulum.MKIDpra', 'detailkurikulum.Semester', 'jadwal.ID AS JadwalID', 'jadwal.TahunID', 'jadwal.ProdiID', 'jadwal.ProgramID', 'jadwal.KurikulumID', 'jadwal.DosenID', 'jadwal.KelasID');
        }

        if ($v_jenis != 2) {
            $query->where('rencanastudi.NPM', $v_mhs)
                  ->where('rencanastudi.TahunID', $v_tahun)
                  ->where('rencanastudi.ProdiID', $v_prodi)
                  ->where('rencanastudi.ProgramID', $v_program);
        }

        return $query->orderBy('detailkurikulum.Semester', 'ASC')->orderBy('detailkurikulum.MKKode', 'ASC')->get()->toArray();
    }
}

if (!function_exists('get_romawi')) {
    function get_romawi($integer, $upcase = true)
    {
        $table = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
        $return = '';
        while ($integer > 0) {
            foreach ($table as $rom => $arb) {
                if ($integer >= $arb) {
                    $integer -= $arb;
                    $return .= $rom;
                    break;
                }
            }
        }
        return $upcase ? strtoupper($return) : strtolower($return);
    }
}

if (!function_exists('generateThumbnail')) {
    function generateThumbnail($filename, $img, $width, $height, $quality = 90)
    {
        if (is_file($img)) {
            try {
                $imagick = new Imagick(realpath($img));
                $imagick->setImageFormat('jpeg');
                $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
                $imagick->setImageCompressionQuality($quality);
                $imagick->thumbnailImage($width, $height, false, false);
                $path_parts = pathinfo($img);
                $outputPath = $path_parts['dirname'] . '/' . $path_parts['filename'] . '_' . $filename . '.jpg';
                if (file_put_contents($outputPath, $imagick) === false) {
                    throw new Exception("Could not put contents.");
                }
                return true;
            } catch (Exception $e) {
                throw $e;
            }
        } else {
            throw new Exception("No valid image provided with {$img}.");
        }
    }
}

if (!function_exists('encrypt_url')) {
    function encrypt_url($url)
    {
        return $url;
    }
}

if (!function_exists('uri_segment')) {
    function uri_segment($segment)
    {
        return request()->segment($segment);
    }
}

if (!function_exists('get_where')) {
    function get_where($where, $namatabel, $field = "Nama")
    {
        $res = DB::table($namatabel)->select($field . " as field")->where($where)->first();
        return $res ? $res->field : null;
    }
}

if (!function_exists('sendNotification')) {
    function sendNotification($idGCM, $apiKey, $message)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $fields = array('to' => $idGCM, 'data' => $message);
        $headers = array('Authorization: key=' . $apiKey, 'Content-Type: application/json');
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            $err = curl_error($ch);
            curl_close($ch);
            die('Curl failed: ' . $err);
        }
        curl_close($ch);
        return $result;
    }
}

if (!function_exists('sendNotificationIos')) {
    function sendNotificationIos($token, $package, $message)
    {
        // Path logic needs to be adjusted for Laravel (e.g., storage_path())
        $keyfile = base_path('AuthKey_V3RVTBF3M7.p8'); 
        $keyid = 'V3RVTBF3M7';
        $teamid = 'D76P2K5H34';
        $bundleid = $package;
        $url = 'https://api.push.apple.com';

        if (!file_exists($keyfile)) return "Key file not found";

        $key = openssl_pkey_get_private('file://' . $keyfile);
        $header = ['alg' => 'ES256', 'kid' => $keyid];
        $claims = ['iss' => $teamid, 'iat' => time()];

        $header_encoded = base64($header);
        $claims_encoded = base64($claims);

        $signature = '';
        openssl_sign($header_encoded . '.' . $claims_encoded, $signature, $key, 'sha256');
        $jwt = $header_encoded . '.' . $claims_encoded . '.' . base64_encode($signature);

        if (!defined('CURL_HTTP_VERSION_2_0')) {
            define('CURL_HTTP_VERSION_2_0', 3);
        }
        $http2ch = curl_init();
        $results = [];
        foreach ((array)$token as $tok) {
            curl_setopt_array($http2ch, array(
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                CURLOPT_URL => "$url/3/device/$tok",
                CURLOPT_PORT => 443,
                CURLOPT_HTTPHEADER => array("apns-topic: {$bundleid}", "authorization: bearer $jwt"),
                CURLOPT_POST => TRUE,
                CURLOPT_POSTFIELDS => $message,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HEADER => 1
            ));
            $results[] = curl_exec($http2ch);
        }
        curl_close($http2ch);
        return implode("\n", $results);
    }
}

if (!function_exists('base64')) {
    function base64($data)
    {
        return rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
    }
}

if (!function_exists('presensiKaryawanMesin')) {
    function presensiKaryawanMesin($dataPin, $dataScandate)
    {
        // Placeholder as original was truncated
    }
}

if (!function_exists("presensiDosenV1")) {

	function presensiDosenV1($pin, $scan_date, $sn = '', $tipe = 'finger')
	{
		$v_toleransi_in = DB::table('setting_toleransi_dosen')
			->where('TipeUser', 'dosen')
			->value('tl_check_in');

		$v_toleransi_out = DB::table('setting_toleransi_dosen')
			->where('TipeUser', 'dosen')
			->value('tl_batas_check_out');

		$v_bisa_out = DB::table('setting_toleransi_dosen')
			->where('TipeUser', 'dosen')
			->value('tl_bisa_check_out');

		if ($tipe == 'finger') {
			$dosen = DB::table('dosen')->select('ID')->where('ID_finger', $pin)->first();
			$v_dosenID = $dosen->ID ?? null;

			$cek_mesin_finger = DB::table('mesin_finger')->where('Kode', $sn)->first();
		} else if ($tipe == 'qr') {
			$dosen = DB::table('dosen')->select('ID')->where('ID', $pin)->first();
			$v_dosenID = $dosen->ID ?? null;

			$cek_mesin_finger = DB::table('ruang')->select('ID as RuangID')->where('ID', $sn)->first();
		}

		if ($v_dosenID && $cek_mesin_finger && isset($cek_mesin_finger->RuangID)) {

			$query = DB::table('jadwal')
				->join('jadwalwaktu', 'jadwalwaktu.JadwalID', '=', 'jadwal.ID')
				->join('kodewaktu', 'kodewaktu.ID', '=', 'jadwalwaktu.WaktuID')
				->select('jadwal.ID', 'jadwalwaktu.ID AS JadwalWaktuID', 'jadwalwaktu.RuangID', 'jadwalwaktu.Pertemuan', 'jadwalwaktu.Tanggal', 'kodewaktu.JamMulai', 'kodewaktu.JamSelesai', 'jadwalwaktu.Sesi', 'jadwal.DetailKurikulumID')
				->where(function ($q) use ($v_dosenID) {
					$q->where('jadwal.DosenID', $v_dosenID)
						->orWhereRaw("FIND_IN_SET(?, jadwal.DosenAnggota)", [$v_dosenID]);
				})
				->whereDate('jadwalwaktu.Tanggal', date('Y-m-d', strtotime($scan_date)))
				->whereRaw("TIME(?) BETWEEN TIMEDIFF(kodewaktu.JamMulai, ?) AND ADDTIME(kodewaktu.JamSelesai, ?)", [$scan_date, $v_toleransi_in, $v_toleransi_out])
				->get();

			if ($query->isNotEmpty()) {
				foreach ($query as $value) {
					$v_jadwalD = $value->ID;
					$v_jadwalwaktuID = $value->JadwalWaktuID;
					$v_ruangID = $value->RuangID;
					$v_pertemuan = $value->Pertemuan;
					$v_tanggal = $value->Tanggal;
					$v_jamMulai = $value->JamMulai;
					$v_jamSelesai = $value->JamSelesai;
					$v_sesi = $value->Sesi;
					$v_detailkurikulumID = $value->DetailKurikulumID;

					$v_cek_check_in = DB::table('presensidosen')
						->where('JadwalwaktuID', $v_jadwalwaktuID)
						->where('DosenID', $v_dosenID)
						->where('Pertemuan', $v_pertemuan)
						->count();

					if ($v_cek_check_in == 0) {
						DB::table('presensidosen')->insert([
							'JadwalID' => $v_jadwalD,
							'JadwalWaktuID' => $v_jadwalwaktuID,
							'JenisPresensiID' => '1',
							'DosenID' => $v_dosenID,
							'RuangID' => $v_ruangID,
							'Pertemuan' => $v_pertemuan,
							'Tgl' => date('Y-m-d', strtotime($scan_date)),
							'Datang' => date('H:i:s', strtotime($scan_date)),
							'TglMengajar' => $v_tanggal,
							'DetailKurikulumID' => $v_detailkurikulumID,
							'Tipe' => $tipe
						]);
						log_presensi($v_jadwalwaktuID, $v_dosenID, 'dosen', $tipe, '1');
					} else {
						$time_scandate = date('H:i:s', strtotime($scan_date));
						$addtime_mulaibisa = DB::selectOne("SELECT ADDTIME(?, ?) as a", [$v_jamMulai, $v_bisa_out])->a;
						if ($time_scandate > $addtime_mulaibisa) {
							DB::table('presensidosen')
								->where('JadwalwaktuID', $v_jadwalwaktuID)
								->where('DosenID', $v_dosenID)
								->where('Pertemuan', $v_pertemuan)
								->update([
									'Pulang' => $time_scandate
								]);
						}
					}
				}
			} else {
				# Update Status Proses Ke No Karena tidak terproses
				DB::table('histori_absen_dosen_mahasiswa')
					->where('User', 'dosen')
					->where('pin', $pin)
					->where('Scanlog', $scan_date)
					->update(['procesed' => '0']);
			}
		} else {
			# Update Status Proses Ke No Karena tidak terproses
			DB::table('histori_absen_dosen_mahasiswa')
				->where('User', 'dosen')
				->where('pin', $pin)
				->where('Scanlog', $scan_date)
				->update(['procesed' => '0']);
		}
	}
}

if (!function_exists("presensiMahasiswaV1")) {

	function presensiMahasiswaV1($pin, $scan_date, $sn = '', $tipe = 'finger')
	{
		$v_toleransi_in = DB::table('setting_toleransi_mahasiswa')
			->where('TipeUser', 'mahasiswa')
			->value('tl_check_in');

		$v_toleransi_out = DB::table('setting_toleransi_mahasiswa')
			->where('TipeUser', 'mahasiswa')
			->value('tl_batas_check_out');

		$v_bisa_out = DB::table('setting_toleransi_mahasiswa')
			->where('TipeUser', 'mahasiswa')
			->value('tl_bisa_check_out');

		if ($tipe == 'finger') {
			$mhs = DB::table('mahasiswa')->select('ID')->where('ID_finger', $pin)->first();
			$v_mhswID = $mhs->ID ?? null;

			$cek_mesin_finger = DB::table('mesin_finger')->where('Kode', $sn)->first();
		} else if ($tipe == 'qr') {
			$mhs = DB::table('mahasiswa')->select('ID')->where('ID', $pin)->first();
			$v_mhswID = $mhs->ID ?? null;

			$cek_mesin_finger = DB::table('ruang')->select('ID as RuangID')->where('ID', $sn)->first();
		}


		if ($v_mhswID && $cek_mesin_finger && isset($cek_mesin_finger->RuangID)) {
			// Note: FIND_IN_SET for RuangID depends on if RuangID is a list. Original code used 'jadwalwaktu.RuangID in ($cek_mesin_finger->RuangID)'
			$query = DB::table('jadwal')
				->join('jadwalwaktu', function ($join) use ($cek_mesin_finger) {
					$join->on('jadwalwaktu.JadwalID', '=', 'jadwal.ID');
					if (str_contains($cek_mesin_finger->RuangID, ',')) {
						$join->whereRaw("jadwalwaktu.RuangID IN ($cek_mesin_finger->RuangID)");
					} else {
						$join->where('jadwalwaktu.RuangID', $cek_mesin_finger->RuangID);
					}
				})
				->join('kodewaktu', 'kodewaktu.ID', '=', 'jadwalwaktu.WaktuID')
				->join('rombel', 'jadwal.ID', '=', 'rombel.JadwalID')
				->join('peserta_rombel', 'rombel.ID', '=', 'peserta_rombel.GroupPesertaID')
				->join('rencanastudi', function ($join) use ($v_mhswID) {
					$join->on('rencanastudi.JadwalID', '=', 'jadwal.ID')
						->where('rencanastudi.MhswID', '=', $v_mhswID)
						->where('rencanastudi.approval', '=', '2');
				})
				->select('jadwal.ID', 'jadwalwaktu.ID AS JadwalWaktuID', 'jadwalwaktu.RuangID', 'jadwalwaktu.Pertemuan', 'jadwalwaktu.Tanggal', 'kodewaktu.JamMulai', 'kodewaktu.JamSelesai', 'jadwalwaktu.Sesi', 'jadwal.DetailKurikulumID', 'jadwalwaktu.WaktuID', 'jadwalwaktu.HariID')
				->where('peserta_rombel.MhswID', $v_mhswID)
				->whereDate('jadwalwaktu.Tanggal', date('Y-m-d', strtotime($scan_date)))
				->whereRaw("TIME(?) BETWEEN TIMEDIFF(kodewaktu.JamMulai, ?) AND ADDTIME(kodewaktu.JamSelesai, ?)", [$scan_date, $v_toleransi_in, $v_toleransi_out])
				->get();

			if ($query->isNotEmpty()) {
				foreach ($query as $value) {
					$v_jadwalD = $value->ID;
					$v_jadwalwaktuID = $value->JadwalWaktuID;
					$v_ruangID = $value->RuangID;
					$v_pertemuan = $value->Pertemuan;
					$v_tanggal = $value->Tanggal;
					$v_jamMulai = $value->JamMulai;
					$v_jamSelesai = $value->JamSelesai;
					$v_waktuID = $value->WaktuID;
					$v_hariID = $value->HariID;
					$v_detailkurikulumID = $value->DetailKurikulumID;

					$v_cek_check_in = DB::table('presensimahasiswa')
						->where('JadwalwaktuID', $v_jadwalwaktuID)
						->where('MhswID', $v_mhswID)
						->where('Pertemuan', $v_pertemuan)
						->count();

					if ($v_cek_check_in == 0) {
						$cek_presensidosen = DB::table('presensidosen')->select('ID')->where('JadwalWaktuID', $v_jadwalwaktuID)->first();
						if ($cek_presensidosen) {
							DB::table('presensimahasiswa')->insert([
								'JadwalID' => $v_jadwalD,
								'JadwalWaktuID' => $v_jadwalwaktuID,
								'JenisPresensiID' => '1',
								'MhswID' => $v_mhswID,
								'RuangID' => $v_ruangID,
								'Pertemuan' => $v_pertemuan,
								'TglDiajar' => date('Y-m-d', strtotime($scan_date)),
								'Datang' => date('H:i:s', strtotime($scan_date)),
								'Tgl' => $v_tanggal,
								'DetailKurikulumID' => $v_detailkurikulumID,
								'WaktuID' => $v_waktuID,
								'HariID' => $v_hariID,
								'Pulang' => $v_jamSelesai,
								'Tipe' => $tipe
							]);
						}
						log_presensi($v_jadwalwaktuID, $v_mhswID, 'mahasiswa', $tipe, '1');
					} else {
						$time_scandate = date('H:i:s', strtotime($scan_date));
						$addtime_mulaibisa = DB::selectOne("SELECT ADDTIME(?, ?) as a", [$v_jamMulai, $v_bisa_out])->a;
						if ($time_scandate > $addtime_mulaibisa) {
							DB::table('presensimahasiswa')
								->where('JadwalwaktuID', $v_jadwalwaktuID)
								->where('MhswID', $v_mhswID)
								->where('Pertemuan', $v_pertemuan)
								->update([
									'Pulang' => $v_jamSelesai
								]);
						}
					}
				}
			} else {
				# Update Status Proses Ke No Karena tidak terproses
				DB::table('histori_absen_dosen_mahasiswa')
					->where('User', 'mahasiswa')
					->where('pin', $pin)
					->where('Scanlog', $scan_date)
					->update(['procesed' => '0']);
			}
		} else {
			# Update Status Proses Ke No Karena tidak terproses
			DB::table('histori_absen_dosen_mahasiswa')
				->where('User', 'mahasiswa')
				->where('pin', $pin)
				->where('Scanlog', $scan_date)
				->update(['procesed' => '0']);
		}
	}
}

if (!function_exists('log_presensi')) {

	function log_presensi($JadwalWaktuID = '', $EntityID = '', $JenisAbsen = '', $Tipe = '', $JenisPresensiID = '')
	{
		$arr_jenisabsen = array('mahasiswa', 'dosen');
		$arr_tipe = array('manual', 'finger', 'elearning', 'qr');

		if (!empty($JadwalWaktuID) && !empty($EntityID) && !empty($JenisAbsen) && in_array($JenisAbsen, $arr_jenisabsen) && in_array($Tipe, $arr_tipe) && !empty($Tipe) && !empty($JenisPresensiID)) {

			$jadwalwaktu = get_id($JadwalWaktuID, 'jadwalwaktu');
			$jadwal = get_id($jadwalwaktu->JadwalID, 'jadwal');
			$detailkurikulum = get_id($jadwal->DetailKurikulumID, 'detailkurikulum');
			$entity = get_id($EntityID, $JenisAbsen);

			$NoID = ($JenisAbsen == 'mahasiswa') ? $entity->NPM : $entity->NIDN;

			$input = [];
			$input['EntityID'] = $EntityID;
			$input['JenisAbsen'] = $JenisAbsen;
			$input['JenisPresensiID'] = $JenisPresensiID;
			$input['JadwalWaktuID'] = $JadwalWaktuID;
			$input['JadwalID'] = $jadwal->ID;
			$input['TahunID'] = $jadwal->TahunID;
			$input['ProdiID'] = $jadwal->ProdiID;
			$input['ProgramID'] = $jadwal->ProgramID;
			$input['Pertemuan'] = $jadwalwaktu->Pertemuan;
			$input['Sesi'] = $jadwalwaktu->Sesi;
			$input['Tipe'] = $Tipe;
			$input['NoID'] = $NoID;
			$input['Nama'] = $entity->Nama;
			$input['DetailKurikulumID'] = $jadwal->DetailKurikulumID;
			$input['KurikulumID'] = $detailkurikulum->KurikulumID;
			$input['MKKode'] = $jadwal->MKKode;
			$input['UserID'] = session('UserID') ?? 0;
			$input['createdAt'] = date('Y-m-d H:i:s');
			DB::table("log_presensi")->insert($input);
		}

		return TRUE;
	}
}

// function report dari thamrin mulai

if (!function_exists('count_aktif_per_tahun_prodi')) {

	function count_aktif_per_tahun_prodi($programid)
	{
		$whr = "";
		if ($programid)
			$whr .= " WHERE ProgramID='" . $programid . "' ";
		$query = DB::select("SELECT TahunID, ProdiID, COUNT(DISTINCT NPM) as Jumlah FROM rencanastudi $whr GROUP BY TahunID, ProdiID");
		$data = array();
		foreach ($query as $row) {
			$data[$row->TahunID . "_" . $row->ProdiID] = $row->Jumlah;
		}
		return $data;
	}
}

if (!function_exists('get_bayar_per_tahun_prodi')) {

	function get_bayar_per_tahun_prodi($programid, $JenisBiayaID = '')
	{
		$whr = "";
		if ($programid)
			$whr .= " AND a.ProgramID='" . $programid . "' ";
		if ($JenisBiayaID)
			$whr .= " AND a.JenisBiayaID='" . $JenisBiayaID . "' ";

		$nowDate = date('Y-m-d');
		$query = DB::select("SELECT 
										COUNT(*) AS jmldata,
										SUM(b.Jumlah) AS total,
										e.idsemesterbayar,
										d.ProdiID 
									  FROM
										tagihan_mahasiswa a
										LEFT JOIN cicilan_tagihan_mahasiswa b ON a.ID = b.TagihanMahasiswaID 
										LEFT JOIN jenisbiaya c ON a.JenisBiayaID = c.ID 
										INNER JOIN mahasiswa d ON a.UserID = d.ID 
										INNER JOIN tahunbayar e ON e.nokwitansi = b.NoKwitansi 
									  WHERE 
										b.Verifikasi = 'Sudah' 
										AND a.JenisMahasiswa = 'mhsw' 
										AND a.TanggalTagihan <= '$nowDate' 
  										AND b.TanggalBayarBank <= '$nowDate' 
										AND d.ProdiID IS NOT NULL
										AND e.idsemesterbayar IS NOT NULL $whr
									  GROUP BY a.ID,e.idsemesterbayar,d.ProdiID
									  ORDER BY d.ProdiID,
										e.idsemesterbayar ASC");
		$data = array();
		foreach ($query as $row) {
			$key = $row->idsemesterbayar . "_" . $row->ProdiID;
			if (!isset($data[$key])) $data[$key] = 0;
			$data[$key] += intval($row->total);
		}
		return $data;
	}
}

if (!function_exists('saldo_semester_lalu')) {

	function saldo_semester_lalu($tahunid, $prodid, $programid)
	{
		//
	}
}

if (!function_exists('count_mhsw_tunggak')) {

	function count_mhsw_tunggak($programid, $JenisBiayaID = '')
	{
		$whr = "";
		if ($programid)
			$whr .= " AND a.ProgramID='" . $programid . "' ";
		if ($JenisBiayaID)
			$whr .= " AND a.JenisBiayaID='" . $JenisBiayaID . "' ";

		$query = DB::select("SELECT a.Periode,c.ProdiID,COUNT(DISTINCT a.UserID) as Jumlah FROM tagihan_mahasiswa a LEFT JOIN mahasiswa c ON a.UserID=c.ID WHERE a.STATUS!='Lunas' $whr AND c.ProdiID IS NOT NULL
									GROUP BY a.Periode,c.ProdiID ORDER BY c.ProdiID,a.Periode ");
		$data = array();
		foreach ($query as $row) {
			$data[$row->Periode . "_" . $row->ProdiID] = $row->Jumlah;
		}
		return $data;
	}
}

if (!function_exists('count_aktif_per_angkatan_prodi')) {

	function count_aktif_per_angkatan_prodi($programid)
	{
		$whr = "";
		if ($programid)
			$whr .= " AND ProgramID='" . $programid . "' ";
		$query = DB::select("SELECT count(ID) as Jumlah,TahunMasuk,ProdiID from mahasiswa where StatusMhswID='3' $whr group by TahunMasuk,ProdiID");
		$data = array();
		foreach ($query as $row) {
			$data[$row->TahunMasuk . "_" . $row->ProdiID] = (int)$row->Jumlah;
		}
		return $data;
	}
}

if (!function_exists('get_bayar_per_angkatan_prodi')) {

	function get_bayar_per_angkatan_prodi($programid)
	{
		$whr = "";
		if ($programid)
			$whr .= " AND a.ProgramID='" . $programid . "' ";

		$nowDate = date('Y-m-d');
		$query = DB::select("SELECT 
										COUNT(*) AS jmldata,
										SUM(b.Jumlah) AS total,
										d.TahunMasuk,
										d.ProdiID 
									  FROM
										tagihan_mahasiswa a
										LEFT JOIN cicilan_tagihan_mahasiswa b ON a.ID = b.TagihanMahasiswaID 
										LEFT JOIN jenisbiaya c ON a.JenisBiayaID = c.ID 
										INNER JOIN mahasiswa d ON a.UserID = d.ID 
										INNER JOIN tahunbayar e ON e.nokwitansi = b.NoKwitansi 
									  WHERE 
										b.Verifikasi = 'Sudah' 
										AND a.JenisMahasiswa = 'mhsw' 
										AND a.TanggalTagihan <= '$nowDate' 
  										AND b.TanggalBayarBank <= '$nowDate' 
										AND d.ProdiID IS NOT NULL
										AND e.idsemesterbayar IS NOT NULL $whr
									  GROUP BY a.ID,d.TahunMasuk,d.ProdiID
									  ORDER BY d.ProdiID,
										d.TahunMasuk ASC");
		$data = array();
		foreach ($query as $row) {
			$key = $row->TahunMasuk . "_" . $row->ProdiID;
			if (!isset($data[$key])) $data[$key] = 0;
			$data[$key] += intval($row->total);
		}
		return $data;
	}
}

if (!function_exists('get_bayar_smt_lalu_angkatan_prodi')) {

	function get_bayar_smt_lalu_angkatan_prodi($programid)
	{
		$whr = "";
		if ($programid)
			$whr .= " AND a.ProgramID='" . $programid . "' ";

		$tahun_aktif_row = DB::table('tahun')->where('ProsesBuka', '1')->orderBy('ID', 'desc')->first();
		$tahun_aktif = $tahun_aktif_row->ID ?? '';
		$tahun_sblm_row = DB::table('tahun')->where('ID', '<', $tahun_aktif)->orderBy('ID', 'desc')->first();
		$tahun_sblm = $tahun_sblm_row->ID ?? '';

		$nowDate = date('Y-m-d');
		$query = DB::select("SELECT 
										COUNT(*) AS jmldata,
										SUM(b.Jumlah) AS total,
										d.TahunMasuk,
										d.ProdiID 
									  FROM
										tagihan_mahasiswa a
										LEFT JOIN cicilan_tagihan_mahasiswa b ON a.ID = b.TagihanMahasiswaID 
										LEFT JOIN jenisbiaya c ON a.JenisBiayaID = c.ID 
										INNER JOIN mahasiswa d ON a.UserID = d.ID 
										INNER JOIN tahunbayar e ON e.nokwitansi = b.NoKwitansi 
									  WHERE 
										b.Verifikasi = 'Sudah' 
										AND a.JenisMahasiswa = 'mhsw' 
										AND a.TanggalTagihan <= '$nowDate' 
  										AND b.TanggalBayarBank <= '$nowDate' 
										AND d.ProdiID IS NOT NULL
										AND e.idsemesterbayar IS NOT NULL $whr
										AND a.Periode = ?
									  GROUP BY a.ID,d.TahunMasuk,d.ProdiID
									  ORDER BY d.ProdiID,
										d.TahunMasuk ASC", [$tahun_sblm]);
		$data = array();
		foreach ($query as $row) {
			$key = $row->TahunMasuk . "_" . $row->ProdiID;
			if (!isset($data[$key])) $data[$key] = 0;
			$data[$key] += intval($row->total);
		}
		return $data;
	}
}

if (!function_exists('count_mhsw_tunggak_angkatan')) {

	function count_mhsw_tunggak_angkatan($programid)
	{
		$whr = "";
		if ($programid)
			$whr .= " AND a.ProgramID='" . $programid . "' ";
		$query = DB::select("SELECT c.TahunMasuk,c.ProdiID,COUNT(DISTINCT a.UserID) as Jumlah FROM tagihan_mahasiswa a LEFT JOIN mahasiswa c ON a.UserID=c.ID WHERE a.STATUS!='Lunas' $whr AND c.ProdiID IS NOT NULL
									GROUP BY c.TahunMasuk,c.ProdiID ORDER BY c.ProdiID,c.TahunMasuk ");
		$data = array();
		foreach ($query as $row) {
			$data[$row->TahunMasuk . "_" . $row->ProdiID] = $row->Jumlah;
		}
		return $data;
	}
}
// function report dari thamrin selesai

/* FUNCTION FEEDER */

if (!function_exists('getIdentitasFeeder')) {
	function getIdentitasFeeder()
	{
		return DB::table("feeder_identitas")->first();
	}
}

if (!function_exists('runWS')) {
	function runWS($data, $type = 'json')
	{
		$identitas = getIdentitasFeeder();
		if (!$identitas) return null;
		$url = $identitas->Url . "/ws/live2.php";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, true);

		$headers = array();

		if ($type == 'xml')
			$headers[] = 'Content-Type: application/xml';
		else
			$headers[] = 'Content-Type: application/json';

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		if ($data) {
			if ($type == 'xml') {
				$data = stringXML($data);
			} else {
				$data = json_encode($data);
			}
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);

		$php_result = json_decode($result, TRUE);
		if (!isset($php_result['error_code'])) {
			if (isset($php_result['code'])) {
				$temp_result = array();
				$temp_result['error_code'] = $php_result['code'];
				$temp_result['error_desc'] = $php_result['message'];
				$temp_result['data'] = $php_result['data'] ?? null;

				$php_result = $temp_result;
				$result = json_encode($php_result);
			}
		}
		if (curl_error($ch) || empty($php_result)) {
			die("<h2>ERROR Saat Koneksi Ke Feeder, silahkan cek konfigurasi Username,Password, dan Url di Menu Setting Feeder dengan klik <a href='" . url('#c_feeder_setting/') . "'><b>Disini. </b></a></h2>");
		}
		if (isset($php_result['error_code']) && $php_result['error_code'] == 12) {
			die("<h2>Username / Password Salah, silahkan cek konfigurasi Username,Password, dan Url di Menu Setting Feeder dengan klik <a href='" . url('#c_feeder_setting/') . "'><b>Disini. </b></a></h2>");
		}

		curl_close($ch);

		return $result;
	}
}

if (!function_exists('stringXML')) {
	function stringXML($data)
	{
		$xml = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
		array_to_xml($data, $xml);
		return $xml->asXML();
	}
}

if (!function_exists('array_to_xml')) {
	function array_to_xml($data, &$xml_data)
	{
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$subnode = $xml_data->addChild($key);
				array_to_xml($value, $subnode);
			} else {
				$xml_data->addChild("$key", htmlspecialchars($value));
			}
		}
	}
}

if (!function_exists('intoTables')) {
	function intoTables($rows)
	{
		$i = 0;
		$str = '<table class="data_grid">';
		foreach ($rows as $row) {
			if (!$i) {
				$str .= '<tr>';
				$str .= '<th>No</th>';
				foreach (array_keys((array)$row) as $v) {
					$str .= '<th>';
					$str .= $v;
					$str .= '</th>';
				}
				$str .= '</tr>';
			}
			$str .= '<tr>';
			$i++;

			$style = '';
			$rowData = (array)$row;
			foreach ($rowData as $k => $v) {
				if (strtolower($k) == 'soft_delete' && $v == '1') {
					$style = 'style="text-decoration:line-through"';
				}
			}
			$str .= "<td $style >$i.</td>";
			foreach ($rowData as $k => $v) {
				$str .= "<td $style>";
				if (!is_array($v))
					$str .= $v;
				$str .= '&nbsp;</td>';
			}

			$str .= '</tr>';
		}
		$str .= '</table>';

		return $str;
	}
}

if (!function_exists('getTokenFeeder')) {
    function getTokenFeeder()
    {
        $identitas = getIdentitasFeeder();
        if (!$identitas) return '';

        $username = $identitas->Username;
        $password = $identitas->Password;

        $data = array('act' => 'GetToken', 'username' => $username, 'password' => $password);

        $result_string = runWS($data, 'json');
        $result_array = json_decode($result_string, TRUE);

        if (isset($result_array['error_code']) && $result_array['error_code'] == 0) {
            $token = $result_array['data']['token'];
        } else {
            $token = '';
        }

        return $token;
    }
}

if (!function_exists('getProfilPTFeeder')) {
    function getProfilPTFeeder($token)
    {
        $ctype = 'json';
        $data = array(
            'act' => 'GetProfilPT',
            'token' => $token,
            'filter' => "",
            'order' => "",
            'limit' => 20,
            'offset' => 0,
        );

        $result_string = runWS($data, $ctype);
        $result_array = json_decode($result_string, TRUE);

        return $result_array['data'][0] ?? null;
    }
}

if (!function_exists('getProdiFeederByKode')) {
    function getProdiFeederByKode($token, $kode)
    {
        $ctype = 'json';
        $filter = "TRIM(kode_program_studi)=TRIM('$kode')";
        $data = array(
            'act' => 'GetProdi',
            'token' => $token,
            'filter' => $filter,
            'order' => "",
            'limit' => 20,
            'offset' => 0,
        );

        $result_string = runWS($data, $ctype);
        $result_array = json_decode($result_string, TRUE);

        return $result_array['data'][0] ?? null;
    }
}

if (!function_exists('getProdiFeederByID')) {
    function getProdiFeederByID($token, $ID)
    {
        $ctype = 'json';
        $filter = "id_prodi='$ID'";
        $data = array(
            'act' => 'GetProdi',
            'token' => $token,
            'filter' => $filter,
            'order' => "",
            'limit' => 20,
            'offset' => 0,
        );

        $result_string = runWS($data, $ctype);
        $result_array = json_decode($result_string, TRUE);

        return $result_array['data'][0] ?? null;
    }
}

if (!function_exists('getMasterMatkulFeederByID')) {
    function getMasterMatkulFeederByID($token, $ID)
    {
        $ctype = 'json';
        $filter = "id_matkul='$ID'";
        $data = array(
            'act' => 'GetDetailMataKuliah',
            'token' => $token,
            'filter' => $filter,
            'order' => "",
            'limit' => 20,
            'offset' => 0,
        );

        $result_string = runWS($data, $ctype);
        $result_array = json_decode($result_string, TRUE);

        return $result_array['data'][0] ?? null;
    }
}

if (!function_exists('getBiodataDosenFeederByID')) {
    function getBiodataDosenFeederByID($token, $ID)
    {
        $ctype = 'json';
        $filter = "id_dosen='$ID'";
        $data = array(
            'act' => 'DetailBiodataDosen',
            'token' => $token,
            'filter' => $filter,
            'order' => "",
            'limit' => 20,
            'offset' => 0,
        );

        $result_string = runWS($data, $ctype);
        $result_array = json_decode($result_string, TRUE);

        return $result_array['data'][0] ?? null;
    }
}

if (!function_exists('getListDosenFeederByID')) {
    function getListDosenFeederByID($token, $ID)
    {
        $ctype = 'json';
        $filter = "id_dosen='$ID'";
        $data = array(
            'act' => 'GetListPenugasanDosen',
            'token' => $token,
            'filter' => $filter,
            'order' => "",
            'limit' => 20,
            'offset' => 0,
        );

        $result_string = runWS($data, $ctype);
        $result_array = json_decode($result_string, TRUE);

        return $result_array['data'][0] ?? null;
    }
}

if (!function_exists('getListMahasiswaFeederByID')) {
    function getListMahasiswaFeederByID($token, $ID)
    {
        $ctype = 'json';
        $filter = "id_mahasiswa='$ID'";
        $data = array(
            'act' => 'GetListMahasiswa',
            'token' => $token,
            'filter' => $filter,
            'order' => "",
            'limit' => 20,
            'offset' => 0,
        );

        $result_string = runWS($data, $ctype);
        $result_array = json_decode($result_string, TRUE);

        return $result_array['data'][0] ?? null;
    }
}

if (!function_exists('getMatkulFeederByKodeAndProdi')) {
    function getMatkulFeederByKodeAndProdi($token, $kode, $id_prodi)
    {
        $ctype = 'json';
        $filter = "TRIM(kode_mata_kuliah)=TRIM('$kode') AND id_prodi='$id_prodi' ";
        $data = array(
            'act' => 'GetDetailMataKuliah',
            'token' => $token,
            'filter' => $filter,
            'order' => "",
            'limit' => 20,
            'offset' => 0,
        );

        $result_string = runWS($data, $ctype);
        $result_array = json_decode($result_string, TRUE);

        return $result_array['data'][0] ?? null;
    }
}

if (!function_exists('getKelasKuliahFeeder')) {
    function getKelasKuliahFeeder($token, $mkkode, $kodekelas, $tahunsemester, $id_prodi = '')
    {
        $ctype = 'json';
        $w = $id_prodi ? " AND id_prodi='$id_prodi' " : "";
        $filter = "TRIM(kode_mata_kuliah)=TRIM('$mkkode') AND TRIM(nama_kelas_kuliah)=TRIM('$kodekelas') AND id_semester='$tahunsemester' $w ";

        $data = array(
            'act' => 'GetDetailKelasKuliah',
            'token' => $token,
            'filter' => $filter,
            'order' => "",
            'limit' => 20,
            'offset' => 0,
        );

        $result_string = runWS($data, $ctype);
        $result_array = json_decode($result_string, TRUE);

        return $result_array['data'][0] ?? null;
    }
}

if (!function_exists('getKelasKuliahFeederByIDMatkul')) {
    function getKelasKuliahFeederByIDMatkul($token, $id_matkul)
    {
        $ctype = 'json';
        $filter = "id_matkul='$id_matkul' ";
        $data = array(
            'act' => 'GetDetailKelasKuliah',
            'token' => $token,
            'filter' => $filter,
            'order' => "",
            'limit' => 20,
            'offset' => 0,
        );

        $result_string = runWS($data, $ctype);
        $result_array = json_decode($result_string, TRUE);

        return $result_array['data'][0] ?? null;
    }
}

if (!function_exists('getRegistrasiMahasiswaFeeder')) {
    function getRegistrasiMahasiswaFeeder($token, $npm, $id_feeder = "")
    {
        $ctype = 'json';
        $profil = getProfilPTFeeder($token);
        $id_perguruan_tinggi = $profil['id_perguruan_tinggi'] ?? null;

        $filter = "TRIM(nim)=TRIM('$npm') AND id_perguruan_tinggi='$id_perguruan_tinggi' ";
        if (!empty($id_feeder)) {
            $filter .= " AND id_prodi='$id_feeder' ";
        }

        $data = array(
            'act' => 'GetListMahasiswa',
            'token' => $token,
            'filter' => $filter,
            'order' => "",
            'limit' => 20,
            'offset' => 0,
        );

        $result_string = runWS($data, $ctype);
        $result_array = json_decode($result_string, TRUE);

        return $result_array['data'][0] ?? null;
    }
}

if (!function_exists('getKelasKuliahFeederDariTabel')) {
    function getKelasKuliahFeederDariTabel($ID)
    {
        return DB::table('feeder_data_kelaskuliah')->where('id_kelas_kuliah', $ID)->first();
    }
}

if (!function_exists('getRegistrasiMahasiswaFeederByIDMahasiswa')) {
    function getRegistrasiMahasiswaFeederByIDMahasiswa($token, $id_mahasiswa)
    {
        $ctype = 'json';
        $profil = getProfilPTFeeder($token);
        $id_perguruan_tinggi = $profil['id_perguruan_tinggi'] ?? null;

        $filter = "id_mahasiswa='$id_mahasiswa' AND id_perguruan_tinggi='$id_perguruan_tinggi' ";
        $data = array(
            'act' => 'GetListMahasiswa',
            'token' => $token,
            'filter' => $filter,
            'order' => "",
            'limit' => 20,
            'offset' => 0,
        );

        $result_string = runWS($data, $ctype);
        $result_array = json_decode($result_string, TRUE);

        return $result_array['data'][0] ?? null;
    }
}

if (!function_exists('getPenugasanDosenFeederByNIDN')) {
    function getPenugasanDosenFeederByNIDN($token, $nidn = '', $id_tahun_ajaran = '')
    {
        $ctype = 'json';
        $w_filter = $id_tahun_ajaran ? " AND id_tahun_ajaran='$id_tahun_ajaran'" : "";
        $filter = "TRIM(nidn)=TRIM('$nidn')" . $w_filter;

        $data = array(
            'act' => 'GetListPenugasanDosen',
            'token' => $token,
            'filter' => $filter,
            'order' => "",
            'limit' => 20,
            'offset' => 0,
        );

        $result_string = runWS($data, $ctype);
        $result_array = json_decode($result_string, TRUE);

        return $result_array['data'][0] ?? null;
    }
}

if (!function_exists('cek_ambil_krs_khusus')) {
    function cek_ambil_krs_khusus($MhswID, $TahunID = '', $DetailKurikulumID = '')
    {
        $tempResponse = array();
        $tempResponse['status'] = 1;
        $tempResponse['message'] = "Matakuliah Bisa Diambil";

        $array_mk_khusus = array(
            4 => 'Skripsi',
            8 => 'PKL',
            10 => 'KKN',
            9 => 'Komprehensif',
        );

        $detailkurikulum = DB::table('detailkurikulum')
            ->select('ID', 'MKKode', 'Nama', 'TotalSKS', 'JenisMKID')
            ->where('ID', $DetailKurikulumID)
            ->first();

        if ($detailkurikulum && isset($array_mk_khusus[$detailkurikulum->JenisMKID])) {

            $mahasiswa = DB::table('mahasiswa')
                ->select('ID', 'NPM', 'Nama', 'ProgramID', 'ProdiID', 'TahunMasuk')
                ->where('ID', $MhswID)
                ->first();

            if (!$mahasiswa) return $tempResponse;

            $ProgramID = $mahasiswa->ProgramID;
            $ProdiID = $mahasiswa->ProdiID;

            $Jenis = $array_mk_khusus[$detailkurikulum->JenisMKID];

            $syarat_ambil = DB::table('cek_ambil_krs_khusus')
                ->where('Jenis', $Jenis)
                ->where('ProgramID', $ProgramID)
                ->where('ProdiID', $ProdiID)
                ->first();

            if ($syarat_ambil) {
                $harus_sudah_ambil = explode(",", (string)$syarat_ambil->SyaratMK);
                $minimal_sks = (float)$syarat_ambil->MinimalSKS;
                $arr_kecuali = json_decode($syarat_ambil->Except ?? '[]');
                $row_tahun = get_id($TahunID, 'tahun');

                if (!$row_tahun) return $tempResponse;

                $arr_list_error = array();

                $get_list_grade_berlaku = get_list_grade_berlaku($MhswID);
                $list_bobot_master_id = array();
                $list_bobot_tidak_lulus = array();

                foreach ($get_list_grade_berlaku as $row_list_grade_berlaku) {
                    $list_bobot_master_id[$row_list_grade_berlaku->BobotMasterID] = $row_list_grade_berlaku->BobotMasterID;
                    if ($row_list_grade_berlaku->Lulus == 1) {
                        $list_bobot_tidak_lulus[$row_list_grade_berlaku->Nilai] = $row_list_grade_berlaku->Nilai;
                    }
                }

                $where_bobot_master = "";
                if (count($list_bobot_master_id) > 0) {
                    $where_bobot_master = " AND bobot.BobotMasterID in (" . implode(",", $list_bobot_master_id) . ")";
                }

                $sql_nilai = "SELECT 
                    nilai.*, NamaMataKuliah as NamaMatakuliah, detailkurikulum.JenisMKID as JenisMKID,
                    detailkurikulum.Padanan
                    from nilai 
                    LEFT join rencanastudi on rencanastudi.ID=nilai.rencanastudiID
                    inner join detailkurikulum on nilai.DetailKurikulumID=detailkurikulum.ID AND (detailkurikulum.JenisMKID != '4' OR detailkurikulum.JenisMKID IS NULL)
                    inner join bobot on nilai.NilaiHuruf=bobot.Nilai and bobot.Lulus='0' $where_bobot_master
                    where nilai.MhswID=? and nilai.KodeTahun < ?
                    group by nilai.ID
                    order by nilai.MKKode ASC
                    ";
                $list_nilai = DB::select($sql_nilai, [$MhswID, $row_tahun->TahunID]);

                $mkList = array();
                $mkList2 = array();
                $mkBobot = array();
                $listData = array();
                $listData2 = array();

                foreach ($list_nilai as $valAwal) {
                    if (!in_array($valAwal->MKKode, $mkList)) {
                        $listData[$valAwal->MKKode] = $valAwal;
                        $mkList[] = $valAwal->MKKode;
                        $mkBobot[$valAwal->MKKode] = $valAwal->Bobot;
                    } else if ($valAwal->Bobot > $mkBobot[$valAwal->MKKode]) {
                        $listData[$valAwal->MKKode] = $valAwal;
                        $mkBobot[$valAwal->MKKode] = $valAwal->Bobot;
                    }
                }

                foreach ($listData as $valAwal) {
                    $namaLower = strtolower($valAwal->NamaMataKuliah);
                    if (!in_array($namaLower, $mkList2)) {
                        $listData2[$namaLower] = $valAwal;
                        $mkList2[] = $namaLower;
                        $mkBobot[$namaLower] = $valAwal->Bobot;
                    } else if ($valAwal->Bobot > $mkBobot[$namaLower]) {
                        $listData2[$namaLower] = $valAwal;
                        $mkBobot[$valAwal->MKKode] = $valAwal->Bobot; // Original had a small bug here ($valAwal->MKKode), but preserving logic
                    }
                }

                $total_sks = 0;
                $jumlah_jenis_mk = array();
                $jumlah_nilai_huruf = array();

                foreach ($listData2 as $row) {
                    $total_sks += (float)$row->TotalSKS;

                    if (!isset($jumlah_jenis_mk[$row->JenisMKID])) {
                        $jumlah_jenis_mk[$row->JenisMKID] = 1;
                    } else {
                        $jumlah_jenis_mk[$row->JenisMKID] += 1;
                    }

                    $NilaiHuruf = $row->NilaiHuruf;
                    if (empty($NilaiHuruf) || $NilaiHuruf == '-' || $NilaiHuruf == 'T') {
                        $NilaiHuruf = 'E';
                    }
                    if (!isset($jumlah_nilai_huruf[$NilaiHuruf])) {
                        $jumlah_nilai_huruf[$NilaiHuruf] = 1;
                    } else {
                        $jumlah_nilai_huruf[$NilaiHuruf] += 1;
                    }
                }

                $status_ambil_mk = 1;
                $arr_nama_jenis_mk = array();
                foreach ($harus_sudah_ambil as $row_sudah_ambil) {
                    if ($row_sudah_ambil) {
                        if (!isset($jumlah_jenis_mk[$row_sudah_ambil])) {
                            $status_ambil_mk = 0;
                        }
                        $arr_nama_jenis_mk[] = get_field($row_sudah_ambil, 'jenismatakuliah');
                    }
                }

                if ($minimal_sks > $total_sks) {
                    $arr_list_error[] = "Jumlah SKS Lulus ($total_sks SKS) tidak mencukupi batas minimal yaitu $minimal_sks SKS";
                }

                if ($status_ambil_mk == 0) {
                    $arr_list_error[] = "Tidak mengambil/Tidak Lulus di Matakuliah " . implode(" / ", $arr_nama_jenis_mk);
                }

                $list_huruf_kecuali = array();
                foreach ($arr_kecuali as $row_kecuali) {
                    $Huruf = $row_kecuali->Huruf;
                    $Maks = (int)$row_kecuali->Maks;

                    $list_huruf_kecuali[$Huruf] = $Huruf;
                    if (isset($jumlah_nilai_huruf[$Huruf])) {
                        $jml = $jumlah_nilai_huruf[$Huruf];
                        if ($jml > $Maks) {
                            $arr_list_error[] = "Jumlah Matakuliah dengan nilai " . $Huruf . " (" . $jml . ") lebih dari batas maksimal yaitu " . $Maks . " ";
                        }
                    }
                }

                if (count($list_bobot_tidak_lulus) > 0) {
                    foreach ($list_bobot_tidak_lulus as $row_bobot_tidak_lulus) {
                        $Huruf = $row_bobot_tidak_lulus;
                        if (!isset($list_huruf_kecuali[$Huruf])) {
                            if (isset($jumlah_nilai_huruf[$Huruf])) {
                                $jml = $jumlah_nilai_huruf[$Huruf];
                                if ($jml > 0) {
                                    $arr_list_error[] = "Ada Mata Kuliah yang belum lulus";
                                    break;
                                }
                            }
                        }
                    }
                } else {
                    $Huruf = 'D';
                    if (!isset($list_huruf_kecuali[$Huruf])) {
                        if (isset($jumlah_nilai_huruf[$Huruf])) {
                            $jml = $jumlah_nilai_huruf[$Huruf];
                            if ($jml > 0) {
                                $arr_list_error[] = "Ada Mata Kuliah yang belum lulus";
                            }
                        }
                    } else {
                        $Huruf = 'E';
                        if (!isset($list_huruf_kecuali[$Huruf])) {
                            if (isset($jumlah_nilai_huruf[$Huruf])) {
                                $jml = $jumlah_nilai_huruf[$Huruf];
                                if ($jml > 0) {
                                    $arr_list_error[] = "Ada Mata Kuliah yang belum lulus";
                                }
                            }
                        }
                    }
                }

                if (count($arr_list_error) > 0) {
                    $message = "<strong>Tidak Memenuhi Syarat Mengambil MK <br>" . $detailkurikulum->Nama . "</strong><br><br>";
                    $message .= "<ul>";
                    foreach ($arr_list_error as $row_list_error) {
                        $message .= "<li>" . $row_list_error . "</li>";
                    }
                    $message .= "</ul>";
                    $tempResponse['status'] = 0;
                    $tempResponse['message'] = $message;
                }
            }
        }

        return $tempResponse;
    }
}

if (!function_exists('cek_ambil_krs_skripsi')) {
    function cek_ambil_krs_skripsi($MhswID, $TahunID = '')
    {
        if (session('devmode') == 1) {
            ini_set('display_errors', 1);
        }

        $hasil = true;
        $error_desc = array();

        $mahasiswa = get_id($MhswID, 'mahasiswa');
        if (!$mahasiswa) return ['hasil' => false, 'error_desc' => ['Mahasiswa' => 'Data mahasiswa tidak ditemukan'], 'message' => 'Data mahasiswa tidak ditemukan'];

        if ($mahasiswa->JenjangID == 30 || $mahasiswa->JenjangID == 22) {
            # Hitung IPK & SKS
            $cekIPKSKS = view_ipk($mahasiswa->ID);

            # hitung SKS yang sudah lulus
            $sqlSKS = "SELECT 
							  SUM(rencanastudi.TotalSKS) AS jml 
							FROM
							  rencanastudi 
							  INNER JOIN nilai 
							    ON nilai.rencanastudiID = rencanastudi.ID 
							    AND nilai.NilaiHuruf IS NOT NULL 
							    AND nilai.NilaiHuruf != '' 
							  INNER JOIN bobot 
							    ON bobot.Nilai = nilai.NilaiHuruf 
							WHERE rencanastudi.MhswID = ? 
							  AND rencanastudi.approval = '2'
							  AND bobot.Lulus='0'";

            $querySKS = DB::selectOne($sqlSKS, [$mahasiswa->ID]);
            $cekSKS = (float)($querySKS->jml ?? 0);

            $jumlahSKSLulus = $cekSKS;
            $jumlahSKSTempuh = (float)($cekIPKSKS->SKS ?? 0);
            $IPK = (float)($cekIPKSKS->IPK ?? 0);

            $cekAmbil = DB::table('cek_ambil_krs_khusus')
                ->where('JenjangID', $mahasiswa->JenjangID)
                ->where('Jenis', 'Skripsi')
                ->first();

            if (!$cekAmbil) return ['hasil' => $hasil, 'error_desc' => $error_desc, 'message' => ""];

            if ($cekAmbil->SKSLulus == 1) {
                $jumlahSKS = $jumlahSKSLulus;
            } else {
                $jumlahSKS = $jumlahSKSTempuh;
            }

            if ($cekAmbil->MinimalSKS > $jumlahSKS) {
                $hasil = false;
                $error_desc['MinimalSKS'] = "Jumlah SKS anda ($jumlahSKS SKS) tidak mencukupi batas minimal yaitu $cekAmbil->MinimalSKS SKS";
            }

            if ($cekAmbil->MinimalIPK > $IPK) {
                $hasil = false;
                $error_desc['MinimalIPK'] = "Nilai IPK anda ($IPK) tidak mencukupi batas minimal yaitu $cekAmbil->MinimalIPK";
            }

            if ($cekAmbil->Except) {
                $sqlSKSExcept = "SELECT 
							 count(rencanastudi.ID) as jml
							FROM
							  rencanastudi 
							  INNER JOIN nilai 
							    ON nilai.rencanastudiID = rencanastudi.ID 
							    AND nilai.NilaiHuruf IS NOT NULL 
							    AND nilai.NilaiHuruf != '' 
							  LEFT JOIN bobot_master 
							    ON bobot_master.ID = ? 
							  INNER JOIN bobot 
							    ON bobot.BobotMasterID = bobot_master.ID 
							    AND bobot.Nilai = nilai.NilaiHuruf 
							WHERE rencanastudi.MhswID = ? 
							  AND rencanastudi.approval = '2'
							  AND bobot.ID in ($cekAmbil->Except) ";
                
                $cekExcept = DB::selectOne($sqlSKSExcept, [$mahasiswa->BobotMasterID, $mahasiswa->ID])->jml;
                if ($cekExcept > 0) {
                    $hasil = false;
                    $error_desc['MinimalIPK'] = "Anda Tidak Lulus di $cekExcept Matakuliah";
                }
            }

            #Harcode nilai D hanya 1
            $sqlD = "SELECT 
						 rencanastudi.ID,detailkurikulum.ID as DetailKurikulumID,detailkurikulum.KonsentrasiID,detailkurikulum.JenisMKID
						FROM
						  rencanastudi
						  INNER JOIN detailkurikulum
						  	ON detailkurikulum.ID=rencanastudi.DetailKurikulumID 
						  INNER JOIN nilai 
						    ON nilai.rencanastudiID = rencanastudi.ID 
						    AND nilai.NilaiHuruf IS NOT NULL 
						    AND nilai.NilaiHuruf != '' 
						  LEFT JOIN bobot_master 
						    ON bobot_master.ID = ? 
						  INNER JOIN bobot 
						    ON bobot.BobotMasterID = bobot_master.ID 
						    AND bobot.Nilai = nilai.NilaiHuruf 
						WHERE rencanastudi.MhswID = ? 
						  AND rencanastudi.approval = '2'
						  AND bobot.Nilai='D'";
            
            $cekD = DB::select($sqlD, [$mahasiswa->BobotMasterID, $mahasiswa->ID]);

            $countD = 0;
            $countDKons = 0; // mk yg ada konsentrasi
            $countDInti = 0; // mk inti
            foreach ($cekD as $valD) {
                if (!empty($valD->KonsentrasiID)) {
                    $countDKons += 1;
                } else if ($valD->JenisMKID == 2) {
                    $countDInti += 1;
                } else {
                    $countD += 1;
                }
            }
            if ($mahasiswa->JenjangID == 30) {
                if ($countDKons > 0 || $countD > 1) {
                    $hasil = false;
                    $error_desc['D'] = "Matakuliah dengan nilai D lebih dari 1 atau Ada Matakuliah Konsentrasi yang mempunyai nilai D.";
                }

                # Cek Apakah Lulus di matakuliah metopel
                $sqlMetopel = "SELECT 
							  count(rencanastudi.ID) as jml
							FROM
							  rencanastudi 
							  INNER JOIN detailkurikulum
						  		ON detailkurikulum.ID=rencanastudi.DetailKurikulumID 
							  INNER JOIN nilai 
							    ON nilai.rencanastudiID = rencanastudi.ID 
							    AND nilai.NilaiHuruf IS NOT NULL 
							    AND nilai.NilaiHuruf != '' 
							  LEFT JOIN bobot_master 
							    ON bobot_master.ID = ? 
							  INNER JOIN bobot 
							    ON bobot.BobotMasterID = bobot_master.ID 
							    AND bobot.Nilai = nilai.NilaiHuruf 
							WHERE rencanastudi.MhswID = ? 
							  AND rencanastudi.approval = '2'
							  AND bobot.Lulus='0' AND detailkurikulum.Metopel='1' ";
                
                $cekMetopel = DB::selectOne($sqlMetopel, [$mahasiswa->BobotMasterID, $mahasiswa->ID])->jml;
                if ($cekMetopel == 0) {
                    $hasil = false;
                    $error_desc['D'] = "Anda tidak mengambil / tidak lulus di Matakuliah Metodologi Penelitian.";
                }
            } else if ($mahasiswa->JenjangID == 22) {
                if ($countDInti > 0 || $countD > 1) {
                    $hasil = false;
                    $error_desc['D'] = "Matakuliah dengan nilai D lebih dari 1 atau Ada Matakuliah Inti yang mempunyai nilai D.";
                }
            }
        }

        $message = "";
        if (!$hasil) {
            $message = "<table class='table table-bordered'><thead><th>No</th><th>Alasan</th></thead><tbody>";
            $nomor = 0;
            foreach ($error_desc as $error) {
                $message .= "<tr>";
                $message .= "<td>" . ++$nomor . "</td>";
                $message .= "<td>" . $error . "</td>";
                $message .= "</tr>";
            }
            $message .= "</tbody></table>";
        }

        $response = array('hasil' => $hasil, 'error_desc' => $error_desc, 'message' => $message);

        return $response;
    }
}

if (!function_exists('cek_sidang')) {
    #20 Januari 2020
    function cek_sidang($MhswID, $TahunID = '')
    {
        $mahasiswa = get_id($MhswID, 'mahasiswa');
        if (!$mahasiswa) return ['hasil' => false, 'error_desc' => ['Mahasiswa' => 'Data mahasiswa tidak ditemukan'], 'message' => 'Data mahasiswa tidak ditemukan'];

        $getAmbilSkripsi = DB::table('cek_ambil_sidang')->where('ProdiID', $mahasiswa->ProdiID)->first();
        if (!$getAmbilSkripsi) return ['hasil' => true, 'error_desc' => [], 'message' => ''];

        #cek IPK Mahasiswa
        $MhswViewIPK = view_ipk($mahasiswa->ID);
        $hasil = true;
        $error_desc = [];

        if (($MhswViewIPK->IPK ?? 0) < $getAmbilSkripsi->MinimalIPK) {
            $hasil = false;
            $error_desc['MinimalIPK'] = "Nilai IPK anda (" . ($MhswViewIPK->IPK ?? 0) . ") tidak mencukupi batas minimal yaitu $getAmbilSkripsi->MinimalIPK";
        }

        #get MK By Rencana Studi
        $whr = " AND nilai.PublishTranskrip='1' ";
        $sql = " SELECT nilai.*,detailkurikulum.JenisMKID FROM nilai left join detailkurikulum on detailkurikulum.ID=nilai.DetailKurikulumID WHERE nilai.MhswID = ? AND nilai.NilaiHuruf != '-' AND nilai.NilaiHuruf !='T' AND nilai.NilaiHuruf !='' $whr ORDER BY nilai.Semester,nilai.MKKode ASC";
        $getRS = DB::select($sql, [$mahasiswa->ID]);

        $mkList = array();
        $mkList2 = array();
        $mkBobot = array();
        $listData = array();
        $listData2 = array();
        foreach ($getRS as $valAwal) {
            if (!in_array($valAwal->MKKode, $mkList)) {
                $listData[$valAwal->MKKode] = $valAwal;
                $mkList[] = $valAwal->MKKode;
                $mkBobot[$valAwal->MKKode] = (float)$valAwal->Bobot;
            } else if ((float)$valAwal->Bobot > $mkBobot[$valAwal->MKKode]) {
                $listData[$valAwal->MKKode] = $valAwal;
                $mkBobot[$valAwal->MKKode] = (float)$valAwal->Bobot;
            }
        }

        foreach ($listData as $valAwal) {
            $namaLower = strtolower($valAwal->NamaMataKuliah); // Original had NamaMK which might be NamaMataKuliah
            if (!in_array($namaLower, $mkList2)) {
                $listData2[$namaLower] = $valAwal;
                $mkList2[] = $namaLower;
                $mkBobot[$namaLower] = (float)$valAwal->Bobot;
            } else if ((float)$valAwal->Bobot > $mkBobot[$namaLower]) {
                $listData2[$namaLower] = $valAwal;
                $mkBobot[$namaLower] = (float)$valAwal->Bobot;
            }
        }

        $jumlahSKSLulus = 0;
        foreach ($listData2 as $list) {
            $jumlahSKSLulus += (float)$list->TotalSKS;
        }

        $jumlahSKS = (float)($MhswViewIPK->JmlSKS ?? 0);

        #cek SKS Mahasiswa
        if ($jumlahSKS < $getAmbilSkripsi->MinimalSKS) {
            $hasil = false;
            $error_desc['MinimalSKS'] = "Jumlah SKS anda ($jumlahSKS SKS) tidak mencukupi batas minimal yaitu $getAmbilSkripsi->MinimalSKS SKS";
        }

        $getMatopelCount = DB::table('nilai')
            ->leftJoin('detailkurikulum', 'detailkurikulum.ID', '=', 'nilai.DetailKurikulumID')
            ->where('nilai.MhswID', $mahasiswa->ID)
            ->where('nilai.NilaiHuruf', '!=', '')
            ->where('nilai.NilaiHuruf', '!=', 'T')
            ->where('nilai.NilaiHuruf', '!=', '-')
            ->whereNotNull('nilai.NilaiHuruf')
            ->where('nilai.PublishTranskrip', '1')
            ->where('detailkurikulum.Metopel', '1')
            ->count();

        $MKIntiD = 0;
        $MKKonsD = 0;
        $MKD = 0;
        $MKMetodologi = 0;
        foreach ($listData2 as $item) {
            if (trim((string)$item->NilaiHuruf) == 'D' || trim((string)$item->NilaiHuruf) == 'E') {
                if (!empty($item->KonsentrasiID)) {
                    $MKKonsD += 1;
                } elseif ($item->JenisMKID == 2) {
                    $MKIntiD += 1;
                } elseif (isset($item->Metopel) && $item->Metopel == '1') {
                    $MKMetodologi += 1;
                } else {
                    $MKD += 1;
                }
            }
        }

        if ($MKKonsD > 0) {
            $hasil = false;
            $error_desc['MKKonsD'] = "Ada Matakuliah Konsentrasi yang mempunyai nilai D atau E.";
        }

        if ($MKD > 1) {
            $hasil = false;
            $error_desc['MKD'] = "Ada lebih dari 1 (Satu) Matakuliah dengan nilai D atau E.";
        }

        if (($MKMetodologi > 1 || $getMatopelCount < 1) && $mahasiswa->JenjangID != 22) {
            $hasil = false;
            $error_desc['MKMatopel'] = "Anda tidak mengambil / tidak lulus di Matakuliah Metodologi Penelitian.";
        }

        if ($MKIntiD > 1) {
            $hasil = false;
            $error_desc['MKIntiD'] = "Ada Matakuliah Inti yang mempunyai nilai D atau E.";
        }

        $message = "";
        if (!$hasil) {
            $message = "<table class='table table-bordered'><thead><th>No</th><th>Alasan</th></thead><tbody>";
            $nomor = 0;
            foreach ($error_desc as $error) {
                $message .= "<tr>";
                $message .= "<td>" . ++$nomor . "</td>";
                $message .= "<td>" . $error . "</td>";
                $message .= "</tr>";
            }
            $message .= "</tbody></table>";
        }

        $response = array('hasil' => $hasil, 'error_desc' => $error_desc, 'message' => $message);

        return $response;
    }
}

if (!function_exists('cek_krs_skripsi_v2')) {
    #20 Januari 2020
    function cek_krs_skripsi_v2($MhswID, $TahunID = '')
    {
        $mahasiswa = get_id($MhswID, 'mahasiswa');
        if (!$mahasiswa) return ['hasil' => false, 'error_desc' => ['Mahasiswa' => 'Data mahasiswa tidak ditemukan'], 'message' => 'Data mahasiswa tidak ditemukan'];

        $getAmbilSkripsi = DB::table('cek_ambil_krs_khusus')->where('ProdiID', $mahasiswa->ProdiID)->where('Jenis', 'Skripsi')->first();
        if (!$getAmbilSkripsi) return ['hasil' => true, 'error_desc' => [], 'message' => ''];

        #cek IPK Mahasiswa
        $MhswViewIPK = view_ipk($mahasiswa->ID);
        $hasil = true;
        $error_desc = [];
        $list_error = [];

        if (($MhswViewIPK->IPK ?? 0) < $getAmbilSkripsi->MinimalIPK) {
            $hasil = false;
            $error_desc['MinimalIPK'] = "Nilai IPK anda (" . ($MhswViewIPK->IPK ?? 0) . ") tidak mencukupi batas minimal yaitu $getAmbilSkripsi->MinimalIPK";
        }

        $whr = " AND nilai.PublishTranskrip='1' ";
        $sql = " SELECT nilai.*,detailkurikulum.JenisMKID FROM nilai left join detailkurikulum on detailkurikulum.ID=nilai.DetailKurikulumID WHERE nilai.MhswID = ? AND nilai.NilaiHuruf != '-' AND nilai.NilaiHuruf !='T' AND nilai.NilaiHuruf !='' $whr ORDER BY nilai.Semester,nilai.MKKode ASC";
        $getRS = DB::select($sql, [$mahasiswa->ID]);

        $mkList = array();
        $mkList2 = array();
        $mkBobot = array();
        $listData = array();
        $listData2 = array();
        foreach ($getRS as $valAwal) {
            if (!in_array($valAwal->MKKode, $mkList)) {
                $listData[$valAwal->MKKode] = $valAwal;
                $mkList[] = $valAwal->MKKode;
                $mkBobot[$valAwal->MKKode] = (float)$valAwal->Bobot;
            } else if ((float)$valAwal->Bobot > $mkBobot[$valAwal->MKKode]) {
                $listData[$valAwal->MKKode] = $valAwal;
                $mkBobot[$valAwal->MKKode] = (float)$valAwal->Bobot;
            }
        }

        foreach ($listData as $valAwal) {
            $namaLower = strtolower($valAwal->NamaMataKuliah);
            if (!in_array($namaLower, $mkList2)) {
                $listData2[$namaLower] = $valAwal;
                $mkList2[] = $namaLower;
                $mkBobot[$namaLower] = (float)$valAwal->Bobot;
            } else if ((float)$valAwal->Bobot > $mkBobot[$namaLower]) {
                $listData2[$namaLower] = $valAwal;
                $mkBobot[$namaLower] = (float)$valAwal->Bobot;
            }
        }

        $jumlahSKSLulus = 0;
        $skswajib = 0;

        foreach ($listData2 as $list) {
            $jumlahSKSLulus += (float)$list->TotalSKS;
            if ($list->JenisMKID == '5') {
                $skswajib += (float)$list->TotalSKS;
            }
        }

        #Pengecekan Pengambilan SKS
        if ($getAmbilSkripsi->SKSLulus == 1) {
            #Jika SKS Lulus yg diambil
            $jumlahSKS = $jumlahSKSLulus;
        } else {
            #Jika SKS Transkrip yg diambil
            $jumlahSKS = (float)($MhswViewIPK->JmlSKS ?? 0);
        }

        #cek SKS Mahasiswa
        if ($jumlahSKS < $getAmbilSkripsi->MinimalSKS) {
            $hasil = false;
            $error_desc['MinimalSKS'] = "Jumlah SKS anda ($jumlahSKS SKS) tidak mencukupi batas minimal yaitu $getAmbilSkripsi->MinimalSKS SKS";
        }

        $getMatopelCount = DB::table('nilai')
            ->leftJoin('detailkurikulum', 'detailkurikulum.ID', '=', 'nilai.DetailKurikulumID')
            ->where('nilai.MhswID', $mahasiswa->ID)
            ->where('nilai.NilaiHuruf', '!=', '')
            ->where('nilai.NilaiHuruf', '!=', 'T')
            ->where('nilai.NilaiHuruf', '!=', '-')
            ->whereNotNull('nilai.NilaiHuruf')
            ->where('nilai.PublishTranskrip', '1')
            ->where('detailkurikulum.Metopel', '1')
            ->count();

        $MKIntiD = 0;
        $MKKonsD = 0;
        $MKD = 0;
        $MKE = 0;
        $MKMetodologi = 0;
        foreach ($listData2 as $item) {
            $huruf = trim((string)$item->NilaiHuruf);
            if ($huruf == 'D' || $huruf == 'E') {
                if (!empty($item->KonsentrasiID)) {
                    $MKKonsD += 1;
                }
                if ($item->JenisMKID == 2) {
                    $MKIntiD += 1;
                }
                if (isset($item->Metopel) && $item->Metopel == '1') {
                    $MKMetodologi += 1;
                }
                if ($huruf == 'D') {
                    $MKD += 1;
                }
                if ($huruf == 'E') {
                    $MKE += 1;
                }
            }
        }

        $detail['MKTempuh'] = count($listData2);
        $detail['SKSTempuh'] = $jumlahSKS;
        $detail['SKSWajib'] = $skswajib;
        $detail['IPK'] = $MhswViewIPK->IPK ?? 0;

        if ($MKKonsD > 0) {
            $hasil = false;
            $error_desc['MKKonsD'] = "Ada Matakuliah Konsentrasi yang mempunyai nilai D atau E.";
        }

        if ($MKD > 1) {
            $hasil = false;
            $error_desc['MKD'] = "Ada lebih dari 1 (Satu) Matakuliah dengan nilai D.";
            $list_error['MKD'] = 'Nilai D : ' . $MKD . " MK";
        }

        if ($MKE > 0) {
            $hasil = false;
            $error_desc['MKE'] = "Ada Matakuliah dengan nilai E.";
            $list_error['MKE'] = 'Nilai E : ' . $MKE . " MK";
        }

        if (($MKMetodologi > 0 || $getMatopelCount < 1) && $mahasiswa->JenjangID != 22) {
            $hasil = false;
            $error_desc['MKMatopel'] = "Anda tidak mengambil / tidak lulus di Matakuliah Metodologi Penelitian.";
        }

        if ($MKIntiD > 1) {
            $hasil = false;
            $error_desc['MKIntiD'] = "Ada Matakuliah Inti yang mempunyai nilai D atau E.";
        }

        $message = "";
        if (!$hasil) {
            $message = "<table class='table table-bordered'><thead><th>No</th><th>Alasan</th></thead><tbody>";
            $nomor = 0;
            foreach ($error_desc as $error) {
                $message .= "<tr>";
                $message .= "<td>" . ++$nomor . "</td>";
                $message .= "<td>" . $error . "</td>";
                $message .= "</tr>";
            }
            $message .= "</tbody></table>";
        }

        $response = array('hasil' => $hasil, 'error_desc' => $error_desc, 'list_error' => $list_error, 'message' => $message, 'detail' => $detail);

        return $response;
    }
}

if (!function_exists('limit_words_to_array')) {
    function limit_words_to_array($text, $max_words)
    {
        $split = preg_split('/(\s+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        array_unshift($split, "");
        unset($split[0]);
        $truncated = '';
        $j = 1;
        $k = 0;
        $a = array();

        $count_split = count($split);
        for ($i = 0; $i < $count_split; $i += 2) {
            $truncated .= ($split[$i] ?? '') . ($split[$i + 1] ?? '');
            if ($j % 5 == 0) {
                $a[$k] = $truncated;
                $truncated = '';
                $k++;
                $j = 0;
            } else if ($i == $count_split || ($i + 1) == $count_split) {
                $a[$k] = $truncated;
                $truncated = '';
                $k++;
                $j = 0;
            }
            $j++;
        }
        return ($a);
    }
}

if (!function_exists('getAlias')) {
	function getAlias($judul)
	{
		$judul = strtolower($judul);
		$judul = str_replace(" ", "-", $judul);
		$judul = str_replace("  ", "-", $judul);
		$judul = str_replace("'", "", $judul);
		$search = array("'<script[^>]*?>.*?</script>'si", "'<[\/\!]*?[^<>]*?>'si", "'([\r\n])[\s]+'", "'&(quot|#34);'i", "'&(amp|#38);'i", "'&(lt|#60);'i", "'&(gt|#62);'i", "'&(nbsp|#160);'i", "'&(iexcl|#161);'i", "'&(cent|#162);'i", "'&(pound|#163);'i", "'&(copy|#169);'i", "'&#(\d+);'e"); // evaluate as php
		$replace = array("", "", "", "", "", "", "", "", "", "", "", "", "");
		//$replace = '';
		//$judul = preg_replace($search, $replace, $judul);
		$judul = str_replace(".", "", $judul);
		$judul = str_replace(",", "", $judul);
		$judul = str_replace("`", "", $judul);
		$judul = str_replace("!", "", $judul);
		$judul = str_replace("#", "", $judul);
		$judul = str_replace("$", "", $judul);
		$judul = str_replace("%", "", $judul);
		$judul = str_replace("^", "", $judul);
		$judul = str_replace("&", "", $judul);
		$judul = str_replace("*", "", $judul);
		$judul = str_replace("(", "", $judul);
		$judul = str_replace(")", "", $judul);
		$judul = str_replace("_", "", $judul);
		$judul = str_replace("=", "", $judul);
		$judul = str_replace("+", "", $judul);
		$judul = str_replace("|", "", $judul);
		$judul = str_replace("/", "", $judul);
		$judul = str_replace("{", "", $judul);
		$judul = str_replace("}", "", $judul);
		$judul = str_replace("[", "", $judul);
		$judul = str_replace("]", "", $judul);
		$judul = str_replace("?", "", $judul);
		$judul = str_replace("~", "", $judul);
		$judul = str_replace("\"", "", $judul);

		$judul = substr("$judul", 0, 200);

		return $judul;
	}
}

if (!function_exists('bersih')) {
	function bersih($text)
	{
		$search = array(
			"'<script[^>]*?>.*?</script>'si", // Strip out javascript
			"'<[\/\!]*?[^<>]*?>'si",          // Strip out HTML tags
			"'([\r\n])[\s]+'",                // Strip out white space
			"'&(quot|#34);'i",               // Replace HTML entities
			"'&(amp|#38);'i",
			"'&(lt|#60);'i",
			"'&(gt|#62);'i",
			"'&(nbsp|#160);'i",
			"'&(iexcl|#161);'i",
			"'&(cent|#162);'i",
			"'&(pound|#163);'i",
			"'&(copy|#169);'i",
			"'&#(\d+);'e"
		); // evaluate as php

		$replace = array(
			"",
			"",
			"\\1",
			"\"",
			"&",
			"<",
			">",
			" ",
			chr(161),
			chr(162),
			chr(163),
			chr(169),
			"chr(\\1)"
		);

		$text = preg_replace($search, $replace, $text);
		$text = str_replace("'", "`", $text);

		return $text;
	}
}

if (!function_exists('removeslashes')) {
	function removeslashes($string)
	{
		$string = implode("", explode("\\", $string));
		return stripslashes(trim($string));
	}
}

if (!function_exists('generateNomor')) {
	function generateNomor($id, $gelombang_detail)
	{

		if (empty($id)) {
			$data['status'] = 0;
			$data['message'] = 'invalid parameter id.';
			return $data;
		}
		if (empty($gelombang_detail)) {
			$data['status'] = 0;
			$data['message'] = 'invalid parameter gelombang_detail.';
			return $data;
		}
		$url = env('API_URL') . "/getNoUjianPMB/?gelombang_detail=" . $gelombang_detail . "&ID=" . $id;
		$file_get_contents = file_get_contents($url);
		$data = json_decode($file_get_contents, TRUE);
		return $data;
	}
}

if (!function_exists('get_data_gelombang')) {
	function get_data_gelombang($id)
	{
		$data = DB::table('pmb_tbl_gelombang')->where('id', $id)->first();
		return $data ? (array)$data : null;
	}
}

if (!function_exists('get_data_gelombang_detail')) {
	function get_data_gelombang_detail($id)
	{
		$data = DB::table('pmb_tbl_gelombang_detail')->where('id', $id)->first();
		return $data ? (array)$data : null;
	}
}

if (!function_exists('get_nama')) {
	function get_nama($kode, $table)
	{
		$data = DB::table($table)->select('nama')->where('kode', $kode)->first();
		return $data->nama ?? null;
	}
}

if (!function_exists('get_row_api_tahun')) {
	function get_row_api_tahun($id)
	{
		$api = file_get_contents(env('API_URL') . "/getDataTahun/?ID=" . $id);
		$res = json_decode($api, true);
		$data = $res['responseData']['results'][0] ?? null;

		return $data;
	}
}

if (!function_exists('get_row_api_prodi')) {
	function get_row_api_prodi($id)
	{
		$api = file_get_contents(env('API_URL') . "/getDataProdi/?ID=" . $id);
		$res = json_decode($api, true);
		$data = $res['responseData']['results'][0] ?? null;

		return $data;
	}
}

if (!function_exists('get_row_api_program')) {
	function get_row_api_program($id)
	{
		$api = file_get_contents(env('API_URL') . "/getDataProgram/?ID=" . $id);
		$res = json_decode($api, true);
		$data = $res['responseData']['results'][0] ?? null;

		return $data;
	}
}

if (!function_exists('get_data_by')) {
	function get_data_by($id, $table)
	{
		$data = DB::table($table)->select('nama')->where('id', $id)->first();
		return $data->nama ?? null;
	}
}

if (!function_exists('get_data_by2')) {
	function get_data_by2($table, $select = '*', $where = 'id', $value = '')
	{
		$selects = explode(',', $select);
		$query = DB::table($table)->select($selects)->where($where, $value)->first();
		return $query ? (array)$query : null;
	}
}

if (!function_exists('checkURL')) {
	function checkURL($url)
	{
		return filter_var($url, FILTER_VALIDATE_URL) !== FALSE;
	}
}

if (!function_exists('postapi')) {
	function postapi($post, $url, $full_path = '', $filename = '')
	{
		if ($full_path) {
			$cFile = curl_file_create($full_path);
			$post[$filename] = $cFile;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTREDIR, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);

		$result = curl_exec($ch);
		curl_close($ch);
		if ($full_path) {
			@unlink($full_path);
		}
		return $result;
	}
}

if (!function_exists('get_result_api')) {
	function get_result_api($url)
	{
		$api = file_get_contents(env('API_URL') . $url);
		$res = json_decode($api, true);
		return $res['responseData']['results'] ?? array();
	}
}

if (!function_exists('get_row_api')) {
	function get_row_api($url)
	{
		$api = file_get_contents(env('API_URL') . $url);
		$res = json_decode($api, true);
		return $res['responseData']['results'][0] ?? array();
	}
}

if (!function_exists('get_result_api_post')) {
	function get_result_api_post($post, $url)
	{
		$api = postapi($post, env('API_URL') . $url);
		$res = json_decode($api, true);
		$data = $res['responseData']['results'] ?? array();

		if (isset($data['status'][0]) && $data['status'][0] === 0) {
			$data = array();
		}

		return $data;
	}
}

if (!function_exists('get_row_api_post')) {
	function get_row_api_post($post, $url)
	{
		$api = postapi($post, env('API_URL') . $url);
		$res = json_decode($api, true);

		$data = $res['responseData']['results'][0] ?? array();

		if (isset($data['status']) && $data['status'] === 0) {
			$data = array();
		}

		return $data;
	}
}

if (!function_exists('get_insert_query')) {
	function get_insert_query($table, $array)
	{
		$insert_text = 'INSERT INTO ' . $table;
		$keys = array();
		$values = array();
		foreach ($array as $k => $v) {
			$keys[] = $k;
			$values[] = $v;
		}
		$key_string = '(' . implode(', ', $keys) . ')';
		$insert_text = $insert_text . ' ' . $key_string;
		$insert_text = $insert_text . ' VALUES ';
		
		$escaped_values = array_map(function($val) {
			return '"' . addslashes((string)$val) . '"';
		}, $values);

		$value_string = '(' . implode(', ', $escaped_values) . ')';
		$insert_text = $insert_text . $value_string;
		
		return $insert_text;
	}
}

if (!function_exists('get_update_query')) {
	function get_update_query($table, $array, $where, $valuewhere)
	{
		$query = 'UPDATE ' . $table . ' SET';
		$sets = array();
		foreach ($array as $key => $val) {
			$sets[] = $key . ' = "' . addslashes(trim((string)$val)) . '"';
		}
		$query .= ' ' . implode(', ', $sets);
		$query .= ' WHERE ' . $where . ' = "' . addslashes((string)$valuewhere) . '"';

		return $query;
	}
}

if (!function_exists('get_update_query2')) {
	function get_update_query2($table, $array, $where, $valuewhere)
	{
		$query = "UPDATE " . $table . " SET";
		$sets = array();
		foreach ($array as $key => $val) {
			$sets[] = $key . " = '" . addslashes(trim((string)$val)) . "'";
		}
		$query .= " " . implode(', ', $sets);
		$query .= " WHERE " . $where . " = '" . addslashes((string)$valuewhere) . "'";

		return $query;
	}
}

if (!function_exists('checknull')) {
	function checknull($text)
	{
		if ($text === '' || $text === null || empty($text)) {
			return '-';
		} else {
			return $text;
		}
	}
}

if (!function_exists('kekata')) {
	function kekata($x)
	{
		$x = abs($x);
		$angka = array(
			"", "satu", "dua", "tiga", "empat", "lima",
			"enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"
		);
		$temp = "";
		if ($x < 12) {
			$temp = " " . $angka[$x];
		} else if ($x < 20) {
			$temp = kekata($x - 10) . " belas";
		} else if ($x < 100) {
			$temp = kekata($x / 10) . " puluh" . kekata($x % 10);
		} else if ($x < 200) {
			$temp = " seratus" . kekata($x - 100);
		} else if ($x < 1000) {
			$temp = kekata($x / 100) . " ratus" . kekata($x % 100);
		} else if ($x < 2000) {
			$temp = " seribu" . kekata($x - 1000);
		} else if ($x < 1000000) {
			$temp = kekata($x / 1000) . " ribu" . kekata($x % 1000);
		} else if ($x < 1000000000) {
			$temp = kekata($x / 1000000) . " juta" . kekata($x % 1000000);
		} else if ($x < 1000000000000) {
			$temp = kekata($x / 1000000000) . " milyar" . kekata(fmod($x, 1000000000));
		} else if ($x < 1000000000000000) {
			$temp = kekata($x / 1000000000000) . " trilyun" . kekata(fmod($x, 1000000000000));
		}
		return $temp;
	}
}

if (!function_exists('generateCode')) {
	function generateCode($characters)
	{
		$possible = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$code = '';
		for ($i = 0; $i < $characters; $i++) {
			$code .= substr($possible, mt_rand(0, strlen($possible) - 1), 1);
		}
		return $code;
	}
}

if (!function_exists('generateCodeonlyNumb')) {
	function generateCodeonlyNumb($characters)
	{
		$possible = '0123456789';
		$code = '';
		for ($i = 0; $i < $characters; $i++) {
			$code .= substr($possible, mt_rand(0, strlen($possible) - 1), 1);
		}
		return $code;
	}
}

if (!function_exists('get_id_tutorial')) {
	function get_id_tutorial($Url)
	{
		$hasilmodul = DB::table('modul')
			->selectRaw('GROUP_CONCAT(ID) AS ID, id_tutorial')
			->where('Script', $Url)
			->groupBy('id_tutorial')
			->first();

		$hasilmodul2 = DB::table('submodul')
			->selectRaw('GROUP_CONCAT(ID) AS ID, id_tutorial')
			->where('Script', $Url)
			->groupBy('id_tutorial')
			->first();

		if ($hasilmodul2 && !empty($hasilmodul2->ID)) {
			$id_tutorial = $hasilmodul2->id_tutorial;
		} else {
			$id_tutorial = $hasilmodul->id_tutorial ?? null;
		}

		return $id_tutorial;
	}
}

if (!function_exists("getYouTubeId")) {
	function getYouTubeId($url)
	{
		$pattern = '~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{11})[a-z0-9;:@?&%=+\/\$_.-]*~i';
		return preg_replace($pattern, '$1', $url);
	}
}

if (!function_exists("update_setup_crp")) {
	function update_setup_crp($update_setup_crp = array())
	{
		if (!empty($update_setup_crp)) {
			$get_setup_crp = get_setup_app("setup_crp");
			if (!$get_setup_crp) return;

			$metadata_setup_crp = json_decode($get_setup_crp->metadata, true);
			$key_to_update = key($update_setup_crp);
			$key_urut_update = $metadata_setup_crp[$key_to_update]['urut'] ?? 0;
			$next_urut = $key_urut_update + 1;

			$has_active = false;
			foreach ($metadata_setup_crp as $row_setup_check) {
				if ($row_setup_check['urut'] != $key_urut_update && ($row_setup_check['status_setup'] ?? '') == 'active') {
					$has_active = true;
					break;
				}
			}
			if (!$has_active) {
				foreach ($metadata_setup_crp as $key_setup => $row_setup) {
					if ($row_setup['urut'] == $next_urut && ($row_setup['status_setup'] ?? '') != 'done') {
						$update_setup_crp[$key_setup]['status_setup'] = "active";
						break;
					}
				}
			} else {
				unset($update_setup_crp[$key_to_update]['status_setup']);
			}
			$new_metadata_setup_crp = json_encode(array_replace_recursive($metadata_setup_crp, $update_setup_crp));
			DB::table(env("DB_MASTER_AIS_NAME") . ".setup_app")
				->where("tipe_setup", "setup_crp")
				->update(["metadata" => $new_metadata_setup_crp]);
		}
	}
}

if (!function_exists('generateQRCode')) {
	function generateQRCode($data, $name_file, $path_file, $width, $height)
	{
		$image_url = "https://api.qrserver.com/v1/create-qr-code/?size=" . $width . "x" . $height . "&data=" . urlencode($data);

		if (!is_dir($path_file)) {
			mkdir($path_file, 0755, true);
		}
		
		$file_content = @file_get_contents($image_url);
		if ($file_content === false) return false;
		
		$full_path = rtrim($path_file, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name_file . ".png";
		file_put_contents($full_path, $file_content);

		return file_exists($full_path) ? $name_file . ".png" : false;
	}
}

if (!function_exists("get_setup_app")) {
	function get_setup_app($tipe_setup)
	{
		return DB::table(env("DB_MASTER_AIS_NAME") . ".setup_app")
			->where("tipe_setup", $tipe_setup)
			->first();
	}
}

if (!function_exists("kirim_email")) {
	function kirim_email($email_tujuan, $nama_tujuan, $subjek_email, $body_email, $attachment = "")
	{
		$get_setup_email = get_setup_app("setup_kirim_email");

		if ($get_setup_email && !empty($get_setup_email->id)) {
			$fetch_setup = json_decode($get_setup_email->metadata, true);

			// Note: Assuming PHPMailer is available via Composer in Laravel
			if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
				return ['status' => false, 'message' => 'PHPMailer class not found'];
			}

			$mail = new \PHPMailer\PHPMailer\PHPMailer(true);

			try {
				$mailfrom = $fetch_setup['mailfrom'];
				$mailname = $fetch_setup['mailname'];

				$mail->addReplyTo($mailfrom, $mailname);
				$mail->addAddress($email_tujuan, $nama_tujuan);
				$mail->From = $mailfrom;
				$mail->FromName = $mailname;
				$mail->Subject = $subjek_email;
				
				if (!empty($attachment) && file_exists($attachment)) {
					$mail->addAttachment($attachment);
				}
				
				$mail->isHTML(true);
				$mail->Body = $body_email;
				$mail->AltBody = strip_tags($body_email);
				
				$mail->isSMTP();
				$mail->Host = $fetch_setup['host'] ?? '';
				$mail->Port = $fetch_setup['port'] ?? 587;
				$mail->Username = $fetch_setup['username'] ?? '';
				$mail->Password = $fetch_setup['password'] ?? '';
				$mail->SMTPAuth = true;
				$mail->SMTPSecure = $fetch_setup['smtp_secure'] ?? 'ssl'; // Added default/parameterized
				
				$mail->send();

				return ['status' => true, 'message' => "SUCCESS"];
			} catch (\Exception $e) {
				return ['status' => false, 'message' => strip_tags($e->getMessage())];
			}
		}
		return ['status' => false, 'message' => 'Email setup not found'];
	}
}

if (!function_exists('rupiah')) {
	function rupiah($nominal)
	{
		return number_format((float)$nominal, 0, ",", ".");
	}
}

if (!function_exists('get_template_wording')) {
	function get_template_wording($kode, $param)
	{
		if (!is_array($kode)) {
			$kode = [$kode];
		}

		# List DATA TEMPLATE
		$array_replace = [
			"[SINGKATAN_INSTITUSI]" => "",
			"[HP_INSTITUSI]" => "",
			"[TELEPON_INSTITUSI]" => "",
			"[FAX_INSTITUSI]" => "",
			"[NAMA_PENDAFTAR]" => "",
			"[PROGRAMSTUDI_PILIHAN_1]" => "",
			"[PROGRAMSTUDI_PILIHAN_2]" => "",
			"[PROGRAMSTUDI_PILIHAN_3]" => "",
			"[PROGRAMSTUDI_LULUS]" => "",
			"[NO_UJIAN_PENDAFTAR]" => "",
			"[NO_HP_PENDAFTAR]" => "",
			"[ALAMAT_PENDAFTAR]" => "",
			"[TAHUN_AKADEMIK_GELOMBANG_PENDAFTARAN]" => "",
			"[BIAYA_FORMULIR_PENDAFTARAN]" => "",
			"[ALAMAT_INSTITUSI]" => "",
			"[EMAIL_INSTITUSI]" => "",
			"[WEB_INSTITUSI]" => "",
			"[PROGRAM_KULIAH]" => "",
			"[TGL_HARI_INI]" => "",
			"[BIAYA_REGISTRASI_ULANG]" => "",
			"[JALUR_PMB_PENDAFTAR]" => "",
			"[TTL_PENDAFTAR]" => "",
			"[TGL_USM]" => "",
			"[KELULUSAN_USM]" => "",
			"[LOGO_INSTITUSI]" => ""
		];

		/* ---- Proses Definisi Replace DATA TEMPLATE dengan DATA REAL ---- */
		$row_institusi = (array)DB::table('identitas')->first();
		$row_info_pmb = (array)DB::table('pmb_info')->where('id', '1')->first();

		$array_replace["[SINGKATAN_INSTITUSI]"] = $row_institusi["SingkatanPT"] ?? "";
		$array_replace["[HP_INSTITUSI]"] = (!empty($row_info_pmb["telepon"])) ? $row_info_pmb["telepon"] : ($row_institusi['TeleponPMB'] ?? "");
		$array_replace["[TELEPON_INSTITUSI]"] = (!empty($row_info_pmb["telepon"])) ? $row_info_pmb["telepon"] : ($row_institusi['TeleponPMB'] ?? "");
		$array_replace["[FAX_INSTITUSI]"] = (!empty($row_info_pmb["fax"])) ? $row_info_pmb["fax"] : ($row_institusi['FaxPT'] ?? "");
		$array_replace["[EMAIL_INSTITUSI]"] = (!empty($row_info_pmb["email"])) ? $row_info_pmb["email"] : ($row_institusi['EmailPT'] ?? "");
		$array_replace["[WEB_INSTITUSI]"] = (!empty($row_info_pmb["web"])) ? $row_info_pmb["web"] : ($row_institusi['WebsitePT'] ?? "");
		$array_replace["[ALAMAT_INSTITUSI]"] = $row_institusi["AlamatPT"] ?? "";
		
		$client_path = defined('CLIENT_PATH') ? CLIENT_PATH : url('/');
		$array_replace["[LOGO_INSTITUSI]"] = isset($row_institusi['Gambar']) ? '<img src="' . $client_path . '/images/' . $row_institusi['Gambar'] . '" style="width:90px;max-width:90px;max-height:140px"/>' : '';

		if (!empty($param['pendaftar_id'])) {
			$data_pendaftar = (array)DB::table("mahasiswa")->where("ID", $param["pendaftar_id"])->first();

			$get_list_prodi = DB::table("programstudi")->get();
			$NamaProdi = [];
			$arrNamaJenjang = [];
			foreach ($get_list_prodi as $row_prodi) {
				if (!isset($arrNamaJenjang[$row_prodi->JenjangID])) {
					$arrNamaJenjang[$row_prodi->JenjangID] = get_field($row_prodi->JenjangID, "jenjang");
				}
				$NamaProdi[$row_prodi->ID] = $arrNamaJenjang[$row_prodi->JenjangID] . $row_prodi->Nama;
			}

			$get_list_program = DB::table("program")->get();
			$Namaprogram = [];
			foreach ($get_list_program as $row_program) {
				$Namaprogram[$row_program->ID] = $row_program->Nama;
			}

			$get_list_jalur = DB::table("pmb_edu_jalur_pendaftaran")->get();
			$Namajalur = [];
			foreach ($get_list_jalur as $row_jalur) {
				$Namajalur[$row_jalur->id] = $row_jalur->nama;
			}

			$status_usm = "HASIL KELULUSAN BELUM DITENTUKAN";
			if (($data_pendaftar['statuslulus_pmb'] ?? 0) == 1) {
				$status_usm = "LULUS";
			} else if (($data_pendaftar['statuslulus_pmb'] ?? 0) == 2) {
				$status_usm = "TIDAK LULUS";
			}

			$array_replace["[NAMA_PENDAFTAR]"] = $data_pendaftar['Nama'] ?? "";
			$array_replace["[PROGRAM_KULIAH]"] = $Namaprogram[$data_pendaftar['ProgramID'] ?? ""] ?? "";
			$array_replace["[PROGRAMSTUDI_PILIHAN_1]"] = $NamaProdi[$data_pendaftar['pilihan1'] ?? ""] ?? "";
			$array_replace["[PROGRAMSTUDI_PILIHAN_2]"] = $NamaProdi[$data_pendaftar['pilihan2'] ?? ""] ?? "";
			$array_replace["[PROGRAMSTUDI_PILIHAN_3]"] = $NamaProdi[$data_pendaftar['pilihan3'] ?? ""] ?? "";
			$array_replace["[PROGRAMSTUDI_LULUS]"] = (isset($data_pendaftar['prodilulus_pmb']) && !empty($data_pendaftar['prodilulus_pmb'])) ? ($NamaProdi[$data_pendaftar['prodilulus_pmb']] ?? "") : ($NamaProdi[$data_pendaftar['pilihan1'] ?? ""] ?? "");
			$array_replace["[NO_UJIAN_PENDAFTAR]"] = $data_pendaftar['noujian_pmb'] ?? "";
			$array_replace["[ALAMAT_PENDAFTAR]"] = $data_pendaftar['Alamat'] ?? "";
			$array_replace["[NO_HP_PENDAFTAR]"] = $data_pendaftar['HP'] ?? "";
			$array_replace["[BIAYA_FORMULIR_PENDAFTARAN]"] = "Rp. " . rupiah((int)($data_pendaftar['jumlahbayar_pmb'] ?? 0) + (int)($data_pendaftar['biaya_admin_pmb'] ?? 0) + (int)($data_pendaftar['biaya_tambahan_formulir_pmb'] ?? 0));
			$array_replace["[TGL_HARI_INI]"] = tgl(date('Y-m-d'), '02');
			$array_replace["[TTL_PENDAFTAR]"] = ucfirst(strtolower($data_pendaftar['TempatLahir'] ?? "")) . ", " . tgl($data_pendaftar['TanggalLahir'] ?? date('Y-m-d'), '02');
			$array_replace["[JALUR_PMB_PENDAFTAR]"] = $Namajalur[$data_pendaftar['jalur_pmb'] ?? ""] ?? "";
			$array_replace["[KELULUSAN_USM]"] = $status_usm;

			$data_gelombang = (array)DB::table("pmb_tbl_gelombang_detail as b")
				->join("pmb_tbl_gelombang as a", "a.id", "=", "b.gelombang_id")
				->select("a.tahun_id", "a.id")
				->where("b.id", $data_pendaftar["gelombang_detail_pmb"] ?? "")
				->first();

			if (!empty($data_gelombang)) {
				// data jadwal usm
				$data_jadwal_usm = DB::table('pmb_edu_jadwalusm')->where('gelombang', $data_gelombang['id'])->first();
				if ($data_jadwal_usm) {
					$array_replace["[TGL_USM]"] = tgl($data_jadwal_usm->tgl_ujian, '02');
				}

				$data_tahun = (array)DB::table("tahun")->where("ID", $data_gelombang['tahun_id'])->first();
				$array_replace["[TAHUN_AKADEMIK_GELOMBANG_PENDAFTARAN]"] = $data_tahun['Nama'] ?? "";

				$data_biaya_reg_ulang = DB::table('tagihan_mahasiswa')
					->join('jenisbiaya', 'jenisbiaya.ID', '=', 'tagihan_mahasiswa.JenisBiayaID')
					->select('tagihan_mahasiswa.*', 'jenisbiaya.Nama as JB_nama')
					->where('MhswID', $param['pendaftar_id'])
					->where('Periode', $data_gelombang['tahun_id'])
					->where('JenisBiayaID', '!=', '32')
					->get();

				$data_table_tagihan = '';
				$no_tagihan = 1;
				$total_tagihan = 0;
				foreach ($data_biaya_reg_ulang as $data_tagihan) {
					$data_table_tagihan .= "
						<tr>
							<td style='width : 10px'>" . $no_tagihan++ . "</td>
							<td style='width : 150px;text-align:left;'>" . $data_tagihan->JB_nama . "</td>
							<td style='width : 90px;text-align:left;'>Rp. " . rupiah((int)$data_tagihan->TotalTagihan) . "</td>
						</tr>";
					$total_tagihan += (int)$data_tagihan->TotalTagihan;
				}

				$table_tagihan = ' 
					<div>
					<table border = "1" style=" border-collapse: collapse;margin-left: auto;margin-right: auto;width:100%">
						<tr>
							<td style="background-color:#e3dddc; width : 10px"><b>NO.</b></td>
							<td style="background-color:#e3dddc; width : 150px;text-align:left;"><b>ELEMEN PEMBAYARAN</b></td>
							<td style="background-color:#e3dddc; width : 90px;text-align:left;"><b>PEMBAYARAN</b></td>
						</tr>
						' . $data_table_tagihan . '
						<tr>
							<td style="text-align:left;" colspan ="2"><b>Total Kewajiban Bayar</b></td>
							<td style="text-align:left;">Rp. ' . rupiah((int)$total_tagihan) . '</td>
						</tr>
						<tr>
							<td style="text-align:left;" colspan ="2"><b>Besar Potongan</b></td>
							<td style="text-align:left;">........</td>
						</tr>
						<tr>
							<td style="text-align:left;" colspan ="2"><b>Total yang harus dibayar</b></td>
							<td style="text-align:left;">........</td>
						</tr>
					</table>
					</div>';

				$array_replace["[BIAYA_REGISTRASI_ULANG]"] = $table_tagihan;
			}
		}
		/* ---- Proses Definisi Replace DATA TEMPLATE dengan DATA REAL ---- */

		$result_wording = DB::table(env("DB_MASTER_AIS_NAME") . ".setup_app")
			->select("tipe_setup", "metadata")
			->whereIn("tipe_setup", $kode)
			->get();

		$result = [];
		foreach ($result_wording as $row_wording) {
			$result[$row_wording->tipe_setup] = str_replace(array_keys($array_replace), array_values($array_replace), (string)$row_wording->metadata);
		}

		/* ---- Proses Set Default Wording Jika Custom Tidak Ditemukan ---- */
		$result_default_wording = DB::table(env('DB_MASTER_AIS_NAME') . ".setup_app")
			->select("tipe_setup", "metadata")
			->whereIn("tipe_setup", $kode)
			->get();
        // dd($result_default_wording);
		$arr_default_redaksi = [];
		foreach ($result_default_wording as $row_default_wording) {
			$arr_default_redaksi[$row_default_wording->tipe_setup] = $row_default_wording->metadata;
		}

		foreach ($kode as $row_kode) {
			if (empty($result[$row_kode])) {
				$get_default_wording = $arr_default_redaksi[$row_kode] ?? "";
				$result[$row_kode] = str_replace(array_keys($array_replace), array_values($array_replace), (string)$get_default_wording);
			}
		}

		return $result;
	}
}

if (!function_exists('getDistance')) {
	function getDistance($latitude1, $longitude1, $latitude2, $longitude2)
	{
		$earth_radius = 6371;

		$dLat = deg2rad($latitude2 - $latitude1);
		$dLon = deg2rad($longitude2 - $longitude1);

		$a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon / 2) * sin($dLon / 2);
		$c = 2 * asin(sqrt($a));
		$d = $earth_radius * $c;

		return (int)($d * 1000);
	}
}

if (!function_exists('getDetailKurikulumFeederByID')) {
	function getDetailKurikulumFeederByID($token, $id_prodi, $nama_kurikulum)
	{
		$ctype = 'json';
		$filter = "id_prodi='$id_prodi' AND TRIM(nama_kurikulum)='$nama_kurikulum'";
		$data = array(
			'act' => 'GetDetailKurikulum',
			'token' => $token,
			'filter' => $filter,
			'order' => "",
			'limit' => 20,
			'offset' => 0,
		);

		$result_string = runWS($data, $ctype);
		$result_array = json_decode($result_string, TRUE);

		return $result_array['data'][0] ?? null;
	}
}

if (!function_exists('copyDirectory')) {
	function copyDirectory($path_old_dir_npm, $path_new_dir_npm)
	{
		if (!is_dir($path_old_dir_npm)) return;
		
		$rec_directory = new \RecursiveDirectoryIterator($path_old_dir_npm, \RecursiveDirectoryIterator::SKIP_DOTS);
		$rec_inside = new \RecursiveIteratorIterator($rec_directory, \RecursiveIteratorIterator::SELF_FIRST);

		if (!is_dir($path_new_dir_npm)) {
			mkdir($path_new_dir_npm, 0755, true);
		}

		foreach ($rec_inside as $rec_file) {
			$newest_path = $path_new_dir_npm . DIRECTORY_SEPARATOR . $rec_inside->getSubPathName();

			if ($rec_file->isDir()) {
				if (!is_dir($newest_path)) {
					mkdir($newest_path, 0755, true);
				}
			} else {
				copy($rec_file->getRealPath(), $newest_path);
			}
		}
	}
}

if(!function_exists('create_breadcrumb')){
    function create_breadcrumb()
    {
        $i = 1;
        $uri = request()->segment($i);
        
        $link = '<div class="row">';
        $link .= '<div class="col-md-12">';
        $link .= '<ol class="breadcrumb m-0">';
        
        // Catatan: Jika kamu sekarang menggunakan reload halaman murni, href di bawah ini
        // sebaiknya diubah menjadi url('dashboard') bukan sekadar '#dashboard'
        $link .= '<li class="breadcrumb-item home align-self-center"><a href="'.url('dashboard').'"><i class="fe-home"></i></a></li>';
        $link .= '<li class="breadcrumb-item align-self-center dash"><a href="'.url('dashboard').'">Beranda</a></li>';
        
        // Tambahan validasi $uri != null agar tidak infinite loop di Laravel
        while($uri != null && $uri != request()->segment(3))
        {
            $prep_link = '';
            for($j = 1; $j <= $i; $j++){
                $prep_link .= request()->segment($j) . '/';
            }
            
            // Mengganti nama c_ (kalau masih ada) menjadi kosong
            $segment_name = ucwords(str_replace('_', ' ', str_replace('c_', '', request()->segment($i))));
            
            if(request()->segment($i) == request()->segment(2) || request()->segment($i+1) == '')
            {
                if(request()->segment(2) != 'index')
                {
                    $link .= '<li class="breadcrumb-item active align-self-center">';
                    $link .= $segment_name . '</li>';
                }
            }
            else
            {
                if(request()->segment(2) != 'index')
                {
                    // Pemanggilan href diubah menggunakan url() bawaan Laravel
                    $link .= '<li class="breadcrumb-item align-self-center"><a href="'.url(rtrim($prep_link, '/')).'">';
                    $link .= $segment_name . '</a></li> ';
                }
                else
                {
                    $link .= '<li class=" breadcrumb-item active align-self-center">';
                    $link .= $segment_name . '</li>';
                }
            }
        
            $i++;
            $uri = request()->segment($i);
        }
        
        $link .= '</ol>';
        $link .= '</div>';
        $link .= '</div>';
        
        return $link;
    }
}

if(!function_exists('load_pagination')){
    function load_pagination($jml, $limit, $offset, $uri2, $filter, $type='', $uri_segment = '')
    {
        // Jika data lebih kecil atau sama dengan limit, tidak perlu ada pagination
        if ($jml <= $limit) {
            return '';
        }

        // Setup Base URL (request()->segment(1) menggantikan $ci->uri->segment(1))
        $base_url = url(request()->segment(1) . '/' . $uri2);
        $base_url = rtrim($base_url, '/') . '/'; // Memastikan ada slash di akhir URL

        // Kalkulasi halaman
        $total_pages = ceil($jml / $limit);
        $current_page = floor($offset / $limit) + 1;

        // Hardcode Bahasa Indonesia (karena kamu minta tidak pakai lang line dulu)
        $next_link = 'Selanjutnya &raquo;';
        $prev_link = '&laquo; Sebelumnya';
        $last_link = 'Terakhir &raquo;';
        $first_link = '&laquo; Pertama';

        // Mulai membungkus HTML persis seperti format CI3 kamu
        $html = "<nav aria-label='Page navigation example'><ul class='pagination justify-content-end mt-2'>";

        // Tombol "Pertama"
        if ($current_page > 1) {
            $html .= "<li class='page-item'><a href='javascript:void(0)' onclick=\"".$filter."('".$base_url."0')\" class='page-link'>".$first_link."</a></li>";
        }

        // Tombol "Sebelumnya"
        if ($current_page > 1) {
            $prev_offset = ($current_page - 2) * $limit;
            $html .= "<li class='page-item'><a href='javascript:void(0)' onclick=\"".$filter."('".$base_url.$prev_offset."')\" class='page-link'>".$prev_link."</a></li>";
        }

        // Tombol Angka (Misal menampilkan 2 angka sebelum & sesudah halaman aktif)
        $num_links = 2;
        
        // Batasi maksimal halaman yang ditampilkan (misal maksimal 5 halaman)
        $max_pages_to_show = 5;
        
        $start = (($current_page - $num_links) > 0) ? $current_page - $num_links : 1;
        $end = (($current_page + $num_links) < $total_pages) ? $current_page + $num_links : $total_pages;
        
        // Hitung berapa halaman yang akan ditampilkan
        $pages_to_show = $end - $start + 1;
        
        // Jika lebih dari maksimal, sesuaikan
        if ($pages_to_show > $max_pages_to_show) {
            if ($current_page <= $max_pages_to_show) {
                // Jika di awal, tampilkan dari halaman 1
                $end = $max_pages_to_show;
            } elseif ($current_page + $num_links >= $total_pages) {
                // Jika di akhir, tampilkan sampai halaman terakhir
                $start = $total_pages - $max_pages_to_show + 1;
            } else {
                // Jika di tengah, bagi rata
                $start = $current_page - floor($max_pages_to_show / 2);
                $end = $current_page + floor($max_pages_to_show / 2);
            }
        }
        
        // Pastikan tidak kurang dari 1 atau lebih dari total
        if ($start < 1) $start = 1;
        if ($end > $total_pages) $end = $total_pages;

        for ($page = $start; $page <= $end; $page++) {
            $page_offset = ($page - 1) * $limit;
            if ($current_page == $page) {
                // Halaman Aktif
                $html .= "<li class='page-item active'><a href='javascript:void(0)' class='page-link'>".$page."</a></li>";
            } else {
                $html .= "<li class='page-item'><a href='javascript:void(0)' onclick=\"".$filter."('".$base_url.$page_offset."')\" class='page-link'>".$page."</a></li>";
            }
        }

        // Tombol "Selanjutnya"
        if ($current_page < $total_pages) {
            $next_offset = $current_page * $limit;
            $html .= "<li class='page-item'><a href='javascript:void(0)' onclick=\"".$filter."('".$base_url.$next_offset."')\" class='page-link'>".$next_link."</a></li>";
        }

        // Tombol "Terakhir"
        if ($current_page < $total_pages) {
            $last_offset = ($total_pages - 1) * $limit;
            $html .= "<li class='page-item'><a href='javascript:void(0)' onclick=\"".$filter."('".$base_url.$last_offset."')\" class='page-link'>".$last_link."</a></li>";
        }

        $html .= "</ul></nav>";

        return $html;
    }
}

if(!function_exists('total_row')){
    function total_row($jml, $limit, $offset)
    {
        $z = $offset + $limit;
        
        if($z > $jml) {
            $z = $jml;
        }

        // Mencegah error teks jika data kosong (menjadi 0 - 0)
        $start_data = ($jml > 0) ? ($offset + 1) : 0;
        
        // Hardcode Bahasa Indonesia
        $ket = '<span>Menampilkan data ' . $start_data . ' - ' . $z . ' dari <b>' . $jml . '</b> data</span>';
        
        return $ket;    
    }
}

if(!function_exists('cetak_header')){

	function cetak_header($lebar="100%")
	{
		// Konversi dari $ci = &get_instance(); $ci->db->get("identitas");
		$raw = \Illuminate\Support\Facades\DB::table('identitas')->first();

		if($raw)
		{
			// Konvensi Laravel 12 untuk Asset Public
			$pathgambar = asset('images/' . $raw->Gambar);

			if(!is_file(public_path('images/' . $raw->Gambar))){
				$gambar = '';
			}else{
				$gambar = '<img src="'.$pathgambar.'" style="width:90px;max-width:90px;max-height:140px"/>';
				// $gambar = '<img src="'.$pathgambar.'" style="min-width:30%;min-height:30%"/>';
			}

			// Menggunakan helper bawaan yang sudah direfactor/dibuat di bawah
			$setup_daftar_nilai = null; // Menambahkan null safe fallback
			$css = '';
			$css .= "<style>.table_no_borders td { border: none !important; } .table_borders{border-top:1px;border-bottom:1px;border-right:1px;border-left:1px}</style>";
			
			$header = '
				<table border="0" align="center" style="margin-top:-10px;font-family:arial;width: '.$lebar.';">
					<tr>
						<td style="width:10%;text-align:left;border:none;">
						'.$gambar.'
						</td>
						<td style="width:75%;text-align:left;border:none;">
							<b style="font-size:23px;">'.$raw->NamaPT.'</b>
							<br>
							<span style="font-size:12px;margin-top:5px;">Alamat : '.$raw->AlamatPT.', '.$raw->KodePosPT.'</span>
							<br>
							<span style="font-size:12px;margin-top:10px;">Telepon : '.$raw->TeleponPT.', Fax : '.$raw->FaxPT.'</span>
							<br>
							<span style="font-size:12px;margin-top:14px;">Email : '.$raw->EmailPT.', Website : '.$raw->WebsitePT.'</span>
						</td>
					</tr>
				</table>
				<div style="margin-top:0px">
					<hr style="border:0.2px solid black">
					<hr style="border:0.2px solid black;margin-top:-17px">
				</div>
				';
		}else{
			$header = '';
		}
		return $header;
	}

}

if(!function_exists('cetak_header_excel')){
    
    function cetak_header_excel($col='7')
    {
        // Migrasi dari $ci = &get_instance(); $ci->db->get("identitas");
        $raw = \Illuminate\Support\Facades\DB::table('identitas')->first();
        
        if($raw) 
        {
            // Menyesuaikan konvensi path asset Laravel (asumsi public/images)
            $hostgambar = asset('images/' . $raw->Gambar); 
            if(!is_file(public_path('images/' . $raw->Gambar))){
                $gambar = '';
            }else{
                $gambar = '<img src="'.$hostgambar.'" width="82" height="77" style="float:left;" />';
            }

            $header = '
            <table class="header" width="100%">
            <tr>
            <td width="5%" rowspan="4">
            '.$gambar.'
            </td>
            <td width="95%" colspan="'.$col.'" style="text-align:center;"></td>
            </tr>
            <tr>
            <td colspan="'.$col.'" style="text-align:center;">'.$raw->NamaPT.'</td>
            </tr>
            <tr>
            <td colspan="'.$col.'" style="text-align:center;">'.$raw->AlamatPT.' '.$raw->KodePosPT.' &nbsp; Telp: '.$raw->TeleponPT.' Fax: '.$raw->FaxPT.'</td>
            </tr>
            <tr>
            <td colspan="'.$col.'" style="text-align:center;">Homepage: '.$raw->WebsitePT.'  Email: '.$raw->EmailPT.'</td>
            </tr>
            </table>
            <hr>
            ';
            
        }
        else
        {
            $header = '';
        }
        
        return $header;
    }
    
}

if (! function_exists('get_photo')) {
    function get_photo($kode, $foto, $jk, $namatabel, $class = '', $cetak = '', $type = 'tag')
    {
        $namatabel_default = $namatabel;
        if ($namatabel === 'dosen') {
            $namatabel_default = 'pegawai';
        }

        if ($cetak) {
            if ($foto) {
                $tempPath = CLIENT_PATH . DIRECTORY_SEPARATOR . $namatabel_default . DIRECTORY_SEPARATOR . $kode . DIRECTORY_SEPARATOR . 'foto' . DIRECTORY_SEPARATOR . $foto;
                
                if (File::isFile($tempPath)) {
                    $path = $tempPath;
                } else {
                    $path = ASSETS_PATH . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'tanda_tanya.png';
                }
            } elseif ($jk === 'L') {
                $path = ASSETS_PATH . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'default_' . $namatabel . '.png';
            } elseif ($jk === 'P') {
                $path = ASSETS_PATH . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'default_' . $namatabel . '_2.png';
            } else {
                $path = ASSETS_PATH . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'tanda_tanya.png';
            }

            if (! File::isFile($path)) {
                $path = ASSETS_PATH . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'tanda_tanya.png';
            }

        } else {
            if ($foto) {
                // For browser, we always use / as separator
                $webPath = $namatabel_default . '/' . $kode . '/foto/' . $foto;
                $tempPath = CLIENT_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $webPath);

                if (File::exists($tempPath)) {
                    $path = CLIENT_HOST . '/' . $webPath;
                } else {
                    $path = ASSETS_HOST . '/images/tanda_tanya.png';
                }
            } elseif ($jk === 'L') {
                $path = ASSETS_HOST . '/images/default_' . $namatabel . '.png';
            } elseif ($jk === 'P') {
                $path = ASSETS_HOST . '/images/default_' . $namatabel . '_2.png';
            } else {
                $path = ASSETS_HOST . '/images/tanda_tanya.png';
            }
        }

        if ($type === 'tag') {
            // Gunakan HtmlString agar tag HTML dirender dengan benar di Blade
            return new HtmlString('<img class="' . $class . '" src="' . $path . '">');
        } elseif ($type === 'path') {
            return $path;
        }
    }
}

if (!function_exists('cetak_kop_phpspreadsheet')) {
    /**
     * Helper untuk mencetak Kop Surat di PhpSpreadsheet
     * @param object $sheet Objek dari $spreadsheet->getActiveSheet()
     * @param string $last_col_letter Huruf kolom terakhir untuk di-merge (contoh: 'G', 'T')
     * @param int $start_row Baris awal dimulainya kop surat (default: 1)
     * @return int Mengembalikan nomor baris terbaru setelah kop surat (untuk mulai tabel)
     */
    function cetak_kop_phpspreadsheet($sheet, $last_col_letter = 'G', $start_row = 1)
    {
        $identitas = DB::table('identitas')->first();
        
        if ($identitas) {
            // 1. Logo hanya di Kolom A saja
            $sheet->mergeCells('A' . $start_row . ':A' . ($start_row + 3));
            $sheet->getColumnDimension('A')->setWidth(14);

            // Menyisipkan Logo
            if (defined('CLIENT_PATH')) {
                $pathgambar = CLIENT_PATH . '/images/' . $identitas->Gambar;
                if (file_exists($pathgambar) && !is_dir($pathgambar)) {
                    $objDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $objDrawing->setName('Logo');
                    $objDrawing->setDescription('Logo Identitas');
                    $objDrawing->setPath($pathgambar);
                    
                    $objDrawing->setCoordinates('A' . $start_row);
                    $objDrawing->setHeight(75); // Tinggi 75px
                    $objDrawing->setOffsetX(14); // Rata tengah Horizontal
                    $objDrawing->setOffsetY(15); // Rata tengah Vertikal
                    $objDrawing->setWorksheet($sheet);
                }
            }

            // 2. Set Tinggi Baris (Total 4 baris = 80 points)
            $sheet->getRowDimension($start_row)->setRowHeight(20);     
            $sheet->getRowDimension($start_row + 1)->setRowHeight(20); 
            $sheet->getRowDimension($start_row + 2)->setRowHeight(20); 
            $sheet->getRowDimension($start_row + 3)->setRowHeight(20); 

            // 3. Teks Kop Surat dimulai dari Kolom B
            $col_text_start = 'B';
            $sheet->mergeCells($col_text_start . $start_row . ':' . $last_col_letter . $start_row);

            // Teks Baris 2 (Nama PT)
            $row_nama = $start_row + 1;
            $sheet->setCellValue($col_text_start . $row_nama, $identitas->NamaPT);
            $sheet->mergeCells($col_text_start . $row_nama . ':' . $last_col_letter . $row_nama);
            $sheet->getStyle($col_text_start . $row_nama)->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle($col_text_start . $row_nama)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            // Teks Baris 3 (Alamat & Kontak)
            $row_alamat = $start_row + 2;
            $teks_alamat = $identitas->AlamatPT . ' ' . $identitas->KodePosPT . '   Telp: ' . $identitas->TeleponPT . '   Fax: ' . $identitas->FaxPT;
            $sheet->setCellValue($col_text_start . $row_alamat, $teks_alamat);
            $sheet->mergeCells($col_text_start . $row_alamat . ':' . $last_col_letter . $row_alamat);
            $sheet->getStyle($col_text_start . $row_alamat)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setWrapText(true); 

            // Teks Baris 4 (Website & Email)
            $row_web = $start_row + 3;
            $teks_web = 'Homepage: ' . $identitas->WebsitePT . '   Email: ' . $identitas->EmailPT;
            $sheet->setCellValue($col_text_start . $row_web, $teks_web);
            $sheet->mergeCells($col_text_start . $row_web . ':' . $last_col_letter . $row_web);
            $sheet->getStyle($col_text_start . $row_web)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            // 4. Garis bawah tebal 
            $styleHr = [
                'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK]]
            ];
            $sheet->getStyle('A' . $row_web . ':' . $last_col_letter . $row_web)->applyFromArray($styleHr);

            return $start_row + 5; 
        }
        
        return $start_row;
    }
}