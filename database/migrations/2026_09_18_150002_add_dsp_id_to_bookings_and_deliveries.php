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
            $table->unsignedBigInteger('dsp_id')->nullable()->after('user_id');
            $table->foreign('dsp_id')->references('id')->on('dsp_applications')->nullOnDelete();
        });

        Schema::table('deliveries', function (Blueprint $table) {
            $table->unsignedBigInteger('dsp_id')->nullable()->after('booking_id');
            $table->decimal('dsp_commission_amount', 10, 2)->default(0.00)->after('stage');
            $table->enum('dsp_commission_status', ['pending', 'credited', 'cancelled'])->default('pending')->after('dsp_commission_amount');
            $table->string('delivery_otp', 10)->nullable()->after('dsp_commission_status');
            $table->text('delivery_notes')->nullable()->after('delivery_otp');

            $table->foreign('dsp_id')->references('id')->on('dsp_applications')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropForeign(['dsp_id']);
            $table->dropColumn([
                'dsp_id',
                'dsp_commission_amount',
                'dsp_commission_status',
                'delivery_otp',
                'delivery_notes',
            ]);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['dsp_id']);
            $table->dropColumn('dsp_id');
        });
    }
};
