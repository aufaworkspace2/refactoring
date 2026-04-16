<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GenerateDenda extends Model
{
    protected $table = 'histori_generate_denda';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    public function tagihanMahasiswa()
    {
        return $this->belongsTo(TagihanMahasiswa::class, 'TagihanMahasiswaID', 'ID');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'MhswID', 'ID');
    }

    public function jenisBiaya()
    {
        return $this->belongsTo(JenisBiaya::class, 'JenisBiayaID', 'ID');
    }
}
