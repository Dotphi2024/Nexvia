@extends('adminlayouts.vertical')

@section('title', 'Booking Details #' . $booking->booking_number)

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.bookings.index') }}" class="text-muted text-decoration-none small">
                    <iconify-icon icon="solar:arrow-left-linear" class="align-middle"></iconify-icon> Bookings List
                </a>
                <span class="text-muted small">/</span>
                <span class="text-dark small fw-semibold font-monospace">#{{ $booking->booking_number }}</span>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h4 class="fw-extrabold text-dark mb-0">Booking Receipt #{{ $booking->booking_number }}</h4>
                @if($booking->is_offline)
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-12 fw-bold d-inline-flex align-items-center gap-1">
                        <iconify-icon icon="solar:shop-2-bold"></iconify-icon>
                        OFFLINE SALE ({{ strtoupper(str_replace('_', ' ', $booking->offline_payment_method ?? 'COUNTER')) }})
                    </span>
                @else
                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 fs-12 fw-bold d-inline-flex align-items-center gap-1">
                        <iconify-icon icon="solar:global-outline"></iconify-icon>
                        ONLINE BOOKING
                    </span>
                @endif

                @if($booking->payment_status === 'fully_paid')
                    <span class="badge bg-success text-white px-2 py-1 fs-12">Fully Paid</span>
                @else
                    <span class="badge bg-warning text-dark px-2 py-1 fs-12">20% Paid (Balance Due)</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('booking.receipt', $booking->booking_number) }}" target="_blank" class="btn btn-outline-primary d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3">
                <iconify-icon icon="solar:printer-bold" class="fs-18"></iconify-icon>
                <span>Print / View Digital Receipt</span>
            </a>
            @if((float)$booking->balance_amount > 0)
                <button type="button" class="btn btn-success d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#recordBalanceModal">
                    <iconify-icon icon="solar:wallet-money-bold" class="fs-18"></iconify-icon>
                    <span>Record Balance Payment</span>
                </button>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
            <iconify-icon icon="solar:check-circle-bold" class="fs-18 me-1 align-middle text-success"></iconify-icon>
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show rounded-3 mb-4" role="alert">
            <iconify-icon icon="solar:info-circle-bold" class="fs-18 me-1 align-middle"></iconify-icon>
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Main Details Left Column -->
        <div class="col-lg-8">

            <!-- Offline Purchase Specific Details Card (if offline) -->
            @if($booking->is_offline)
                <div class="card border-0 rounded-4 shadow-sm mb-4 border-start border-success border-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-success mb-0 d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:shop-2-bold" class="fs-20"></iconify-icon>
                                Offline Purchase Entry Details
                            </h5>
                            <span class="badge bg-success-subtle text-success micro font-monospace">Counter Record</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-3 col-6">
                                <span class="text-muted micro d-block text-uppercase fw-bold">Channel</span>
                                <strong class="text-dark fs-13">{{ ucfirst(str_replace('_', ' ', $booking->purchase_channel ?? 'Offline Store')) }}</strong>
                            </div>
                            <div class="col-md-3 col-6">
                                <span class="text-muted micro d-block text-uppercase fw-bold">Payment Method</span>
                                <span class="badge bg-light text-dark border fs-12 fw-semibold">{{ ucfirst(str_replace('_', ' ', $booking->offline_payment_method ?? 'Cash')) }}</span>
                            </div>
                            <div class="col-md-3 col-6">
                                <span class="text-muted micro d-block text-uppercase fw-bold">Reference / UTR</span>
                                <strong class="font-monospace text-primary fs-13">{{ $booking->offline_payment_ref ?: 'None' }}</strong>
                            </div>
                            <div class="col-md-3 col-6">
                                <span class="text-muted micro d-block text-uppercase fw-bold">Entry Date</span>
                                <strong class="text-dark fs-13">{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M, Y') }}</strong>
                            </div>
                            @if($booking->offline_notes)
                                <div class="col-12 mt-2 pt-2 border-top">
                                    <span class="text-muted micro d-block fw-bold text-uppercase">Counter Remarks / Notes:</span>
                                    <p class="text-secondary small mb-0 mt-1 fst-italic">{{ $booking->offline_notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Booking Breakdown Information -->
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="card-title mb-0 fw-bold text-dark">Order & Product Breakdown</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0 align-middle">
                        <tbody>
                            <tr>
                                <th class="w-30 bg-light">Product Name</th>
                                <td class="fw-bold fs-15 text-dark">
                                    {{ $booking->product_name }}
                                    @if($booking->product)
                                        <a href="{{ route('admin.dls_farm_equipments.products.edit', $booking->product->id) }}" class="micro text-primary ms-2 text-decoration-none">
                                            (View Catalog Item)
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Model Code & Variant</th>
                                <td>
                                    <span class="font-monospace fw-bold">{{ $booking->model_code ?: 'Standard' }}</span>
                                    @if($booking->selected_color)
                                        <span class="badge bg-light text-dark ms-2 border">Color: {{ $booking->selected_color }}</span>
                                    @endif
                                    @if($booking->quantity > 1)
                                        <span class="badge bg-light text-dark ms-2 border">Quantity: {{ $booking->quantity }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Total MRP / Sale Amount</th>
                                <td class="fw-bold fs-15 text-dark">₹{{ number_format($booking->mrp, 2) }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Amount Paid</th>
                                <td class="text-success fw-extrabold fs-15">₹{{ number_format($booking->booking_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Remaining 60-Day Balance</th>
                                <td>
                                    @if((float)$booking->balance_amount > 0)
                                        <span class="text-danger fw-extrabold fs-15">₹{{ number_format($booking->balance_amount, 2) }}</span>
                                        <span class="badge bg-warning text-dark ms-2">Due in {{ $booking->days_remaining }} days</span>
                                    @else
                                        <span class="text-success fw-bold fs-15">₹0.00 (Fully Settled)</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Booking Date</th>
                                <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M, Y') }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">60-Day Balance Due Date</th>
                                <td class="fw-bold text-primary">
                                    {{ \Carbon\Carbon::parse($booking->balance_due_date)->format('d M, Y') }}
                                    @if($booking->payment_status !== 'fully_paid')
                                        <span class="text-muted micro ms-1">({{ $booking->days_remaining }} days remaining)</span>
                                    @endif
                                </td>
                            </tr>
                            @if($booking->payment_receipt)
                                <tr>
                                    <th class="bg-light">Attached Payment Proof</th>
                                    <td>
                                        <a href="{{ asset($booking->payment_receipt) }}" target="_blank" class="btn btn-sm btn-outline-primary fw-semibold">
                                            <iconify-icon icon="solar:document-text-bold" class="me-1 align-middle"></iconify-icon> View Receipt / Document File
                                        </a>
                                        @if(preg_match('/\.(jpg|jpeg|png|webp)$/i', $booking->payment_receipt))
                                            <div class="mt-2">
                                                <a href="{{ asset($booking->payment_receipt) }}" target="_blank">
                                                    <img src="{{ asset($booking->payment_receipt) }}" alt="Receipt" class="img-thumbnail rounded" style="max-height: 120px;">
                                                </a>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Customer & Delivery Address Card -->
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-dark">Customer Profile & Delivery Address</h5>
                    @if($booking->user_id)
                        <a href="{{ route('admin.customers.show', $booking->user_id) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                            <iconify-icon icon="solar:user-bold" class="me-1 align-middle"></iconify-icon> Open Customer Profile
                        </a>
                    @endif
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <span class="text-muted micro d-block text-uppercase fw-bold">Customer Name</span>
                            <strong class="text-dark fs-14">{{ $booking->customer_name }}</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted micro d-block text-uppercase fw-bold">Mobile Phone</span>
                            <span class="text-dark font-monospace fs-14">{{ $booking->customer_phone }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted micro d-block text-uppercase fw-bold">Email Address</span>
                            <span class="text-dark fs-14">{{ $booking->customer_email ?: 'None provided' }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted micro d-block text-uppercase fw-bold">Location</span>
                            <span class="text-dark fs-14">{{ $booking->city }}, {{ $booking->state }} - {{ $booking->pincode }}</span>
                        </div>
                        <div class="col-12 border-top pt-2">
                            <span class="text-muted micro d-block text-uppercase fw-bold">Shipping / Billing Address</span>
                            <p class="text-dark mb-0 small mt-1">{{ $booking->shipping_address }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delivery & DSP Tracking Card -->
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:delivery-bold" class="text-primary fs-20"></iconify-icon>
                        <h5 class="card-title mb-0 fw-bold text-dark">Authorized DSP & Delivery Tracking</h5>
                    </div>
                    @if($booking->delivery)
                        <span class="badge bg-primary-subtle text-primary font-monospace fs-12 fw-bold">
                            {{ $booking->delivery->tracking_number }}
                        </span>
                    @endif
                </div>
                <div class="card-body p-4">
                    @if($booking->delivery)
                        <div class="row g-3">
                            <div class="col-md-6">
                                <span class="text-muted micro d-block text-uppercase fw-bold">Tracking Number</span>
                                <span class="font-monospace fw-bold text-primary fs-14">{{ $booking->delivery->tracking_number }}</span>
                                <a href="{{ url('/deliveries/' . $booking->delivery->tracking_number) }}" target="_blank" class="d-block micro text-decoration-none mt-1">
                                    <iconify-icon icon="solar:link-circle-bold" class="align-middle"></iconify-icon> Customer Live Tracking Page
                                </a>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted micro d-block text-uppercase fw-bold">Current Delivery Stage</span>
                                <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1 fs-13 text-capitalize mt-1">
                                    {{ str_replace('_', ' ', $booking->delivery->stage) }}
                                </span>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted micro d-block text-uppercase fw-bold">Assigned DSP Partner</span>
                                @if($booking->delivery->dsp)
                                    <strong class="text-dark d-block fs-14">{{ $booking->delivery->dsp->business_name ?? $booking->delivery->dsp->applicant_name }}</strong>
                                    <span class="text-muted micro">{{ $booking->delivery->dsp->applicant_name }} ({{ $booking->delivery->dsp->mobile }})</span>
                                @else
                                    <span class="text-muted small">Auto-assigned / Central Warehouse</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted micro d-block text-uppercase fw-bold">Delivery Timestamp</span>
                                <span class="text-dark small">
                                    {{ $booking->delivery->delivered_at ? \Carbon\Carbon::parse($booking->delivery->delivered_at)->format('d M, Y H:i') : 'In Pipeline' }}
                                </span>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0 small">No delivery record attached yet.</p>
                    @endif
                </div>
            </div>

            <!-- Balance Payments & Ledger Audit Card -->
            @php
                $adminHistory = is_array($booking->balance_payments_history) ? $booking->balance_payments_history : [];
            @endphp
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-dark">Payment Transactions & Proofs Ledger</h5>
                    <span class="badge bg-primary rounded-pill">{{ count($adminHistory) }} Entry(ies)</span>
                </div>
                <div class="card-body p-0">
                    @if(!empty($adminHistory))
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Mode / Channel</th>
                                        <th>Amount Paid</th>
                                        <th>Reference / UTR</th>
                                        <th>Proof File</th>
                                        <th>Paid Date</th>
                                        <th class="pe-4">Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($adminHistory as $i => $txn)
                                        <tr>
                                            <td class="ps-4 small text-muted">{{ $i + 1 }}</td>
                                            <td>
                                                <span class="badge bg-light text-dark border fs-12 fw-semibold">
                                                    {{ strtoupper(str_replace('_', ' ', $txn['payment_method'] ?? ($txn['mode'] ?? 'Payment'))) }}
                                                </span>
                                            </td>
                                            <td class="fw-bold text-success fs-14">₹{{ number_format($txn['amount'] ?? 0, 2) }}</td>
                                            <td class="font-monospace small text-primary">{{ $txn['reference_no'] ?? ($txn['payment_id'] ?? '-') }}</td>
                                            <td>
                                                @if(!empty($txn['receipt_file']))
                                                    <a href="{{ asset($txn['receipt_file']) }}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2 small">
                                                        <iconify-icon icon="solar:paperclip-bold" class="align-middle"></iconify-icon> View
                                                    </a>
                                                @else
                                                    <span class="text-muted micro">—</span>
                                                @endif
                                            </td>
                                            <td class="small">{{ \Carbon\Carbon::parse($txn['paid_at'] ?? now())->format('d M, Y H:i') }}</td>
                                            <td class="pe-4 small text-muted">{{ $txn['notes'] ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted p-4 mb-0 small">No additional balance payments recorded yet.</p>
                    @endif
                </div>
            </div>

            <!-- Transfer History Audit Trail -->
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="card-title mb-0 fw-bold text-dark">Booking Transfer Audit Trail</h5>
                </div>
                <div class="card-body p-0">
                    @if($booking->transfers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Transferred From</th>
                                        <th>Transferred To</th>
                                        <th>Phone</th>
                                        <th>Date</th>
                                        <th class="pe-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($booking->transfers as $tr)
                                        <tr>
                                            <td class="ps-4 fw-semibold">{{ $tr->fromUser->name ?? 'Original Customer' }}</td>
                                            <td>{{ $tr->to_name }}</td>
                                            <td>{{ $tr->to_phone }}</td>
                                            <td>{{ $tr->transferred_at ? \Carbon\Carbon::parse($tr->transferred_at)->format('d M, Y H:i') : 'Pending' }}</td>
                                            <td class="pe-4"><span class="badge bg-success">{{ strtoupper($tr->status) }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted p-4 mb-0 small">No transfers recorded for this booking receipt.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Sidebar Controls -->
        <div class="col-lg-4">
            <!-- Update Status Card -->
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="card-title mb-0 fw-bold text-dark">Manage Booking Status</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.bookings.update.status', $booking->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Payment Status</label>
                            <select name="payment_status" class="form-select bg-light">
                                <option value="paid" {{ $booking->payment_status === 'paid' ? 'selected' : '' }}>20% Paid (Balance Due)</option>
                                <option value="fully_paid" {{ $booking->payment_status === 'fully_paid' ? 'selected' : '' }}>Fully Paid</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Booking / Delivery Status</label>
                            <select name="booking_status" class="form-select bg-light">
                                <option value="booked" {{ $booking->booking_status === 'booked' ? 'selected' : '' }}>Booked (Ready for Dispatch)</option>
                                <option value="balance_paid" {{ $booking->booking_status === 'balance_paid' ? 'selected' : '' }}>Balance Paid</option>
                                <option value="completed" {{ $booking->booking_status === 'completed' ? 'selected' : '' }}>Completed / Delivered</option>
                                <option value="cancelled" {{ $booking->booking_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-bold shadow-sm">
                            Save Status Changes
                        </button>
                    </form>
                </div>
            </div>

            <!-- Quick Action: Record Offline Balance Payment -->
            @if((float)$booking->balance_amount > 0)
                <div class="card border-0 rounded-4 shadow-sm mb-4 border-2 border-success">
                    <div class="card-body p-4 text-center">
                        <iconify-icon icon="solar:wallet-money-bold" class="text-success fs-1 mb-2"></iconify-icon>
                        <h5 class="fw-bold text-dark mb-1">Balance Due: ₹{{ number_format($booking->balance_amount, 2) }}</h5>
                        <p class="text-muted micro mb-3">Customer has {{ $booking->days_remaining }} days left until 60-day deadline.</p>
                        <button type="button" class="btn btn-success w-100 py-2 rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#recordBalanceModal">
                            Record Balance Payment
                        </button>
                    </div>
                </div>
            @endif

            <!-- Official Payment QR Code Reference -->
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fs-14 fw-bold">Official Remittance QR Code</h5>
                    <span class="badge bg-primary-subtle text-primary micro">IDFC Bank</span>
                </div>
                <div class="card-body text-center p-4">
                    <img src="{{ asset('images/dls_payment_qr.png') }}" alt="UPI QR Code" class="img-fluid rounded border p-2 bg-white mb-2 shadow-sm" style="max-height: 180px;">
                    <div class="small fw-bold text-dark">DLS AGRO INFRAVENTURE PVT LTD</div>
                    <div class="font-monospace micro text-danger fw-bold mt-1">dlsagroin.09@idfcbank</div>
                    <span class="micro text-muted d-block mt-2">Display this QR to customer for direct UPI or bank transfer settlement.</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Record Balance Payment -->
@if((float)$booking->balance_amount > 0)
<div class="modal fade" id="recordBalanceModal" tabindex="-1" aria-labelledby="recordBalanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark" id="recordBalanceModalLabel">
                    <iconify-icon icon="solar:wallet-money-bold" class="text-success me-1 align-middle"></iconify-icon>
                    Record Offline Balance Payment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.bookings.record_balance', $booking->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-light border rounded-3 p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Current Balance Due:</span>
                            <strong class="text-danger fs-15">₹{{ number_format($booking->balance_amount, 2) }}</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Payment Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control fw-bold text-success" value="{{ $booking->balance_amount }}" max="{{ $booking->balance_amount }}" required>
                        <span class="micro text-muted">Enter full remaining balance or partial amount</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">💵 Cash Received</option>
                            <option value="bank_transfer">🏦 Bank Transfer / NEFT / RTGS</option>
                            <option value="upi">📱 UPI / QR Code</option>
                            <option value="cheque">📑 Cheque / DD</option>
                            <option value="pos_card">💳 POS Card Swipe</option>
                            <option value="other">Other Offline Mode</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">UTR / Reference / Cheque Number</label>
                        <input type="text" name="reference_no" class="form-control font-monospace" placeholder="e.g. UTR-982310 or CHQ-0012">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="paid_at" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Attach Receipt / Slip <span class="micro text-muted">(Optional)</span></label>
                        <input type="file" name="receipt_file" class="form-control">
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold text-dark">Notes / Counter Remarks</label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g. Received at main showroom cash counter">
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold px-4">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
