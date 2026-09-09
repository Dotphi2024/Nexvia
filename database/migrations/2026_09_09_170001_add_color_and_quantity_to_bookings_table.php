<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('selected_color', 100)->nullable()->after('model_code');
            $table->unsignedInteger('quantity')->default(1)->after('selected_color');
            $table->string('customer_email')->nullable()->after('customer_phone');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['selected_color', 'quantity', 'customer_email']);
        });
    }
};
