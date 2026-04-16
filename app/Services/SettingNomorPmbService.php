<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SettingNomorPmbService
{
    public function get_pmb()
    {
        $row = DB::table('pmb_tbl_format_pmb')->where('id', 1)->first();
        
        if (!$row) {
            return null;
        }

        $nomor_pmb = json_decode($row->nomor_pmb, true);
        $master = [];
        if (is_array($nomor_pmb)) {
            foreach ($nomor_pmb as $data) {
                $master[] = $data['kode'];
            }
        }

        $data_master = DB::table('pmb_tbl_master_format_nim')->get();

        return [
            'row' => $row,
            'master' => $master,
            'jum_master' => count($master),
            'data_master' => $data_master
        ];
    }

    public function save_pmb($save, $ID, $format)
    {
        $data_format = [];
        foreach ($format as $f) {
            $data_format[]['kode'] = $f;
        }
        $input['nomor_pmb'] = json_encode($data_format);

        if ($save == 1) {
            $input['id'] = 1;
            return DB::table('pmb_tbl_format_pmb')->insert($input);
        }

        if ($save == 2) {
            return DB::table('pmb_tbl_format_pmb')->where('id', $ID)->update($input);
        }
    }

    public function get_invoice()
    {
        $row = DB::table('pmb_tbl_format_invoice')->where('id', 1)->first();
        
        if (!$row) {
            return null;
        }

        $nomor_invoice = json_decode($row->nomor_invoice, true);
        $master = [];
        if (is_array($nomor_invoice)) {
            foreach ($nomor_invoice as $data) {
                $master[] = $data['kode'];
            }
        }

        $data_master = DB::table('pmb_tbl_master_format_nim')->get();

        return [
            'row' => $row,
            'master' => $master,
            'jum_master' => count($master),
            'data_master' => $data_master
        ];
    }

    public function save_invoice($save, $ID, $format)
    {
        $data_format = [];
        foreach ($format as $f) {
            $data_format[]['kode'] = $f;
        }
        $input['nomor_invoice'] = json_encode($data_format);

        if ($save == 1) {
            $input['id'] = 1;
            return DB::table('pmb_tbl_format_invoice')->insert($input);
        }

        if ($save == 2) {
            return DB::table('pmb_tbl_format_invoice')->where('id', $ID)->update($input);
        }
    }

    public function get_nim()
    {
        $row = DB::table('pmb_tbl_format_nim')->where('id', 1)->first();
        
        if (!$row) {
            return null;
        }

        $format_nim = json_decode($row->format_nim, true);
        $master = [];
        if (is_array($format_nim)) {
            foreach ($format_nim as $data) {
                $master[] = $data['kode'];
            }
        }

        $data_master = DB::table('pmb_tbl_master_format_nim')->get();

        return [
            'row' => $row,
            'master' => $master,
            'jum_master' => count($master),
            'data_master' => $data_master
        ];
    }

    public function save_nim($save, $ID, $format)
    {
        $data_format = [];
        foreach ($format as $f) {
            $data_format[]['kode'] = $f;
        }
        $input['format_nim'] = json_encode($data_format);

        if ($save == 1) {
            $input['id'] = 1;
            return DB::table('pmb_tbl_format_nim')->insert($input);
        }

        if ($save == 2) {
            return DB::table('pmb_tbl_format_nim')->where('id', $ID)->update($input);
        }
    }

    public function getMaster()
    {
        return DB::table('pmb_tbl_master_format_nim')->get();
    }
}
