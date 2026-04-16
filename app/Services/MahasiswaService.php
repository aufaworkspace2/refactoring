<?php
namespace App\Services;

use App\Models\Mahasiswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class MahasiswaService
{
    public function get_data($limit, $offset, $ProgramID='', $ProdiID='', $KelasID='', $StatusMhswID='', $TahunMasuk='', $JenjangID='', $keyword='', $ID='', $statusPindahan='', $SecondGeneration='', $SemesterMasuk='', $orderby='', $descasc='')
    {
        $user_id = Session::get('UserID');
        $user = DB::table('user')->where('ID', $user_id)->first();
        
        $arrProgram = null;
        if ($user && $user->ProgramID) {
            $arrProgram = explode(",", $user->ProgramID);
        }
        
        $arrProdi = null;
        if ($user && $user->ProdiID) {
            $arrProdi = explode(",", $user->ProdiID);
        }

        $query = DB::table('mahasiswa');
        $query->select('mahasiswa.*');

        $levelKode = explode(',', Session::get('LevelKode') ?? '');

        if ($ProgramID) {
            $query->whereIn('mahasiswa.ProgramID', explode(',', $ProgramID));
        } else {
            if (!in_array('SPR', $levelKode) && $arrProgram) {
                $query->whereIn('mahasiswa.ProgramID', $arrProgram);
            }
        }

        if ($ProdiID) {
            $query->whereIn('mahasiswa.ProdiID', explode(',', $ProdiID));
        } else {
            if (!in_array('SPR', $levelKode) && $arrProdi) {
                $query->whereIn('mahasiswa.ProdiID', $arrProdi);
            }
        }

        if ($KelasID) $query->where('mahasiswa.KelasID', $KelasID);
        if ($StatusMhswID) $query->where('mahasiswa.StatusMhswID', $StatusMhswID);
        if ($TahunMasuk) $query->where('mahasiswa.TahunMasuk', $TahunMasuk);
        if ($SemesterMasuk) $query->where('mahasiswa.SemesterMasuk', $SemesterMasuk);
        if ($JenjangID) $query->where('mahasiswa.JenjangID', $JenjangID);
        if ($ID) $query->where('mahasiswa.ID', $ID);
        
        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('mahasiswa.NPM', 'LIKE', "%{$keyword}%")
                  ->orWhere('mahasiswa.Nama', 'LIKE', "%{$keyword}%");
            });
        }
        
        if (!empty($statusPindahan)) {
            $query->where('mahasiswa.StatusPindahan', $statusPindahan);
        }

        if ($SecondGeneration != "") {
            $query->where('mahasiswa.SecondGeneration', $SecondGeneration);
        }

        if ($orderby != "" && $descasc != "") {
            $query->orderBy($orderby, $descasc);
        }

        $query->where('mahasiswa.jenis_mhsw', 'mhsw');
        $query->groupBy('mahasiswa.ID');

        if ($limit !== null) {
            $query->take($limit);
        }
        if ($offset !== null) {
            $query->skip($offset);
        }

        $results = $query->get();

        foreach ($results as $row) {
            $row->Nama = stripslashes($row->Nama);
        }

        return $results;
    }

    public function count_all($ProgramID='', $ProdiID='', $KelasID='', $StatusMhswID='', $TahunMasuk='', $JenjangID='', $keyword='', $statusPindahan='', $SecondGeneration='', $SemesterMasuk='', $orderby='', $descasc='')
    {
        $user_id = Session::get('UserID');
        $user = DB::table('user')->where('ID', $user_id)->first();
        
        $arrProgram = null;
        if ($user && $user->ProgramID) {
            $arrProgram = explode(",", $user->ProgramID);
        }
        
        $arrProdi = null;
        if ($user && $user->ProdiID) {
            $arrProdi = explode(",", $user->ProdiID);
        }
            
        $query = DB::table('mahasiswa');
        $query->select('mahasiswa.*');

        $levelKode = explode(',', Session::get('LevelKode') ?? '');

        if ($ProgramID) {
            $query->whereIn('mahasiswa.ProgramID', explode(',', $ProgramID));
        } else {
            if (!in_array('SPR', $levelKode) && $arrProgram) {
                $query->whereIn('mahasiswa.ProgramID', $arrProgram);
            }
        }

        if ($ProdiID) {
            $query->whereIn('mahasiswa.ProdiID', explode(',', $ProdiID));
        } else {
            if (!in_array('SPR', $levelKode) && $arrProdi) {
                $query->whereIn('mahasiswa.ProdiID', $arrProdi);
            }
        }

        if ($KelasID) $query->where('mahasiswa.KelasID', $KelasID);
        if ($StatusMhswID) $query->where('mahasiswa.StatusMhswID', $StatusMhswID);
        if ($TahunMasuk) $query->where('mahasiswa.TahunMasuk', $TahunMasuk);
        if ($SemesterMasuk) $query->where('mahasiswa.SemesterMasuk', $SemesterMasuk);
        if ($JenjangID) $query->where('mahasiswa.JenjangID', $JenjangID);
        
        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('mahasiswa.NPM', 'LIKE', "%{$keyword}%")
                  ->orWhere('mahasiswa.Nama', 'LIKE', "%{$keyword}%");
            });
        }
        
        if (!empty($statusPindahan)) {
            $query->where('mahasiswa.StatusPindahan', $statusPindahan);
        }

        if ($SecondGeneration != "") {
            $query->where('mahasiswa.SecondGeneration', $SecondGeneration);
        }

        $query->where('mahasiswa.jenis_mhsw', 'mhsw');
        $query->groupBy('mahasiswa.ID');
        
        return $query->get()->count();
    }

    public function get_id($id)
    {
        return DB::table('mahasiswa')->where('ID', $id)->first();
    }

    public function add($data)
    {
        return DB::table('mahasiswa')->insertGetId($data);
    }

    public function edit($id, $data)
    {
        return DB::table('mahasiswa')->where('ID', $id)->update($data);
    }

    public function delete($id)
    {
        if (is_array($id)) {
            return DB::table('mahasiswa')->whereIn('ID', $id)->delete();
        }
        return DB::table('mahasiswa')->where('ID', $id)->delete();
    }

    public function get_tahun()
    {
        return DB::select("SELECT DISTINCT TahunMasuk FROM mahasiswa WHERE NPM IS NOT NULL AND TahunMasuk != '' ORDER BY TahunMasuk DESC");
    }

    public function insertSekolah($NamaSekolah = '')
    {
        $insert = [
            'nama' => $NamaSekolah,
            'jenjang' => 'SMA'
        ];
        return DB::table('sekolahdata')->insertGetId($insert);
    }
}
