<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Menyimpan promo yang dipakai pada transaksi (untuk usage tracking)
            $table->foreignId('promo_id')
                ->nullable()
                ->after('discount')
                ->constrained('promos')
                ->nullOnDelete();

            $table->index(['promo_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['promo_id']);
            $table->dropIndex(['promo_id', 'customer_id']);
            $table->dropColumn('promo_id');
        });
    }
};
