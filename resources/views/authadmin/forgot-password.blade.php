@extends('layouts.auth', ['title' => 'Email Verification'])

@section('content')

<div class="col-md-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card p-3 mb-0">
                <div class="card-body">

                    <div class="mb-0 border-0 p-md-5 p-lg-0 p-4">
                        <div class="mb-4 p-0 text-center">
                            @if(isset($settings->logo) && !empty($settings->logo))
                            <a class="auth-logo">
                                <img src="{{ asset('logo/' . $settings->logo) }}" alt="logo-dark" class="mx-auto" height="28" />
                            </a>
                            @else
                            <a class="auth-logo">
                                <img src="{{ asset('images/nexvia.png') }}" alt="logo-dark" class="mx-auto" height="35" />
                            </a>
                            @endif
                        </div>

                        <div class="auth-title-section mb-3 text-center mt-2">
                            <h3 class="text-dark fs-20 fw-medium mb-2">Reset Password</h3>
                            <p class="text-muted fs-15">Enter the email address associated with your account and we will send you a link to reset your password.</p>
                        </div>

                        <div class="pt-0">
                            <form action="{{ route('admin.password.email') }}" method="POST" id="resetForm" class="my-4">
                                @csrf

                                <div class="form-group mb-3">
                                    <label for="emailaddress" class="form-label">Email address</label>
                                    <input class="form-control" type="email" name="email" id="emailaddress" required="" placeholder="Enter your email" value="{{ old('email') }}">
                                    @error('email')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-0 row">
                                    <div class="col-12">
                                        <div class="d-grid">
                                            <button class="btn btn-primary" type="submit" id="submitBtn"> Submit </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="mt-3 text-center">
                                <p class="mb-0">Already have an account ? <a href="{{ route('admin.login.page') }}"
                                        class="fw-medium text-primary"> Login here </a> </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.getElementById('resetForm').addEventListener('submit', function () {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
    });
</script>
@endsection