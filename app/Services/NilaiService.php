<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Nilai;
use App\Models\Jadwal;
use App\Models\BobotNilai;
use App\Models\BobotMahasiswa;
use App\Models\NilaiHistory;

class NilaiService
{
    /**
     * Get Jadwal data with filters for Grade Management
     */
    public function getDataJadwal($programID, $tahunID, $prodiID, $kurikulumID, $konsentrasiID, $kelasID, $semester, $keyword, $dosenID = '')
    {
        $query = DB::table('detailkurikulum')
            ->select(
                'detailkurikulum.ID as matkulID',
                'detailkurikulum.MKKode as mkkode',
                'detailkurikulum.Nama as namaMatkul',
                'detailkurikulum.TotalSKS as totalSKS',
                'detailkurikulum.KonsentrasiID as konsentrasiID',
                'detailkurikulum.KurikulumID as kurikulumID',
                'detailkurikulum.Semester as semester',
                'kelas.Nama as namaKelas',
                'kelas.ID as KelasID',
                'dosen.ID as DosenID',
                'dosen.Title as title',
                'dosen.Nama as namaDosen',
                'dosen.Gelar as gelar',
                'jadwal.DosenAnggota as dosenAnggota',
                'jadwal.JumlahPeserta as totalPeserta',
                'jadwal.ID as jadwalID',
                'jadwal.TahunID as tahunID',
                'jadwal.Aktif',
                'jadwal.Kunci',
                'detailkurikulum.ProdiID',
                'jadwal.gabungan'
            )
            ->join('jadwal', 'jadwal.DetailKurikulumID', '=', 'detailkurikulum.ID')
            ->leftJoin('dosen', 'dosen.ID', '=', 'jadwal.DosenID')
            ->leftJoin('kelas', 'jadwal.KelasID', '=', 'kelas.ID');

        if ($tahunID) $query->where('jadwal.TahunID', $tahunID);
        if ($programID) $query->where('detailkurikulum.ProgramID', $programID);
        if ($prodiID) $query->whereIn('detailkurikulum.ProdiID', explode(',', $prodiID));
        if ($kurikulumID) $query->where('detailkurikulum.KurikulumID', $kurikulumID);
        if ($konsentrasiID) $query->where('detailkurikulum.KonsentrasiID', $konsentrasiID);
        if ($kelasID) $query->where('jadwal.KelasID', $kelasID);
        if ($semester) $query->where('detailkurikulum.Semester', $semester);
        
        if ($dosenID) {
            $query->where(function($q) use ($dosenID) {
                $q->where('jadwal.DosenID', $dosenID)
                  ->orWhereRaw("FIND_IN_SET(?, DosenAnggota)", [$dosenID]);
            });
        }

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('detailkurikulum.MKKode', 'LIKE', "%$keyword%")
                  ->orWhere('detailkurikulum.Nama', 'LIKE', "%$keyword%");
            });
        }

        return $query->orderBy('detailkurikulum.ProdiID')
            ->orderBy('detailkurikulum.Semester')
            ->orderBy('detailkurikulum.MKKode')
            ->orderBy('kelas.Nama')
            ->get();
    }

    /**
     * Get list of combined schedules
     */
    public function getListJadwalGabungan($tahunID)
    {
        return DB::table('jadwal')
            ->select('jadwal_gabungan.*')
            ->join('jadwal_gabungan', 'jadwal_gabungan.jadwalID', '=', 'jadwal.ID')
            ->where('jadwal.TahunID', $tahunID)
            ->get();
    }

    /**
     * Get students (participants) for a specific schedule/course
     */
    public function getPesertaKRS($listJadwal = [], $detailKurikulumID = '', $tahunID = '')
    {
        $query = DB::table('rencanastudi')
            ->select(
                'mahasiswa.ID as MhswID',
                'rencanastudi.ID as rencanastudiID',
                'nilai.ID as nilaiID',
                'mahasiswa.NPM as npm',
                'mahasiswa.Nama as nama',
                'mahasiswa.ProdiID as prodiID',
                'mahasiswa.TahunMasuk as tahunMasuk',
                'mahasiswa.BobotMasterID as bobotMasterID',
                'nilai.NilaiAkhir as akhirNilai',
                'nilai.NilaiHuruf as hurufNilai',
                'nilai.ValidasiDosen',
                'nilai.PublishKHS',
                'nilai.PublishTranskrip',
                'nilai.Lock',
                'rencanastudi.JadwalID as jadwalID'
            )
            ->join('mahasiswa', 'mahasiswa.ID', '=', 'rencanastudi.MhswID')
            ->leftJoin('nilai', 'nilai.rencanastudiID', '=', 'rencanastudi.ID')
            ->leftJoin('rencanastudi_waiting', 'rencanastudi_waiting.rencanastudiID', '=', 'rencanastudi.ID')
            ->whereNull('rencanastudi_waiting.ID');

        if (!empty($listJadwal)) {
            $query->whereIn('rencanastudi.JadwalID', $listJadwal);
        }
        if ($detailKurikulumID) {
            $query->where('rencanastudi.DetailKurikulumID', $detailKurikulumID);
        }
        if ($tahunID) {
            $query->where('rencanastudi.TahunID', $tahunID);
        }

        return $query->groupBy('mahasiswa.ID')
            ->orderBy('mahasiswa.NPM', 'ASC')
            ->get();
    }

    /**
     * Get Grade weights for a specific course/schedule
     */
    public function getBobotNilai($matkulID, $prodiID, $tahunID, $jadwalID = '')
    {
        $query = DB::table('bobotnilai')
            ->select('bobotnilai.Persen', 'bobotnilai.JenisBobotID', 'jenisbobot.Nama as jenisnama', 'jenisbobot.Modify', 'jenisbobot.KategoriJenisBobotID')
            ->join('jenisbobot', 'jenisbobot.ID', '=', 'bobotnilai.JenisBobotID')
            ->where('bobotnilai.Persen', '>', 0)
            ->where('bobotnilai.ProdiID', $prodiID)
            ->where('bobotnilai.TahunID', $tahunID)
            ->where('bobotnilai.DetailKurikulumID', $matkulID);

        if ($jadwalID) {
            $query->where('bobotnilai.JadwalID', $jadwalID);
        }

        return $query->orderBy('jenisbobot.Urut', 'ASC')->get();
    }

    /**
     * Calculate Attendance Percentage for a student
     */
    public function getPersentasePresensi($mhswID, $jadwalID)
    {
        $totalPertemuan = DB::table('jadwalwaktu')
            ->where('JadwalID', $jadwalID)
            ->count();

        if ($totalPertemuan == 0) return 0;

        $sumNilaiPresensi = DB::table('presensimahasiswa')
            ->join('jenispresensi', 'jenispresensi.ID', '=', 'presensimahasiswa.JenisPresensiID')
            ->where('presensimahasiswa.MhswID', $mhswID)
            ->where('presensimahasiswa.JadwalID', $jadwalID)
            ->whereNotIn('presensimahasiswa.Pertemuan', [98, 99])
            ->sum('jenispresensi.Nilai');

        return round(($sumNilaiPresensi / $totalPertemuan) * 100);
    }

    /**
     * Save Grade Weightings
     */
    public function saveBobot($data)
    {
        $totalAffected = 0;
        $createAt = date('Y-m-d H:i:s');
        $userID = Session::get('UserID');

        foreach ($data['KategoriJenisBobotID'] as $KJBID) {
            if (isset($data['JenisBobotID'][$KJBID])) {
                foreach ($data['JenisBobotID'][$KJBID] as $jenisBobotID) {
                    $persen = $data['persen'][$KJBID][$jenisBobotID];

                    $cekData = DB::table('bobotnilai')
                        ->where('TahunID', $data['tahunID'])
                        ->where('DetailKurikulumID', $data['detailkurikulumID'])
                        ->where('JenisBobotID', $jenisBobotID)
                        ->where('JadwalID', $data['jadwalID'])
                        ->first();

                    $matkul = DB::table('detailkurikulum')->where('ID', $data['detailkurikulumID'])->first();

                    $values = [
                        'TahunID' => $data['tahunID'],
                        'ProdiID' => $matkul->ProdiID,
                        'KurikulumID' => $matkul->KurikulumID,
                        'DetailKurikulumID' => $data['detailkurikulumID'],
                        'JadwalID' => $data['jadwalID'],
                        'Nama' => $matkul->Nama,
                        'Persen' => $persen,
                        'NamaInggris' => $matkul->NamaInggris,
                        'JenisBobotID' => $jenisBobotID,
                        'userID' => $userID,
                        'updateAt' => $createAt
                    ];

                    if ($cekData) {
                        DB::table('bobotnilai')->where('ID', $cekData->ID)->update($values);
                    } else {
                        $values['createAt'] = $createAt;
                        DB::table('bobotnilai')->insert($values);
                    }
                    $totalAffected++;
                }
            }
        }
        return $totalAffected;
    }

    /**
     * Save Student Grades
     */
    public function saveNilai($post)
    {
        $listMahasiswa = $post['mhswID'];
        $tahunID = $post['tahunID'];
        $detailkurikulumID = $post['detailkurikulumID'];
        $userID = Session::get('UserID');
        $createAt = date('Y-m-d H:i:s');
        
        $matkul = DB::table('detailkurikulum')->where('ID', $detailkurikulumID)->first();
        $prodi = DB::table('programstudi')->where('ID', $matkul->ProdiID)->first();
        $kodeTahun = DB::table('tahun')->where('ID', $tahunID)->value('TahunID');

        $totalAffected = 0;

        foreach ($listMahasiswa as $index => $mhswID) {
            if (empty($post['typeEdit']) || (isset($post['checkID']) && in_array($mhswID, $post['checkID']))) {
                
                $NA = 0;
                // Process detailed weights (bobot_mahasiswa)
                if (isset($post['jenisBobot'][$mhswID])) {
                    foreach ($post['jenisBobot'][$mhswID] as $kategoriID => $jenisBobotArray) {
                        $NABobot = 0;
                        foreach ($jenisBobotArray as $z => $jenisBobotID) {
                            $nilai = $post['nilaiBobot'][$mhswID][$kategoriID][$z] ?? 0;
                            $persen = $post['persenBobot'][$mhswID][$kategoriID][$z] ?? 0;

                            $bobotMhsw = [
                                'MhswID' => $mhswID,
                                'DetailKurikulumID' => $detailkurikulumID,
                                'TahunID' => $tahunID,
                                'JenisBobotID' => $jenisBobotID,
                                'Nilai' => $nilai,
                                'Persen' => $persen,
                                'userID' => $userID,
                                'updateAt' => $createAt
                            ];

                            $cekBobot = DB::table('bobot_mahasiswa')
                                ->where('MhswID', $mhswID)
                                ->where('DetailKurikulumID', $detailkurikulumID)
                                ->where('TahunID', $tahunID)
                                ->where('JenisBobotID', $jenisBobotID)
                                ->first();

                            if ($cekBobot) {
                                DB::table('bobot_mahasiswa')->where('ID', $cekBobot->ID)->update($bobotMhsw);
                            } else {
                                $bobotMhsw['createAt'] = $createAt;
                                DB::table('bobot_mahasiswa')->insert($bobotMhsw);
                            }

                            $NABobot += round(($persen * $nilai) / 100, 2);
                        }
                        
                        // Handle multiple categories if needed (logic from legacy)
                        // This part needs adjustment based on how KategoriJenisBobot works
                        $NA += $NABobot; 
                    }
                }

                $finalHuruf = strtoupper(get_grade_return($mhswID, $NA));
                $mahasiswa = DB::table('mahasiswa')->where('ID', $mhswID)->first();
                $bobotAngka = get_bobot_angka($mhswID); // Helper usually returns array of [Grade => BobotObj]
                $currentBobot = $bobotAngka[$finalHuruf]->Bobot ?? 0;

                $nilaiData = [
                    'NilaiAkhir' => $NA,
                    'NilaiHuruf' => $finalHuruf,
                    'rencanastudiID' => $post['rencanastudiID'][$mhswID],
                    'MhswID' => $mhswID,
                    'NPM' => $mahasiswa->NPM,
                    'NamaMahasiswa' => $mahasiswa->Nama,
                    'ProgramID' => $matkul->ProgramID,
                    'ProdiID' => $prodi->ID,
                    'NamaProdi' => $prodi->Nama,
                    'NamaProgram' => DB::table('program')->where('ID', $matkul->ProgramID)->value('Nama'),
                    'TahunMasuk' => $mahasiswa->TahunMasuk,
                    'DetailKurikulumID' => $matkul->ID,
                    'MKKode' => $matkul->MKKode,
                    'NamaMataKuliah' => $matkul->Nama,
                    'TotalSKS' => $matkul->TotalSKS,
                    'SKSTatapMuka' => $matkul->SKSTatapMuka,
                    'SKSPraktikum' => $matkul->SKSPraktikum,
                    'SKSPraktekLap' => $matkul->SKSPraktekLap,
                    'Semester' => $matkul->Semester,
                    'Bobot' => $currentBobot,
                    'NilaiBobot' => $currentBobot * $matkul->TotalSKS,
                    'TahunID' => $tahunID,
                    'KodeTahun' => $kodeTahun,
                    'userID' => $userID,
                    'ValidasiDosen' => 1,
                    'updateAt' => $createAt
                ];

                $cekNilai = DB::table('nilai')->where('rencanastudiID', $nilaiData['rencanastudiID'])->first();

                if ($cekNilai) {
                    DB::table('nilai')->where('ID', $cekNilai->ID)->update($nilaiData);
                } else {
                    $nilaiData['createAt'] = $createAt;
                    DB::table('nilai')->insert($nilaiData);
                }

                // History
                DB::table('nilai_history')->insert([
                    'rencanastudiID' => $nilaiData['rencanastudiID'],
                    'NilaiHuruf' => $finalHuruf,
                    'NilaiAkhir' => $NA,
                    'userID' => $userID,
                    'Aksi' => $post['typeEdit'] ?? 'input',
                    'MhswID' => $mhswID,
                    'NPM' => $mahasiswa->NPM,
                    'MKKode' => $matkul->MKKode,
                    'detailkurikulumID' => $matkul->ID,
                    'createAt' => $createAt,
                    'updateAt' => $createAt
                ]);

                $totalAffected++;
            }
        }
        return $totalAffected;
    }
}
