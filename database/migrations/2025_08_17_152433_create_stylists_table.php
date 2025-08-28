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
    Schema::create('stylists', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('title')->nullable();
    $table->text('bio')->nullable();
    $table->integer('experience_years')->default(0);
    $table->text('specializations')->nullable();
    $table->decimal('rating', 3, 2)->default(0.00);
    $table->integer('review_count')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stylists');
    }
};
