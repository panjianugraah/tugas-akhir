<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_ppdb', function (Blueprint $table) {
            $table->foreignId('periode_ppdb_id')
                ->nullable()
                ->after('id')
                ->constrained('periode_ppdb')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('data_ppdb', function (Blueprint $table) {
            $table->dropConstrainedForeignId('periode_ppdb_id');
        });
    }
};
