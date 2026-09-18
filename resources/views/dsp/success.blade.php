<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted | NEXVIA™ DSP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f6fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 p-3">
    <div class="card border-0 shadow-lg p-4 p-md-5 text-center" style="max-width: 580px; border-radius: 16px;">
        <div class="avatar-lg mx-auto mb-3 bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 40px; margin: 0 auto;">
            <i class="bx bx-check-circle"></i>
        </div>
        <h3 class="fw-bold text-dark mb-2">DSP Application Submitted!</h3>
        <p class="text-muted">
            Thank you for applying to become an <strong>Authorised Delivery & Service Partner (DSP)</strong> for NEXVIA™.
        </p>

        @if($appNumber)
            <div class="p-3 bg-light rounded border border-light-subtle my-3">
                <span class="text-muted small d-block text-uppercase fw-semibold">Your Application Number</span>
                <h4 class="fw-bold text-primary font-monospace mb-0 mt-1">{{ $appNumber }}</h4>
            </div>
        @endif

        <p class="text-muted small mb-4">
            Our territory operations team will review your application, verify premises/service capabilities, and contact you for the next steps and DSP Agreement execution.
        </p>

        <div class="d-flex justify-content-center gap-2">
            <a href="{{ route('dsp.apply') }}" class="btn btn-outline-primary">
                Submit Another Application
            </a>
            <a href="{{ route('home') }}" class="btn btn-primary fw-semibold">
                Go to Portal
            </a>
        </div>
    </div>
</body>
</html>
