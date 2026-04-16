<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Khs extends Model
{
    protected $table = 'nilai';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];
}
