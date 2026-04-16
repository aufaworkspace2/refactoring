<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SumberInformasiPendaftaran extends Model
{
    protected $table = 'pmb_tbl_referensi_daftar';
    protected $primaryKey = 'id_ref_daftar';
    public $timestamps = false;

    protected $guarded = [];
}
