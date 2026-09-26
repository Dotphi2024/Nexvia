@extends('adminlayouts.vertical', ['title' => 'Edit Product'])

@section('title', 'Edit Product – ' . $product->name)

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Edit Product</h4>
            <p class="text-muted small mb-0">Update technical specifications, overview, features, pricing, stock, and media</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">← Back to List</a>
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
                    <h6 class="fw-bold text-dark mb-0">Product Details, Specifications & Controls</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Category & Title -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold text-dark">Category *</label>
                                <select name="category_id" class="form-select" required>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }} ({{ ucfirst(str_replace('_', ' ', $cat->type)) }})
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
                                <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}" placeholder="e.g. SKU-NEX-001">
                            </div>
                        </div>

                        <!-- PRODUCT OVERVIEW -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark d-flex align-items-center gap-1">
                                <iconify-icon icon="solar:document-text-bold" class="text-primary fs-5"></iconify-icon>
                                Product Overview & Summary
                            </label>
                            <textarea name="overview" class="form-control" rows="4" 
                                      placeholder="Comprehensive summary of the product, motor/technology highlights, daily convenience, and application details...">{{ old('overview', $product->overview) }}</textarea>
                            <span class="micro text-muted">A clear, engaging overview displayed on product details screen on mobile and web.</span>
                        </div>

                        <!-- KEY FEATURES & HIGHLIGHTS -->
                        <div class="card border border-light-subtle shadow-sm mb-4">
                            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1">
                                        <iconify-icon icon="solar:star-bold" class="text-warning fs-5"></iconify-icon>
                                        Key Features & Highlights
                                    </h6>
                                    <span class="micro text-muted">Bullet points showcasing key advantages, safety, battery, and smart connectivity</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success fw-semibold" id="addFeatureBtn">
                                    + Add Feature
                                </button>
                            </div>
                            <div class="card-body p-3">
                                <div id="featuresContainer">
                                    @php
                                        $existingFeatures = old('features', $product->key_features ?? []);
                                        if (is_string($existingFeatures)) {
                                            $existingFeatures = json_decode($existingFeatures, true) ?? [];
                                        }
                                        if (empty($existingFeatures)) {
                                            $existingFeatures = [''];
                                        }
                                    @endphp
                                    @foreach($existingFeatures as $feat)
                                        <div class="input-group mb-2 feature-row">
                                            <span class="input-group-text bg-light text-muted">
                                                <iconify-icon icon="solar:check-circle-bold" class="text-success"></iconify-icon>
                                            </span>
                                            <input type="text" name="features[]" class="form-control" 
                                                   placeholder="Feature bullet point (e.g. Regenerative Braking System)" 
                                                   value="{{ $feat }}">
                                            <button type="button" class="btn btn-outline-danger remove-feature-btn" title="Remove Feature">
                                                <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- TECHNICAL SPECIFICATIONS -->
                        <div class="card border border-light-subtle shadow-sm mb-4">
                            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1">
                                        <iconify-icon icon="solar:tuning-square-2-bold" class="text-primary fs-5"></iconify-icon>
                                        Technical Specifications
                                    </h6>
                                    <span class="micro text-muted">Engineered parameters and values (e.g. Motor Power, Battery Capacity, Top Speed, Range, Weight, Brakes)</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" id="addSpecBtn">
                                    + Add Specification
                                </button>
                            </div>
                            <div class="card-body p-3">
                                <div id="specsContainer">
                                    @php
                                        $existingSpecs = old('specs', $product->specs ?? []);
                                        if (is_string($existingSpecs)) {
                                            $existingSpecs = json_decode($existingSpecs, true) ?? [];
                                        }
                                        if (empty($existingSpecs)) {
                                            $existingSpecs = [
                                                'Motor Type' => '',
                                                'Battery Capacity' => '',
                                            ];
                                        }
                                    @endphp
                                    @foreach($existingSpecs as $specName => $specVal)
                                        <div class="row g-2 mb-2 spec-row align-items-center">
                                            <div class="col-md-5">
                                                <input type="text" name="spec_names[]" class="form-control" 
                                                       placeholder="Parameter (e.g. Battery Capacity)" value="{{ is_string($specName) ? $specName : '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" name="spec_values[]" class="form-control" 
                                                       placeholder="Value (e.g. 60V 30Ah Lithium-Ion)" value="{{ is_array($specVal) ? implode(', ', $specVal) : $specVal }}">
                                            </div>
                                            <div class="col-md-1 text-end">
                                                <button type="button" class="btn btn-outline-danger btn-sm remove-spec-btn w-100" title="Remove Spec">
                                                    <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- VIDEO URL & OFFER TEXT -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Product Video URL</label>
                                <input type="url" name="video_url" class="form-control" value="{{ old('video_url', $product->video_url) }}" placeholder="https://youtube.com/watch?v=...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Offer / Promotional Badge Text</label>
                                <input type="text" name="offer_text" class="form-control" value="{{ old('offer_text', $product->offer_text) }}" placeholder="e.g. Festival Special ₹2,000 Off">
                            </div>
                        </div>

                        <!-- IMAGE UPLOADS -->
                        <div class="row g-3 mb-4 p-3 bg-light rounded border border-light-subtle">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Update Main Image</label>
                                @if($product->main_image)
                                    <div class="mb-2">
                                        <img src="{{ \App\Helpers\ImageHelper::resolve($product->main_image) }}" class="rounded border" width="60" height="60" style="object-fit: cover;">
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
                                            <img src="{{ \App\Helpers\ImageHelper::resolve($img) }}" class="rounded border" width="45" height="45" style="object-fit: cover;">
                                        @endforeach
                                    </div>
                                @endif
                                <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
                                <span class="micro text-muted">Upload additional gallery photos.</span>
                            </div>
                        </div>

                        <!-- PRICING & 20% BOOKING -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Total Product Price (MRP ₹) *</label>
                                <input type="number" step="0.01" name="mrp" id="mrpInput" class="form-control" value="{{ old('mrp', $product->mrp) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Booking Percentage (%)</label>
                                <input type="number" name="booking_percentage" class="form-control bg-light" value="{{ $product->booking_percentage }}" required readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Calculated Booking Amount (₹)</label>
                                <input type="text" id="calculatedBooking" class="form-control bg-light fw-bold text-success" 
                                       value="₹{{ number_format($product->booking_amount, 2) }}" readonly>
                            </div>
                        </div>

                        <!-- STOCK & STATUS -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Available Stock Count *</label>
                                <input type="number" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Product Status (Activate/Deactivate) *</label>
                                <select name="status" class="form-select">
                                    <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Inactive / Deactivated</option>
                                </select>
                            </div>
                        </div>

                        <!-- SELF DEALER & REFERRAL INCENTIVE SETTINGS -->
                        <div class="card bg-light border-0 mb-4 p-3">
                            <h6 class="fw-bold text-dark mb-2">Self Dealer & Referral Settings</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">Eligible Referral Calculation Value (₹)</label>
                                    <input type="number" step="0.01" name="eligible_referral_value" class="form-control" 
                                           value="{{ old('eligible_referral_value', $product->eligible_referral_value) }}" placeholder="Leave empty to use MRP">
                                    <span class="micro text-muted">Basis for referral points calculation. Defaults to MRP if blank.</span>
                                </div>
                                <div class="col-md-6 d-flex flex-column justify-content-center gap-2 pt-2">
                                    <div class="form-check">
                                        <input type="checkbox" name="referral_eligible" value="1" class="form-check-input" id="refEligible" {{ old('referral_eligible', $product->referral_eligible) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold text-dark small" for="refEligible">
                                            Referral Eligible (Can be referred to earn points)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="self_dealer_eligible" value="1" class="form-check-input" id="sdEligible" {{ old('self_dealer_eligible', $product->self_dealer_eligible) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold text-dark small" for="sdEligible">
                                            Self Dealer Activator (Booking this activates Self Dealer status)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- WARRANTY & INSTALLATION -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Warranty Information</label>
                                <input type="text" name="warranty_info" class="form-control" value="{{ old('warranty_info', $product->warranty_info) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Installation & Doorstep Setup</label>
                                <input type="text" name="installation_info" class="form-control" value="{{ old('installation_info', $product->installation_info) }}">
                            </div>
                        </div>

                        <!-- FEATURED DEAL SWITCH -->
                        <div class="card border border-warning-subtle bg-warning-subtle p-3 rounded-3 mb-4">
                            <div class="form-check form-switch mb-0">
                                <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="featCheck" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark fs-14" for="featCheck">
                                    🔥 Mark as Trending & Lightning Deal (Feature on App Home)
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary fw-semibold px-4">Update Product & Specifications</button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 20% calculation
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

    // Dynamic Features
    const featuresContainer = document.getElementById('featuresContainer');
    const addFeatureBtn = document.getElementById('addFeatureBtn');

    if (addFeatureBtn) {
        addFeatureBtn.addEventListener('click', function () {
            const div = document.createElement('div');
            div.className = 'input-group mb-2 feature-row';
            div.innerHTML = `
                <span class="input-group-text bg-light text-muted">
                    <iconify-icon icon="solar:check-circle-bold" class="text-success"></iconify-icon>
                </span>
                <input type="text" name="features[]" class="form-control" placeholder="Feature bullet point (e.g. Regenerative Braking System)">
                <button type="button" class="btn btn-outline-danger remove-feature-btn" title="Remove Feature">
                    <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                </button>
            `;
            featuresContainer.appendChild(div);
        });
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('.remove-feature-btn')) {
            const row = e.target.closest('.feature-row');
            if (document.querySelectorAll('.feature-row').length > 1) {
                row.remove();
            } else {
                row.querySelector('input').value = '';
            }
        }
    });

    // Dynamic Specifications
    const specsContainer = document.getElementById('specsContainer');
    const addSpecBtn = document.getElementById('addSpecBtn');

    if (addSpecBtn) {
        addSpecBtn.addEventListener('click', function () {
            const div = document.createElement('div');
            div.className = 'row g-2 mb-2 spec-row align-items-center';
            div.innerHTML = `
                <div class="col-md-5">
                    <input type="text" name="spec_names[]" class="form-control" placeholder="Parameter (e.g. Wheelbase)">
                </div>
                <div class="col-md-6">
                    <input type="text" name="spec_values[]" class="form-control" placeholder="Value (e.g. 1350 mm)">
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-spec-btn w-100" title="Remove Spec">
                        <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                    </button>
                </div>
            `;
            specsContainer.appendChild(div);
        });
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('.remove-spec-btn')) {
            const row = e.target.closest('.spec-row');
            if (document.querySelectorAll('.spec-row').length > 1) {
                row.remove();
            } else {
                row.querySelectorAll('input').forEach(i => i.value = '');
            }
        }
    });
});
</script>
@endsection
