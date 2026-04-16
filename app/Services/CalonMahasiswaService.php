<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Service untuk Calon Mahasiswa PMB
 * Handles calon mahasiswa (pendaftar) management
 */
class CalonMahasiswaService
{
    private string $table = 'mahasiswa';
    private string $pk = 'ID';

    /**
     * Get calon mahasiswa dengan filter kompleks
     * 
     * @param array $filters
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function get_data(array $filters = [], ?int $limit = null, ?int $offset = null): array
    {
        try {
            $query = DB::table($this->table)
                ->select('mahasiswa.*')
                ->leftJoin('pmb_tbl_gelombang_detail', 'mahasiswa.gelombang_detail_pmb', '=', 'pmb_tbl_gelombang_detail.id')
                ->leftJoin('pmb_tbl_gelombang', 'pmb_tbl_gelombang_detail.gelombang_id', '=', 'pmb_tbl_gelombang.id');

            // Apply filters
            if (!empty($filters['gelombang'])) {
                $query->where('pmb_tbl_gelombang.ID', $filters['gelombang']);
            }

            if (!empty($filters['gelombang_detail'])) {
                $query->where('mahasiswa.gelombang_detail_pmb', $filters['gelombang_detail']);
            }

            if (!empty($filters['jalur_pendaftaran'])) {
                $query->where('mahasiswa.jalur_pmb', $filters['jalur_pendaftaran']);
            }

            if (!empty($filters['program'])) {
                $query->where('mahasiswa.ProgramID', $filters['program']);
            }

            if (!empty($filters['pilihan1'])) {
                $query->where('mahasiswa.pilihan1', $filters['pilihan1']);
            }

            if (!empty($filters['bayar'])) {
                $query->where('mahasiswa.statusbayar_pmb', $filters['bayar']);
            }

            if (!empty($filters['keyword'])) {
                $query->where(function($q) use ($filters) {
                    $q->where('mahasiswa.Nama', 'LIKE', "%{$filters['keyword']}%")
                      ->orWhere('mahasiswa.noujian_pmb', 'LIKE', "%{$filters['keyword']}%");
                });
            }

            $query->orderBy('mahasiswa.Nama', 'ASC');

            if ($limit !== null) {
                $query->limit($limit);
            }
            if ($offset !== null) {
                $query->offset($offset);
            }

            return $query->get()
                ->map(fn($item) => (array) $item)
                ->toArray();
        } catch (Exception $e) {
            \Log::error('CalonMahasiswaService::get_data - Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Count total calon mahasiswa dengan filter
     * 
     * @param array $filters
     * @return int
     */
    public function count_all(array $filters = []): int
    {
        try {
            $query = DB::table($this->table)
                ->leftJoin('pmb_tbl_gelombang_detail', 'mahasiswa.gelombang_detail_pmb', '=', 'pmb_tbl_gelombang_detail.id')
                ->leftJoin('pmb_tbl_gelombang', 'pmb_tbl_gelombang_detail.gelombang_id', '=', 'pmb_tbl_gelombang.id');

            // Apply filters
            if (!empty($filters['gelombang'])) {
                $query->where('pmb_tbl_gelombang.ID', $filters['gelombang']);
            }

            if (!empty($filters['gelombang_detail'])) {
                $query->where('mahasiswa.gelombang_detail_pmb', $filters['gelombang_detail']);
            }

            if (!empty($filters['jalur_pendaftaran'])) {
                $query->where('mahasiswa.jalur_pmb', $filters['jalur_pendaftaran']);
            }

            if (!empty($filters['program'])) {
                $query->where('mahasiswa.ProgramID', $filters['program']);
            }

            if (!empty($filters['pilihan1'])) {
                $query->where('mahasiswa.pilihan1', $filters['pilihan1']);
            }

            if (!empty($filters['bayar'])) {
                $query->where('mahasiswa.statusbayar_pmb', $filters['bayar']);
            }

            if (!empty($filters['keyword'])) {
                $query->where(function($q) use ($filters) {
                    $q->where('mahasiswa.Nama', 'LIKE', "%{$filters['keyword']}%")
                      ->orWhere('mahasiswa.noujian_pmb', 'LIKE', "%{$filters['keyword']}%");
                });
            }

            return $query->count();
        } catch (Exception $e) {
            \Log::error('CalonMahasiswaService::count_all - Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get single calon mahasiswa by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function get_id(int $id): ?array
    {
        try {
            $result = DB::table($this->table)->where($this->pk, $id)->first();
            return $result ? (array) $result : null;
        } catch (Exception $e) {
            \Log::error('CalonMahasiswaService::get_id - Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Insert new calon mahasiswa
     * 
     * @param array $data
     * @return int Insert ID
     */
    public function add(array $data): int
    {
        return DB::table($this->table)->insertGetId($data);
    }

    /**
     * Update existing calon mahasiswa
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function edit(int $id, array $data): bool
    {
        return (bool) DB::table($this->table)
            ->where($this->pk, $id)
            ->update($data);
    }

    /**
     * Delete calon mahasiswa
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return (bool) DB::table($this->table)->where($this->pk, $id)->delete();
    }

    /**
     * Get format nomor pendaftaran
     *
     * @return string|null
     */
    public function getFormatNomor(): ?string
    {
        try {
            return DB::table('pmb_tbl_format_pmb')->value('nomor_pmb');
        } catch (Exception $e) {
            \Log::error('CalonMahasiswaService::getFormatNomor - Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get data lengkap calon mahasiswa dengan semua informasi tambahan
     * (kelengkapan data, persyaratan, konfirmasi bayar, dll)
     *
     * @param string $whr WHERE clause untuk filter
     * @param int $bayar Status bayar (0/1)
     * @param string $orderby Order by clause
     * @return array
     */
    public function getMahasiswaPMB(string $whr = '', int $bayar = 0, string $orderby = ''): array
    {
        try {
            $wbayar = '';
            if ($bayar !== '') {
                $wbayar = " and statusbayar_pmb='$bayar' ";
            }

            $query = DB::select("
                SELECT mahasiswa.*,
                    program.Nama as programNama,
                    programstudi.Nama as prodiNama,
                    pmb_tbl_gelombang.nama as gelombangNama,
                    agama.Nama as agamaNama,
                    statussipil.Nama as statussipilNama
                FROM mahasiswa
                INNER JOIN program ON program.ID=mahasiswa.ProgramID
                INNER JOIN programstudi ON programstudi.ID=mahasiswa.pilihan1
                LEFT JOIN agama ON agama.ID=mahasiswa.AgamaID
                LEFT JOIN statussipil ON statussipil.Kode=mahasiswa.StatusSipil
                INNER JOIN pmb_tbl_gelombang_detail ON pmb_tbl_gelombang_detail.id=mahasiswa.gelombang_detail_pmb
                INNER JOIN pmb_tbl_gelombang ON pmb_tbl_gelombang_detail.gelombang_id=pmb_tbl_gelombang.id
                WHERE (mahasiswa.jenis_mhsw='calon' OR mahasiswa.statuslulus_pmb='1')
                $whr $wbayar $orderby
            ");

            // Convert to array
            $result = array_map(function($item) {
                return (array) $item;
            }, $query);

            return $result;

        } catch (Exception $e) {
            \Log::error('CalonMahasiswaService::getMahasiswaPMB - Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Count total calon mahasiswa dengan filter
     *
     * @param string $whr WHERE clause
     * @param int $bayar Status bayar
     * @param string $orderby Order by
     * @return int
     */
    public function countVerifikasiPMB(string $whr = '', int $bayar = 0, string $orderby = ''): int
    {
        try {
            $wbayar = '';
            if ($bayar !== '') {
                $wbayar = " and mahasiswa.statusbayar_pmb='$bayar' ";
            }

            $result = DB::select("
                SELECT COUNT(mahasiswa.ID) as c
                FROM mahasiswa
                INNER JOIN pmb_tbl_gelombang_detail ON mahasiswa.gelombang_detail_pmb=pmb_tbl_gelombang_detail.id
                INNER JOIN pmb_tbl_gelombang ON pmb_tbl_gelombang_detail.gelombang_id=pmb_tbl_gelombang.id
                WHERE (mahasiswa.jenis_mhsw='calon' OR mahasiswa.statuslulus_pmb='1')
                $whr $wbayar $orderby
            ");

            return $result[0]->c ?? 0;

        } catch (Exception $e) {
            \Log::error('CalonMahasiswaService::countVerifikasiPMB - Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get data calon mahasiswa lengkap dengan semua informasi untuk view
     *
     * @param array $filters
     * @param int $bayar
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function getDataLengkap(array $filters = [], int $bayar = 0, ?int $limit = null, ?int $offset = null): array
    {
        try {
            // Build WHERE clause
            $whr = $this->buildWhereClause($filters);

            // Build ORDER BY - same as CI3
            if ($bayar == 1) {
                $orderby = "order by mahasiswa.urutall_pmb DESC, mahasiswa.noujian_pmb DESC";
            } else {
                $orderby = "order by mahasiswa.ID desc";
            }

            // Add LIMIT if provided
            if ($limit !== null && $offset !== null) {
                $orderby .= " limit $offset, $limit";
            }

            // Get data
            $query = $this->getMahasiswaPMB($whr, $bayar, $orderby);

            // Process each row with additional data
            $processedQuery = [];
            $arrKelengkapan = $this->getArrKelengkapan();

            foreach ($query as $row) {
                $row = (array) $row;

                // Get data Ayah
                $ayah = DB::table('ortu')
                    ->where(['MhswID' => $row['ID'], 'Keterangan' => 'Ayah'])
                    ->first();
                if ($ayah) {
                    foreach ((array) $ayah as $key => $val) {
                        $row["Ayah_{$key}"] = $val;
                    }
                }

                // Get data Ibu
                $ibu = DB::table('ortu')
                    ->where(['MhswID' => $row['ID'], 'Keterangan' => 'Ibu'])
                    ->first();
                if ($ibu) {
                    foreach ((array) $ibu as $key => $val) {
                        $row["Ibu_{$key}"] = $val;
                    }
                }

                // Get wilayah sekolah
                if (!empty($row['KecamatanSekolah'])) {
                    $row['ProvinsiSekolah'] = '';
                    $row['KotaSekolah'] = '';
                } else {
                    $row['ProvinsiSekolah'] = '';
                    $row['KotaSekolah'] = '';
                }

                // Calculate kelengkapan data
                $kelengkapanData = 0;
                $maxKelengkapanData = count($arrKelengkapan);
                $listBelumLengkap = [];

                foreach ($arrKelengkapan as $key => $val) {
                    $explVal = explode('~', $val);
                    if (!empty($row[$key])) {
                        $kelengkapanData += (int)($explVal[0]);
                    } else {
                        $listBelumLengkap[] = $explVal[1];
                    }
                }

                $row['persentase_kelengkapan_data'] = $maxKelengkapanData > 0
                    ? number_format(($kelengkapanData / $maxKelengkapanData) * 100, 0, '.', '.')
                    : 0;
                $row['list_belum_lengkap'] = $listBelumLengkap;

                // Get metode pembayaran
                $channelPembayaran = $row['channel_pembayaran_formulir_pmb'] ?? null;
                $row['metode_pembayaran'] = null; // Default value
                if ($channelPembayaran) {
                    $row['metode_pembayaran'] = DB::table('channel_pembayaran')
                        ->where('ID', $channelPembayaran)
                        ->value('MetodePembayaranID');
                }

                // Get konfirmasi bayar
                $konfirmasi = DB::table('pmb_tbl_konfirmasi_bayar')
                    ->where('pendaftaranid', $row['ID'])
                    ->get();

                $dataKonfirmasi = [];
                foreach ($konfirmasi as $rowKonf) {
                    $dataKonfirmasi[$rowKonf->id] = [
                        'id' => $rowKonf->id,
                        'nama' => $rowKonf->nama,
                        'nomor' => $rowKonf->nomor,
                        'bank' => $rowKonf->bank,
                        'tanggal' => date('d/m/Y', strtotime($rowKonf->tanggal)),
                        'tanggal_transfer' => date('d/m/Y', strtotime($rowKonf->tanggal)),
                        'jumlah' => $rowKonf->jumlah,
                        'status' => $rowKonf->status,
                        'fileasli' => $rowKonf->file,
                        'file' => url('pmb/' . $row['ID'] . '/document/lainnya/' . $rowKonf->file)
                    ];
                }

                $row['jml_konfirmasi'] = count($konfirmasi);
                $row['data_konfirmasi'] = $dataKonfirmasi;

                // Get nama user yang input
                $userid = $row['userid_pmb'];
                if ($userid == 999) {
                    $row['namauser'] = 'Daftar Online';
                } else {
                    $user = DB::table('user')->where('ID', $userid)->value('Nama');
                    $row['namauser'] = $user ?? '-';
                }

                // Get nama agent
                $koderef = $row['kode_referal_pmb'] ?? '';
                if ($koderef != '') {
                    $agent = DB::table('pmb_tbl_agent')
                        ->where('kode_referal', $koderef)
                        ->value('nama');
                    $row['nama_agent'] = $agent ?? '-';
                } else {
                    $row['nama_agent'] = '-';
                }

                // Status ujian
                $ujian = $row['ujian_online_pmb'] ?? '';
                $ikutUjian = $row['ikut_ujian_pmb'] ?? '';

                if ($ujian == "1") {
                    $row['ujian_str'] = "<label style='margin:0 !important;cursor: pointer;' class='badge badge-success'>Bisa Ujian</label>";
                } elseif ($ujian == "2") {
                    $row['ujian_str'] = "<label style='margin:0 !important;cursor: pointer;' class='badge badge-danger'>Tidak Bisa Ujian</label>";
                } else {
                    $row['ujian_str'] = "<label style='margin:0 !important;cursor: pointer;' class='badge badge-secondary'>Belum Diset</label>";
                }

                if ($ikutUjian == "1") {
                    $row['ikut_ujian_str'] = "<label style='margin:0 !important;cursor: pointer;' class='badge badge-success'>Ikut Ujian</label>";
                } elseif ($ikutUjian == "2") {
                    $row['ikut_ujian_str'] = "<label style='margin:0 !important;cursor: pointer;' class='badge badge-danger'>Tidak Ikut Ujian</label>";
                } else {
                    $row['ikut_ujian_str'] = "<label style='margin:0 !important;cursor: pointer;' class='badge badge-secondary'>Belum Diset</label>";
                }

                $row['password_ujian'] = $row['password_ujian_online_pmb'] ?? '';

                // Get persyaratan
                $syaratTerpenuhi = 0;
                $totalPersyaratan = 0;
                $datasyarat = [];
                $jumlahSyaratUpload = [];

                $listSyarat = DB::table('pmb_edu_syarat')
                    ->whereRaw("FIND_IN_SET('{$row['jalur_pmb']}', jalur_pendaftaran)")
                    ->where('tipe', 'umum')
                    ->get();

                foreach ($listSyarat as $rowSyarat) {
                    $fileSyarat = DB::table('pmb_edu_file_syarat')
                        ->where(['idsyarat' => $rowSyarat->id, 'idpendaftaran' => $row['ID']])
                        ->first();

                    $namafile = $fileSyarat->namafile ?? '';
                    $lokasifile = url('pmb/' . $row['ID'] . '/document/lainnya/' . $namafile);

                    if ($namafile != '' && $namafile != null) {
                        $syaratTerpenuhi++;
                    }

                    $datasyarat[$row['ID']][$rowSyarat->id] = [
                        'namafile' => $namafile,
                        'syarat' => $rowSyarat->nama,
                        'link' => $lokasifile
                    ];

                    if (!empty($namafile)) {
                        $jumlahSyaratUpload[$row['ID']] = ($jumlahSyaratUpload[$row['ID']] ?? 0) + 1;
                    }

                    $totalPersyaratan++;
                }

                $row['persentase_kelengkapan_dokumen_persyaratan'] = $totalPersyaratan > 0
                    ? ($syaratTerpenuhi / $totalPersyaratan) * 100
                    : 0;
                $row['datasyarat'] = $datasyarat;
                $row['jumlahsyaratupload'] = $jumlahSyaratUpload;

                $processedQuery[] = $row;
            }

            return $processedQuery;

        } catch (Exception $e) {
            \Log::error('CalonMahasiswaService::getDataLengkap - Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Build WHERE clause from filters
     */
    private function buildWhereClause(array $filters): string
    {
        $whr = '';

        if (!empty($filters['gelombang'])) {
            $whr .= " and pmb_tbl_gelombang.ID='" . $filters['gelombang'] . "' ";
        }

        if (!empty($filters['gelombang_detail'])) {
            $whr .= " and mahasiswa.gelombang_detail_pmb='" . $filters['gelombang_detail'] . "'";
        }

        if (!empty($filters['jenis_pendaftaran'])) {
            $whr .= " and mahasiswa.StatusPindahan='" . $filters['jenis_pendaftaran'] . "'";
        }

        if (!empty($filters['jalur_pendaftaran'])) {
            $whr .= " and mahasiswa.jalur_pmb='" . $filters['jalur_pendaftaran'] . "'";
        }

        if (!empty($filters['program'])) {
            $whr .= " and mahasiswa.ProgramID='" . $filters['program'] . "'";
        }

        if (!empty($filters['pilihan1'])) {
            $whr .= " and mahasiswa.pilihan1='" . $filters['pilihan1'] . "'";
        }

        if (!empty($filters['pilihan2'])) {
            $whr .= " and mahasiswa.pilihan2='" . $filters['pilihan2'] . "'";
        }

        if (isset($filters['ujian']) && $filters['ujian'] !== '') {
            if ($filters['ujian'] == '1') {
                $whr .= " and mahasiswa.ujian_online_pmb='1'";
            } else {
                $whr .= " and mahasiswa.ujian_online_pmb !='1' ";
            }
        }

        if (isset($filters['ikut_ujian']) && $filters['ikut_ujian'] !== '') {
            if ($filters['ikut_ujian'] == '1') {
                $whr .= " and mahasiswa.ikut_ujian_pmb='1'";
            } else {
                $whr .= " and mahasiswa.ikut_ujian_pmb !='1' ";
            }
        }

        if (!empty($filters['keyword'])) {
            $keyword = DB::getPdo()->quote('%' . $filters['keyword'] . '%');
            $whr .= " and (mahasiswa.noujian_pmb like $keyword OR mahasiswa.Nama like $keyword OR mahasiswa.Email like $keyword)";
        }

        return $whr;
    }

    /**
     * Get array kelengkapan data calon mahasiswa
     */
    private function getArrKelengkapan(): array
    {
        return [
            "ProgramID" => "1~Program",
            "jalur_pmb" => "1~Jalur Pendaftar",
            "gelombang_detail_pmb" => "1~Gelombang",
            "JenisSekolahID" => "1~Pendidikan Terakhir",
            "JurusanSekolahID" => "1~Tipe Lulusan",
            "pilihan1" => "1~Pilihan Prodi",
            "ref_daftar" => "1~Mengetahui Dari?",
            "Nama" => "1~Nama Lengkap",
            "NoIdentitas" => "1~No KTP/NIK",
            "foto_pmb" => "1~Foto",
            "TempatLahir" => "1~Tempat Lahir",
            "TanggalLahir" => "1~Tanggal Lahir",
            "Kelamin" => "1~Jenis Kelamin",
            "TinggiBadan" => "1~TinggiBadan",
            "Berat" => "1~Berat Badan",
            "AgamaID" => "1~Agama",
            "Kewarganegaraan" => "1~Kewarganegaraan",
            "StatusSipil" => "1~Status Sipil",
            "Alamat" => "1~Alamat",
            "PropinsiID" => "1~Provinsi",
            "KotaID" => "1~Kota",
            "KecamatanID" => "1~Kecamatan",
            "Kelurahan" => "1~Kelurahan",
            "Dusun" => "1~Dusun",
            "RT" => "1~RT",
            "RW" => "1~RW",
            "KodePos" => "1~Kode Pos",
            "HP" => "1~HP",
            "Ayah_Nama" => "1~Nama Ayah Kandung",
            "Ayah_TempatLahir" => "1~Tempat Lahir Ayah",
            "Ayah_TanggalLahir" => "1~Tanggal Lahir Ayah",
            "Ayah_AgamaID" => "1~Agama Ayah",
            "Ayah_Alamat" => "1~Alamat Ayah",
            "Ayah_PropinsiID" => "1~Provinsi Ayah",
            "Ayah_KotaID" => "1~Kota Ayah",
            "Ayah_KecamatanID" => "1~Kecamatan Ayah",
            "Ayah_KodePos" => "1~Kode Pos Ayah",
            "Ayah_HP" => "1~HP Ayah",
            "Ayah_Telepon" => "1~Telepon Ayah",
            "Ayah_JenisSekolahID" => "1~Pendidikan Terakhir Ayah",
            "Ayah_PekerjaanID" => "1~Pekerjaan Ayah",
            "Ayah_PenghasilanID" => "1~Penghasilan Ayah",
            "Ayah_NamaInstansi" => "1~Nama Instansi Ayah",
            "Ayah_AlamatInstansi" => "1~Alamat Instansi Ayah",
            "Ibu_Nama" => "1~Nama Ibu Kandung",
            "Ibu_PropinsiID" => "1~Propinsi Ibu",
            "Ibu_KotaID" => "1~Kota Ibu",
            "Ibu_KecamatanID" => "1~Kecamatan Ibu",
            "Ibu_KodePos" => "1~Kode Pos Ibu",
            "Ibu_Kelurahan" => "1~Kelurahan Ibu",
            "StatusPindahan" => "1~Status Mahasiswa",
            "SekolahID" => "1~Nama Sekolah / Kampus",
            "AsalNIM" => "1~NIS / NIM",
            "AlamatSekolah" => "1~Alamat Sekolah",
            "ProvinsiSekolah" => "1~Provinsi Sekolah",
            "KotaSekolah" => "1~Kota Sekolah",
            "KecamatanSekolah" => "1~Kecamatan Sekolah",
            "KodePosSekolah" => "1~Kode Pos Sekolah",
            "TeleponSekolah" => "1~Telepon Sekolah",
            "TahunLulus" => "1~Tahun Lulus",
            "NoIjazah" => "1~No Ijazah",
            "Nilaiunas" => "1~Nilai IPK / Rata-Rata",
            "UkuranAlmamater" => "1~Ukuran Almamater",
            "AlamatSuratMenyurat" => "1~Alamat Surat Menyurat"
        ];
    }
}
