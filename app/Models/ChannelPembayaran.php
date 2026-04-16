<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelPembayaran extends Model
{
    protected $table = 'channel_pembayaran';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];
}
