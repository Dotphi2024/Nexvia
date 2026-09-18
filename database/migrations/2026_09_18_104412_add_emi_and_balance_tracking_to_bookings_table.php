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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('balance_payment_mode')->nullable()->after('payment_type'); // 'full', 'emi', 'flexible'
            $table->unsignedTinyInteger('emi_tenure_months')->nullable()->after('balance_payment_mode'); // 3, 6, 9, 12
            $table->decimal('emi_monthly_amount', 10, 2)->nullable()->after('emi_tenure_months');
            $table->unsignedTinyInteger('emi_installments_paid')->default(0)->after('emi_monthly_amount');
            $table->json('balance_payments_history')->nullable()->after('emi_installments_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'balance_payment_mode',
                'emi_tenure_months',
                'emi_monthly_amount',
                'emi_installments_paid',
                'balance_payments_history',
            ]);
        });
    }
};
