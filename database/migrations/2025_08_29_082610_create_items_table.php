<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $t) {
            $t->id();
            $t->string('sku')->unique();          // e.g. WAX-01
            $t->string('name');                   // e.g. Hair Wax
            $t->string('unit')->default('pcs');   // pcs, ml, g
            $t->unsignedInteger('reorder_level')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
