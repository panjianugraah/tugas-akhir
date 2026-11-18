<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    protected $table = 'guru';

    protected $fillable = [
        'nip',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'mata_pelajaran',
        'no_hp',
        'email',
        'alamat',
        'status',
        'mata_pelajaran_id'
    ];

    public function kelasWali()
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }

    public function mapel()
    {
        return $this->belongsTo( MataPelajaran::class, 'mata_pelajaran_id');
    }
}
