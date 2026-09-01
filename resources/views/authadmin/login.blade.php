@extends('adminlayouts.base', ['title' => 'Login'])

@section('content')
<section class="min-vh-100 d-flex justify-content-center align-items-center bg-light py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">

                        <div class="text-center mb-4">
                            <h3 class="fw-bold text-primary text-uppercase mb-2" style="letter-spacing: 2px; font-weight: 900;">NEXVIA</h3>
                            <h5 class="fw-bold text-dark mb-1">Admin Portal</h5>
                            <p class="text-muted fs-14 mb-0">Sign in to access your administrative dashboard</p>
                        </div>

                        <form method="POST" id="loginForm" action="{{ route('admin.login.submit') }}">
                            @csrf

                            @if ($errors->any())
                                <div class="alert alert-danger border-0 rounded-3 mb-4 py-2 px-3 fs-13">
                                    @foreach ($errors->all() as $error)
                                        <div><i class="bx bx-error-circle me-1"></i> {{ $error }}</div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mb-3">
                                <label for="emailaddress" class="form-label fw-semibold text-dark fs-13">Email Address</label>
                                <input class="form-control form-control-lg fs-14 rounded-3" type="email" name="email" id="emailaddress" required placeholder="name@company.com" value="{{ old('email') }}" autofocus>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold text-dark fs-13">Password</label>
                                <input class="form-control form-control-lg fs-14 rounded-3" type="password" required id="password" name="password" placeholder="••••••••">
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="checkbox-signin" name="remember" checked>
                                    <label class="form-check-label fs-13 text-muted" for="checkbox-signin">Remember me</label>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button class="btn btn-primary btn-lg rounded-3 fs-14 fw-semibold py-2.5" id="submitBtn" type="submit">
                                    Sign In to Dashboard
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.getElementById('loginForm').addEventListener('submit', function() {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Authenticating...';
    });
</script>
@endsection