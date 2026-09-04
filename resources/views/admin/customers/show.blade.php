@extends('adminlayouts.vertical', ['title' => 'Customer Profile Details'])

@section('title', 'Customer Profile: ' . $customer->name)

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <iconify-icon icon="solar:arrow-left-linear" class="align-middle me-1"></iconify-icon> Back to Customers
            </a>
            <h4 class="fw-bold text-dark mb-0">Customer Profile: {{ $customer->name }}</h4>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('admin.customers.status', $customer->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm {{ $customer->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }} fw-semibold">
                    {{ $customer->status === 'active' ? 'Deactivate Account' : 'Activate Account' }}
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Profile Card -->
        <div class="col-lg-4">
            <div class="card border border-light-subtle shadow-sm mb-4">
                <div class="card-body p-4 text-center">
                    @if($customer->profile_pic)
                        <img src="{{ str_starts_with($customer->profile_pic, 'http') ? $customer->profile_pic : asset('customer_pics/' . $customer->profile_pic) }}" alt="avatar" class="rounded-circle border mb-3" width="90" height="90" style="object-fit: cover;">
                    @else
                        <div class="avatar-lg bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-28 mx-auto mb-3" style="width: 90px; height: 90px;">
                            {{ strtoupper(substr($customer->name ?? 'C', 0, 1)) }}
                        </div>
                    @endif

                    <h5 class="fw-bold text-dark mb-1">{{ $customer->name }}</h5>
                    <span class="badge {{ $customer->status === 'active' ? 'bg-success' : 'bg-danger' }} mb-3">
                        {{ ucfirst($customer->status) }} Customer Account
                    </span>

                    <div class="border-top pt-3 text-start">
                        <div class="mb-2">
                            <span class="text-muted small d-block">Phone Number:</span>
                            <strong class="text-dark fs-14">{{ $customer->phone }}</strong>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted small d-block">Email Address:</span>
                            <strong class="text-dark fs-14">{{ $customer->email ?? 'Not Provided' }}</strong>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted small d-block">Wallet Balance:</span>
                            <strong class="text-success fs-15">₹{{ number_format($customer->wallet_balance ?? 0, 2) }}</strong>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Registered On:</span>
                            <strong class="text-dark fs-14">{{ $customer->created_at ? $customer->created_at->format('d M Y, h:i A') : '—' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Saved Delivery Addresses & Bookings -->
        <div class="col-lg-8">
            <!-- Delivery Addresses -->
            <div class="card border border-light-subtle shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0">
                        <iconify-icon icon="solar:map-point-wave-bold" class="text-primary me-1 align-middle fs-18"></iconify-icon>
                        Saved Delivery Addresses ({{ $customer->addresses->count() }})
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Title / Name</th>
                                    <th>Phone</th>
                                    <th>Street Address</th>
                                    <th>City, State</th>
                                    <th>Pincode</th>
                                    <th>Default</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customer->addresses as $address)
                                    <tr>
                                        <td class="text-muted small">{{ $loop->iteration }}</td>
                                        <td>
                                            <strong class="text-dark d-block">{{ $address->name }}</strong>
                                        </td>
                                        <td>{{ $address->phone }}</td>
                                        <td>{{ $address->street }}</td>
                                        <td>{{ $address->city }}, {{ $address->state }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $address->pincode }}</span></td>
                                        <td>
                                            @if($address->is_default)
                                                <span class="badge bg-success-subtle text-success fw-bold">Primary Default</span>
                                            @else
                                                <span class="text-muted small">Standard</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            No saved delivery addresses found for this customer.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Customer Bookings History -->
            <div class="card border border-light-subtle shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold text-dark mb-0">
                        <iconify-icon icon="solar:ticket-bold" class="text-primary me-1 align-middle fs-18"></iconify-icon>
                        Customer Bookings ({{ $customer->bookings->count() }})
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Booking #</th>
                                    <th>Date</th>
                                    <th>Total Amount</th>
                                    <th>Paid</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customer->bookings as $booking)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.bookings.show', $booking->id) }}" class="fw-bold text-primary text-decoration-none">
                                                {{ $booking->booking_number }}
                                            </a>
                                        </td>
                                        <td>{{ $booking->created_at ? $booking->created_at->format('d M Y') : '—' }}</td>
                                        <td>₹{{ number_format($booking->total_amount, 2) }}</td>
                                        <td><span class="text-success fw-semibold">₹{{ number_format($booking->booking_amount_paid, 2) }}</span></td>
                                        <td><span class="text-danger fw-semibold">₹{{ number_format($booking->remaining_balance, 2) }}</span></td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info">{{ ucfirst($booking->booking_status) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            No booking transactions found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
