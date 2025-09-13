<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_movements', 'item_name')) {
                $table->string('item_name')->nullable()->after('item_id');
            }
        });

        Schema::table('stock_movements', function (Blueprint $table) {
        
            $table->unsignedBigInteger('item_id')->nullable()->change();

            try { $table->dropForeign('stock_movements_item_id_foreign'); } catch (\Throwable $e) {}

            $table->foreign('item_id')
                ->references('id')->on('items')
                ->onDelete('SET NULL'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
             try { $table->dropForeign(['item_id']); } catch (\Throwable $e) {}
        });
    }
};
