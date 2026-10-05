<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'delivery_challan_number')) {
                $table->string('delivery_challan_number')->nullable()->after('qr_code_hash');
            }
            if (!Schema::hasColumn('bookings', 'challan_generated_at')) {
                $table->timestamp('challan_generated_at')->nullable()->after('delivery_challan_number');
            }
        });

        Schema::table('deliveries', function (Blueprint $table) {
            if (!Schema::hasColumn('deliveries', 'challan_number')) {
                $table->string('challan_number')->nullable()->after('tracking_number');
            }
            if (!Schema::hasColumn('deliveries', 'challan_generated_at')) {
                $table->timestamp('challan_generated_at')->nullable()->after('challan_number');
            }
            if (!Schema::hasColumn('deliveries', 'challan_status')) {
                $table->string('challan_status')->default('pending_payment')->after('challan_generated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['delivery_challan_number', 'challan_generated_at']);
        });

        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn(['challan_number', 'challan_generated_at', 'challan_status']);
        });
    }
};
