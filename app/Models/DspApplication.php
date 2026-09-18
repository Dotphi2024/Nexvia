<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;

class DspApplication extends Model implements AuthenticatableContract
{
    use HasFactory, Authenticatable;

    protected $table = 'dsp_applications';

    protected $fillable = [
        // Meta
        'application_number',
        'application_date',
        'preferred_territory_area',
        'pincodes',
        'district',
        'state',

        // 1. Applicant Details
        'applicant_name',
        'father_or_spouse_name',
        'date_of_birth',
        'mobile',
        'password',
        'whatsapp',
        'email',
        'residential_address',
        'residential_pincode',

        // 2. Business Details
        'business_name',
        'business_constitution',
        'business_constitution_other',
        'year_established',
        'pan',
        'gstin',
        'existing_business_activity',
        'years_of_experience',

        // 3. Proposed DSP Location
        'premises_type',
        'complete_address',
        'premises_pincode',
        'total_area_sqft',
        'frontage_feet',
        'available_facilities',

        // 4. Service Capability
        'presently_operate_service_centre',
        'technicians_count',
        'ev_technician_status',
        'electrical_technician_status',
        'home_appliance_technician_status',
        'agree_to_nexvia_training',

        // 5. Products & Delivery Capability
        'products_handled',
        'vehicles_two_wheeler',
        'vehicles_three_wheeler',
        'vehicles_pickup_lcv',
        'vehicles_other',
        'max_delivery_radius_km',
        'pdi_handover_sop',
        'otp_delivery_confirmation',

        // 6. Security Stock Deposit
        'security_deposit_amount',
        'deposit_terms_agreed',
        'deposit_payment_status',
        'deposit_transaction_reference',
        'deposit_payment_proof',

        // 8. Declaration & Review
        'declaration_agreed',
        'declaration_date',
        'declaration_signature_name',
        'signature_file',
        'status',
        'wallet_balance',
        'total_earned',
        'total_redeemed',
        'payout_account_number',
        'payout_ifsc',
        'payout_bank_name',
        'payout_holder_name',
        'payout_upi_id',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'application_date'                 => 'date',
        'date_of_birth'                    => 'date',
        'declaration_date'                 => 'date',
        'reviewed_at'                      => 'datetime',
        'available_facilities'             => 'array',
        'products_handled'                 => 'array',
        'total_area_sqft'                  => 'decimal:2',
        'frontage_feet'                    => 'decimal:2',
        'max_delivery_radius_km'           => 'decimal:2',
        'security_deposit_amount'          => 'decimal:2',
        'presently_operate_service_centre' => 'boolean',
        'agree_to_nexvia_training'         => 'boolean',
        'pdi_handover_sop'                 => 'boolean',
        'otp_delivery_confirmation'        => 'boolean',
        'deposit_terms_agreed'             => 'boolean',
        'declaration_agreed'               => 'boolean',
    ];

    public function reviewer()
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'dsp_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'dsp_id');
    }

    public function walletTransactions()
    {
        return $this->hasMany(DspWalletTransaction::class, 'dsp_id')->orderBy('created_at', 'desc');
    }

    public function payoutRequests()
    {
        return $this->hasMany(DspPayoutRequest::class, 'dsp_id')->orderBy('created_at', 'desc');
    }

    /**
     * Parse serviced pincodes into cleaned array
     */
    public function servicedPincodesArray(): array
    {
        if (empty($this->pincodes)) {
            return [];
        }

        // Could be comma separated or whitespace separated
        $parts = preg_split('/[\s,]+/', trim($this->pincodes));
        return array_values(array_filter(array_map('trim', $parts)));
    }

    /**
     * Check if this DSP services a specific pincode
     */
    public function isServicingPincode(?string $pincode): bool
    {
        if (empty($pincode)) {
            return false;
        }

        $clean = trim($pincode);
        $serviced = $this->servicedPincodesArray();

        // Exact match
        if (in_array($clean, $serviced)) {
            return true;
        }

        // Check if premises pincode matches
        if (!empty($this->premises_pincode) && trim($this->premises_pincode) === $clean) {
            return true;
        }

        return false;
    }

    /**
     * Credit 5% commission on successful product delivery
     */
    public function creditDeliveryCommission(float $productAmount, ?Delivery $delivery = null, ?Booking $booking = null): DspWalletTransaction
    {
        $commissionRate = 0.05; // 5%
        $commissionEarned = round($productAmount * $commissionRate, 2);

        $newBalance = round((float)$this->wallet_balance + $commissionEarned, 2);
        $newEarned = round((float)$this->total_earned + $commissionEarned, 2);

        $this->update([
            'wallet_balance' => $newBalance,
            'total_earned'   => $newEarned,
        ]);

        $orderNumber = $delivery?->order?->order_number ?? $booking?->booking_number ?? 'Order';
        $productName = $booking?->product_name ?? $delivery?->booking?->product_name ?? 'Product';

        $tx = DspWalletTransaction::create([
            'dsp_id'           => $this->id,
            'amount'           => $commissionEarned,
            'type'             => 'credit',
            'source'           => 'delivery_commission',
            'delivery_id'      => $delivery?->id,
            'booking_id'       => $booking?->id,
            'reference_number' => 'DSP-COM-' . date('Ymd') . '-' . rand(1000, 9999),
            'description'      => "5% Delivery Commission for {$productName} (#{$orderNumber}) - Product Value ₹" . number_format($productAmount, 2),
            'balance_after'    => $newBalance,
        ]);

        if ($delivery) {
            $delivery->update([
                'dsp_commission_amount' => $commissionEarned,
                'dsp_commission_status' => 'credited',
            ]);
        }

        return $tx;
    }

    /**
     * Total Fleet Count
     */
    public function getTotalVehiclesAttribute(): int
    {
        return (int)$this->vehicles_two_wheeler + (int)$this->vehicles_three_wheeler + (int)$this->vehicles_pickup_lcv;
    }

    /**
     * Human readable status badge
     */
    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'approved'     => ['class' => 'bg-success', 'text' => 'Approved'],
            'under_review' => ['class' => 'bg-warning text-dark', 'text' => 'Under Review'],
            'rejected'     => ['class' => 'bg-danger', 'text' => 'Rejected'],
            default        => ['class' => 'bg-info text-dark', 'text' => 'Pending'],
        };
    }
}
