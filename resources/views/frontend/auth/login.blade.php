@extends('frontend.layouts.app')

@section('title', 'Sign In – NEXVIA Customer Portal')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center py-4">
        <div class="col-md-6 col-lg-5">
            <div class="card card-nexvia p-4 p-md-5 shadow-lg">
                <div class="text-center mb-4">
                    <a href="{{ route('home') }}" class="nexvia-brand-logo fs-2 d-block mb-1">NEXVIA</a>
                    <h5 class="fw-bold text-dark mb-1">Customer Login & Registration</h5>
                    <p class="small text-muted mb-0">Enter your mobile phone number to receive OTP</p>
                </div>

                <form action="{{ route('customer.send.otp') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Mobile Number *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">+91</span>
                            <input type="text" name="phone" class="form-control form-control-lg fs-6" placeholder="Enter 10 digit mobile number" required autofocus>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-nexvia-primary btn-lg w-100 py-3 mb-3 shadow">
                        Send OTP Code
                    </button>
                    
                    <p class="micro text-muted text-center mb-0">
                        By continuing, you agree to NEXVIA's Terms of Service & Privacy Policy.
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
