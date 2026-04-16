<?php

namespace App\Services;

use App\Models\KeteranganStatusMahasiswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use stdClass;

class KeteranganStatusMahasiswaService
{
    /**
     * Get filtered data list
     * Legacy: m_keterangan_status_mahasiswa->get_data()
     */
    public function get_data($limit, $offset, $ProdiID = '', $TahunID = '', $StatusMhswID = '', $keyword = '', $TahunMasuk = '', $ProgramID = '')
    {
        $entityID = Session::get('EntityID');
        $prodiList = DB::table('karyawan')->where('ID', $entityID)->value('ProdiID');
        $arrProdi = $prodiList ? explode(",", $prodiList) : [];

        $query = DB::table('keteranganstatusmahasiswa')
            ->select('keteranganstatusmahasiswa.*', 'mahasiswa.NPM', 'mahasiswa.Nama')
            ->join('mahasiswa', 'mahasiswa.ID', '=', 'keteranganstatusmahasiswa.MhswID');

        if ($ProgramID) {
            $query->where('mahasiswa.ProgramID', $ProgramID);
        }

        if ($ProdiID) {
            $query->where('keteranganstatusmahasiswa.ProdiID', $ProdiID);
        } else {
            $levelKode = Session::get('LevelKode') ?? '';
            if (!in_array('SPR', explode(',', $levelKode))) {
                if (!empty($arrProdi)) {
                    $query->whereIn('keteranganstatusmahasiswa.ProdiID', $arrProdi);
                } else {
                    // If not SPR and no prodi access, return empty results
                    $query->whereRaw('1 = 0');
                }
            }
        }

        if ($TahunID) {
            $query->where('keteranganstatusmahasiswa.TahunID', $TahunID);
        }

        if ($StatusMhswID) {
            $query->where('keteranganstatusmahasiswa.StatusMahasiswaID', $StatusMhswID);
        }

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('mahasiswa.NPM', 'LIKE', "%$keyword%")
                    ->orWhere('mahasiswa.Nama', 'LIKE', "%$keyword%");
            });
        }

        if ($TahunMasuk) {
            $query->where("mahasiswa.TahunMasuk", $TahunMasuk);
        }

        return $query->orderBy('keteranganstatusmahasiswa.ID', 'DESC')
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
    }

    /**
     * Count all filtered data
     * Legacy: m_keterangan_status_mahasiswa->count_all()
     */
    public function count_all($ProdiID = '', $TahunID = '', $StatusMhswID = '', $keyword = '', $TahunMasuk = '', $ProgramID = '')
    {
        $entityID = Session::get('EntityID');
        $prodiList = DB::table('karyawan')->where('ID', $entityID)->value('ProdiID');
        $arrProdi = $prodiList ? explode(",", $prodiList) : [];

        $query = DB::table('keteranganstatusmahasiswa')
            ->join('mahasiswa', 'mahasiswa.ID', '=', 'keteranganstatusmahasiswa.MhswID');

        if ($ProgramID) {
            $query->where('mahasiswa.ProgramID', $ProgramID);
        }

        if ($ProdiID) {
            $query->where('keteranganstatusmahasiswa.ProdiID', $ProdiID);
        } else {
            $levelKode = Session::get('LevelKode') ?? '';
            if (!in_array('SPR', explode(',', $levelKode))) {
                if (!empty($arrProdi)) {
                    $query->whereIn('keteranganstatusmahasiswa.ProdiID', $arrProdi);
                } else {
                    // If not SPR and no prodi access, return empty results
                    $query->whereRaw('1 = 0');
                }
            }
        }

        if ($TahunID) {
            $query->where('keteranganstatusmahasiswa.TahunID', $TahunID);
        }

        if ($StatusMhswID) {
            $query->where('keteranganstatusmahasiswa.StatusMahasiswaID', $StatusMhswID);
        }

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('mahasiswa.NPM', 'LIKE', "%$keyword%")
                    ->orWhere('mahasiswa.Nama', 'LIKE', "%$keyword%");
            });
        }

        if ($TahunMasuk) {
            $query->where("mahasiswa.TahunMasuk", $TahunMasuk);
        }

        return $query->count();
    }

    /**
     * Get options for Mahasiswa dropdown
     * Legacy: c_keterangan_status_mahasiswa->changemhsw()
     */
    public function get_mahasiswa_options($ProgramID, $ProdiID, $ID = null)
    {
        $wh = "";
        if ($ID) {
            $wh = "OR ID = $ID";
        }

        $sql1 = DB::select("SELECT * FROM mahasiswa WHERE ProgramID=? AND ProdiID=? AND StatusMhswID NOT IN (1, 4, 5, 6, 0) $wh ORDER BY NPM ASC", [$ProgramID, $ProdiID]);
        $sql2 = DB::select("SELECT * FROM mahasiswa WHERE ProgramID=? AND ProdiID=? AND ID NOT IN (SELECT MhswID FROM keteranganstatusmahasiswa)", [$ProgramID, $ProdiID]);

        $status_mahasiswa = array_map(fn($val) => $val->NPM, $sql1);
        $mahasiswa_npm = array_map(fn($val) => $val->NPM, $sql2);

        $unique_npm = array_unique(array_merge($status_mahasiswa, $mahasiswa_npm));
        
        $options = [];
        foreach ($unique_npm as $npm) {
            $data_final = DB::table('mahasiswa')->where('NPM', $npm)->first();
            if ($data_final) {
                $stat = get_field($data_final->StatusMhswID, 'statusmahasiswa');
                $options[] = [
                    'ID' => $data_final->ID,
                    'NPM' => $data_final->NPM,
                    'Nama' => $data_final->Nama,
                    'Status' => $stat,
                    'Selected' => ($data_final->ID == $ID)
                ];
            }
        }
        return $options;
    }

    /**
     * Save or update record
     * Legacy: c_keterangan_status_mahasiswa->save()
     */
    public function save($data, $save_type)
    {
        $StatusMhswID = $data['StatusMhswID'];
        $MhswID = $data['MhswID'];
        $TahunID = $data['TahunID'];
        $NamaStatus = get_field($StatusMhswID, 'statusmahasiswa', 'Nama');

        $input = [
            'ProdiID' => $data['ProdiID'],
            'TahunID' => $TahunID,
            'MhswID' => $MhswID,
            'StatusMahasiswaID' => $StatusMhswID,
            'Status' => $NamaStatus,
            'Nomor_Surat' => $data['Nomor_Surat'],
            'Mulai_Semester' => ($data['Mulai_Semester'][0] ?? '') . ($data['Mulai_Semester'][1] ?? ''),
            'Akhir_Semester' => ($data['Akhir_Semester'][0] ?? '') . ($data['Akhir_Semester'][1] ?? ''),
            'Alasan' => $data['Alasan'] ?? '',
            'Tgl' => gantitanggal($data['Tgl'] ?? '', "Y-m-d"),
        ];

        if ($save_type == 1) {
            $id = DB::table('keteranganstatusmahasiswa')->insertGetId($input);
            
            if (function_exists('log_akses')) {
                log_akses('Tambah', 'Menambah Data Setup keterangan Status Mahasiswa Dengan Nama ' . $MhswID);
            }

            DB::table('mahasiswa')->where('ID', $MhswID)->update(['StatusMhswID' => $StatusMhswID]);

            $mhsw = DB::table('mahasiswa')->select('ID', 'NPM', 'Nama')->where('ID', $MhswID)->first();
            if ($mhsw) {
                DB::table('log_set_status')->insert([
                    'MhswID' => $mhsw->ID,
                    'NPM' => $mhsw->NPM,
                    'TahunID' => $TahunID,
                    'StatusMhswID' => $StatusMhswID,
                    'Status' => $NamaStatus,
                    'createdAt' => date('Y-m-d H:i:s'),
                    'UserID' => Session::get('UserID')
                ]);
            }
            return $id;
        } else {
            $ID = $data['ID'];
            DB::table('keteranganstatusmahasiswa')->where('ID', $ID)->update($input);

            if (function_exists('log_akses')) {
                log_akses('Ubah', "Mengubah Data Keterangan Status Mahasiswa ID: $ID");
            }

            $mhsw = DB::table('mahasiswa')->select('ID', 'NPM', 'Nama')->where('ID', $MhswID)->first();
            if ($mhsw) {
                DB::table('log_set_status')->insert([
                    'MhswID' => $mhsw->ID,
                    'NPM' => $mhsw->NPM,
                    'TahunID' => $TahunID,
                    'StatusMhswID' => $StatusMhswID,
                    'Status' => $NamaStatus,
                    'Type' => 'edit',
                    'createdAt' => date('Y-m-d H:i:s'),
                    'UserID' => Session::get('UserID')
                ]);
            }

            DB::table('mahasiswa')->where('ID', $MhswID)->update(['StatusMhswID' => $StatusMhswID]);
            return $ID;
        }
    }

    /**
     * Delete records
     * Legacy: c_keterangan_status_mahasiswa->delete()
     */
    public function delete($checkid)
    {
        if (is_array($checkid)) {
            foreach ($checkid as $id) {
                $row = DB::table('keteranganstatusmahasiswa')->where('ID', $id)->first();
                if ($row) {
                    DB::table('keteranganstatusmahasiswa')->where('ID', $id)->delete();
                    if (function_exists('log_akses')) {
                        log_akses('Hapus', 'Menghapus Data Keterangan Status Mahasiswa MhswID: ' . $row->MhswID);
                    }
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Process import from Excel
     * Legacy: c_keterangan_status_mahasiswa->import()
     */
    public function process_import($values)
    {
        $arrFailed = [];
        foreach ($values as $row) {
            $data_mahasiswa = (array) get_data_by2("mahasiswa", "*", "NPM", $row['A']);
            $MhswID = $data_mahasiswa['ID'] ?? null;

            $TahunID_data = get_data_by2("tahun", "ID", "TahunID", $row['B']);
            $TahunID = $TahunID_data['ID'] ?? null;

            $StatusMhswID_data = get_data_by2("statusmahasiswa", "ID", "Nama", $row['D']);
            $StatusMhswID = $StatusMhswID_data['ID'] ?? null;

            if (empty($MhswID)) {
                $arrFailed[] = 'NPM ' . $row['A'] . " Gagal Insert Karena Mahasiswa Tidak Ditemukan.";
                continue;
            }

            if (empty($TahunID)) {
                $arrFailed[] = 'NPM ' . $row['A'] . " Gagal Insert Karena Kode Tahun Tidak Ditemukan.";
                continue;
            }

            if (empty($StatusMhswID)) {
                $arrFailed[] = 'NPM ' . $row['A'] . " Gagal Insert Karena Status Mahasiswa Tidak Ditemukan.";
                continue;
            }

            $input = [
                'ProdiID' => $data_mahasiswa['ProdiID'],
                'TahunID' => $TahunID,
                'MhswID' => $MhswID,
                'StatusMahasiswaID' => $StatusMhswID,
                'Status' => $row['D'],
                'Nomor_Surat' => $row['F'],
                'Mulai_Semester' => null,
                'Akhir_Semester' => null,
                'Alasan' => $row['E'],
                'Tgl' => gantitanggal($row['C'], "Y-m-d"),
            ];

            $check = DB::table('keteranganstatusmahasiswa')
                ->where('MhswID', $MhswID)
                ->where('TahunID', $TahunID)
                ->where('StatusMahasiswaID', $StatusMhswID)
                ->first();

            if ($check) {
                DB::table('keteranganstatusmahasiswa')->where('ID', $check->ID)->update($input);
            } else {
                DB::table('keteranganstatusmahasiswa')->insert($input);
            }

            DB::table('mahasiswa')->where('ID', $MhswID)->update(['StatusMhswID' => $StatusMhswID]);

            DB::table('log_set_status')->insert([
                'MhswID' => $MhswID,
                'NPM' => $data_mahasiswa['NPM'],
                'TahunID' => $TahunID,
                'StatusMhswID' => $StatusMhswID,
                'Status' => $row['D'],
                'createdAt' => date('Y-m-d H:i:s'),
                'UserID' => Session::get('UserID'),
                'source' => "excel"
            ]);
        }
        return $arrFailed;
    }

    /**
     * Reactive student status
     * Legacy: c_keterangan_status_mahasiswa->reactive()
     */
    public function reactive($checkid)
    {
        $row = DB::table('keteranganstatusmahasiswa')->where('ID', $checkid)->first();
        if ($row) {
            $MhswID = $row->MhswID;
            $update = DB::table('mahasiswa')->where('ID', $MhswID)->update(['StatusMhswID' => '3']);

            if ($update) {
                $mhsw = DB::table('mahasiswa')->select('ID', 'NPM', 'Nama')->where('ID', $MhswID)->first();
                DB::table('log_set_status')->insert([
                    'MhswID' => $mhsw->ID,
                    'NPM' => $mhsw->NPM,
                    'TahunID' => $row->TahunID,
                    'StatusMhswID' => 3,
                    'Status' => 'Aktif',
                    'createdAt' => date('Y-m-d H:i:s'),
                    'UserID' => Session::get('UserID')
                ]);

                if (function_exists('log_akses')) {
                    log_akses('Edit', 'Mengubah Keterangan Status Mahasiswa Menjadi Aktif MhswID: ' . $MhswID);
                }
                return true;
            }
        }
        return false;
    }
}
