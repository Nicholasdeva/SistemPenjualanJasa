<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_profiles', function (Blueprint $table) {
            $table->foreignId('verified_by')->nullable()->constrained('users')->restrictOnDelete();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('moderated_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->boolean('moderation_locked')->default(false);
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->constrained('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_id');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('moderation_locked');
            $table->dropConstrainedForeignId('moderated_by');
        });

        Schema::table('seller_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verified_by');
        });
    }
};
