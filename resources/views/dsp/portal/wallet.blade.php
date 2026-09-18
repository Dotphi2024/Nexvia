@extends('dsp.layouts.portal')

@section('title', '5% Earnings & Cash Wallet – DSP Partner Portal')

@section('content')
<!-- Hero Balance Card -->
<div class="card-dsp p-4 mb-4" style="background: linear-gradient(135deg, #065f46 0%, #047857 100%); color: #ffffff;">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-white text-success fw-bold">DSP CASH WALLET</span>
                <span class="text-white-50 small">5% Commission on Delivered Orders</span>
            </div>
            <h2 class="fw-extrabold text-white mb-1">₹{{ number_format($dsp->wallet_balance ?? 0, 2) }}</h2>
            <p class="text-white-50 mb-0 small">
                Available cash balance redeemable directly to your Bank Account or UPI.
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="bg-white bg-opacity-10 border border-white border-opacity-20 p-3 rounded-4 text-center">
                <span class="micro text-white-50 d-block text-uppercase fw-bold">Lifetime Earned</span>
                <span class="fs-4 fw-bold text-white">₹{{ number_format($dsp->total_earned ?? 0, 2) }}</span>
            </div>
            <div class="bg-white bg-opacity-10 border border-white border-opacity-20 p-3 rounded-4 text-center">
                <span class="micro text-white-50 d-block text-uppercase fw-bold">Total Redeemed</span>
                <span class="fs-4 fw-bold text-warning">₹{{ number_format($dsp->total_redeemed ?? 0, 2) }}</span>
            </div>
            <button type="button" class="btn btn-warning fw-bold px-4 py-3 rounded-3 shadow" data-bs-toggle="modal" data-bs-target="#redeemModal">
                <iconify-icon icon="solar:hand-money-bold" class="align-middle me-1 fs-5"></iconify-icon>
                Redeem Cash Now
            </button>
        </div>
    </div>
</div>

