<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class RekapitulasiReferensiDaftarPmbService
{
    public function get_data($gelombang = '', $gelombang_detail = '', $programID = '', $pilihan1 = '', $tahunMasuk = '', $statusbayar_registrasi_pmb = '', $sekolahID = '')
    {
        $whr = '';

        if ($gelombang) {
            $whr .= " and pmb_tbl_gelombang.id='" . $gelombang . "' ";
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

        $results = DB::select("SELECT
            CONCAT(
            jenjang.Nama,
            ' | ',
            programstudi.Nama
            ) AS prodiNama,
            programstudi.ID AS prodiID,
            program.Nama AS programNama,
            pmb_tbl_gelombang.ID AS gelombang_id,
            mahasiswa.ref_daftar,
            pmb_tbl_referensi_daftar.nama_ref,
            pmb_tbl_gelombang_detail.id AS gelombang_detail_id,
            COUNT(DISTINCT mahasiswa.ID) AS JumlahPendaftar
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
            LEFT JOIN pmb_tbl_referensi_daftar
                ON pmb_tbl_referensi_daftar.id_ref_daftar = mahasiswa.ref_daftar
            WHERE 1 = 1
            $whr GROUP BY mahasiswa.ref_daftar order by mahasiswa.ref_daftar,programstudi.KodePMB ASC,program.KodePMB ASC");

        return array_map(function ($item) {
            return (array) $item;
        }, $results);
    }

    public function get_all_referensi()
    {
        $results = DB::table('pmb_tbl_referensi_daftar')
            ->select('id_ref_daftar', 'nama_ref')
            ->orderBy('id_ref_daftar')
            ->get();
        return array_map(function ($item) {
            return (array) $item;
        }, $results->toArray());
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
}
