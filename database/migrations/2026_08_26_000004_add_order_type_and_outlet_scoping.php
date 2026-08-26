<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tipe penyajian pesanan (dine-in / take-away)
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('order_type', ['dine_in', 'takeaway'])
                ->default('dine_in')
                ->after('payment_method');
        });

        // 2. outlet_id pada stock_movements (backfill dari product_stocks)
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('outlet_id')->nullable()->after('product_stock_id');
        });

        DB::statement(
            'UPDATE stock_movements SET outlet_id = product_stocks.outlet_id FROM product_stocks WHERE stock_movements.product_stock_id = product_stocks.id'
        );

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('outlet_id')->nullable(false)->change();
            $table->foreign('outlet_id')->references('id')->on('outlets')->cascadeOnDelete();
            $table->index('outlet_id');
        });

        // 3. outlet_id pada promos & customer_promos (NULL = global / semua outlet)
        Schema::table('promos', function (Blueprint $table) {
            $table->foreignId('outlet_id')->nullable()->after('name');
            $table->foreign('outlet_id')->references('id')->on('outlets')->nullOnDelete();
            $table->index('outlet_id');
        });

        Schema::table('customer_promos', function (Blueprint $table) {
            $table->foreignId('outlet_id')->nullable()->after('name');
            $table->foreign('outlet_id')->references('id')->on('outlets')->nullOnDelete();
            $table->index('outlet_id');
        });
    }

    public function down(): void
    {
        Schema::table('customer_promos', function (Blueprint $table) {
            $table->dropForeign(['outlet_id']);
            $table->dropIndex(['outlet_id']);
            $table->dropColumn('outlet_id');
        });

        Schema::table('promos', function (Blueprint $table) {
            $table->dropForeign(['outlet_id']);
            $table->dropIndex(['outlet_id']);
            $table->dropColumn('outlet_id');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['outlet_id']);
            $table->dropIndex(['outlet_id']);
            $table->dropColumn('outlet_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('order_type');
        });
    }
};
