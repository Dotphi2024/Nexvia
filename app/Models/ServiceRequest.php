<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'booking_id',
        'subject',
        'service_type',
        'status',
        'details',
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
