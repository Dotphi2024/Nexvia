@extends('frontend.layouts.app')

@section('title', $product->name . ' – NEXVIA Smart Buying')

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.index', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row g-5">
        <!-- Image / Preview Section -->
        <div class="col-lg-6">
            <div class="card card-nexvia p-4 text-center bg-white">
                <span class="badge bg-success align-self-start mb-3 px-3 py-2 fs-6">In Stock ({{ $product->stock }} units)</span>
                
                @if($product->main_image)
                    <img src="{{ asset($product->main_image) }}" alt="{{ $product->name }}" class="img-fluid rounded-3 my-3" style="max-height: 380px; object-fit: contain;">
                @else
                    <iconify-icon icon="{{ $product->category->icon ?? 'solar:box-bold-duotone' }}" style="font-size: 180px; color: #2563eb;" class="my-4"></iconify-icon>
                @endif

                @if(is_array($product->gallery) && count($product->gallery) > 0)
                    <div class="d-flex justify-content-center gap-2 mt-3 overflow-auto">
                        @foreach($product->gallery as $gImg)
                            <img src="{{ asset($gImg) }}" class="rounded border p-1" width="60" height="60" style="object-fit: cover;">
                        @endforeach
                    </div>
                @endif

                <div class="d-flex justify-content-center gap-3 mt-4 pt-3 border-top">
                    <div class="p-2 border rounded-3 bg-light"><iconify-icon icon="solar:shield-check-bold-duotone" class="text-primary fs-3"></iconify-icon></div>
                    <div class="p-2 border rounded-3 bg-light"><iconify-icon icon="solar:delivery-bold-duotone" class="text-primary fs-3"></iconify-icon></div>
                    <div class="p-2 border rounded-3 bg-light"><iconify-icon icon="solar:history-bold-duotone" class="text-primary fs-3"></iconify-icon></div>
                </div>
            </div>
        </div>

        <!-- Product Details & Pricing Model -->
        <div class="col-lg-6">
            <span class="text-uppercase text-primary fw-bold small tracking-wider">{{ $product->category->name }}</span>
            <h2 class="fw-bold text-dark mb-2">{{ $product->name }}</h2>
            @if($product->model_code)
                <p class="text-muted small mb-3">Model Code: <span class="font-monospace text-dark fw-bold">{{ $product->model_code }}</span></p>
            @endif

            <!-- 20% PRICE DISPLAY MODEL CARD -->
            <div class="card p-4 rounded-4 bg-light border-2 border-primary mb-4 shadow-sm">
                <div class="row align-items-center text-center text-md-start g-3">
                    <div class="col-md-6 border-end-md">
                        <span class="text-muted small">Total Product Price (MRP):</span>
                        <h4 class="fw-bold text-secondary text-decoration-line-through mb-0">₹{{ number_format($product->mrp, 2) }}</h4>
                    </div>
                    <div class="col-md-6">
                        <span class="badge bg-primary text-white mb-1">Pay 20% Upfront</span>
                        <h3 class="fw-extrabold text-primary mb-0">₹{{ number_format($product->booking_amount, 2) }}</h3>
                    </div>
                </div>

                <hr class="my-3 border-secondary border-opacity-25">

                <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-3 border">
                    <div>
                        <span class="fw-bold text-dark d-block">Balance Payment: ₹{{ number_format($product->balance_amount, 2) }}</span>
                        <span class="small text-muted">Must be cleared within <strong>60 Days</strong> of booking.</span>
                    </div>
                    <span class="badge bg-warning text-dark px-3 py-2 fw-bold">60 Days Window</span>
                </div>
            </div>

            <!-- Action Buttons: Book Now vs Full Payment -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <a href="{{ route('booking.checkout', ['slug' => $product->slug, 'type' => 'booking_20']) }}" class="btn btn-nexvia-primary btn-lg w-100 py-3 shadow">
                        <iconify-icon icon="solar:ticket-bold" class="me-1 fs-5 align-middle"></iconify-icon>
                        BOOK NOW (₹{{ number_format($product->booking_amount, 0) }})
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="{{ route('booking.checkout', ['slug' => $product->slug, 'type' => 'full_payment']) }}" class="btn btn-nexvia-outline btn-lg w-100 py-3">
                        BUY FULL PAYMENT (₹{{ number_format($product->mrp, 0) }})
                    </a>
                </div>
            </div>

            <!-- Non-Refundable Policy Notice -->
            <div class="alert alert-warning border-0 rounded-3 d-flex align-items-start gap-2 mb-4">
                <iconify-icon icon="solar:info-circle-bold" class="fs-4 text-warning flex-shrink-0 mt-1"></iconify-icon>
                <div class="small">
                    <strong>Refund Policy:</strong> The 20% Booking Amount is non-refundable, but 100% <strong>Transferable</strong> to any friend or buyer via receipt transfer.
                </div>
            </div>

            <!-- Key Features & Specs -->
            <div class="card card-nexvia p-4 mb-4">
                <h5 class="fw-bold text-dark mb-3">Key Features</h5>
                @if(is_array($product->key_features))
                    <ul class="list-unstyled mb-0">
                        @foreach($product->key_features as $feature)
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:check-circle-bold" class="text-success fs-5"></iconify-icon>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted small mb-0">Premium build quality and high performance guarantee.</p>
                @endif
            </div>

            <!-- Technical Specifications -->
            @if(is_array($product->specs))
                <div class="card card-nexvia p-4 mb-4">
                    <h5 class="fw-bold text-dark mb-3">Technical Specifications</h5>
                    <table class="table table-sm table-striped mb-0">
                        <tbody>
                            @foreach($product->specs as $key => $val)
                                <tr>
                                    <th class="text-muted w-40">{{ $key }}</th>
                                    <td class="fw-semibold text-dark">{{ $val }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- Warranty & Installation -->
            <div class="row g-3">
                <div class="col-6">
                    <div class="p-3 rounded-3 bg-light border">
                        <span class="small text-muted d-block">Warranty</span>
                        <strong class="text-dark small">{{ $product->warranty_info }}</strong>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 bg-light border">
                        <span class="small text-muted d-block">Installation</span>
                        <strong class="text-dark small">{{ $product->installation_info }}</strong>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
