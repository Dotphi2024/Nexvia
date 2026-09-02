@extends('adminlayouts.vertical', ['title' => 'Add Product'])

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Add New Product</h4>
            <p class="text-muted small mb-0">Create product listing with 20% booking price model and image uploads</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">← Back to List</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border border-light-subtle shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold text-dark mb-0">Product Details & Images</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Category *</label>
                            <select name="category_id" class="form-select" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }} ({{ ucfirst(str_replace('_', ' ', $cat->type)) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Product Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. NEXVIA E4 Electric Scooter">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Model Code</label>
                            <input type="text" name="model_code" class="form-control" placeholder="e.g. NEX-E4-2026">
                        </div>

                        <!-- IMAGE UPLOADS -->
                        <div class="row g-3 mb-3 p-3 bg-light rounded border border-light-subtle">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Main Product Image</label>
                                <input type="file" name="main_image" class="form-control" accept="image/*">
                                <span class="micro text-muted">Upload JPG, PNG or WEBP (Max 4MB)</span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Additional Gallery Images</label>
                                <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
                                <span class="micro text-muted">Select multiple images for product gallery</span>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Total Product Price (MRP ₹) *</label>
                                <input type="number" step="0.01" name="mrp" class="form-control" required placeholder="60000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Booking Percentage (%)</label>
                                <input type="number" name="booking_percentage" class="form-control" value="20" required readonly>
                                <span class="micro text-muted">Default 20% booking amount will be calculated automatically.</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Available Stock Count *</label>
                            <input type="number" name="stock" class="form-control" value="25" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Warranty Information</label>
                            <input type="text" name="warranty_info" class="form-control" value="3 Years Comprehensive Battery & Motor Warranty">
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="featCheck" checked>
                            <label class="form-check-label fw-semibold text-dark" for="featCheck">Feature on Homepage</label>
                        </div>

                        <button type="submit" class="btn btn-primary fw-semibold px-4">Save Product & Upload Images</button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
