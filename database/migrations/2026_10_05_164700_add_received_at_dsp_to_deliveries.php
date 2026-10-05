<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            if (!Schema::hasColumn('deliveries', 'received_at_dsp_at')) {
                $table->timestamp('received_at_dsp_at')->nullable()->after('dispatched_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            if (Schema::hasColumn('deliveries', 'received_at_dsp_at')) {
                $table->dropColumn('received_at_dsp_at');
            }
        });
    }
};
