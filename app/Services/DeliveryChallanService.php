<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Delivery;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DeliveryChallanService
{
    /**
     * Automatically generate Digital Delivery Challan (DC) when 100% full payment is confirmed.
     *
     * @param Booking $booking
     * @return Delivery|null
     */
    public function generateForBooking(Booking $booking): ?Delivery
    {
        // Strict Rule: Only generate Delivery Challan if booking is 100% fully paid
        if ($booking->payment_status !== 'fully_paid' && (float)$booking->balance_amount > 0) {
            return null;
        }

        $isDirty = false;

        // 1. Generate Challan Number if not already generated
        if (empty($booking->delivery_challan_number)) {
            $challanNumber = 'DC-' . date('Y') . '-' . strtoupper(Str::random(6));
            $booking->delivery_challan_number = $challanNumber;
            $booking->challan_generated_at = now();
            $isDirty = true;
        } else {
            $challanNumber = $booking->delivery_challan_number;
        }

        // 2. Generate Tax Invoice Reference if not present
        if (empty($booking->invoice_number)) {
            $invoiceNumber = 'INV-' . date('Y') . '-' . strtoupper(substr(md5($booking->booking_number . 'INV'), 0, 6));
            $booking->invoice_number = $invoiceNumber;
            $isDirty = true;
        } else {
            $invoiceNumber = $booking->invoice_number;
        }

        // 3. Generate or assign Serial No / Chassis No / IMEI if not present
        if (empty($booking->serial_number)) {
            $modelPrefix = strtoupper(preg_replace('/[^A-Z0-9]/', '', substr($booking->model_code ?: 'NX', 0, 3)));
            $serialNumber = 'NX-' . ($modelPrefix ?: 'EV') . '-' . date('Y') . '-' . strtoupper(substr(md5($booking->booking_number . 'SERIAL'), 0, 8));
            $booking->serial_number = $serialNumber;
            $isDirty = true;
        } else {
            $serialNumber = $booking->serial_number;
        }

        if ($isDirty) {
            $booking->save();
        }

        // Find or create Delivery tracking record for DSP
        $delivery = Delivery::firstOrCreate(
            ['booking_id' => $booking->id],
            [
                'dsp_id'          => $booking->dsp_id,
                'tracking_number' => 'TRK-' . rand(10000000, 99999999),
                'stage'           => 'order_confirmed',
            ]
        );

        // Update Delivery record with Challan, Invoice, and Serial details
        $delivery->update([
            'challan_number'       => $challanNumber,
            'challan_generated_at' => $delivery->challan_generated_at ?: now(),
            'challan_status'       => 'generated',
            'serial_number'        => $delivery->serial_number ?: $serialNumber,
            'invoice_number'       => $delivery->invoice_number ?: $invoiceNumber,
            'pdi_status'           => $delivery->pdi_status ?: 'passed',
            'stage'                => in_array($delivery->stage, ['order_confirmed', 'pending']) ? 'processing' : $delivery->stage,
            'dsp_id'               => $delivery->dsp_id ?: $booking->dsp_id,
        ]);

        return $delivery;
    }

    /**
     * Get Digital Delivery Challan formatted payload for API / View.
     */
    public function getChallanPayload(Booking $booking): ?array
    {
        if ($booking->payment_status !== 'fully_paid' && (float)$booking->balance_amount > 0) {
            return [
                'status'         => false,
                'is_eligible'    => false,
                'challan_status' => 'pending_full_payment',
                'message'        => 'Digital Delivery Challan (DC) will be automatically generated once 100% full payment is confirmed.',
                'balance_amount' => (float)$booking->balance_amount,
            ];
        }

        $delivery = $this->generateForBooking($booking);

        $challanNumber = $booking->delivery_challan_number ?: ($delivery?->challan_number);
        $issueDate = $booking->challan_generated_at ? $booking->challan_generated_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s');
        $invoiceNumber = $booking->invoice_number ?: ($delivery?->invoice_number ?: ('INV-' . date('Y') . '-' . strtoupper(substr(md5($booking->booking_number), 0, 6))));
        $serialNumber = $booking->serial_number ?: ($delivery?->serial_number ?: ('NX-EV-' . date('Y') . '-' . strtoupper(substr(md5($booking->booking_number), 0, 8))));

        $totalMrp = (float) $booking->mrp;
        $qty = max(1, (int) ($booking->quantity ?: 1));
        $unitPrice = round($totalMrp / $qty, 2);

        // Category & Product Classification for GST (5% for DLS Agro vs 18% for others)
        $category = $booking->product?->category;
        $catType  = strtolower($category?->type ?? '');
        $catName  = strtolower($category?->name ?? '');
        $catSlug  = strtolower($category?->slug ?? '');
        $prodName = strtolower($booking->product_name ?? '');

        $isDlsAgro = ($catType === 'dls_farm_equipment' || $catType === 'dls_agro' || $catType === 'agro' || $catType === 'farm')
            || str_contains($catName, 'farm')
            || str_contains($catName, 'agro')
            || str_contains($catSlug, 'farm')
            || str_contains($catSlug, 'agro')
            || str_contains($prodName, 'cultivator')
            || str_contains($prodName, 'tiller')
            || str_contains($prodName, 'dls agro');

        if ($isDlsAgro) {
            // 5% GST for DLS Agro Products (2.5% CGST + 2.5% SGST)
            $gstRatePercent = 5.0;
            $cgstRate = '2.5%';
            $sgstRate = '2.5%';
            $taxableValue = round($totalMrp / 1.05, 2);
            $cgstAmount = round($taxableValue * 0.025, 2);
            $sgstAmount = round($taxableValue * 0.025, 2);
            $totalTax = round($totalMrp - $taxableValue, 2);
            $hsnCode = '8432.80.90'; // Agricultural Machinery & Farm Equipment
        } else {
            // 18% GST for All Other Products (9% CGST + 9% SGST)
            $gstRatePercent = 18.0;
            $cgstRate = '9%';
            $sgstRate = '9%';
            $taxableValue = round($totalMrp / 1.18, 2);
            $cgstAmount = round($taxableValue * 0.09, 2);
            $sgstAmount = round($taxableValue * 0.09, 2);
            $totalTax = round($totalMrp - $taxableValue, 2);
            $hsnCode = '8711.60.90'; // Default: Electric Two-Wheelers & EV Mobility
            if (str_contains($catName, 'solar') || str_contains($catName, 'energy')) {
                $hsnCode = '8504.40.80';
            } elseif (str_contains($catName, 'appliance') || str_contains($catName, 'tv') || str_contains($catName, 'electronics')) {
                $hsnCode = '8528.72.00';
            }
        }

        $warrantyInfo = $booking->product?->warranty_info 
            ?: '3 Years Comprehensive Motor/Controller & 3 Years Battery Pack Warranty';

        $dsp = $booking->dsp ?: $delivery?->dsp;

        // Build complete payment receipts and installment ledger
        $tokenAmount = (float) $booking->booking_amount;
        $tokenReceipt = $booking->payment_receipt;
        $tokenReceiptUrl = $tokenReceipt ? (str_starts_with($tokenReceipt, 'http') ? $tokenReceipt : asset($tokenReceipt)) : null;

        $rawHistory = is_array($booking->balance_payments_history) ? $booking->balance_payments_history : [];
        $installmentsList = [];
        $allReceiptUrls = [];
        if ($tokenReceiptUrl) {
            $allReceiptUrls[] = [
                'type'        => 'token_20_percent',
                'title'       => 'Initial 20% Token Booking Receipt',
                'receipt_url' => $tokenReceiptUrl,
            ];
        }

        foreach ($rawHistory as $idx => $item) {
            $itemReceipt = $item['receipt_file'] ?? null;
            $itemReceiptUrl = $item['receipt_url'] ?? ($itemReceipt ? (str_starts_with($itemReceipt, 'http') ? $itemReceipt : asset($itemReceipt)) : null);
            if ($itemReceiptUrl) {
                $allReceiptUrls[] = [
                    'type'        => 'installment_80_percent',
                    'title'       => 'Installment #' . ($item['installment_no'] ?? ($idx + 1)) . ' Receipt',
                    'receipt_url' => $itemReceiptUrl,
                    'reference_no'=> $item['reference_no'] ?? null,
                ];
            }
            $item['receipt_url'] = $itemReceiptUrl;
            $installmentsList[] = $item;
        }

        return [
            'status'                  => true,
            'is_eligible'             => true,
            'challan_number'          => $challanNumber,
            'challan_status'          => 'generated',
            'issued_at'               => $issueDate,
            'booking_number'          => $booking->booking_number,
            'booking_date'            => $booking->booking_date ? $booking->booking_date->format('Y-m-d') : null,
            'invoice_number'          => $invoiceNumber,
            'serial_number'           => $serialNumber,

            // Company & Seller GST Details
            'seller'                  => [
                'company_name'        => 'DLS AGRO INFRAVENTURE PVT. LTD.',
                'brand_name'          => 'NEXVIA™',
                'gstin'               => '27AABCD1234E1Z5',
                'pan'                 => 'AABCD1234E',
                'state_code'          => '27 (Maharashtra)',
                'hub_address'         => 'NEXVIA Central Mobility Hub, Pune, Maharashtra - 411045',
                'support_email'       => 'support@nexvia.in',
                'toll_free'           => '1800-NEXVIA-CARE (1800-639-842)',
            ],

            // Customer / Consignee Details
            'customer'                => [
                'name'                => $booking->customer_name,
                'phone'               => $booking->customer_phone,
                'email'               => $booking->customer_email,
                'shipping_address'    => $booking->shipping_address,
                'city'                => $booking->city,
                'state'               => $booking->state,
                'pincode'             => $booking->pincode,
            ],

            // Product / Dispatched Item Details
            'product'                 => [
                'id'                  => $booking->product_id,
                'name'                => $booking->product_name,
                'category'            => $booking->product?->category?->name ?: 'Electric Mobility & Smart Tech',
                'model_code'          => $booking->model_code ?: 'NEX-STANDARD',
                'selected_color'      => $booking->selected_color ?: 'Standard',
                'quantity'            => $qty,
                'unit_mrp'            => $unitPrice,
                'total_mrp'           => $totalMrp,
                'serial_number'       => $serialNumber,
                'hsn_code'            => $hsnCode,
            ],

            // GST & Tax Breakdown (5% for DLS Agro vs 18% for others)
            'tax_breakdown'           => [
                'hsn_code'            => $hsnCode,
                'gst_rate'            => $gstRatePercent . '%',
                'gst_rate_percent'    => $gstRatePercent,
                'is_dls_agro'         => $isDlsAgro,
                'taxable_value'       => $taxableValue,
                'cgst_rate'           => $cgstRate,
                'cgst_amount'         => $cgstAmount,
                'sgst_rate'           => $sgstRate,
                'sgst_amount'         => $sgstAmount,
                'total_tax'           => $totalTax,
                'grand_total'         => $totalMrp,
            ],

            // Comprehensive Payment & Installments Ledger
            'payment'                 => [
                'total_amount'            => $totalMrp,
                'amount_paid'             => $totalMrp,
                'balance_remaining'       => 0.00,
                'payment_status'          => '100% FULLY PAID',
                'payment_type'            => $booking->payment_type,
                'payment_ref'             => $booking->offline_payment_ref ?: ('TXN-' . strtoupper(substr(md5($booking->booking_number), 0, 8))),
                'payment_confirmed_at'    => $issueDate,
                'token_20_percent'        => [
                    'amount'       => $tokenAmount,
                    'receipt_file' => $tokenReceipt,
                    'receipt_url'  => $tokenReceiptUrl,
                    'paid_at'      => $booking->booking_date ? $booking->booking_date->format('Y-m-d') : null,
                ],
                'balance_80_percent'      => [
                    'total_balance_paid' => round($totalMrp - $tokenAmount, 2),
                    'installments_count' => count($installmentsList),
                    'installments'       => $installmentsList,
                ],
                'all_receipts'            => $allReceiptUrls,
            ],

            // Warranty Details
            'warranty'                => [
                'coverage'            => $warrantyInfo,
                'period'              => '36 Months (3 Years)',
                'terms'               => 'Applicable Pan-India across all Authorised NEXVIA / DLS Service Hubs & Territory DSP Network',
                'helpline'            => '1800-NEXVIA-CARE / support@nexvia.in',
            ],

            // DSP (Delivery & Service Point) Partner Details
            'dsp_partner'             => $dsp ? [
                'id'                  => $dsp->id,
                'dsp_code'            => $dsp->application_number ?: ('DSP-MH-' . str_pad($dsp->id, 4, '0', STR_PAD_LEFT)),
                'business_name'       => $dsp->business_name ?: $dsp->applicant_name,
                'contact_person'      => $dsp->applicant_name,
                'phone'               => $dsp->mobile ?: $dsp->phone,
                'email'               => $dsp->email,
                'hub_address'         => $dsp->complete_address ?: ($dsp->district . ', ' . $dsp->state),
                'district'            => $dsp->district,
                'pincode'             => $dsp->premises_pincode,
                'serviced_pincodes'   => $dsp->pincodes,
            ] : [
                'status'              => 'Assigned Nearest Territory Partner',
                'dsp_code'            => 'DSP-AUTO-ALLOCATED',
                'business_name'       => 'NEXVIA Territory Logistics Hub',
                'hub_address'         => 'Authorised Regional Dispatch Hub, ' . $booking->state,
                'district'            => $booking->city,
                'pincode'             => $booking->pincode,
            ],

            // Delivery & Logistics Status (No OTP required for Challan generation)
            'delivery'                => [
                'tracking_number'     => $delivery?->tracking_number,
                'stage'               => $delivery?->stage ?: 'processing',
                'pdi_status'          => $delivery?->pdi_status ?: 'Passed / Verified',
                'delivery_otp'        => null,
                'otp_required'        => false,
                'delivery_mode'       => 'Doorstep Territory Direct Delivery (No OTP Required)',
            ],

            // Security & Verification Hash
            'verification_qr_hash'    => hash('sha256', $challanNumber . $booking->booking_number . 'NEXVIA_OFFICIAL_DC_VERIFIED'),
            'verification_url'        => url('/booking/challan/' . $booking->booking_number),
        ];
    }
}
