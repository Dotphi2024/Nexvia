@extends('frontend.layouts.app')

@section('title', 'Verify OTP – NEXVIA')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center py-4">
        <div class="col-md-6 col-lg-5">
            <div class="card card-nexvia p-4 p-md-5 text-center shadow-lg">
                <h4 class="fw-bold text-dark mb-2">Enter Verification Code</h4>
                <p class="small text-muted mb-4">OTP code sent to <strong>+91 {{ $phone }}</strong></p>

                <form action="{{ route('customer.verify.otp') }}" method="POST">
                    @csrf
                    <input type="hidden" name="phone" value="{{ $phone }}">

                    <div class="mb-4">
                        <input type="text" name="otp" class="form-control form-control-lg text-center font-monospace fs-3 tracking-widest" maxlength="6" placeholder="******" required autofocus>
                    </div>

                    <button type="submit" class="btn btn-nexvia-primary btn-lg w-100 py-3 mb-3 shadow">
                        Verify & Continue
                    </button>
                    
                    <a href="{{ route('customer.login') }}" class="text-secondary small text-decoration-none">Change Mobile Number</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
