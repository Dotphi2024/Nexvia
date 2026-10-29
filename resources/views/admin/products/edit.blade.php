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

                        <!-- Category, Subcategory & Title -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Category *</label>
                                <select name="category_id" id="category_select" class="form-select" required>
                                    <option value="">-- Select Category --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ (old('category_id', $product->category_id) == $cat->id) ? 'selected' : '' }}>
                                            {{ $cat->name }} ({{ ucfirst(str_replace('_', ' ', $cat->type)) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Subcategory <span class="text-muted small fw-normal">(Optional)</span></label>
                                <select name="subcategory_id" id="subcategory_select" class="form-select">
                                    <option value="">-- No Subcategory / General --</option>
                                    @foreach($subcategories as $sub)
                                        <option value="{{ $sub->id }}" {{ (old('subcategory_id', $product->subcategory_id) == $sub->id) ? 'selected' : '' }}>
                                            {{ $sub->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
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

                        <!-- PRODUCT IMAGES & GALLERY -->
                        <div class="card border border-light-subtle shadow-sm mb-4">
                            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1">
                                        <iconify-icon icon="solar:gallery-wide-bold" class="text-primary fs-5"></iconify-icon>
                                        Product Images & Gallery
                                    </h6>
                                    <span class="micro text-muted">Manage existing showcase photos or add new gallery images with live previews</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary fw-semibold d-flex align-items-center gap-1" id="addGalleryBtnTop">
                                    <iconify-icon icon="solar:add-circle-bold" class="fs-6"></iconify-icon>
                                    + Add Gallery Image
                                </button>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    <!-- Main Product Image -->
                                    <div class="col-lg-5 col-md-12">
                                        <div class="p-3 bg-light rounded border border-light-subtle h-100">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="form-label fw-semibold text-dark mb-0">Main Product Image</label>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Primary Showcase</span>
                                            </div>
                                            <span class="micro text-muted d-block mb-3">
                                                @if($product->main_image)
                                                    Leave empty to keep existing image, remove it to leave blank, or upload a new one (JPG, PNG, WEBP - Max 4MB).
                                                @else
                                                    Upload a showcase photo or leave empty to keep blank (JPG, PNG, WEBP - Max 4MB).
                                                @endif
                                            </span>

                                            <input type="hidden" name="remove_main_image" id="remove_main_image_input" value="0">
                                            
                                            @if($product->main_image)
                                                <div id="currentMainImageWrapper" class="mb-3 d-flex align-items-center justify-content-between gap-2 p-2 bg-white rounded border">
                                                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                                                        <img src="{{ \App\Helpers\ImageHelper::resolve($product->main_image) }}" class="rounded border flex-shrink-0" width="60" height="60" style="object-fit: cover;">
                                                        <div class="overflow-hidden">
                                                            <span class="d-block small fw-semibold text-dark text-truncate">Current Main Image</span>
                                                            <span class="micro text-muted">Active main showcase image</span>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-outline-danger btn-sm text-nowrap d-flex align-items-center gap-1 flex-shrink-0" id="deleteCurrentMainImageBtn" title="Remove main image and keep blank">
                                                        <iconify-icon icon="solar:trash-bin-trash-bold" class="fs-6"></iconify-icon>
                                                        <span>Remove</span>
                                                    </button>
                                                </div>

                                                <!-- Notice shown when user clicks Remove -->
                                                <div id="mainImageRemovedNotice" class="alert alert-warning py-2 px-3 mb-3 d-none align-items-center justify-content-between border-warning-subtle rounded">
                                                    <div class="d-flex align-items-center gap-2 small text-dark">
                                                        <iconify-icon icon="solar:trash-bin-trash-bold" class="text-danger fs-5 flex-shrink-0"></iconify-icon>
                                                        <span><strong>Main image removed.</strong> Saved as blank/no image unless replaced.</span>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-dark py-0 px-2 micro fw-semibold flex-shrink-0" id="undoRemoveMainImageBtn" title="Restore existing image">
                                                        Undo
                                                    </button>
                                                </div>
                                            @endif

                                            <div class="mb-2">
                                                <input type="file" name="main_image" id="main_image_input" class="form-control" accept="image/*">
                                            </div>

                                            <!-- Live Preview Container for New Main Image -->
                                            <div id="mainImagePreviewWrapper" class="d-none">
                                                <div class="position-relative d-inline-block border rounded p-1 bg-white shadow-sm">
                                                    <img id="mainImagePreview" src="" alt="Main Preview" class="rounded" style="max-height: 140px; max-width: 100%; object-fit: contain;">
                                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 p-0 rounded-circle d-flex align-items-center justify-content-center" 
                                                            id="clearMainImageBtn" style="width: 22px; height: 22px;" title="Cancel replacement">
                                                        <iconify-icon icon="solar:close-circle-bold" class="fs-6"></iconify-icon>
                                                    </button>
                                                </div>
                                                <div class="micro text-muted mt-1" id="mainImageInfo"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Additional Gallery Images with + Options -->
                                    <div class="col-lg-7 col-md-12">
                                        <div class="p-3 bg-light rounded border border-light-subtle h-100 d-flex flex-column">
                                            <!-- Existing Gallery Photos -->
                                            @if(!empty($product->gallery) && is_array($product->gallery) && count($product->gallery) > 0)
                                                <div class="mb-3 p-2 bg-white rounded border border-light-subtle">
                                                    <label class="form-label fw-bold text-dark small mb-1">Existing Gallery Photos ({{ count($product->gallery) }})</label>
                                                    <span class="micro text-muted d-block mb-2">Click the red <strong>✕</strong> on any image to delete it upon save:</span>
                                                    <div class="d-flex gap-2 flex-wrap" id="existingGalleryList">
                                                        @foreach($product->gallery as $idx => $img)
                                                            <div class="position-relative existing-gallery-item border rounded p-1 bg-light shadow-sm" id="existing_item_{{ $idx }}">
                                                                <input type="hidden" name="existing_gallery[]" value="{{ $img }}">
                                                                <img src="{{ \App\Helpers\ImageHelper::resolve($img) }}" class="rounded" width="55" height="55" style="object-fit: cover;">
                                                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0 rounded-circle d-flex align-items-center justify-content-center remove-existing-gallery-btn" 
                                                                        style="width: 20px; height: 20px; transform: translate(30%, -30%);" 
                                                                        data-target="existing_item_{{ $idx }}" title="Delete this image">
                                                                    <iconify-icon icon="solar:close-circle-bold" class="fs-6"></iconify-icon>
                                                                </button>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <label class="form-label fw-semibold text-dark mb-0">Upload New Gallery Images</label>
                                                    <span class="micro text-muted d-block">Add more photos with the <strong>+ Add Image</strong> button below</span>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-primary fw-semibold d-flex align-items-center gap-1" id="addGalleryBtnHeader">
                                                    <iconify-icon icon="solar:add-circle-bold" class="fs-6"></iconify-icon>
                                                    + Add Image
                                                </button>
                                            </div>

                                            <!-- Dynamic Gallery Rows Container -->
                                            <div id="galleryContainer" class="d-flex flex-column gap-2 flex-grow-1">
                                                <!-- Dynamic rows will be inserted here -->
                                            </div>

                                            <!-- Gallery Footer with Count & Add More Button -->
                                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-light-subtle">
                                                <span class="micro text-muted" id="galleryCountBadge">0 new photo slots</span>
                                                <button type="button" class="btn btn-sm btn-primary fw-semibold d-flex align-items-center gap-1" id="addGalleryBtnBottom">
                                                    <iconify-icon icon="solar:add-circle-bold" class="fs-6"></iconify-icon>
                                                    + Add Another Image
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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

    // Dynamic Subcategory Loader based on selected Category
    const categorySelect = document.getElementById('category_select');
    const subcategorySelect = document.getElementById('subcategory_select');

    if (categorySelect && subcategorySelect) {
        categorySelect.addEventListener('change', function() {
            const catId = this.value;
            subcategorySelect.innerHTML = '<option value="">Loading subcategories...</option>';

            if (!catId) {
                subcategorySelect.innerHTML = '<option value="">-- No Subcategory / Select Category First --</option>';
                return;
            }

            fetch(`{{ route('admin.subcategories.by-category') }}?category_id=${catId}`)
                .then(res => res.json())
                .then(data => {
                    subcategorySelect.innerHTML = '<option value="">-- No Subcategory / General --</option>';
                    if (data.status && data.subcategories && data.subcategories.length > 0) {
                        data.subcategories.forEach(sub => {
                            const opt = document.createElement('option');
                            opt.value = sub.id;
                            opt.textContent = sub.name;
                            subcategorySelect.appendChild(opt);
                        });
                    }
                })
                .catch(err => {
                    console.error('Error fetching subcategories:', err);
                    subcategorySelect.innerHTML = '<option value="">-- No Subcategory / General --</option>';
                });
        });
    }

    // ==========================================
    // Dynamic Product Gallery with + Add Image (Edit)
    // ==========================================
    const galleryContainer = document.getElementById('galleryContainer');
    const addGalleryBtnTop = document.getElementById('addGalleryBtnTop');
    const addGalleryBtnHeader = document.getElementById('addGalleryBtnHeader');
    const addGalleryBtnBottom = document.getElementById('addGalleryBtnBottom');
    const galleryCountBadge = document.getElementById('galleryCountBadge');
    let galleryRowIndex = 0;

    function formatFileSize(bytes) {
        if (!bytes || bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    function updateGalleryStats() {
        if (!galleryContainer) return;
        const rows = galleryContainer.querySelectorAll('.gallery-row');
        let filledCount = 0;
        rows.forEach(r => {
            const input = r.querySelector('.gallery-file-input');
            if (input && input.files && input.files.length > 0) {
                filledCount += input.files.length;
            }
        });
        if (galleryCountBadge) {
            if (rows.length === 0) {
                galleryCountBadge.textContent = '0 new photos added';
            } else if (filledCount === 0) {
                galleryCountBadge.textContent = `${rows.length} new slot(s) ready (no photos selected)`;
            } else {
                galleryCountBadge.innerHTML = `<span class="text-success fw-bold">${filledCount} new photo(s) selected</span>`;
            }
        }
    }

    function createGalleryRow(openFileDialog = false) {
        if (!galleryContainer) return;
        galleryRowIndex++;
        const rowId = `gallery_row_${galleryRowIndex}`;
        const div = document.createElement('div');
        div.className = 'gallery-row p-2 bg-white rounded border border-light-subtle shadow-sm';
        div.id = rowId;
        div.innerHTML = `
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <div class="gallery-thumb-box rounded border bg-light d-flex align-items-center justify-content-center overflow-hidden position-relative" 
                         style="width: 54px; height: 54px; min-width: 54px;">
                        <iconify-icon icon="solar:gallery-wide-outline" class="text-muted fs-4 placeholder-icon"></iconify-icon>
                        <img src="" class="preview-img w-100 h-100 d-none" style="object-fit: cover;" alt="Gallery preview">
                    </div>
                </div>
                <div class="col">
                    <div class="input-group input-group-sm">
                        <input type="file" name="gallery[]" class="form-control gallery-file-input" accept="image/*" multiple>
                        <button type="button" class="btn btn-outline-danger remove-gallery-btn" title="Remove this photo slot">
                            <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                        </button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-1 px-1">
                        <span class="micro text-muted file-info-text text-truncate" style="max-width: 250px;">Select photo (or multiple)</span>
                        <span class="badge bg-light text-muted border border-light-subtle file-size-badge d-none"></span>
                    </div>
                </div>
            </div>
        `;

        galleryContainer.appendChild(div);

        const fileInput = div.querySelector('.gallery-file-input');
        const previewImg = div.querySelector('.preview-img');
        const placeholderIcon = div.querySelector('.placeholder-icon');
        const fileInfoText = div.querySelector('.file-info-text');
        const fileSizeBadge = div.querySelector('.file-size-badge');

        fileInput.addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                const firstFile = this.files[0];
                const totalFiles = this.files.length;

                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('d-none');
                    placeholderIcon.classList.add('d-none');
                };
                reader.readAsDataURL(firstFile);

                if (totalFiles === 1) {
                    fileInfoText.textContent = firstFile.name;
                    fileSizeBadge.textContent = formatFileSize(firstFile.size);
                    fileSizeBadge.classList.remove('d-none');
                } else {
                    fileInfoText.innerHTML = `<strong>${firstFile.name}</strong> + ${totalFiles - 1} more`;
                    let totalSize = Array.from(this.files).reduce((acc, f) => acc + f.size, 0);
                    fileSizeBadge.textContent = `${totalFiles} files (${formatFileSize(totalSize)})`;
                    fileSizeBadge.classList.remove('d-none');
                }
            } else {
                previewImg.src = '';
                previewImg.classList.add('d-none');
                placeholderIcon.classList.remove('d-none');
                fileInfoText.textContent = 'Select photo (or multiple)';
                fileSizeBadge.classList.add('d-none');
            }
            updateGalleryStats();
        });

        updateGalleryStats();

        if (openFileDialog) {
            fileInput.click();
        }
    }

    if (galleryContainer) {
        if (addGalleryBtnTop) {
            addGalleryBtnTop.addEventListener('click', function () {
                createGalleryRow(true);
            });
        }

        if (addGalleryBtnHeader) {
            addGalleryBtnHeader.addEventListener('click', function () {
                createGalleryRow(true);
            });
        }

        if (addGalleryBtnBottom) {
            addGalleryBtnBottom.addEventListener('click', function () {
                createGalleryRow(true);
            });
        }

        document.addEventListener('click', function (e) {
            const removeBtn = e.target.closest('.remove-gallery-btn');
            if (removeBtn && galleryContainer.contains(removeBtn)) {
                const row = removeBtn.closest('.gallery-row');
                row.remove();
                updateGalleryStats();
            }

            // Remove existing gallery photo item
            const removeExistingBtn = e.target.closest('.remove-existing-gallery-btn');
            if (removeExistingBtn) {
                const targetId = removeExistingBtn.getAttribute('data-target');
                const targetElem = document.getElementById(targetId);
                if (targetElem) {
                    targetElem.remove();
                }
            }
        });
    }

    // Main Image Instant Preview & Removal
    const mainImageInput = document.getElementById('main_image_input');
    const mainImagePreviewWrapper = document.getElementById('mainImagePreviewWrapper');
    const mainImagePreview = document.getElementById('mainImagePreview');
    const mainImageInfo = document.getElementById('mainImageInfo');
    const clearMainImageBtn = document.getElementById('clearMainImageBtn');
    const removeMainImageInput = document.getElementById('remove_main_image_input');
    const currentMainImageWrapper = document.getElementById('currentMainImageWrapper');
    const mainImageRemovedNotice = document.getElementById('mainImageRemovedNotice');
    const deleteCurrentMainImageBtn = document.getElementById('deleteCurrentMainImageBtn');
    const undoRemoveMainImageBtn = document.getElementById('undoRemoveMainImageBtn');

    if (deleteCurrentMainImageBtn && removeMainImageInput) {
        deleteCurrentMainImageBtn.addEventListener('click', function () {
            removeMainImageInput.value = '1';
            if (currentMainImageWrapper) currentMainImageWrapper.classList.add('d-none');
            if (mainImageRemovedNotice) {
                mainImageRemovedNotice.classList.remove('d-none');
                mainImageRemovedNotice.classList.add('d-flex');
            }
            if (mainImageInput) {
                mainImageInput.value = '';
            }
            if (mainImagePreviewWrapper) {
                mainImagePreviewWrapper.classList.add('d-none');
            }
            if (mainImagePreview) {
                mainImagePreview.src = '';
            }
        });
    }

    if (undoRemoveMainImageBtn && removeMainImageInput) {
        undoRemoveMainImageBtn.addEventListener('click', function () {
            removeMainImageInput.value = '0';
            if (mainImageRemovedNotice) {
                mainImageRemovedNotice.classList.add('d-none');
                mainImageRemovedNotice.classList.remove('d-flex');
            }
            if (currentMainImageWrapper) {
                currentMainImageWrapper.classList.remove('d-none');
            }
        });
    }

    if (mainImageInput && mainImagePreviewWrapper && mainImagePreview) {
        mainImageInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const reader = new FileReader();
                reader.onload = function (e) {
                    mainImagePreview.src = e.target.result;
                    mainImagePreviewWrapper.classList.remove('d-none');
                    if (mainImageInfo) {
                        mainImageInfo.textContent = `New: ${file.name} (${formatFileSize(file.size)})`;
                    }
                };
                reader.readAsDataURL(file);

                if (removeMainImageInput) {
                    removeMainImageInput.value = '0';
                }
                if (mainImageRemovedNotice) {
                    mainImageRemovedNotice.classList.add('d-none');
                    mainImageRemovedNotice.classList.remove('d-flex');
                }
            } else {
                mainImagePreviewWrapper.classList.add('d-none');
                mainImagePreview.src = '';
            }
        });

        if (clearMainImageBtn) {
            clearMainImageBtn.addEventListener('click', function () {
                mainImageInput.value = '';
                mainImagePreviewWrapper.classList.add('d-none');
                mainImagePreview.src = '';
                if (removeMainImageInput && removeMainImageInput.value === '1') {
                    if (mainImageRemovedNotice) {
                        mainImageRemovedNotice.classList.remove('d-none');
                        mainImageRemovedNotice.classList.add('d-flex');
                    }
                } else if (currentMainImageWrapper) {
                    currentMainImageWrapper.classList.remove('d-none');
                }
            });
        }
    }

    // Form submit protection: disable empty file inputs before submit
    const productForm = document.querySelector('form[action*="products"]');
    if (productForm) {
        productForm.addEventListener('submit', function () {
            document.querySelectorAll('.gallery-file-input').forEach(input => {
                if (!input.files || input.files.length === 0) {
                    input.disabled = true;
                }
            });
        });
    }

    window.addEventListener('pageshow', function () {
        document.querySelectorAll('.gallery-file-input').forEach(input => {
            input.disabled = false;
        });
    });
});
</script>
@endsection
