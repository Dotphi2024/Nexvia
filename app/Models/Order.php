<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'total_amount',
        'booking_amount',
        'balance_amount',
        'product_credit_applied',
        'payment_type',
        'payment_method',
        'payment_status',
        'order_status',
        'customer_name',
        'customer_phone',
        'shipping_address',
        'city',
        'state',
        'pincode',
    ];

    protected $casts = [
        'total_amount'           => 'decimal:2',
        'booking_amount'         => 'decimal:2',
        'balance_amount'         => 'decimal:2',
        'product_credit_applied' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }
}
