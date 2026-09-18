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
        Schema::create('dsp_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dsp_id');
            $table->decimal('amount', 12, 2);
            $table->enum('type', ['credit', 'debit']);
            $table->string('source')->default('delivery_commission'); // delivery_commission, cash_redemption, admin_adjustment
            $table->unsignedBigInteger('delivery_id')->nullable();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->decimal('balance_after', 12, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('dsp_id')->references('id')->on('dsp_applications')->cascadeOnDelete();
            $table->foreign('delivery_id')->references('id')->on('deliveries')->nullOnDelete();
            $table->foreign('booking_id')->references('id')->on('bookings')->nullOnDelete();
            $table->index(['dsp_id', 'created_at']);
        });

        Schema::create('dsp_payout_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dsp_id');
            $table->string('request_number')->unique();
            $table->decimal('amount', 12, 2);
            $table->string('payout_mode')->default('bank'); // bank, upi
            $table->string('bank_account_number')->nullable();
            $table->string('bank_ifsc')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_holder_name')->nullable();
            $table->string('upi_id')->nullable();
            $table->enum('status', ['pending', 'approved', 'paid', 'rejected'])->default('pending');
            $table->string('transaction_reference')->nullable();
            $table->text('admin_notes')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('dsp_id')->references('id')->on('dsp_applications')->cascadeOnDelete();
            $table->index(['dsp_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dsp_payout_requests');
        Schema::dropIfExists('dsp_wallet_transactions');
    }
};
