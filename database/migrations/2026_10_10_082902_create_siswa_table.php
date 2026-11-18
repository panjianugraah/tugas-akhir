<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // create_siswa_table
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ppdb_id')->nullable()->unique()
                ->constrained('data_ppdb')->nullOnDelete();
            $table->foreignId('kelas_id')->nullable()
                ->constrained('kelas')->nullOnDelete();
            $table->string('nis', 20)->unique();
            $table->string('nisn', 10)->unique();
            $table->string('nama_lengkap');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir', 100);
            $table->date('tanggal_lahir');
            $table->string('nama_orang_tua');
            $table->string('no_hp', 20);
            $table->string('email')->nullable();
            $table->text('alamat');
            $table->enum('status', ['aktif', 'lulus', 'pindah', 'keluar'])->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
