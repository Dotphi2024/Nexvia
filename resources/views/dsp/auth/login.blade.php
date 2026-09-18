<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DSP Partner Login – NEXVIA™</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Iconify -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .login-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 460px;
        }

        .login-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            padding: 2.25rem 2rem;
            color: #ffffff;
            text-align: center;
            position: relative;
        }

        .btn-dsp {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            color: #ffffff;
            font-weight: 700;
            padding: 0.85rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
            transition: all 0.2s ease;
        }

        .btn-dsp:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .form-control {
            border-radius: 12px;
            padding: 0.8rem 1rem;
            border: 1.5px solid #e2e8f0;
        }

        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <div class="d-inline-flex bg-white text-primary p-3 rounded-4 shadow-sm mb-3">
                <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="fs-1"></iconify-icon>
            </div>
            <h4 class="fw-bold mb-1">DSP Partner Login</h4>
            <p class="text-white-50 small mb-0">Authorised Delivery & Service Partner Portal</p>
        </div>

        <div class="p-4 p-md-4">
            @if(session('success'))
                <div class="alert alert-success small rounded-3 mb-3" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger small rounded-3 mb-3" role="alert">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('dsp.login.post') }}" method="POST">
                @csrf

                <!-- Mobile Number -->
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-dark">Registered Mobile Number *</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0 rounded-start-3">
                            <iconify-icon icon="solar:phone-bold"></iconify-icon>
                        </span>
                        <input type="text" name="mobile" class="form-control border-start-0" placeholder="e.g. 9876543210" value="{{ old('mobile') }}" required autofocus>
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-dark d-flex justify-content-between">
                        <span>Password *</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0 rounded-start-3">
                            <iconify-icon icon="solar:lock-password-bold"></iconify-icon>
                        </span>
                        <input type="password" name="password" id="passwordInput" class="form-control border-start-0 border-end-0" placeholder="Enter your portal password" required>
                        <button class="btn btn-outline-secondary border-start-0 rounded-end-3" type="button" onclick="togglePass()">
                            <iconify-icon icon="solar:eye-bold" id="eyeIcon"></iconify-icon>
                        </button>
                    </div>
                    <div class="form-text text-muted micro mt-1">
                        Default initial password for approved DSPs is <code>dsp@123</code> or your phone suffix.
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="rememberCheck" value="1">
                        <label class="form-check-label small text-muted" for="rememberCheck">
                            Keep me logged in
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-dsp w-100 py-3 mb-3">
                    <iconify-icon icon="solar:login-2-bold" class="me-1 align-middle fs-5"></iconify-icon>
                    Access DSP Partner Portal
                </button>
            </form>

            <div class="text-center pt-3 border-top">
                <p class="text-muted small mb-1">Not yet registered as an Authorised Partner?</p>
                <a href="{{ route('dsp.apply') }}" class="fw-bold text-primary text-decoration-none small">
                    <iconify-icon icon="solar:document-add-bold" class="align-middle me-1"></iconify-icon>
                    Apply for NEXVIA™ DSP Franchise
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePass() {
            const passInput = document.getElementById('passwordInput');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                eyeIcon.setAttribute('icon', 'solar:eye-closed-bold');
            } else {
                passInput.type = 'password';
                eyeIcon.setAttribute('icon', 'solar:eye-bold');
            }
        }
    </script>
</body>
</html>
