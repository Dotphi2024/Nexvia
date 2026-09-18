@extends('dsp.layouts.portal')

@section('title', 'DSP Dashboard')

@section('content')
<!-- Top Hero Partner Banner -->
<div class="card-dsp p-4 mb-4" style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); color: #ffffff;">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success text-white px-3 py-1 fw-bold">AUTHORISED PARTNER</span>
                <span class="badge bg-white bg-opacity-20 text-white font-monospace">#{{ $dsp->application_number }}</span>
            </div>
            <h3 class="fw-extrabold text-white mb-1">{{ $dsp->business_name ?: $dsp->applicant_name }}</h3>
            <p class="text-white-50 mb-0 small">
                <iconify-icon icon="solar:map-point-bold" class="align-middle me-1 text-warning"></iconify-icon>
                Territory: <strong>{{ $dsp->preferred_territory_area ?: 'Regional Hub' }}</strong>
                ({{ $dsp->district ?: 'District' }}, {{ $dsp->state ?: 'State' }})
                • Servicing PIN Codes: <code>{{ $dsp->pincodes ?: 'All Territory' }}</code>
            </p>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="bg-white bg-opacity-10 border border-white border-opacity-20 p-3 rounded-4 text-center">
                <span class="micro text-white-50 d-block text-uppercase fw-bold">Redeemable Cash</span>
                <span class="fs-3 fw-extrabold text-success">₹{{ number_format($dsp->wallet_balance ?? 0, 2) }}</span>
            </div>
            <a href="{{ route('dsp.wallet') }}" class="btn btn-warning fw-bold px-4 py-3 rounded-3 shadow">
                <iconify-icon icon="solar:hand-money-bold" class="align-middle me-1 fs-5"></iconify-icon>
                Redeem Cash
            </a>
        </div>
    </div>
</div>

