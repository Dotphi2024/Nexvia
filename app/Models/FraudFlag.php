<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FraudFlag extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'referrer_id',
        'booking_id',
        'flag_type',
        'flag_reason',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    public function referrer()
    {
        return $this->belongsTo(Customer::class, 'referrer_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function reviewedBy()
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    // Scopes
    public function scopePendingReview($query)
    {
        return $query->where('status', 'pending_review');
    }

    public function scopeConfirmedFraud($query)
    {
        return $query->where('status', 'confirmed_fraud');
    }
}
