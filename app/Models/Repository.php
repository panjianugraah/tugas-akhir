<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Repository extends Model
{
    protected $table = 'repository';

    protected $fillable = [
        'mata_pelajaran_id',
        'judul',
        'deskripsi',
        'path',
        'nama_file',
        'ekstensi',
        'ukuran',
        'created_by',
    ];

    protected $hidden = ['path'];

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
