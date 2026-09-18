@extends('adminlayouts.vertical', ['title' => 'DLS Farm Equipments – Products'])

@section('title', 'DLS Farm Equipment Products')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1">DLS Farm Equipments – Products</h4>
            <p class="text-muted small mb-0">Manage machinery, cultivators, pumps, stock, and booking prices</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.dls_farm_equipments.categories.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-category me-1"></i> Manage Categories
            </a>
            <a href="{{ route('admin.dls_farm_equipments.products.create') }}" class="btn btn-primary btn-sm fw-semibold px-3">
                <i class="bx bx-plus me-1"></i> Add Farm Product
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3 py-2 px-3 small" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter & Search Controls Bar -->
    <div class="card border border-light-subtle shadow-sm mb-3">
        <div class="card-body p-2">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <!-- Status & Deal Filter Pills -->
                <div class="btn-group btn-group-sm" role="group">
                    <a href="{{ route('admin.dls_farm_equipments.products.index', array_merge(request()->except('filter', 'page'), ['filter' => 'all'])) }}" 
                       class="btn {{ ($filter ?? 'all') === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}">
                        All Farm Products <span class="badge bg-secondary ms-1">{{ $totalCount }}</span>
                    </a>
                    <a href="{{ route('admin.dls_farm_equipments.products.index', array_merge(request()->except('filter', 'page'), ['filter' => 'trending'])) }}" 
                       class="btn {{ ($filter ?? '') === 'trending' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        Featured <span class="badge {{ ($filter ?? '') === 'trending' ? 'bg-white text-primary' : 'bg-secondary' }} ms-1">{{ $trendingCount }}</span>
                    </a>
                    <a href="{{ route('admin.dls_farm_equipments.products.index', array_merge(request()->except('filter', 'page'), ['filter' => 'instock'])) }}" 
                       class="btn {{ ($filter ?? '') === 'instock' ? 'btn-success' : 'btn-outline-secondary' }}">
                        In Stock
                    </a>
                    <a href="{{ route('admin.dls_farm_equipments.products.index', array_merge(request()->except('filter', 'page'), ['filter' => 'inactive'])) }}" 
                       class="btn {{ ($filter ?? '') === 'inactive' ? 'btn-warning' : 'btn-outline-secondary' }}">
                        Inactive <span class="badge bg-secondary ms-1">{{ $inactiveCount }}</span>
                    </a>
                </div>

                <!-- Category & Search Filters -->
                <form action="{{ route('admin.dls_farm_equipments.products.index') }}" method="GET" class="d-flex align-items-center gap-2 m-0">
                    @if(!empty($filter) && $filter !== 'all')
                        <input type="hidden" name="filter" value="{{ $filter }}">
                    @endif
                    <select name="category_id" class="form-select form-select-sm" style="min-width: 170px;" onchange="this.form.submit()">
                        <option value="">All Farm Categories</option>
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
                        <a href="{{ route('admin.dls_farm_equipments.products.index', ['filter' => $filter ?? 'all']) }}" class="btn btn-sm btn-light text-muted" title="Clear search">
                            <i class="bx bx-x"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card border border-light-subtle shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 50px;">Photo</th>
                            <th>Product Details</th>
                            <th>Category</th>
                            <th>Pricing (MRP / 20% Booking)</th>
                            <th>Stock & Badges</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td class="ps-3">
                                    <img src="{{ \App\Helpers\ImageHelper::resolve($product->main_image) }}" 
                                         alt="{{ $product->name }}" 
                                         class="rounded border" 
                                         width="44" height="44" 
                                         style="object-fit: cover;"
                                         onerror="this.onerror=null;this.src='{{ asset('images/no-image.png') }}';">
                                </td>
                                <td>
                                    <div class="fw-bold text-dark fs-14">{{ $product->name }}</div>
                                    <div class="text-muted small">
                                        @if($product->model_code)
                                            <span class="badge bg-light text-secondary border font-monospace me-1">{{ $product->model_code }}</span>
                                        @endif
                                        @if($product->sku)
                                            <span class="text-muted font-monospace micro">SKU: {{ $product->sku }}</span>
                                        @endif
                                    </div>
                                    @if($product->offer_text)
                                        <span class="badge bg-danger-subtle text-danger micro mt-1">{{ $product->offer_text }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">
                                        {{ $product->category->name ?? 'Farm Category' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark fs-14">₹{{ number_format($product->mrp, 2) }}</div>
                                    <div class="text-success small fw-semibold">
                                        20% Book: ₹{{ number_format($product->booking_amount, 2) }}
                                    </div>
                                    <div class="text-muted micro">
                                        Bal: ₹{{ number_format($product->balance_amount, 2) }}
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        @if($product->stock > 5)
                                            <span class="badge bg-success-subtle text-success">{{ $product->stock }} in stock</span>
                                        @elseif($product->stock > 0)
                                            <span class="badge bg-warning-subtle text-warning">{{ $product->stock }} left</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">Out of stock</span>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-1 mt-1">
                                        @if($product->referral_eligible)
                                            <span class="badge bg-primary-subtle text-primary micro" title="Referral Eligible">Ref: OK</span>
                                        @endif
                                        @if($product->self_dealer_eligible)
                                            <span class="badge bg-dark-subtle text-dark micro" title="Self Dealer Eligible">Dealer: OK</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <form action="{{ route('admin.dls_farm_equipments.products.status', $product->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm p-0 border-0" title="Toggle Status">
                                            @if($product->status === 'active')
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form action="{{ route('admin.dls_farm_equipments.products.featured', $product->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm p-0 border-0" title="Toggle Featured">
                                            @if($product->is_featured)
                                                <span class="badge bg-warning text-dark"><i class="bx bxs-star"></i> Featured</span>
                                            @else
                                                <span class="badge bg-light text-muted border"><i class="bx bx-star"></i> Regular</span>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('admin.dls_farm_equipments.products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Product">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.dls_farm_equipments.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this farm product?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Product">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bx bx-box fs-32 d-block mb-2 text-muted"></i>
                                    <h6>No DLS Farm Equipment products found</h6>
                                    <p class="small text-muted mb-3">Add categories and products under DLS Farm Equipments to get started.</p>
                                    <a href="{{ route('admin.dls_farm_equipments.products.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bx bx-plus me-1"></i> Add First Farm Product
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
                <div class="card-footer bg-white border-top py-3 d-flex justify-content-end">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
