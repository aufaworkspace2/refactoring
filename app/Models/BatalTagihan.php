<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatalTagihan extends Model
{
    protected $table = 'tagihan_mahasiswa';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'MhswID', 'ID');
    }

    public function jenisBiaya()
    {
        return $this->belongsTo(JenisBiaya::class, 'JenisBiayaID', 'ID');
    }

    public function cicilan()
    {
        return $this->hasOne(CicilanTagihanMahasiswa::class, 'TagihanMahasiswaID', 'ID');
    }
}
