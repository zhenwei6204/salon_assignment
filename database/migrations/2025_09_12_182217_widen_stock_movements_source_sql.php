<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `stock_movements` MODIFY `source` VARCHAR(40) NOT NULL DEFAULT 'manual'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `stock_movements` MODIFY `source` VARCHAR(10) NOT NULL DEFAULT 'manual'");
    }
};
