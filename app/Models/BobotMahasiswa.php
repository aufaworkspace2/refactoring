<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BobotMahasiswa extends Model
{
    protected $table = 'bobot_mahasiswa';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];
}
