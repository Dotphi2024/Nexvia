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
        Schema::table('users', function (Blueprint $table) {
            // OTP fields
            $table->string('otp', 6)->nullable()->after('phone');
            $table->timestamp('otp_expires_at')->nullable()->after('otp');

            // Phone verification
            $table->timestamp('phone_verified_at')->nullable()->after('otp_expires_at');

            // Plain API token (no Sanctum dependency)
            $table->string('api_token', 80)->nullable()->unique()->after('phone_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['otp', 'otp_expires_at', 'phone_verified_at', 'api_token']);
        });
    }
};
