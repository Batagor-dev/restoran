<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            // Per-outlet price override. NULL means fallback to products.price.
            $table->decimal('price', 15, 2)->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
