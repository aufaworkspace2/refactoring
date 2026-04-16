<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class JumlahSudahBayarRegistrasiUlangPmbService
{
    public function get_data($gelombang = '', $gelombang_detail = '', $programID = '', $pilihan1 = '', $tahunMasuk = '', $statusbayar_registrasi_pmb = '', $sekolahID = '')
    {
        $whr = '';

        if ($gelombang) {
            $whr .= " and pmb_tbl_gelombang.ID='" . $gelombang . "' ";
        }

        if ($gelombang_detail) {
            $whr .= " and mahasiswa.gelombang_detail_pmb='" . $gelombang_detail . "'";
        }

        if ($programID) {
            $whr .= " and mahasiswa.ProgramID='" . $programID . "'";
        }

        if ($pilihan1) {
            $whr .= " and mahasiswa.pilihan1='" . $pilihan1 . "'";
        }

        if ($tahunMasuk) {
            $whr .= " and mahasiswa.TahunMasuk='" . $tahunMasuk . "'";
        }

        if ($statusbayar_registrasi_pmb) {
            if ($statusbayar_registrasi_pmb == '00') {
                $whr .= " and mahasiswa.statusbayar_registrasi_pmb not in (1,2,3) ";
            } else if ($statusbayar_registrasi_pmb == '011') {
                $whr .= " and mahasiswa.statusbayar_registrasi_pmb in (1,2,3) ";
            } else {
                $whr .= " and mahasiswa.statusbayar_registrasi_pmb='" . $statusbayar_registrasi_pmb . "' ";
            }
        }

        if ($sekolahID) {
            $whr .= " and mahasiswa.SekolahID='" . $sekolahID . "'";
        }

        $sql = "SELECT
            concat(jenjang.Nama,' | ',programstudi.Nama) AS prodiNama,
            programstudi.ID AS prodiID,
            program.Nama AS programNama,
            pmb_tbl_gelombang.ID AS gelombang_id,
            pmb_tbl_gelombang_detail.id AS gelombang_detail_id,
            COUNT(DISTINCT mahasiswa.ID) AS JumlahSudahBayar
            FROM
            mahasiswa
            INNER JOIN pmb_tbl_gelombang_detail
                ON pmb_tbl_gelombang_detail.id = mahasiswa.gelombang_detail_pmb
            INNER JOIN programstudi
                ON programstudi.ID = mahasiswa.pilihan1
            INNER JOIN jenjang
                ON programstudi.JenjangID = jenjang.ID
            INNER JOIN program
                ON program.ID = mahasiswa.ProgramID
            INNER JOIN pmb_tbl_gelombang
                ON pmb_tbl_gelombang_detail.gelombang_id = pmb_tbl_gelombang.id
            WHERE mahasiswa.statuslulus_pmb = '1' and mahasiswa.statusregistrasi_pmb='1'
            $whr GROUP BY mahasiswa.pilihan1,mahasiswa.ProgramID order by programstudi.KodePMB ASC,program.KodePMB ASC";

        $results = DB::select($sql);

        // Convert to array format to maintain compatibility with CI3 result_array()
        return array_map(function ($item) {
            return (array) $item;
        }, $results);
    }

    public function get_tahun_masuk()
    {
        $results = DB::select("SELECT TahunMasuk from mahasiswa where statusregistrasi_pmb='1' group by TahunMasuk order by TahunMasuk DESC");
        return array_map(function ($item) {
            return (array) $item;
        }, $results);
    }

    public function get_gelombang()
    {
        $results = DB::table('pmb_tbl_gelombang')->get();
        return array_map(function ($item) {
            return (array) $item;
        }, $results->toArray());
    }

    public function get_program()
    {
        $results = DB::table('program')->get();
        return array_map(function ($item) {
            return (array) $item;
        }, $results->toArray());
    }

    public function get_programstudi()
    {
        $results = DB::table('programstudi')->get();
        return array_map(function ($item) {
            return (array) $item;
        }, $results->toArray());
    }
}
