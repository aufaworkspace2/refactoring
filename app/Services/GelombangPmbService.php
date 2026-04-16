<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class GelombangPmbService
{
    private $table = 'pmb_tbl_gelombang';
    private $table2 = 'pmb_tbl_gelombang_detail';
    private $pk = 'id';

    /**
     * Get data with pagination and filters
     */
    public function get_data($limit, $offset, $keyword = '')
    {
        $query = DB::table($this->table)
            ->select("$this->table.*", DB::raw("count($this->table2.id) as PendaftaranTerbuka"));

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where("$this->table.nama", 'LIKE', "%{$keyword}%")
                  ->orWhere("$this->table.kode", 'LIKE', "%{$keyword}%");
            });
        }

        $query->leftJoin($this->table2, function ($join) {
            $join->on("$this->table.id", '=', "$this->table2.gelombang_id")
                 ->whereBetween(DB::raw('current_date()'), ["$this->table2.date_start", "$this->table2.date_end"]);
        })
        ->groupBy("$this->table.id")
        ->orderBy('Nama', 'ASC');

        if ($limit !== null) {
            $query->limit($limit);
        }
        if ($offset !== null) {
            $query->offset($offset);
        }

        return $query->get()->map(fn($item) => (array) $item)->toArray();
    }

    /**
     * Count all total row data
     */
    public function count_all($keyword = '')
    {
        $query = DB::table($this->table);

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'LIKE', "%{$keyword}%")
                  ->orWhere('kode', 'LIKE', "%{$keyword}%");
            });
        }

        return $query->count();
    }

    /**
     * Get data detail with pagination and filters
     */
    public function get_data_detail($limit, $offset, $gelombang_id = '', $keyword = '')
    {
        $query = DB::table('pmb_tbl_gelombang_detail')
            ->select('pmb_tbl_gelombang_detail.*')
            ->leftJoin('pmb_pilihan_pendaftaran', 'pmb_pilihan_pendaftaran.id', '=', 'pmb_tbl_gelombang_detail.pilihan_pendaftaran_id')
            ->leftJoin('program', 'program.ID', '=', 'pmb_tbl_gelombang_detail.program_id')
            ->leftJoin('programstudi', 'programstudi.ID', '=', 'pmb_tbl_gelombang_detail.prodi_id');

        if ($gelombang_id) {
            $query->where('gelombang_id', $gelombang_id);
        }

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('program.Nama', 'LIKE', "%{$keyword}%")
                  ->orWhere('programstudi.Nama', 'LIKE', "%{$keyword}%")
                  ->orWhere('pmb_pilihan_pendaftaran.nama', 'LIKE', "%{$keyword}%");
            });
        }

        $query->orderBy('pmb_tbl_gelombang_detail.id', 'ASC');

        if ($limit !== null) {
            $query->limit($limit);
        }
        if ($offset !== null) {
            $query->offset($offset);
        }

        return $query->get()->map(fn($item) => (array) $item)->toArray();
    }

    /**
     * Count all detail row data
     */
    public function count_all_detail($gelombang_id = '', $keyword = '')
    {
        $query = DB::table('pmb_tbl_gelombang_detail')
            ->leftJoin('pmb_pilihan_pendaftaran', 'pmb_pilihan_pendaftaran.id', '=', 'pmb_tbl_gelombang_detail.pilihan_pendaftaran_id')
            ->leftJoin('program', 'program.ID', '=', 'pmb_tbl_gelombang_detail.program_id')
            ->leftJoin('programstudi', 'programstudi.ID', '=', 'pmb_tbl_gelombang_detail.prodi_id');

        if ($gelombang_id) {
            $query->where('gelombang_id', $gelombang_id);
        }

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('program.Nama', 'LIKE', "%{$keyword}%")
                  ->orWhere('programstudi.Nama', 'LIKE', "%{$keyword}%")
                  ->orWhere('pmb_pilihan_pendaftaran.nama', 'LIKE', "%{$keyword}%");
            });
        }

        return $query->count();
    }

    /**
     * Get data with id
     */
    public function get_id($id)
    {
        $result = DB::table($this->table)
            ->where($this->pk, $id)
            ->first();

        return $result ? (array) $result : null;
    }

    /**
     * Get detail data with id
     */
    public function get_id_detail($id)
    {
        $result = DB::table($this->table2)
            ->where($this->pk, $id)
            ->first();

        return $result ? (array) $result : null;
    }

    /**
     * Add data
     */
    public function add($data)
    {
        return DB::table($this->table)->insertGetId($data);
    }

    /**
     * Add detail data
     */
    public function add_detail($data)
    {
        return DB::table($this->table2)->insertGetId($data);
    }

    /**
     * Edit data
     */
    public function edit($id, $data)
    {
        return DB::table($this->table)
            ->where($this->pk, $id)
            ->update($data);
    }

    /**
     * Edit detail data
     */
    public function edit_detail($id, $data)
    {
        return DB::table($this->table2)
            ->where($this->pk, $id)
            ->update($data);
    }

    /**
     * Delete data
     */
    public function delete($id)
    {
        return DB::table($this->table)
            ->where($this->pk, $id)
            ->delete();
    }

    /**
     * Delete detail data
     */
    public function delete_detail($id)
    {
        return DB::table($this->table2)
            ->where($this->pk, $id)
            ->delete();
    }

    /**
     * Get data for generate gelombang
     */
    public function get_data_generate($gelombang_id = '', $program = '', $prodi = '', $jalur = '', $status = '')
    {
        // Get data gelombang
        $dataGelombang = DB::table('pmb_tbl_gelombang')->where('id', $gelombang_id)->first();
        if (!$dataGelombang) {
            return [];
        }

        // Get data tahun
        $dataTahun = DB::table('tahun')->where('ID', $dataGelombang->tahun_id)->first();

        $query = DB::table('biaya_semester')
            ->select(
                'biaya_semester.JalurPendaftaran AS jalur',
                'jenis_pendaftaran.ID AS jenis_pendaftaran',
                'biaya_semester.ProgramID AS program_id',
                'biaya_semester.ProdiID AS prodi_id',
                'biaya_semester.ID AS biaya_semester_satu_id',
                'biaya.ID AS biaya_pendaftaran',
                'pmb_tbl_gelombang_detail.ID AS gelombang_detail_id'
            )
            ->join('jenis_pendaftaran', 'jenis_pendaftaran.Kode', '=', 'biaya_semester.JenisPendaftaran')
            ->join('biaya', function ($join) {
                $join->on('biaya.BiayaSemesterID', '=', 'biaya_semester.ID')
                     ->where('biaya.JenisBiayaID', '=', '32');
            })
            ->leftJoin('pmb_tbl_gelombang_detail', function ($join) use ($dataGelombang) {
                $join->on('pmb_tbl_gelombang_detail.gelombang_id', '=', DB::raw($dataGelombang->id))
                     ->on('pmb_tbl_gelombang_detail.jalur', '=', 'biaya_semester.JalurPendaftaran')
                     ->on('pmb_tbl_gelombang_detail.jenis_pendaftaran', '=', 'jenis_pendaftaran.ID')
                     ->on('pmb_tbl_gelombang_detail.program_id', '=', 'biaya_semester.ProgramID')
                     ->on('pmb_tbl_gelombang_detail.prodi_id', '=', 'biaya_semester.ProdiID');
            })
            ->join('pmb_pilihan_pendaftaran', function ($join) use ($dataGelombang) {
                $join->on('pmb_pilihan_pendaftaran.jalur', '=', 'biaya_semester.JalurPendaftaran')
                     ->on('pmb_pilihan_pendaftaran.program_id', '=', 'biaya_semester.ProgramID')
                     ->on('pmb_pilihan_pendaftaran.jenis_pendaftaran', '=', 'jenis_pendaftaran.ID')
                     ->where('pmb_pilihan_pendaftaran.tahun_id', '=', $dataGelombang->tahun_id);
            })
            ->where('biaya_semester.TahunMasuk', $dataGelombang->tahunmasuk)
            ->where('biaya_semester.SemesterMasuk', $dataTahun->Semester ?? '')
            ->where('biaya_semester.Semester', '1')
            ->where('biaya_semester.GelombangKe', $dataGelombang->GelombangKe);

        if ($program) {
            $query->whereIn('biaya_semester.ProgramID', $program);
        }
        if ($prodi) {
            $query->whereIn('biaya_semester.ProdiID', $prodi);
        }
        if ($jalur) {
            $query->whereIn('biaya_semester.JalurPendaftaran', $jalur);
        }

        if ($status) {
            if ($status == 1) {
                $query->where('pmb_tbl_gelombang_detail.ID', '!=', null);
            } else if ($status == 2) {
                $query->where('pmb_tbl_gelombang_detail.ID', null);
            }
        }

        return $query->get()->map(fn($item) => (array) $item)->toArray();
    }

    /**
     * Count data for generate gelombang
     */
    public function count_data_generate($gelombang_id = '', $program = '', $prodi = '', $jalur = '', $status = '')
    {
        // Get data gelombang
        $dataGelombang = DB::table('pmb_tbl_gelombang')->where('id', $gelombang_id)->first();
        if (!$dataGelombang) {
            return 0;
        }

        // Get data tahun
        $dataTahun = DB::table('tahun')->where('ID', $dataGelombang->tahun_id)->first();

        $query = DB::table('biaya_semester')
            ->select(
                'biaya_semester.JalurPendaftaran AS jalur',
                'jenis_pendaftaran.ID AS jenis_pendaftaran',
                'biaya_semester.ProgramID AS program_id',
                'biaya_semester.ProdiID AS prodi_id',
                'biaya_semester.ID AS biaya_semester_satu_id'
            )
            ->join('jenis_pendaftaran', 'jenis_pendaftaran.Kode', '=', 'biaya_semester.JenisPendaftaran')
            ->join('biaya', function ($join) {
                $join->on('biaya.BiayaSemesterID', '=', 'biaya_semester.ID')
                     ->where('biaya.JenisBiayaID', '=', '32');
            })
            ->leftJoin('pmb_tbl_gelombang_detail', function ($join) use ($dataGelombang) {
                $join->on('pmb_tbl_gelombang_detail.gelombang_id', '=', DB::raw($dataGelombang->id))
                     ->on('pmb_tbl_gelombang_detail.jalur', '=', 'biaya_semester.JalurPendaftaran')
                     ->on('pmb_tbl_gelombang_detail.jenis_pendaftaran', '=', 'jenis_pendaftaran.ID')
                     ->on('pmb_tbl_gelombang_detail.program_id', '=', 'biaya_semester.ProgramID')
                     ->on('pmb_tbl_gelombang_detail.prodi_id', '=', 'biaya_semester.ProdiID');
            })
            ->join('pmb_pilihan_pendaftaran', function ($join) use ($dataGelombang) {
                $join->on('pmb_pilihan_pendaftaran.jalur', '=', 'biaya_semester.JalurPendaftaran')
                     ->on('pmb_pilihan_pendaftaran.program_id', '=', 'biaya_semester.ProgramID')
                     ->on('pmb_pilihan_pendaftaran.jenis_pendaftaran', '=', 'jenis_pendaftaran.ID')
                     ->where('pmb_pilihan_pendaftaran.tahun_id', '=', $dataGelombang->tahun_id);
            })
            ->where('biaya_semester.TahunMasuk', $dataGelombang->tahunmasuk)
            ->where('biaya_semester.SemesterMasuk', $dataTahun->Semester ?? '')
            ->where('biaya_semester.Semester', '1')
            ->where('biaya_semester.GelombangKe', $dataGelombang->GelombangKe);

        if ($program) {
            $query->whereIn('biaya_semester.ProgramID', $program);
        }
        if ($prodi) {
            $query->whereIn('biaya_semester.ProdiID', $prodi);
        }
        if ($jalur) {
            $query->whereIn('biaya_semester.JalurPendaftaran', $jalur);
        }

        if ($status) {
            if ($status == 1) {
                $query->where('pmb_tbl_gelombang_detail.ID', '!=', null);
            } else if ($status == 2) {
                $query->where('pmb_tbl_gelombang_detail.ID', null);
            }
        }

        return $query->count();
    }

    /**
     * Check if kode already exists
     */
    public function checkDuplicateKode($kode, $id = '')
    {
        $query = DB::table('pmb_tbl_gelombang')
            ->select('id', 'nama')
            ->where('kode', $kode);

        if ($id) {
            $query->where('id', '!=', $id);
        }

        return $query->first();
    }

    /**
     * Check if nama already exists in same tahun
     */
    public function checkDuplicateNama($nama, $tahun_id, $id = '')
    {
        $query = DB::table('pmb_tbl_gelombang')
            ->select('id', 'nama')
            ->where('nama', $nama)
            ->where('tahun_id', $tahun_id);

        if ($id) {
            $query->where('id', '!=', $id);
        }

        return $query->first();
    }

    /**
     * Check if biaya semester exists for tahun
     */
    public function checkBiayaSemester($tahun_id)
    {
        $tahun = DB::table('tahun')->where('ID', $tahun_id)->first();
        if (!$tahun) {
            return false;
        }

        $kode_tahun = $tahun->TahunID;

        $biaya_semester = DB::table('biaya_semester')
            ->where('KodeTahun', $kode_tahun)
            ->first();

        return $biaya_semester ? true : false;
    }

    /**
     * Get tahun masuk from tahun_id
     */
    public function getTahunMasuk($tahun_id)
    {
        $tahun = DB::table('tahun')->where('ID', $tahun_id)->first();
        if (!$tahun) {
            return '';
        }

        $kode_tahun = $tahun->TahunID;
        return substr($kode_tahun, 0, 4);
    }

    /**
     * Update tanggal batch for detail
     */
    public function updateTanggalBatch($gelombang_id, $tgl1, $tgl2)
    {
        $data_gelombang = $this->get_data_detail(9999999, 0, $gelombang_id);
        $totalBerhasil = 0;
        $totalData = count($data_gelombang);

        foreach ($data_gelombang as $gelombang) {
            $update['date_start'] = $tgl1;
            $update['date_end'] = $tgl2;

            DB::table('pmb_tbl_gelombang_detail')
                ->where('id', $gelombang->id)
                ->update($update);

            $totalBerhasil++;
        }

        return [
            'totalBerhasil' => $totalBerhasil,
            'totalData' => $totalData,
            'totalGagal' => $totalData - $totalBerhasil
        ];
    }

    /**
     * Get gelombang detail by filters
     */
    public function get_gelombang_detail($gelombang_id, $prodi_id = '', $program_id = '')
    {
        $query = DB::table('pmb_tbl_gelombang_detail')
            ->select('pmb_tbl_gelombang_detail.*', 'pmb_tbl_gelombang.tahun_id', 'pmb_tbl_gelombang.nama', 'pmb_tbl_gelombang.kode', 'tahun.Nama as nama_tahun')
            ->join('pmb_tbl_gelombang', 'pmb_tbl_gelombang.id', '=', 'pmb_tbl_gelombang_detail.gelombang_id')
            ->join('tahun', 'tahun.ID', '=', 'pmb_tbl_gelombang.tahun_id')
            ->where('pmb_tbl_gelombang.id', $gelombang_id);

        if ($prodi_id) {
            $query->whereRaw("FIND_IN_SET('$prodi_id', pmb_tbl_gelombang_detail.prodi_id) != 0");
        }
        if ($program_id) {
            $query->whereRaw("FIND_IN_SET('$program_id', pmb_tbl_gelombang_detail.program_id) != 0");
        }

        return $query->orderBy('tahun.TahunID', 'DESC')
                     ->orderBy('pmb_tbl_gelombang.kode', 'ASC')
                     ->get()
                     ->map(fn($item) => (array) $item)
                     ->toArray();
    }

    /**
     * Get penawaran biaya pendaftaran
     */
    public function get_penawaran($pilihan_pendaftaran_id, $prodi_id, $biaya_semester_satu_id, $gelombang_id)
    {
        $row_gelombang = DB::table('pmb_tbl_gelombang')->where('id', $gelombang_id)->first();
        if (!$row_gelombang) {
            return [];
        }

        $pil = DB::table('pmb_pilihan_pendaftaran')->where('id', $pilihan_pendaftaran_id)->first();
        if (!$pil) {
            return [];
        }

        $tahun = DB::table('tahun')->where('ID', $pil->tahun_id)->first();

        $jenis_pendaftaran = array_filter(explode(',', $pil->jenis_pendaftaran ?? ''));

        $kode_jenis_pendaftaran = array("'-'");
        foreach ($jenis_pendaftaran as $jp) {
            $jp_field = DB::table('jenis_pendaftaran')->where('ID', $jp)->first();
            if ($jp_field && $jp_field->Kode) {
                $kode_jenis_pendaftaran[] = "'" . $jp_field->Kode . "'";
            }
        }

        $whr_sql_penawaran = '';
        if ($tahun && $tahun->Semester) {
            $whr_sql_penawaran .= " and a.SemesterMasuk='$tahun->Semester'";
        }

        $sql_penawaran = "SELECT a.*,b.Jumlah as formulir
            from biaya_semester a
            inner join biaya b
            on a.ID=b.BiayaSemesterID
            and b.JenisBiayaID='32'
            where a.Semester='1'
            and a.ProdiID='$prodi_id'
            and a.KodeTahun='$tahun->TahunID'
            and a.ProgramID='$pil->program_id'
            and a.JalurPendaftaran='$pil->jalur'
            and a.GelombangKe='$row_gelombang->GelombangKe'
            $whr_sql_penawaran
            and a.JenisPendaftaran in (".implode(',', $kode_jenis_pendaftaran).")
            order by ID ASC";

        return DB::select($sql_penawaran);
    }

    /**
     * Get detail pilihan pendaftaran
     */
    public function get_detail_pilihan_pendaftaran($pilihan_pendaftaran_id)
    {
        $get_pilihan_pendaftaran = DB::table('pmb_pilihan_pendaftaran')->where('id', $pilihan_pendaftaran_id)->first();
        if (!$get_pilihan_pendaftaran) {
            return null;
        }

        // Get jenis pendaftaran
        $NamaJenisPendaftaran = '';
        if (!empty($get_pilihan_pendaftaran->jenis_pendaftaran)) {
            $jenis_ids = explode(',', $get_pilihan_pendaftaran->jenis_pendaftaran);
            $NamaJenisPendaftaran = DB::table('jenis_pendaftaran')
                ->whereIn('id', $jenis_ids)
                ->pluck('Nama')
                ->implode(', ');
        }

        return [
            'NamaProgram' => DB::table('program')->where('ID', $get_pilihan_pendaftaran->program_id)->value('Nama') ?? '',
            'NamaJalur' => DB::table('pmb_edu_jalur_pendaftaran')->where('id', $get_pilihan_pendaftaran->jalur)->value('nama') ?? '',
            'NamaJenisPendaftaran' => $NamaJenisPendaftaran
        ];
    }
}
