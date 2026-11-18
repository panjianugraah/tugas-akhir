<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    protected $table = 'mata_pelajaran';

    protected $fillable = [
        'nama',
    ];

    public function materi()
    {
        return $this->hasMany(Repository::class, 'mata_pelajaran_id');
    }

    public function guru()
    {
        return $this->hasMany( Guru::class, 'mata_pelajaran_id');
    }
}
