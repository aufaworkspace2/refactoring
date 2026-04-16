<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class LaporanStatusInputNilaiService
{
    /**
     * Search data with filters
     */
    public function searchData($filters)
    {
        $programID = $filters['ProgramID'] ?? '';
        $prodiID = $filters['ProdiID'] ?? '';
        $tahunID = $filters['TahunID'] ?? '';
        $dosenID = $filters['DosenID'] ?? '';
        $status = $filters['Status'] ?? '';

        // Get jadwal dosen
        $listJadwal = $this->getJadwalDosen($dosenID, $programID, $prodiID, $tahunID);

        // Get jadwal gabungan
        $jadwalGabunganRaw = $this->getListJadwalGabungan($tahunID);
        $jadwalGabungan = [];

        foreach ($jadwalGabunganRaw as $value) {
            $jadwalGabungan[$value->jadwalID] = $value;
            $jadwalExp = explode(',', $value->jadwalGabungan);
            foreach ($jadwalExp as $values) {
                $jadwalGabungan[$values] = $value;
            }
        }

        $query = [];

        foreach ($listJadwal as $value) {
            if ($value->jadwalID) {
                $whereJadwal = [];
                if ($value->gabungan == 'YA') {
                    $jadwalGabunganData = $this->getJadwalGabungan($value->jadwalID);
                    $whereJadwal = $jadwalGabunganData ? explode(',', $jadwalGabunganData->listJadwal) : [];
                } else {
                    $whereJadwal[] = $value->jadwalID;
                }

                $dataNilai = $this->getPesertaKRS($whereJadwal);

                $emptyNilai = 0;
                $hasNilai = 0;
                foreach ($dataNilai as $rowNilai) {
                    if (!empty($rowNilai->nilaiID)) {
                        $hasNilai += 1;
                    } else {
                        $emptyNilai += 1;
                    }
                }

                $value->hasNilai = $hasNilai;
                $value->emptyNilai = $emptyNilai;
                $totalPeserta = count($dataNilai);
                $value->persentaseNilai = $totalPeserta > 0 ? intval($hasNilai / $totalPeserta * 100) : 0;

                if ($status == '1' && $value->persentaseNilai <= 0) {
                    continue;
                } elseif ($status == '2' && $value->persentaseNilai > 0) {
                    continue;
                }
            }

            $query[$value->jadwalID] = $value;
        }

        return [
            'query' => $query,
            'jadwalGabungan' => $jadwalGabungan,
        ];
    }

    /**
     * Get jadwal dosen
     */
    private function getJadwalDosen($dosenID, $programID, $prodiID, $tahunID)
    {
        $query = DB::table('jadwal')
            ->select(
                'jadwal.ID as jadwalID',
                'jadwal.DosenID as dosenID',
                'jadwal.DosenAnggota as dosenAnggota',
                'jadwal.TahunID as tahunID',
                'jadwal.DetailKurikulumID as detailkurikulumID',
                'jadwal.KelasID as kelasID',
                'jadwal.Aktif as aktif',
                'detailkurikulum.MKKode as mkkode',
                'detailkurikulum.Nama as namaMatkul',
                'detailkurikulum.TotalSKS as totalSKS',
                'detailkurikulum.ProgramID as programID',
                'detailkurikulum.ProdiID as prodiID',
                'jadwal.Pengumuman as pengumuman',
                'jadwal.JumlahPeserta as totalPeserta',
                'jadwal.Gambar',
                'jadwal.Deskripsi',
                'jadwal.gabungan'
            )
            ->join('detailkurikulum', 'detailkurikulum.ID', '=', 'jadwal.DetailKurikulumID')
            ->where('jadwal.Aktif', 'Ya');

        if (!empty($programID) && $programID != 'semua') {
            $query->where('jadwal.ProgramID', $programID);
        }
        if (!empty($tahunID) && $tahunID != 'semua') {
            $query->where('jadwal.TahunID', $tahunID);
        }
        if (!empty($prodiID) && $prodiID != 'semua') {
            $query->where('jadwal.ProdiID', $prodiID);
        }
        if ($dosenID) {
            $query->where(function ($q) use ($dosenID) {
                $q->where('jadwal.DosenID', $dosenID)
                  ->orWhereRaw("FIND_IN_SET(?, jadwal.DosenAnggota)", [$dosenID]);
            });
        }

        return $query->get()->toArray();
    }

    /**
     * Get jadwal gabungan
     */
    private function getListJadwalGabungan($tahunID)
    {
        return DB::table('jadwal')
            ->select('jadwal_gabungan.*')
            ->join('jadwal_gabungan', 'jadwal_gabungan.jadwalID', '=', 'jadwal.ID')
            ->where('jadwal.TahunID', $tahunID)
            ->get()
            ->toArray();
    }

    /**
     * Get jadwal gabungan by ID
     */
    private function getJadwalGabungan($jadwalID)
    {
        return DB::table('jadwal_gabungan')
            ->where('jadwalID', $jadwalID)
            ->first();
    }

    /**
     * Get peserta KRS
     */
    private function getPesertaKRS($listJadwal)
    {
        if (empty($listJadwal)) {
            return [];
        }

        return DB::table('rombel')
            ->select(
                'mahasiswa.ID as MhswID',
                'rencanastudi.ID as rencanastudiID',
                'nilai.ID as nilaiID',
                'mahasiswa.NPM as npm',
                'mahasiswa.Nama as nama',
                'mahasiswa.ProdiID as prodiID',
                'mahasiswa.JenjangID as jenjangID',
                'mahasiswa.TahunMasuk as tahunMasuk',
                'mahasiswa.KonsentrasiID as konsentrasiID',
                'kelas.Nama as namaKelas',
                'program.Nama as namaProgram',
                'programstudi.Nama as namaProdi',
                'jenjang.Nama as namaJenjang',
                'kurikulum.Nama as namaKurikulum',
                'rombel.JadwalID as jadwalID',
                'mahasiswa.BobotMasterID as bobotMasterID',
                DB::raw('MAX(nilai.NilaiAkhir) as akhirNilai'),
                DB::raw('MIN(nilai.NilaiHuruf) as hurufNilai'),
                'nilai.ValidasiDosen',
                'nilai.PublishKHS',
                'nilai.PublishTranskrip',
                'nilai.Lock'
            )
            ->join('peserta_rombel', 'rombel.ID', '=', 'peserta_rombel.GroupPesertaID')
            ->join('mahasiswa', 'mahasiswa.ID', '=', 'peserta_rombel.MhswID')
            ->join('program', 'program.ID', '=', 'mahasiswa.ProgramID')
            ->join('jenjang', 'jenjang.ID', '=', 'mahasiswa.JenjangID')
            ->join('programstudi', 'programstudi.ID', '=', 'mahasiswa.ProdiID')
            ->join('jadwal', 'jadwal.ID', '=', 'rombel.jadwalID')
            ->join('kelas', 'kelas.ID', '=', 'jadwal.KelasID')
            ->join('rencanastudi', function ($join) {
                $join->on('rencanastudi.MhswID', '=', 'mahasiswa.ID')
                     ->on('rencanastudi.JadwalID', '=', 'rombel.JadwalID');
            })
            ->join('detailkurikulum', 'detailkurikulum.ID', '=', 'rencanastudi.detailKurikulumID')
            ->join('kurikulum', 'kurikulum.ID', '=', 'detailkurikulum.KurikulumID')
            ->leftJoin('nilai', 'nilai.rencanastudiID', '=', 'rencanastudi.ID')
            ->where('rombel.default', '1')
            ->whereIn('rombel.JadwalID', $listJadwal)
            ->get()
            ->toArray();
    }

    /**
     * Get record by ID
     */
    public function getById($id)
    {
        return DB::table('jabatan')->where('ID', $id)->first();
    }

    /**
     * Check duplicate
     */
    public function checkDuplicate($kodeDikti, $singkatan, $excludeId = null)
    {
        $query = DB::table('jabatan')
            ->where(function ($q) use ($kodeDikti, $singkatan) {
                $q->where('KodeDikti', $kodeDikti)
                  ->orWhere('singkatan', $singkatan);
            });

        if ($excludeId) {
            $query->where('ID', '!=', $excludeId);
        }

        return $query->first();
    }

    /**
     * Create new record
     */
    public function create($data)
    {
        return DB::table('jabatan')->insertGetId([
            'Nama' => $data['Nama'],
            'singkatan' => $data['singkatan'],
            'KodeDikti' => $data['KodeDikti'],
            'Urut' => $data['Urut'] ?? null,
            'TunjanganFungsionalDosKar' => $data['TunjanganFungsionalDosKar'] ?? null,
            'TunjanganFungsionalDosSaja' => $data['TunjanganFungsionalDosSaja'] ?? null,
        ]);
    }

    /**
     * Update record
     */
    public function update($id, $data)
    {
        return DB::table('jabatan')->where('ID', $id)->update([
            'Nama' => $data['Nama'],
            'singkatan' => $data['singkatan'],
            'KodeDikti' => $data['KodeDikti'],
            'Urut' => $data['Urut'] ?? null,
            'TunjanganFungsionalDosKar' => $data['TunjanganFungsionalDosKar'] ?? null,
            'TunjanganFungsionalDosSaja' => $data['TunjanganFungsionalDosSaja'] ?? null,
        ]);
    }

    /**
     * Delete record
     */
    public function delete($id)
    {
        return DB::table('jabatan')->where('ID', $id)->delete();
    }

    /**
     * Get all data (for PDF export)
     */
    public function getData($programID = '', $prodiID = '', $keyword = '')
    {
        $query = DB::table('jabatan');

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('Nama', 'like', '%' . $keyword . '%')
                  ->orWhere('singkatan', 'like', '%' . $keyword . '%')
                  ->orWhere('KodeDikti', 'like', '%' . $keyword . '%');
            });
        }

        return $query->get()->toArray();
    }
}
