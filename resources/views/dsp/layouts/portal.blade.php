<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'DSP Partner Portal') – NEXVIA</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Iconify -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>

    <style>
        :root {
            --dsp-primary: #1e3a8a;
            --dsp-primary-hover: #172554;
            --dsp-accent: #0284c7;
            --dsp-success: #059669;
            --dsp-warning: #d97706;
            --dsp-bg: #f8fafc;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--dsp-bg);
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar-dsp {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-bottom: 3px solid #3b82f6;
        }

        .nav-link-dsp {
            color: #cbd5e1;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .nav-link-dsp:hover, .nav-link-dsp.active {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
        }

        .card-dsp {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: all 0.2s ease;
        }

        .card-stat {
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            padding: 1.25rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .badge-commission {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            color: #065f46;
            border: 1px solid #a7f3d0;
            font-weight: 700;
            padding: 0.35rem 0.65rem;
            border-radius: 8px;
        }

        .table-dsp th {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #64748b;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            padding: 0.85rem 1rem;
        }

        .table-dsp td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .micro {
            font-size: 0.75rem;
        }

        footer {
            margin-top: auto;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            padding: 1.25rem 0;
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Header Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dsp py-3 sticky-top shadow-sm">
        <div class="container-fluid px-lg-5">
            <a class="navbar-brand text-white fw-bold d-flex align-items-center gap-2" href="{{ route('dsp.dashboard') }}">
                <span class="bg-primary text-white p-2 rounded-3 d-inline-flex">
                    <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="fs-4"></iconify-icon>
                </span>
                <div>
                    <span class="fs-5 tracking-wide text-white">NEXVIA<span class="text-primary">™</span></span>
                    <span class="badge bg-primary text-white ms-2 micro">DSP PORTAL</span>
                </div>
            </a>

            <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#dspNavbar">
                <iconify-icon icon="solar:hamburger-menu-bold" class="fs-3 text-white"></iconify-icon>
            </button>

            <div class="collapse navbar-collapse" id="dspNavbar">
                <ul class="navbar-nav me-auto ms-lg-4 mb-2 mb-lg-0 gap-1">
                    <li class="nav-item">
                        <a class="nav-link-dsp {{ request()->routeIs('dsp.dashboard') ? 'active' : '' }}" href="{{ route('dsp.dashboard') }}">
                            <iconify-icon icon="solar:chart-square-bold"></iconify-icon> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-dsp {{ request()->routeIs('dsp.deliveries*') ? 'active' : '' }}" href="{{ route('dsp.deliveries') }}">
                            <iconify-icon icon="solar:box-bold"></iconify-icon> Territory Deliveries
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-dsp {{ request()->routeIs('dsp.wallet') ? 'active' : '' }}" href="{{ route('dsp.wallet') }}">
                            <iconify-icon icon="solar:wallet-money-bold"></iconify-icon> 5% Earnings & Cash Wallet
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-dsp {{ request()->routeIs('dsp.profile') ? 'active' : '' }}" href="{{ route('dsp.profile') }}">
                            <iconify-icon icon="solar:map-point-bold"></iconify-icon> Territory & Profile
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <!-- Wallet Quick Pill -->
                    <a href="{{ route('dsp.wallet') }}" class="text-decoration-none">
                        <div class="bg-success bg-opacity-20 border border-success border-opacity-25 px-3 py-2 rounded-3 text-white d-flex align-items-center gap-2">
                            <iconify-icon icon="solar:wallet-bold" class="text-success fs-5"></iconify-icon>
                            <div>
                                <div class="micro text-white-50 lh-1">Cash Balance</div>
                                <div class="fw-bold text-success fs-6 lh-1">₹{{ number_format(Auth::guard('dsp')->user()->wallet_balance ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </a>

                    <!-- User Profile Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-outline-light btn-sm rounded-3 dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                            <iconify-icon icon="solar:user-circle-bold" class="fs-5"></iconify-icon>
                            <span class="small fw-semibold">{{ Str::limit(Auth::guard('dsp')->user()->business_name ?: Auth::guard('dsp')->user()->applicant_name, 18) }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg rounded-3 border-0 py-2">
                            <li class="px-3 py-2 border-bottom">
                                <span class="d-block fw-bold text-dark">{{ Auth::guard('dsp')->user()->business_name ?: Auth::guard('dsp')->user()->applicant_name }}</span>
                                <span class="d-block micro text-muted">{{ Auth::guard('dsp')->user()->mobile }}</span>
                                <span class="badge bg-success-subtle text-success micro mt-1">Authorised DSP</span>
                            </li>
                            <li><a class="dropdown-item py-2" href="{{ route('dsp.profile') }}"><iconify-icon icon="solar:settings-bold" class="me-2"></iconify-icon> Bank & Profile</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('dsp.wallet') }}"><iconify-icon icon="solar:hand-money-bold" class="me-2"></iconify-icon> Redeem Cash</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('dsp.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger py-2">
                                        <iconify-icon icon="solar:logout-2-bold" class="me-2"></iconify-icon> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="py-4">
        <div class="container-fluid px-lg-5">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:check-circle-bold" class="fs-4 text-success"></iconify-icon>
                        <div>{{ session('success') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:info-circle-bold" class="fs-4 text-info"></iconify-icon>
                        <div>{{ session('info') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <iconify-icon icon="solar:danger-triangle-bold" class="fs-4 text-danger"></iconify-icon>
                        <strong>Please check the following errors:</strong>
                    </div>
                    <ul class="mb-0 ps-4 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container-fluid px-lg-5 text-center text-muted small">
            <div>© {{ date('Y') }} NEXVIA™ – Authorised Delivery & Service Partner (DSP) Network. All rights reserved.</div>
            <div class="micro mt-1 text-secondary">Secured with 256-bit encryption. Territory commission rate: 5.0% on verified delivery completion.</div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
