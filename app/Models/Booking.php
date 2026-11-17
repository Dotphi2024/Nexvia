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
        'is_offline',
        'purchase_channel',
        'offline_payment_method',
        'offline_payment_ref',
        'offline_notes',
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
        'delivery_challan_number',
        'serial_number',
        'invoice_number',
        'challan_generated_at',
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
        'is_offline' => 'boolean',
        'booking_date' => 'date',
        'balance_due_date' => 'date',
        'challan_generated_at' => 'datetime',
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

    public function getIsExpired60DaysAttribute()
    {
        if ($this->payment_status === 'fully_paid') {
            return false;
        }
        if (!$this->balance_due_date) {
            return false;
        }
        return ($this->days_remaining <= 0) || Carbon::parse($this->balance_due_date)->isPast();
    }

    public function getCanReallocatePaidAmountAttribute()
    {
        return $this->booking_status !== 'reallocated' 
            && $this->booking_status !== 'cancelled' 
            && (float) $this->filled_amount > 0;
    }

    public function getFilledAmountAttribute()
    {
        if ($this->payment_status === 'fully_paid') {
            return (float) $this->mrp;
        }

        $paid = (float) $this->mrp - (float) $this->balance_amount;
        if ($paid <= 0) {
            $paid = (float) $this->booking_amount;
        }

        return max(0, round($paid, 2));
    }

    public function getCanDownloadChallanAttribute(): bool
    {
        // Challan is ONLY downloadable once 100% full payment is completed (balance cleared)
        $hasNoBalance = (float)$this->balance_amount <= 0.01;
        $isFullyPaid = in_array(strtolower((string)$this->payment_status), ['fully_paid', 'completed']);
        return ($hasNoBalance || $isFullyPaid) && ($this->booking_status !== 'cancelled');
    }

    public function getChallanStatusAttribute(): string
    {
        if ($this->can_download_challan) {
            return 'generated';
        }
        return 'pending_full_payment';
    }

    public function getTaxBreakdownAttribute(): array
    {
        $totalWithTax = (float) $this->mrp;
        $qty = max(1, (int) ($this->quantity ?: 1));

        $product  = $this->product ?: Product::find($this->product_id);
        $category = $product?->category;
        $catType  = strtolower($category?->type ?? '');
        $catName  = strtolower($category?->name ?? '');
        $catSlug  = strtolower($category?->slug ?? '');
        $prodName = strtolower($this->product_name ?? '');

        $isDlsAgro = ($catType === 'dls_farm_equipment' || $catType === 'dls_agro' || $catType === 'agro' || $catType === 'farm')
            || str_contains($catName, 'farm')
            || str_contains($catName, 'agro')
            || str_contains($catSlug, 'farm')
            || str_contains($catSlug, 'agro')
            || str_contains($prodName, 'cultivator')
            || str_contains($prodName, 'tiller')
            || str_contains($prodName, 'dls agro');

        $gstRatePercent = $isDlsAgro ? 5.0 : 18.0;
        $cgstRate = $isDlsAgro ? '2.5%' : '9%';
        $sgstRate = $isDlsAgro ? '2.5%' : '9%';

        if ($product && (float)$product->mrp > 0) {
            $taxableValue = round((float)$product->mrp * $qty, 2);
            $totalTax = round($totalWithTax - $taxableValue, 2);
            if ($totalTax <= 0 || abs($totalTax - round($taxableValue * ($gstRatePercent / 100), 2)) > 10.0) {
                $totalTax = round($taxableValue * ($gstRatePercent / 100), 2);
                $grandInvoiceTotal = round($taxableValue + $totalTax, 2);
            } else {
                $grandInvoiceTotal = $totalWithTax;
            }
        } else {
            $taxMultiplier = 1.0 + ($gstRatePercent / 100.0);
            $taxableValue = round($totalWithTax / $taxMultiplier, 2);
            $totalTax = round($totalWithTax - $taxableValue, 2);
            $grandInvoiceTotal = $totalWithTax;
        }

        $cgstAmount = round($totalTax / 2, 2);
        $sgstAmount = round($totalTax - $cgstAmount, 2);

        return [
            'is_dls_agro'      => $isDlsAgro,
            'gst_rate_percent' => $gstRatePercent,
            'cgst_rate'        => $cgstRate,
            'cgst_amount'      => $cgstAmount,
            'sgst_rate'        => $sgstRate,
            'sgst_amount'      => $sgstAmount,
            'total_tax'        => $totalTax,
            'taxable_amount'   => $taxableValue,
            'base_mrp'         => $taxableValue,
            'grand_total'      => $grandInvoiceTotal,
        ];
    }

    public function getBaseMrpAttribute(): float
    {
        return (float) ($this->tax_breakdown['base_mrp'] ?? $this->mrp);
    }
}

