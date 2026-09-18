<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DspWalletTransaction extends Model
{
    use HasFactory;

    protected $table = 'dsp_wallet_transactions';

    protected $fillable = [
        'dsp_id',
        'amount',
        'type', // 'credit', 'debit'
        'source', // 'delivery_commission', 'cash_redemption', 'admin_adjustment'
        'delivery_id',
        'booking_id',
        'reference_number',
        'description',
        'balance_after',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function dsp()
    {
        return $this->belongsTo(DspApplication::class, 'dsp_id');
    }

    public function delivery()
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
