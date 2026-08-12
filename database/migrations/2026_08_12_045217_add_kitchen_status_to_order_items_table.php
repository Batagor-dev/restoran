<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->enum('kitchen_status', ['pending', 'cooking', 'ready', 'served'])->default('pending')->after('notes');
            $table->timestamp('ready_at')->nullable()->after('kitchen_status');
            $table->foreignId('cooked_by')->nullable()->constrained('users')->onDelete('set null')->after('ready_at');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['cooked_by']);
            $table->dropColumn(['kitchen_status', 'ready_at', 'cooked_by']);
        });
    }
};