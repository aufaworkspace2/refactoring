<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PanduanPembayaran extends Model
{
    protected $table = 'panduan_pembayaran';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];
}
