<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
    
            if (! Schema::hasColumn('items', 'deleted_at')) {
                $table->softDeletes();
            }

            $table->dropUnique('items_sku_unique');

       
            $table->unique(['sku', 'deleted_at'], 'items_sku_deleted_unique');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropUnique('items_sku_deleted_unique');
            $table->unique('sku', 'items_sku_unique');
        });
    }
};
