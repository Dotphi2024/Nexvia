<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique(); // e.g. NEX-2026-9812
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('product_name');
            $table->string('model_code')->nullable();
            $table->decimal('mrp', 10, 2);
            $table->decimal('booking_amount', 10, 2);
            $table->decimal('balance_amount', 10, 2);
            $table->date('booking_date');
            $table->date('balance_due_date');
            $table->string('payment_type')->default('booking_20'); // booking_20, full_payment
            $table->string('payment_status')->default('paid'); // paid (for booking), balance_due, fully_paid
            $table->string('booking_status')->default('booked'); // booked, balance_paid, completed, cancelled
            $table->string('transfer_status')->default('original'); // original, transferred
            $table->boolean('non_refundable_accepted')->default(true);
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('shipping_address');
            $table->string('pincode');
            $table->string('city');
            $table->string('state');
            $table->string('qr_code_hash')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
