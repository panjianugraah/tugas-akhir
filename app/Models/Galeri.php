<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Galeri extends Model
{
    protected $table = 'galeri';

    protected $fillable = [
        'gambar',
        'deskripsi',
        'created_by',
    ];

    protected $appends = ['gambar_url'];

    public function getGambarUrlAttribute(): ?string
    {
        return $this->gambar
            ? asset('storage/' . $this->gambar)
            : null;
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
