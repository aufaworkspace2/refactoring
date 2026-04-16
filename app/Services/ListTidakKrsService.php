<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ListTidakKrsService
{
    public function getDataMahasiswaTidakKRS($programID, $tahunMasuk, $prodiID, $tahunID, $statusMhswID, $keyword, $TidakKrs, $offset = '', $limit = '')
    {
        $query = DB::table('mahasiswa')
            ->leftJoin('program', 'program.ID', '=', 'mahasiswa.ProgramID')
            ->leftJoin('programstudi', 'programstudi.ID', '=', 'mahasiswa.ProdiID')
            ->select(
                'mahasiswa.*',
                'program.Nama as ProgramNama',
                'programstudi.Nama as ProdiNama',
                DB::raw('(SELECT COUNT(*) FROM tmp_tidak_krs WHERE tmp_tidak_krs.MhswID = mahasiswa.ID) as jumlah')
            );

        // Filters
        if ($programID && $programID != '') {
            $query->where('mahasiswa.ProgramID', $programID);
        }
        
        if ($prodiID && $prodiID != '') {
            $query->where('mahasiswa.ProdiID', $prodiID);
        }
        
        if ($tahunMasuk && $tahunMasuk != '') {
            $query->where('mahasiswa.TahunMasuk', $tahunMasuk);
        }
        
        if ($statusMhswID && $statusMhswID != '') {
            $query->where('mahasiswa.StatusMhswID', $statusMhswID);
        }
        
        if ($keyword && $keyword != '') {
            $query->where(function($q) use ($keyword) {
                $q->where('mahasiswa.Nama', 'like', "%{$keyword}%")
                  ->orWhere('mahasiswa.NPM', 'like', "%{$keyword}%");
            });
        }

        // Filter "Tidak KRS" - students who didn't register
        if ($TidakKrs && $TidakKrs != '') {
            // This typically filters students based on tmp_tidak_krs table
            $query->whereExists(function($sub) use ($tahunID) {
                $sub->select(DB::raw(1))
                    ->from('tmp_tidak_krs')
                    ->whereColumn('tmp_tidak_krs.MhswID', 'mahasiswa.ID')
                    ->where('tmp_tidak_krs.Status', '0');
            });
        }

        $query->orderBy('mahasiswa.NPM', 'ASC');

        if ($limit != '' && $offset != '') {
            return $query->skip($offset)->take($limit)->get();
        }

        return $query->get();
    }

    public function countDataMahasiswaTidakKRS($programID, $tahunMasuk, $prodiID, $tahunID, $statusMhswID, $keyword, $TidakKrs)
    {
        $query = DB::table('mahasiswa');

        if ($programID && $programID != '') {
            $query->where('mahasiswa.ProgramID', $programID);
        }
        
        if ($prodiID && $prodiID != '') {
            $query->where('mahasiswa.ProdiID', $prodiID);
        }
        
        if ($tahunMasuk && $tahunMasuk != '') {
            $query->where('mahasiswa.TahunMasuk', $tahunMasuk);
        }
        
        if ($statusMhswID && $statusMhswID != '') {
            $query->where('mahasiswa.StatusMhswID', $statusMhswID);
        }
        
        if ($keyword && $keyword != '') {
            $query->where(function($q) use ($keyword) {
                $q->where('mahasiswa.Nama', 'like', "%{$keyword}%")
                  ->orWhere('mahasiswa.NPM', 'like', "%{$keyword}%");
            });
        }

        if ($TidakKrs && $TidakKrs != '') {
            $query->whereExists(function($sub) use ($tahunID) {
                $sub->select(DB::raw(1))
                    ->from('tmp_tidak_krs')
                    ->whereColumn('tmp_tidak_krs.MhswID', 'mahasiswa.ID')
                    ->where('tmp_tidak_krs.Status', '0');
            });
        }

        return $query->count();
    }

    public function setStudentStatus($mhswID, $status, $tahunID, $jb = 59)
    {
        $tahun = DB::table('tahun')->where('ID', $tahunID)->first();
        $mhsw = DB::table('mahasiswa')->where('ID', $mhswID)->first();

        if (!$tahun || !$mhsw) {
            return 'error';
        }

        // Get semesters where student didn't KRS
        $result_semester = DB::table('tmp_tidak_krs')
            ->join('tahun', 'tahun.ID', '=', 'tmp_tidak_krs.TahunID')
            ->where('tmp_tidak_krs.MhswID', $mhsw->ID)
            ->where('tahun.TahunID', '<=', $tahun->TahunID)
            ->where('tmp_tidak_krs.Status', '0')
            ->orderBy('tahun.TahunID', 'ASC')
            ->select('tmp_tidak_krs.*')
            ->get();

        $arrTahunID = [];
        $arrTMPID = [];
        foreach ($result_semester as $row_semester) {
            $arrTahunID[$row_semester->TahunID] = $row_semester->TahunID;
            $arrTMPID[$row_semester->ID] = $row_semester->ID;
        }

        // If status is Cuti (2), validate price setup
        if ($status == 2) {
            $cur = date("Y-m-d");
            $whereHarga = "(ProgramID='{$mhsw->ProgramID}' OR ProgramID='0')
                        AND (ProdiID='{$mhsw->ProdiID}' OR ProdiID='0')
                        AND (JenisPendaftaran='{$mhsw->StatusPindahan}' OR JenisPendaftaran='0')
                        AND (TahunMasuk='{$mhsw->TahunMasuk}' OR TahunMasuk='0')";

            $setupHarga = DB::table('setup_harga_biaya_variable')
                ->whereRaw($whereHarga)
                ->whereRaw("('$cur' BETWEEN TanggalMulai AND TanggalSelesai)")
                ->orderByRaw('ProgramID DESC, ProdiID DESC, TahunMasuk DESC')
                ->limit(1)
                ->where('Jenis', 'Cuti')
                ->first();

            if (empty($setupHarga)) {
                return 2; // Error: Setup harga cuti tidak ditemukan
            }
        }

        // Update mahasiswa status
        $update = DB::table('mahasiswa')
            ->where('ID', $mhswID)
            ->update(['StatusMhswID' => $status]);

        if ($update) {
            $NamaStatus = get_field($status, 'statusmahasiswa', 'Nama');

            // Insert to keteranganstatusmahasiswa
            $input = [
                'ProdiID' => $mhsw->ProdiID,
                'TahunID' => $tahun->ID,
                'MhswID' => $mhswID,
                'StatusMahasiswaID' => $status,
                'Status' => $NamaStatus,
                'Nomor_Surat' => '',
                'Mulai_Semester' => $tahun->TahunID,
                'Akhir_Semester' => $tahun->TahunID,
                'Alasan' => 'Diubah di menu list tidak KRS',
                'InactivePaksa' => 1,
                'Tgl' => date('Y-m-d')
            ];

            $insert = DB::table('keteranganstatusmahasiswa')->insert($input);

            if ($insert) {
                // Insert log
                DB::table('log_set_status')->insert([
                    'MhswID' => $mhsw->ID,
                    'NPM' => $mhsw->NPM,
                    'TahunID' => $tahun->ID,
                    'StatusMhswID' => $status,
                    'Status' => $NamaStatus,
                    'createdAt' => date('Y-m-d H:i:s'),
                    'UserID' => Session::get('UserID')
                ]);

                // Update tmp_tidak_krs status
                if (count($arrTMPID) > 0) {
                    DB::table('tmp_tidak_krs')
                        ->whereIn('ID', $arrTMPID)
                        ->update(['Status' => 1]);
                }

                // If status is Cuti (2), handle tagihan
                if ($status == 2) {
                    foreach ($arrTahunID as $keyTahunID) {
                        $list_tagihan = DB::table('tagihan_mahasiswa')
                            ->where('MhswID', $mhsw->ID)
                            ->where('Periode', $keyTahunID)
                            ->get();

                        foreach ($list_tagihan as $row_tagihan) {
                            if ($row_tagihan->TotalCicilan == 0) {
                                if (function_exists('delete_tagihan')) {
                                    delete_tagihan($row_tagihan->ID);
                                }
                            }
                        }

                        if (function_exists('update_tagihan_variable')) {
                            update_tagihan_variable($mhsw->ID, $keyTahunID, $jb);
                        }
                    }
                }
            }
        }

        return 'success';
    }
}
