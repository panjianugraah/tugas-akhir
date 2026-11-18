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
        // create_kelas_table
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelas', 50);          // contoh: VII A
            $table->unsignedTinyInteger('tingkat');    // 7, 8, 9
            $table->string('tahun_ajaran', 9);         // contoh: 2026/2027
            $table->unsignedInteger('kapasitas')->default(32);
            $table->foreignId('wali_kelas_id')->nullable()
                ->constrained('guru')->nullOnDelete();
            $table->timestamps();

            $table->unique(['nama_kelas', 'tahun_ajaran']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
