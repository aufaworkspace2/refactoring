<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagihanMahasiswa extends Model
{
    protected $table = 'tagihan_mahasiswa';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'MhswID', 'ID');
    }

    public function semester()
    {
        return $this->belongsTo(TagihanMahasiswaSemester::class, 'TagihanMahasiswaSemesterID', 'ID');
    }

    public function jenisBiaya()
    {
        return $this->belongsTo(JenisBiaya::class, 'JenisBiayaID', 'ID');
    }

    public function termins()
    {
        return $this->hasMany(TagihanMahasiswaTermin::class, 'TagihanMahasiswaID', 'ID');
    }

    public function details()
    {
        return $this->hasMany(TagihanMahasiswaDetail::class, 'TagihanMahasiswaID', 'ID');
    }

    public function draftTagihan()
    {
        return $this->belongsTo(DraftTagihanMahasiswa::class, 'DraftTagihanMahasiswaID', 'ID');
    }
}
