@extends('adminlayouts.vertical', ['title' => 'Self Dealers'])

@section('content')
<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Self Dealer Management</h4>
            <p class="text-muted small mb-0">View and manage all active NEXVIA Self Dealers</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.referral.config.settings') }}" class="btn btn-outline-primary btn-sm fw-semibold">
                ⚙️ Referral Incentive Config
            </a>
            <a href="{{ route('admin.self_dealers.fraud_flags') }}" class="btn btn-outline-danger btn-sm fw-semibold">
                🚩 Fraud Flags
                @if($stats['fraud_flags'] > 0)
                    <span class="badge bg-danger ms-1">{{ $stats['fraud_flags'] }}</span>
                @endif
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 text-center">
                    <span class="text-muted small d-block">Total Dealers</span>
                    <h4 class="fw-bold text-primary mb-0">{{ number_format($stats['total_dealers']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 text-center">
                    <span class="text-muted small d-block">Active</span>
                    <h4 class="fw-bold text-success mb-0">{{ number_format($stats['active_dealers']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 text-center">
                    <span class="text-muted small d-block">Points Issued</span>
                    <h4 class="fw-bold text-info mb-0">{{ number_format($stats['total_points']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 text-center">
                    <span class="text-muted small d-block">Pending</span>
                    <h4 class="fw-bold text-warning mb-0">{{ number_format($stats['pending_points']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 text-center">
                    <span class="text-muted small d-block">Available</span>
                    <h4 class="fw-bold text-success mb-0">{{ number_format($stats['available_points']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 text-center">
                    <span class="text-muted small d-block">Redeemed</span>
                    <h4 class="fw-bold text-secondary mb-0">{{ number_format($stats['redeemed_points']) }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" class="row g-2">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Search by name, dealer code, referral code, phone..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary btn-sm w-100">Search</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.self_dealers.index') }}" class="btn btn-outline-secondary btn-sm w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Dealers Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Self Dealer</th>
                            <th>Dealer Code</th>
                            <th>Referral Code</th>
                            <th>Status</th>
                            <th>Available Points</th>
                            <th>Pending Points</th>
                            <th>Lifetime Earned</th>
                            <th>Activated</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dealers as $dealer)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold">{{ $dealer->name }}</div>
                                <small class="text-muted">{{ $dealer->phone }}</small>
                            </td>
                            <td>
                                <code class="text-primary">{{ $dealer->self_dealer_code }}</code>
                            </td>
                            <td>
                                <code>{{ $dealer->referral_code }}</code>
                            </td>
                            <td>
                                @if($dealer->self_dealer_status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($dealer->self_dealer_status === 'suspended')
                                    <span class="badge bg-danger">Suspended</span>
                                @else
                                    <span class="badge bg-warning text-dark">{{ ucfirst($dealer->self_dealer_status) }}</span>
                                @endif
                            </td>
                            <td class="fw-semibold text-success">
                                {{ number_format($dealer->selfDealerWallet->available_points ?? 0) }}
                            </td>
                            <td class="text-warning fw-semibold">
                                {{ number_format($dealer->selfDealerWallet->pending_points ?? 0) }}
                            </td>
                            <td class="text-info fw-semibold">
                                {{ number_format($dealer->selfDealerWallet->total_earned ?? 0) }}
                            </td>
                            <td>
                                <small>{{ $dealer->self_dealer_activated_at ? $dealer->self_dealer_activated_at->format('d M Y') : '—' }}</small>
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.self_dealers.show', $dealer->id) }}"
                                   class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No Self Dealers found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($dealers->hasPages())
        <div class="card-footer bg-white border-0">
            {{ $dealers->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
