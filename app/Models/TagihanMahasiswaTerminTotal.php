<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagihanMahasiswaTerminTotal extends Model
{
    protected $table = 'tagihan_mahasiswa_termin_total';
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

    public function draftTerminTotal()
    {
        return $this->belongsTo(DraftTagihanMahasiswaTerminTotal::class, 'DraftTagihanMahasiswaTerminTotalID', 'ID');
    }
}