<!-- Cash Redemption Modal -->
<div class="modal fade" id="redeemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title fw-bold">
                    <iconify-icon icon="solar:cash-out-bold" class="text-warning me-1 align-middle"></iconify-icon>
                    Request Cash Redemption Payout
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('dsp.wallet.redeem') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info bg-opacity-10 border-info p-3 rounded-3 mb-3 small">
                        Available Balance: <strong class="text-success fs-6">₹{{ number_format($dsp->wallet_balance ?? 0, 2) }}</strong>. Minimum withdrawal: ₹100.
                    </div>

                    <!-- Amount -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Redemption Amount (₹) *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">₹</span>
                            <input type="number" step="0.01" min="100" max="{{ $dsp->wallet_balance }}" name="amount" class="form-control form-control-lg fw-bold" placeholder="e.g. 5000" required>
                        </div>
                    </div>

                    <!-- Payout Mode -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Payout Mode *</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payout_mode" id="modeBank" value="bank" checked onchange="togglePayoutMode('bank')">
                                <label class="form-check-label fw-semibold text-dark small" for="modeBank">Bank Account (IMPS/NEFT)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payout_mode" id="modeUpi" value="upi" onchange="togglePayoutMode('upi')">
                                <label class="form-check-label fw-semibold text-dark small" for="modeUpi">UPI ID</label>
                            </div>
                        </div>
                    </div>

                    <!-- Bank Details Section -->
                    <div id="bankSection">
                        <div class="mb-2">
                            <label class="form-label fw-semibold text-dark micro">Account Holder Name *</label>
                            <input type="text" name="bank_holder_name" class="form-control form-control-sm" value="{{ $dsp->payout_holder_name ?: ($dsp->applicant_name ?: $dsp->business_name) }}" placeholder="Name as per bank passbook">
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold text-dark micro">Account Number *</label>
                            <input type="text" name="bank_account_number" class="form-control form-control-sm font-monospace" value="{{ $dsp->payout_account_number }}" placeholder="Bank account number">
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label fw-semibold text-dark micro">IFSC Code *</label>
                                <input type="text" name="bank_ifsc" class="form-control form-control-sm font-monospace text-uppercase" value="{{ $dsp->payout_ifsc }}" placeholder="e.g. HDFC0001234">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold text-dark micro">Bank Name *</label>
                                <input type="text" name="bank_name" class="form-control form-control-sm" value="{{ $dsp->payout_bank_name }}" placeholder="e.g. HDFC Bank">
                            </div>
                        </div>
                    </div>

                    <!-- UPI Section -->
                    <div id="upiSection" class="d-none">
                        <div class="mb-2">
                            <label class="form-label fw-semibold text-dark micro">UPI ID / VPA *</label>
                            <input type="text" name="upi_id" class="form-control font-monospace" value="{{ $dsp->payout_upi_id }}" placeholder="e.g. yourname@upi or mobile@okhdfcbank">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold px-4">
                        Submit Redemption Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Payout Requests History -->
    <div class="col-lg-5">
        <div class="card-dsp p-4 mb-4">
            <h5 class="fw-bold text-dark mb-3">
                <iconify-icon icon="solar:history-bold" class="text-primary me-1 align-middle"></iconify-icon>
                Recent Cash Redemptions
            </h5>

            @if($payoutRequests->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($payoutRequests as $req)
                        <div class="list-group-item px-0 py-3">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div>
                                    <span class="font-monospace fw-bold text-dark small">#{{ $req->request_number }}</span>
                                    <span class="micro text-muted d-block">{{ $req->created_at->format('d M, Y H:i') }}</span>
                                </div>
                                <span class="badge {{ $req->status_badge['class'] }}">
                                    {{ $req->status_badge['text'] }}
                                </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-extrabold text-dark fs-6">₹{{ number_format($req->amount, 2) }}</span>
                                <span class="micro text-muted">
                                    {{ $req->payout_mode === 'upi' ? 'UPI: ' . $req->upi_id : 'Bank: ' . Str::mask($req->bank_account_number, '*', 0, -4) }}
                                </span>
                            </div>

                            @if($req->transaction_reference)
                                <div class="p-2 bg-light rounded-2 font-monospace micro text-success mt-2">
                                    UTR / Ref: <strong>{{ $req->transaction_reference }}</strong>
                                </div>
                            @endif

                            @if($req->admin_notes)
                                <div class="micro text-muted mt-1">Note: {{ $req->admin_notes }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="micro text-muted text-center py-4 mb-0">No cash redemptions requested yet.</p>
            @endif
        </div>
    </div>

    <!-- Full Transactions Ledger -->
    <div class="col-lg-7">
        <div class="card-dsp p-4 mb-4">
            <h5 class="fw-bold text-dark mb-3">
                <iconify-icon icon="solar:document-text-bold" class="text-success me-1 align-middle"></iconify-icon>
                Earnings & Wallet Statement
            </h5>

            @if($transactions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-dsp align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description / Ref</th>
                                <th>Type</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $tx)
                                <tr>
                                    <td>
                                        <span class="micro text-muted d-block">{{ $tx->created_at->format('d M, Y') }}</span>
                                        <span class="micro text-muted font-monospace">{{ $tx->created_at->format('H:i') }}</span>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-dark">{{ $tx->description }}</div>
                                        @if($tx->reference_number)
                                            <span class="micro font-monospace text-muted">Ref: {{ $tx->reference_number }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($tx->type === 'credit')
                                            <span class="badge bg-success-subtle text-success micro">5% COMMISSION</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger micro">REDEMPTION</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold {{ $tx->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                        {{ $tx->type === 'credit' ? '+' : '-' }}₹{{ number_format($tx->amount, 2) }}
                                    </td>
                                    <td class="text-end small text-muted font-monospace">
                                        ₹{{ number_format($tx->balance_after, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $transactions->links() }}
                </div>
            @else
                <p class="micro text-muted text-center py-4 mb-0">No wallet transactions recorded yet.</p>
            @endif
        </div>
    </div>
</div>

<script>
function togglePayoutMode(mode) {
    if (mode === 'bank') {
        document.getElementById('bankSection').classList.remove('d-none');
        document.getElementById('upiSection').classList.add('d-none');
    } else {
        document.getElementById('bankSection').classList.add('d-none');
        document.getElementById('upiSection').classList.remove('d-none');
    }
}
</script>
@endsection
