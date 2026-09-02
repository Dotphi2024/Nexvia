@extends('frontend.layouts.app')

@section('title', 'NEXVIA – Smart Products. Smart Buying. Smart Earning Benefits.')

@section('content')

<!-- Main Hero Banner -->
<section class="py-5 bg-dark text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="badge badge-20-booking mb-3">🔥 Smart Buying Revolution</span>
                <h1 class="display-4 fw-extrabold text-white mb-3" style="letter-spacing: -1px;">
                    Book Your NEXVIA Product Today
                </h1>
                <p class="fs-5 text-light opacity-90 mb-4 fw-light">
                    Pay <strong class="text-warning">20% Booking Amount Now</strong> & Pay Balance Within <strong class="text-warning">60 Days</strong>. Transferable digital receipts & 100% brand warranty guarantee.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('products.index') }}" class="btn btn-nexvia-primary btn-lg px-4 py-3 shadow">Explore All Products</a>
                    <a href="{{ route('products.index', ['category' => 'electric-scooters']) }}" class="btn btn-outline-light btn-lg px-4 py-3 rounded-3">Electric Scooters</a>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <div class="p-4 rounded-4 bg-white bg-opacity-10 backdrop-blur border border-white border-opacity-20 shadow-lg">
                    <iconify-icon icon="solar:electric-scooter-bold-duotone" style="font-size: 100px; color: #60a5fa;"></iconify-icon>
                    <h3 class="fw-bold text-white mt-3">NEXVIA E4 Scooter</h3>
                    <p class="text-info mb-3">120 KM Range • 75 KM/H • Smart Touch Screen</p>
                    <div class="bg-dark rounded-3 p-3 text-start">
                        <div class="d-flex justify-content-between text-muted small mb-1">
                            <span>MRP: <del>₹85,000</del></span>
                            <span class="text-success fw-bold">Save Big</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fs-4 fw-bold text-warning">₹17,000</span>
                                <span class="small text-light ms-1">(20% Booking)</span>
                            </div>
                            <span class="badge bg-secondary">Balance ₹68,000 (60 Days)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Secondary Promo Banner -->
<section class="py-3 bg-primary text-white text-center">
    <div class="container">
        <span class="fw-bold">⚡ Special Offer:</span> Pay 20% Booking Amount Now – Balance Within 60 Days with 100% Transferable Receipt Guarantee!
    </div>
</section>

<!-- Category Grid -->
<section class="py-5 container">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Explore Categories</h2>
            <p class="text-muted mb-0">Select a category to view smart products & booking options</p>
        </div>
        <a href="{{ route('products.index') }}" class="text-primary fw-semibold text-decoration-none">View All →</a>
    </div>

    <div class="row g-4">
        @foreach($categories as $category)
            <div class="col-lg-3 col-md-4 col-6">
                <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="text-decoration-none">
                    <div class="card card-nexvia text-center p-4 h-100">
                        <div class="mb-3 text-primary">
                            <iconify-icon icon="{{ $category->icon ?? 'solar:box-minimalistic-bold-duotone' }}" style="font-size: 48px;"></iconify-icon>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">{{ $category->name }}</h5>
                        <span class="small text-muted">{{ $category->description ?? 'Smart Products' }}</span>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</section>

<!-- Featured Products Section -->
<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="badge bg-primary-subtle text-primary mb-2 fw-semibold">Featured Lineup</span>
                <h2 class="fw-bold text-dark mb-1">Smart Products available for 20% Booking</h2>
            </div>
            <a href="{{ route('products.index') }}" class="btn btn-nexvia-outline btn-sm">See All Products</a>
        </div>

        <div class="row g-4">
            @foreach($featuredProducts as $product)
                <div class="col-lg-3 col-md-6">
                    <div class="card card-nexvia h-100 d-flex flex-column">
                        <div class="position-relative p-4 text-center bg-light rounded-top-4 d-flex align-items-center justify-content-center" style="height: 200px;">
                            <span class="position-absolute top-0 start-0 m-3 badge bg-primary">20% Booking</span>
                            @if($product->main_image)
                                <img src="{{ asset($product->main_image) }}" alt="{{ $product->name }}" class="img-fluid" style="max-height: 160px; object-fit: contain;">
                            @else
                                <iconify-icon icon="{{ $product->category->icon ?? 'solar:box-bold-duotone' }}" style="font-size: 72px; color: #7c3aed;"></iconify-icon>
                            @endif
                        </div>
                        <div class="card-body d-flex flex-column">
                            <span class="small text-muted mb-1">{{ $product->category->name }}</span>
                            <h6 class="fw-bold text-dark mb-2 text-truncate" title="{{ $product->name }}">{{ $product->name }}</h6>
                            
                            <div class="mt-auto pt-3 border-top">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small text-muted">MRP:</span>
                                    <span class="fw-semibold text-dark">₹{{ number_format($product->mrp, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small fw-bold text-primary">Book Now (20%):</span>
                                    <span class="fs-5 fw-bold text-primary">₹{{ number_format($product->booking_amount, 2) }}</span>
                                </div>
                                <div class="bg-light p-2 rounded-2 text-center small text-secondary mb-3">
                                    Balance: <strong>₹{{ number_format($product->balance_amount, 2) }}</strong> (in 60 Days)
                                </div>

                                <a href="{{ route('products.show', $product->slug) }}" class="btn btn-nexvia-primary w-100">View Product Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Electric Mobility Highlight Section -->
@if($scooters->count() > 0)
<section class="py-5 container">
    <div class="p-5 rounded-5 bg-gradient text-white shadow-lg" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border: 1px solid #334155;">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <span class="badge bg-warning text-dark font-monospace mb-2 fs-6">NEXVIA ELECTRIC MOBILITY</span>
                <h2 class="display-6 fw-bold text-white mb-3">Electric Scooters Lineup</h2>
                <p class="text-slate-300 lead mb-4">Experience high performance eco-friendly mobility with low maintenance and smart app integration.</p>
                
                <div class="row g-3">
                    @foreach($scooters as $scooter)
                        <div class="col-12 bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-10 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold text-white mb-0">{{ $scooter->name }}</h6>
                                <span class="small text-info">Book @ ₹{{ number_format($scooter->booking_amount, 0) }}</span>
                            </div>
                            <a href="{{ route('products.show', $scooter->slug) }}" class="btn btn-sm btn-light fw-bold px-3">Book Now</a>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <iconify-icon icon="solar:electric-scooter-bold-duotone" style="font-size: 160px; color: #38bdf8;"></iconify-icon>
            </div>
        </div>
    </div>
</section>
@endif

@endsection
