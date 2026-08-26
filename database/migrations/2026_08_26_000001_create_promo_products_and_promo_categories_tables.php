<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_id')->constrained('promos')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['promo_id', 'product_id']);
        });

        Schema::create('promo_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_id')->constrained('promos')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('product_categories')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['promo_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_categories');
        Schema::dropIfExists('promo_products');
    }
};
