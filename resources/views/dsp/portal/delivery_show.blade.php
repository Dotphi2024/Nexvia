@extends('dsp.layouts.portal')

@section('title', 'Delivery Details – #' . ($delivery->booking?->booking_number ?? $delivery->tracking_number))

@section('content')
<div class="mb-4">
    <a href="{{ route('dsp.deliveries') }}" class="btn btn-outline-secondary btn-sm rounded-3 fw-bold mb-3">
        <iconify-icon icon="solar:arrow-left-bold" class="align-middle me-1"></iconify-icon> Back to Deliveries
    </a>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                Delivery Order #{{ $delivery->booking?->booking_number ?? $delivery->tracking_number }}
            </h4>
            <span class="font-monospace text-muted small">Tracking Number: <strong>{{ $delivery->tracking_number }}</strong></span>
            <span class="text-muted small ms-3">Challan: <strong>{{ $delivery->challan_number ?: 'Pending' }}</strong></span>
            <span class="text-muted small ms-3">Created: {{ $delivery->created_at->format('d M, Y H:i') }}</span>
        </div>

        <div>
            @if($delivery->stage === 'delivered')
                <span class="badge bg-success fs-6 px-3 py-2">
                    <iconify-icon icon="solar:check-circle-bold" class="align-middle me-1"></iconify-icon> DELIVERED
                </span>
            @elseif($delivery->stage === 'out_for_delivery')
                <span class="badge bg-info text-white fs-6 px-3 py-2">
                    <iconify-icon icon="solar:bicycling-bold" class="align-middle me-1"></iconify-icon> OUT FOR DELIVERY
                </span>
            @elseif($delivery->stage === 'received_at_dsp')
                <span class="badge bg-primary text-white fs-6 px-3 py-2">
                    <iconify-icon icon="solar:box-minimalistic-bold" class="align-middle me-1"></iconify-icon> RECEIVED AT DSP HUB
                </span>
            @elseif($delivery->stage === 'dispatched')
                <span class="badge bg-secondary text-white fs-6 px-3 py-2">
                    <iconify-icon icon="solar:plain-2-bold" class="align-middle me-1"></iconify-icon> DISPATCHED TO DSP
                </span>
            @else
                <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                    <iconify-icon icon="solar:clock-circle-bold" class="align-middle me-1"></iconify-icon> ASSIGNED TO YOU
                </span>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4 shadow-sm" role="alert">
        <iconify-icon icon="solar:check-circle-bold" class="fs-18 me-1 align-middle text-success"></iconify-icon>
        <strong>Success!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 shadow-sm" role="alert">
        <iconify-icon icon="solar:danger-circle-bold" class="fs-18 me-1 align-middle text-danger"></iconify-icon>
        <strong>Action Required:</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <!-- Left Column: Delivery Status Action & Customer Details -->
    <div class="col-lg-7">
        <!-- 5% Commission Banner -->
        <div class="card-dsp p-4 mb-4" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border: 1.5px solid #a7f3d0;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="micro text-success text-uppercase fw-bold d-block">Your Authorised DSP Commission (5%)</span>
                    <h3 class="fw-extrabold text-success mb-0">+₹{{ number_format($potentialCommission, 2) }}</h3>
                    <span class="micro text-muted">Calculated on product MRP of ₹{{ number_format($delivery->booking?->mrp ?? $delivery->order?->total_amount ?? 0, 2) }}</span>
                </div>
                <div>
                    @if($delivery->stage === 'delivered')
                        <span class="badge bg-success text-white px-3 py-2">
                            <iconify-icon icon="solar:check-circle-bold" class="align-middle me-1"></iconify-icon>
                            CREDITED TO CASH WALLET
                        </span>
                    @else
                        <span class="badge bg-warning text-dark px-3 py-2">
                            <iconify-icon icon="solar:clock-circle-bold" class="align-middle me-1"></iconify-icon>
                            CREDITED ON OTP DELIVERY
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- 3-Stage Delivery Action Form Cards -->
        @if($delivery->stage !== 'delivered')
            
            <!-- Action 1: Product Received at DSP Hub (if not received yet) -->
            @if(in_array($delivery->stage, ['order_confirmed', 'processing', 'dispatched', 'assigned', 'pending']))
                <div class="card-dsp p-4 mb-4 border-2 border-primary">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="fw-bold text-dark mb-0">
                            <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="text-primary me-1 align-middle fs-20"></iconify-icon>
                            Step 1: Confirm Product Physical Receipt at Hub
                        </h5>
                        <span class="badge bg-primary-subtle text-primary micro">Pending Physical Receipt</span>
                    </div>
                    <p class="small text-muted mb-3">
                        When the central warehouse dispatch vehicle arrives at your DSP center, verify package condition, seal, and model before confirming receipt.
                    </p>

                    <form action="{{ route('dsp.deliveries.status', $delivery->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="stage" value="received_at_dsp">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">Receipt & Inspection Remarks</label>
                            <input type="text" name="delivery_notes" class="form-control" placeholder="e.g. Received unit intact, seal verified, battery kit checked at Baner DSP hub.">
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100 py-2 fw-bold shadow-sm">
                            <iconify-icon icon="solar:check-circle-bold" class="align-middle me-1"></iconify-icon>
                            Confirm "Product Received at DSP Hub"
                        </button>
                    </form>
                </div>
            @endif

            <!-- Action 2 & 3: Out for Delivery & Complete OTP Delivery -->
            <div class="card-dsp p-4 mb-4 border-primary">
                <h5 class="fw-bold text-dark mb-3">
                    <iconify-icon icon="solar:delivery-bold" class="text-primary me-1 align-middle"></iconify-icon>
                    Update Delivery Handover Status
                </h5>

                <form action="{{ route('dsp.deliveries.status', $delivery->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Select Action Stage *</label>
                        <select name="stage" class="form-select form-select-lg" required>
                            @if($delivery->stage !== 'out_for_delivery')
                                <option value="out_for_delivery" {{ ($delivery->stage === 'received_at_dsp') ? 'selected' : '' }}>
                                    🚚 Mark Out for Delivery (Vehicle In Transit to Customer Doorstep)
                                </option>
                            @endif
                            <option value="delivered" {{ ($delivery->stage === 'out_for_delivery') ? 'selected' : '' }}>
                                ✅ Mark as DELIVERED & Activate Warranty (Customer OTP Required)
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold text-dark small mb-0">
                                Customer Delivery OTP <span class="text-danger">* (Required for Delivery)</span>
                            </label>
                            <span class="micro text-muted">Customer has this code on their receipt/SMS</span>
                        </div>
                        <input type="text" name="delivery_otp" class="form-control form-control-lg font-monospace fw-bold text-primary" 
                               placeholder="Enter 6-digit Customer OTP (e.g. {{ $delivery->delivery_otp ?: '849201' }})">
                        <div class="micro text-muted mt-1">
                            <iconify-icon icon="solar:shield-check-bold" class="text-success align-middle"></iconify-icon>
                            Security Rule: Delivery cannot be confirmed and 5% commission will not be credited without valid customer OTP.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">PDI & Handover Notes</label>
                        <textarea name="delivery_notes" class="form-control" rows="2" placeholder="e.g. PDI inspection passed, unboxed, vehicle keys and warranty certificate handed over.">{{ $delivery->delivery_notes }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100 py-3 fw-bold shadow">
                        <iconify-icon icon="solar:check-circle-bold" class="me-1 align-middle fs-5"></iconify-icon>
                        Confirm Delivery & Earn ₹{{ number_format($potentialCommission, 2) }}
                    </button>
                </form>
            </div>
        @else
            <!-- Completed & Active Warranty Card -->
            <div class="card-dsp p-4 mb-4 bg-light border-2 border-success">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-success text-white p-3 rounded-circle fs-3">
                        <iconify-icon icon="solar:check-read-bold"></iconify-icon>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Delivery Completed Successfully</h5>
                        <p class="small text-muted mb-0">Delivered on <strong>{{ $delivery->delivered_at ? $delivery->delivered_at->format('d M, Y \a\t H:i') : 'Recently' }}</strong>. 5% Commission (₹{{ number_format($delivery->dsp_commission_amount ?: $potentialCommission, 2) }}) credited to your cash wallet.</p>
                    </div>
                </div>

                <!-- Automatic Warranty Info Box -->
                <div class="p-3 bg-white rounded-3 border border-success-subtle">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="text-success small d-flex align-items-center gap-1">
                            <iconify-icon icon="solar:shield-check-bold" class="fs-18"></iconify-icon>
                            Official Warranty Automatically Activated (Active 🟢)
                        </strong>
                        <span class="badge bg-success text-white micro">3 Years Coverage</span>
                    </div>
                    <div class="micro text-muted">
                        Serial / Chassis No: <strong class="font-monospace text-dark">{{ $delivery->serial_number ?: ($delivery->booking?->serial_number ?: 'NX-EV-2026-CH88491') }}</strong> • 
                        Validity: <strong>{{ now()->format('d M, Y') }} to {{ now()->addYears(3)->format('d M, Y') }}</strong>
                    </div>
                </div>
            </div>
        @endif

        <!-- Customer Contact & Address -->
        <div class="card-dsp p-4 mb-4">
            <h5 class="fw-bold text-dark mb-3">
                <iconify-icon icon="solar:user-bold" class="text-primary me-1 align-middle"></iconify-icon>
                Customer & Destination Shipping Address
            </h5>

            @php
                $booking = $delivery->booking;
                $order = $delivery->order;
                $custName = $booking?->customer_name ?? $order?->customer_name ?? 'NEXVIA Customer';
                $custPhone = $booking?->customer_phone ?? $order?->customer_phone ?? '';
                $custEmail = $booking?->customer_email ?? '';
                $address = $booking?->shipping_address ?? $order?->shipping_address ?? '';
                $city = $booking?->city ?? $order?->city ?? '';
                $state = $booking?->state ?? $order?->state ?? '';
                $pincode = $booking?->pincode ?? $order?->pincode ?? '';
            @endphp

            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <span class="micro text-muted d-block">Customer Name</span>
                    <strong class="text-dark">{{ $custName }}</strong>
                </div>
                <div class="col-sm-6">
                    <span class="micro text-muted d-block">Contact Phone</span>
                    <strong class="text-dark">{{ $custPhone }}</strong>
                </div>
            </div>

            <!-- Click to Call & WhatsApp Quick Buttons -->
            @if($custPhone)
                <div class="d-flex gap-2 mb-3">
                    <a href="tel:{{ $custPhone }}" class="btn btn-outline-primary btn-sm flex-fill fw-bold py-2">
                        <iconify-icon icon="solar:phone-calling-bold" class="align-middle me-1 fs-5"></iconify-icon>
                        Call Customer
                    </a>
                    <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $custPhone) }}" target="_blank" class="btn btn-outline-success btn-sm flex-fill fw-bold py-2">
                        <iconify-icon icon="logos:whatsapp-icon" class="align-middle me-1 fs-5"></iconify-icon>
                        WhatsApp Message
                    </a>
                </div>
            @endif

            <div class="p-3 bg-light rounded-3 border">
                <span class="micro text-muted text-uppercase fw-bold d-block mb-1">Destination Address</span>
                <p class="text-dark small mb-1">{{ $address }}</p>
                <div class="small fw-semibold text-secondary">
                    {{ $city }}{{ $city && $state ? ', ' : '' }}{{ $state }}
                    <span class="badge bg-primary text-white ms-1 font-monospace">PIN: {{ $pincode }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Product Specs & Pricing -->
    <div class="col-lg-5">
        <div class="card-dsp p-4 mb-4">
            <h5 class="fw-bold text-dark mb-3">Product Information</h5>

            <div class="p-3 bg-light rounded-3 border mb-3">
                <h6 class="fw-bold text-dark mb-1">{{ $booking?->product_name ?? 'NEXVIA Mobility Unit' }}</h6>
                @if($booking?->model_code)
                    <div class="font-monospace text-muted micro mb-2">Model: {{ $booking->model_code }}</div>
                @endif
                <div class="d-flex justify-content-between small">
                    <span class="text-muted">Quantity:</span>
                    <strong class="text-dark">{{ $booking?->quantity ?? 1 }} Unit(s)</strong>
                </div>
                @if($booking?->selected_color)
                    <div class="d-flex justify-content-between small mt-1">
                        <span class="text-muted">Selected Color:</span>
                        <strong class="text-dark">{{ $booking->selected_color }}</strong>
                    </div>
                @endif
                <div class="d-flex justify-content-between small mt-1">
                    <span class="text-muted">Serial / Chassis No:</span>
                    <strong class="text-primary font-monospace">{{ $delivery->serial_number ?: ($booking?->serial_number ?: 'Assigned at Dispatch') }}</strong>
                </div>
            </div>

            <div class="d-flex justify-content-between mb-2 small">
                <span class="text-muted">Total Product Value (MRP):</span>
                <strong class="text-dark">₹{{ number_format($delivery->booking?->mrp ?? $delivery->order?->total_amount ?? 0, 2) }}</strong>
            </div>

            @if($booking)
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Booking Down Payment (20%):</span>
                    <strong class="text-success">₹{{ number_format($booking->booking_amount, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Balance Due (80%):</span>
                    <strong class="{{ $booking->payment_status === 'fully_paid' ? 'text-success' : 'text-danger' }}">
                        ₹{{ number_format($booking->balance_amount, 2) }}
                    </strong>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Customer Payment Status:</span>
                    <span class="badge {{ $booking->payment_status === 'fully_paid' ? 'bg-success' : 'bg-warning text-dark' }}">
                        {{ strtoupper(str_replace('_', ' ', $booking->payment_status)) }}
                    </span>
                </div>
            @endif

            <hr class="my-3">

            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small">DSP Commission (5%):</span>
                <span class="badge-commission fs-6">
                    ₹{{ number_format($potentialCommission, 2) }}
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
