<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Konversi extends Model
{
    protected $table = 'konversi';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];
}
