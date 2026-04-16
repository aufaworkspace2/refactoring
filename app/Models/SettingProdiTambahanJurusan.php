<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingProdiTambahanJurusan extends Model
{
    protected $table = 'setting_prodi_tambahan_jurusan';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'JalurID' => 'string',
        'ProdiID' => 'string',
        'ListProdi2' => 'string',
        'ListProdi3' => 'string',
    ];
}
