<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';

    protected $fillable = [
        'ppdb_id', 'kelas_id', 'nis', 'nisn', 'nama_lengkap', 'jenis_kelamin',
        'tempat_lahir', 'tanggal_lahir', 'nama_orang_tua', 'no_hp', 'email',
        'alamat', 'status',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function ppdb()
    {
        return $this->belongsTo(DataPpdb::class, 'ppdb_id');
    }
}