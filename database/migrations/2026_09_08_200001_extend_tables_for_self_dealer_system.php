<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Extend users table — add missing Self Dealer fields only
        // (is_self_dealer, self_dealer_code, referral_code already exist)
        Schema::table('users', function (Blueprint $table) {
            $table->enum('self_dealer_status', ['inactive', 'pending', 'active', 'suspended'])->default('inactive')->after('self_dealer_code');
            $table->timestamp('self_dealer_activated_at')->nullable()->after('self_dealer_status');
            $table->foreignId('activation_booking_id')->nullable()->constrained('bookings')->onDelete('set null')->after('self_dealer_activated_at');
            $table->string('terms_version', 20)->nullable()->after('activation_booking_id');
            $table->timestamp('terms_accepted_at')->nullable()->after('terms_version');
        });

        // 2. Extend customer_category_progress — add stage & cycle columns
        Schema::table('customer_category_progress', function (Blueprint $table) {
            $table->tinyInteger('current_stage')->default(1)->after('referral_count');
            $table->integer('cycle_number')->default(1)->after('current_stage');
            $table->integer('total_referrals_all_time')->default(0)->after('cycle_number');
        });

        // 3. Extend referrals table
        Schema::table('referrals', function (Blueprint $table) {
            $table->string('transaction_type', 20)->default('referral')->after('status');
            $table->decimal('eligible_product_value', 10, 2)->default(0.00)->after('transaction_type');
            $table->integer('cycle_number')->default(1)->after('eligible_product_value');
            $table->tinyInteger('referral_stage')->nullable()->after('cycle_number');
            $table->string('rule_version', 20)->nullable()->after('referral_stage');
            $table->text('notes')->nullable()->after('rule_version');
            $table->timestamp('approved_at')->nullable()->after('notes');
            $table->timestamp('reversed_at')->nullable()->after('approved_at');
        });

        // 4. Extend wallet_transactions
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->string('transaction_type', 30)->default('referral_incentive')->after('description');
            $table->enum('status', ['pending', 'available', 'redeemed', 'reversed'])->default('pending')->after('transaction_type');
            $table->foreignId('referral_id')->nullable()->constrained('referrals')->onDelete('set null')->after('status');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null')->after('referral_id');
            $table->decimal('incentive_percentage', 5, 2)->nullable()->after('category_id');
            $table->tinyInteger('referral_stage')->nullable()->after('incentive_percentage');
            $table->integer('cycle_number')->nullable()->after('referral_stage');
            $table->string('rule_version', 20)->nullable()->after('cycle_number');
            $table->timestamp('available_at')->nullable()->after('rule_version');
            $table->timestamp('reversed_at')->nullable()->after('available_at');
        });

        // 5. Extend products table
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('eligible_referral_value', 10, 2)->nullable()->after('balance_amount');
            $table->boolean('referral_eligible')->default(true)->after('eligible_referral_value');
            $table->boolean('self_dealer_eligible')->default(true)->after('referral_eligible');
        });

        // 6. Extend categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->string('referral_category_code', 10)->nullable()->after('type');
            $table->boolean('referral_eligible')->default(true)->after('referral_category_code');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['referral_category_code', 'referral_eligible']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['eligible_referral_value', 'referral_eligible', 'self_dealer_eligible']);
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropForeign(['referral_id']);
            $table->dropForeign(['category_id']);
            $table->dropColumn(['transaction_type', 'status', 'referral_id', 'category_id',
                'incentive_percentage', 'referral_stage', 'cycle_number', 'rule_version',
                'available_at', 'reversed_at']);
        });

        Schema::table('referrals', function (Blueprint $table) {
            $table->dropColumn(['transaction_type', 'eligible_product_value', 'cycle_number',
                'referral_stage', 'rule_version', 'notes', 'approved_at', 'reversed_at']);
        });

        Schema::table('customer_category_progress', function (Blueprint $table) {
            $table->dropColumn(['current_stage', 'cycle_number', 'total_referrals_all_time']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['activation_booking_id']);
            $table->dropColumn(['self_dealer_status', 'self_dealer_activated_at',
                'activation_booking_id', 'terms_version', 'terms_accepted_at']);
        });
    }
};
