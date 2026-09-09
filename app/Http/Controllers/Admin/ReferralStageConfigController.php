<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralStageConfig;
use Illuminate\Http\Request;

class ReferralStageConfigController extends Controller
{
    /**
     * Show the current admin-configurable stage settings.
     */
    public function settings()
    {
        $stages = ReferralStageConfig::orderBy('stage_number')->get();

        // If not seeded yet, show defaults
        if ($stages->isEmpty()) {
            $stages = collect([
                (object)['stage_number' => 1, 'incentive_percentage' => 10.00, 'activation_credit_percentage' => 20.00, 'is_active' => true],
                (object)['stage_number' => 2, 'incentive_percentage' => 12.00, 'activation_credit_percentage' => 20.00, 'is_active' => true],
                (object)['stage_number' => 3, 'incentive_percentage' => 15.00, 'activation_credit_percentage' => 20.00, 'is_active' => true],
                (object)['stage_number' => 4, 'incentive_percentage' => 18.00, 'activation_credit_percentage' => 20.00, 'is_active' => true],
                (object)['stage_number' => 5, 'incentive_percentage' => 20.00, 'activation_credit_percentage' => 20.00, 'is_active' => true],
            ]);
        }

        return view('admin.referral_config.settings', compact('stages'));
    }

    /**
     * Update stage percentages and activation credit %.
     */
    public function update(Request $request)
    {
        $request->validate([
            'activation_credit_percentage' => 'required|numeric|min:1|max:100',
            'stages'                       => 'required|array',
            'stages.*.stage_number'        => 'required|integer|between:1,5',
            'stages.*.incentive_percentage'=> 'required|numeric|min:0|max:100',
        ]);

        $activationPct = $request->activation_credit_percentage;
        $adminId       = auth()->id();

        foreach ($request->stages as $stageData) {
            ReferralStageConfig::updateOrCreate(
                ['stage_number' => $stageData['stage_number']],
                [
                    'incentive_percentage'         => $stageData['incentive_percentage'],
                    'activation_credit_percentage' => $activationPct,
                    'is_active'                    => true,
                    'updated_by'                   => $adminId,
                    'version'                      => 'v' . now()->format('Ymd.Hi'),
                ]
            );
        }

        return redirect()
            ->route('admin.referral.config.settings')
            ->with('success', '✅ Referral Incentive configuration updated successfully.');
    }
}
