<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'from_user_id',
        'to_name',
        'to_phone',
        'to_user_id',
        'transfer_otp',
        'transfer_otp_expires_at',
        'status',
        'transferred_at',
    ];

    protected $casts = [
        'transfer_otp_expires_at' => 'datetime',
        'transferred_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(Customer::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(Customer::class, 'to_user_id');
    }
}
