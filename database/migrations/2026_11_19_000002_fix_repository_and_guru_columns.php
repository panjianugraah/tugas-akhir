<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repository', function (Blueprint $table) {
            $table->foreignId('mata_pelajaran_id')
                ->nullable()
                ->after('id')
                ->constrained('mata_pelajaran')
                ->nullOnDelete();
        });

        Schema::table('repository', function (Blueprint $table) {
            $table->renameColumn('nama_asli', 'nama_file');
        });

        Schema::table('guru', function (Blueprint $table) {
            $table->foreignId('mata_pelajaran_id')
                ->nullable()
                ->after('tanggal_lahir')
                ->constrained('mata_pelajaran')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mata_pelajaran_id');
        });

        Schema::table('repository', function (Blueprint $table) {
            $table->renameColumn('nama_file', 'nama_asli');
        });

        Schema::table('repository', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mata_pelajaran_id');
        });
    }
};
