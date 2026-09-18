@extends('dsp.layouts.portal')

@section('title', 'Territory & Profile Settings – DSP Partner Portal')

@section('content')
<div class="mb-4">
    <h4 class="fw-bold text-dark mb-1">DSP Partner Territory & Profile</h4>
    <p class="text-muted small mb-0">View your authorised service territory PIN codes and manage default payout bank credentials.</p>
</div>

<div class="row g-4">
    <!-- Territory & Business Info -->
    <div class="col-lg-5">
        <!-- Territory Card -->
        <div class="card-dsp p-4 mb-4 border-primary">
            <h5 class="fw-bold text-dark mb-3">
                <iconify-icon icon="solar:map-point-wave-bold" class="text-primary me-1 align-middle"></iconify-icon>
                Authorised Territory Coverage
            </h5>

            <div class="mb-3">
                <span class="micro text-muted d-block text-uppercase fw-bold">Primary Hub Area</span>
                <strong class="text-dark">{{ $dsp->preferred_territory_area ?: 'Regional Hub' }}</strong>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <span class="micro text-muted d-block text-uppercase fw-bold">District</span>
                    <strong class="text-dark">{{ $dsp->district ?: 'Not specified' }}</strong>
                </div>
                <div class="col-6">
                    <span class="micro text-muted d-block text-uppercase fw-bold">State</span>
                    <strong class="text-dark">{{ $dsp->state ?: 'Not specified' }}</strong>
                </div>
            </div>

            <div class="p-3 bg-light rounded-3 border">
                <span class="micro text-muted d-block text-uppercase fw-bold mb-2">Serviced PIN Codes</span>
                @php
                    $pincodes = $dsp->servicedPincodesArray();
                @endphp
                @if(count($pincodes) > 0)
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($pincodes as $pin)
                            <span class="badge bg-primary-subtle text-primary font-monospace py-1 px-2">{{ $pin }}</span>
                        @endforeach
                    </div>
                @else
                    <span class="small text-muted">All PIN codes in {{ $dsp->district ?: 'District' }}.</span>
                @endif
            </div>
        </div>

        <!-- Business Details -->
        <div class="card-dsp p-4 mb-4">
            <h5 class="fw-bold text-dark mb-3">
                <iconify-icon icon="solar:buildings-bold" class="text-secondary me-1 align-middle"></iconify-icon>
                Enterprise Credentials
            </h5>

            <ul class="list-group list-group-flush small">
                <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                    <span class="text-muted">Entity Name:</span>
                    <strong class="text-dark">{{ $dsp->business_name ?: $dsp->applicant_name }}</strong>
                </li>
                <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                    <span class="text-muted">Constitution:</span>
                    <strong class="text-dark text-capitalize">{{ str_replace('_', ' ', $dsp->business_constitution) }}</strong>
                </li>
                <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                    <span class="text-muted">PAN:</span>
                    <strong class="font-monospace text-dark">{{ $dsp->pan ?: 'N/A' }}</strong>
                </li>
                <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                    <span class="text-muted">GSTIN:</span>
                    <strong class="font-monospace text-dark">{{ $dsp->gstin ?: 'N/A' }}</strong>
                </li>
                <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                    <span class="text-muted">Premises Address:</span>
                    <span class="text-dark text-end" style="max-width: 220px;">{{ $dsp->complete_address }}</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Payout Settings & Password Change Form -->
    <div class="col-lg-7">
        <div class="card-dsp p-4 mb-4">
            <h5 class="fw-bold text-dark mb-3">
                <iconify-icon icon="solar:card-send-bold" class="text-success me-1 align-middle"></iconify-icon>
                Default Cash Payout Settings
            </h5>
            <p class="micro text-muted mb-4">These bank and UPI details will be pre-filled when you request cash redemption from your 5% commission earnings.</p>

            <form action="{{ route('dsp.profile.update') }}" method="POST">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark small">Bank Account Number</label>
                        <input type="text" name="payout_account_number" class="form-control font-monospace" value="{{ old('payout_account_number', $dsp->payout_account_number) }}" placeholder="e.g. 10048728156">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark small">Bank IFSC Code</label>
                        <input type="text" name="payout_ifsc" class="form-control font-monospace text-uppercase" value="{{ old('payout_ifsc', $dsp->payout_ifsc) }}" placeholder="e.g. IDFB0042281">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark small">Bank Name</label>
                        <input type="text" name="payout_bank_name" class="form-control" value="{{ old('payout_bank_name', $dsp->payout_bank_name) }}" placeholder="e.g. IDFC FIRST Bank">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark small">Account Holder Name</label>
                        <input type="text" name="payout_holder_name" class="form-control" value="{{ old('payout_holder_name', $dsp->payout_holder_name ?: ($dsp->applicant_name ?: $dsp->business_name)) }}" placeholder="Name on account">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark small">Direct UPI ID / VPA</label>
                        <input type="text" name="payout_upi_id" class="form-control font-monospace" value="{{ old('payout_upi_id', $dsp->payout_upi_id) }}" placeholder="e.g. yourname@okhdfcbank or dsp@upi">
                        <span class="micro text-muted">Supports instant payouts via UPI</span>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="fw-bold text-dark mb-3">
                    <iconify-icon icon="solar:lock-password-bold" class="text-primary me-1 align-middle"></iconify-icon>
                    Change Portal Password
                </h5>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark small">Current Password</label>
                        <input type="password" name="current_password" class="form-control" placeholder="Required if changing password">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark small">New Password</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Minimum 6 characters">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark small">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" class="form-control" placeholder="Re-enter new password">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary fw-bold px-4 py-2 shadow-sm">
                    Save Profile & Payout Settings
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
