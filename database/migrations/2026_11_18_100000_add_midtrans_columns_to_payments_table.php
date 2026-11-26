<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hanya tambah kolom yang belum ada (sebagian sudah dibuat migration lain)
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'midtrans_order_id')) {
                $table->string('midtrans_order_id')->nullable()->unique();
            }
            if (! Schema::hasColumn('payments', 'transaction_id')) {
                $table->string('transaction_id')->nullable();
            }
            if (! Schema::hasColumn('payments', 'payment_type')) {
                $table->string('payment_type')->nullable();
            }
            if (! Schema::hasColumn('payments', 'snap_token')) {
                $table->string('snap_token')->nullable();
            }
            if (! Schema::hasColumn('payments', 'raw_notification')) {
                $table->json('raw_notification')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Hanya hapus kolom milik migration ini
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'midtrans_order_id')) {
                $table->dropUnique(['midtrans_order_id']);
                $table->dropColumn('midtrans_order_id');
            }
            if (Schema::hasColumn('payments', 'raw_notification')) {
                $table->dropColumn('raw_notification');
            }
        });
    }
};