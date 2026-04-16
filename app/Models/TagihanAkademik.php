<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagihanAkademik extends Model
{
    protected $table = 'tagihan_akademik';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'MhswID', 'ID');
    }

    public function opsiMahasiswa()
    {
        return $this->hasOne(OpsiMahasiswa::class, 'MhswID', 'MhswID')
            ->whereColumn('TahunID', 'tagihan_akademik.TahunID');
    }
}
