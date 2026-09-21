@extends('frontend.layouts.app')

@section('title', 'Customer Dashboard – NEXVIA')

@section('content')
<div class="container py-5">

    <!-- Profile Header Card & Wallet Summary -->
    <div class="card card-nexvia p-4 mb-4 bg-gradient text-dark border-0 shadow-sm" style="background: linear-gradient(135deg, #eff6ff 0%, #e0f2fe 100%);">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-3" style="width: 60px; height: 60px;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h4 class="fw-bold mb-1 text-dark">{{ $user->name }}</h4>
                    <span class="text-muted small me-3"><iconify-icon icon="solar:phone-bold" class="align-middle"></iconify-icon> {{ $user->phone }}</span>
                    <span class="text-muted small"><iconify-icon icon="solar:letter-bold" class="align-middle"></iconify-icon> {{ $user->email }}</span>
                </div>
            </div>
            <div class="d-flex gap-3">
                <div class="bg-white p-3 rounded-4 border text-center shadow-xs">
                    <span class="small text-muted d-block">Product Credit Wallet</span>
                    <span class="fs-4 fw-extrabold text-success">₹{{ number_format($user->wallet_balance ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Active 20% Bookings & 60-Day Balance System -->
    <div class="card card-nexvia p-4 mb-4">
        <h5 class="fw-bold text-dark mb-3">
            <iconify-icon icon="solar:ticket-bold-duotone" class="text-primary fs-4 align-middle me-1"></iconify-icon>
            My Product Bookings & 60-Day Balance Timeline
        </h5>

        @if($bookings->count() > 0)
            <div class="row g-4">
                @foreach($bookings as $booking)
                    <div class="col-lg-6">
                        <div class="card p-4 rounded-4 border {{ $booking->payment_status === 'fully_paid' ? 'border-success bg-light' : 'border-primary' }} h-100 shadow-sm">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="badge bg-primary font-monospace">#{{ $booking->booking_number }}</span>
                                    <h5 class="fw-bold text-dark mb-0 mt-1">{{ $booking->product_name }}</h5>
                                </div>
                                @if($booking->booking_status === 'reallocated')
                                    <span class="badge bg-secondary text-white px-3 py-2">REALLOCATED</span>
                                @elseif($booking->payment_status === 'fully_paid')
                                    <span class="badge bg-success text-white px-3 py-2">FULLY PAID</span>
                                @elseif($booking->can_reallocate_paid_amount)
                                    <span class="badge bg-danger text-white px-3 py-2">60-DAYS EXPIRED</span>
                                @else
                                    <span class="badge bg-warning text-dark px-3 py-2">BALANCE DUE</span>
                                @endif
                            </div>

                            <!-- Price Split -->
                            <div class="row g-2 text-center bg-white p-3 rounded-3 border mb-3">
                                <div class="col-4 border-end">
                                    <span class="micro text-muted d-block">MRP</span>
                                    <strong class="text-dark small">₹{{ number_format($booking->mrp, 0) }}</strong>
                                </div>
                                <div class="col-4 border-end">
                                    <span class="micro text-success d-block">20% Paid</span>
                                    <strong class="text-success small">₹{{ number_format($booking->booking_amount, 0) }}</strong>
                                </div>
                                <div class="col-4">
                                    <span class="micro text-danger d-block">80% Balance</span>
                                    <strong class="text-danger small">₹{{ number_format($booking->balance_amount, 0) }}</strong>
                                </div>
                            </div>

                            <!-- 60-DAY COUNTDOWN TIMER & PROGRESS BAR -->
                            @if($booking->booking_status === 'reallocated')
                                <div class="alert alert-info py-2 px-3 small rounded-3 mb-3">
                                    <iconify-icon icon="solar:info-circle-bold" class="me-1 align-middle text-primary"></iconify-icon>
                                    <strong>Reallocated to Wallet:</strong> Paid amount ₹{{ number_format($booking->filled_amount, 2) }} is credited to your Product Credits wallet.
                                </div>
                            @elseif($booking->can_reallocate_paid_amount)
                                <div class="alert alert-warning py-2 px-3 small rounded-3 mb-3">
                                    <iconify-icon icon="solar:clock-circle-bold" class="me-1 align-middle text-danger"></iconify-icon>
                                    <strong>60-Day Period Concluded:</strong> You can use your filled amount of ₹{{ number_format($booking->filled_amount, 2) }} to purchase another item.
                                </div>
                            @elseif($booking->payment_status !== 'fully_paid')
                                <div class="mb-3">
                                    @if($booking->balance_payment_mode === 'emi')
                                        <div class="badge bg-primary-subtle text-primary border mb-2 d-inline-block text-wrap text-start">
                                            <iconify-icon icon="solar:calendar-bold" class="me-1 align-middle"></iconify-icon>
                                            EMI Active: {{ $booking->emi_installments_paid }} of {{ $booking->emi_tenure_months }} Paid (₹{{ number_format($booking->emi_monthly_amount, 0) }}/mo)
                                        </div>
                                    @endif
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="fw-bold text-dark">Balance Due: ₹{{ number_format($booking->balance_amount, 0) }}</span>
                                        <span class="fw-bold text-danger">{{ $booking->days_remaining }} Days Remaining</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        @php
                                            $percentUsed = min(100, max(0, ((60 - $booking->days_remaining) / 60) * 100));
                                        @endphp
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $percentUsed }}%;"></div>
                                    </div>
                                    <span class="micro text-muted mt-1 d-block">Due Date: {{ \Carbon\Carbon::parse($booking->balance_due_date)->format('d M, Y') }}</span>
                                </div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center pt-2 mt-auto border-top gap-2 flex-wrap">
                                <a href="{{ route('booking.receipt', $booking->booking_number) }}" class="btn btn-outline-primary btn-sm fw-semibold">
                                    View Digital Receipt
                                </a>

                                @if($booking->can_reallocate_paid_amount)
                                    <form action="{{ route('booking.reallocate', $booking->booking_number) }}" method="POST" onsubmit="return confirm('Transfer your paid amount of ₹{{ number_format($booking->filled_amount, 2) }} into Product Credit to purchase another item?');" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-warning btn-sm fw-bold text-dark shadow-sm">
                                            <iconify-icon icon="solar:cart-large-minimalistic-bold" class="me-1 align-middle"></iconify-icon>
                                            Buy Another Item (₹{{ number_format($booking->filled_amount, 0) }})
                                        </button>
                                    </form>
                                @elseif($booking->booking_status === 'reallocated')
                                    <a href="{{ url('/products') }}" class="btn btn-primary btn-sm fw-bold">
                                        <iconify-icon icon="solar:shop-2-bold" class="me-1 align-middle"></iconify-icon> Browse Products
                                    </a>
                                @elseif($booking->payment_status !== 'fully_paid')
                                    <button type="button" class="btn btn-success btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#payBalanceModal{{ $booking->id }}">
                                        <iconify-icon icon="solar:card-2-bold" class="me-1 align-middle"></iconify-icon> Pay Balance (EMI / Full)
                                    </button>
                                @endif

                                    <!-- Pay Balance Settlement Modal -->
                                    <div class="modal fade" id="payBalanceModal{{ $booking->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                                                <div class="modal-header bg-primary text-white">
                                                    <div>
                                                        <h6 class="modal-title fw-bold mb-0">
                                                            <iconify-icon icon="solar:wallet-money-bold" class="me-1 align-middle text-warning"></iconify-icon>
                                                            Pay Remaining 80% Balance – #{{ $booking->booking_number }}
                                                        </h6>
                                                        <span class="small text-white-50">Remaining Balance: ₹{{ number_format($booking->balance_amount, 2) }}</span>
                                                    </div>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-4 text-start">
                                                    <!-- Nav Pills -->
                                                    <ul class="nav nav-pills nav-fill mb-3 bg-light p-1 rounded-3" role="tablist">
                                                        <li class="nav-item">
                                                            <button class="nav-link active fw-bold py-2" data-bs-toggle="pill" data-bs-target="#dash-tab-full{{ $booking->id }}" type="button">
                                                                1. Full Amount
                                                            </button>
                                                        </li>
                                                        <li class="nav-item">
                                                            <button class="nav-link fw-bold py-2" data-bs-toggle="pill" data-bs-target="#dash-tab-emi{{ $booking->id }}" type="button">
                                                                2. Easy EMI
                                                            </button>
                                                        </li>
                                                        <li class="nav-item">
                                                            <button class="nav-link fw-bold py-2" data-bs-toggle="pill" data-bs-target="#dash-tab-flex{{ $booking->id }}" type="button">
                                                                3. Flexible Amount
                                                            </button>
                                                        </li>
                                                    </ul>

                                                    <div class="tab-content">
                                                        <!-- TAB 1: FULL -->
                                                        <div class="tab-pane fade show active" id="dash-tab-full{{ $booking->id }}">
                                                            <div class="text-center p-3 bg-light rounded-3 border mb-3">
                                                                <span class="text-muted small d-block">Full Balance Amount Due:</span>
                                                                <h3 class="fw-bold text-success my-1">₹{{ number_format($booking->balance_amount, 2) }}</h3>
                                                                <span class="badge bg-success-subtle text-success micro">Clear balance in full</span>
                                                            </div>
                                                            <form action="{{ route('booking.pay.balance', $booking->booking_number) }}" method="POST" enctype="multipart/form-data">
                                                                @csrf
                                                                <input type="hidden" name="payment_mode" value="full">
                                                                <div class="mb-2">
                                                                    <label class="form-label small fw-semibold text-muted">Transaction ID / UTR (Optional)</label>
                                                                    <input type="text" name="reference_no" class="form-control form-control-sm" placeholder="e.g. UPI UTR 425123456789">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-semibold text-muted d-flex justify-content-between mb-1">
                                                                        <span>Payment Proof</span>
                                                                        <span class="badge bg-light text-muted border micro">Optional</span>
                                                                    </label>
                                                                    <input type="file" name="payment_receipt" class="form-control form-control-sm" accept="image/*,.pdf">
                                                                </div>
                                                                <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm">
                                                                    Confirm Full Payment (₹{{ number_format($booking->balance_amount, 0) }})
                                                                </button>
                                                            </form>
                                                        </div>

                                                        <!-- TAB 2: EMI -->
                                                        <div class="tab-pane fade" id="dash-tab-emi{{ $booking->id }}">
                                                            @php
                                                                $cBal = (float)$booking->balance_amount;
                                                                $dTenure = $booking->emi_tenure_months ?: 6;
                                                            @endphp
                                                            <form action="{{ route('booking.pay.balance', $booking->booking_number) }}" method="POST" enctype="multipart/form-data">
                                                                @csrf
                                                                <input type="hidden" name="payment_mode" value="emi">

                                                                <label class="form-label fw-bold text-dark small">Select Tenure:</label>
                                                                <div class="row g-2 mb-3">
                                                                    @foreach([3, 6, 9, 12] as $m)
                                                                        <div class="col-6 col-sm-3">
                                                                            <input type="radio" class="btn-check" name="emi_tenure" id="dash_emi_{{ $booking->id }}_{{ $m }}" value="{{ $m }}" {{ $dTenure == $m ? 'checked' : '' }} onchange="document.getElementById('dashEmiVal{{ $booking->id }}').innerText = '₹' + Math.round({{ $cBal }} / {{ $m }}).toLocaleString('en-IN')">
                                                                            <label class="btn btn-outline-primary w-100 p-2 text-center rounded-3" for="dash_emi_{{ $booking->id }}_{{ $m }}">
                                                                                <strong>{{ $m }} Mo</strong>
                                                                                <span class="d-block micro">₹{{ number_format(round($cBal / $m, 0)) }}/m</span>
                                                                            </label>
                                                                        </div>
                                                                    @endforeach
                                                                </div>

                                                                <div class="p-3 bg-light rounded border text-center mb-3">
                                                                    <span class="small text-muted d-block">Monthly Installment:</span>
                                                                    <h4 class="fw-bold text-primary mb-0" id="dashEmiVal{{ $booking->id }}">
                                                                        ₹{{ number_format(round($cBal / $dTenure, 0)) }}
                                                                    </h4>
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

                                                        <!-- TAB 3: FLEXIBLE -->
                                                        <div class="tab-pane fade" id="dash-tab-flex{{ $booking->id }}">
                                                            <form action="{{ route('booking.pay.balance', $booking->booking_number) }}" method="POST" enctype="multipart/form-data">
                                                                @csrf
                                                                <input type="hidden" name="payment_mode" value="flexible">

                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-bold text-dark">Enter Custom Amount to Pay (₹):</label>
                                                                    <input type="number" name="custom_amount" class="form-control fw-bold fs-5" min="100" max="{{ (float)$booking->balance_amount }}" step="1" value="{{ min(5000, (float)$booking->balance_amount) }}" required>
                                                                    <span class="micro text-muted">Remaining Balance: ₹{{ number_format($booking->balance_amount, 2) }}</span>
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
                                                                    Pay Custom Amount
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>

                                                    <div class="p-3 bg-light rounded-3 border text-center mt-3">
                                                        <span class="micro text-muted d-block mb-1">Scan directly with UPI (Google Pay, PhonePe, Paytm, BHIM):</span>
                                                        <img src="{{ asset('images/dls_payment_qr.png') }}" alt="UPI QR Code" class="img-fluid rounded border bg-white p-1" style="max-height: 140px;">
                                                        <span class="d-block micro font-monospace mt-1 text-danger">UPI ID: dlsagroin.09@idfcbank</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4 text-muted">
                <p class="mb-2">You have no active bookings yet.</p>
                <a href="{{ route('products.index') }}" class="btn btn-nexvia-primary btn-sm">Explore Products & Book for 20%</a>
            </div>
        @endif
    </div>

    <!-- WALLET TRANSACTION STATEMENT HISTORY -->
    <div class="card card-nexvia p-4 mb-4">
        <h5 class="fw-bold text-dark mb-3">
            <iconify-icon icon="solar:history-bold-duotone" class="text-success fs-4 align-middle me-1"></iconify-icon>
            Product Credit Wallet Statement
        </h5>

        @if($walletTransactions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Source</th>
                            <th>Type</th>
                            <th class="text-end">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($walletTransactions as $tx)
                            <tr>
                                <td class="small text-muted">{{ $tx->created_at->format('d M, Y H:i') }}</td>
                                <td class="fw-semibold text-dark">{{ $tx->description }}</td>
                                <td><span class="badge bg-light text-dark">{{ ucfirst(str_replace('_', ' ', $tx->source)) }}</span></td>
                                <td>
                                    <span class="badge {{ $tx->type === 'credit' ? 'bg-success' : 'bg-danger' }}">
                                        {{ strtoupper($tx->type) }}
                                    </span>
                                </td>
                                <td class="text-end fw-bold {{ $tx->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                    {{ $tx->type === 'credit' ? '+' : '-' }}₹{{ number_format($tx->amount, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted small mb-0 text-center py-3">No wallet transactions recorded yet.</p>
        @endif
    </div>

    <!-- Profile & Customer Details Settings Form -->
    <div class="card card-nexvia p-4">
        <h5 class="fw-bold text-dark mb-3">Customer Profile Information</h5>
        <form action="{{ route('customer.profile.update') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Full Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">PIN Code</label>
                    <input type="text" name="pincode" class="form-control" value="{{ $user->pincode }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">City</label>
                    <input type="text" name="city" class="form-control" value="{{ $user->city }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">State</label>
                    <input type="text" name="state" class="form-control" value="{{ $user->state }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date of Birth</label>
                    <input type="date" name="dob" class="form-control" value="{{ $user->dob ? \Carbon\Carbon::parse($user->dob)->format('Y-m-d') : '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">GST Details (Optional for Business)</label>
                    <input type="text" name="gst_number" class="form-control" value="{{ $user->gst_number }}" placeholder="27AAAAA0000A1Z5">
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-nexvia-primary">Save Profile Changes</button>
                </div>
            </div>
        </form>
    </div>

</div>

@section('scripts')
<script>
function copyRefLink() {
    var copyText = document.getElementById("refLinkInput");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    document.getElementById("copyNotice").classList.remove("d-none");
    setTimeout(function() {
        document.getElementById("copyNotice").classList.add("d-none");
    }, 3000);
}
</script>
@endsection

@endsection
