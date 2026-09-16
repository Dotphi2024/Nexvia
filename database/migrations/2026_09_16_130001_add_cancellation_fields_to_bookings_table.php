<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('qr_code_hash');
            }
            if (!Schema::hasColumn('bookings', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
            }
        });

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'self_dealer_status')) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN self_dealer_status ENUM('inactive', 'pending', 'active', 'suspended', 'cancelled') DEFAULT 'inactive'");
        }
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['cancellation_reason', 'cancelled_at']);
        });
    }
};
