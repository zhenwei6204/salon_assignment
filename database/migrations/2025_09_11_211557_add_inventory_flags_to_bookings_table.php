$m2 = Get-ChildItem database/migrations -Filter "*add_inventory_flags_to_bookings_table*.php" | Select-Object -First 1
Set-Content $m2.FullName @'
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
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'inventory_deducted_at')) {
                $table->timestamp('inventory_deducted_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('bookings', 'inventory_reverted_at')) {
                $table->timestamp('inventory_reverted_at')->nullable()->after('inventory_deducted_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'inventory_reverted_at')) {
                $table->dropColumn('inventory_reverted_at');
            }
            if (Schema::hasColumn('bookings', 'inventory_deducted_at')) {
                $table->dropColumn('inventory_deducted_at');
            }
        });
    }
};
