<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use stdClass;

class PublishNilaiUasService
{
    public function countJadwalDosen($dosenID, $programID, $prodiID, $tahunID, $jadwalID, $MKID = [])
    {
        $query = DB::table('jadwal')
            ->join('detailkurikulum', 'detailkurikulum.ID', '=', 'jadwal.DetailKurikulumID');

        if (!empty($programID) && $programID != 'semua') {
            $query->where('jadwal.ProgramID', $programID);
        }
        if (!empty($tahunID) && $tahunID != 'semua') {
            $query->where('jadwal.TahunID', $tahunID);
        }
        if (!empty($jadwalID)) {
            $query->where('jadwal.ID', $jadwalID);
        }
        if (!empty($prodiID) && $prodiID != 'semua') {
            $query->where('jadwal.ProdiID', $prodiID);
        }
        if (!empty($MKID)) {
            $query->whereIn('detailkurikulum.ID', is_array($MKID) ? $MKID : [$MKID]);
        }
        if ($dosenID) {
            $query->where(function ($q) use ($dosenID) {
                $q->where('jadwal.DosenID', $dosenID)
                  ->orWhereRaw("FIND_IN_SET(?, DosenAnggota)", [$dosenID]);
            });
        }
        $query->where('jadwal.Aktif', 'Ya');

        return $query->count();
    }

