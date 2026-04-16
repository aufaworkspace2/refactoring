<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagihanMahasiswaTermin extends Model
{
    protected $table = 'tagihan_mahasiswa_termin';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    public function tagihan()
    {
        return $this->belongsTo(TagihanMahasiswa::class, 'TagihanMahasiswaID', 'ID');
    }

    public function draftTermin()
    {
        return $this->belongsTo(DraftTagihanMahasiswaTermin::class, 'DraftTagihanMahasiswaTerminID', 'ID');
    }
}
