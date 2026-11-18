<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_ppdb', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->string('nisn', 10)->unique();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('asal_sekolah');
            $table->string('nama_orang_tua');
            $table->string('no_hp', 20);
            $table->string('email')->nullable();
            $table->text('alamat');
            $table->enum('status', ['menunggu', 'diterima', 'ditolak'])->default('menunggu');
            $table->timestamps();
        });
    }
};