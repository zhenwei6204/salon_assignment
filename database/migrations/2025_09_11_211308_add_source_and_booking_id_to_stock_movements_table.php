$m1 = Get-ChildItem database/migrations -Filter "*add_source_and_booking_id_to_stock_movements_table*.php" | Select-Object -First 1
Set-Content $m1.FullName @'
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
            if (! Schema::hasColumn('stock_movements', 'source')) {
                $table->enum('source', ['manual', 'booking'])->default('manual')->after('type');
            }
            if (! Schema::hasColumn('stock_movements', 'booking_id')) {
                $table->foreignId('booking_id')->nullable()->after('source')
                      ->constrained('bookings')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_movements', 'source')) {
                $table->enum('source', ['manual', 'booking'])->default('manual')->after('type');
            }
            if (! Schema::hasColumn('stock_movements', 'booking_id')) {
                $table->foreignId('booking_id')->nullable()->after('source')
                      ->constrained('bookings')->nullOnDelete();
            }
        });
    }
};
