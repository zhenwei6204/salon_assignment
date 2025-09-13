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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
        
            // Foreign keys
            $table->unsignedInteger('stylist_id'); // matches stylists.id (int)
            $table->unsignedBigInteger('user_id'); // matches users.id (bigint)
        
            $table->tinyInteger('rating')->unsigned()->comment('1 to 5');
            $table->text('comment')->nullable();
            $table->timestamps();
        
            // Foreign key constraints
            $table->foreign('stylist_id')
                  ->references('id')->on('stylists')
                  ->onDelete('cascade');
        
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
