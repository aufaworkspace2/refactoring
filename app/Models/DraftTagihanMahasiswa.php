<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DraftTagihanMahasiswa extends Model
{
    protected $table = 'draft_tagihan_mahasiswa';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    // Relationships
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'MhswID', 'ID');
    }

    public function tahun()
    {
        return $this->belongsTo(Tahun::class, 'Periode', 'ID');
    }

    public function jenisBiaya()
    {
        return $this->belongsTo(JenisBiaya::class, 'JenisBiayaID', 'ID');
    }

    public function draftSemester()
    {
        return $this->belongsTo(DraftTagihanMahasiswaSemester::class, 'DraftTagihanMahasiswaSemesterID', 'ID');
    }

    public function termins()
    {
        return $this->hasMany(DraftTagihanMahasiswaTermin::class, 'DraftTagihanMahasiswaID', 'ID');
    }

    public function details()
    {
        return $this->hasMany(DraftTagihanMahasiswaDetail::class, 'DraftTagihanMahasiswaID', 'ID');
    }
}
