@extends('adminlayouts.vertical', ['title' => 'Products'])

@section('title', 'Product Inventory Management')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1">Products & Inventory</h4>
            <p class="text-muted small mb-0">Manage catalog, pricing, stock levels, and spotlight deals</p>
        </div>
        <div>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm fw-semibold px-3">
                <i class="bx bx-plus me-1"></i> Add Product
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3 py-2 px-3 small" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Clean Filter & Search Controls Bar -->
    <div class="card border border-light-subtle shadow-sm mb-3">
        <div class="card-body p-2">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <!-- Status & Deal Filter Pills -->
                <div class="btn-group btn-group-sm" role="group">
                    <a href="{{ route('admin.products.index', array_merge(request()->except('filter', 'page'), ['filter' => 'all'])) }}" 
                       class="btn {{ ($filter ?? 'all') === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}">
                        All Products <span class="badge bg-secondary ms-1">{{ $totalCount }}</span>
                    </a>
                    <a href="{{ route('admin.products.index', array_merge(request()->except('filter', 'page'), ['filter' => 'trending'])) }}" 
                       class="btn {{ ($filter ?? '') === 'trending' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        Featured <span class="badge {{ ($filter ?? '') === 'trending' ? 'bg-white text-primary' : 'bg-secondary' }} ms-1">{{ $trendingCount }}</span>
                    </a>
                    <a href="{{ route('admin.products.index', array_merge(request()->except('filter', 'page'), ['filter' => 'instock'])) }}" 
                       class="btn {{ ($filter ?? '') === 'instock' ? 'btn-success' : 'btn-outline-secondary' }}">
                        In Stock
                    </a>
                    <a href="{{ route('admin.products.index', array_merge(request()->except('filter', 'page'), ['filter' => 'inactive'])) }}" 
                       class="btn {{ ($filter ?? '') === 'inactive' ? 'btn-warning' : 'btn-outline-secondary' }}">
                        Inactive <span class="badge bg-secondary ms-1">{{ $inactiveCount }}</span>
                    </a>
                </div>

                <!-- Category & Search Filters -->
                <form action="{{ route('admin.products.index') }}" method="GET" class="d-flex align-items-center gap-2 m-0">
                    @if(!empty($filter) && $filter !== 'all')
                        <input type="hidden" name="filter" value="{{ $filter }}">
                    @endif
                    <select name="category_id" class="form-select form-select-sm" style="min-width: 170px;" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (string)($categoryId ?? '') === (string)$cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>

                    <div class="input-group input-group-sm" style="max-width: 220px;">
                        <input type="text" name="q" value="{{ $search ?? '' }}" class="form-control" placeholder="Search product...">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bx bx-search"></i>
                        </button>
                    </div>

                    @if(!empty($categoryId) || !empty($search))
                        <a href="{{ route('admin.products.index', ['filter' => $filter ?? 'all']) }}" class="btn btn-sm btn-light text-muted" title="Clear search">
                            <i class="bx bx-x"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Product Catalog Table -->
    <div class="card border border-light-subtle shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase fs-11 text-muted">
                        <tr>
                            <th style="width: 50px;">Image</th>
                            <th>Product Information</th>
                            <th>Category</th>
                            <th>Price & 20% Token</th>
                            <th>Stock</th>
                            <th class="text-center">Featured</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-3" style="width: 130px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <!-- Image -->
                                <td>
                                    <img src="{{ \App\Helpers\ImageHelper::resolve($product->main_image) }}" 
                                         alt="{{ $product->name }}" 
                                         class="rounded border" 
                                         width="44" 
                                         height="44" 
                                         style="object-fit: cover;"
                                         onerror="this.onerror=null;this.src='{{ asset('images/no-image.png') }}';">
                                </td>

                                <!-- Product Info -->
                                <td>
                                    <div class="mb-1">
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="fw-bold text-dark text-decoration-none fs-13">
                                            {{ $product->name }}
                                        </a>
                                    </div>
                                    <div class="text-muted micro d-flex gap-2 align-items-center">
                                        <span>Model: <span class="text-dark">{{ $product->model_code ?? 'NEX-STD' }}</span></span>
                                        <span>•</span>
                                        <span>SKU: <span class="text-dark">{{ $product->sku ?? '—' }}</span></span>
                                    </div>
                                </td>

                                <!-- Category -->
                                <td>
                                    <span class="badge bg-light text-secondary border font-monospace fs-11">
                                        {{ $product->category->name ?? 'Uncategorized' }}
                                    </span>
                                </td>

                                <!-- Pricing & 20% Booking -->
                                <td>
                                    <div class="fw-bold text-dark fs-13">₹{{ number_format($product->mrp, 2) }}</div>
                                    <div class="text-primary micro">Token: ₹{{ number_format($product->booking_amount ?: ($product->mrp * 0.20), 2) }} (20%)</div>
                                </td>

                                <!-- Stock -->
                                <td>
                                    @if($product->stock > 0)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle fw-semibold">
                                            {{ $product->stock }} in stock
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-semibold">
                                            Out of stock
                                        </span>
                                    @endif
                                </td>

                                <!-- Featured Toggle -->
                                <td class="text-center">
                                    <form action="{{ route('admin.products.featured', $product->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @if($product->is_featured)
                                            <button type="submit" class="btn btn-sm btn-primary-subtle text-primary border border-primary-subtle py-0 px-2 fs-11 rounded-pill fw-semibold" title="Click to remove from featured">
                                                Featured
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-2 fs-11 rounded-pill" title="Click to mark as featured">
                                                + Feature
                                            </button>
                                        @endif
                                    </form>
                                </td>

                                <!-- Status Toggle -->
                                <td class="text-center">
                                    <form action="{{ route('admin.products.status', $product->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm p-0 border-0" title="Click to toggle status">
                                            <span class="badge {{ $product->status === 'active' ? 'bg-success' : 'bg-secondary' }} rounded-pill px-2">
                                                {{ ucfirst($product->status) }}
                                            </span>
                                        </button>
                                    </form>
                                </td>

                                <!-- Action Buttons -->
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary py-0 px-2 fs-12">
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Delete this product?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2 fs-12">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bx bx-package fs-32 d-block mx-auto mb-2 text-secondary opacity-50"></i>
                                    <p class="mb-0 fw-semibold">No products found matching the criteria.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($products->hasPages())
                <div class="p-3 border-top bg-white d-flex justify-content-end">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
