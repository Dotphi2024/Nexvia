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
</div>
@endsection