<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop FK first if it exists (in case of partial attempts)
        try {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropForeign(['stylist_id']);
            });
        } catch (\Throwable $e) {
            // ignore if it didn't exist
        }

        // Change to SIGNED INT (raw SQL avoids needing doctrine/dbal)
        DB::statement('ALTER TABLE reviews MODIFY stylist_id INT NOT NULL');

        // Re-add the FK to stylists(id)
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreign('stylist_id')
                  ->references('id')->on('stylists')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Reverse (back to UNSIGNED INT)
        try {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropForeign(['stylist_id']);
            });
        } catch (\Throwable $e) {}

        DB::statement('ALTER TABLE reviews MODIFY stylist_id INT UNSIGNED NOT NULL');
    }
};
