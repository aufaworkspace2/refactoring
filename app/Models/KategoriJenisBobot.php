<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriJenisBobot extends Model
{
    protected $table = 'kategori_jenisbobot';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];
}
