<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_number',
        'user_id',
        'dsp_id',
        'product_id',
        'product_name',
        'model_code',
        'selected_color',
        'quantity',
        'mrp',
        'booking_amount',
        'balance_amount',
        'booking_date',
        'balance_due_date',
        'payment_type',
        'payment_status',
        'booking_status',
        'transfer_status',
        'non_refundable_accepted',
        'customer_name',
        'customer_phone',
        'customer_email',
        'shipping_address',
        'pincode',
        'city',
        'state',
        'qr_code_hash',
        'cancellation_reason',
        'cancelled_at',
        'payment_receipt',
        'balance_payment_mode',
        'emi_tenure_months',
        'emi_monthly_amount',
        'emi_installments_paid',
        'balance_payments_history',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'balance_due_date' => 'date',
        'cancelled_at' => 'datetime',
        'mrp' => 'decimal:2',
        'booking_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'emi_monthly_amount' => 'decimal:2',
        'quantity' => 'integer',
        'emi_tenure_months' => 'integer',
        'emi_installments_paid' => 'integer',
        'balance_payments_history' => 'array',
        'non_refundable_accepted' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    public function dsp()
    {
        return $this->belongsTo(DspApplication::class, 'dsp_id');
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class, 'booking_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function transfers()
    {
        return $this->hasMany(BookingTransfer::class);
    }

    public function getDaysRemainingAttribute()
    {
        if ($this->payment_status === 'fully_paid') {
            return 0;
        }
        $dueDate = Carbon::parse($this->balance_due_date);
        $diff = now()->startOfDay()->diffInDays($dueDate, false);
        return max(0, (int)$diff);
    }

    public function getIsOverdueAttribute()
    {
        if ($this->payment_status === 'fully_paid') {
            return false;
        }
        return Carbon::parse($this->balance_due_date)->isPast();
    }
}
