<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
     
        Schema::table('service_item_consumptions', function (Blueprint $table) {
            // If you previously attempted FKs, drop them defensively
            try { $table->dropForeign('service_item_consumptions_item_id_foreign'); } catch (\Throwable $e) {}
            try { $table->dropForeign('service_item_consumptions_service_id_foreign'); } catch (\Throwable $e) {}
        });

    
        DB::statement("
            DELETE sic
            FROM service_item_consumptions sic
            LEFT JOIN items i ON i.id = sic.item_id
            WHERE i.id IS NULL
        ");
        DB::statement("
            DELETE sic
            FROM service_item_consumptions sic
            LEFT JOIN services s ON s.id = sic.service_id
            WHERE s.id IS NULL
        ");

     
        Schema::table('service_item_consumptions', function (Blueprint $table) {
         
            $table->unsignedBigInteger('item_id')->change();

         
            $table->integer('service_id')->change();
        });

        
        Schema::table('service_item_consumptions', function (Blueprint $table) {
            $table->index('item_id');
            $table->index('service_id');

            $table->foreign('item_id')
                ->references('id')->on('items')
                ->cascadeOnDelete();

            $table->foreign('service_id')
                ->references('id')->on('services')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_item_consumptions', function (Blueprint $table) {
            try { $table->dropForeign(['item_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['service_id']); } catch (\Throwable $e) {}
            try { $table->dropIndex(['item_id']); } catch (\Throwable $e) {}
            try { $table->dropIndex(['service_id']); } catch (\Throwable $e) {}
        });
    }
};
