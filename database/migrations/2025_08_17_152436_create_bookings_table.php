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
       Schema::create('bookings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('service_id')->constrained()->onDelete('cascade');
    $table->foreignId('stylist_id')->constrained()->onDelete('cascade');
    $table->string('customer_name');
    $table->string('customer_email');
    $table->string('customer_phone');
    $table->date('booking_date');
    $table->time('booking_time');
    $table->time('end_time');
    $table->decimal('total_price', 10, 2);
    $table->string('status')->default('pending');
    $table->string('booking_reference')->unique();
    $table->text('special_requests')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
