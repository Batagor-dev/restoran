<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('product_categories') && !Schema::hasColumn('product_categories', 'deleted_at')) {
            Schema::table('product_categories', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('product_categories') && Schema::hasColumn('product_categories', 'deleted_at')) {
            Schema::table('product_categories', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