<!-- Stat Cards: Deliveries & 5% Commissions -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card-stat d-flex align-items-center gap-3">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <iconify-icon icon="solar:box-minimalistic-bold-duotone"></iconify-icon>
            </div>
            <div>
                <div class="micro text-muted text-uppercase fw-bold">Total Deliveries</div>
                <div class="fs-4 fw-extrabold text-dark">{{ $totalDeliveries }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card-stat d-flex align-items-center gap-3">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                <iconify-icon icon="solar:clock-circle-bold-duotone"></iconify-icon>
            </div>
            <div>
                <div class="micro text-muted text-uppercase fw-bold">Pending Delivery</div>
                <div class="fs-4 fw-extrabold text-warning">{{ $pendingDeliveries }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card-stat d-flex align-items-center gap-3">
            <div class="stat-icon bg-info bg-opacity-10 text-info">
                <iconify-icon icon="solar:delivery-bold-duotone"></iconify-icon>
            </div>
            <div>
                <div class="micro text-muted text-uppercase fw-bold">Out for Delivery</div>
                <div class="fs-4 fw-extrabold text-info">{{ $outForDeliveryCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card-stat d-flex align-items-center gap-3">
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
            </div>
            <div>
                <div class="micro text-muted text-uppercase fw-bold">Successfully Delivered</div>
                <div class="fs-4 fw-extrabold text-success">{{ $deliveredCount }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Active Deliveries in Territory -->
    <div class="col-lg-8">
        <div class="card-dsp p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <iconify-icon icon="solar:box-bold" class="text-primary align-middle me-1"></iconify-icon>
                        Recent Territory Deliveries
                    </h5>
                    <p class="micro text-muted mb-0">Orders & bookings matching your assigned PIN codes. Deliver and earn 5% cash!</p>
                </div>
                <a href="{{ route('dsp.deliveries') }}" class="btn btn-outline-primary btn-sm fw-bold">View All Deliveries</a>
            </div>

            @if($recentDeliveries->count() > 0)
                <div class="table-responsive">
                    <table class="table table-dsp align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Order / Product</th>
                                <th>Customer & PIN</th>
                                <th>5% Commission</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentDeliveries as $delivery)
                                @php
                                    $booking = $delivery->booking;
                                    $order = $delivery->order;
                                    $productPrice = (float)($booking?->mrp ?? $order?->total_amount ?? 0);
                                    $commission = round($productPrice * 0.05, 2);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark font-monospace small">#{{ $booking?->booking_number ?? $delivery->tracking_number }}</div>
                                        <div class="small text-muted">{{ Str::limit($booking?->product_name ?? 'NEXVIA Product', 24) }}</div>
                                        <span class="micro text-secondary">MRP: ₹{{ number_format($productPrice, 0) }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark small">{{ $booking?->customer_name ?? $order?->customer_name }}</div>
                                        <a href="tel:{{ $booking?->customer_phone ?? $order?->customer_phone }}" class="text-decoration-none micro text-primary d-inline-flex align-items-center gap-1">
                                            <iconify-icon icon="solar:phone-bold"></iconify-icon> {{ $booking?->customer_phone ?? $order?->customer_phone }}
                                        </a>
                                        <div class="micro text-muted font-monospace"><iconify-icon icon="solar:map-point-bold"></iconify-icon> PIN: <strong>{{ $booking?->pincode ?? $order?->pincode }}</strong></div>
                                    </td>
                                    <td>
                                        <span class="badge-commission">
                                            +₹{{ number_format($commission, 2) }}
                                        </span>
                                        <div class="micro text-muted mt-1">5% on delivery</div>
                                    </td>
                                    <td>
                                        @if($delivery->stage === 'delivered')
                                            <span class="badge bg-success-subtle text-success">DELIVERED</span>
                                        @elseif($delivery->stage === 'out_for_delivery')
                                            <span class="badge bg-info-subtle text-info">OUT FOR DELIVERY</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning">READY TO DISPATCH</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('dsp.deliveries.show', $delivery->id) }}" class="btn btn-outline-secondary btn-sm fw-bold">
                                            Manage
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="fs-1 text-muted opacity-50 mb-2"></iconify-icon>
                    <p class="mb-0">No active deliveries currently in your assigned territory.</p>
                    <span class="micro text-secondary">When customers book or order products in PIN codes ({{ $dsp->pincodes ?: 'All' }}), they will appear here automatically!</span>
                </div>
            @endif
        </div>
    </div>

    <!-- 5% Earnings & Wallet Card -->
    <div class="col-lg-4">
        <div class="card-dsp p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0">
                    <iconify-icon icon="solar:wallet-money-bold" class="text-success me-1 align-middle"></iconify-icon>
                    Cash Earnings & Payouts
                </h6>
                <a href="{{ route('dsp.wallet') }}" class="micro text-primary fw-bold text-decoration-none">Full Statement</a>
            </div>

            <div class="bg-light p-3 rounded-3 border mb-3">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Lifetime Commission Earned:</span>
                    <strong class="text-dark">₹{{ number_format($dsp->total_earned ?? 0, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Total Cash Redeemed:</span>
                    <strong class="text-danger">₹{{ number_format($dsp->total_redeemed ?? 0, 2) }}</strong>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between">
                    <span class="fw-bold text-dark small">Available Balance:</span>
                    <strong class="text-success fs-5">₹{{ number_format($dsp->wallet_balance ?? 0, 2) }}</strong>
                </div>
            </div>

            <h6 class="micro text-uppercase fw-bold text-muted mb-2">Recent Commission Credits</h6>
            @if($recentTransactions->count() > 0)
                <ul class="list-group list-group-flush small">
                    @foreach($recentTransactions as $tx)
                        <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold text-dark">{{ Str::limit($tx->description, 28) }}</div>
                                <span class="micro text-muted">{{ $tx->created_at->format('d M, H:i') }}</span>
                            </div>
                            <span class="fw-bold {{ $tx->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                {{ $tx->type === 'credit' ? '+' : '-' }}₹{{ number_format($tx->amount, 2) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="micro text-muted text-center py-3 mb-0">No wallet transactions recorded yet.</p>
            @endif

            <a href="{{ route('dsp.wallet') }}" class="btn btn-success w-100 py-2 fw-bold mt-3 shadow-sm">
                <iconify-icon icon="solar:cash-out-bold" class="me-1 align-middle"></iconify-icon>
                Withdraw to Bank / UPI
            </a>
        </div>
    </div>
</div>
@endsection
