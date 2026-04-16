<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingNomorNim extends Model
{
    protected $table = 'pmb_tbl_format_nim';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'format_nim' => 'array',
    ];
}
