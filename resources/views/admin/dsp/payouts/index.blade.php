@extends('adminlayouts.vertical', ['title' => 'DSP Cash Redemptions & Payouts'])

@section('title', 'DSP Cash Redemptions & Payouts')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">DSP Cash Redemptions & Payouts</h4>
            <p class="text-muted small mb-0">Review, verify, and disburse cash redemption requests from Authorised Delivery & Service Partners (earned via 5% delivery commissions).</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.dsp.index') }}" class="btn btn-outline-primary btn-sm fw-semibold">
                <iconify-icon icon="solar:users-group-two-rounded-bold" class="align-middle me-1"></iconify-icon> DSP Partners Directory
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border border-light-subtle shadow-sm p-3">
                <span class="micro text-muted text-uppercase fw-bold">Total Payout Requests</span>
                <h4 class="fw-bold text-dark my-1">{{ $stats['total_count'] }}</h4>
                <span class="micro text-muted">₹{{ number_format($stats['total_amount'], 2) }} total requested</span>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border border-warning shadow-sm p-3 bg-warning bg-opacity-10">
                <span class="micro text-warning text-uppercase fw-bold">Pending Remittance</span>
                <h4 class="fw-bold text-warning my-1">₹{{ number_format($stats['pending_amount'], 2) }}</h4>
                <span class="micro text-muted">{{ $stats['pending_count'] }} awaiting bank transfer</span>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border border-success shadow-sm p-3 bg-success bg-opacity-10">
                <span class="micro text-success text-uppercase fw-bold">Total Paid Out</span>
                <h4 class="fw-bold text-success my-1">₹{{ number_format($stats['paid_amount'], 2) }}</h4>
                <span class="micro text-muted">{{ $stats['paid_count'] }} transactions completed</span>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border border-light-subtle shadow-sm p-3">
                <span class="micro text-muted text-uppercase fw-bold">Commission Rate</span>
                <h4 class="fw-bold text-primary my-1">5.0%</h4>
                <span class="micro text-muted">Per delivered product</span>
            </div>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="card border border-light-subtle shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex gap-2">
                <a href="{{ route('admin.dsp.payouts.index', ['status' => 'all']) }}" class="btn btn-sm {{ $status === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">All</a>
                <a href="{{ route('admin.dsp.payouts.index', ['status' => 'pending']) }}" class="btn btn-sm {{ $status === 'pending' ? 'btn-warning' : 'btn-outline-secondary' }}">Pending</a>
                <a href="{{ route('admin.dsp.payouts.index', ['status' => 'paid']) }}" class="btn btn-sm {{ $status === 'paid' ? 'btn-success text-white' : 'btn-outline-secondary' }}">Paid</a>
                <a href="{{ route('admin.dsp.payouts.index', ['status' => 'rejected']) }}" class="btn btn-sm {{ $status === 'rejected' ? 'btn-danger text-white' : 'btn-outline-secondary' }}">Rejected</a>
            </div>

            <form action="{{ route('admin.dsp.payouts.index') }}" method="GET" class="d-flex gap-2">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by Ref #, DSP name, mobile..." value="{{ $search }}">
                <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
            </form>
        </div>

        <div class="card-body p-0">
            @if($payouts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Request #</th>
                                <th>DSP Partner</th>
                                <th>Mode & Destination</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                                <th>UTR / Reference</th>
                                <th>Requested Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payouts as $payout)
                                <tr>
                                    <td>
                                        <span class="fw-bold font-monospace text-primary small">#{{ $payout->request_number }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark small">{{ $payout->dsp?->business_name ?: $payout->dsp?->applicant_name }}</div>
                                        <span class="micro text-muted font-monospace"><iconify-icon icon="solar:phone-bold"></iconify-icon> {{ $payout->dsp?->mobile }}</span>
                                    </td>
                                    <td>
                                        @if($payout->payout_mode === 'upi')
                                            <span class="badge bg-info-subtle text-info micro">UPI</span>
                                            <div class="small font-monospace text-dark">{{ $payout->upi_id }}</div>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary micro">BANK TRANSFER</span>
                                            <div class="small fw-semibold text-dark">{{ $payout->bank_name }} - {{ $payout->bank_holder_name }}</div>
                                            <div class="micro font-monospace text-muted">A/c: {{ $payout->bank_account_number }} (IFSC: {{ $payout->bank_ifsc }})</div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-extrabold text-success fs-6">₹{{ number_format($payout->amount, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $payout->status_badge['class'] }}">
                                            {{ $payout->status_badge['text'] }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($payout->transaction_reference)
                                            <span class="font-monospace text-success small fw-bold">{{ $payout->transaction_reference }}</span>
                                        @else
                                            <span class="text-muted micro">Pending Disbursal</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">
                                        {{ $payout->created_at->format('d M, Y H:i') }}
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#actionModal{{ $payout->id }}">
                                            Manage
                                        </button>

                                        <!-- Modal for Admin Status Update -->
                                        <div class="modal fade text-start" id="actionModal{{ $payout->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content rounded-4 border-0 shadow-lg">
                                                    <div class="modal-header bg-dark text-white">
                                                        <h6 class="modal-title fw-bold">
                                                            Process Payout #{{ $payout->request_number }}
                                                        </h6>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('admin.dsp.payouts.status', $payout->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body p-4">
                                                            <div class="p-3 bg-light rounded-3 border mb-3">
                                                                <div class="d-flex justify-content-between mb-1">
                                                                    <span class="small text-muted">Partner:</span>
                                                                    <strong class="text-dark">{{ $payout->dsp?->business_name ?: $payout->dsp?->applicant_name }}</strong>
                                                                </div>
                                                                <div class="d-flex justify-content-between mb-1">
                                                                    <span class="small text-muted">Amount:</span>
                                                                    <strong class="text-success fs-5">₹{{ number_format($payout->amount, 2) }}</strong>
                                                                </div>
                                                                <div class="d-flex justify-content-between">
                                                                    <span class="small text-muted">Payout Destination:</span>
                                                                    <span class="small font-monospace text-dark text-end">
                                                                        {{ $payout->payout_mode === 'upi' ? 'UPI: ' . $payout->upi_id : "{$payout->bank_name} A/c {$payout->bank_account_number} ({$payout->bank_ifsc})" }}
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold text-dark small">Update Status *</label>
                                                                <select name="status" class="form-select" required>
                                                                    <option value="paid" {{ $payout->status === 'paid' ? 'selected' : '' }}>Mark as PAID (Funds Transferred)</option>
                                                                    <option value="approved" {{ $payout->status === 'approved' ? 'selected' : '' }}>Mark as APPROVED (Processing)</option>
                                                                    <option value="rejected" {{ $payout->status === 'rejected' ? 'selected' : '' }}>Mark as REJECTED (Refund Balance)</option>
                                                                </select>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold text-dark small">Bank UTR / Transaction Reference</label>
                                                                <input type="text" name="transaction_reference" class="form-control font-monospace" value="{{ $payout->transaction_reference }}" placeholder="e.g. UTR123456789012">
                                                                <span class="micro text-muted">Required when marking as Paid</span>
                                                            </div>

                                                            <div class="mb-2">
                                                                <label class="form-label fw-semibold text-dark small">Admin Remarks / Notes</label>
                                                                <textarea name="admin_notes" class="form-control" rows="2" placeholder="Optional notes for partner audit">{{ $payout->admin_notes }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary btn-sm fw-semibold px-3">
                                                                Save Payout Status
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top">
                    {{ $payouts->links() }}
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <iconify-icon icon="solar:wallet-money-bold-duotone" class="fs-1 text-muted opacity-50 mb-2"></iconify-icon>
                    <p class="mb-0">No cash redemption requests found matching your filter.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
