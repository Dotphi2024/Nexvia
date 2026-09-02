@extends('adminlayouts.vertical', ['title' => 'Edit Product'])

@section('title', 'Edit Product – ' . $product->name)

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Edit Product</h4>
            <p class="text-muted small mb-0">Update pricing, SKU, video URL, offers, stock, and images</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">← Back to List</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border border-light-subtle shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold text-dark mb-0">Product Details & Controls (SKU, Video, Offer, Status)</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Category *</label>
                            <select name="category_id" class="form-select" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }} ({{ ucfirst(str_replace('_', ' ', $cat->type)) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Product Name *</label>
                            <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Model Code</label>
                                <input type="text" name="model_code" class="form-control" value="{{ $product->model_code }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">SKU Code</label>
                                <input type="text" name="sku" class="form-control" value="{{ $product->sku }}" placeholder="e.g. SKU-NEX-001">
                            </div>
                        </div>

                        <!-- VIDEO URL & OFFER TEXT -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Product Video URL</label>
                                <input type="url" name="video_url" class="form-control" value="{{ $product->video_url }}" placeholder="https://youtube.com/watch?v=...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Offer / Promotional Badge Text</label>
                                <input type="text" name="offer_text" class="form-control" value="{{ $product->offer_text }}" placeholder="e.g. Festival Special ₹2,000 Off">
                            </div>
                        </div>

                        <!-- IMAGE UPLOAD -->
                        <div class="mb-3 p-3 bg-light rounded border border-light-subtle">
                            <label class="form-label fw-semibold text-dark">Update Main Image</label>
                            @if($product->main_image)
                                <div class="mb-2">
                                    <img src="{{ asset($product->main_image) }}" class="rounded border" width="60" height="60" style="object-fit: cover;">
                                </div>
                            @endif
                            <input type="file" name="main_image" class="form-control" accept="image/*">
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Total Product Price (MRP ₹) *</label>
                                <input type="number" step="0.01" name="mrp" class="form-control" value="{{ $product->mrp }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Booking Percentage (%)</label>
                                <input type="number" name="booking_percentage" class="form-control" value="{{ $product->booking_percentage }}" required readonly>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Available Stock Count *</label>
                                <input type="number" name="stock" class="form-control" value="{{ $product->stock }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Product Status (Activate/Deactivate) *</label>
                                <select name="status" class="form-select">
                                    <option value="active" {{ $product->status === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ $product->status === 'inactive' ? 'selected' : '' }}>Inactive / Deactivated</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Warranty Information</label>
                            <input type="text" name="warranty_info" class="form-control" value="{{ $product->warranty_info }}">
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="featCheck" {{ $product->is_featured ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold text-dark" for="featCheck">Feature on Homepage</label>
                        </div>

                        <button type="submit" class="btn btn-primary fw-semibold px-4">Update Product</button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
