@extends('frontend.layouts.app')

@section('title', ($selectedCategory ? $selectedCategory->name : 'All Products') . ' – NEXVIA Catalog')

@section('content')
<div class="container py-4">
    <div class="row g-4">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="card card-nexvia p-4">
                <h5 class="fw-bold text-dark mb-3">Categories</h5>
                <div class="list-group list-group-flush">
                    <a href="{{ route('products.index') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 {{ !request('category') ? 'fw-bold text-primary' : 'text-secondary' }}">
                        All Categories
                    </a>
                    @foreach($categories as $cat)
                        <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="list-group-item list-group-item-action border-0 px-0 py-2 {{ request('category') == $cat->slug ? 'fw-bold text-primary' : 'text-secondary' }}">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold text-dark mb-0">{{ $selectedCategory ? $selectedCategory->name : 'All Smart Products' }}</h3>
                    <span class="small text-muted">Showing {{ $products->total() }} products with 20% Booking option</span>
                </div>
            </div>

            @if($products->count() > 0)
                <div class="row g-4">
                    @foreach($products as $product)
                        <div class="col-md-4 col-sm-6">
                            <div class="card card-nexvia h-100 d-flex flex-column">
                                <div class="position-relative p-4 text-center bg-light rounded-top-4 d-flex align-items-center justify-content-center" style="height: 180px;">
                                    <span class="position-absolute top-0 start-0 m-3 badge bg-primary">20% Booking</span>
                                    @if($product->main_image)
                                        <img src="{{ asset($product->main_image) }}" alt="{{ $product->name }}" class="img-fluid" style="max-height: 140px; object-fit: contain;">
                                    @else
                                        <iconify-icon icon="{{ $product->category->icon ?? 'solar:box-bold-duotone' }}" style="font-size: 64px; color: #7c3aed;"></iconify-icon>
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

                                        <a href="{{ route('products.show', $product->slug) }}" class="btn btn-nexvia-primary w-100">View Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $products->links() }}
                </div>
            @else
                <div class="card card-nexvia p-5 text-center">
                    <iconify-icon icon="solar:box-minimalistic-line-duotone" style="font-size: 64px;" class="text-muted mb-3"></iconify-icon>
                    <h4>No products found</h4>
                    <p class="text-muted">Try selecting a different category or clearing search filter.</p>
                    <div>
                        <a href="{{ route('products.index') }}" class="btn btn-nexvia-primary">Clear Filters</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
