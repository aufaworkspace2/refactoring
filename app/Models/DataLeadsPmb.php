<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataLeadsPmb extends Model
{
    protected $table = 'pmb_user_moreinfo';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
