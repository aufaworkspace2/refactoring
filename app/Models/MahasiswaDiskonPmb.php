<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MahasiswaDiskonPmb extends Model
{
    protected $table = 'mahasiswa';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get draft tagihan with diskon
     */
    public function draftTagihan()
    {
        return $this->hasMany(DraftTagihanMahasiswa::class, 'MhswID', 'ID');
    }
}
