<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'source',
        'booking_id',
        'description',
        'transaction_type',
        'status',
        'referral_id',
        'category_id',
        'incentive_percentage',
        'referral_stage',
        'cycle_number',
        'rule_version',
        'available_at',
        'reversed_at',
    ];

    protected $casts = [
        'amount'               => 'decimal:2',
        'incentive_percentage' => 'decimal:2',
        'available_at'         => 'datetime',
        'reversed_at'          => 'datetime',
    ];

    // --- Relationships ---

    public function user()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function referral()
    {
        return $this->belongsTo(Referral::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // --- Scopes ---

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeRedeemed($query)
    {
        return $query->where('status', 'redeemed');
    }

    public function scopeReversed($query)
    {
        return $query->where('status', 'reversed');
    }

    public function scopeActivationPoints($query)
    {
        return $query->where('transaction_type', 'activation_points');
    }

    public function scopeReferralIncentive($query)
    {
        return $query->where('transaction_type', 'referral_incentive');
    }
}
