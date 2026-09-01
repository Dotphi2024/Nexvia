@extends('adminlayouts.vertical', ['title' => 'Dashboard'])

@section('content')
<div class="container-fluid py-4">

    <!-- Top Greeting Bar -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark fs-22">
                Welcome back, {{ Auth::guard('admin')->user()->name ?? 'Admin' }} 👋
            </h3>
            <p class="text-muted mb-0 fs-13">
                <i class="bx bx-calendar me-1"></i>{{ \Carbon\Carbon::now()->setTimezone('Asia/Kolkata')->format('l, d F Y | h:i A') }} (IST)
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-4">
                <div class="card-body text-center py-5">
                    <h4 class="fw-bold text-primary mb-2">Admin Dashboard</h4>
                    <p class="text-muted">You are logged in to the Admin Panel.</p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection