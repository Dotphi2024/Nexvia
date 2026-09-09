<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Admin-configurable referral stage percentages (NOT hardcoded)
        Schema::create('referral_stage_configs', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('stage_number');           // 1, 2, 3, 4, 5
            $table->decimal('incentive_percentage', 5, 2); // 10, 12, 15, 18, 20
            $table->decimal('activation_credit_percentage', 5, 2)->default(20.00); // own purchase reward
            $table->boolean('is_active')->default(true);
            $table->string('version', 20)->default('v1.0');
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();
        });

        // Self Dealer Wallet — per dealer summary
        Schema::create('self_dealer_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('total_earned', 12, 2)->default(0.00);      // Lifetime points earned
            $table->decimal('available_points', 12, 2)->default(0.00);  // Ready to redeem
            $table->decimal('pending_points', 12, 2)->default(0.00);    // Awaiting qualification
            $table->decimal('redeemed_points', 12, 2)->default(0.00);   // Used on purchases
            $table->decimal('reversed_points', 12, 2)->default(0.00);   // Cancelled/reversed
            $table->timestamps();

            $table->unique('user_id');
        });

        // Fraud Flags — for admin review
        Schema::create('fraud_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('referrer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('set null');
            $table->string('flag_type', 50);
            // duplicate_mobile, self_referral, duplicate_payment, repeated_cancellation,
            // suspicious_velocity, circular_transaction
            $table->string('flag_reason');
            $table->enum('status', ['pending_review', 'cleared', 'confirmed_fraud'])->default('pending_review');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fraud_flags');
        Schema::dropIfExists('self_dealer_wallets');
        Schema::dropIfExists('referral_stage_configs');
    }
};
