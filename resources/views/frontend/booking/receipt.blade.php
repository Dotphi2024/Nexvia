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

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <iconify-icon icon="solar:check-circle-bold" class="me-1 align-middle fs-5"></iconify-icon>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Strict Commitment & 60-Day Status Alert -->
                    @if($booking->booking_status === 'reallocated')
                        <div class="alert alert-info border-2 d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4 p-3 rounded-4 shadow-sm" role="alert">
                            <div class="d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:wallet-money-bold" class="fs-2 text-primary"></iconify-icon>
                                <div>
                                    <strong class="d-block text-dark">Amount Reallocated to Product Credits</strong>
                                    <span class="small text-muted">Your paid amount of <strong>₹{{ number_format($booking->filled_amount, 2) }}</strong> has been transferred to your Product Credits wallet.</span>
                                </div>
                            </div>
                            <a href="{{ url('/products') }}" class="btn btn-primary btn-sm fw-bold">
                                <iconify-icon icon="solar:bag-heart-bold" class="me-1 align-middle"></iconify-icon> Browse Catalog to Buy Item
                            </a>
                        </div>
                    @elseif($booking->can_reallocate_paid_amount)
                        <div class="alert alert-warning border-2 border-warning d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 p-3 rounded-4 shadow-sm" role="alert">
                            <div class="d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:clock-circle-bold" class="fs-2 text-warning"></iconify-icon>
                                <div>
                                    <strong class="d-block text-dark">60-Day Settlement Window Expired</strong>
                                    <span class="small text-muted">The 60-day period for remaining balance payment has ended. You can convert your total paid amount of <strong>₹{{ number_format($booking->filled_amount, 2) }}</strong> into Product Credit to purchase another item.</span>
                                </div>
                            </div>
                            <form action="{{ route('booking.reallocate', $booking->booking_number) }}" method="POST" onsubmit="return confirm('Transfer your paid amount of ₹{{ number_format($booking->filled_amount, 2) }} into Product Credit to buy another item?');">
                                @csrf
                                <button type="submit" class="btn btn-warning fw-bold text-dark shadow-sm">
                                    <iconify-icon icon="solar:cart-large-minimalistic-bold" class="me-1 align-middle fs-5"></iconify-icon>
                                    Buy Another Item with Paid Amount (₹{{ number_format($booking->filled_amount, 2) }})
                                </button>
                            </form>
                        </div>
                    @endif

                    <div class="p-2 px-3 bg-light rounded-3 border d-flex align-items-center justify-content-between mb-4 small">
                        <span class="text-muted">
                            <iconify-icon icon="solar:shield-check-bold" class="text-success me-1 align-middle"></iconify-icon>
                            <strong>Non-Cancellable Booking:</strong> Once the 20% deposit is paid, bookings are non-cancellable. Balance payable within 60 days via flexible amounts or EMI.
                        </span>
                    </div>

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
                                @php
                                    $paymentsHistory = is_array($booking->balance_payments_history) ? $booking->balance_payments_history : [];
                                    $totalPaidTowardsBalance = collect($paymentsHistory)->sum('amount');
                                @endphp
                                @if($totalPaidTowardsBalance > 0)
                                    <tr class="table-info">
                                        <td>
                                            <strong>Balance Paid So Far</strong>
                                            @if($booking->balance_payment_mode === 'emi')
                                                <span class="badge bg-primary text-white ms-2">EMI Active: {{ $booking->emi_installments_paid }} of {{ $booking->emi_tenure_months }} Installments Paid</span>
                                            @elseif($booking->balance_payment_mode === 'flexible')
                                                <span class="badge bg-secondary text-white ms-2">Flexible Payments</span>
                                            @endif
                                            <span class="d-block small text-muted">{{ count($paymentsHistory) }} payment transaction(s) recorded</span>
                                        </td>
                                        <td class="text-end fw-bold text-primary fs-5">₹{{ number_format($totalPaidTowardsBalance, 2) }}</td>
                                    </tr>
                                @endif
                                <tr class="{{ $booking->payment_status === 'fully_paid' ? 'table-light' : 'table-warning' }}">
                                    <td>
                                        <strong>80% Balance Payment Due</strong>
                                        @if($booking->payment_status === 'fully_paid')
                                            <span class="d-block small text-success">All balance settled in full. Product ready for delivery!</span>
                                        @else
                                            <span class="d-block small text-danger">Due Date: {{ \Carbon\Carbon::parse($booking->balance_due_date)->format('d M, Y') }} (Pay in Full, EMI, or Flexible)</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-dark fs-5">
                                        @if($booking->payment_status === 'fully_paid')
                                            <span class="badge bg-success fs-6">FULLY PAID</span>
                                        @else
                                            ₹{{ number_format($booking->balance_amount, 2) }}
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if(!empty($paymentsHistory))
                        <!-- Balance Payment & EMI History Table -->
                        <div class="mb-4">
                            <h6 class="text-uppercase text-muted fw-bold small tracking-wider mb-2">Balance Payment Transactions</h6>
                            <div class="table-responsive rounded-3 border">
                                <table class="table table-sm table-hover mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Payment Mode</th>
                                            <th>Reference / UTR</th>
                                            <th>Date & Time</th>
                                            <th class="text-end">Amount Paid</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($paymentsHistory as $idx => $txn)
                                            <tr>
                                                <td class="fw-semibold">{{ $idx + 1 }}</td>
                                                <td>
                                                    @if(($txn['mode'] ?? '') === 'emi')
                                                        <span class="badge bg-info-subtle text-info">EMI Installment {{ $txn['installment_no'] ?? ($idx + 1) }} / {{ $txn['tenure_months'] ?? ($booking->emi_tenure_months ?? '-') }}</span>
                                                    @elseif(($txn['mode'] ?? '') === 'flexible')
                                                        <span class="badge bg-warning-subtle text-warning">Flexible Partial</span>
                                                    @else
                                                        <span class="badge bg-success-subtle text-success">Full Balance</span>
                                                    @endif
                                                </td>
                                                <td class="font-monospace small text-muted">
                                                    {{ $txn['reference_no'] ?? ($txn['payment_id'] ?? '-') }}
                                                    @if(!empty($txn['receipt_file']))
                                                        <a href="{{ asset($txn['receipt_file']) }}" target="_blank" class="badge bg-primary-subtle text-primary text-decoration-none ms-1">
                                                            <iconify-icon icon="solar:paperclip-bold" class="align-middle"></iconify-icon> Proof
                                                        </a>
                                                    @endif
                                                </td>
                                                <td class="small">{{ \Carbon\Carbon::parse($txn['paid_at'] ?? now())->format('d M, Y h:i A') }}</td>
                                                <td class="text-end fw-bold text-dark">₹{{ number_format($txn['amount'] ?? 0, 2) }}</td>
                                                <td class="text-center"><span class="badge bg-success">Received</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    @if($booking->payment_receipt)
                        <!-- Customer Uploaded Payment Receipt Banner -->
                        <div class="mb-4 p-3 bg-light rounded-3 border d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:document-text-bold" class="fs-4 text-primary"></iconify-icon>
                                <div>
                                    <strong class="text-dark small d-block">Payment Receipt / Proof Attached</strong>
                                    <span class="micro text-muted">Payment confirmation screenshot or slip has been uploaded.</span>
                                </div>
                            </div>
                            <a href="{{ asset($booking->payment_receipt) }}" target="_blank" class="btn btn-sm btn-outline-primary fw-semibold">
                                <iconify-icon icon="solar:eye-bold" class="me-1 align-middle"></iconify-icon> View Receipt Proof
                            </a>
                        </div>
                    @endif

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
                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                        <a href="{{ \Illuminate\Support\Facades\Route::has('customer.dashboard') ? route('customer.dashboard') : url('/') }}" class="btn btn-outline-secondary">
                            ← Back to Dashboard
                        </a>

                        <div class="d-flex flex-wrap gap-2">
                            @if($booking->can_reallocate_paid_amount)
                                <form action="{{ route('booking.reallocate', $booking->booking_number) }}" method="POST" onsubmit="return confirm('Transfer your paid amount of ₹{{ number_format($booking->filled_amount, 2) }} into Product Credit to buy another item?');" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning fw-bold text-dark shadow-sm">
                                        <iconify-icon icon="solar:cart-large-minimalistic-bold" class="me-1 align-middle fs-5"></iconify-icon>
                                        Buy Another Item with Paid Amount (₹{{ number_format($booking->filled_amount, 2) }})
                                    </button>
                                </form>
                            @endif

                            @if($booking->booking_status === 'reallocated')
                                <a href="{{ url('/products') }}" class="btn btn-primary fw-bold shadow-sm">
                                    <iconify-icon icon="solar:shop-2-bold" class="me-1 align-middle fs-5"></iconify-icon>
                                    Buy Another Item (Catalog)
                                </a>
                            @endif

                            @if($booking->payment_status !== 'fully_paid' && $booking->booking_status !== 'reallocated')
                                <button type="button" class="btn btn-success fw-bold px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#payBalanceModal">
                                    <iconify-icon icon="solar:card-2-bold" class="me-1 align-middle fs-5"></iconify-icon>
                                    Pay 80% Balance (EMI / Full / Flexible)
                                </button>
                            @endif

                            @if($booking->user_id === Auth::guard('web')->id() && $booking->payment_status !== 'fully_paid' && $booking->booking_status !== 'reallocated')
                                <button type="button" class="btn btn-outline-warning fw-bold px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#transferModal">
                                    <iconify-icon icon="solar:transfer-horizontal-bold" class="me-1 align-middle"></iconify-icon>
                                    Transfer Receipt
                                </button>
                            @endif
                        </div>
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

