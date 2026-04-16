<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PilihanPendaftaranPmbService
{
    private $table = 'pmb_pilihan_pendaftaran';
    private $pk = 'id';

    /**
     * Get data with pagination and filters
     */
    public function get_data($limit, $offset, $keyword = '', $tahun_id = '')
    {
        $query = DB::table($this->table)
            ->select($this->table . '.*', DB::raw('COUNT(pmb_tbl_gelombang_detail.id) AS jumlah_gelombang_detail'));

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where($this->table . '.nama', 'LIKE', "%{$keyword}%");
            });
        }

        if ($tahun_id) {
            $query->where($this->table . '.tahun_id', $tahun_id);
        }

        $query->leftJoin('pmb_tbl_gelombang_detail', 'pmb_tbl_gelombang_detail.pilihan_pendaftaran_id', '=', $this->table . '.id')
            ->orderBy($this->table . '.id', 'ASC')
            ->groupBy($this->table . '.id');

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
    public function count_all($keyword = '', $tahun_id = '')
    {
        $query = DB::table($this->table);

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'LIKE', "%{$keyword}%");
            });
        }

        if ($tahun_id) {
            $query->where('tahun_id', $tahun_id);
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
     * Add data
     */
    public function add($data)
    {
        return DB::table($this->table)->insertGetId($data);
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
     * Delete data
     */
    public function delete($id)
    {
        return DB::table($this->table)
            ->where($this->pk, $id)
            ->delete();
    }

    /**
     * Delete multiple data
     */
    public function deleteMultiple($ids)
    {
        return DB::table($this->table)
            ->whereIn($this->pk, $ids)
            ->delete();
    }

    /**
     * Check if nama already exists (excluding current id)
     */
    public function checkDuplicate($nama, $tahun_id, $id = '')
    {
        $query = DB::table('pmb_pilihan_pendaftaran')
            ->select('id', 'nama')
            ->where('nama', $nama)
            ->where('tahun_id', $tahun_id);

        if ($id) {
            $query->where('id', '!=', $id);
        }

        return $query->first();
    }

    /**
     * Update aktif status
     */
    public function updateAktif($val, $buka)
    {
        $input['aktif'] = $buka;
        $input['user_update'] = Session::get('UserID');

        return DB::table('pmb_pilihan_pendaftaran')
            ->where('ID', $val)
            ->update($input);
    }

    /**
     * Get diskon list based on filters
     */
    public function getDiskonList($tahun_id, $program_id, $jenis_pendaftaran, $jalur, $select_master_diskon = '')
    {
        $tahun = DB::table('tahun')->where('ID', $tahun_id)->first();

        $master_diskon_id_list = array();

        if ($tahun) {
            $kode_tahun = $tahun->TahunID;

            $kode_jenis_pendaftaran = array(0);

            if (is_array($jenis_pendaftaran)) {
                foreach ($jenis_pendaftaran as $jp) {
                    $jp_field = DB::table('jenis_pendaftaran')->where('ID', $jp)->first();
                    if ($jp_field) {
                        $kode_jenis_pendaftaran[] = $jp_field->Kode;
                    }
                }
            }

            $where = array(
                'KodeTahun' => $kode_tahun,
                'ProgramID' => $program_id,
                'JalurPendaftaran' => $jalur,
            );

            $biaya_semester = DB::table('biaya_semester')
                ->where($where)
                ->whereIn('JenisPendaftaran', $kode_jenis_pendaftaran)
                ->get()
                ->toArray();

            foreach ($biaya_semester as $bs) {
                $diskon_arr = explode(",", $bs->MasterDiskonID_list ?? '');
                foreach ($diskon_arr as $da) {
                    if ($da) {
                        $master_diskon_id_list[] = $da;
                    }
                }
            }
        }

        if (count($master_diskon_id_list) > 0) {
            $query = DB::table('master_diskon')
                ->select('master_diskon.*', DB::raw('if(master_diskon.ProdiID = 0, CONCAT("Semua Programstudi"), CONCAT(jenjang.Nama," || ",programstudi.Nama)) as prodi'))
                ->leftJoin('programstudi', 'programstudi.ID', '=', 'master_diskon.ProdiID')
                ->leftJoin('jenjang', 'jenjang.ID', '=', 'programstudi.JenjangID')
                ->whereIn('master_diskon.ID', $master_diskon_id_list)
                ->get()
                ->toArray();
        } else {
            $query = array();
        }

        return $query;
    }

    /**
     * Get all tahun data
     */
    public function getAllTahun()
    {
        return DB::table('tahun')->get()->toArray();
    }

    /**
     * Get all program data
     */
    public function getAllProgram()
    {
        return DB::table('program')->get()->toArray();
    }

    /**
     * Get all jenis pendaftaran data
     */
    public function getAllJenisPendaftaran()
    {
        return DB::table('jenis_pendaftaran')->get()->toArray();
    }

    /**
     * Get all jalur pendaftaran data (aktif only)
     */
    public function getAllJalurPendaftaran()
    {
        return DB::table('pmb_edu_jalur_pendaftaran')
            ->where('aktif', '1')
            ->get()
            ->toArray();
    }

    /**
     * Get field value from table
     */
    public function getField($id, $table, $field = 'Nama')
    {
        if (!$id) {
            return '';
        }

        $result = DB::table($table)
            ->where('ID', $id)
            ->first();

        if ($result && isset($result->$field)) {
            return $result->$field;
        }

        // Try lowercase field name
        $lowerField = strtolower($field);
        if ($result && isset($result->$lowerField)) {
            return $result->$lowerField;
        }

        return '';
    }
}
