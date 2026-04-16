<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositMahasiswa extends Model
{
    protected $table = 'deposit_mahasiswa';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'MhswID', 'ID');
    }

    public function history()
    {
        return $this->hasMany(HistoriDeposit::class, 'MhswID', 'MhswID');
    }
}
