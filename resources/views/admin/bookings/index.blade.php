@extends('adminlayouts.vertical')

@section('title', 'Bookings & 60-Day Balances Management')

@section('content')
<div class="container-fluid py-3">
    <!-- Page Header & Action Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-extrabold text-dark mb-1">Customer Bookings & 60-Day Balances</h4>
            <p class="text-muted small mb-0">Track online and offline bookings, 60-day balance timelines, delivery status, and customer payments</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 shadow-sm fw-semibold">
                <iconify-icon icon="solar:cart-plus-bold" class="fs-18"></iconify-icon>
                <span>+ Add Offline Purchase</span>
            </a>
            <a href="{{ route('admin.transfers.audit') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3">
                <iconify-icon icon="solar:users-group-two-rounded-outline" class="fs-18"></iconify-icon>
                <span>Transfers Audit</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
            <iconify-icon icon="solar:check-circle-bold" class="fs-18 me-1 align-middle text-success"></iconify-icon>
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-2 col-sm-4 col-6">
            <a href="{{ route('admin.bookings.index') }}" class="card border-0 rounded-4 shadow-sm text-decoration-none h-100 {{ !request()->filled('channel') && !request()->filled('payment_status') ? 'border-bottom border-primary border-3' : '' }}">
                <div class="card-body p-3">
                    <span class="text-muted micro text-uppercase fw-bold">All Bookings</span>
                    <h3 class="fw-extrabold text-dark mb-0 mt-1">{{ number_format($stats['total'] ?? 0) }}</h3>
                </div>
            </a>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <a href="{{ route('admin.bookings.index', ['channel' => 'online']) }}" class="card border-0 rounded-4 shadow-sm text-decoration-none h-100 {{ request('channel') === 'online' ? 'border-bottom border-info border-3' : '' }}">
                <div class="card-body p-3">
                    <span class="text-info micro text-uppercase fw-bold">Online Web/App</span>
                    <h3 class="fw-extrabold text-info mb-0 mt-1">{{ number_format($stats['online'] ?? 0) }}</h3>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-4 col-6">
            <a href="{{ route('admin.bookings.index', ['channel' => 'offline']) }}" class="card border-0 rounded-4 shadow-sm text-decoration-none h-100 {{ request('channel') === 'offline' ? 'border-bottom border-success border-3' : '' }}">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success micro text-uppercase fw-bold">Offline Store / Counter</span>
                        <span class="badge bg-success-subtle text-success micro rounded-pill">New Feature</span>
                    </div>
                    <h3 class="fw-extrabold text-success mb-0 mt-1">{{ number_format($stats['offline'] ?? 0) }}</h3>
                </div>
            </a>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <a href="{{ route('admin.bookings.index', ['payment_status' => 'fully_paid']) }}" class="card border-0 rounded-4 shadow-sm text-decoration-none h-100 {{ request('payment_status') === 'fully_paid' ? 'border-bottom border-success border-3' : '' }}">
                <div class="card-body p-3">
                    <span class="text-success micro text-uppercase fw-bold">Fully Paid</span>
                    <h3 class="fw-extrabold text-success mb-0 mt-1">{{ number_format($stats['fully_paid'] ?? 0) }}</h3>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-4 col-12">
            <a href="{{ route('admin.bookings.index', ['payment_status' => 'paid']) }}" class="card border-0 rounded-4 shadow-sm text-decoration-none h-100 {{ request('payment_status') === 'paid' ? 'border-bottom border-warning border-3' : '' }}">
                <div class="card-body p-3">
                    <span class="text-warning micro text-uppercase fw-bold">60-Day Balance Due</span>
                    <h3 class="fw-extrabold text-warning mb-0 mt-1">{{ number_format($stats['balance_due'] ?? 0) }}</h3>
                </div>
            </a>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="card border-0 rounded-4 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.bookings.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                        </span>
                        <input type="text" name="search" class="form-control bg-light border-start-0 ps-0" placeholder="Search Receipt #, Customer, Phone, Product..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <select name="channel" class="form-select bg-light">
                        <option value="">All Channels (Online + Offline)</option>
                        <option value="online" {{ request('channel') === 'online' ? 'selected' : '' }}>Online Orders Only</option>
                        <option value="offline" {{ request('channel') === 'offline' ? 'selected' : '' }}>Offline Purchases Only</option>
                    </select>
                </div>

                <div class="col-md-3 col-sm-6">
                    <select name="payment_status" class="form-select bg-light">
                        <option value="">All Payment Statuses</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>20% Paid (Balance Due)</option>
                        <option value="fully_paid" {{ request('payment_status') === 'fully_paid' ? 'selected' : '' }}>Fully Paid</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    @if(request()->hasAny(['search', 'channel', 'payment_status', 'booking_status']))
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-light" title="Reset Filters">
                            <iconify-icon icon="solar:restart-bold"></iconify-icon>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="card border-0 rounded-4 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 50px;">#</th>
                            <th>Receipt & Channel</th>
                            <th>Customer</th>
                            <th>Product Name</th>
                            <th>Paid Amount</th>
                            <th>Balance Due</th>
                            <th>60-Day Due Date</th>
                            <th>Payment Status</th>
                            <th>Delivery Stage</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            <tr>
                                <td class="ps-4 text-muted small fw-semibold">
                                    {{ $loop->iteration + ($bookings->currentPage() - 1) * $bookings->perPage() }}
                                </td>
                                <td>
                                    <div class="d-flex flex-column align-items-start gap-1">
                                        <span class="badge bg-primary-subtle text-primary font-monospace fs-13 fw-bold">#{{ $booking->booking_number }}</span>
                                        @if($booking->is_offline)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle micro fw-semibold d-inline-flex align-items-center gap-1">
                                                <iconify-icon icon="solar:shop-2-bold"></iconify-icon>
                                                Offline ({{ ucfirst(str_replace('_', ' ', $booking->offline_payment_method ?? 'Direct')) }})
                                            </span>
                                        @else
                                            <span class="badge bg-info-subtle text-info border border-info-subtle micro fw-semibold d-inline-flex align-items-center gap-1">
                                                <iconify-icon icon="solar:global-outline"></iconify-icon>
                                                Online
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-dark d-block fs-14">{{ $booking->customer_name }}</strong>
                                    <span class="text-muted micro d-flex align-items-center gap-1">
                                        <iconify-icon icon="solar:phone-bold" class="fs-12"></iconify-icon>
                                        {{ $booking->customer_phone }}
                                    </span>
                                    @if($booking->city || $booking->state)
                                        <span class="text-muted micro d-block">{{ $booking->city }}{{ $booking->city && $booking->state ? ', ' : '' }}{{ $booking->state }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="max-width: 250px;">
                                        <span class="fs-13 text-dark fw-semibold text-truncate d-block">{{ $booking->product_name }}</span>
                                        @if($booking->model_code)
                                            <span class="micro text-muted font-monospace">{{ $booking->model_code }}</span>
                                        @endif
                                        @if($booking->quantity > 1)
                                            <span class="badge bg-light text-dark micro ms-1">Qty: {{ $booking->quantity }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-success fw-extrabold fs-14">
                                    ₹{{ number_format($booking->booking_amount, 2) }}
                                </td>
                                <td class="fs-14">
                                    @if((float)$booking->balance_amount > 0)
                                        <span class="text-danger fw-bold">₹{{ number_format($booking->balance_amount, 2) }}</span>
                                    @else
                                        <span class="text-success small fw-semibold">₹0.00 (Cleared)</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fs-13 text-dark d-block">{{ \Carbon\Carbon::parse($booking->balance_due_date)->format('d M, Y') }}</span>
                                    @if($booking->payment_status !== 'fully_paid')
                                        <span class="micro text-warning fw-bold">({{ $booking->days_remaining }} days remaining)</span>
                                    @else
                                        <span class="micro text-muted">Completed</span>
                                    @endif
                                </td>
                                <td>
                                    @if($booking->payment_status === 'fully_paid')
                                        <span class="badge bg-success text-white px-2 py-1 fs-12">Fully Paid</span>
                                    @else
                                        <span class="badge bg-warning text-dark px-2 py-1 fs-12">Balance Due</span>
                                    @endif
                                </td>
                                <td>
                                    @if($booking->delivery)
                                        <div class="d-flex flex-column align-items-start">
                                            <span class="badge bg-light text-dark font-monospace micro border">
                                                {{ $booking->delivery->tracking_number }}
                                            </span>
                                            <span class="micro text-capitalize text-muted mt-1">
                                                {{ str_replace('_', ' ', $booking->delivery->stage) }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-muted micro">Not Dispatched</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-sm btn-soft-primary px-3 rounded-pill fw-semibold">
                                            Inspect Receipt
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:box-minimalistic-outline" class="fs-1 text-muted opacity-50 mb-2"></iconify-icon>
                                    <p class="mb-2 fw-semibold">No bookings found matching criteria.</p>
                                    <a href="{{ route('admin.bookings.create') }}" class="btn btn-sm btn-primary mt-1">
                                        + Record First Offline Sale
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top">
                {{ $bookings->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
