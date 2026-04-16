<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResetUsmPmb extends Model
{
    protected $table = 'mahasiswa';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $guarded = [];
}
