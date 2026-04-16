<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilStudi extends Model
{
    protected $table = 'hasilstudi';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];
}
