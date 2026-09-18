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
        Schema::table('dsp_applications', function (Blueprint $table) {
            $table->string('password')->nullable()->after('mobile');
            $table->rememberToken()->after('password');
            $table->decimal('wallet_balance', 12, 2)->default(0.00)->after('status');
            $table->decimal('total_earned', 12, 2)->default(0.00)->after('wallet_balance');
            $table->decimal('total_redeemed', 12, 2)->default(0.00)->after('total_earned');
            $table->string('payout_account_number')->nullable()->after('total_redeemed');
            $table->string('payout_ifsc')->nullable()->after('payout_account_number');
            $table->string('payout_bank_name')->nullable()->after('payout_ifsc');
            $table->string('payout_holder_name')->nullable()->after('payout_bank_name');
            $table->string('payout_upi_id')->nullable()->after('payout_holder_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dsp_applications', function (Blueprint $table) {
            $table->dropColumn([
                'password',
                'remember_token',
                'wallet_balance',
                'total_earned',
                'total_redeemed',
                'payout_account_number',
                'payout_ifsc',
                'payout_bank_name',
                'payout_holder_name',
                'payout_upi_id',
            ]);
        });
    }
};
