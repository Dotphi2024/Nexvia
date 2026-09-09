<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralStageConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'stage_number',
        'incentive_percentage',
        'activation_credit_percentage',
        'is_active',
        'version',
        'notes',
        'updated_by',
    ];

    protected $casts = [
        'incentive_percentage'        => 'decimal:2',
        'activation_credit_percentage' => 'decimal:2',
        'is_active'                   => 'boolean',
    ];

    /**
     * Get the rate for a specific stage from DB (Variant A: 10/12/15/18/20)
     */
    public static function getRateForStage(int $stage): float
    {
        $config = static::where('stage_number', $stage)->where('is_active', true)->first();
        if ($config) {
            return (float) $config->incentive_percentage;
        }
        // Fallback hardcoded Variant A rates (safety net only)
        $defaults = [1 => 10.00, 2 => 12.00, 3 => 15.00, 4 => 18.00, 5 => 20.00];
        return $defaults[$stage] ?? 10.00;
    }

    /**
     * Get activation credit percentage from DB
     */
    public static function getActivationRate(): float
    {
        $config = static::where('stage_number', 1)->where('is_active', true)->first();
        return $config ? (float) $config->activation_credit_percentage : 20.00;
    }

    public function updatedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }
}
