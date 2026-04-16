<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SettingPilihanTambahanPmbService
{
    private $setupDb = 'edufectacampus_dev.setup_app';

    public function getSettingMunculPmb()
    {
        $row = DB::table($this->setupDb)->where('tipe_setup', 'setup_muncul_pilihan_pmb')->first();
        
        if (!$row) {
            return null;
        }

        return json_decode($row->metadata, true);
    }

    public function getSettingTambahanNominal()
    {
        $row = DB::table($this->setupDb)->where('tipe_setup', 'setup_nominal_formulir_tambahan_pmb')->first();
        
        if (!$row) {
            return null;
        }

        return json_decode($row->metadata, true);
    }

    public function saveSettingMunculPmb($metadata)
    {
        $row = DB::table($this->setupDb)->where('tipe_setup', 'setup_muncul_pilihan_pmb')->first();

        $json_metadata = json_encode($metadata);

        if ($row) {
            return DB::table($this->setupDb)
                ->where('tipe_setup', 'setup_muncul_pilihan_pmb')
                ->update(['metadata' => $json_metadata]);
        } else {
            return DB::table($this->setupDb)->insert([
                'tipe_setup' => 'setup_muncul_pilihan_pmb',
                'metadata' => $json_metadata
            ]);
        }
    }

    public function saveSettingTambahanNominal($metadata)
    {
        $row = DB::table($this->setupDb)->where('tipe_setup', 'setup_nominal_formulir_tambahan_pmb')->first();

        $json_metadata = json_encode($metadata);

        if ($row) {
            return DB::table($this->setupDb)
                ->where('tipe_setup', 'setup_nominal_formulir_tambahan_pmb')
                ->update(['metadata' => $json_metadata]);
        } else {
            return DB::table($this->setupDb)->insert([
                'tipe_setup' => 'setup_nominal_formulir_tambahan_pmb',
                'metadata' => $json_metadata
            ]);
        }
    }
}
