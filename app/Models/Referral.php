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
    ];

    protected $casts = [
        'benefit_percentage' => 'decimal:2',
        'product_value' => 'decimal:2',
        'credit_earned' => 'decimal:2',
    ];

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
}
