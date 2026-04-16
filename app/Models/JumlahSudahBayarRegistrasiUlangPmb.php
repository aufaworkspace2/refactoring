<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JumlahSudahBayarRegistrasiUlangPmb extends Model
{
    protected $table = 'mahasiswa';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $guarded = [];
}
