<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BobotNilai extends Model
{
    protected $table = 'bobotnilai';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];
}
