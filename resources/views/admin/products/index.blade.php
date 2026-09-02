@extends('adminlayouts.vertical', ['title' => 'Products'])

@section('title', 'Product Inventory Management')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Product Inventory & Catalog Controls</h4>
            <p class="text-muted small mb-0">Manage smart products, SKU, images, video URLs, offers, and activation status</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm fw-semibold">
            + Add Product
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border border-light-subtle shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Image</th>
                            <th>Product Name, SKU & Offer</th>
                            <th>Category</th>
                            <th>MRP (₹)</th>
                            <th>20% Booking (₹)</th>
                            <th>80% Balance (₹)</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>
                                    @if($product->main_image)
                                        <img src="{{ asset($product->main_image) }}" alt="product" class="rounded border" width="45" height="45" style="object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded border d-flex align-items-center justify-content-center text-muted" style="width: 45px; height: 45px;">
                                            <iconify-icon icon="{{ $product->category->icon ?? 'solar:box-bold' }}" class="fs-20"></iconify-icon>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <strong class="text-dark d-block fs-14">{{ $product->name }}</strong>
                                    <span class="text-muted small font-monospace">Model: {{ $product->model_code ?? 'NEX-STANDARD' }} | SKU: {{ $product->sku ?? 'N/A' }}</span>
                                    @if($product->offer_text)
                                        <span class="d-block badge bg-warning-subtle text-warning-emphasis micro font-monospace mt-1">{{ $product->offer_text }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark font-monospace">{{ $product->category->name }}</span>
                                </td>
                                <td class="fw-bold text-dark">₹{{ number_format($product->mrp, 2) }}</td>
                                <td class="text-primary fw-bold">₹{{ number_format($product->booking_amount, 2) }}</td>
                                <td class="text-danger fw-bold">₹{{ number_format($product->balance_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-success-subtle text-success">
                                        {{ $product->stock }} Units
                                    </span>
                                </td>
                                <td>
                                    <form action="{{ route('admin.products.status', $product->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $product->status === 'active' ? 'btn-success' : 'btn-secondary' }} px-2 py-0 fs-11">
                                            {{ ucfirst($product->status) }}
                                        </button>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary me-1">Edit</a>
                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Delete this product?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
