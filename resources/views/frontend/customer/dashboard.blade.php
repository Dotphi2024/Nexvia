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
                                @if($booking->payment_status === 'fully_paid')
                                    <span class="badge bg-success text-white px-3 py-2">FULLY PAID</span>
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
                            @if($booking->payment_status !== 'fully_paid')
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="fw-bold text-dark">60-Day Balance Payment Window</span>
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

                            <div class="d-flex justify-content-between align-items-center pt-2 mt-auto border-top">
                                <a href="{{ route('booking.receipt', $booking->booking_number) }}" class="btn btn-outline-primary btn-sm fw-semibold">
                                    View Digital Receipt
                                </a>

                                @if($booking->payment_status !== 'fully_paid')
                                    <form action="{{ route('booking.pay.balance', $booking->booking_number) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm fw-bold">
                                            Pay Balance (₹{{ number_format($booking->balance_amount, 0) }})
                                        </button>
                                    </form>
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
