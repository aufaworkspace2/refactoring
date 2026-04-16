<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DraftTagihanMahasiswaDetail extends Model
{
    protected $table = 'draft_tagihan_mahasiswa_detail';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    public function draftTagihan()
    {
        return $this->belongsTo(DraftTagihanMahasiswa::class, 'DraftTagihanMahasiswaID', 'ID');
    }
}
