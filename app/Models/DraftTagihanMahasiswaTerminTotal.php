<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DraftTagihanMahasiswaTerminTotal extends Model
{
    protected $table = 'draft_tagihan_mahasiswa_termin_total';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'MhswID', 'ID');
    }
}
