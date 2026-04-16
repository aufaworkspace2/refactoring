<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transkrip extends Model
{
    protected $table = 'transkrip';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];
}
