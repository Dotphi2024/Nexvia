<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('name');
            $table->string('model_code')->nullable();
            $table->string('slug')->unique();
            $table->decimal('mrp', 10, 2);
            $table->integer('booking_percentage')->default(20);
            $table->decimal('booking_amount', 10, 2);
            $table->decimal('balance_amount', 10, 2);
            $table->integer('stock')->default(50);
            $table->string('main_image')->nullable();
            $table->json('gallery')->nullable();
            $table->json('key_features')->nullable();
            $table->json('specs')->nullable();
            $table->string('warranty_info')->default('1 Year Brand Warranty');
            $table->string('installation_info')->default('Free Installation Available');
            $table->string('delivery_info')->default('Dispatched within 3-5 business days');
            $table->boolean('is_featured')->default(false);
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
