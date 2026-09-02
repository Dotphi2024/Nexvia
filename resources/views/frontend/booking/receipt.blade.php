@extends('frontend.layouts.app')

@section('title', 'Booking Receipt #' . $booking->booking_number)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card card-nexvia border-2 border-primary overflow-hidden shadow-lg">
                <!-- Receipt Header -->
                <div class="bg-primary text-white p-4 text-center position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold tracking-wider fs-5">NEXVIA DIGITAL RECEIPT</span>
                        <span class="badge bg-white text-primary font-monospace fs-6">#{{ $booking->booking_number }}</span>
                    </div>
                    <p class="mb-0 text-light opacity-90 small">Smart Booking Receipt • Pay 20% Now, Balance in 60 Days</p>
                </div>

                <div class="card-body p-4 p-md-5">

                    <!-- Customer & Receipt Summary -->
                    <div class="row g-3 mb-4 p-3 bg-light rounded-4">
                        <div class="col-sm-6">
                            <span class="small text-muted d-block">Customer Name:</span>
                            <strong class="text-dark fs-5">{{ $booking->customer_name }}</strong>
                            <span class="d-block small text-secondary">{{ $booking->customer_phone }}</span>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <span class="small text-muted d-block">Transfer Status:</span>
                            @if($booking->transfer_status === 'transferred')
                                <span class="badge bg-purple text-white fs-6">Transferred Receipt</span>
                            @else
                                <span class="badge bg-success text-white fs-6">Original Receipt</span>
                            @endif
                        </div>
                    </div>

                    <!-- Product Details -->
                    <div class="mb-4">
                        <h6 class="text-uppercase text-muted fw-bold small tracking-wider mb-2">Product Specification</h6>
                        <div class="d-flex align-items-center gap-3 p-3 border rounded-3 bg-white">
                            <iconify-icon icon="{{ $booking->product->category->icon ?? 'solar:box-bold-duotone' }}" style="font-size: 50px; color: #2563eb;"></iconify-icon>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">{{ $booking->product_name }}</h5>
                                <span class="small text-muted">Model: {{ $booking->model_code ?? 'NEXVIA Standard' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Summary Table -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Description</th>
                                    <th class="text-end">Amount (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Product Total MRP</td>
                                    <td class="text-end fw-bold">₹{{ number_format($booking->mrp, 2) }}</td>
                                </tr>
                                <tr class="table-success">
                                    <td>
                                        <strong>20% Booking Amount Paid</strong>
                                        <span class="d-block small text-muted">Paid on {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M, Y') }}</span>
                                    </td>
                                    <td class="text-end fw-bold text-success fs-5">₹{{ number_format($booking->booking_amount, 2) }}</td>
                                </tr>
                                <tr class="{{ $booking->payment_status === 'fully_paid' ? 'table-light' : 'table-warning' }}">
                                    <td>
                                        <strong>80% Balance Payment Due</strong>
                                        <span class="d-block small text-danger">Due Date: {{ \Carbon\Carbon::parse($booking->balance_due_date)->format('d M, Y') }} (60 Days Window)</span>
                                    </td>
                                    <td class="text-end fw-bold text-dark fs-5">
                                        @if($booking->payment_status === 'fully_paid')
                                            <span class="badge bg-success">FULLY PAID</span>
                                        @else
                                            ₹{{ number_format($booking->balance_amount, 2) }}
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Simulated QR Code & Verification -->
                    <div class="row align-items-center g-3 p-3 border rounded-3 bg-light mb-4">
                        <div class="col-auto">
                            <div class="p-2 bg-white rounded border d-inline-block text-center">
                                <iconify-icon icon="solar:qr-code-bold" style="font-size: 72px; color: #0f172a;"></iconify-icon>
                                <span class="d-block font-monospace micro text-muted" style="font-size: 10px;">VERIFIED QR</span>
                            </div>
                        </div>
                        <div class="col">
                            <h6 class="fw-bold text-dark mb-1">Authentic NEXVIA Certificate</h6>
                            <p class="small text-muted mb-0">Hash: <span class="font-monospace text-dark">{{ $booking->qr_code_hash ?? 'NEX-VERIFIED-RECEIPT' }}</span></p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center">
                        <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-secondary">
                            ← Back to Dashboard
                        </a>

                        @if($booking->user_id === Auth::guard('web')->id() && $booking->payment_status !== 'fully_paid')
                            <button type="button" class="btn btn-warning fw-bold px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#transferModal">
                                <iconify-icon icon="solar:transfer-horizontal-bold" class="me-1 align-middle"></iconify-icon>
                                TRANSFER BOOKING RECEIPT
                            </button>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- TRANSFER BOOKING MODAL -->
@if($booking->user_id === Auth::guard('web')->id() && $booking->payment_status !== 'fully_paid')
<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold">Transfer Booking Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('booking.transfer.initiate', $booking->booking_number) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">
                        Transfer ownership of your 20% Booking Receipt #{{ $booking->booking_number }} to another customer via OTP verification.
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Recipient Full Name *</label>
                        <input type="text" name="to_name" class="form-control" required placeholder="Enter new owner's name">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Recipient Mobile Number *</label>
                        <input type="text" name="to_phone" class="form-control" required placeholder="Enter recipient mobile number">
                    </div>

                    <div class="alert alert-info small mb-0">
                        An OTP verification code will be sent to confirm recipient acceptance.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold">Send Transfer OTP</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
