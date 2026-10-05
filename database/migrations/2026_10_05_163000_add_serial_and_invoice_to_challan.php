<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            if (!Schema::hasColumn('deliveries', 'serial_number')) {
                $table->string('serial_number')->nullable()->after('challan_number');
            }
            if (!Schema::hasColumn('deliveries', 'invoice_number')) {
                $table->string('invoice_number')->nullable()->after('serial_number');
            }
            if (!Schema::hasColumn('deliveries', 'pdi_status')) {
                $table->string('pdi_status')->default('pending')->after('stage');
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'serial_number')) {
                $table->string('serial_number')->nullable()->after('delivery_challan_number');
            }
            if (!Schema::hasColumn('bookings', 'invoice_number')) {
                $table->string('invoice_number')->nullable()->after('serial_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            if (Schema::hasColumn('deliveries', 'serial_number')) {
                $table->dropColumn(['serial_number', 'invoice_number', 'pdi_status']);
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'serial_number')) {
                $table->dropColumn(['serial_number', 'invoice_number']);
            }
        });
    }
};
