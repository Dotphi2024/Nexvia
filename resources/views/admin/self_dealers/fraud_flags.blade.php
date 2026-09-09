@extends('adminlayouts.vertical', ['title' => 'Fraud Flags & Audit Review'])

@section('content')
<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Referral Fraud Flags</h4>
            <p class="text-muted small mb-0">Review suspicious activities, self-referral attempts, and duplicate booking claims</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.self_dealers.index') }}" class="btn btn-outline-secondary btn-sm">
                ← Back to Self Dealers
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.self_dealers.fraud_flags') }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses (Pending: {{ $pendingCount }})</option>
                        <option value="pending_review" {{ request('status') == 'pending_review' ? 'selected' : '' }}>Pending Review Only</option>
                        <option value="cleared" {{ request('status') == 'cleared' ? 'selected' : '' }}>Cleared</option>
                        <option value="confirmed_fraud" {{ request('status') == 'confirmed_fraud' ? 'selected' : '' }}>Confirmed Fraud</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.self_dealers.fraud_flags') }}" class="btn btn-light btn-sm w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Flags Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-secondary small text-uppercase">
                            <th class="ps-3">Flag #</th>
                            <th>Flag Type</th>
                            <th>Customer / Buyer</th>
                            <th>Referrer (Claimant)</th>
                            <th>Booking #</th>
                            <th>Details & Reason</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($flags as $flag)
                            <tr>
                                <td class="ps-3 fw-bold">#{{ $flag->id }}</td>
                                <td>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        {{ ucwords(str_replace('_', ' ', $flag->flag_type)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($flag->user)
                                        <div class="fw-semibold text-dark">{{ $flag->user->name }}</div>
                                        <div class="text-muted small">{{ $flag->user->phone }}</div>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($flag->referrer)
                                        <a href="{{ route('admin.self_dealers.show', $flag->referrer_id) }}" class="fw-semibold text-decoration-none text-primary">
                                            {{ $flag->referrer->name }}
                                        </a>
                                        <div class="text-muted small font-monospace">{{ $flag->referrer->self_dealer_code }}</div>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($flag->booking)
                                        <span class="font-monospace fw-semibold">#{{ $flag->booking->booking_number ?? $flag->booking_id }}</span>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="small text-dark">{{ $flag->flag_reason }}</div>
                                    @if($flag->admin_notes)
                                        <div class="text-muted small fst-italic mt-1">Note: {{ $flag->admin_notes }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($flag->status === 'pending_review')
                                        <span class="badge bg-warning text-dark">Pending Review</span>
                                    @elseif($flag->status === 'cleared')
                                        <span class="badge bg-success">Cleared</span>
                                    @else
                                        <span class="badge bg-danger">Confirmed Fraud</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    {{ $flag->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td class="text-end pe-3">
                                    @if($flag->status === 'pending_review')
                                        <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $flag->id }}">
                                            Review
                                        </button>

                                        {{-- Review Modal --}}
                                        <div class="modal fade text-start" id="reviewModal{{ $flag->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form action="{{ route('admin.self_dealers.fraud_review', $flag->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h6 class="modal-title fw-bold">Review Flag #{{ $flag->id }}</h6>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p class="small text-muted mb-3">{{ $flag->flag_reason }}</p>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold">Decision</label>
                                                                <select name="status" class="form-select" required>
                                                                    <option value="cleared">Clear (Legitimate Referral)</option>
                                                                    <option value="confirmed_fraud">Confirm Fraud (Reverse Points)</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold">Admin Notes</label>
                                                                <textarea name="admin_notes" class="form-control" rows="3" placeholder="Explain the verification steps taken..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary btn-sm">Save Decision</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted small">Done</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bx bx-shield-quarter fs-2 d-block mb-2 text-secondary"></i>
                                    No fraud flags found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($flags->hasPages())
                <div class="p-3 border-top">
                    {{ $flags->links() }}
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
