<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periode_ppdb', function (Blueprint $table) {
            $table->id();
            $table->string('nama_periode', 150);
            $table->dateTime('tanggal_mulai');
            $table->dateTime('tanggal_selesai');
            $table->unsignedInteger('kuota');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periode_ppdb');
    }
};