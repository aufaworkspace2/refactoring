<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeteranganStatusMahasiswa extends Model
{
    protected $table = 'keteranganstatusmahasiswa';
    protected $primaryKey = 'ID';
    public $timestamps = false; // Legacy table doesn't have Laravel's created_at/updated_at

    protected $fillable = [
        'ProdiID',
        'TahunID',
        'MhswID',
        'StatusMahasiswaID',
        'Status',
        'Nomor_Surat',
        'Mulai_Semester',
        'Akhir_Semester',
        'Alasan',
        'Tgl',
    ];
}
