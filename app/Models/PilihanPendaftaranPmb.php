<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PilihanPendaftaranPmb extends Model
{
    protected $table = 'pmb_pilihan_pendaftaran';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = [];
}
