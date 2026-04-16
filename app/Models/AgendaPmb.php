<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaPmb extends Model
{
    protected $table = 'pmb_tbl_agenda';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'publish' => 'integer',
        'tanggal' => 'date',
    ];
}
