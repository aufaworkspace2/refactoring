<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class RekapitulasiPendaftaranPmbService
{
    public function get_data($gelombang = '', $gelombang_detail = '', $ujian = '', $program = '', $ikut_ujian = '', $tgl1 = '', $tgl2 = '', $status = '')
    {
        $whr = '';

        if ($gelombang) {
            $whr .= " and pmb_tbl_gelombang.ID='" . $gelombang . "' ";
        }

        if ($gelombang_detail) {
            $whr .= " and mahasiswa.gelombang_detail_pmb='" . $gelombang_detail . "'";
        }

        if ($ujian) {
            if ($ujian == '1') {
                $whr .= " and mahasiswa.ujian_online_pmb='" . $ujian . "'";
            } else {
                $whr .= " and mahasiswa.ujian_online_pmb !='1' ";
            }
        }

        if ($program) {
            $whr .= " and mahasiswa.ProgramID='" . $program . "'";
        }

        if ($ikut_ujian) {
            if ($ikut_ujian == '1') {
                $whr .= " and mahasiswa.ikut_ujian_pmb='" . $ikut_ujian . "'";
            } else {
                $whr .= " and mahasiswa.ikut_ujian_pmb !='1' ";
            }
        }

        if ($tgl1) {
            $whr .= " and date(mahasiswa.TglBuat) >= '" . $tgl1 . "'";
        }

        if ($tgl2) {
            $whr .= " and date(mahasiswa.TglBuat) <= '" . $tgl2 . "'";
        }

        if ($status) {
            if ($status == 'verifikasi') {
                $whr .= " and mahasiswa.statusbayar_pmb='0'";
            } else if ($status == 'calon') {
                $whr .= " and mahasiswa.statusbayar_pmb='1'";
            } else if ($status == 'sudahregistrasiulang') {
                $whr .= " and mahasiswa.statusbayar_registrasi_pmb='1' ";
            } else if ($status == 'sudahgeneratenim') {
                $whr .= " and mahasiswa.jenis_mhsw='mhsw' ";
            }
        }

        $results = DB::select("
            select mahasiswa.pilihan1, mahasiswa.ProgramID, mahasiswa.jalur_pmb, COUNT(DISTINCT mahasiswa.ID) AS jumlah
            from mahasiswa
            inner join program on program.ID=mahasiswa.ProgramID
            inner join pmb_tbl_gelombang_detail on pmb_tbl_gelombang_detail.id=mahasiswa.gelombang_detail_pmb
            inner join pmb_tbl_gelombang on pmb_tbl_gelombang_detail.gelombang_id=pmb_tbl_gelombang.id
            $whr
            GROUP BY mahasiswa.pilihan1, mahasiswa.ProgramID
        ");

        return array_map(function ($item) {
            return (array) $item;
        }, $results);
    }

    public function get_all_programstudi()
    {
        $results = DB::table('programstudi')->get();
        return array_map(function ($item) {
            return (array) $item;
        }, $results->toArray());
    }

    public function get_all_gelombang()
    {
        $results = DB::table('pmb_tbl_gelombang')->get();
        return array_map(function ($item) {
            return (array) $item;
        }, $results->toArray());
    }
}
