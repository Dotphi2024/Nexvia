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
}
