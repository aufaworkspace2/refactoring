<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterFormatNimPmb extends Model
{
    protected $table = 'pmb_tbl_master_format_nim';
    protected $primaryKey = 'kode';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];
}
