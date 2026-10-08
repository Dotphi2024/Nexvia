<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Automatically ensure all products have eligible_referral_value at least equal to their full MRP
        DB::table('products')
            ->where(function ($q) {
                $q->whereNull('eligible_referral_value')
                  ->orWhere('eligible_referral_value', '<=', 0)
                  ->orWhereRaw('eligible_referral_value < mrp');
            })
            ->update([
                'eligible_referral_value' => DB::raw('mrp'),
            ]);
    }

    public function down(): void
    {
        // No-op
    }
};
