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
            $table->boolean('is_offline')->default(false)->after('booking_number');
            $table->string('purchase_channel', 50)->default('online')->after('is_offline'); // online, offline_store, offline_direct, telephonic, showroom
            $table->string('offline_payment_method', 50)->nullable()->after('purchase_channel'); // cash, bank_transfer, cheque, upi, pos_card, other
            $table->string('offline_payment_ref', 100)->nullable()->after('offline_payment_method');
            $table->text('offline_notes')->nullable()->after('offline_payment_ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'is_offline',
                'purchase_channel',
                'offline_payment_method',
                'offline_payment_ref',
                'offline_notes',
            ]);
        });
    }
};
