<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Customer Category Referral Progress
        Schema::create('customer_category_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->integer('referral_count')->default(0);
            $table->decimal('current_tier_percentage', 5, 2)->default(10.00); // 10, 12, 15, 18, 20
            $table->timestamps();

            $table->unique(['user_id', 'category_id']);
        });

        // 2. Detailed Referrals Track
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('referee_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->integer('sequence_in_category')->default(1);
            $table->decimal('benefit_percentage', 5, 2)->default(10.00);
            $table->decimal('product_value', 10, 2);
            $table->decimal('credit_earned', 10, 2);
            $table->string('status')->default('credited'); // pending, credited, redeemed
            $table->timestamps();
        });

        // 3. Orders & Order Items
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('booking_amount', 10, 2)->default(0.00);
            $table->decimal('balance_amount', 10, 2)->default(0.00);
            $table->decimal('product_credit_applied', 10, 2)->default(0.00);
            $table->string('payment_type')->default('booking_20'); // booking_20, full_payment
            $table->string('payment_method')->default('upi'); // upi, card, net_banking, emi
            $table->string('payment_status')->default('paid'); // paid, balance_due, fully_paid
            $table->string('order_status')->default('confirmed'); // confirmed, processing, dispatched, delivered
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('shipping_address');
            $table->string('city');
            $table->string('state');
            $table->string('pincode');
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('product_name');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_mrp', 10, 2);
            $table->decimal('unit_booking_amount', 10, 2);
            $table->timestamps();
        });

        // 4. Order Deliveries (7-stage tracking)
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('set null');
            $table->string('tracking_number')->unique();
            $table->string('stage')->default('order_confirmed');
            // order_confirmed, processing, dispatched, out_for_delivery, delivered, installation_pending, installation_completed
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('installation_completed_at')->nullable();
            $table->timestamps();
        });

        // 5. Automatic Warranties
        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('set null');
            $table->string('serial_number')->unique();
            $table->date('purchase_date');
            $table->date('warranty_start');
            $table->date('warranty_end');
            $table->string('status')->default('active'); // active, expired, claimed
            $table->string('warranty_document_path')->nullable();
            $table->timestamps();
        });

        // 6. Installation Tracking
        Schema::create('installations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('set null');
            $table->string('technician_name')->nullable();
            $table->string('technician_phone')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->string('status')->default('pending'); // pending, scheduled, in_progress, completed, rescheduled
            $table->integer('rating')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();
        });

        // 7. Warehouse Inventory Control
        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('sku')->unique();
            $table->string('serial_number')->nullable();
            $table->string('warehouse_name')->default('Main Warehouse');
            $table->integer('stock_qty')->default(0);
            $table->integer('reserved_qty')->default(0);
            $table->integer('sold_qty')->default(0);
            $table->integer('low_stock_threshold')->default(5);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stocks');
        Schema::dropIfExists('installations');
        Schema::dropIfExists('warranties');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('customer_category_progress');
    }
};
