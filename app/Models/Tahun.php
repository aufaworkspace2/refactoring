<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tahun extends Model
{
    protected $table = 'tahun';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];
}
