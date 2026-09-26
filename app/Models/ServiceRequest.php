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
        'customer_name',
        'customer_phone',
        'address',
        'pincode',
        'city',
        'state',
        'booking_id',
        'dsp_id',
        'subject',
        'service_type',
        'priority',
        'status',
        'is_attended',
        'attended_at',
        'attended_by_name',
        'attended_by_phone',
        'dsp_notes',
        'resolved_at',
        'resolution_notes',
        'resolution_proof',
        'details',
        'attachments',
    ];

    protected $casts = [
        'is_attended' => 'boolean',
        'attended_at' => 'datetime',
        'resolved_at' => 'datetime',
        'attachments' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function dsp()
    {
        return $this->belongsTo(DspApplication::class, 'dsp_id');
    }

    /**
     * Human readable attended badge
     */
    public function getAttendedBadgeAttribute(): array
    {
        if ($this->is_attended) {
            return [
                'class' => 'bg-success text-white',
                'label' => 'Attended',
                'time'  => $this->attended_at ? $this->attended_at->format('d M, Y H:i') : null,
            ];
        }

        return [
            'class' => 'bg-warning text-dark',
            'label' => 'Not Attended',
            'time'  => null,
        ];
    }
}
