<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyaratPmb extends Model
{
    protected $table = 'pmb_edu_syarat';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = [];
}
