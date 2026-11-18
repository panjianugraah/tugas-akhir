<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataPpdb extends Model
{
    protected $table = 'data_ppdb';

    protected $fillable = [
        'periode_ppdb_id',
        'nama_lengkap',
        'nisn',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'asal_sekolah',
        'nama_orang_tua',
        'no_hp',
        'email',
        'alamat',
        'status'
    ];

    public function siswa()
    {
        return $this->hasOne(Siswa::class, 'ppdb_id');
    }

    public function periodePpdb()
    {
        return $this->belongsTo(PeriodePpdb::class, 'periode_ppdb_id');
    }
}
