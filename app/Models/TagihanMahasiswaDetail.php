<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagihanMahasiswaDetail extends Model
{
    protected $table = 'tagihan_mahasiswa_detail';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    public function tagihan()
    {
        return $this->belongsTo(TagihanMahasiswa::class, 'TagihanMahasiswaID', 'ID');
    }

    public function draftDetail()
    {
        return $this->belongsTo(DraftTagihanMahasiswaDetail::class, 'DraftTagihanMahasiswaDetailID', 'ID');
    }
}
