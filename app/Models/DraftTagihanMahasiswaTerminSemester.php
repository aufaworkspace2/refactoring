<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DraftTagihanMahasiswaTerminSemester extends Model
{
    protected $table = 'draft_tagihan_mahasiswa_termin_semester';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    public function draftSemester()
    {
        return $this->belongsTo(DraftTagihanMahasiswaSemester::class, 'DraftTagihanMahasiswaSemesterID', 'ID');
    }
}
