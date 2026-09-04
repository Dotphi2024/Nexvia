@extends('adminlayouts.vertical', ['title' => 'Customer Accounts'])

@section('title', 'Customer Management')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Customer Accounts</h4>
            <p class="text-muted small mb-0">Manage customer accounts, status, referral tracking, and delivery addresses</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border border-light-subtle shadow-sm p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Total Customers</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($stats['total']) }}</h3>
                    </div>
                    <div class="avatar-md bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center">
                        <iconify-icon icon="solar:users-group-two-rounded-bold" class="fs-24"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border border-light-subtle shadow-sm p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Active Accounts</span>
                        <h3 class="fw-bold text-success mb-0 mt-1">{{ number_format($stats['active']) }}</h3>
                    </div>
                    <div class="avatar-md bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center">
                        <iconify-icon icon="solar:check-circle-bold" class="fs-24"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border border-light-subtle shadow-sm p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Inactive / Blocked</span>
                        <h3 class="fw-bold text-danger mb-0 mt-1">{{ number_format($stats['inactive']) }}</h3>
                    </div>
                    <div class="avatar-md bg-danger-subtle text-danger rounded-3 d-flex align-items-center justify-content-center">
                        <iconify-icon icon="solar:forbidden-bold" class="fs-24"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border border-light-subtle shadow-sm p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Saved Addresses</span>
                        <h3 class="fw-bold text-info mb-0 mt-1">{{ number_format($stats['totalAddresses']) }}</h3>
                    </div>
                    <div class="avatar-md bg-info-subtle text-info rounded-3 d-flex align-items-center justify-content-center">
                        <iconify-icon icon="solar:map-point-wave-bold" class="fs-24"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card border border-light-subtle shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.customers.index') }}" method="GET" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search by Name, Email, or Phone..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Account Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive / Blocked Only</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold">Filter</button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card border border-light-subtle shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0">Registered Customers List</h6>
            <span class="badge bg-secondary-subtle text-secondary fs-12">{{ $customers->total() }} Records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Customer Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Addresses</th>
                            <th>Bookings</th>
                            <th>Status</th>
                            <th>Registered On</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td class="text-muted small">{{ $loop->iteration + ($customers->currentPage() - 1) * $customers->perPage() }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($customer->profile_pic)
                                            <img src="{{ str_starts_with($customer->profile_pic, 'http') ? $customer->profile_pic : asset('customer_pics/' . $customer->profile_pic) }}" alt="avatar" class="rounded-circle border" width="36" height="36" style="object-fit: cover;">
                                        @else
                                            <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-13" style="width: 36px; height: 36px;">
                                                {{ strtoupper(substr($customer->name ?? 'C', 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('admin.customers.show', $customer->id) }}" class="fw-bold text-dark text-decoration-none">
                                                {{ $customer->name }}
                                            </a>
                                            <span class="d-block text-muted micro">ID: #{{ $customer->id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $customer->phone }}</span>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ $customer->email ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info fw-semibold">{{ $customer->addresses_count }} Address(es)</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary fw-semibold">{{ $customer->bookings_count }} Booking(s)</span>
                                </td>
                                <td>
                                    <form action="{{ route('admin.customers.status', $customer->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm p-0 border-0" title="Click to toggle status">
                                            <span class="badge {{ $customer->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                                {{ ucfirst($customer->status) }}
                                            </span>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ $customer->created_at ? $customer->created_at->format('d M Y, h:i A') : '—' }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-sm btn-outline-primary" title="View Customer Details">
                                            <iconify-icon icon="solar:eye-linear" class="fs-16"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this customer account?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Account">
                                                <iconify-icon icon="solar:trash-bin-trash-linear" class="fs-16"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:users-group-two-rounded-outline" class="fs-36 d-block mx-auto mb-2 text-secondary"></iconify-icon>
                                    <p class="mb-0 fw-semibold">No customer accounts found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($customers->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
