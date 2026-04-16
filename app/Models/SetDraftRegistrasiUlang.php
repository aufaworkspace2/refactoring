<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetDraftRegistrasiUlang extends Model
{
    protected $table = 'mahasiswa';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
