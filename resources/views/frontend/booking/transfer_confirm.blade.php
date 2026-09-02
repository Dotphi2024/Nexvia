@extends('frontend.layouts.app')

@section('title', 'Confirm Booking Transfer')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card card-nexvia p-4 p-md-5 text-center shadow-lg">
                <div class="mb-3">
                    <iconify-icon icon="solar:shield-warning-bold-duotone" style="font-size: 64px; color: #f59e0b;"></iconify-icon>
                </div>

                <h4 class="fw-bold text-dark mb-2">Confirm Booking Transfer</h4>
                <p class="small text-muted mb-4">
                    Transferring Receipt <strong>#{{ $transfer->booking->booking_number }}</strong> to <strong>{{ $transfer->to_name }}</strong> ({{ $transfer->to_phone }}).
                </p>

                <form action="{{ route('booking.transfer.process', $transfer->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Enter 6-Digit Transfer OTP *</label>
                        <input type="text" name="otp" class="form-control form-control-lg text-center font-monospace fs-3 tracking-widest" maxlength="6" placeholder="******" required autofocus>
                    </div>

                    <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold py-3 shadow mb-3">
                        Verify OTP & Complete Transfer
                    </button>
                    <a href="{{ route('customer.dashboard') }}" class="text-secondary small text-decoration-none">Cancel Transfer</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
