<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalWaktu extends Model
{
    protected $table = 'jadwalwaktu';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];
}
