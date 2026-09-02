@extends('frontend.layouts.app')

@section('title', 'Checkout Booking – ' . $product->name)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h3 class="fw-bold text-dark mb-4">Complete Your Booking</h3>

            <form action="{{ route('booking.process', $product->slug) }}" method="POST">
                @csrf
                <input type="hidden" name="payment_type" value="{{ $paymentType }}">

                <div class="row g-4">
                    <!-- Shipping / Customer Details Form -->
                    <div class="col-lg-7">
                        <div class="card card-nexvia p-4 mb-4">
                            <h5 class="fw-bold text-dark mb-3">Customer & Delivery Information</h5>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Full Name *</label>
                                <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $user->name ?? '') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Mobile Phone Number *</label>
                                <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone', $user->phone ?? '') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Delivery Address *</label>
                                <textarea name="shipping_address" class="form-control" rows="3" required placeholder="House/Flat No., Street, Landmark">{{ old('shipping_address') }}</textarea>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Pincode *</label>
                                    <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $user->pincode ?? '') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">City *</label>
                                    <input type="text" name="city" class="form-control" value="{{ old('city', $user->city ?? '') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">State *</label>
                                    <input type="text" name="state" class="form-control" value="{{ old('state', $user->state ?? '') }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- PRODUCT CREDIT WALLET REDEMPTION -->
                        @if(Auth::guard('web')->check() && $user->wallet_balance > 0)
                            <div class="card card-nexvia p-4 mb-4 border-success bg-success bg-opacity-10">
                                <div class="d-flex align-items-center gap-3">
                                    <iconify-icon icon="solar:wallet-money-bold" class="fs-1 text-success"></iconify-icon>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0">Use Product Credit Wallet Balance</h6>
                                        <span class="small text-muted">Available Credit Balance: <strong class="text-success fs-6">₹{{ number_format($user->wallet_balance, 2) }}</strong></span>
                                    </div>
                                </div>
                                <hr class="my-2 border-success border-opacity-25">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="use_wallet" id="walletCheck" value="1">
                                    <label class="form-check-label fw-bold text-dark small" for="walletCheck">
                                        Apply Product Credit Wallet balance to discount your upfront payment!
                                    </label>
                                </div>
                            </div>
                        @endif

                        <!-- REFUND POLICY MANDATORY ACCEPTANCE -->
                        @if($paymentType === 'booking_20')
                            <div class="card card-nexvia p-4 border-warning bg-warning bg-opacity-10">
                                <h6 class="fw-bold text-dark mb-2">
                                    <iconify-icon icon="solar:shield-warning-bold" class="text-warning fs-5 align-middle me-1"></iconify-icon>
                                    Non-Refundable Booking Policy Declaration
                                </h6>
                                <p class="small text-secondary mb-3">
                                    By booking with NEXVIA, you pay only 20% today. The remaining 80% balance is payable within 60 days. Please note: <strong>The 20% Booking Amount is Non-Refundable</strong>, but your digital receipt is 100% <strong>Transferable</strong> to any other buyer.
                                </p>

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="non_refundable_terms" id="termsCheck" required value="1">
                                    <label class="form-check-label fw-bold text-dark small" for="termsCheck">
                                        I understand and accept that the 20% Booking Amount is Non-Refundable, and the 80% balance is due within 60 days. *
                                    </label>
                                </div>
                                @error('non_refundable_terms')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @errorEnd
                            </div>
                        @endif
                    </div>

                    <!-- Order Summary Card -->
                    <div class="col-lg-5">
                        <div class="card card-nexvia p-4">
                            <h5 class="fw-bold text-dark mb-3">Order & Payment Summary</h5>

                            <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 mb-3">
                                <iconify-icon icon="{{ $product->category->icon ?? 'solar:box-bold-duotone' }}" style="font-size: 40px; color: #2563eb;"></iconify-icon>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">{{ $product->name }}</h6>
                                    <span class="small text-muted">{{ $product->category->name }} (Commission Slab: {{ $product->category->commission_percentage }}%)</span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Product Price (MRP):</span>
                                <span class="fw-bold">₹{{ number_format($product->mrp, 2) }}</span>
                            </div>

                            <hr class="my-2">

                            @if($paymentType === 'booking_20')
                                <div class="d-flex justify-content-between mb-2 text-primary fs-5 fw-bold">
                                    <span>Payable Today (20%):</span>
                                    <span>₹{{ number_format($product->booking_amount, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-3 text-secondary small">
                                    <span>Balance Due (within 60 Days):</span>
                                    <span class="fw-bold">₹{{ number_format($product->balance_amount, 2) }}</span>
                                </div>
                                <div class="p-2 bg-light rounded text-center small text-muted mb-4">
                                    Balance due date: <strong>{{ \Carbon\Carbon::today()->addDays(60)->format('d M, Y') }}</strong>
                                </div>
                            @else
                                <div class="d-flex justify-content-between mb-3 text-primary fs-5 fw-bold">
                                    <span>Full Payment Today:</span>
                                    <span>₹{{ number_format($product->mrp, 2) }}</span>
                                </div>
                            @endif

                            <button type="submit" class="btn btn-nexvia-primary btn-lg w-100 py-3 shadow">
                                Confirm & Pay ₹{{ number_format($paymentType === 'booking_20' ? $product->booking_amount : $product->mrp, 0) }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
