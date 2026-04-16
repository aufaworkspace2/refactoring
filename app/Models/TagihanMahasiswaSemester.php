<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagihanMahasiswaSemester extends Model
{
    protected $table = 'tagihan_mahasiswa_semester';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'MhswID', 'ID');
    }

    public function tagihans()
    {
        return $this->hasMany(TagihanMahasiswa::class, 'TagihanMahasiswaSemesterID', 'ID');
    }

    public function draftSemester()
    {
        return $this->belongsTo(DraftTagihanMahasiswaSemester::class, 'DraftTagihanMahasiswaSemesterID', 'ID');
    }

    public function terminSemesters()
    {
        return $this->hasMany(TagihanMahasiswaTerminSemester::class, 'TagihanMahasiswaSemesterID', 'ID');
    }
}
