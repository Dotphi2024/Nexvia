<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'overview',
        'model_code',
        'sku',
        'slug',
        'mrp',
        'booking_percentage',
        'booking_amount',
        'balance_amount',
        'eligible_referral_value',
        'referral_eligible',
        'self_dealer_eligible',
        'stock',
        'main_image',
        'video_url',
        'offer_text',
        'gallery',
        'key_features',
        'specs',
        'warranty_info',
        'installation_info',
        'delivery_info',
        'is_featured',
        'status',
    ];

    protected $casts = [
        'mrp'                    => 'decimal:2',
        'booking_amount'         => 'decimal:2',
        'balance_amount'         => 'decimal:2',
        'eligible_referral_value'=> 'decimal:2',
        'referral_eligible'      => 'boolean',
        'self_dealer_eligible'   => 'boolean',
        'gallery'                => 'array',
        'key_features'           => 'array',
        'specs'                  => 'array',
        'is_featured'            => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class, 'product_id');
    }

    /**
     * Ensure booking_amount is always calculated if not explicitly set (default 20% of MRP)
     */
    public function getBookingAmountAttribute($value)
    {
        if ($value !== null && (float) $value > 0) {
            return (float) $value;
        }
        $pct = (float) ($this->booking_percentage ?: 20.00);
        return round(((float) $this->mrp) * ($pct / 100), 2);
    }

    /**
     * Ensure balance_amount is always calculated if not explicitly set (MRP minus booking amount)
     */
    public function getBalanceAmountAttribute($value)
    {
        if ($value !== null && (float) $value > 0) {
            return (float) $value;
        }
        return max(0.00, round(((float) $this->mrp) - (float) $this->booking_amount, 2));
    }

    /**
     * Alias for key_features as features
     */
    public function getFeaturesAttribute()
    {
        return $this->key_features ?: [];
    }

    /**
     * Formatted technical specifications as list of { name, value }
     */
    public function getTechnicalSpecificationsAttribute(): array
    {
        $specs = $this->specs ?: [];
        if (is_string($specs)) {
            $specs = json_decode($specs, true) ?? [];
        }

        $list = [];
        if (is_array($specs)) {
            foreach ($specs as $name => $value) {
                $list[] = [
                    'name'  => (string) $name,
                    'value' => is_array($value) ? implode(', ', $value) : (string) $value,
                ];
            }
        }
        return $list;
    }
}
