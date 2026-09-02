@extends('adminlayouts.vertical', ['title' => 'Booking Engine Controls'])

@section('title', 'Booking Engine Configuration & Rules')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Booking Engine Configuration</h4>
            <p class="text-muted small mb-0">Control global booking rules, 20% down payment, 60-day balance period, transfer eligibility, and reminder schedules</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border border-light-subtle shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold text-dark mb-0">Booking Engine Parameters (Section F Controls)</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.booking.engine.update') }}" method="POST">
                        @csrf

                        <!-- Default Booking % -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Default Booking Amount (%) *</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" name="default_booking_percentage" class="form-control" value="{{ $engineSettings['default_booking_percentage'] ?? 20 }}" required>
                                    <span class="input-group-text bg-light fw-bold">%</span>
                                </div>
                                <span class="micro text-muted">Default upfront booking down payment percentage.</span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Balance Period (Days) *</label>
                                <div class="input-group">
                                    <input type="number" name="balance_period_days" class="form-control" value="{{ $engineSettings['balance_period_days'] ?? 60 }}" required>
                                    <span class="input-group-text bg-light fw-bold">Days</span>
                                </div>
                                <span class="micro text-muted">Default balance payment deadline window.</span>
                            </div>
                        </div>

                        <!-- Transfer Allowed & Terms Version -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Transfer Allowed *</label>
                                <select name="transfer_allowed" class="form-select" required>
                                    <option value="1" {{ ($engineSettings['transfer_allowed'] ?? '1') == '1' ? 'selected' : '' }}>Yes - Receipt Ownership Transfer Allowed</option>
                                    <option value="0" {{ ($engineSettings['transfer_allowed'] ?? '1') == '0' ? 'selected' : '' }}>No - Disable Receipt Transfers</option>
                                </select>
                                <span class="micro text-muted">Allows customers to transfer booking receipts via OTP.</span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Booking Terms Declaration Version *</label>
                                <input type="text" name="booking_terms_version" class="form-control" value="{{ $engineSettings['booking_terms_version'] ?? 'v1.2' }}" required>
                                <span class="micro text-muted">Version tag of non-refundable terms declaration.</span>
                            </div>
                        </div>

                        <!-- Reminder Schedule & Expiry Handling -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Reminder Schedule (Days Remaining) *</label>
                                <input type="text" name="reminder_schedule_days" class="form-control font-monospace" value="{{ $engineSettings['reminder_schedule_days'] ?? '30,15,7,3,0' }}" required>
                                <span class="micro text-muted">Comma-separated days remaining to dispatch reminders.</span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Expiry / Closure Handling *</label>
                                <select name="expiry_handling" class="form-select" required>
                                    <option value="cancel_and_credit" {{ ($engineSettings['expiry_handling'] ?? '') == 'cancel_and_credit' ? 'selected' : '' }}>Cancel Booking & Issue Product Credit</option>
                                    <option value="extend_grace_period" {{ ($engineSettings['expiry_handling'] ?? '') == 'extend_grace_period' ? 'selected' : '' }}>Extend 7-Day Grace Period</option>
                                    <option value="forfeit" {{ ($engineSettings['expiry_handling'] ?? '') == 'forfeit' ? 'selected' : '' }}>Forfeit Non-Refundable Booking</option>
                                </select>
                                <span class="micro text-muted">Action taken when 60-day balance deadline passes.</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary fw-semibold px-4">Update Booking Engine Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
