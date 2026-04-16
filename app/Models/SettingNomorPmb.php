<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingNomorPmb extends Model
{
    protected $table = 'pmb_tbl_format_pmb';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'nomor_pmb' => 'array',
    ];
}
