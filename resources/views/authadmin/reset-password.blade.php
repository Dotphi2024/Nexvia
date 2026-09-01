@extends('layouts.auth', ['title' => 'Recover Password'])

@section('content')

<div class="col-xl-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card p-3 mb-0">
                <div class="card-body">

                    <div class="mb-0 border-0 p-md-5 p-lg-0 p-4">
                        <div class="mb-4 p-0 text-center">
                            @if(isset($settings->logo) && !empty($settings->logo))
                            <a  class="auth-logo">
                                <img src="{{ asset('logo/' . $settings->logo) }}" alt="logo-dark" class="mx-auto" height="28" />
                            </a>
                            @else
                            <a  class="auth-logo">
                                <img src="{{ asset('images/nexvia.png') }}" alt="logo-dark" class="mx-auto" height="35" />
                            </a>
                            @endif
                        </div>

                        <div class="auth-title-section mb-3 text-center">
                            <h3 class="text-dark fs-20 fw-medium mb-2">Welcome back</h3>
                        </div>

                        <div class="pt-0">
                            <form action="{{ route('admin.password.store') }}" method="POST" id="resetForm" class="my-4">
                                @csrf
                                <input type="hidden" name="token" value="{{ request()->route('token') }}">
                                <div class="form-group mb-3">
                                    <label for="emailaddress" class="form-label">Email address</label>
                                    <input class="form-control" type="email" name="email" id="emailaddress" required="" placeholder="Enter your email" value="{{ old('email') }}">
                                    @error('email')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input class="form-control" type="password" required="" id="password" name="password" placeholder="Enter your password">
                                    @error('password')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input class="form-control" type="password" required="" name="password_confirmation" id="password_confirmation" placeholder="Enter your confirm  password">
                                    @error('password')
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
                        </div>
                    </div>

                </div>
            </div>


        </div>
    </div>
</div>

<script>
    document.getElementById('resetForm').addEventListener('submit', function() {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
    });
</script>

@endsection