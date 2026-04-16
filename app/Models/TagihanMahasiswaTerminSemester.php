<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagihanMahasiswaTerminSemester extends Model
{
    protected $table = 'tagihan_mahasiswa_termin_semester';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    public function semester()
    {
        return $this->belongsTo(TagihanMahasiswaSemester::class, 'TagihanMahasiswaSemesterID', 'ID');
    }

    public function draftTerminSemester()
    {
        return $this->belongsTo(DraftTagihanMahasiswaTerminSemester::class, 'DraftTagihanMahasiswaTerminSemesterID', 'ID');
    }
}
