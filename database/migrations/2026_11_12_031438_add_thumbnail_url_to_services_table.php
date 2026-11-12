<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tahap 2: gambar sampul jasa. ERD awal belum punya kolom gambar,
 * jadi ditambahkan lewat migration baru (migration lama tidak diubah).
 * Isinya path relatif di disk "public", misalnya "services/abc.jpg".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('thumbnail_url')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('thumbnail_url');
        });
    }
};