    public function getJadwalDosen($dosenID, $programID, $prodiID, $tahunID, $jadwalID, $limit = 100000, $offset = 0, $MKID = [])
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
                'jadwal.gabungan',
                'jadwal.ID'
            )
            ->join('detailkurikulum', 'detailkurikulum.ID', '=', 'jadwal.DetailKurikulumID');

        if (!empty($programID) && $programID != 'semua') {
            $query->where('jadwal.ProgramID', $programID);
        }
        if (!empty($tahunID) && $tahunID != 'semua') {
            $query->where('jadwal.TahunID', $tahunID);
        }
        if (!empty($jadwalID)) {
            $query->where('jadwal.ID', $jadwalID);
        }
        if (!empty($prodiID) && $prodiID != 'semua') {
            $query->where('jadwal.ProdiID', $prodiID);
        }
        if (!empty($MKID)) {
            $query->whereIn('detailkurikulum.ID', is_array($MKID) ? $MKID : [$MKID]);
        }
        if ($dosenID) {
            $query->where(function ($q) use ($dosenID) {
                $q->where('jadwal.DosenID', $dosenID)
                  ->orWhereRaw("FIND_IN_SET(?, DosenAnggota)", [$dosenID]);
            });
        }
        $query->where('jadwal.Aktif', 'Ya');

        if ($limit) {
            $query->limit($limit)->offset($offset);
        }

        return $query->get()->map(fn($item) => (object) $item)->toArray();
    }

    public function getListJadwalGabungan($tahunID)
    {
        return DB::table('jadwal')
            ->select('jadwal_gabungan.*')
            ->join('jadwal_gabungan', 'jadwal_gabungan.jadwalID', '=', 'jadwal.ID')
            ->where('jadwal.TahunID', $tahunID)
            ->get();
    }

    public function checkJadwalGabungan($jadwalID)
    {
        return DB::table('jadwal_gabungan')
            ->select('id', 'jadwalID')
            ->where('jadwalID', $jadwalID)
            ->orWhereRaw("FIND_IN_SET(?, jadwalGabungan)", [$jadwalID])
            ->first();
    }

    public function getPesertaKRS($listJadwal = [], $valid = '', $DetailKurikulumID = '', $TahunID = '', $list_mhsw = [])
    {
        $query = DB::table('rombel');

        if (!empty($listJadwal)) {
            $query->select(
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
            ->join('detailkurikulum', 'detailkurikulum.ID', '=', 'rencanastudi.DetailKurikulumID')
            ->join('kurikulum', 'kurikulum.ID', '=', 'detailkurikulum.KurikulumID')
            ->leftJoin('nilai', 'nilai.rencanastudiID', '=', 'rencanastudi.ID')
            ->where('rombel.default', '1')
            ->whereIn('rombel.JadwalID', $listJadwal);
        } else {
            $query->from('rencanastudi')
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
                    'rencanastudi.JadwalID as jadwalID',
                    'mahasiswa.BobotMasterID as bobotMasterID',
                    DB::raw('MAX(nilai.NilaiAkhir) as akhirNilai'),
                    DB::raw('MIN(nilai.NilaiHuruf) as hurufNilai'),
                    'nilai.PublishKHS',
                    'nilai.PublishTranskrip',
                    'nilai.ValidasiDosen',
                    'nilai.Lock'
                )
                ->join('mahasiswa', 'mahasiswa.ID', '=', 'rencanastudi.MhswID')
                ->join('program', 'program.ID', '=', 'mahasiswa.ProgramID')
                ->join('jenjang', 'jenjang.ID', '=', 'mahasiswa.JenjangID')
                ->join('programstudi', 'programstudi.ID', '=', 'mahasiswa.ProdiID')
                ->join('kurikulum', 'kurikulum.ID', '=', 'mahasiswa.KurikulumID')
                ->leftJoin('kelas', 'kelas.ID', '=', 'mahasiswa.KelasID')
                ->leftJoin('nilai', 'nilai.rencanastudiID', '=', 'rencanastudi.ID')
                ->leftJoin('rencanastudi_waiting', 'rencanastudi_waiting.rencanastudiID', '=', 'rencanastudi.ID')
                ->whereNull('rencanastudi_waiting.ID');
        }

        if ($DetailKurikulumID) {
            $query->where('rencanastudi.DetailKurikulumID', $DetailKurikulumID);
        }

        if ($TahunID) {
            $query->where('rencanastudi.TahunID', $TahunID);
        }

        if (is_array($list_mhsw) && count($list_mhsw) > 0) {
            $query->whereIn('rencanastudi.MhswID', $list_mhsw);
        }

        return $query->groupBy('mahasiswa.ID')
            ->orderBy('mahasiswa.NPM', 'ASC')
            ->get();
    }

    public function getBobotMahasiswaCount($listJadwalID)
    {
        return DB::table('rencanastudi')
            ->select(
                'rencanastudi.MhswID',
                'rencanastudi.JadwalID',
                'nilai.ID AS NilaiID',
                'nilai.PublishKHS',
                'nilai.PublishTranskrip',
                'nilai.ValidasiDosen'
            )
            ->join('rombel', 'rombel.JadwalID', '=', 'rencanastudi.JadwalID')
            ->join('peserta_rombel', function ($join) {
                $join->on('peserta_rombel.GroupPesertaID', '=', 'rombel.ID')
                     ->on('peserta_rombel.MhswID', '=', 'rencanastudi.MhswID');
            })
            ->leftJoin('nilai', 'nilai.rencanastudiID', '=', 'rencanastudi.ID')
            ->whereIn('rencanastudi.JadwalID', $listJadwalID)
            ->groupBy('rencanastudi.MhswID', 'rencanastudi.JadwalID')
            ->get();
    }

    public function getDosenList()
    {
        return DB::table('dosen')
            ->select('dosen.ID', 'dosen.NIDN', 'dosen.Nama', 'dosen.Title', 'dosen.Gelar')
            ->join('jadwal', function($join) {
                $join->on('jadwal.DosenID', '=', 'dosen.ID')
                     ->orWhereRaw("FIND_IN_SET(dosen.ID, jadwal.DosenAnggota)");
            })
            ->whereNotNull('dosen.Nama')
            ->where('dosen.Nama', '!=', '')
            ->where('dosen.Nama', '!=', '-')
            ->groupBy('dosen.ID')
            ->orderBy('dosen.Nama', 'ASC')
            ->get();
    }

    public function getKategoriJenisBobot($skstetori, $skspraktik)
    {
        $kategoriJenisBobot = 0;
        if (empty($skspraktik) && $skstetori > 0) {
            $kategoriJenisBobot = 1;
        } elseif (empty($skstetori) && $skspraktik > 0) {
            $kategoriJenisBobot = 2;
        }

        $query = DB::table('kategori_jenisbobot');
        if (!empty($kategoriJenisBobot)) {
            $query->where('ID', $kategoriJenisBobot);
        }
        return $query->get();
    }

    public function getBobotNilai($jadwalID, $kategoriIDs)
    {
        return DB::table('bobotnilai')
            ->select('bobotnilai.Persen', 'bobotnilai.JenisBobotID', 'jenisbobot.Nama as jenisnama', 'jenisbobot.Modify', 'jenisbobot.KategoriJenisBobotID')
            ->join('jenisbobot', 'jenisbobot.ID', '=', 'bobotnilai.JenisBobotID')
            ->where('bobotnilai.Persen', '>', '0')
            ->where('bobotnilai.JadwalID', $jadwalID)
            ->whereIn('jenisbobot.KategoriJenisBobotID', $kategoriIDs)
            ->groupBy('bobotnilai.JenisBobotID')
            ->orderBy('jenisbobot.Urut', 'ASC')
            ->get();
    }

    public function getBobotMahasiswaAll($mhswIDs, $detailKurikulumID, $tahunID, $jenisBobotIDs)
    {
        $data = DB::table('bobot_mahasiswa')
            ->whereIn('MhswID', $mhswIDs)
            ->where('DetailKurikulumID', $detailKurikulumID)
            ->where('TahunID', $tahunID)
            ->whereIn('JenisBobotID', $jenisBobotIDs)
            ->get();

        $result = [];
        foreach ($data as $row) {
            $result[$row->MhswID][$row->JenisBobotID] = $row;
        }
        return $result;
    }

    public function getPresensiMahasiswaCount($mhswIDs, $jadwalID)
    {
        // Logic from CI3: SELECT SUM(b.Nilai) as s FROM presensimahasiswa a,jenispresensi b WHERE a.MhswID='$row->MhswID' AND a.JadwalID='$JadwalID' AND a.JenisPresensiID=b.ID
        $sum = DB::table('presensimahasiswa as a')
            ->join('jenispresensi as b', 'a.JenisPresensiID', '=', 'b.ID')
            ->select('a.MhswID', DB::raw('SUM(b.Nilai) as s'))
            ->whereIn('a.MhswID', $mhswIDs)
            ->where('a.JadwalID', $jadwalID)
            ->groupBy('a.MhswID')
            ->get()
            ->pluck('s', 'MhswID');

        // Logic from CI3: SELECT * FROM presensimahasiswa WHERE MhswID='$row->MhswID' AND JadwalID='$JadwalID'
        $count = DB::table('presensimahasiswa')
            ->select('MhswID', DB::raw('COUNT(*) as total'))
            ->whereIn('MhswID', $mhswIDs)
            ->where('JadwalID', $jadwalID)
            ->groupBy('MhswID')
            ->get()
            ->pluck('total', 'MhswID');

        return [
            'sum' => $sum,
            'total' => $count
        ];
    }
}
