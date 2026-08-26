<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // normal | refunded | voided
            $table->enum('status_transaction', ['normal', 'refunded', 'voided'])
                ->default('normal')
                ->after('status_order');

            $table->text('void_reason')->nullable()->after('status_transaction');
            $table->timestamp('voided_at')->nullable()->after('void_reason');
            $table->foreignId('voided_by')->nullable()->after('voided_at')
                ->constrained('users')->nullOnDelete();

            $table->text('refund_reason')->nullable()->after('voided_by');
            $table->timestamp('refunded_at')->nullable()->after('refund_reason');
            $table->foreignId('refunded_by')->nullable()->after('refunded_at')
                ->constrained('users')->nullOnDelete();

            $table->index(['status_transaction', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['voided_by']);
            $table->dropForeign(['refunded_by']);
            $table->dropIndex(['status_transaction', 'created_at']);
            $table->dropColumn([
                'status_transaction',
                'void_reason', 'voided_at', 'voided_by',
                'refund_reason', 'refunded_at', 'refunded_by',
            ]);
        });
    }
};
