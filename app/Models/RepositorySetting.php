<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepositorySetting extends Model
{
    protected $fillable = [
        'kode_akses',
        'versi',
    ];

    protected $hidden = ['kode_akses'];

    protected $casts = [
        'versi' => 'integer',
    ];
}