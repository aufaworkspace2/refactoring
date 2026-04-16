<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingNomorInvoice extends Model
{
    protected $table = 'pmb_tbl_format_invoice';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'nomor_invoice' => 'array',
    ];
}
