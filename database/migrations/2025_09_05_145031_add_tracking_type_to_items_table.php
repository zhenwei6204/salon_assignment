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
        Schema::table('items', function (Illuminate\Database\Schema\Blueprint $t) {
            $t->enum('tracking_type', ['unit', 'measure'])->default('unit')->after('unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Illuminate\Database\Schema\Blueprint $t) {
            $t->dropColumn('tracking_type');
        });
    }
};