<!-- PAY 80% BALANCE SETTLEMENT MODAL (FULL / EMI / FLEXIBLE) -->
@if($booking->payment_status !== 'fully_paid')
<div class="modal fade" id="payBalanceModal" tabindex="-1" aria-labelledby="payBalanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-primary text-white">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="payBalanceModalLabel">
                        <iconify-icon icon="solar:wallet-money-bold" class="me-1 align-middle"></iconify-icon>
                        Settle Remaining 80% Balance
                    </h5>
                    <span class="small text-white-50">Booking #{{ $booking->booking_number }} • Total Remaining: ₹{{ number_format($booking->balance_amount, 2) }}</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Payment Mode Nav Tabs -->
                <ul class="nav nav-pills nav-fill mb-4 bg-light p-1 rounded-3" id="balancePayTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold py-2" id="tab-full-btn" data-bs-toggle="pill" data-bs-target="#tab-full" type="button" role="tab">
                            <iconify-icon icon="solar:card-check-bold" class="me-1 align-middle"></iconify-icon>
                            1. Full Amount
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2" id="tab-emi-btn" data-bs-toggle="pill" data-bs-target="#tab-emi" type="button" role="tab">
                            <iconify-icon icon="solar:calendar-date-bold" class="me-1 align-middle"></iconify-icon>
                            2. Pay in Easy EMI
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2" id="tab-flexible-btn" data-bs-toggle="pill" data-bs-target="#tab-flexible" type="button" role="tab">
                            <iconify-icon icon="solar:slider-minimalistic-horizontal-bold" class="me-1 align-middle"></iconify-icon>
                            3. Flexible (Any Amount)
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="balancePayTabsContent">
                    <!-- TAB 1: FULL AMOUNT -->
                    <div class="tab-pane fade show active" id="tab-full" role="tabpanel">
                        <div class="alert alert-primary d-flex align-items-center gap-3">
                            <iconify-icon icon="solar:shield-check-bold" style="font-size: 32px;"></iconify-icon>
                            <div>
                                <strong class="d-block">Pay Complete Remaining Balance</strong>
                                <span class="small">Clear the entire remaining ₹{{ number_format($booking->balance_amount, 2) }} in one easy payment to immediately complete your order.</span>
                            </div>
                        </div>

                        <form action="{{ route('booking.pay.balance', $booking->booking_number) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="payment_mode" value="full">

                            <div class="row g-3 align-items-center mb-3">
                                <div class="col-md-6 text-center border-end">
                                    <span class="text-muted small d-block">Amount Due Today</span>
                                    <h2 class="fw-bold text-success my-1">₹{{ number_format($booking->balance_amount, 2) }}</h2>
                                    <span class="badge bg-success-subtle text-success micro">100% Final Settlement</span>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold text-muted">Transaction ID / UTR (Optional)</label>
                                        <input type="text" name="reference_no" class="form-control form-control-sm" placeholder="e.g. UPI UTR 425123456789">
                                    </div>
                                    <div>
                                        <label class="form-label small fw-semibold text-muted d-flex justify-content-between mb-1">
                                            <span>Upload Receipt Proof</span>
                                            <span class="badge bg-light text-muted border micro">Optional</span>
                                        </label>
                                        <input type="file" name="payment_receipt" class="form-control form-control-sm" accept="image/*,.pdf">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm">
                                Confirm & Pay ₹{{ number_format($booking->balance_amount, 2) }} Full Balance
                            </button>
                        </form>
                    </div>

                    <!-- TAB 2: EMI INSTALLMENT -->
                    <div class="tab-pane fade" id="tab-emi" role="tabpanel">
                        <div class="alert alert-info d-flex align-items-center gap-3 mb-3">
                            <iconify-icon icon="solar:calendar-bold" style="font-size: 32px;"></iconify-icon>
                            <div>
                                <strong class="d-block">Flexible Monthly EMI Plans (3 to 12 Months)</strong>
                                <span class="small">Split your remaining balance into equal monthly payments without stress.</span>
                            </div>
                        </div>

                        <form action="{{ route('booking.pay.balance', $booking->booking_number) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="payment_mode" value="emi">

                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">Select EMI Tenure:</label>
                                <div class="row g-2">
                                    @php
                                        $currentBal = (float) $booking->balance_amount;
                                        $selectedTenure = $booking->emi_tenure_months ?: 6;
                                    @endphp
                                    @foreach([3, 6, 9, 12] as $months)
                                        @php
                                            $monthly = round($currentBal / $months, 2);
                                        @endphp
                                        <div class="col-6 col-sm-3">
                                            <input type="radio" class="btn-check emi-radio" name="emi_tenure" id="emi_tenure_{{ $months }}" value="{{ $months }}" {{ $selectedTenure == $months ? 'checked' : '' }} data-monthly="{{ $monthly }}">
                                            <label class="btn btn-outline-primary w-100 p-2 text-center h-100 rounded-3" for="emi_tenure_{{ $months }}">
                                                <strong class="d-block">{{ $months }} Months</strong>
                                                <span class="fs-6 fw-bold text-dark d-block mt-1">₹{{ number_format($monthly, 0) }}</span>
                                                <span class="micro text-muted">/ month</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="p-3 bg-light rounded-3 border mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="small text-muted d-block">Installment to Pay Now:</span>
                                        <h3 class="fw-bold text-primary mb-0" id="emiMonthlyDisplay">
                                            ₹{{ number_format(round($currentBal / $selectedTenure, 2), 2) }}
                                        </h3>
                                    </div>
                                    <div class="text-end">
                                        @if($booking->emi_installments_paid > 0)
                                            <span class="badge bg-warning text-dark fs-6">Installment {{ $booking->emi_installments_paid + 1 }} of {{ $selectedTenure }}</span>
                                        @else
                                            <span class="badge bg-info text-dark fs-6">Installment 1 of <span id="emiTenureCount">{{ $selectedTenure }}</span></span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-muted">Transaction ID / UTR (Optional)</label>
                                    <input type="text" name="reference_no" class="form-control form-control-sm" placeholder="e.g. UPI UTR 425123456789">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-muted d-flex justify-content-between mb-1">
                                        <span>Payment Proof</span>
                                        <span class="badge bg-light text-muted border micro">Optional</span>
                                    </label>
                                    <input type="file" name="payment_receipt" class="form-control form-control-sm" accept="image/*,.pdf">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                                Pay Current EMI Installment
                            </button>
                        </form>
                    </div>

                    <!-- TAB 3: FLEXIBLE (ANY AMOUNT) -->
                    <div class="tab-pane fade" id="tab-flexible" role="tabpanel">
                        <div class="alert alert-warning d-flex align-items-center gap-3 mb-3">
                            <iconify-icon icon="solar:wallet-bold" style="font-size: 32px;"></iconify-icon>
                            <div>
                                <strong class="d-block">Custom Partial Payments — Pay Any Amount</strong>
                                <span class="small">Choose exactly how much you want to pay towards your balance right now (minimum ₹100).</span>
                            </div>
                        </div>

                        <form action="{{ route('booking.pay.balance', $booking->booking_number) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="payment_mode" value="flexible">

                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">Enter Amount to Pay (₹):</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text fw-bold">₹</span>
                                    <input type="number" name="custom_amount" id="customAmountInput" class="form-control fw-bold fs-4 text-dark" min="100" max="{{ (float)$booking->balance_amount }}" step="1" value="{{ min(5000, (float)$booking->balance_amount) }}" required>
                                </div>
                                <span class="micro text-muted">Max payable: ₹{{ number_format($booking->balance_amount, 2) }}</span>
                            </div>

                            <!-- Quick Preset Buttons -->
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="small text-muted align-self-center me-1">Quick Select:</span>
                                @foreach([1000, 2000, 5000, 10000, 25000] as $preset)
                                    @if($preset < (float)$booking->balance_amount)
                                        <button type="button" class="btn btn-sm btn-outline-secondary preset-btn" data-val="{{ $preset }}">₹{{ number_format($preset, 0) }}</button>
                                    @endif
                                @endforeach
                                <button type="button" class="btn btn-sm btn-outline-success preset-btn" data-val="{{ (float)$booking->balance_amount }}">Full (₹{{ number_format($booking->balance_amount, 0) }})</button>
                            </div>

                            <div class="p-3 bg-light rounded-3 border mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Estimated Remaining Balance After This Payment:</span>
                                    <strong class="fs-5 text-dark" id="estRemainingDisplay">
                                        ₹{{ number_format(max(0, (float)$booking->balance_amount - min(5000, (float)$booking->balance_amount)), 2) }}
                                    </strong>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-muted">Transaction ID / UTR (Optional)</label>
                                    <input type="text" name="reference_no" class="form-control form-control-sm" placeholder="e.g. UPI UTR 425123456789">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-muted d-flex justify-content-between mb-1">
                                        <span>Payment Proof</span>
                                        <span class="badge bg-light text-muted border micro">Optional</span>
                                    </label>
                                    <input type="file" name="payment_receipt" class="form-control form-control-sm" accept="image/*,.pdf">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-warning w-100 py-2 fw-bold text-dark shadow-sm">
                                Pay Custom Amount Now
                            </button>
                        </form>
                    </div>
                </div>

                <!-- UPI QR Scan Reference Section -->
                <div class="border-top pt-3 mt-4 text-center">
                    <button class="btn btn-sm btn-link text-decoration-none text-muted" type="button" data-bs-toggle="collapse" data-bs-target="#qrDetailsCollapse">
                        <iconify-icon icon="solar:qr-code-bold" class="me-1 align-middle"></iconify-icon>
                        Scan UPI QR Code Directly (Optional) ▼
                    </button>

                    <div class="collapse mt-3" id="qrDetailsCollapse">
                        <div class="p-3 bg-light rounded-3 border text-center">
                            <img src="{{ asset('images/dls_payment_qr.png') }}" alt="UPI QR Code" class="img-fluid rounded border bg-white p-2 shadow-sm" style="max-height: 200px;">
                            <div class="mt-2 small">
                                <div><span class="text-muted">UPI ID:</span> <strong class="text-danger font-monospace">dlsagroin.09@idfcbank</strong></div>
                                <div><span class="text-muted">Account:</span> <strong class="text-dark">DLS AGRO INFRAVENTURE PRIVATE LIMITED</strong></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // EMI Calculation
    const emiRadios = document.querySelectorAll('.emi-radio');
    const emiMonthlyDisplay = document.getElementById('emiMonthlyDisplay');
    const emiTenureCount = document.getElementById('emiTenureCount');

    emiRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.checked) {
                const monthly = parseFloat(this.dataset.monthly || 0);
                if (emiMonthlyDisplay) emiMonthlyDisplay.innerText = '₹' + monthly.toLocaleString('en-IN', { minimumFractionDigits: 2 });
                if (emiTenureCount) emiTenureCount.innerText = this.value;
            }
        });
    });

    // Flexible Calculation
    const totalBal = {{ (float) $booking->balance_amount }};
    const customInput = document.getElementById('customAmountInput');
    const estRemainingDisplay = document.getElementById('estRemainingDisplay');

    function updateEst() {
        if (!customInput || !estRemainingDisplay) return;
        const val = parseFloat(customInput.value) || 0;
        const remaining = Math.max(0, totalBal - val);
        estRemainingDisplay.innerText = '₹' + remaining.toLocaleString('en-IN', { minimumFractionDigits: 2 });
    }

    if (customInput) {
        customInput.addEventListener('input', updateEst);
    }

    document.querySelectorAll('.preset-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            if (customInput) {
                customInput.value = this.dataset.val;
                updateEst();
            }
        });
    });
});
</script>
@endif

@endsection
