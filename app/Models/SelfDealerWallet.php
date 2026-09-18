<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SelfDealerWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_earned',
        'available_points',
        'pending_points',
        'redeemed_points',
        'reversed_points',
    ];

    protected $casts = [
        'total_earned'      => 'decimal:2',
        'available_points'  => 'decimal:2',
        'pending_points'    => 'decimal:2',
        'redeemed_points'   => 'decimal:2',
        'reversed_points'   => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    /**
     * Credit points directly to available wallet (instant qualification)
     */
    public function creditAvailable(float $points): void
    {
        $this->available_points += $points;
        $this->total_earned     += $points;
        $this->save();
    }

    /**
     * Credit points to wallet (increases pending or available based on status)
     */
    public function creditPending(float $points): void
    {
        $this->pending_points += $points;
        $this->total_earned   += $points;
        $this->save();
    }

    /**
     * Move points from pending → available after qualification
     */
    public function qualifyPoints(float $points): void
    {
        $this->pending_points   = max(0, $this->pending_points - $points);
        $this->available_points += $points;
        $this->save();
    }

    /**
     * Deduct available points on redemption
     */
    public function redeemPoints(float $points): bool
    {
        if ($this->available_points < $points) {
            return false;
        }
        $this->available_points -= $points;
        $this->redeemed_points  += $points;
        $this->save();
        return true;
    }

    /**
     * Reverse points (cancellation/return)
     */
    public function reversePoints(float $points, string $status = 'pending'): void
    {
        if ($status === 'pending') {
            $this->pending_points = max(0, $this->pending_points - $points);
        } else {
            $this->available_points = max(0, $this->available_points - $points);
        }
        $this->total_earned     = max(0, $this->total_earned - $points);
        $this->reversed_points += $points;
        $this->save();
    }
}
