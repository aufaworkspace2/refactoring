<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuPmb extends Model
{
    protected $table = 'pmb_tbl_kanal';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['status' => 'integer', 'megamenu' => 'integer'];
}
