<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriSoalPmb extends Model
{
    protected $table = 'pmb_tbl_kategori_soal';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}
