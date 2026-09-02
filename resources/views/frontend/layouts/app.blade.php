<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'NEXVIA – Smart Products. Smart Buying. Smart Earning Benefits.')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <style>
        :root {
            --nexvia-primary: #7c3aed;
            --nexvia-primary-dark: #6d28d9;
            --nexvia-secondary: #0f172a;
            --nexvia-accent: #f59e0b;
            --nexvia-bg: #f8fafc;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--nexvia-bg);
            color: #1e293b;
        }
        .navbar-nexvia {
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            padding: 12px 0;
            sticky: top;
        }
        .nexvia-brand-logo {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: var(--nexvia-primary);
            text-decoration: none;
        }
        .btn-nexvia-primary {
            background-color: var(--nexvia-primary);
            color: #ffffff;
            font-weight: 600;
            border-radius: 10px;
            padding: 10px 22px;
            border: none;
            transition: all 0.2s ease;
        }
        .btn-nexvia-primary:hover {
            background-color: var(--nexvia-primary-dark);
            color: #ffffff;
            transform: translateY(-1px);
        }
        .btn-nexvia-outline {
            border: 2px solid var(--nexvia-primary);
            color: var(--nexvia-primary);
            font-weight: 600;
            border-radius: 10px;
            padding: 9px 20px;
            transition: all 0.2s ease;
        }
        .btn-nexvia-outline:hover {
            background-color: var(--nexvia-primary);
            color: #ffffff;
        }
        .card-nexvia {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }
        .card-nexvia:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.08);
            border-color: #cbd5e1;
        }
        .badge-20-booking {
            background: #f3e8ff;
            color: #6b21a8;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
        }
        .badge-60-days {
            background: #fef3c7;
            color: #92400e;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
        }
        .footer-nexvia {
            background: #0f172a;
            color: #94a3b8;
            padding: 50px 0 20px;
            margin-top: 80px;
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Header Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-nexvia sticky-top">
        <div class="container">
            <a class="nexvia-brand-logo d-flex align-items-center gap-2" href="{{ route('home') }}">
                <iconify-icon icon="solar:shield-star-bold" class="fs-2" style="color: #7c3aed;"></iconify-icon>
                <span style="color: #7c3aed; font-weight: 800;">NEXVIA</span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <!-- Search Bar -->
                <form class="d-flex mx-auto col-lg-5 my-2 my-lg-0" action="{{ route('products.index') }}" method="GET">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control rounded-start-pill ps-4 py-2 border-end-0" placeholder="Search TV, AC, Scooters, Appliances..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary bg-white rounded-end-pill px-3 border-start-0" style="color: #7c3aed;" type="submit">
                            <iconify-icon icon="solar:magnifer-linear" class="fs-5 align-middle"></iconify-icon>
                        </button>
                    </div>
                </form>

                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    <li class="nav-item">
                        <a class="nav-link fw-semibold text-dark me-2" href="{{ route('products.index') }}">All Products</a>
                    </li>

                    @auth('web')
                        <li class="nav-item me-2">
                            <a class="btn btn-nexvia-outline btn-sm d-flex align-items-center gap-1" href="{{ route('customer.dashboard') }}">
                                <iconify-icon icon="solar:user-bold-duotone" class="fs-5"></iconify-icon>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <form action="{{ route('customer.logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link text-danger text-decoration-none fw-semibold">Logout</button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item me-2">
                            <a class="btn btn-nexvia-outline" href="{{ route('customer.login') }}">Sign In</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-nexvia-primary" href="{{ route('customer.login') }}">Get Started</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Alert Flash Messages -->
    <div class="container mt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
                <strong>Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-4" role="alert">
                <strong>Notice:</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show rounded-4" role="alert">
                <strong>Info:</strong> {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer-nexvia">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <a class="nexvia-brand-logo d-inline-block mb-3" style="color: #a78bfa;" href="{{ route('home') }}">NEXVIA</a>
                    <p class="small text-secondary">Smart Products. Smart Buying. Smart Earning Benefits. Pay just 20% booking amount upfront and clear balance within 60 days with transferable digital receipts.</p>
                </div>
                <div class="col-lg-2 col-6">
                    <h6 class="text-white fw-bold mb-3">Categories</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="{{ route('products.index', ['category' => 'electric-scooters']) }}" class="text-decoration-none text-secondary">Electric Scooters</a></li>
                        <li class="mb-2"><a href="{{ route('products.index', ['category' => 'smart-led-tv']) }}" class="text-decoration-none text-secondary">Smart LED TV</a></li>
                        <li class="mb-2"><a href="{{ route('products.index', ['category' => 'refrigerator']) }}" class="text-decoration-none text-secondary">Refrigerators</a></li>
                        <li class="mb-2"><a href="{{ route('products.index', ['category' => 'air-conditioner']) }}" class="text-decoration-none text-secondary">Air Conditioners</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-6">
                    <h6 class="text-white fw-bold mb-3">NEXVIA Model</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><span class="text-secondary">✓ 20% Down Payment</span></li>
                        <li class="mb-2"><span class="text-secondary">✓ 60-Day Balance Window</span></li>
                        <li class="mb-2"><span class="text-secondary">✓ Transferable Receipt</span></li>
                        <li class="mb-2"><span class="text-secondary">✓ Brand Warranty Assurance</span></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h6 class="text-white fw-bold mb-3">Customer Support</h6>
                    <p class="small text-secondary mb-1">Email: support@nexvia.com</p>
                    <p class="small text-secondary mb-1">Helpline: +91 1800-NEXVIA-99</p>
                    <p class="small text-secondary">Hours: Mon - Sat (9 AM - 7 PM)</p>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <div class="text-center small text-secondary">
                © {{ date('Y') }} NEXVIA Ecosystem. All Rights Reserved.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('styles')
</body>
</html>
