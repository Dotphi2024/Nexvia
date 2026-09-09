<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_id',
        'referee_id',
        'booking_id',
        'category_id',
        'sequence_in_category',
        'benefit_percentage',
        'product_value',
        'credit_earned',
        'status',
        'transaction_type',
        'eligible_product_value',
        'cycle_number',
        'referral_stage',
        'rule_version',
        'notes',
        'approved_at',
        'reversed_at',
    ];

    protected $casts = [
        'benefit_percentage'    => 'decimal:2',
        'product_value'         => 'decimal:2',
        'credit_earned'         => 'decimal:2',
        'eligible_product_value'=> 'decimal:2',
        'approved_at'           => 'datetime',
        'reversed_at'           => 'datetime',
    ];

    // --- Relationships ---

    public function referrer()
    {
        return $this->belongsTo(Customer::class, 'referrer_id');
    }

    public function referee()
    {
        return $this->belongsTo(Customer::class, 'referee_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function walletTransaction()
    {
        return $this->hasOne(WalletTransaction::class);
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

    public function scopeReversed($query)
    {
        return $query->where('status', 'reversed');
    }

    public function scopeReferralType($query)
    {
        return $query->where('transaction_type', 'referral');
    }

    public function scopeActivationType($query)
    {
        return $query->where('transaction_type', 'activation');
    }

    // --- Helpers ---

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isReversed(): bool
    {
        return $this->status === 'reversed';
    }
}
