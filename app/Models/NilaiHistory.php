<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NilaiHistory extends Model
{
    protected $table = 'nilai_history';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];
}
