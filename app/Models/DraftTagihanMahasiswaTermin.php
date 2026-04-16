<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DraftTagihanMahasiswaTermin extends Model
{
    protected $table = 'draft_tagihan_mahasiswa_termin';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    public function draftTagihan()
    {
        return $this->belongsTo(DraftTagihanMahasiswa::class, 'DraftTagihanMahasiswaID', 'ID');
    }
}
