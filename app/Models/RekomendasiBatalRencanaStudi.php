<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekomendasiBatalRencanaStudi extends Model
{
    protected $table = 'rekomendasi_batal_rencanastudi';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'MhswID', 'ID');
    }

    public function rencanaStudi()
    {
        return $this->belongsTo(RencanaStudi::class, 'rencanastudiID', 'ID');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'ID');
    }
}
