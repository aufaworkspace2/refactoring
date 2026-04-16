<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagePmb extends Model
{
    protected $table = 'pmb_tbl_page';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['status' => 'integer'];
}
