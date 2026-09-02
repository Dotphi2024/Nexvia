@extends('adminlayouts.vertical', ['title' => 'Dashboard'])

@section('content')
<div class="container-fluid py-3">

    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Admin Dashboard</h4>
            <p class="text-muted small mb-0">Overview of NEXVIA E-Commerce, 20% Bookings, Collections & Inventory</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm fw-semibold">
                + Add Product
            </a>
            <a href="{{ route('admin.booking.engine.settings') }}" class="btn btn-outline-dark btn-sm fw-semibold">
                Booking Engine Controls
            </a>
        </div>
    </div>

    <!-- 10 KPI METRICS GRID (SECTION D) -->
    <div class="row g-3 mb-4">
        <!-- 1. Revenue -->
        <div class="col-md-3">
            <div class="card border border-light-subtle shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Revenue</span>
                    <h3 class="fw-bold text-primary mb-0">₹{{ number_format($revenue, 2) }}</h3>
                </div>
            </div>
        </div>

        <!-- 2. Today's Orders -->
        <div class="col-md-3">
            <div class="card border border-light-subtle shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Today's Orders</span>
                    <h3 class="fw-bold text-dark mb-0">{{ number_format($todaysOrders) }}</h3>
                </div>
            </div>
        </div>

        <!-- 3. Today's Bookings -->
        <div class="col-md-3">
            <div class="card border border-light-subtle shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Today's 20% Bookings</span>
                    <h3 class="fw-bold text-info mb-0">{{ number_format($todaysBookings) }}</h3>
                </div>
            </div>
        </div>

        <!-- 4. Collections -->
        <div class="col-md-3">
            <div class="card border border-light-subtle shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Collections</span>
                    <h3 class="fw-bold text-success mb-0">₹{{ number_format($collections, 2) }}</h3>
                </div>
            </div>
        </div>

        <!-- 5. Outstanding Balance -->
        <div class="col-md-3">
            <div class="card border border-light-subtle shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Outstanding Balance</span>
                    <h3 class="fw-bold text-danger mb-0">₹{{ number_format($outstandingBalance, 2) }}</h3>
                </div>
            </div>
        </div>

        <!-- 6. Customers -->
        <div class="col-md-3">
            <div class="card border border-light-subtle shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Customers</span>
                    <h3 class="fw-bold text-dark mb-0">{{ number_format($totalCustomers) }}</h3>
                </div>
            </div>
        </div>

        <!-- 7. Self Dealers -->
        <div class="col-md-3">
            <div class="card border border-light-subtle shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Self Dealers</span>
                    <h3 class="fw-bold text-secondary mb-0">{{ number_format($selfDealers) }}</h3>
                    <span class="micro text-muted">Dealer Module Ready</span>
                </div>
            </div>
        </div>

        <!-- 8. Product Credit Liability -->
        <div class="col-md-3">
            <div class="card border border-light-subtle shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Product Credit Liability</span>
                    <h3 class="fw-bold text-warning mb-0">₹{{ number_format($productCreditLiability, 2) }}</h3>
                    <span class="micro text-muted">Active Customer Wallet Credits</span>
                </div>
            </div>
        </div>

        <!-- 9. Inventory -->
        <div class="col-md-3">
            <div class="card border border-light-subtle shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Inventory</span>
                    <h3 class="fw-bold text-dark mb-0">{{ number_format($inventoryCount) }} <span class="fs-12 text-muted fw-normal">Units</span></h3>
                </div>
            </div>
        </div>

        <!-- 10. Service Requests -->
        <div class="col-md-3">
            <div class="card border border-light-subtle shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Service Requests</span>
                    <h3 class="fw-bold text-dark mb-0">{{ number_format($serviceRequestsCount) }} <span class="fs-12 text-muted fw-normal">Open</span></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Section: Recent Bookings & Recent Transfers -->
    <div class="row g-4">
        <!-- Recent Bookings Table -->
        <div class="col-lg-8">
            <div class="card border border-light-subtle shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0">Recent Customer Bookings</h6>
                    <a href="{{ route('admin.bookings.index') }}" class="small text-primary text-decoration-none">View All Bookings →</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Receipt #</th>
                                    <th>Customer</th>
                                    <th>Product</th>
                                    <th>20% Paid</th>
                                    <th>80% Due</th>
                                    <th>Due Date</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentBookings as $booking)
                                    <tr>
                                        <td>
                                            <span class="font-monospace fw-bold text-primary">#{{ $booking->booking_number }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $booking->customer_name }}</div>
                                            <div class="small text-muted">{{ $booking->customer_phone }}</div>
                                        </td>
                                        <td class="small">{{ $booking->product_name }}</td>
                                        <td class="text-success fw-bold">₹{{ number_format($booking->booking_amount, 0) }}</td>
                                        <td class="text-danger fw-bold">₹{{ number_format($booking->balance_amount, 0) }}</td>
                                        <td class="small">
                                            <div>{{ \Carbon\Carbon::parse($booking->balance_due_date)->format('d M, Y') }}</div>
                                            @if($booking->payment_status !== 'fully_paid')
                                                <div class="text-warning small">({{ $booking->days_remaining }} days left)</div>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-sm btn-outline-primary">Details</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transfers Feed -->
        <div class="col-lg-4">
            <div class="card border border-light-subtle shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0">Booking Transfers Audit</h6>
                    <a href="{{ route('admin.transfers.audit') }}" class="small text-primary text-decoration-none">Audit Log →</a>
                </div>
                <div class="card-body p-3">
                    @if($recentTransfers->count() > 0)
                        @foreach($recentTransfers as $transfer)
                            <div class="p-2 border rounded mb-2 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="font-monospace small text-primary">#{{ $transfer->booking->booking_number }}</strong>
                                    <span class="badge bg-success small">Completed</span>
                                </div>
                                <div class="small text-dark">
                                    From <strong>{{ $transfer->fromUser->name ?? 'User' }}</strong> to <strong>{{ $transfer->to_name }}</strong> ({{ $transfer->to_phone }})
                                </div>
                                <div class="text-muted micro">{{ $transfer->transferred_at ? \Carbon\Carbon::parse($transfer->transferred_at)->format('d M, Y H:i') : '' }}</div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-3 small">No receipt transfers yet.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection