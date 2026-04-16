<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Mengambil daftar menu utama untuk dashboard
     */
    public function getMenus($userId)
    {

        $userId = 1;

        // Query aslinya dipindahkan ke sini menggunakan raw query binding Laravel
        // Binding [ ? ] digunakan agar aman dari SQL Injection
        $query = "
            SELECT DISTINCT modul.MdlGrpID, modulgrup.*
            FROM modul
            JOIN modulgrup ON modulgrup.ID = modul.MdlGrpID
            JOIN levelmodul ON levelmodul.ModulID = modul.ID
            WHERE levelmodul.Read = 'YA'
            AND levelmodul.type = 'modul'
            AND levelmodul.LevelID IN (
                SELECT LevelID FROM leveluser WHERE UserID = ?
            )
            GROUP BY modul.MdlGrpID, modulgrup.ID
            ORDER BY modulgrup.Urut
        ";

        return DB::select($query, [$userId]);
    }

    /**
     * Logika panjang dari Controller CI3 untuk menghitung progress dipindah ke sini
     */
    public function getDashboardProgressData()
    {
        $terisi = 0;
        $total = 10;
        $list_data = [];

        // 1. Pengecekan Fakultas (Contoh adaptasi dari CI3-mu)
        $fakultas = DB::table('fakultas')
            ->leftJoin('identitas', 'identitas.ID', '=', 'fakultas.IdentitasID')
            ->select(
            DB::raw("IFNULL(fakultas.FakultasID, '') AS Kode_Fakultas"),
            DB::raw("IFNULL(fakultas.Nama, '') AS Nama_Fakultas"),
            'fakultas.ID'
        )
            ->groupBy('fakultas.ID')
            ->get();

        if ($fakultas->count() > 0) {
            $terisi += 1;
        // (Logika pengecekan field kosong lainnya ditaruh di sini)
        }
        else {
            $list_data[3] = [
                'alert' => "Data Fakultas Belum Terisi.",
                'detail' => ''
            ];
        }

        // Pengecekan Dosen
        $jml_dosen = DB::table('dosen')->count();
        if ($jml_dosen > 5) {
            $terisi += 1;
        }
        else {
            $list_data[5] = [
                'alert' => "Jumlah Dosen Belum Memenuhi Kuota Minimum.",
                'detail' => "Jumlah Dosen: $jml_dosen Orang. Minimum: 5 Orang."
            ];
        }

        // Hitung persentase
        $progress_percent = (int)(($terisi / $total) * 100);

        return [
            'progress_percent' => $progress_percent,
            'list_alert_progress' => $list_data
        ];
    }
}