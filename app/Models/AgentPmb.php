<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentPmb extends Model
{
    protected $table = 'pmb_tbl_agent';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = [];
}
