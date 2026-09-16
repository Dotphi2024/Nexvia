@extends('adminlayouts.vertical', ['title' => 'Self Dealer — ' . $dealer->name])

@section('content')
<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('admin.self_dealers.index') }}" class="text-muted small">← Back to Self Dealers</a>
            <h4 class="fw-bold text-dark mb-0 mt-1">{{ $dealer->name }}</h4>
            <div class="d-flex align-items-center gap-2 mt-1">
                <code class="text-primary">{{ $dealer->self_dealer_code }}</code>
                <span class="text-muted">|</span>
                <code>{{ $dealer->referral_code }}</code>
                @if($dealer->self_dealer_status === 'active')
                    <span class="badge bg-success">Active</span>
                @else
                    <span class="badge bg-danger">{{ ucfirst($dealer->self_dealer_status) }}</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2">
            @if($dealer->self_dealer_status === 'active')
                <form method="POST" action="{{ route('admin.self_dealers.status', $dealer->id) }}">
                    @csrf
                    <input type="hidden" name="status" value="suspended">
                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Suspend this Self Dealer?')">Suspend</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.self_dealers.status', $dealer->id) }}">
                    @csrf
                    <input type="hidden" name="status" value="active">
                    <button class="btn btn-sm btn-outline-success">Reactivate</button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-4">

        {{-- Wallet Summary --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white fw-semibold">
                    💰 Incentive Points Wallet
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Available Points</span>
                            <span class="fw-bold text-success fs-5">{{ number_format($wallet->available_points ?? 0) }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Pending Points</span>
                            <span class="fw-bold text-warning">{{ number_format($wallet->pending_points ?? 0) }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Redeemed Points</span>
                            <span class="fw-bold text-secondary">{{ number_format($wallet->redeemed_points ?? 0) }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Reversed Points</span>
                            <span class="fw-bold text-danger">{{ number_format($wallet->reversed_points ?? 0) }}</span>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold">Lifetime Earned</span>
                        <span class="fw-bold text-info">{{ number_format($wallet->total_earned ?? 0) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dealer Info --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light fw-semibold">👤 Dealer Information</div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted">Phone</td><td class="fw-semibold">{{ $dealer->phone }}</td></tr>
                        <tr><td class="text-muted">Email</td><td>{{ $dealer->email }}</td></tr>
                        <tr><td class="text-muted">City</td><td>{{ $dealer->city ?? '—' }}</td></tr>
                        <tr><td class="text-muted">State</td><td>{{ $dealer->state ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Activated</td><td>{{ $dealer->self_dealer_activated_at?->format('d M Y') ?? '—' }}</td></tr>
                        <tr><td class="text-muted">T&C Version</td><td>{{ $dealer->terms_version ?? '—' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Category-wise Progress --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light fw-semibold">📊 Category Stage Progress</div>
                <div class="card-body p-0">
                    @forelse($categoryProgress as $progress)
                    <div class="px-3 py-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold small">{{ $progress->category->name ?? 'N/A' }}</span>
                            <span class="badge bg-primary">Stage {{ $progress->current_stage }}/5 | Cycle {{ $progress->cycle_number }}</span>
                        </div>
                        {{-- Stage progress bar --}}
                        <div class="d-flex gap-1 mt-1">
                            @php $stages = [1=>10, 2=>12, 3=>15, 4=>18, 5=>20]; @endphp
                            @foreach($stages as $s => $pct)
                                <div class="flex-fill text-center rounded px-1 py-0"
                                     style="font-size:10px; background:{{ $s < $progress->current_stage ? '#198754' : ($s == $progress->current_stage ? '#0d6efd' : '#e9ecef') }};
                                            color: {{ $s <= $progress->current_stage ? '#fff' : '#6c757d' }};">
                                    {{ $pct }}%
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted">{{ $progress->total_referrals_all_time }} total referrals</small>
                    </div>
                    @empty
                    <div class="p-3 text-muted small">No category referrals yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Referral History --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-light fw-semibold">📋 Referral Incentive Transactions</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Type</th>
                            <th>Referred Customer</th>
                            <th>Booking #</th>
                            <th>Category</th>
                            <th>Stage</th>
                            <th>Cycle</th>
                            <th>Eligible Value</th>
                            <th>Rate</th>
                            <th>Points</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($referrals as $referral)
                        <tr>
                            <td class="ps-3">{{ $referral->id }}</td>
                            <td>
                                @if($referral->transaction_type === 'activation')
                                    <span class="badge bg-info">Activation</span>
                                @else
                                    <span class="badge bg-primary">Referral</span>
                                @endif
                            </td>
                            <td>
                                @if($referral->referee)
                                    <strong class="text-dark d-block fs-13">{{ $referral->referee->name }}</strong>
                                    <span class="text-muted micro">{{ $referral->referee->phone }}</span>
                                @else
                                    <span class="text-muted small">Own Purchase</span>
                                @endif
                            </td>
                            <td>
                                @if($referral->booking)
                                    <a href="{{ route('admin.bookings.show', $referral->booking_id) }}" class="fw-bold text-primary text-decoration-none fs-13">
                                        {{ $referral->booking->booking_number }}
                                    </a>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>{{ $referral->category->name ?? '—' }}</td>
                            <td>
                                @if($referral->referral_stage)
                                    <span class="badge bg-info-subtle text-info fw-bold">Stage {{ $referral->referral_stage }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $referral->cycle_number ?? '—' }}</td>
                            <td>₹{{ number_format($referral->eligible_product_value, 2) }}</td>
                            <td><span class="fw-bold text-dark">{{ $referral->benefit_percentage }}%</span></td>
                            <td class="fw-bold text-success">{{ number_format($referral->credit_earned, 0) }} pts</td>
                            <td>
                                @if($referral->status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($referral->status === 'available')
                                    <span class="badge bg-success">Available</span>
                                @elseif($referral->status === 'reversed')
                                    <span class="badge bg-danger">Reversed</span>
                                @elseif($referral->status === 'redeemed')
                                    <span class="badge bg-secondary">Redeemed</span>
                                @endif
                            </td>
                            <td><small>{{ $referral->created_at->format('d M Y') }}</small></td>
                            <td class="text-end pe-3">
                                @if($referral->status === 'pending')
                                    <form method="POST" action="{{ route('admin.self_dealers.qualify', $referral->id) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-xs btn-outline-success btn-sm">✅ Qualify</button>
                                    </form>
                                @endif
                                @if(in_array($referral->status, ['pending', 'available']))
                                    <button class="btn btn-xs btn-outline-danger btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#reverseModal{{ $referral->id }}">⚠️ Reverse</button>

                                    {{-- Reverse Modal --}}
                                    <div class="modal fade" id="reverseModal{{ $referral->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form method="POST" action="{{ route('admin.self_dealers.reverse', $referral->id) }}">
                                                @csrf
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h6 class="modal-title">Reverse Referral #{{ $referral->id }}</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <label class="form-label">Reason for Reversal</label>
                                                        <input type="text" name="reason" class="form-control" required
                                                               placeholder="e.g. Order cancelled by customer">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger btn-sm">Confirm Reversal</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13" class="text-center py-4 text-muted">No referral transactions found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($referrals->hasPages())
        <div class="card-footer bg-white border-0">{{ $referrals->links() }}</div>
        @endif
    </div>

</div>
@endsection
