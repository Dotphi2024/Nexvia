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
        Schema::table('service_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('dsp_id')->nullable()->after('booking_id');
            $table->string('customer_name')->nullable()->after('user_id');
            $table->string('customer_phone', 20)->nullable()->after('customer_name');
            $table->text('address')->nullable()->after('customer_phone');
            $table->string('pincode', 10)->nullable()->after('address');
            $table->string('city', 100)->nullable()->after('pincode');
            $table->string('state', 100)->nullable()->after('city');
            $table->string('priority', 20)->default('medium')->after('service_type'); // low, medium, high, urgent
            $table->boolean('is_attended')->default(false)->after('status');
            $table->dateTime('attended_at')->nullable()->after('is_attended');
            $table->string('attended_by_name')->nullable()->after('attended_at');
            $table->string('attended_by_phone', 20)->nullable()->after('attended_by_name');
            $table->text('dsp_notes')->nullable()->after('attended_by_phone');
            $table->dateTime('resolved_at')->nullable()->after('dsp_notes');
            $table->text('resolution_notes')->nullable()->after('resolved_at');
            $table->string('resolution_proof')->nullable()->after('resolution_notes');
            $table->json('attachments')->nullable()->after('details');

            $table->foreign('dsp_id')->references('id')->on('dsp_applications')->onDelete('set null');
            $table->index('pincode');
            $table->index('is_attended');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign(['dsp_id']);
            $table->dropIndex(['pincode']);
            $table->dropIndex(['is_attended']);
            $table->dropIndex(['status']);
            $table->dropColumn([
                'dsp_id',
                'customer_name',
                'customer_phone',
                'address',
                'pincode',
                'city',
                'state',
                'priority',
                'is_attended',
                'attended_at',
                'attended_by_name',
                'attended_by_phone',
                'dsp_notes',
                'resolved_at',
                'resolution_notes',
                'resolution_proof',
                'attachments',
            ]);
        });
    }
};
