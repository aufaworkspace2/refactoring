<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannerPmb extends Model
{
    protected $table = 'pmb_tbl_banner';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['status' => 'integer'];
}
