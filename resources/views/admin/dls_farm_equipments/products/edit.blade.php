@extends('adminlayouts.vertical', ['title' => 'Edit Farm Equipment Product'])

@section('title', 'Edit Product – ' . $product->name)

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Edit Farm Equipment Product</h4>
            <p class="text-muted small mb-0">Update pricing, SKU, specs, status, and media</p>
        </div>
        <a href="{{ route('admin.dls_farm_equipments.products.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Back to Products
        </a>
    </div>

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
            <strong>Please correct the following errors:</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border border-light-subtle shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold text-dark mb-0">Product Details – {{ $product->name }}</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.dls_farm_equipments.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Category & Title -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold text-dark">Category *</label>
                                <select name="category_id" class="form-select" required>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-semibold text-dark">Product Name *</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                            </div>
                        </div>

                        <!-- Model & SKU -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Model Code</label>
                                <input type="text" name="model_code" class="form-control" value="{{ old('model_code', $product->model_code) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">SKU Code</label>
                                <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}">
                            </div>
                        </div>

                        <!-- Video URL & Offer Text -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Demonstration Video URL</label>
                                <input type="url" name="video_url" class="form-control" value="{{ old('video_url', $product->video_url) }}" placeholder="https://youtube.com/watch?v=...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Offer / Promotional Badge Text</label>
                                <input type="text" name="offer_text" class="form-control" value="{{ old('offer_text', $product->offer_text) }}" placeholder="e.g. Subsidy Available">
                            </div>
                        </div>

                        <!-- Main Image & Gallery -->
                        <div class="row g-3 mb-3 p-3 bg-light rounded border border-light-subtle">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Update Main Image</label>
                                @if($product->main_image)
                                    <div class="mb-2">
                                        <img src="{{ \App\Helpers\ImageHelper::resolve($product->main_image) }}" 
                                             class="rounded border" width="60" height="60" style="object-fit: cover;"
                                             onerror="this.onerror=null;this.src='{{ asset('images/no-image.png') }}';">
                                    </div>
                                @endif
                                <input type="file" name="main_image" class="form-control" accept="image/*">
                                <span class="micro text-muted">Leave empty to keep existing image.</span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Add to Gallery Images</label>
                                @if(!empty($product->gallery) && is_array($product->gallery))
                                    <div class="d-flex gap-2 mb-2 flex-wrap">
                                        @foreach($product->gallery as $img)
                                            <img src="{{ \App\Helpers\ImageHelper::resolve($img) }}" 
                                                 class="rounded border" width="40" height="40" style="object-fit: cover;"
                                                 onerror="this.onerror=null;this.src='{{ asset('images/no-image.png') }}';">
                                        @endforeach
                                    </div>
                                @endif
                                <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
                                <span class="micro text-muted">Upload more images to append to gallery.</span>
                            </div>
                        </div>

                        <!-- Pricing and 20% Calculation -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Total Product Price (MRP ₹) *</label>
                                <input type="number" step="0.01" name="mrp" id="mrpInput" class="form-control" 
                                       value="{{ old('mrp', $product->mrp) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Booking Percentage (%)</label>
                                <input type="number" name="booking_percentage" id="bookingPercentInput" 
                                       class="form-control bg-light" value="{{ old('booking_percentage', $product->booking_percentage ?? 20) }}" required readonly>
                                <span class="micro text-muted">Default 20% booking token</span>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Booking Token Amount (₹)</label>
                                <input type="text" id="calculatedBooking" class="form-control bg-light fw-bold text-success" 
                                       value="₹{{ number_format($product->booking_amount, 2) }}" readonly>
                                <span class="micro text-muted">Balance: ₹{{ number_format($product->balance_amount, 2) }}</span>
                            </div>
                        </div>

                        <!-- Stock & Status -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Available Stock *</label>
                                <input type="number" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" {{ $product->status === 'active' ? 'selected' : '' }}>Active (Visible on App)</option>
                                    <option value="inactive" {{ $product->status === 'inactive' ? 'selected' : '' }}>Inactive (Hidden)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Self Dealer & Referral Settings -->
                        <div class="card bg-light border-0 mb-3 p-3">
                            <h6 class="fw-bold text-dark mb-2">Referral & Self Dealer Eligibility</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">Eligible Referral Calculation Value (₹)</label>
                                    <input type="number" step="0.01" name="eligible_referral_value" class="form-control" 
                                           value="{{ old('eligible_referral_value', $product->eligible_referral_value) }}">
                                    <span class="micro text-muted">Base value for referral commission calculations.</span>
                                </div>
                                <div class="col-md-6 d-flex flex-column justify-content-center gap-2 pt-2">
                                    <div class="form-check">
                                        <input type="checkbox" name="referral_eligible" value="1" class="form-check-input" id="refEligible" 
                                               {{ $product->referral_eligible ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold text-dark small" for="refEligible">
                                            Referral Eligible (Enables points on customer referral)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="self_dealer_eligible" value="1" class="form-check-input" id="sdEligible" 
                                               {{ $product->self_dealer_eligible ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold text-dark small" for="sdEligible">
                                            Self Dealer Activator (Eligible for self-dealer margin)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Warranty & Installation Info -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Warranty Information</label>
                                <input type="text" name="warranty_info" class="form-control" 
                                       value="{{ old('warranty_info', $product->warranty_info) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Installation & Demo Info</label>
                                <input type="text" name="installation_info" class="form-control" 
                                       value="{{ old('installation_info', $product->installation_info) }}">
                            </div>
                        </div>

                        <!-- Featured Switch -->
                        <div class="card border border-warning-subtle bg-warning-subtle p-3 rounded-3 mb-4">
                            <div class="form-check form-switch mb-1">
                                <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="featCheck" 
                                       {{ $product->is_featured ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark fs-14" for="featCheck">
                                    🔥 Mark as Featured Farm Equipment
                                </label>
                            </div>
                            <span class="text-muted small">
                                Featured items are spotlighted on promotional banners and top search results.
                            </span>
                        </div>

                        <button type="submit" class="btn btn-primary fw-semibold px-4">
                            <i class="bx bx-save me-1"></i> Update Product
                        </button>
                        <a href="{{ route('admin.dls_farm_equipments.products.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const mrpInput = document.getElementById('mrpInput');
    const calculatedBooking = document.getElementById('calculatedBooking');

    function calculate() {
        const mrp = parseFloat(mrpInput.value) || 0;
        const booking = mrp * 0.20;
        calculatedBooking.value = '₹' + booking.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    if (mrpInput) {
        mrpInput.addEventListener('input', calculate);
    }
});
</script>
@endsection
