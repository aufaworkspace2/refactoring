<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfoPmb extends Model
{
    protected $table = 'pmb_info';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}
