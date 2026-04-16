<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CetakPerprodiPmb extends Model
{
    protected $table = 'pmb_tbl_gelombang';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = [];
}
