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
            

            $table->unsignedInteger('booking_id')->nullable()->change();
        });

      
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreign('booking_id', 'stock_movements_booking_id_foreign')
                  ->references('id')->on('bookings')
                  ->nullOnDelete();   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign('stock_movements_booking_id_foreign');
            
            $table->unsignedBigInteger('booking_id')->nullable()->change();
        });
    }
};
