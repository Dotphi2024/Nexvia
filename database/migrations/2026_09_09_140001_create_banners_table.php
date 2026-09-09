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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('badge_text')->nullable();
            $table->string('image');
            $table->string('banner_type')->default('hero'); // hero, promo, referral, popup
            $table->string('target_type')->default('url');  // url, category, product, booking, none
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('link_url')->nullable();
            $table->string('button_text')->nullable()->default('Shop Now');
            $table->integer('sort_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('clicks_count')->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
