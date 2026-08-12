<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code_invoice')->unique();
            $table->foreignId('outlet_id')->constrained()->onDelete('cascade');
            $table->foreignId('cashier_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('table_id')->nullable()->constrained('dining_tables')->onDelete('set null');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->string('customer_name')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->enum('payment_method', ['cash', 'qris', 'debit', 'credit'])->default('cash');
            $table->enum('status_order', ['pending', 'processing', 'completed', 'cancelled'])->default('pending');
            $table->softDeletes();
            $table->timestamps();
            $table->enum('payment_method', ['cash', 'qris', 'debit', 'credit'])->default('cash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};