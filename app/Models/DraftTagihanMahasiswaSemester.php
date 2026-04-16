<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DraftTagihanMahasiswaSemester extends Model
{
    protected $table = 'draft_tagihan_mahasiswa_semester';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'MhswID', 'ID');
    }

    public function draftTagihans()
    {
        return $this->hasMany(DraftTagihanMahasiswa::class, 'DraftTagihanMahasiswaSemesterID', 'ID');
    }
}
