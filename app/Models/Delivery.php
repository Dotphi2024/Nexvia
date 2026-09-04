<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'booking_id',
        'tracking_number',
        'stage',
        'dispatched_at',
        'delivered_at',
        'installation_completed_at',
    ];

    protected $casts = [
        'dispatched_at'             => 'datetime',
        'delivered_at'              => 'datetime',
        'installation_completed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
