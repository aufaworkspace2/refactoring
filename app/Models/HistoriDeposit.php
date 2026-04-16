<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoriDeposit extends Model
{
    protected $table = 'histori_deposit';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'MhswID', 'ID');
    }

    public function jenisBiaya()
    {
        return $this->belongsTo(JenisBiaya::class, 'JenisBiayaID', 'ID');
    }

    public function deposit()
    {
        return $this->belongsTo(DepositMahasiswa::class, 'MhswID', 'MhswID');
    }
}
