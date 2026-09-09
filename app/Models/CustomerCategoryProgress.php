<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerCategoryProgress extends Model
{
    use HasFactory;

    protected $table = 'customer_category_progress';

    protected $fillable = [
        'user_id',
        'category_id',
        'referral_count',
        'current_tier_percentage',
        'current_stage',
        'cycle_number',
        'total_referrals_all_time',
    ];

    protected $casts = [
        'current_tier_percentage' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the current stage rate from DB config (Variant A: 10/12/15/18/20)
     */
    public function getCurrentRate(): float
    {
        return ReferralStageConfig::getRateForStage($this->current_stage);
    }

    /**
     * Advance stage after a successful referral.
     * After Stage 5 → Reset to Stage 1, increment cycle number.
     */
    public function advanceStage(): void
    {
        $this->referral_count++;
        $this->total_referrals_all_time++;

        if ($this->current_stage >= 5) {
            // RESET — cycle complete
            $this->current_stage  = 1;
            $this->cycle_number  += 1;
        } else {
            $this->current_stage++;
        }

        // Keep current_tier_percentage in sync for legacy compatibility
        $this->current_tier_percentage = ReferralStageConfig::getRateForStage($this->current_stage);
        $this->save();
    }

    /**
     * Check if stage just completed a full cycle (stage was 5 before advance).
     */
    public function justCompletedCycle(): bool
    {
        return $this->current_stage === 1 && $this->cycle_number > 1;
    }
}
