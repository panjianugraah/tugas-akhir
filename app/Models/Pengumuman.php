<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Pengumuman extends Model
{
    protected $table = 'pengumuman';

    protected $fillable = [
        'judul',
        'slug',
        'isi',
        'gambar',
        'berlaku_sampai',
        'status',
        'created_by',
    ];

    protected $casts = [
        'berlaku_sampai' => 'date:Y-m-d',
    ];

    // Ikut dikirim di JSON: URL lengkap gambar
    protected $appends = ['gambar_url'];

    public function getGambarUrlAttribute(): ?string
    {
        return $this->gambar ? asset('storage/' . $this->gambar) : null;
    }

    protected static function booted(): void
    {
        // Slug dibuat otomatis dari judul
        static::saving(function (Pengumuman $p) {
            if ($p->isDirty('judul') || ! $p->slug) {
                $p->slug = Str::slug($p->judul) . '-' . Str::lower(Str::random(5));
            }
        });

        // Gambar ikut terhapus saat pengumuman dihapus
        static::deleting(function (Pengumuman $p) {
            if ($p->gambar) {
                Storage::disk('public')->delete($p->gambar);
            }
        });
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
