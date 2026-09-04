<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Installation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'booking_id',
        'technician_name',
        'technician_phone',
        'scheduled_at',
        'status',
        'rating',
        'feedback',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
