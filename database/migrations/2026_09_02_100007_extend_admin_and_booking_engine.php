<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extend products table
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'sku')) {
                $table->string('sku')->nullable()->after('model_code');
            }
            if (!Schema::hasColumn('products', 'video_url')) {
                $table->string('video_url')->nullable()->after('main_image');
            }
            if (!Schema::hasColumn('products', 'offer_text')) {
                $table->string('offer_text')->nullable()->after('video_url');
            }
        });

        // Booking engine global settings
        Schema::create('booking_engine_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Service & Warranty requests
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('set null');
            $table->string('subject');
            $table->string('service_type')->default('warranty'); // warranty, installation, repair
            $table->string('status')->default('open'); // open, in_progress, resolved
            $table->text('details');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
        Schema::dropIfExists('booking_engine_settings');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sku', 'video_url', 'offer_text']);
        });
    }
};
