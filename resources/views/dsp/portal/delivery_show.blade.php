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
            <span class="text-muted small ms-3">Created: {{ $delivery->created_at->format('d M, Y H:i') }}</span>
        </div>

        <div>
            @if($delivery->stage === 'delivered')
                <span class="badge bg-success fs-6 px-3 py-2">DELIVERED</span>
            @elseif($delivery->stage === 'out_for_delivery')
                <span class="badge bg-info text-white fs-6 px-3 py-2">OUT FOR DELIVERY</span>
            @else
                <span class="badge bg-warning text-dark fs-6 px-3 py-2">ASSIGNED TO YOU</span>
            @endif
        </div>
    </div>
</div>

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
                            CREDITED ON DELIVERY
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Status Action Form Card -->
        @if($delivery->stage !== 'delivered')
            <div class="card-dsp p-4 mb-4 border-primary">
                <h5 class="fw-bold text-dark mb-3">
                    <iconify-icon icon="solar:delivery-bold" class="text-primary me-1 align-middle"></iconify-icon>
                    Update Delivery Status
                </h5>

                <form action="{{ route('dsp.deliveries.status', $delivery->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Select Action *</label>
                        <select name="stage" class="form-select form-select-lg" required>
                            @if($delivery->stage !== 'out_for_delivery')
                                <option value="out_for_delivery">Mark Out for Delivery (Vehicle In Transit)</option>
                            @endif
                            <option value="delivered" selected>Mark as SUCCESSFULLY DELIVERED (Credit 5% Commission)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Customer Delivery OTP / Signature Ref</label>
                        <input type="text" name="delivery_otp" class="form-control font-monospace" placeholder="Enter customer OTP code if available">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Delivery Execution Notes</label>
                        <textarea name="delivery_notes" class="form-control" rows="3" placeholder="e.g. Unboxed, PDI completed, vehicle keys handed over to customer."></textarea>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100 py-3 fw-bold shadow">
                        <iconify-icon icon="solar:check-circle-bold" class="me-1 align-middle fs-5"></iconify-icon>
                        Confirm Status & Earn ₹{{ number_format($potentialCommission, 2) }}
                    </button>
                </form>
            </div>
        @else
            <div class="card-dsp p-4 mb-4 bg-light">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success text-white p-3 rounded-circle fs-3">
                        <iconify-icon icon="solar:check-read-bold"></iconify-icon>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Delivery Completed Successfully</h5>
                        <p class="small text-muted mb-0">Delivered on <strong>{{ $delivery->delivered_at ? $delivery->delivered_at->format('d M, Y \a\t H:i') : 'Recently' }}</strong>. 5% Commission (₹{{ number_format($delivery->dsp_commission_amount ?: $potentialCommission, 2) }}) is available in your cash wallet.</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Customer Contact & Address -->
        <div class="card-dsp p-4 mb-4">
            <h5 class="fw-bold text-dark mb-3">
                <iconify-icon icon="solar:user-bold" class="text-primary me-1 align-middle"></iconify-icon>
                Customer & Shipping Information
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
