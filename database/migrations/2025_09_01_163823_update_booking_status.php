<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 0 (safe): make sure the column accepts both old & new values.
        // This ALTER will succeed regardless of what the data currently holds.
        DB::statement(
            "ALTER TABLE bookings 
             MODIFY COLUMN status ENUM('pending','confirmed','booked','completed','cancelled') 
             NOT NULL DEFAULT 'booked'"
        );

        // Step 1: Normalize all existing rows to a final valid value.
        // Here we map anything that's not in the final set to 'booked'.
        DB::table('bookings')
            ->whereNull('status')
            ->orWhereIn('status', ['pending', 'confirmed'])
            ->orWhereNotIn('status', ['booked', 'completed', 'cancelled'])
            ->update(['status' => 'booked']);

        // Step 2: Now safely shrink the enum to just the final three values.
        DB::statement(
            "ALTER TABLE bookings 
             MODIFY COLUMN status ENUM('booked','completed','cancelled') 
             NOT NULL DEFAULT 'booked'"
        );
    }

    public function down(): void
    {
        DB::statement(
            "ALTER TABLE bookings 
             MODIFY COLUMN status ENUM('pending','confirmed','booked','completed','cancelled') 
             NOT NULL DEFAULT 'pending'"
        );
    }
};