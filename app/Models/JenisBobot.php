<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisBobot extends Model
{
    protected $table = 'jenisbobot';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];
}
