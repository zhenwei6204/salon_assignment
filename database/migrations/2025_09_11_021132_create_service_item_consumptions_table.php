<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_item_consumptions', function (Blueprint $table) {
            $table->id();

            // Match services.id (INT UNSIGNED)
            $table->unsignedInteger('service_id');
            $table
                ->foreign('service_id')
                ->references('id')->on('services')
                ->cascadeOnDelete();

            // Match items.id (BIGINT UNSIGNED)
            $table->unsignedBigInteger('item_id');
            $table
                ->foreign('item_id')
                ->references('id')->on('items')
                ->cascadeOnDelete();

            $table->decimal('qty_per_service', 10, 2)->default(1);
            $table->timestamps();
            $table->unique(['service_id', 'item_id']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('service_item_consumptions');
    }
};
