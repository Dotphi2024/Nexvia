@extends('adminlayouts.vertical', ['title' => 'Referral Transactions Audit Log'])

@section('content')
<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Referral Transactions Log</h4>
            <p class="text-muted small mb-0">Complete immutable ledger of points credited, pending, unlocked, and reversed</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.self_dealers.index') }}" class="btn btn-outline-secondary btn-sm">
                ← Back to Self Dealers
            </a>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.self_dealers.transactions') }}" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="redeemed" {{ request('status') == 'redeemed' ? 'selected' : '' }}>Redeemed</option>
                        <option value="reversed" {{ request('status') == 'reversed' ? 'selected' : '' }}>Reversed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm" placeholder="From Date">
                </div>
                <div class="col-md-3">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm" placeholder="To Date">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-50">Filter</button>
                    <a href="{{ route('admin.self_dealers.transactions') }}" class="btn btn-light btn-sm w-50">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-secondary small text-uppercase">
                            <th class="ps-3">Txn #</th>
                            <th>Self Dealer</th>
                            <th>Type / Description</th>
                            <th>Category</th>
                            <th>Stage / Tier</th>
                            <th>Cycle</th>
                            <th class="text-end">Points / Amount</th>
                            <th>Status</th>
                            <th class="pe-3 text-end">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr>
                                <td class="ps-3 fw-bold font-monospace">#{{ $tx->id }}</td>
                                <td>
                                    @if($tx->user)
                                        <a href="{{ route('admin.self_dealers.show', $tx->user_id) }}" class="fw-semibold text-decoration-none text-primary">
                                            {{ $tx->user->name }}
                                        </a>
                                        <div class="text-muted small font-monospace">{{ $tx->user->self_dealer_code }}</div>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $tx->description }}</div>
                                    <div class="text-muted small">
                                        @if($tx->booking_id)
                                            Booking #{{ $tx->booking_id }}
                                        @endif
                                        @if($tx->referral_id)
                                            • Ref #{{ $tx->referral_id }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($tx->category)
                                        <span class="badge bg-secondary-subtle text-secondary">{{ $tx->category->name }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($tx->referral_stage)
                                        <span class="badge bg-info-subtle text-info fw-semibold">Stage {{ $tx->referral_stage }}</span>
                                        <div class="text-muted small">{{ $tx->incentive_percentage }}%</div>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($tx->cycle_number)
                                        <span class="text-dark small fw-semibold">Cycle #{{ $tx->cycle_number }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($tx->type === 'credit')
                                        <span class="fw-bold text-success">+₹{{ number_format($tx->amount, 2) }}</span>
                                    @else
                                        <span class="fw-bold text-danger">-₹{{ number_format($tx->amount, 2) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($tx->status === 'available')
                                        <span class="badge bg-success">Available</span>
                                    @elseif($tx->status === 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($tx->status === 'redeemed')
                                        <span class="badge bg-info">Redeemed</span>
                                    @elseif($tx->status === 'reversed')
                                        <span class="badge bg-danger">Reversed</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($tx->status ?? 'done') }}</span>
                                    @endif
                                </td>
                                <td class="pe-3 text-end small text-muted">
                                    {{ $tx->created_at->format('d M Y, h:i A') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bx bx-receipt fs-2 d-block mb-2 text-secondary"></i>
                                    No transactions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
                <div class="p-3 border-top">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
