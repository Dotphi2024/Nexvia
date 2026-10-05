@extends('frontend.layouts.app')

@section('title', 'Digital Delivery Challan – ' . $booking->booking_number)

@section('content')
<div class="container py-4 py-lg-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            
            <!-- Top Action Bar (hidden when printing) -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 no-print">
                <a href="{{ route('booking.receipt', $booking->booking_number) }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1 rounded-3">
                    <iconify-icon icon="solar:arrow-left-linear" class="align-middle fs-18"></iconify-icon>
                    <span>Back to Booking Receipt</span>
                </a>
                <div class="d-flex gap-2">
                    <button onclick="window.print()" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 py-2 rounded-3 shadow-sm fw-bold">
                        <iconify-icon icon="solar:printer-bold" class="align-middle fs-18"></iconify-icon>
                        <span>Print Delivery Challan (PDF)</span>
                    </button>
                </div>
            </div>

            @if(!$booking->can_download_challan)
                <div class="card p-5 text-center border-warning bg-warning bg-opacity-10 rounded-4 shadow-sm mb-4">
                    <iconify-icon icon="solar:shield-warning-bold" class="fs-1 text-warning mb-3"></iconify-icon>
                    <h4 class="fw-bold text-dark mb-2">Digital Delivery Challan Pending (100% Full Payment Required)</h4>
                    <p class="text-secondary max-w-500 mx-auto mb-4">
                        The Official Digital Delivery Challan (DC) is automatically generated only after the remaining balance of <strong>₹{{ number_format($booking->balance_amount, 2) }}</strong> is 100% fully paid.
                    </p>
                    <div>
                        <a href="{{ route('booking.receipt', $booking->booking_number) }}" class="btn btn-primary px-4 py-2 rounded-3 fw-bold">
                            Pay Remaining Balance (₹{{ number_format($booking->balance_amount, 2) }})
                        </a>
                    </div>
                </div>
            @else
                @php
                    $cd = $challanData ?? app(\App\Services\DeliveryChallanService::class)->getChallanPayload($booking);
                    $seller = $cd['seller'] ?? [];
                    $customer = $cd['customer'] ?? [];
                    $product = $cd['product'] ?? [];
                    $tax = $cd['tax_breakdown'] ?? [];
                    $warranty = $cd['warranty'] ?? [];
                    $dsp = $cd['dsp_partner'] ?? [];
                    $delivery = $cd['delivery'] ?? [];
                    $qrUrl = url('/booking/challan/' . $booking->booking_number);
                @endphp

                <!-- OFFICIAL DIGITAL DELIVERY CHALLAN DOCUMENT (A4 Optimized) -->
                <div class="card p-4 p-md-5 border rounded-4 shadow bg-white challan-sheet">
                    
                    <!-- Header Section -->
                    <div class="row align-items-center pb-4 mb-4 border-bottom g-3">
                        <div class="col-sm-7">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h2 class="fw-black text-primary mb-0" style="font-weight: 800; letter-spacing: -0.5px;">NEXVIA™</h2>
                                <span class="badge bg-primary text-white micro px-2 py-1 rounded-pill">OFFICIAL DC</span>
                            </div>
                            <div class="small text-dark fw-bold">{{ $seller['company_name'] ?? 'DLS AGRO INFRAVENTURE PVT. LTD.' }}</div>
                            <div class="micro text-muted">CIN: U01111MH2021PTC368921 • GSTIN: <strong class="text-dark font-monospace">{{ $seller['gstin'] ?? '27AABCD1234E1Z5' }}</strong></div>
                            <div class="micro text-muted">Central Hub: {{ $seller['hub_address'] ?? 'Pune, Maharashtra' }} • Email: support@nexvia.in</div>
                        </div>
                        <div class="col-sm-5 text-sm-end">
                            <span class="badge bg-success text-white px-3 py-1 fs-12 rounded-pill mb-2">
                                <iconify-icon icon="solar:check-circle-bold" class="align-middle me-1"></iconify-icon> 100% FULL PAYMENT CONFIRMED
                            </span>
                            <div class="text-uppercase micro fw-bold text-muted">Digital Delivery Challan No:</div>
                            <div class="h5 fw-bold text-dark font-monospace mb-0 text-primary">{{ $cd['challan_number'] }}</div>
                            <div class="micro text-muted mt-1">Date: <strong class="text-dark">{{ \Carbon\Carbon::parse($cd['issued_at'])->format('d M, Y H:i A') }}</strong></div>
                        </div>
                    </div>

                    <!-- Challan Metadata Key-Value Strip -->
                    <div class="bg-light p-3 rounded-3 border mb-4">
                        <div class="row g-2 text-center text-sm-start">
                            <div class="col-6 col-md-3 border-end-md">
                                <span class="micro text-muted d-block text-uppercase fw-bold">Order / Booking ID</span>
                                <strong class="text-primary font-monospace fs-13">#{{ $booking->booking_number }}</strong>
                            </div>
                            <div class="col-6 col-md-3 border-end-md">
                                <span class="micro text-muted d-block text-uppercase fw-bold">Invoice Reference</span>
                                <strong class="text-dark font-monospace fs-13">{{ $cd['invoice_number'] ?? 'INV-' . date('Y') . '-001' }}</strong>
                            </div>
                            <div class="col-6 col-md-3 border-end-md">
                                <span class="micro text-muted d-block text-uppercase fw-bold">Dispatch Tracking</span>
                                <strong class="text-dark font-monospace fs-13">{{ $delivery['tracking_number'] ?? 'TRK-' . rand(10000000, 99999999) }}</strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="micro text-muted d-block text-uppercase fw-bold">Delivery OTP Required</span>
                                <strong class="text-success font-monospace fs-13"><iconify-icon icon="solar:shield-check-bold" class="align-middle"></iconify-icon> YES ({{ $delivery['delivery_otp'] ?? '6-DIGIT OTP' }})</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Customer & DSP Partner Grid -->
                    <div class="row g-4 mb-4">
                        <!-- Consignee Customer Info -->
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 h-100 bg-white">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold text-dark text-uppercase micro tracking-wider mb-0 text-muted">
                                        <iconify-icon icon="solar:user-bold" class="text-primary me-1 align-middle fs-15"></iconify-icon> Consignee / Buyer Details
                                    </h6>
                                    <span class="badge bg-light text-dark border micro">Destination</span>
                                </div>
                                <div class="fw-bold text-dark fs-14">{{ $customer['name'] }}</div>
                                <div class="small text-muted mt-1">
                                    <iconify-icon icon="solar:phone-bold" class="align-middle text-primary me-1"></iconify-icon>
                                    <strong class="text-dark font-monospace">{{ $customer['phone'] }}</strong>
                                    @if(!empty($customer['email']))
                                        <span class="text-muted ms-2">| {{ $customer['email'] }}</span>
                                    @endif
                                </div>
                                <div class="small text-secondary mt-1">
                                    <iconify-icon icon="solar:map-point-bold" class="align-middle text-primary me-1"></iconify-icon>
                                    {{ $customer['shipping_address'] }}, {{ $customer['city'] }}, {{ $customer['state'] }} – <strong class="text-dark font-monospace">{{ $customer['pincode'] }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Authorised DSP Delivery Hub -->
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 h-100 bg-white">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold text-dark text-uppercase micro tracking-wider mb-0 text-muted">
                                        <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="text-primary me-1 align-middle fs-15"></iconify-icon> Authorised Territory Partner (DSP)
                                    </h6>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle micro font-monospace">
                                        {{ $dsp['dsp_code'] ?? 'DSP-HUB' }}
                                    </span>
                                </div>
                                <div class="fw-bold text-dark fs-14">{{ $dsp['business_name'] ?? 'NEXVIA Territory Logistics Hub' }}</div>
                                <div class="small text-muted mt-1">
                                    <iconify-icon icon="solar:phone-bold" class="align-middle text-primary me-1"></iconify-icon>
                                    {{ $dsp['phone'] ?? '1800-639-842' }} 
                                    @if(!empty($dsp['contact_person']))
                                        <span class="text-muted ms-1">(Contact: {{ $dsp['contact_person'] }})</span>
                                    @endif
                                </div>
                                <div class="small text-secondary mt-1">
                                    <iconify-icon icon="solar:map-point-bold" class="align-middle text-primary me-1"></iconify-icon>
                                    {{ $dsp['hub_address'] ?? 'Authorised Regional Delivery Hub' }} (PIN: <strong class="text-dark font-monospace">{{ $dsp['pincode'] ?? $customer['pincode'] }}</strong>)
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items & Technical Serial Details Table -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-uppercase micro text-muted">
                                    <th style="width: 40px;" class="text-center">#</th>
                                    <th>Item Description & Technical Specs</th>
                                    <th class="text-center" style="width: 100px;">HSN/SAC</th>
                                    <th class="text-center" style="width: 80px;">Color</th>
                                    <th class="text-center" style="width: 60px;">Qty</th>
                                    <th class="text-end" style="width: 120px;">Unit Rate</th>
                                    <th class="text-end" style="width: 130px;">Total (INR)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center fw-bold">1</td>
                                    <td>
                                        <div class="fw-bold text-dark fs-14">{{ $product['name'] }}</div>
                                        <div class="micro text-muted mt-1">
                                            <span>Model: <strong class="text-dark font-monospace">{{ $product['model_code'] }}</strong></span> • 
                                            <span>Category: <strong class="text-dark">{{ $product['category'] }}</strong></span>
                                        </div>
                                        <div class="mt-2 p-2 bg-light rounded border micro font-monospace text-dark">
                                            <strong>Serial / Chassis / IMEI No:</strong> <span class="text-primary fw-bold">{{ $product['serial_number'] }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center font-monospace small text-muted">{{ $tax['hsn_code'] ?? '8711.60.90' }}</td>
                                    <td class="text-center small">{{ $product['selected_color'] }}</td>
                                    <td class="text-center fw-bold">{{ $product['quantity'] }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format($product['unit_mrp'], 2) }}</td>
                                    <td class="text-end fw-bold font-monospace text-dark">₹{{ number_format($product['total_mrp'], 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- GST Tax Breakdown & Warranty Information Row -->
                    <div class="row g-4 mb-4">
                        <!-- Warranty & Coverage Details -->
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 h-100 bg-white">
                                <h6 class="fw-bold text-dark text-uppercase micro tracking-wider mb-2 text-muted">
                                    <iconify-icon icon="solar:shield-check-bold" class="text-success me-1 align-middle fs-16"></iconify-icon> Official Warranty & Coverage
                                </h6>
                                <div class="badge bg-success-subtle text-success border border-success-subtle mb-2 fs-12 px-2 py-1">
                                    {{ $warranty['period'] ?? '36 Months (3 Years Warranty)' }}
                                </div>
                                <p class="small text-secondary mb-2">
                                    {{ $warranty['coverage'] ?? '3 Years Comprehensive Motor/Controller & 3 Years Battery Pack Warranty' }}
                                </p>
                                <div class="micro text-muted">
                                    <div>• <strong>Pan-India Service:</strong> {{ $warranty['terms'] ?? 'Valid at all NEXVIA DSP network points.' }}</div>
                                    <div>• <strong>Customer Support Helpline:</strong> <span class="text-dark font-monospace fw-bold">{{ $warranty['helpline'] ?? '1800-NEXVIA-CARE' }}</span></div>
                                </div>
                            </div>
                        </div>

                        <!-- Tax Breakdown Box -->
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-dark text-uppercase micro tracking-wider mb-2 text-muted">
                                    <iconify-icon icon="solar:calculator-minimalistic-bold" class="text-primary me-1 align-middle fs-16"></iconify-icon> GST / Tax Summary (18% GST Included)
                                </h6>
                                <div class="d-flex justify-content-between micro py-1 border-bottom">
                                    <span class="text-muted">Taxable Value (Base Price):</span>
                                    <strong class="font-monospace text-dark">₹{{ number_format($tax['taxable_value'] ?? ($product['total_mrp'] / 1.18), 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between micro py-1 border-bottom">
                                    <span class="text-muted">CGST ({{ $tax['cgst_rate'] ?? '9%' }}):</span>
                                    <span class="font-monospace text-dark">₹{{ number_format($tax['cgst_amount'] ?? (($product['total_mrp'] / 1.18) * 0.09), 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between micro py-1 border-bottom">
                                    <span class="text-muted">SGST / UTGST ({{ $tax['sgst_rate'] ?? '9%' }}):</span>
                                    <span class="font-monospace text-dark">₹{{ number_format($tax['sgst_amount'] ?? (($product['total_mrp'] / 1.18) * 0.09), 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between micro py-1 border-bottom">
                                    <span class="text-muted">Total GST Tax Amount:</span>
                                    <span class="font-monospace fw-bold text-dark">₹{{ number_format($tax['total_tax'] ?? (($product['total_mrp'] / 1.18) * 0.18), 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between fs-14 fw-bold pt-2 text-dark">
                                    <span>Total Dispatched Value:</span>
                                    <span class="text-primary font-monospace">₹{{ number_format($product['total_mrp'], 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between micro text-success pt-1">
                                    <span>Payment Status:</span>
                                    <strong class="text-success font-monospace">100% Saturated (Balance Due: ₹0.00)</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pre-Delivery Inspection (PDI) Checklist & Verification Seal -->
                    <div class="row g-4 mb-4 align-items-center">
                        <div class="col-md-8">
                            <div class="p-3 bg-light rounded-3 border">
                                <h6 class="fw-bold text-dark micro text-uppercase mb-2">
                                    <iconify-icon icon="solar:checklist-minimalistic-bold" class="text-primary me-1 align-middle fs-16"></iconify-icon>
                                    Mandatory Pre-Delivery Inspection (PDI) Checklist
                                </h6>
                                <div class="row g-2 micro text-secondary">
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center gap-1">
                                            <iconify-icon icon="solar:check-square-bold" class="text-success fs-15"></iconify-icon>
                                            <span>Physical Paint & Body Seal Verified</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center gap-1">
                                            <iconify-icon icon="solar:check-square-bold" class="text-success fs-15"></iconify-icon>
                                            <span>Battery & Smart Charging Kit Inspected</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center gap-1">
                                            <iconify-icon icon="solar:check-square-bold" class="text-success fs-15"></iconify-icon>
                                            <span>Chassis / Serial No. Matched with DC</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center gap-1">
                                            <iconify-icon icon="solar:check-square-bold" class="text-success fs-15"></iconify-icon>
                                            <span>Keys, Toolkit & Warranty Card Handed Over</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="p-3 border rounded-3 bg-white h-100 d-flex flex-column justify-content-center align-items-center">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data={{ urlencode($qrUrl) }}" alt="QR Seal" class="img-thumbnail rounded p-1 mb-2" style="width: 100px; height: 100px;">
                                <div class="micro fw-bold text-success">✓ DIGITAL SECURITY VERIFIED</div>
                                <div class="micro text-muted font-monospace text-truncate" style="max-width: 180px;">
                                    {{ substr($cd['verification_qr_hash'] ?? md5($booking->booking_number), 0, 16) }}...
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Handover Signatures & Customer Acknowledgement -->
                    <div class="row g-4 pt-3 mt-2 border-top">
                        <div class="col-12">
                            <p class="micro text-muted mb-3 fst-italic">
                                <strong>Declaration:</strong> Received the above-specified goods in completely sound, brand-new condition with all standard accessories, keys, documentation, and warranty certificate. PDI test completed satisfactorily.
                            </p>
                        </div>
                        <div class="col-6 text-center">
                            <div class="border-top border-secondary border-dashed pt-2 mx-3 mt-3">
                                <span class="micro text-muted text-uppercase fw-bold d-block">Authorised DSP Agent Signature & Stamp</span>
                                <span class="micro text-muted font-monospace">(DSP Code: {{ $dsp['dsp_code'] ?? 'DSP-HUB' }})</span>
                            </div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="border-top border-secondary border-dashed pt-2 mx-3 mt-3">
                                <span class="micro text-muted text-uppercase fw-bold d-block">Customer / Receiver Signature</span>
                                <span class="micro text-muted font-monospace">({{ $customer['name'] }})</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Note -->
                    <div class="text-center pt-4 mt-4 border-top">
                        <span class="micro text-muted">This is an authorized computer-generated Digital Delivery Challan (DC) issued under the Nexvia Sales & Territory Delivery Network. No physical signature required for legal transit.</span>
                    </div>

                </div>
            @endif
        </div>
    </div>
</div>

<style>
@media print {
    .no-print, header, footer, .navbar, .btn {
        display: none !important;
    }
    body {
        background: #fff !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .container {
        max-width: 100% !important;
        width: 100% !important;
        padding: 0 !important;
    }
    .challan-sheet {
        border: none !important;
        box-shadow: none !important;
        padding: 10px !important;
    }
    .border-end-md {
        border-right: 1px solid #dee2e6 !important;
    }
}
@media (min-width: 768px) {
    .border-end-md {
        border-right: 1px solid #dee2e6;
    }
}
</style>
@endsection
