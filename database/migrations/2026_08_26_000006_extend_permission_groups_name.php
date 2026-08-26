<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Selaraskan dengan menu_groups.name (varchar 70) agar nama grup
        // tidak mudah terpotong saat dibuat dari UI.
        Schema::table('permission_groups', function (Blueprint $table) {
            $table->string('name', 70)->change();
        });
    }

    public function down(): void
    {
        Schema::table('permission_groups', function (Blueprint $table) {
            $table->string('name', 20)->change();
        });
    }
};
