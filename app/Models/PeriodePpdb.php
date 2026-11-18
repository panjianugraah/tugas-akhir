<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodePpdb extends Model
{
    protected $table = 'periode_ppdb';

    protected $fillable = [
        'nama_periode',
        'tanggal_mulai',
        'tanggal_selesai',
        'kuota',
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'kuota' => 'integer',
    ];

    public function pendaftar()
    {
        return $this->hasMany( DataPpdb::class,'periode_ppdb_id');
    }
}