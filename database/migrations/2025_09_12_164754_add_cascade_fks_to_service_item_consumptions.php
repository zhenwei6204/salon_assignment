

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
    
        Schema::table('service_item_consumptions', function (Blueprint $table) {
        
            $table->unsignedBigInteger('item_id')->change();

    
            $table->integer('service_id')->change();
        });

     
        try { DB::statement("ALTER TABLE service_item_consumptions DROP FOREIGN KEY sic_item_fk"); } catch (\Throwable $e) {}
        try { DB::statement("ALTER TABLE service_item_consumptions DROP FOREIGN KEY sic_service_fk"); } catch (\Throwable $e) {}

     
        Schema::table('service_item_consumptions', function (Blueprint $table) {
      
            $table->foreign('item_id')
                  ->references('id')->on('items')
                  ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('service_id')
                  ->references('id')->on('services')
                  ->onUpdate('cascade')->onDelete('cascade');

        });
    }

    public function down(): void
    {
        Schema::table('service_item_consumptions', function (Blueprint $table) {     
            $table->dropForeign(['item_id']);
            $table->dropForeign(['service_id']);
        });
    }
};
