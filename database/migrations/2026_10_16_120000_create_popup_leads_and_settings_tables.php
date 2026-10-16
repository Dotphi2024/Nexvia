<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Popup Leads Table
        if (!Schema::hasTable('popup_leads')) {
            Schema::create('popup_leads', function (Blueprint $table) {
                $table->id();
                $table->string('name', 191);
                $table->string('mobile', 25);
                $table->string('email', 191)->nullable();
                $table->text('notes')->nullable();
                $table->string('source_url', 500)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('status', 30)->default('pending'); // pending, contacted, interested, converted, closed
                $table->text('admin_notes')->nullable();
                $table->timestamp('contacted_at')->nullable();
                $table->timestamps();

                $table->index('mobile');
                $table->index('status');
                $table->index('created_at');
            });
        }

        // 2. Popup Settings Table
        if (!Schema::hasTable('popup_settings')) {
            Schema::create('popup_settings', function (Blueprint $table) {
                $table->id();
                $table->boolean('is_enabled')->default(true);
                $table->integer('delay_seconds')->default(7); // 5 to 10 seconds default
                $table->string('title')->default('Get Exclusive Offers & Instant Callback');
                $table->text('subtitle')->nullable();
                $table->string('button_text')->default('Request Instant Callback');
                $table->text('success_message')->nullable();
                $table->integer('reappear_hours')->default(24);
                $table->timestamps();
            });

            // Insert initial default configuration
            DB::table('popup_settings')->insert([
                'is_enabled'        => true,
                'delay_seconds'     => 7,
                'title'             => 'Get Exclusive Offers & Instant Callback',
                'subtitle'          => 'Enter your name and mobile number. Our product specialist will call you with exclusive pricing and full product details.',
                'button_text'       => 'Request Callback Now',
                'success_message'   => 'Thank you! We have received your request. Our specialist will call you shortly.',
                'reappear_hours'    => 24,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('popup_leads');
        Schema::dropIfExists('popup_settings');
    }
};
