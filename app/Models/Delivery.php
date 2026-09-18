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
        'dsp_id',
        'tracking_number',
        'stage',
        'dsp_commission_amount',
        'dsp_commission_status',
        'delivery_otp',
        'delivery_notes',
        'dispatched_at',
        'delivered_at',
        'installation_completed_at',
    ];

    protected $casts = [
        'dsp_commission_amount'     => 'decimal:2',
        'dispatched_at'             => 'datetime',
        'delivered_at'              => 'datetime',
        'installation_completed_at' => 'datetime',
    ];

    public function dsp()
    {
        return $this->belongsTo(DspApplication::class, 'dsp_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
