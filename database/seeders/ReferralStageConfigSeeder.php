<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReferralStageConfig;

class ReferralStageConfigSeeder extends Seeder
{
    /**
     * Seed the Variant A referral stage configuration:
     * Stage 1 = 10%, Stage 2 = 12%, Stage 3 = 15%, Stage 4 = 18%, Stage 5 = 20%
     * Activation Credit = 20%
     * After Stage 5 → RESET to Stage 1
     */
    public function run(): void
    {
        $stages = [
            ['stage_number' => 1, 'incentive_percentage' => 10.00],
            ['stage_number' => 2, 'incentive_percentage' => 12.00],
            ['stage_number' => 3, 'incentive_percentage' => 15.00],
            ['stage_number' => 4, 'incentive_percentage' => 18.00],
            ['stage_number' => 5, 'incentive_percentage' => 20.00],
        ];

        foreach ($stages as $stage) {
            ReferralStageConfig::updateOrCreate(
                ['stage_number' => $stage['stage_number']],
                [
                    'incentive_percentage'         => $stage['incentive_percentage'],
                    'activation_credit_percentage' => 20.00,
                    'is_active'                    => true,
                    'version'                      => 'v1.0',
                    'notes'                        => 'Variant A — 10% → 12% → 15% → 18% → 20% → RESET',
                ]
            );
        }

        $this->command->info('✅ Referral Stage Config seeded — Variant A (10/12/15/18/20%)');
    }
}
