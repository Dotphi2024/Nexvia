<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DspPayoutRequest extends Model
{
    use HasFactory;

    protected $table = 'dsp_payout_requests';

    protected $fillable = [
        'dsp_id',
        'request_number',
        'amount',
        'payout_mode', // 'bank', 'upi'
        'bank_account_number',
        'bank_ifsc',
        'bank_name',
        'bank_holder_name',
        'upi_id',
        'status', // 'pending', 'approved', 'paid', 'rejected'
        'transaction_reference',
        'admin_notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function dsp()
    {
        return $this->belongsTo(DspApplication::class, 'dsp_id');
    }

    public function processor()
    {
        return $this->belongsTo(Admin::class, 'processed_by');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'paid'     => ['class' => 'bg-success text-white', 'text' => 'Paid'],
            'approved' => ['class' => 'bg-info text-white', 'text' => 'Approved'],
            'rejected' => ['class' => 'bg-danger text-white', 'text' => 'Rejected'],
            default    => ['class' => 'bg-warning text-dark', 'text' => 'Pending'],
        };
    }
}
