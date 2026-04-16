<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtikelPmb extends Model
{
    protected $table = 'pmb_tbl_artikel';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['publish' => 'integer', 'status' => 'integer', 'event_date' => 'date'];
}
